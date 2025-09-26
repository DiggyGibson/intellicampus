<?php
// ========================================
// app/Models/PaymentAllocation.php
// ========================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'billing_item_id',
        'amount'
    ];

    protected $casts = [
        'amount' => 'decimal:2'
    ];

    // Relationships
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function billingItem()
    {
        return $this->belongsTo(BillingItem::class);
    }
}