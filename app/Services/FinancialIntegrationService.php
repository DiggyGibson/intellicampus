<?php

namespace App\Services;

use App\Models\AdmissionApplication;
use App\Models\ApplicationFee;
use App\Models\EnrollmentConfirmation;
use App\Models\FinancialTransaction;
use App\Models\StudentAccount;
use App\Models\PaymentGateway;
use App\Models\Invoice;
use App\Models\FeeStructure;
use App\Models\ApplicationCommunication;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class FinancialIntegrationService
{
    /**
     * Payment gateways configuration
     */
    private const PAYMENT_GATEWAYS = [
        'stripe' => ['enabled' => true, 'priority' => 1],
        'paypal' => ['enabled' => true, 'priority' => 2],
        'mobile_money' => ['enabled' => true, 'priority' => 3],
        'bank_transfer' => ['enabled' => true, 'priority' => 4],
    ];

    /**
     * Fee types and amounts
     */
    private const FEE_TYPES = [
        'application_fee' => [
            'freshman' => 50.00,
            'transfer' => 50.00,
            'graduate' => 75.00,
            'international' => 100.00,
            'readmission' => 25.00,
        ],
        'enrollment_deposit' => [
            'undergraduate' => 500.00,
            'graduate' => 750.00,
            'doctoral' => 1000.00,
        ],
        'document_evaluation' => 50.00,
        'late_application' => 25.00,
        'appeal_processing' => 50.00,
    ];

    /**
     * Fee waiver types
     */
    private const WAIVER_TYPES = [
        'need_based' => 'Financial Need',
        'merit_based' => 'Academic Merit',
        'employee' => 'Employee Benefit',
        'veteran' => 'Veteran Benefit',
        'partner_institution' => 'Partner Institution',
        'promotional' => 'Promotional Waiver',
    ];

    /**
     * Refund policies (percentage by days)
     */
    private const REFUND_POLICIES = [
        'application_fee' => [
            7 => 100,   // 100% refund within 7 days
            14 => 50,   // 50% refund within 14 days
            30 => 0,    // No refund after 30 days
        ],
        'enrollment_deposit' => [
            30 => 100,  // 100% refund within 30 days
            60 => 75,   // 75% refund within 60 days
            90 => 50,   // 50% refund within 90 days
            120 => 0,   // No refund after 120 days
        ],
    ];

    /**
     * Process application fee payment
     *
     * @param int $applicationId
     * @param array $paymentData
     * @return array
     * @throws Exception
     */
    public function processApplicationFee(int $applicationId, array $paymentData): array
    {
        DB::beginTransaction();

        try {
            $application = AdmissionApplication::findOrFail($applicationId);
            
            // Check if fee already paid
            $existingFee = ApplicationFee::where('application_id', $applicationId)
                ->where('fee_type', 'application_fee')
                ->where('status', 'paid')
                ->first();

            if ($existingFee) {
                throw new Exception("Application fee has already been paid");
            }

            // Get or create fee record
            $fee = ApplicationFee::firstOrCreate(
                [
                    'application_id' => $applicationId,
                    'fee_type' => 'application_fee',
                ],
                [
                    'amount' => $this->getApplicationFeeAmount($application),
                    'currency' => $paymentData['currency'] ?? 'USD',
                    'status' => 'pending',
                    'due_date' => Carbon::now()->addDays(7),
                ]
            );

            // Process payment through gateway
            $paymentResult = $this->processPaymentThroughGateway($fee, $paymentData);

            if ($paymentResult['success']) {
                // Update fee record
                $fee->status = 'paid';
                $fee->payment_method = $paymentData['payment_method'];
                $fee->transaction_id = $paymentResult['transaction_id'];
                $fee->paid_date = now();
                $fee->receipt_number = $this->generateReceiptNumber();
                $fee->save();

                // Update application
                $application->application_fee_paid = true;
                $application->application_fee_amount = $fee->amount;
                $application->application_fee_date = now();
                $application->application_fee_receipt = $fee->receipt_number;
                $application->save();

                // Create financial transaction record
                $this->createFinancialTransaction($application, $fee, $paymentResult);

                // Generate invoice
                $invoice = $this->generateInvoice($applicationId, 'application_fee');

                // Send payment confirmation
                $this->sendPaymentConfirmation($application, $fee);

                DB::commit();

                Log::info('Application fee processed', [
                    'application_id' => $applicationId,
                    'amount' => $fee->amount,
                    'transaction_id' => $paymentResult['transaction_id'],
                ]);

                return [
                    'success' => true,
                    'message' => 'Payment processed successfully',
                    'transaction_id' => $paymentResult['transaction_id'],
                    'receipt_number' => $fee->receipt_number,
                    'invoice_id' => $invoice->id ?? null,
                    'amount_paid' => $fee->amount,
                    'paid_date' => $fee->paid_date,
                ];

            } else {
                throw new Exception($paymentResult['error'] ?? 'Payment processing failed');
            }

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Application fee processing failed', [
                'application_id' => $applicationId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Process enrollment deposit payment
     *
     * @param int $applicationId
     * @param array $paymentData
     * @return array
     * @throws Exception
     */
    public function processEnrollmentDeposit(int $applicationId, array $paymentData): array
    {
        DB::beginTransaction();

        try {
            $application = AdmissionApplication::with('enrollmentConfirmation')->findOrFail($applicationId);
            
            // Verify application is admitted
            if (!in_array($application->decision, ['admit', 'conditional_admit'])) {
                throw new Exception("Only admitted students can pay enrollment deposit");
            }

            $enrollment = $application->enrollmentConfirmation;
            if (!$enrollment) {
                throw new Exception("Enrollment confirmation record not found");
            }

            // Check if deposit already paid
            if ($enrollment->deposit_paid) {
                throw new Exception("Enrollment deposit has already been paid");
            }

            // Create fee record
            $fee = ApplicationFee::create([
                'application_id' => $applicationId,
                'fee_type' => 'enrollment_deposit',
                'amount' => $enrollment->deposit_amount,
                'currency' => $paymentData['currency'] ?? 'USD',
                'status' => 'pending',
                'due_date' => $enrollment->enrollment_deadline,
            ]);

            // Process payment
            $paymentResult = $this->processPaymentThroughGateway($fee, $paymentData);

            if ($paymentResult['success']) {
                // Update fee record
                $fee->status = 'paid';
                $fee->payment_method = $paymentData['payment_method'];
                $fee->transaction_id = $paymentResult['transaction_id'];
                $fee->paid_date = now();
                $fee->receipt_number = $this->generateReceiptNumber();
                $fee->save();

                // Update enrollment confirmation
                $enrollment->deposit_paid = true;
                $enrollment->deposit_paid_date = now();
                $enrollment->deposit_transaction_id = $paymentResult['transaction_id'];
                $enrollment->save();

                // Update application
                $application->enrollment_deposit_paid = true;
                $application->enrollment_deposit_amount = $fee->amount;
                $application->enrollment_deposit_date = now();
                $application->enrollment_deposit_receipt = $fee->receipt_number;
                $application->save();

                // Create financial transaction
                $this->createFinancialTransaction($application, $fee, $paymentResult);

                // Generate invoice
                $invoice = $this->generateInvoice($applicationId, 'enrollment_deposit');

                // Send confirmation
                $this->sendDepositConfirmation($application, $fee);

                DB::commit();

                Log::info('Enrollment deposit processed', [
                    'application_id' => $applicationId,
                    'amount' => $fee->amount,
                    'transaction_id' => $paymentResult['transaction_id'],
                ]);

                return [
                    'success' => true,
                    'message' => 'Enrollment deposit paid successfully',
                    'transaction_id' => $paymentResult['transaction_id'],
                    'receipt_number' => $fee->receipt_number,
                    'invoice_id' => $invoice->id ?? null,
                    'amount_paid' => $fee->amount,
                    'enrollment_confirmed' => true,
                ];

            } else {
                throw new Exception($paymentResult['error'] ?? 'Payment processing failed');
            }

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Enrollment deposit processing failed', [
                'application_id' => $applicationId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Issue refund for a fee
     *
     * @param int $applicationId
     * @param float $amount
     * @param string $reason
     * @return array
     * @throws Exception
     */
    public function issueRefund(int $applicationId, float $amount, string $reason): array
    {
        DB::beginTransaction();

        try {
            $application = AdmissionApplication::findOrFail($applicationId);
            
            // Get paid fees
            $paidFees = ApplicationFee::where('application_id', $applicationId)
                ->where('status', 'paid')
                ->get();

            if ($paidFees->isEmpty()) {
                throw new Exception("No paid fees found for this application");
            }

            // Calculate total paid amount
            $totalPaid = $paidFees->sum('amount');
            
            if ($amount > $totalPaid) {
                throw new Exception("Refund amount exceeds total paid amount");
            }

            // Determine which fee to refund
            $feeToRefund = $this->determineFeeToRefund($paidFees, $amount);
            
            // Check refund eligibility
            $refundEligibility = $this->checkRefundEligibility($feeToRefund);
            
            if (!$refundEligibility['eligible']) {
                throw new Exception($refundEligibility['reason']);
            }

            // Process refund through gateway
            $refundResult = $this->processRefundThroughGateway($feeToRefund, $amount);

            if ($refundResult['success']) {
                // Update fee record
                $feeToRefund->status = 'refunded';
                $feeToRefund->refunded_amount = $amount;
                $feeToRefund->refunded_date = now();
                $feeToRefund->refund_transaction_id = $refundResult['refund_id'];
                $feeToRefund->refund_reason = $reason;
                $feeToRefund->save();

                // Create refund transaction
                $this->createRefundTransaction($application, $feeToRefund, $refundResult);

                // Update application status if needed
                if ($feeToRefund->fee_type === 'application_fee') {
                    $application->application_fee_paid = false;
                    $application->save();
                } elseif ($feeToRefund->fee_type === 'enrollment_deposit') {
                    $application->enrollment_deposit_paid = false;
                    $application->save();
                    
                    if ($application->enrollmentConfirmation) {
                        $application->enrollmentConfirmation->deposit_paid = false;
                        $application->enrollmentConfirmation->save();
                    }
                }

                // Send refund confirmation
                $this->sendRefundConfirmation($application, $amount, $reason);

                DB::commit();

                Log::info('Refund issued', [
                    'application_id' => $applicationId,
                    'amount' => $amount,
                    'reason' => $reason,
                    'refund_id' => $refundResult['refund_id'],
                ]);

                return [
                    'success' => true,
                    'message' => 'Refund processed successfully',
                    'refund_id' => $refundResult['refund_id'],
                    'amount_refunded' => $amount,
                    'refund_date' => now()->format('Y-m-d'),
                ];

            } else {
                throw new Exception($refundResult['error'] ?? 'Refund processing failed');
            }

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Refund processing failed', [
                'application_id' => $applicationId,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Apply fee waiver
     *
     * @param int $applicationId
     * @param string $waiverType
     * @param array $waiverData
     * @return array
     * @throws Exception
     */
    public function applyFeeWaiver(int $applicationId, string $waiverType, array $waiverData = []): array
    {
        DB::beginTransaction();

        try {
            $application = AdmissionApplication::findOrFail($applicationId);
            
            // Validate waiver type
            if (!isset(self::WAIVER_TYPES[$waiverType])) {
                throw new Exception("Invalid waiver type: {$waiverType}");
            }

            // Check if waiver already applied
            if ($application->fee_waiver_approved) {
                throw new Exception("Fee waiver has already been approved for this application");
            }

            // Verify waiver eligibility
            $eligibility = $this->verifyWaiverEligibility($application, $waiverType, $waiverData);
            
            if (!$eligibility['eligible']) {
                throw new Exception($eligibility['reason']);
            }

            // Get or create fee record
            $fee = ApplicationFee::firstOrCreate(
                [
                    'application_id' => $applicationId,
                    'fee_type' => 'application_fee',
                ],
                [
                    'amount' => $this->getApplicationFeeAmount($application),
                    'currency' => 'USD',
                    'status' => 'waived',
                ]
            );

            // Apply waiver
            $fee->status = 'waived';
            $fee->waiver_type = $waiverType;
            $fee->waiver_amount = $fee->amount * ($eligibility['waiver_percentage'] / 100);
            $fee->waiver_reason = $waiverData['reason'] ?? self::WAIVER_TYPES[$waiverType];
            $fee->waiver_approved_by = auth()->id();
            $fee->waiver_approved_date = now();
            $fee->save();

            // Update application
            $application->fee_waiver_requested = true;
            $application->fee_waiver_approved = true;
            $application->fee_waiver_reason = $fee->waiver_reason;
            $application->application_fee_paid = true; // Mark as paid since waived
            $application->save();

            // Send waiver confirmation
            $this->sendWaiverConfirmation($application, $fee);

            DB::commit();

            Log::info('Fee waiver applied', [
                'application_id' => $applicationId,
                'waiver_type' => $waiverType,
                'waiver_amount' => $fee->waiver_amount,
            ]);

            return [
                'success' => true,
                'message' => 'Fee waiver applied successfully',
                'waiver_type' => $waiverType,
                'waiver_amount' => $fee->waiver_amount,
                'waiver_percentage' => $eligibility['waiver_percentage'],
                'remaining_amount' => $fee->amount - $fee->waiver_amount,
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Fee waiver application failed', [
                'application_id' => $applicationId,
                'waiver_type' => $waiverType,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate invoice for a fee
     *
     * @param int $applicationId
     * @param string $feeType
     * @return Invoice
     * @throws Exception
     */
    public function generateInvoice(int $applicationId, string $feeType): Invoice
    {
        try {
            $application = AdmissionApplication::findOrFail($applicationId);
            $fee = ApplicationFee::where('application_id', $applicationId)
                ->where('fee_type', $feeType)
                ->firstOrFail();

            // Generate invoice number
            $invoiceNumber = $this->generateInvoiceNumber();

            // Create invoice
            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'application_id' => $applicationId,
                'student_id' => $application->student_id ?? null,
                'invoice_date' => now(),
                'due_date' => $fee->due_date,
                'subtotal' => $fee->amount,
                'tax_amount' => 0,
                'total_amount' => $fee->amount,
                'paid_amount' => $fee->status === 'paid' ? $fee->amount : 0,
                'balance_due' => $fee->status === 'paid' ? 0 : $fee->amount,
                'status' => $fee->status === 'paid' ? 'paid' : 'pending',
                'items' => [
                    [
                        'description' => $this->getFeeDescription($feeType),
                        'quantity' => 1,
                        'unit_price' => $fee->amount,
                        'amount' => $fee->amount,
                    ]
                ],
            ]);

            // Generate PDF invoice
            $this->generateInvoicePDF($invoice, $application);

            Log::info('Invoice generated', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoiceNumber,
                'application_id' => $applicationId,
            ]);

            return $invoice;

        } catch (Exception $e) {
            Log::error('Invoice generation failed', [
                'application_id' => $applicationId,
                'fee_type' => $feeType,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Reconcile payments for a specific date
     *
     * @param Carbon $date
     * @return array
     */
    public function reconcilePayments(Carbon $date): array
    {
        try {
            // Get all transactions for the date
            $transactions = FinancialTransaction::whereDate('transaction_date', $date)
                ->where('transaction_type', 'payment')
                ->get();

            $reconciliation = [
                'date' => $date->format('Y-m-d'),
                'total_transactions' => $transactions->count(),
                'total_amount' => $transactions->sum('amount'),
                'by_payment_method' => [],
                'by_fee_type' => [],
                'discrepancies' => [],
                'status' => 'balanced',
            ];

            // Group by payment method
            $byMethod = $transactions->groupBy('payment_method');
            foreach ($byMethod as $method => $methodTransactions) {
                $reconciliation['by_payment_method'][$method] = [
                    'count' => $methodTransactions->count(),
                    'amount' => $methodTransactions->sum('amount'),
                ];
            }

            // Group by fee type
            $byType = $transactions->groupBy('fee_type');
            foreach ($byType as $type => $typeTransactions) {
                $reconciliation['by_fee_type'][$type] = [
                    'count' => $typeTransactions->count(),
                    'amount' => $typeTransactions->sum('amount'),
                ];
            }

            // Check for discrepancies
            foreach ($transactions as $transaction) {
                // Verify with payment gateway
                $gatewayRecord = $this->verifyWithGateway($transaction);
                
                if (!$gatewayRecord['matches']) {
                    $reconciliation['discrepancies'][] = [
                        'transaction_id' => $transaction->id,
                        'issue' => $gatewayRecord['issue'],
                        'our_amount' => $transaction->amount,
                        'gateway_amount' => $gatewayRecord['amount'],
                    ];
                    $reconciliation['status'] = 'discrepancies_found';
                }
            }

            // Generate reconciliation report
            $this->generateReconciliationReport($reconciliation);

            Log::info('Payment reconciliation completed', [
                'date' => $date->format('Y-m-d'),
                'status' => $reconciliation['status'],
                'discrepancies' => count($reconciliation['discrepancies']),
            ]);

            return $reconciliation;

        } catch (Exception $e) {
            Log::error('Payment reconciliation failed', [
                'date' => $date->format('Y-m-d'),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Track outstanding fees for a term
     *
     * @param int $termId
     * @return array
     */
    public function trackOutstandingFees(int $termId): array
    {
        try {
            // Get all applications for the term
            $applications = AdmissionApplication::where('term_id', $termId)
                ->whereIn('status', ['submitted', 'under_review', 'admitted'])
                ->get();

            $outstanding = [
                'term_id' => $termId,
                'total_applications' => $applications->count(),
                'outstanding_fees' => [],
                'total_outstanding_amount' => 0,
                'by_fee_type' => [],
                'overdue' => [],
            ];

            foreach ($applications as $application) {
                // Get unpaid fees
                $unpaidFees = ApplicationFee::where('application_id', $application->id)
                    ->whereIn('status', ['pending', 'overdue'])
                    ->get();

                foreach ($unpaidFees as $fee) {
                    $isOverdue = $fee->due_date && $fee->due_date < now();
                    
                    $feeData = [
                        'application_id' => $application->id,
                        'application_number' => $application->application_number,
                        'applicant_name' => $application->first_name . ' ' . $application->last_name,
                        'fee_type' => $fee->fee_type,
                        'amount' => $fee->amount,
                        'due_date' => $fee->due_date?->format('Y-m-d'),
                        'days_overdue' => $isOverdue ? now()->diffInDays($fee->due_date) : 0,
                        'status' => $isOverdue ? 'overdue' : 'pending',
                    ];

                    $outstanding['outstanding_fees'][] = $feeData;
                    $outstanding['total_outstanding_amount'] += $fee->amount;

                    // Group by fee type
                    if (!isset($outstanding['by_fee_type'][$fee->fee_type])) {
                        $outstanding['by_fee_type'][$fee->fee_type] = [
                            'count' => 0,
                            'amount' => 0,
                        ];
                    }
                    $outstanding['by_fee_type'][$fee->fee_type]['count']++;
                    $outstanding['by_fee_type'][$fee->fee_type]['amount'] += $fee->amount;

                    // Track overdue
                    if ($isOverdue) {
                        $outstanding['overdue'][] = $feeData;
                    }
                }
            }

            // Send reminders for overdue fees
            if (!empty($outstanding['overdue'])) {
                $this->sendOverdueReminders($outstanding['overdue']);
            }

            Log::info('Outstanding fees tracked', [
                'term_id' => $termId,
                'total_outstanding' => $outstanding['total_outstanding_amount'],
                'overdue_count' => count($outstanding['overdue']),
            ]);

            return $outstanding;

        } catch (Exception $e) {
            Log::error('Outstanding fees tracking failed', [
                'term_id' => $termId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Private helper methods
     */

    /**
     * Get application fee amount based on type
     */
    private function getApplicationFeeAmount(AdmissionApplication $application): float
    {
        return self::FEE_TYPES['application_fee'][$application->application_type] ?? 50.00;
    }

    /**
     * Process payment through gateway
     */
    private function processPaymentThroughGateway(ApplicationFee $fee, array $paymentData): array
    {
        $gateway = $paymentData['payment_method'] ?? 'stripe';
        
        // This would integrate with actual payment gateways
        // For now, simulate successful payment
        
        try {
            // Simulate API call to payment gateway
            $transactionId = 'TXN-' . date('YmdHis') . '-' . Str::random(6);
            
            // Log payment attempt
            Log::info('Processing payment', [
                'gateway' => $gateway,
                'amount' => $fee->amount,
                'fee_id' => $fee->id,
            ]);

            // Simulate processing delay
            usleep(500000); // 0.5 seconds

            return [
                'success' => true,
                'transaction_id' => $transactionId,
                'gateway' => $gateway,
                'timestamp' => now(),
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate receipt number
     */
    private function generateReceiptNumber(): string
    {
        $prefix = 'RCP';
        $year = date('Y');
        $random = str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$year}-{$random}";
    }

    /**
     * Create financial transaction record
     */
    private function createFinancialTransaction(
        AdmissionApplication $application,
        ApplicationFee $fee,
        array $paymentResult
    ): void {
        FinancialTransaction::create([
            'application_id' => $application->id,
            'student_id' => $application->student_id ?? null,
            'transaction_type' => 'payment',
            'transaction_date' => now(),
            'amount' => $fee->amount,
            'currency' => $fee->currency,
            'payment_method' => $fee->payment_method,
            'transaction_id' => $paymentResult['transaction_id'],
            'fee_type' => $fee->fee_type,
            'description' => $this->getFeeDescription($fee->fee_type),
            'status' => 'completed',
        ]);
    }

    /**
     * Get fee description
     */
    private function getFeeDescription(string $feeType): string
    {
        $descriptions = [
            'application_fee' => 'Application Processing Fee',
            'enrollment_deposit' => 'Enrollment Confirmation Deposit',
            'document_evaluation' => 'Document Evaluation Fee',
            'late_application' => 'Late Application Fee',
            'appeal_processing' => 'Appeal Processing Fee',
        ];

        return $descriptions[$feeType] ?? ucwords(str_replace('_', ' ', $feeType));
    }

    // Additional helper methods for notifications, validations, etc.
}