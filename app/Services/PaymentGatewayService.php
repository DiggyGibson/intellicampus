<?php
// app/Services/PaymentGatewayService.php

namespace App\Services;

use App\Models\Payment;
use App\Models\StudentAccount;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Webhook;
use Illuminate\Support\Facades\Log;
use Exception;

class PaymentGatewayService
{
    protected $stripe;
    
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }
    
    /**
     * Create a payment intent for online payment
     */
    public function createPaymentIntent($amount, $studentId, $description = null)
    {
        try {
            $amountInCents = (int)($amount * 100);
            
            $intent = PaymentIntent::create([
                'amount' => $amountInCents,
                'currency' => 'usd',
                'description' => $description ?? "IntelliCampus Payment - Student ID: {$studentId}",
                'metadata' => [
                    'student_id' => $studentId,
                    'platform' => 'IntelliCampus'
                ],
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);
            
            return [
                'success' => true,
                'client_secret' => $intent->client_secret,
                'payment_intent_id' => $intent->id,
                'amount' => $amount
            ];
            
        } catch (Exception $e) {
            Log::error('Stripe payment intent creation failed', [
                'error' => $e->getMessage(),
                'student_id' => $studentId
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Confirm and process payment after client-side confirmation
     */
    public function confirmPayment($paymentIntentId, $paymentId)
    {
        try {
            $intent = PaymentIntent::retrieve($paymentIntentId);
            
            if ($intent->status === 'succeeded') {
                // Update payment record
                $payment = Payment::find($paymentId);
                if ($payment) {
                    $payment->status = 'completed';
                    $payment->transaction_id = $intent->id;
                    $payment->payment_details = json_encode([
                        'stripe_payment_intent' => $intent->id,
                        'amount_received' => $intent->amount_received / 100,
                        'payment_method' => $intent->payment_method,
                        'receipt_url' => $intent->charges->data[0]->receipt_url ?? null
                    ]);
                    $payment->processed_at = now();
                    $payment->save();
                    
                    // Apply payment to student account
                    $this->applyPaymentToAccount($payment);
                    
                    return [
                        'success' => true,
                        'payment' => $payment,
                        'receipt_url' => $intent->charges->data[0]->receipt_url ?? null
                    ];
                }
            }
            
            return [
                'success' => false,
                'error' => 'Payment not confirmed'
            ];
            
        } catch (Exception $e) {
            Log::error('Payment confirmation failed', [
                'error' => $e->getMessage(),
                'payment_intent_id' => $paymentIntentId
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Handle Stripe webhook events
     */
    public function handleWebhook($payload, $sigHeader)
    {
        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                config('services.stripe.webhook_secret')
            );
            
            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $this->handlePaymentSuccess($event->data->object);
                    break;
                    
                case 'payment_intent.payment_failed':
                    $this->handlePaymentFailure($event->data->object);
                    break;
                    
                case 'charge.refunded':
                    $this->handleRefund($event->data->object);
                    break;
                    
                default:
                    Log::info('Unhandled webhook event', ['type' => $event->type]);
            }
            
            return ['success' => true];
            
        } catch (Exception $e) {
            Log::error('Webhook processing failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Process refund through Stripe
     */
    public function processRefund($paymentId, $amount = null, $reason = null)
    {
        try {
            $payment = Payment::find($paymentId);
            if (!$payment || !$payment->transaction_id) {
                throw new Exception('Payment not found or not processed through gateway');
            }
            
            $refundData = [
                'payment_intent' => $payment->transaction_id,
            ];
            
            if ($amount) {
                $refundData['amount'] = (int)($amount * 100);
            }
            
            if ($reason) {
                $refundData['reason'] = $reason;
            }
            
            $refund = \Stripe\Refund::create($refundData);
            
            // Create refund record
            \App\Models\Refund::create([
                'payment_id' => $paymentId,
                'amount' => $refund->amount / 100,
                'gateway_refund_id' => $refund->id,
                'reason' => $reason,
                'status' => 'completed',
                'processed_at' => now()
            ]);
            
            // Update student account balance
            $studentAccount = StudentAccount::where('student_id', $payment->student_id)->first();
            if ($studentAccount) {
                $studentAccount->balance += ($refund->amount / 100);
                $studentAccount->save();
            }
            
            return [
                'success' => true,
                'refund_id' => $refund->id,
                'amount' => $refund->amount / 100
            ];
            
        } catch (Exception $e) {
            Log::error('Refund processing failed', [
                'error' => $e->getMessage(),
                'payment_id' => $paymentId
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Apply payment to student account and outstanding charges
     */
    protected function applyPaymentToAccount($payment)
    {
        $account = StudentAccount::where('student_id', $payment->student_id)->first();
        
        if ($account) {
            // Update account balance
            $account->balance -= $payment->amount;
            $account->total_payments += $payment->amount;
            $account->last_payment_date = now();
            $account->save();
            
            // Apply to outstanding billing items
            $remainingAmount = $payment->amount;
            
            $unpaidItems = \App\Models\BillingItem::where('student_id', $payment->student_id)
                ->where('status', '!=', 'paid')
                ->orderBy('due_date')
                ->get();
            
            foreach ($unpaidItems as $item) {
                if ($remainingAmount <= 0) break;
                
                $allocationAmount = min($remainingAmount, $item->balance);
                
                // Create payment allocation
                \App\Models\PaymentAllocation::create([
                    'payment_id' => $payment->id,
                    'billing_item_id' => $item->id,
                    'amount' => $allocationAmount
                ]);
                
                // Update billing item
                $item->balance -= $allocationAmount;
                $item->status = $item->balance <= 0 ? 'paid' : 'partial';
                $item->save();
                
                $remainingAmount -= $allocationAmount;
            }
            
            // Check and remove financial hold if balance is clear
            if ($account->balance <= 0 && $account->has_financial_hold) {
                $this->removeFinancialHold($account);
            }
        }
    }
    
    /**
     * Remove financial hold from account
     */
    protected function removeFinancialHold($account)
    {
        $account->has_financial_hold = false;
        $account->hold_reason = null;
        $account->hold_removed_date = now();
        $account->save();
        
        // Also remove from registration holds
        \DB::table('registration_holds')
            ->where('student_id', $account->student_id)
            ->where('hold_type', 'financial')
            ->delete();
    }
    
    /**
     * Handle successful payment from webhook
     */
    protected function handlePaymentSuccess($paymentIntent)
    {
        $studentId = $paymentIntent->metadata->student_id ?? null;
        
        if ($studentId) {
            // Find pending payment record
            $payment = Payment::where('student_id', $studentId)
                ->where('transaction_id', $paymentIntent->id)
                ->where('status', '!=', 'completed')
                ->first();
            
            if ($payment) {
                $payment->status = 'completed';
                $payment->processed_at = now();
                $payment->save();
                
                $this->applyPaymentToAccount($payment);
                
                // Send confirmation email
                $this->sendPaymentConfirmation($payment);
            }
        }
    }
    
    /**
     * Handle failed payment from webhook
     */
    protected function handlePaymentFailure($paymentIntent)
    {
        $studentId = $paymentIntent->metadata->student_id ?? null;
        
        if ($studentId) {
            $payment = Payment::where('student_id', $studentId)
                ->where('transaction_id', $paymentIntent->id)
                ->first();
            
            if ($payment) {
                $payment->status = 'failed';
                $payment->notes = $paymentIntent->last_payment_error->message ?? 'Payment failed';
                $payment->save();
            }
        }
    }
    
    /**
     * Send payment confirmation email
     */
    protected function sendPaymentConfirmation($payment)
    {
        // Implement email notification
        // This would integrate with your notification system
        Log::info('Payment confirmation email would be sent', [
            'payment_id' => $payment->id,
            'student_id' => $payment->student_id
        ]);
    }
}