<?php
// app/Models/FinancialAid.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialAid extends Model
{
    protected $table = 'financial_aid';
    
    protected $fillable = [
        'student_id', 'term_id', 'aid_name', 'type', 'amount',
        'disbursed_amount', 'status', 'award_date', 'disbursement_date',
        'conditions', 'notes', 'approved_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'disbursed_amount' => 'decimal:2',
        'award_date' => 'date',
        'disbursement_date' => 'date'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function term()
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function disburse()
    {
        if ($this->status !== 'approved') {
            return false;
        }

        \DB::transaction(function () {
            $account = StudentAccount::where('student_id', $this->student_id)->first();
            
            // Apply as credit
            $account->total_aid += $this->amount;
            $account->updateBalance();

            $this->disbursed_amount = $this->amount;
            $this->disbursement_date = now();
            $this->status = 'disbursed';
            $this->save();

            // Log transaction
            FinancialTransaction::create([
                'student_id' => $this->student_id,
                'type' => 'aid',
                'amount' => $this->amount,
                'balance_before' => $account->balance + $this->amount,
                'balance_after' => $account->balance,
                'reference_type' => 'financial_aid',
                'reference_id' => $this->id,
                'description' => "Financial Aid: {$this->aid_name}",
                'created_by' => auth()->id()
            ]);
        });

        return true;
    }
}