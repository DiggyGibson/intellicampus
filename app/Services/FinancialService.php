<?php
// app/Services/FinancialService.php

namespace App\Services;

use App\Models\{StudentAccount, BillingItem, Payment, Invoice, FinancialTransaction};
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancialService
{
    /**
     * Calculate student balance
     */
    public function calculateBalance($studentId)
    {
        $account = StudentAccount::where('student_id', $studentId)->first();
        
        if (!$account) {
            return 0;
        }

        $totalCharges = BillingItem::where('student_id', $studentId)
                                  ->where('type', 'charge')
                                  ->where('status', '!=', 'cancelled')
                                  ->sum('amount');

        $totalCredits = BillingItem::where('student_id', $studentId)
                                  ->where('type', 'credit')
                                  ->where('status', '!=', 'cancelled')
                                  ->sum('amount');

        $totalPayments = Payment::where('student_id', $studentId)
                               ->where('status', 'completed')
                               ->sum('amount');

        $totalAid = DB::table('financial_aid')
                     ->where('student_id', $studentId)
                     ->where('status', 'disbursed')
                     ->sum('disbursed_amount');

        $balance = $totalCharges - $totalCredits - $totalPayments - $totalAid;

        // Update account
        $account->total_charges = $totalCharges;
        $account->total_payments = $totalPayments;
        $account->total_aid = $totalAid;
        $account->balance = $balance;
        $account->save();

        return $balance;
    }

    /**
     * Check if student has financial hold
     */
    public function checkFinancialHold($studentId)
    {
        $account = StudentAccount::where('student_id', $studentId)->first();
        
        if (!$account) {
            return false;
        }

        // Check balance against credit limit
        if ($account->balance > $account->credit_limit) {
            return true;
        }

        // Check for overdue items
        $hasOverdue = BillingItem::where('student_id', $studentId)
                                ->overdue()
                                ->exists();

        if ($hasOverdue) {
            return true;
        }

        // Check account status
        return $account->status === 'hold';
    }

    /**
     * Apply late fees
     */
    public function applyLateFees($termId = null)
    {
        $query = BillingItem::overdue()
                           ->where('status', '!=', 'paid')
                           ->whereDoesntHave('student.billingItems', function ($q) {
                               $q->where('description', 'LIKE', '%Late Fee%')
                                 ->where('created_at', '>', now()->subDays(30));
                           });

        if ($termId) {
            $query->where('term_id', $termId);
        }

        $overdueItems = $query->get();
        $count = 0;

        foreach ($overdueItems as $item) {
            $daysPastDue = $item->due_date->diffInDays(now());
            
            if ($daysPastDue >= 30) {
                $lateFee = min($item->balance * 0.05, 50); // 5% or $50 max
                
                $account = StudentAccount::where('student_id', $item->student_id)->first();
                $account->addCharge(
                    $lateFee,
                    "Late Fee - {$item->description}",
                    $item->term_id,
                    now()->addDays(7)
                );
                
                $count++;
            }
        }

        return $count;
    }

    /**
     * Process refund
     */
    public function processRefund($studentId, $amount, $reason)
    {
        DB::transaction(function () use ($studentId, $amount, $reason) {
            $account = StudentAccount::where('student_id', $studentId)->firstOrFail();
            
            // Create credit billing item
            $credit = BillingItem::create([
                'student_id' => $studentId,
                'term_id' => DB::table('academic_terms')->where('is_current', true)->value('id'),
                'description' => "Refund: {$reason}",
                'type' => 'credit',
                'amount' => $amount,
                'balance' => 0,
                'due_date' => now(),
                'status' => 'paid',
                'created_by' => auth()->id()
            ]);

            // Update account balance
            $account->updateBalance();

            // Log transaction
            FinancialTransaction::create([
                'student_id' => $studentId,
                'type' => 'refund',
                'amount' => $amount,
                'balance_before' => $account->balance + $amount,
                'balance_after' => $account->balance,
                'reference_type' => 'billing_items',
                'reference_id' => $credit->id,
                'description' => "Refund: {$reason}",
                'created_by' => auth()->id()
            ]);
        });

        return true;
    }

    /**
     * Generate aging report
     */
    public function generateAgingReport()
    {
        $aging = DB::table('billing_items')
                   ->select(
                       'student_id',
                       DB::raw('SUM(CASE WHEN due_date > NOW() THEN balance ELSE 0 END) as current'),
                       DB::raw('SUM(CASE WHEN due_date <= NOW() AND due_date > NOW() - INTERVAL \'30 days\' THEN balance ELSE 0 END) as days_30'),
                       DB::raw('SUM(CASE WHEN due_date <= NOW() - INTERVAL \'30 days\' AND due_date > NOW() - INTERVAL \'60 days\' THEN balance ELSE 0 END) as days_60'),
                       DB::raw('SUM(CASE WHEN due_date <= NOW() - INTERVAL \'60 days\' AND due_date > NOW() - INTERVAL \'90 days\' THEN balance ELSE 0 END) as days_90'),
                       DB::raw('SUM(CASE WHEN due_date <= NOW() - INTERVAL \'90 days\' THEN balance ELSE 0 END) as over_90'),
                       DB::raw('SUM(balance) as total')
                   )
                   ->where('balance', '>', 0)
                   ->where('status', '!=', 'cancelled')
                   ->groupBy('student_id')
                   ->having('total', '>', 0)
                   ->get();

        return $aging;
    }
}