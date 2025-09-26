<?php
// app/Services/InvoiceService.php

namespace App\Services;

use App\Models\{Invoice, BillingItem, Student};
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceGenerated;

class InvoiceService
{
    /**
     * Generate invoice for student
     */
    public function generateForStudent($studentId, $termId)
    {
        // Get unpaid billing items
        $items = BillingItem::where('student_id', $studentId)
                           ->where('term_id', $termId)
                           ->where('balance', '>', 0)
                           ->where('status', '!=', 'cancelled')
                           ->get();

        if ($items->isEmpty()) {
            return null;
        }

        // Calculate totals
        $totalAmount = $items->sum('balance');
        
        // Create invoice
        $invoice = Invoice::create([
            'student_id' => $studentId,
            'term_id' => $termId,
            'invoice_date' => now(),
            'due_date' => $items->min('due_date') ?? now()->addDays(30),
            'total_amount' => $totalAmount,
            'balance' => $totalAmount,
            'status' => 'draft'
        ]);

        // Generate line items
        $invoice->generateLineItems();

        return $invoice;
    }

    /**
     * Send invoice to student
     */
    public function sendInvoice(Invoice $invoice)
    {
        $student = Student::findOrFail($invoice->student_id);
        
        // Send email
        Mail::to($student->email)->send(new InvoiceGenerated($invoice));
        
        // Update invoice status
        $invoice->markAsSent();

        return true;
    }

    /**
     * Batch generate invoices
     */
    public function batchGenerate($termId)
    {
        $students = DB::table('enrollments')
                      ->select('student_id')
                      ->where('term_id', $termId)
                      ->where('enrollment_status', 'enrolled')
                      ->distinct()
                      ->pluck('student_id');

        $generated = 0;
        $errors = [];

        foreach ($students as $studentId) {
            try {
                $invoice = $this->generateForStudent($studentId, $termId);
                if ($invoice) {
                    $this->sendInvoice($invoice);
                    $generated++;
                }
            } catch (\Exception $e) {
                $errors[] = "Student {$studentId}: " . $e->getMessage();
            }
        }

        return [
            'generated' => $generated,
            'errors' => $errors
        ];
    }
}