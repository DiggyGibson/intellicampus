<!-- resources/views/financial/payment-success.blade.php -->
@extends('layouts.app')

@section('title', 'Payment Successful')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                    </div>
                    
                    <h2 class="mb-3">Payment Successful!</h2>
                    
                    <p class="text-muted mb-4">
                        Your payment of <strong>${{ number_format($payment->amount, 2) }}</strong> 
                        has been processed successfully.
                    </p>
                    
                    <div class="bg-light rounded p-3 mb-4">
                        <div class="row text-start">
                            <div class="col-6">
                                <small class="text-muted">Payment ID:</small><br>
                                <strong>{{ $payment->payment_number }}</strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Date:</small><br>
                                <strong>{{ $payment->payment_date->format('M d, Y') }}</strong>
                            </div>
                        </div>
                        <hr>
                        <div class="row text-start">
                            <div class="col-6">
                                <small class="text-muted">Method:</small><br>
                                <strong>{{ ucfirst($payment->payment_method) }}</strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">New Balance:</small><br>
                                <strong>${{ number_format($account->balance, 2) }}</strong>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        @if($receiptUrl)
                        <a href="{{ $receiptUrl }}" class="btn btn-primary" target="_blank">
                            <i class="fas fa-download me-2"></i>Download Receipt
                        </a>
                        @endif
                        <a href="{{ route('financial.student-dashboard') }}" class="btn btn-outline-primary">
                            Return to Dashboard
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-3">
                <small class="text-muted">
                    A confirmation email has been sent to your registered email address.
                </small>
            </div>
        </div>
    </div>
</div>
@endsection

<!-- ============================================= -->
<!-- resources/views/financial/student-statement.blade.php -->
<!-- ============================================= -->
@extends('layouts.app')

@section('title', 'Account Statement')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Statement Header -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4 class="mb-3">Account Statement</h4>
                            <p class="mb-1"><strong>Student:</strong> {{ $student->full_name }}</p>
                            <p class="mb-1"><strong>Student ID:</strong> {{ $student->student_id }}</p>
                            <p class="mb-1"><strong>Account Number:</strong> {{ $account->account_number }}</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <p class="mb-1"><strong>Statement Date:</strong> {{ now()->format('F d, Y') }}</p>
                            <h3 class="mt-3 {{ $account->balance > 0 ? 'text-danger' : 'text-success' }}">
                                Balance: ${{ number_format($account->balance, 2) }}
                            </h3>
                            @if($account->has_financial_hold)
                            <span class="badge bg-danger">Financial Hold Active</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Summary -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-muted">Total Charges</h6>
                            <h4>${{ number_format($account->total_charges ?? 0, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-muted">Total Payments</h6>
                            <h4 class="text-success">${{ number_format($account->total_payments ?? 0, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-muted">Financial Aid</h6>
                            <h4 class="text-info">${{ number_format($account->total_aid ?? 0, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-muted">Current Balance</h6>
                            <h4 class="{{ $account->balance > 0 ? 'text-danger' : 'text-success' }}">
                                ${{ number_format($account->balance, 2) }}
                            </h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Billing Items -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Charges & Credits</h5>
                </div>
                <div class="card-body">
                    @if($billingItems->count() > 0)
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Type</th>
                                    <th>Due Date</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-end">Balance</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($billingItems as $item)
                                <tr>
                                    <td>{{ $item->created_at->format('M d, Y') }}</td>
                                    <td>{{ $item->description }}</td>
                                    <td>
                                        <span class="badge {{ $item->type == 'charge' ? 'bg-warning' : 'bg-info' }}">
                                            {{ ucfirst($item->type) }}
                                        </span>
                                    </td>
                                    <td>{{ $item->due_date ? $item->due_date->format('M d, Y') : '-' }}</td>
                                    <td class="text-end">
                                        {{ $item->type == 'credit' ? '-' : '' }}${{ number_format(abs($item->amount), 2) }}
                                    </td>
                                    <td class="text-end">${{ number_format($item->balance, 2) }}</td>
                                    <td>
                                        @if($item->status == 'paid')
                                            <span class="badge bg-success">Paid</span>
                                        @elseif($item->status == 'partial')
                                            <span class="badge bg-warning">Partial</span>
                                        @elseif($item->due_date && $item->due_date < now())
                                            <span class="badge bg-danger">Overdue</span>
                                        @else
                                            <span class="badge bg-secondary">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted text-center py-3">No charges or credits found.</p>
                    @endif
                </div>
            </div>

            <!-- Payment History -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Payment History</h5>
                </div>
                <div class="card-body">
                    @if($payments->count() > 0)
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Payment #</th>
                                    <th>Method</th>
                                    <th>Reference</th>
                                    <th class="text-end">Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payments as $payment)
                                <tr>
                                    <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                                    <td>{{ $payment->payment_number }}</td>
                                    <td>{{ ucfirst($payment->payment_method) }}</td>
                                    <td>{{ $payment->reference_number ?? '-' }}</td>
                                    <td class="text-end text-success">
                                        -${{ number_format($payment->amount, 2) }}
                                    </td>
                                    <td>
                                        <span class="badge bg-success">{{ ucfirst($payment->status) }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted text-center py-3">No payments found.</p>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-4 text-center">
                <button onclick="window.print()" class="btn btn-outline-primary">
                    <i class="fas fa-print me-2"></i>Print Statement
                </button>
                <a href="{{ route('financial.student-dashboard') }}" class="btn btn-outline-secondary">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
@media print {
    .btn, .navbar, .sidebar {
        display: none !important;
    }
    .card {
        border: 1px solid #000 !important;
    }
}
</style>
@endsection