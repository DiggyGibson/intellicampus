<?php
// app/Models/StudentAccount.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'balance',
        'total_charges',
        'total_payments',
        'total_aid',
        'credit_limit',
        'status',
        'has_payment_plan',
        'last_payment_date',
        'next_due_date'
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'total_charges' => 'decimal:2',
        'total_payments' => 'decimal:2',
        'total_aid' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'has_payment_plan' => 'boolean',
        'last_payment_date' => 'date',
        'next_due_date' => 'date'
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function billingItems()
    {
        return $this->hasMany(BillingItem::class, 'student_id', 'student_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'student_id', 'student_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'student_id', 'student_id');
    }

    public function financialAid()
    {
        return $this->hasMany(FinancialAid::class, 'student_id', 'student_id');
    }

    public function paymentPlans()
    {
        return $this->hasMany(PaymentPlan::class, 'student_id', 'student_id');
    }

    public function transactions()
    {
        return $this->hasMany(FinancialTransaction::class, 'student_id', 'student_id');
    }

    // Scopes
    public function scopeWithBalance($query)
    {
        return $query->where('balance', '>', 0);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOnHold($query)
    {
        return $query->where('status', 'hold');
    }

    // Methods
    public function updateBalance()
    {
        $this->balance = $this->total_charges - $this->total_payments - $this->total_aid;
        $this->save();
        return $this->balance;
    }

    public function addCharge($amount, $description, $termId, $dueDate = null, $feeStructureId = null)
    {
        $billingItem = BillingItem::create([
            'student_id' => $this->student_id,
            'term_id' => $termId,
            'fee_structure_id' => $feeStructureId,
            'description' => $description,
            'type' => 'charge',
            'amount' => $amount,
            'balance' => $amount,
            'due_date' => $dueDate ?? now()->addDays(30),
            'status' => 'billed'
        ]);

        $this->total_charges += $amount;
        $this->updateBalance();

        $this->logTransaction('charge', $amount, $description, 'billing_items', $billingItem->id);

        return $billingItem;
    }

    public function applyPayment($payment)
    {
        $remainingAmount = $payment->amount;
        
        // Get unpaid billing items ordered by due date
        $unpaidItems = BillingItem::where('student_id', $this->student_id)
                                  ->where('balance', '>', 0)
                                  ->orderBy('due_date')
                                  ->get();

        foreach ($unpaidItems as $item) {
            if ($remainingAmount <= 0) break;

            $allocationAmount = min($remainingAmount, $item->balance);
            
            // Create payment allocation
            PaymentAllocation::create([
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

        $this->total_payments += $payment->amount;
        $this->last_payment_date = $payment->payment_date;
        $this->updateBalance();

        $this->logTransaction('payment', $payment->amount, 'Payment received', 'payments', $payment->id);

        return $this;
    }

    public function hasFinancialHold()
    {
        return $this->status === 'hold' || $this->balance > $this->credit_limit;
    }

    public function createFinancialHold($reason = null)
    {
        if (!$this->hasFinancialHold()) {
            return null;
        }

        return \DB::table('registration_holds')->updateOrInsert(
            [
                'student_id' => $this->student_id,
                'hold_type' => 'financial'
            ],
            [
                'reason' => $reason ?? 'Outstanding balance: $' . number_format($this->balance, 2),
                'amount_owed' => $this->balance,
                'is_active' => true,
                'placed_date' => now(),
                'placed_by' => auth()->id(),
                'updated_at' => now()
            ]
        );
    }

    public function releaseFinancialHold()
    {
        return \DB::table('registration_holds')
                  ->where('student_id', $this->student_id)
                  ->where('hold_type', 'financial')
                  ->update([
                      'is_active' => false,
                      'resolved_date' => now(),
                      'resolved_by' => auth()->id(),
                      'updated_at' => now()
                  ]);
    }

    protected function logTransaction($type, $amount, $description, $referenceType = null, $referenceId = null)
    {
        $balanceBefore = $this->balance + ($type === 'payment' ? $amount : -$amount);
        
        FinancialTransaction::create([
            'student_id' => $this->student_id,
            'type' => $type,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->balance,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'description' => $description,
            'created_by' => auth()->id()
        ]);
    }
}