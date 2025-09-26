<?php
// app/Models/PaymentPlan.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentPlan extends Model
{
    protected $fillable = [
        'student_id', 'term_id', 'plan_name', 'total_amount',
        'number_of_installments', 'installment_amount', 'start_date',
        'end_date', 'status', 'paid_amount', 'paid_installments',
        'next_due_date', 'terms_conditions', 'approved_by', 'approved_at'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'installment_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'next_due_date' => 'date',
        'approved_at' => 'datetime'
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

    public function schedules()
    {
        return $this->hasMany(PaymentPlanSchedule::class);
    }

    public function generateSchedule()
    {
        $schedules = [];
        $installmentDate = $this->start_date->copy();

        for ($i = 1; $i <= $this->number_of_installments; $i++) {
            $schedules[] = [
                'payment_plan_id' => $this->id,
                'installment_number' => $i,
                'amount' => $this->installment_amount,
                'due_date' => $installmentDate->copy(),
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now()
            ];

            $installmentDate->addMonth();
        }

        PaymentPlanSchedule::insert($schedules);
        
        $this->next_due_date = $this->start_date;
        $this->save();

        return $this->schedules()->get();
    }

    public function recordInstallmentPayment($amount)
    {
        $nextSchedule = $this->schedules()
                             ->where('status', 'pending')
                             ->orderBy('due_date')
                             ->first();

        if ($nextSchedule) {
            $nextSchedule->paid_amount = $amount;
            $nextSchedule->paid_date = now();
            $nextSchedule->status = $amount >= $nextSchedule->amount ? 'paid' : 'partial';
            $nextSchedule->save();

            $this->paid_amount += $amount;
            $this->paid_installments = $this->schedules()->where('status', 'paid')->count();
            
            $nextPending = $this->schedules()
                                ->where('status', 'pending')
                                ->orderBy('due_date')
                                ->first();
            
            $this->next_due_date = $nextPending ? $nextPending->due_date : null;
            
            if ($this->paid_amount >= $this->total_amount) {
                $this->status = 'completed';
            }
            
            $this->save();
        }

        return $this;
    }
}