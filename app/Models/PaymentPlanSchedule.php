<?php
// app/Models/PaymentPlanSchedule.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentPlanSchedule extends Model
{
    protected $fillable = [
        'payment_plan_id', 'installment_number', 'amount',
        'due_date', 'paid_amount', 'paid_date', 'status'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_date' => 'date'
    ];

    public function paymentPlan()
    {
        return $this->belongsTo(PaymentPlan::class);
    }

    public function isOverdue()
    {
        return $this->status === 'pending' && $this->due_date < now();
    }
}