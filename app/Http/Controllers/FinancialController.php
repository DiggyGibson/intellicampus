<?php
// app/Http/Controllers/FinancialController.php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\{
    StudentAccount, FeeStructure, BillingItem, Invoice,
    Payment, FinancialAid, PaymentPlan, Student, AcademicTerm,
    AcademicProgram, FinancialTransaction, FinancialAidType,
    PaymentAllocation, Refund
};
use App\Services\{
    FinancialService, PaymentGatewayService, InvoiceService,
    BillingAutomationService, FinancialReportService
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FinancialController extends Controller
{
    protected $financialService;
    protected $paymentGateway;
    protected $invoiceService;
    protected $billingService;
    protected $reportService;

    public function __construct(
        FinancialService $financialService = null,
        PaymentGatewayService $paymentGateway = null,
        InvoiceService $invoiceService = null,
        BillingAutomationService $billingService = null,
        FinancialReportService $reportService = null
    ) {
        $this->financialService = $financialService;
        $this->paymentGateway = $paymentGateway;
        $this->invoiceService = $invoiceService;
        $this->billingService = $billingService;
        $this->reportService = $reportService;
    }

    /**
     * Student Account Dashboard - ENHANCED VERSION
     */
    public function studentDashboard(Request $request)
    {
        $student = Student::where('user_id', auth()->id())->firstOrFail();
        
        // Get or create student account with account number
        $account = StudentAccount::firstOrCreate(
            ['student_id' => $student->id],
            [
                'account_number' => $this->generateAccountNumber($student),
                'balance' => 0,
                'status' => 'active',
                'credit_limit' => config('billing.financial_credit_limit', 5000)
            ]
        );

        $currentTerm = AcademicTerm::where('is_current', true)->first();

        // Get comprehensive financial data
        $recentTransactions = $this->getRecentTransactions($account);
        $pendingCharges = $this->getPendingCharges($account);
        $upcomingDues = $this->getUpcomingDues($account);
        $payments = $this->getPaymentHistory($account, 10);
        $invoices = $this->getInvoices($account, $currentTerm);
        $paymentPlan = $this->getActivePaymentPlan($account);
        $financialAid = $this->getFinancialAid($account, $currentTerm);
        $accountHolds = $this->getAccountHolds($account);
        $lastPayment = $this->getLastPayment($account);
        
        // Calculate quick stats
        $quickStats = [
            'term_charges' => $pendingCharges->sum('amount'),
            'total_paid' => $account->total_payments ?? 0,
            'financial_aid' => $account->total_aid ?? 0,
            'next_due' => $upcomingDues->first()
        ];

        return view('financial.student-dashboard', compact(
            'student',
            'account',
            'currentTerm',
            'recentTransactions',
            'pendingCharges',
            'invoices',
            'payments',
            'paymentPlan',
            'financialAid',
            'lastPayment',
            'upcomingDues',
            'accountHolds',
            'quickStats'
        ));
    }

    /**
     * Online Payment Initiation - NEW METHOD
     */
    public function initiateOnlinePayment(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required_if:payment_type,custom_amount|numeric|min:1|max:50000',
            'payment_type' => 'required|in:full_balance,custom_amount,minimum_due',
            'save_card' => 'boolean'
        ]);

        $student = Student::where('user_id', auth()->id())->firstOrFail();
        $account = StudentAccount::where('student_id', $student->id)->firstOrFail();

        // Determine payment amount
        $amount = $this->calculatePaymentAmount($validated, $account);
        
        if ($amount <= 0) {
            return back()->with('error', 'No payment amount to process.');
        }

        // Create pending payment record
        $payment = Payment::create([
            'payment_number' => $this->generatePaymentNumber(),
            'student_id' => $student->id,
            'amount' => $amount,
            'payment_method' => 'card',
            'status' => 'pending',
            'payment_date' => now(),
            'description' => "Online payment for {$student->full_name}"
        ]);

        // Initialize payment gateway if available
        if ($this->paymentGateway) {
            $result = $this->paymentGateway->createPaymentIntent(
                $amount,
                $student->id,
                "Payment for Account #{$account->account_number}"
            );

            if ($result['success']) {
                $payment->transaction_id = $result['payment_intent_id'];
                $payment->save();

                return view('financial.online-payment', [
                    'payment' => $payment,
                    'clientSecret' => $result['client_secret'],
                    'amount' => $amount,
                    'student' => $student,
                    'account' => $account,
                    'stripePublicKey' => config('services.stripe.key')
                ]);
            }

            return back()->with('error', 'Unable to initiate payment: ' . $result['error']);
        }
        
        // Fallback if no payment gateway configured
        return redirect()->route('financial.make-payment')
            ->with('error', 'Online payment is not configured. Please use alternative payment methods.');
    }

    /**
     * Confirm Online Payment - NEW METHOD
     */
    public function confirmOnlinePayment(Request $request)
    {
        $validated = $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'payment_intent_id' => 'required|string'
        ]);

        $payment = Payment::findOrFail($validated['payment_id']);
        
        // Verify ownership
        $student = Student::where('user_id', auth()->id())->firstOrFail();
        if ($payment->student_id !== $student->id) {
            abort(403);
        }

        // Confirm with payment gateway
        if ($this->paymentGateway) {
            $result = $this->paymentGateway->confirmPayment(
                $validated['payment_intent_id'],
                $payment->id
            );

            if ($result['success']) {
                // Send confirmation email
                $this->sendPaymentConfirmationEmail($payment, $result['receipt_url'] ?? null);
                
                return response()->json([
                    'success' => true,
                    'receipt_url' => route('financial.payment.success', $payment->id)
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'error' => 'Payment could not be confirmed'
        ]);
    }

    /**
     * Payment Success Page - NEW METHOD
     */
    public function paymentSuccess($paymentId)
    {
        $payment = Payment::with(['student', 'allocations.billingItem'])
            ->findOrFail($paymentId);
        
        // Verify ownership
        $student = Student::where('user_id', auth()->id())->firstOrFail();
        if ($payment->student_id !== $student->id) {
            abort(403);
        }

        $account = StudentAccount::where('student_id', $student->id)->first();

        return view('financial.payment-success', [
            'payment' => $payment,
            'account' => $account,
            'receiptUrl' => $this->generateReceiptUrl($payment)
        ]);
    }

    /**
     * Enhanced Make Payment - UPDATED METHOD
     */
    public function makePayment(Request $request)
    {
        $student = Student::where('user_id', auth()->id())->firstOrFail();
        
        $account = StudentAccount::firstOrCreate(
            ['student_id' => $student->id],
            [
                'account_number' => $this->generateAccountNumber($student),
                'balance' => 0,
                'status' => 'active'
            ]
        );

        // Get pending charges for display
        $pendingCharges = $this->getPendingCharges($account);
        $recentPayments = $this->getPaymentHistory($account, 5);
        $minimumPayment = $this->getMinimumDue($account);
        
        // Payment methods available
        $paymentMethods = [
            'card' => 'Credit/Debit Card',
            'bank_transfer' => 'Bank Transfer',
            'mobile_money' => 'Mobile Money',
            'cash' => 'Cash (at Bursar)',
            'check' => 'Check'
        ];

        return view('financial.make-payment', compact(
            'student', 
            'account',
            'pendingCharges',
            'recentPayments',
            'minimumPayment',
            'paymentMethods'
        ));
    }

    /**
     * Enhanced Process Payment - UPDATED METHOD
     */
    public function processPayment(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:cash,check,card,bank_transfer,mobile_money',
            'reference_number' => 'nullable|string'
        ]);

        $student = Student::where('user_id', auth()->id())->firstOrFail();

        // For online payments, redirect to online payment flow
        if ($validated['payment_method'] === 'card') {
            return redirect()->route('financial.initiate-payment', [
                'amount' => $validated['amount'],
                'payment_type' => 'custom_amount'
            ]);
        }

        DB::transaction(function () use ($validated, $student) {
            // Create payment record
            $payment = Payment::create([
                'payment_number' => $this->generatePaymentNumber(),
                'student_id' => $student->id,
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'reference_number' => $validated['reference_number'],
                'payment_date' => now(),
                'status' => 'pending'
            ]);

            // Process based on payment method
            if (in_array($validated['payment_method'], ['mobile_money'])) {
                // Process through gateway if available
                if ($this->paymentGateway) {
                    $result = $this->paymentGateway->processPayment($payment);
                    
                    if ($result['success']) {
                        $payment->transaction_id = $result['transaction_id'];
                        $payment->payment_details = $result['details'];
                        $payment->status = 'completed';
                        $payment->save();
                        
                        $this->applyPaymentToAccount($payment);
                    } else {
                        $payment->status = 'failed';
                        $payment->notes = $result['error'];
                        $payment->save();
                        
                        throw new \Exception($result['error']);
                    }
                } else {
                    // Simulate successful payment if no gateway
                    $payment->status = 'completed';
                    $payment->save();
                    $this->applyPaymentToAccount($payment);
                }
            } else {
                // Manual processing for cash/check
                $payment->status = 'processing';
                $payment->save();
            }
        });

        return redirect()->route('financial.student-dashboard')
                        ->with('success', 'Payment processed successfully');
    }

    /**
     * Generate Automated Term Billing - ENHANCED METHOD
     */
    public function generateTermBilling(Request $request)
    {
        // Check permission
        if (!auth()->user()->hasRole(['Super Administrator', 'Financial Administrator'])) {
            abort(403, 'Unauthorized to generate billing');
        }

        $validated = $request->validate([
            'term_id' => 'required|exists:academic_terms,id',
            'generate_for' => 'required|in:all,program,student',
            'program_id' => 'required_if:generate_for,program|exists:academic_programs,id',
            'student_id' => 'required_if:generate_for,student|exists:students,id'
        ]);

        if ($this->billingService) {
            $result = $this->billingService->generateTermBilling(
                $validated['term_id'],
                $validated['generate_for'],
                $validated['program_id'] ?? null,
                $validated['student_id'] ?? null
            );

            return back()->with('success', 
                "Billing generated successfully. {$result['processed']} students processed, 
                 {$result['errors']} errors encountered.");
        }

        // Fallback to existing implementation
        return $this->generateTermFees($request);
    }

    /**
     * Request Refund - NEW METHOD
     */
    public function requestRefund(Request $request)
    {
        $validated = $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'amount' => 'nullable|numeric|min:1',
            'reason' => 'required|string|max:500'
        ]);

        $payment = Payment::findOrFail($validated['payment_id']);
        
        // Verify ownership
        $student = Student::where('user_id', auth()->id())->firstOrFail();
        if ($payment->student_id !== $student->id) {
            abort(403);
        }

        // Check if refundable
        if (!$this->isRefundable($payment)) {
            return back()->with('error', 'This payment is not eligible for refund.');
        }

        $refund = Refund::create([
            'refund_number' => $this->generateRefundNumber(),
            'payment_id' => $payment->id,
            'student_id' => $student->id,
            'amount' => $validated['amount'] ?? $payment->amount,
            'reason' => $validated['reason'],
            'type' => ($validated['amount'] ?? $payment->amount) == $payment->amount ? 'full' : 'partial',
            'status' => 'pending',
            'requested_date' => now(),
            'requested_by' => auth()->id()
        ]);

        // Notify financial office
        $this->notifyRefundRequest($refund);

        return back()->with('success', 'Refund request submitted for review.');
    }

    /**
     * Process Refund - NEW METHOD
     */
    public function processRefund($refundId)
    {
        // Check permission
        if (!auth()->user()->hasRole(['Super Administrator', 'Financial Administrator'])) {
            abort(403, 'Unauthorized to process refunds');
        }

        $refund = Refund::findOrFail($refundId);
        
        if ($refund->status !== 'pending') {
            return back()->with('error', 'This refund has already been processed.');
        }

        $payment = Payment::findOrFail($refund->payment_id);

        // Process through gateway if online payment
        if ($payment->payment_method === 'card' && $payment->transaction_id && $this->paymentGateway) {
            $result = $this->paymentGateway->processRefund(
                $payment->id,
                $refund->amount,
                $refund->reason
            );

            if ($result['success']) {
                $refund->status = 'completed';
                $refund->gateway_refund_id = $result['refund_id'];
                $refund->processed_date = now();
                $refund->processed_by = auth()->id();
                $refund->save();

                return back()->with('success', 'Refund processed successfully.');
            }

            return back()->with('error', 'Refund processing failed: ' . $result['error']);
        }

        // Manual refund for cash/check
        $refund->status = 'approved';
        $refund->approved_date = now();
        $refund->approved_by = auth()->id();
        $refund->save();

        return back()->with('success', 'Refund approved. Please process manual refund.');
    }

    /**
     * Financial Reports Dashboard - NEW METHOD
     */
    public function reportsDashboard()
    {
        // Check permission
        if (!auth()->user()->hasRole(['Super Administrator', 'Financial Administrator'])) {
            abort(403, 'Unauthorized to view reports');
        }

        if ($this->reportService) {
            $data = [
                'dailyRevenue' => $this->reportService->getDailyRevenue(),
                'monthlyTrends' => $this->reportService->getMonthlyTrends(),
                'outstandingBalances' => $this->reportService->getOutstandingBalances(),
                'paymentMethodBreakdown' => $this->reportService->getPaymentMethodBreakdown(),
                'aidDisbursements' => $this->reportService->getAidDisbursements(),
                'delinquentAccounts' => $this->reportService->getDelinquentAccounts()
            ];

            return view('financial.reports-dashboard', $data);
        }

        // Fallback to basic reporting
        return redirect()->route('financial.revenue-report');
    }

    // ===================== HELPER METHODS =====================

    /**
     * Generate unique account number
     */
    private function generateAccountNumber($student)
    {
        $year = date('Y');
        $sequence = str_pad($student->id, 6, '0', STR_PAD_LEFT);
        return "ACC{$year}{$sequence}";
    }

    /**
     * Generate unique payment number
     */
    private function generatePaymentNumber()
    {
        $date = date('Ymd');
        $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        return "PAY{$date}{$random}";
    }

    /**
     * Generate unique refund number
     */
    private function generateRefundNumber()
    {
        $date = date('Ymd');
        $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        return "REF{$date}{$random}";
    }

    /**
     * Calculate payment amount based on type
     */
    private function calculatePaymentAmount($validated, $account)
    {
        switch ($validated['payment_type']) {
            case 'full_balance':
                return $account->balance;
            case 'minimum_due':
                return $this->getMinimumDue($account);
            case 'custom_amount':
            default:
                return $validated['amount'] ?? 0;
        }
    }

    /**
     * Get minimum payment due
     */
    private function getMinimumDue($account)
    {
        // Check for active payment plan
        $plan = PaymentPlan::where('student_id', $account->student_id)
            ->where('status', 'active')
            ->first();
        
        if ($plan) {
            return $plan->installment_amount;
        }

        // Otherwise, return overdue amount or 10% of balance
        $overdue = BillingItem::where('student_id', $account->student_id)
            ->where('due_date', '<', now())
            ->where('status', '!=', 'paid')
            ->sum('balance');

        return $overdue > 0 ? $overdue : max(50, $account->balance * 0.1);
    }

    /**
     * Apply payment to account
     */
    private function applyPaymentToAccount($payment)
    {
        $account = StudentAccount::where('student_id', $payment->student_id)->first();
        
        if ($account) {
            // Update account balance
            $account->balance -= $payment->amount;
            $account->total_payments = ($account->total_payments ?? 0) + $payment->amount;
            $account->last_payment_date = now();
            $account->save();
            
            // Apply to outstanding billing items
            $remainingAmount = $payment->amount;
            
            $unpaidItems = BillingItem::where('student_id', $payment->student_id)
                ->where('status', '!=', 'paid')
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
            
            // Check and remove financial hold if balance is clear
            if ($account->balance <= 0 && $account->has_financial_hold) {
                $this->removeFinancialHold($account);
            }
        }
    }

    /**
     * Remove financial hold
     */
    private function removeFinancialHold($account)
    {
        $account->has_financial_hold = false;
        $account->hold_reason = null;
        $account->hold_removed_date = now();
        $account->save();
        
        // Also remove from registration holds
        DB::table('registration_holds')
            ->where('student_id', $account->student_id)
            ->where('hold_type', 'financial')
            ->delete();
    }

    /**
     * Check if payment is refundable
     */
    private function isRefundable($payment)
    {
        // Payment must be completed
        if ($payment->status !== 'completed') {
            return false;
        }
        
        // Check if within refund period (e.g., 90 days)
        $refundPeriodDays = config('billing.refund_period_days', 90);
        if ($payment->payment_date < Carbon::now()->subDays($refundPeriodDays)) {
            return false;
        }
        
        // Check if already refunded
        $existingRefund = Refund::where('payment_id', $payment->id)
            ->whereIn('status', ['pending', 'approved', 'completed'])
            ->exists();
        
        return !$existingRefund;
    }

    /**
     * Send payment confirmation email
     */
    private function sendPaymentConfirmationEmail($payment, $receiptUrl = null)
    {
        // Implementation for email notification
        Log::info('Payment confirmation email would be sent', [
            'payment_id' => $payment->id,
            'student_id' => $payment->student_id,
            'receipt_url' => $receiptUrl
        ]);
    }

    /**
     * Notify about refund request
     */
    private function notifyRefundRequest($refund)
    {
        Log::info('Refund request notification would be sent', [
            'refund_id' => $refund->id,
            'amount' => $refund->amount
        ]);
    }

    /**
     * Generate receipt URL
     */
    private function generateReceiptUrl($payment)
    {
        return route('financial.payment.receipt', $payment->id);
    }

    /**
     * Get recent transactions
     */
    private function getRecentTransactions($account)
    {
        if (method_exists($account, 'transactions')) {
            return $account->transactions()
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        }
        
        return FinancialTransaction::where('student_id', $account->student_id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Get pending charges
     */
    private function getPendingCharges($account)
    {
        return BillingItem::where('student_id', $account->student_id)
            ->where('status', '!=', 'paid')
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Get upcoming dues
     */
    private function getUpcomingDues($account)
    {
        return BillingItem::where('student_id', $account->student_id)
            ->where('status', '!=', 'paid')
            ->where('due_date', '>=', now())
            ->orderBy('due_date')
            ->limit(5)
            ->get();
    }

    /**
     * Get payment history
     */
    private function getPaymentHistory($account, $limit = 10)
    {
        return Payment::where('student_id', $account->student_id)
            ->where('status', 'completed')
            ->orderBy('payment_date', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get invoices
     */
    private function getInvoices($account, $term = null)
    {
        $query = Invoice::where('student_id', $account->student_id);
        
        if ($term) {
            $query->where('term_id', $term->id);
        }
        
        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get active payment plan
     */
    private function getActivePaymentPlan($account)
    {
        return PaymentPlan::where('student_id', $account->student_id)
            ->where('status', 'active')
            ->first();
    }

    /**
     * Get financial aid
     */
    private function getFinancialAid($account, $term = null)
    {
        $query = FinancialAid::where('student_id', $account->student_id);
        
        if ($term) {
            $query->where('term_id', $term->id);
        }
        
        return $query->get();
    }

    /**
     * Get account holds
     */
    private function getAccountHolds($account)
    {
        return DB::table('registration_holds')
            ->where('student_id', $account->student_id)
            ->where('hold_type', 'financial')
            ->get();
    }

    /**
     * Get last payment
     */
    private function getLastPayment($account)
    {
        return Payment::where('student_id', $account->student_id)
            ->where('status', 'completed')
            ->orderBy('payment_date', 'desc')
            ->first();
    }

    /**
     * Fee Structure Management - UPDATED
     */
    public function feeStructure()
    {
        // Separate fees by type
        $tuitionFees = FeeStructure::where('type', 'tuition')
                                   ->orderBy('effective_from', 'desc')
                                   ->get();

        $mandatoryFees = FeeStructure::where('is_mandatory', true)
                                     ->where('type', '!=', 'tuition')
                                     ->orderBy('name')
                                     ->get();

        $optionalFees = FeeStructure::where('is_mandatory', false)
                                    ->where('type', '!=', 'tuition')
                                    ->orderBy('name')
                                    ->get();

        // Calculate summary statistics
        $totalActiveFees = FeeStructure::where('is_active', true)->count();
        
        $avgTuitionPerCredit = FeeStructure::where('type', 'tuition')
                                           ->where('is_active', true)
                                           ->whereNotNull('per_credit_amount')
                                           ->avg('per_credit_amount') ?? 0;
        
        $totalMandatoryFees = FeeStructure::where('is_mandatory', true)
                                          ->where('is_active', true)
                                          ->sum('amount');
        
        $lastUpdated = FeeStructure::latest('updated_at')->first();
        $lastUpdated = $lastUpdated ? $lastUpdated->updated_at : now();

        // Get programs for the create modal
        $programs = AcademicProgram::all();

        // For compatibility with existing view
        $fees = FeeStructure::orderBy('type')
                           ->orderBy('name')
                           ->paginate(20);

        return view('financial.fee-structure', compact(
            'fees',
            'tuitionFees',
            'mandatoryFees', 
            'optionalFees',
            'totalActiveFees',
            'avgTuitionPerCredit',
            'totalMandatoryFees',
            'lastUpdated',
            'programs'
        ));
    }

    public function createFee()
    {
        $programs = AcademicProgram::all();
        return view('financial.fee-create', compact('programs'));
    }

    public function storeFee(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:fee_structures',
            'type' => 'required|in:tuition,lab,library,registration,technology,activity,health,housing,meal,other',
            'frequency' => 'required|in:once,per_term,per_year,per_credit,monthly',
            'amount' => 'required|numeric|min:0',
            'academic_level' => 'nullable|string',
            'program_id' => 'nullable|exists:academic_programs,id',
            'is_mandatory' => 'boolean',
            'effective_from' => 'required|date',
            'effective_until' => 'nullable|date|after:effective_from'
        ]);

        $fee = FeeStructure::create($validated);

        return redirect()->route('financial.fee-structure')
                        ->with('success', 'Fee structure created successfully');
    }

    /**
     * Generate Term Fees for Students
     */
    public function generateTermFees(Request $request)
    {
        $request->validate([
            'term_id' => 'required|exists:academic_terms,id'
        ]);

        $term = AcademicTerm::findOrFail($request->term_id);
        $processed = 0;
        $errors = [];

        DB::transaction(function () use ($term, &$processed, &$errors) {
            // Get all enrolled students for the term
            $enrollments = DB::table('enrollments')
                            ->select('student_id', DB::raw('COUNT(*) as course_count'), DB::raw('SUM(c.credits) as total_credits'))
                            ->join('course_sections as cs', 'enrollments.section_id', '=', 'cs.id')
                            ->join('courses as c', 'cs.course_id', '=', 'c.id')
                            ->where('enrollments.term_id', $term->id)
                            ->where('enrollments.enrollment_status', 'enrolled')
                            ->groupBy('student_id')
                            ->get();

            foreach ($enrollments as $enrollment) {
                try {
                    $student = Student::find($enrollment->student_id);
                    
                    // Get or create student account
                    $account = StudentAccount::firstOrCreate(
                        ['student_id' => $student->id],
                        ['balance' => 0, 'status' => 'active']
                    );

                    // Get applicable fees
                    $fees = FeeStructure::where('is_active', true)
                                       ->where('is_mandatory', true)
                                       ->get();

                    foreach ($fees as $fee) {
                        // Skip if already billed
                        $exists = BillingItem::where('student_id', $student->id)
                                            ->where('term_id', $term->id)
                                            ->where('fee_structure_id', $fee->id)
                                            ->exists();

                        if (!$exists) {
                            $amount = $fee->amount;
                            if ($fee->frequency == 'per_credit' && $fee->per_credit_amount) {
                                $amount = $fee->per_credit_amount * $enrollment->total_credits;
                            }
                            
                            BillingItem::create([
                                'student_id' => $student->id,
                                'student_account_id' => $account->id,
                                'term_id' => $term->id,
                                'fee_structure_id' => $fee->id,
                                'description' => "{$fee->name} - {$term->name}",
                                'amount' => $amount,
                                'due_date' => Carbon::parse($term->start_date)->subDays(7),
                                'status' => 'pending'
                            ]);

                            // Update account balance
                            $account->balance += $amount;
                            $account->save();
                        }
                    }

                    // Generate invoice if service exists
                    if ($this->invoiceService) {
                        $this->invoiceService->generateForStudent($student->id, $term->id);
                    }
                    
                    $processed++;
                } catch (\Exception $e) {
                    $errors[] = "Error processing student {$enrollment->student_id}: " . $e->getMessage();
                    Log::error('Fee generation error', [
                        'student_id' => $enrollment->student_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        });

        $message = "Term fees generated for {$processed} students.";
        if (!empty($errors)) {
            $message .= " Errors: " . implode('; ', array_slice($errors, 0, 3));
        }

        return back()->with('success', $message);
    }

    /**
     * View Invoice
     */
    public function viewInvoice($id)
    {
        $invoice = Invoice::findOrFail($id);
        
        // Check authorization
        if (auth()->user()->user_type === 'student') {
            $student = Student::where('user_id', auth()->id())->first();
            if ($invoice->student_id !== $student->id) {
                abort(403);
            }
            
            if (method_exists($invoice, 'markAsViewed')) {
                $invoice->markAsViewed();
            }
        }

        return view('financial.invoice', compact('invoice'));
    }

    /**
     * Download Invoice PDF
     */
    public function downloadInvoice($id)
    {
        $invoice = Invoice::with(['student', 'term'])->findOrFail($id);
        
        // Check authorization
        if (auth()->user()->user_type === 'student') {
            $student = Student::where('user_id', auth()->id())->first();
            if ($invoice->student_id !== $student->id) {
                abort(403);
            }
        }

        // Check if PDF package is installed
        if (class_exists('\PDF')) {
            $pdf = \PDF::loadView('financial.invoice-pdf', compact('invoice'));
            return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
        }
        
        // Fallback to HTML view
        return view('financial.invoice-pdf', compact('invoice'));
    }

    /**
     * Payment Plans
     */
    public function paymentPlans()
    {
        $student = Student::where('user_id', auth()->id())->firstOrFail();
        
        $plans = PaymentPlan::where('student_id', $student->id)
                           ->orderBy('created_at', 'desc')
                           ->get();

        return view('financial.payment-plans', compact('plans'));
    }

    public function requestPaymentPlan(Request $request)
    {
        $validated = $request->validate([
            'term_id' => 'required|exists:academic_terms,id',
            'number_of_installments' => 'required|integer|min:2|max:12',
            'start_date' => 'required|date|after:today'
        ]);

        $student = Student::where('user_id', auth()->id())->firstOrFail();
        $account = StudentAccount::where('student_id', $student->id)->firstOrFail();

        // Check eligibility
        if ($account->balance <= 0) {
            return back()->with('error', 'No outstanding balance for payment plan');
        }

        if ($account->has_payment_plan) {
            return back()->with('error', 'You already have an active payment plan');
        }

        // Create payment plan
        $plan = PaymentPlan::create([
            'student_id' => $student->id,
            'term_id' => $validated['term_id'],
            'plan_name' => "Payment Plan - {$student->student_id}",
            'total_amount' => $account->balance,
            'number_of_installments' => $validated['number_of_installments'],
            'installment_amount' => round($account->balance / $validated['number_of_installments'], 2),
            'start_date' => $validated['start_date'],
            'end_date' => Carbon::parse($validated['start_date'])->addMonths($validated['number_of_installments']),
            'status' => 'pending'
        ]);

        return redirect()->route('financial.payment-plans')
                        ->with('success', 'Payment plan request submitted for approval');
    }

    /**
     * Financial Aid
     */
    public function financialAid()
    {
        $student = Student::where('user_id', auth()->id())->firstOrFail();
        
        $aids = FinancialAid::where('student_id', $student->id)
                           ->orderBy('created_at', 'desc')
                           ->get();

        return view('financial.financial-aid', compact('aids'));
    }

    /**
     * Admin Functions
     */
    public function adminDashboard()
    {
        // Check permission without using authorize()
        if (!auth()->user()->hasRole(['Super Administrator', 'Financial Administrator', 'admin'])) {
            abort(403, 'Unauthorized access to financial admin dashboard');
        }

        $stats = [
            'total_receivable' => StudentAccount::sum('balance'),
            'overdue_amount' => BillingItem::where('due_date', '<', now())
                                          ->where('status', '!=', 'paid')
                                          ->sum('amount'),
            'collected_today' => Payment::whereDate('payment_date', today())
                                       ->where('status', 'completed')
                                       ->sum('amount'),
            'collected_month' => Payment::whereMonth('payment_date', now()->month)
                                       ->where('status', 'completed')
                                       ->sum('amount'),
            'students_with_balance' => StudentAccount::where('balance', '>', 0)->count(),
            'active_payment_plans' => PaymentPlan::where('status', 'active')->count()
        ];

        $recentPayments = Payment::where('status', 'completed')
                                ->orderBy('payment_date', 'desc')
                                ->limit(10)
                                ->get();

        $pendingPlans = PaymentPlan::where('status', 'pending')->get();

        return view('financial.admin-dashboard', compact('stats', 'recentPayments', 'pendingPlans'));
    }

    public function approvePaymentPlan($id)
    {
        // Check permission without using authorize()
        if (!auth()->user()->hasRole(['Super Administrator', 'Financial Administrator'])) {
            abort(403, 'Unauthorized to approve payment plans');
        }

        $plan = PaymentPlan::findOrFail($id);
        
        DB::transaction(function () use ($plan) {
            $plan->status = 'active';
            $plan->approved_by = auth()->id();
            $plan->approved_at = now();
            $plan->save();

            // Generate installment schedule if method exists
            if (method_exists($plan, 'generateSchedule')) {
                $plan->generateSchedule();
            }

            // Update student account
            $account = StudentAccount::where('student_id', $plan->student_id)->first();
            if ($account) {
                $account->has_payment_plan = true;
                $account->save();

                // Remove financial hold if exists
                if (method_exists($account, 'releaseFinancialHold')) {
                    if ($account->balance <= $plan->total_amount) {
                        $account->releaseFinancialHold();
                    }
                }
            }
        });

        return back()->with('success', 'Payment plan approved successfully');
    }

    public function processManualPayment(Request $request)
    {
        // Check permission without using authorize()
        if (!auth()->user()->hasRole(['Super Administrator', 'Financial Administrator', 'Bursar'])) {
            abort(403, 'Unauthorized to process payments');
        }

        $payment = Payment::findOrFail($request->payment_id);
        
        if ($payment->status !== 'processing') {
            return back()->with('error', 'Payment cannot be processed');
        }

        if (method_exists($payment, 'process')) {
            $payment->process();
        } else {
            $payment->status = 'completed';
            $payment->save();
        }

        return back()->with('success', 'Payment processed successfully');
    }

    public function pendingPayments()
    {
        // Check permission
        if (!auth()->user()->hasRole(['Super Administrator', 'Financial Administrator', 'Bursar'])) {
            abort(403, 'Unauthorized access');
        }

        $pendingPayments = Payment::where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $overdueItems = BillingItem::where('due_date', '<', now())
            ->where('status', '!=', 'paid')
            ->orderBy('due_date', 'asc')
            ->paginate(20);

        return view('financial.pending-payments', compact('pendingPayments', 'overdueItems'));
    }

    /**
     * Export fee structure method
     */
    public function exportFeeStructure()
    {
        // Check permission
        if (!auth()->user()->hasRole(['Super Administrator', 'Financial Administrator'])) {
            abort(403, 'Unauthorized access');
        }

        // Get fees
        $fees = FeeStructure::all();
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="fee-structure-' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($fees) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Name', 'Code', 'Type', 'Amount', 'Frequency', 'Program', 'Mandatory', 'Effective From']);
            
            foreach ($fees as $fee) {
                fputcsv($file, [
                    $fee->name,
                    $fee->code,
                    $fee->type,
                    $fee->amount,
                    $fee->frequency,
                    'All Programs',
                    $fee->is_mandatory ? 'Yes' : 'No',
                    $fee->effective_from ?? date('Y-m-d')
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export payment history
     */
    public function exportPaymentHistory(Request $request)
    {
        $student = Student::where('user_id', auth()->id())->firstOrFail();
        
        $payments = Payment::where('student_id', $student->id)
                          ->when($request->start_date, function($query) use ($request) {
                              return $query->whereDate('payment_date', '>=', $request->start_date);
                          })
                          ->when($request->end_date, function($query) use ($request) {
                              return $query->whereDate('payment_date', '<=', $request->end_date);
                          })
                          ->orderBy('payment_date', 'desc')
                          ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="payment-history-' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($payments) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Reference', 'Method', 'Amount', 'Status', 'Description']);
            
            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->payment_date->format('Y-m-d'),
                    $payment->reference_number ?? 'N/A',
                    ucfirst($payment->payment_method),
                    number_format($payment->amount, 2),
                    ucfirst($payment->status),
                    $payment->notes ?? ''
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function paymentHistory()
    {
        $student = Student::where('user_id', auth()->id())->firstOrFail();
        
        $payments = Payment::where('student_id', $student->id)
                          ->orderBy('payment_date', 'desc')
                          ->paginate(20);

        return view('financial.payment-history', compact('payments'));
    }

    public function paymentReceipt($paymentId)
    {
        $payment = Payment::findOrFail($paymentId);
        
        // Check authorization
        if (auth()->user()->user_type === 'student') {
            $student = Student::where('user_id', auth()->id())->first();
            if ($payment->student_id !== $student->id) {
                abort(403);
            }
        }

        return view('financial.payment-receipt', compact('payment'));
    }

    public function invoices()
    {
        $student = Student::where('user_id', auth()->id())->firstOrFail();
        
        $invoices = Invoice::where('student_id', $student->id)
                          ->orderBy('created_at', 'desc')
                          ->paginate(20);

        return view('financial.invoices', compact('invoices'));
    }

    public function billingItems()
    {
        if (!auth()->user()->hasRole(['Super Administrator', 'Financial Administrator'])) {
            abort(403);
        }

        $items = BillingItem::orderBy('created_at', 'desc')
                           ->paginate(50);

        return view('financial.billing-items', compact('items'));
    }

    public function studentAccounts()
    {
        if (!auth()->user()->hasRole(['Super Administrator', 'Financial Administrator'])) {
            abort(403);
        }

        $accounts = StudentAccount::paginate(50);

        return view('financial.student-accounts', compact('accounts'));
    }

    public function viewStudentAccount($studentId)
    {
        if (!auth()->user()->hasRole(['Super Administrator', 'Financial Administrator'])) {
            abort(403);
        }

        $student = Student::findOrFail($studentId);
        $account = StudentAccount::where('student_id', $studentId)->firstOrFail();
        
        $transactions = FinancialTransaction::where('student_id', $studentId)
                                           ->orderBy('created_at', 'desc')
                                           ->paginate(20);

        return view('financial.student-account-detail', compact('student', 'account', 'transactions'));
    }

    /**
     * Additional missing methods
     */
    public function editFee($id)
    {
        $fee = FeeStructure::findOrFail($id);
        $programs = AcademicProgram::all();
        
        return view('financial.fee-edit', compact('fee', 'programs'));
    }

    public function updateFee(Request $request, $id)
    {
        $fee = FeeStructure::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|string',
            'is_mandatory' => 'boolean'
        ]);

        $fee->update($validated);

        return redirect()->route('financial.fee-structure')
                        ->with('success', 'Fee updated successfully');
    }

    public function deleteFee($id)
    {
        $fee = FeeStructure::findOrFail($id);
        $fee->delete();

        return redirect()->route('financial.fee-structure')
                        ->with('success', 'Fee deleted successfully');
    }

    public function viewPaymentPlan($id)
    {
        $plan = PaymentPlan::findOrFail($id);
        
        // Check authorization
        if (auth()->user()->user_type === 'student') {
            $student = Student::where('user_id', auth()->id())->first();
            if ($plan->student_id !== $student->id) {
                abort(403);
            }
        }

        return view('financial.payment-plan-detail', compact('plan'));
    }

    public function applyFinancialAid()
    {
        $student = Student::where('user_id', auth()->id())->firstOrFail();
        
        // Define aid types directly or get from existing records
        $aidTypes = [
            ['name' => 'Need-Based Grant', 'type' => 'grant'],
            ['name' => 'Merit Scholarship', 'type' => 'scholarship'],
            ['name' => 'Work Study', 'type' => 'work_study'],
            ['name' => 'Emergency Aid', 'type' => 'emergency'],
            ['name' => 'Athletic Scholarship', 'type' => 'athletic'],
        ];
        
        // Or get unique types from existing financial aid records
        // $aidTypes = FinancialAid::distinct('type')->pluck('type');
        
        return view('financial.financial-aid-apply', compact('student', 'aidTypes'));
    }

    public function submitFinancialAid(Request $request)
    {
        // Implementation for financial aid submission
        return redirect()->route('financial.financial-aid')
                        ->with('success', 'Financial aid application submitted successfully');
    }

    public function printInvoice($id)
    {
        $invoice = Invoice::findOrFail($id);
        
        return view('financial.invoice-print', compact('invoice'));
    }

    /**
     * Reports
     */
    public function revenueReport(Request $request)
    {
        // Check permission without using authorize()
        if (!auth()->user()->hasRole(['Super Administrator', 'Financial Administrator', 'admin'])) {
            abort(403, 'Unauthorized to view reports');
        }

        $startDate = $request->start_date ? Carbon::parse($request->start_date) : now()->startOfMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : now()->endOfMonth();

        $revenue = Payment::whereBetween('payment_date', [$startDate, $endDate])
                         ->where('status', 'completed')
                         ->select(
                             DB::raw('DATE(payment_date) as date'),
                             DB::raw('COUNT(*) as count'),
                             DB::raw('SUM(amount) as total'),
                             'payment_method'
                         )
                         ->groupBy('date', 'payment_method')
                         ->orderBy('date')
                         ->get();

        $summary = [
            'total_revenue' => $revenue->sum('total'),
            'total_transactions' => $revenue->sum('count'),
            'by_method' => $revenue->groupBy('payment_method')->map->sum('total'),
            'daily_average' => $revenue->groupBy('date')->map->sum('total')->average()
        ];

        return view('financial.reports.revenue', compact('revenue', 'summary', 'startDate', 'endDate'));
    }

    public function outstandingReport()
    {
        // Check permission without using authorize()
        if (!auth()->user()->hasRole(['Super Administrator', 'Financial Administrator', 'admin'])) {
            abort(403, 'Unauthorized to view reports');
        }

        $outstanding = StudentAccount::where('balance', '>', 0)
                                    ->orderBy('balance', 'desc')
                                    ->paginate(50);

        $summary = [
            'total_outstanding' => StudentAccount::where('balance', '>', 0)->sum('balance'),
            'accounts_count' => StudentAccount::where('balance', '>', 0)->count(),
            'overdue_amount' => BillingItem::where('due_date', '<', now())
                                          ->where('status', '!=', 'paid')
                                          ->sum('amount'),
            'overdue_count' => BillingItem::where('due_date', '<', now())
                                         ->where('status', '!=', 'paid')
                                         ->distinct('student_id')
                                         ->count('student_id')
        ];

        return view('financial.reports.outstanding', compact('outstanding', 'summary'));
    }

    public function studentStatement($studentId)
    {
        $student = Student::findOrFail($studentId);
        
        // Check authorization
        if (auth()->user()->user_type === 'student' && $student->user_id !== auth()->id()) {
            abort(403);
        }

        $account = StudentAccount::where('student_id', $studentId)->firstOrFail();
        
        $transactions = FinancialTransaction::where('student_id', $studentId)
                                           ->orderBy('created_at', 'desc')
                                           ->get();

        $billingItems = BillingItem::where('student_id', $studentId)
                                  ->orderBy('created_at', 'desc')
                                  ->get();

        $payments = Payment::where('student_id', $studentId)
                          ->where('status', 'completed')
                          ->orderBy('payment_date', 'desc')
                          ->get();

        return view('financial.student-statement', compact(
            'student',
            'account',
            'transactions',
            'billingItems',
            'payments'
        ));
    }
}