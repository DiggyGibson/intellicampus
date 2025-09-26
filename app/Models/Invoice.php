<?php
// app/Models/Invoice.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'student_id',
        'term_id',
        'invoice_date',
        'due_date',
        'total_amount',
        'paid_amount',
        'balance',
        'status',
        'line_items',
        'notes',
        'sent_at',
        'viewed_at',
        'paid_at'
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'line_items' => 'array',
        'sent_at' => 'datetime',
        'viewed_at' => 'datetime',
        'paid_at' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = self::generateInvoiceNumber();
            }
        });
    }

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function term()
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    // Methods
    public static function generateInvoiceNumber()
    {
        $prefix = 'INV';
        $year = date('Y');
        $lastInvoice = self::whereYear('created_at', $year)
                           ->orderBy('id', 'desc')
                           ->first();
        
        $sequence = $lastInvoice ? intval(substr($lastInvoice->invoice_number, -6)) + 1 : 1;
        
        return sprintf('%s-%s-%06d', $prefix, $year, $sequence);
    }

    public function updateStatus()
    {
        if ($this->balance <= 0) {
            $this->status = 'paid';
            $this->paid_at = now();
        } elseif ($this->paid_amount > 0) {
            $this->status = 'partial';
        } elseif ($this->due_date < now()) {
            $this->status = 'overdue';
        }
        
        $this->save();
        return $this->status;
    }

    public function markAsSent()
    {
        $this->sent_at = now();
        $this->status = 'sent';
        $this->save();
    }

    public function markAsViewed()
    {
        if (!$this->viewed_at) {
            $this->viewed_at = now();
            if ($this->status === 'sent') {
                $this->status = 'viewed';
            }
            $this->save();
        }
    }

    public function generateLineItems()
    {
        $items = BillingItem::where('student_id', $this->student_id)
                            ->where('term_id', $this->term_id)
                            ->where('status', '!=', 'cancelled')
                            ->get();

        $lineItems = [];
        $total = 0;

        foreach ($items as $item) {
            $lineItems[] = [
                'billing_item_id' => $item->id,
                'description' => $item->description,
                'amount' => $item->amount,
                'balance' => $item->balance,
                'due_date' => $item->due_date->format('Y-m-d')
            ];
            $total += $item->balance;
        }

        $this->line_items = $lineItems;
        $this->total_amount = $total;
        $this->balance = $total - $this->paid_amount;
        $this->save();

        return $this;
    }
}