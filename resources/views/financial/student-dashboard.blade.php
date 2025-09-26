<!-- resources/views/financial/student-dashboard.blade.php -->
@extends('layouts.app')

@section('title', 'Financial Dashboard')

@section('content')
<div class="container-fluid py-4">
    <!-- Account Overview Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="mb-2">Welcome, {{ $student->full_name }}</h3>
                            <p class="mb-0 opacity-8">
                                Account Number: {{ $account->account_number }} | 
                                Student ID: {{ $student->student_id }}
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <h2 class="mb-0">
                                @if($account->balance > 0)
                                    ${{ number_format($account->balance, 2) }}
                                @else
                                    <span class="text-success">$0.00</span>
                                @endif
                            </h2>
                            <p class="mb-0 opacity-8">Current Balance</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Alerts -->
    @if($account->has_financial_hold)
    <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
        <i class="fas fa-exclamation-triangle me-3 fs-4"></i>
        <div>
            <strong>Financial Hold Active:</strong> {{ $account->hold_reason }}
            <br>
            <small>Please make a payment to remove this hold and register for classes.</small>
        </div>
    </div>
    @endif

    @if($paymentPlan && $paymentPlan->next_payment_date)
    <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
        <i class="fas fa-calendar-alt me-3 fs-4"></i>
        <div>
            <strong>Payment Plan Installment Due:</strong> 
            ${{ number_format($paymentPlan->installment_amount, 2) }} on 
            {{ Carbon\Carbon::parse($paymentPlan->next_payment_date)->format('F d, Y') }}
        </div>
    </div>
    @endif

    <!-- Quick Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1">Current Term Charges</p>
                            <h4>${{ number_format($quickStats['term_charges'] ?? 0, 2) }}</h4>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-file-invoice-dollar fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1">Total Payments</p>
                            <h4>${{ number_format($account->total_payments ?? 0, 2) }}</h4>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1">Financial Aid</p>
                            <h4>${{ number_format($account->total_aid ?? 0, 2) }}</h4>
                        </div>
                        <div class="text-info">
                            <i class="fas fa-graduation-cap fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1">Next Due</p>
                            <h4>
                                @if($upcomingDues->first())
                                    {{ Carbon\Carbon::parse($upcomingDues->first()->due_date)->format('M d') }}
                                @else
                                    None
                                @endif
                            </h4>
                        </div>
                        <div class="text-warning">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Quick Actions</h5>
                    <div class="d-flex flex-wrap gap-2">
                        @if($account->balance > 0)
                        <a href="{{ route('financial.initiate-payment') }}" class="btn btn-primary">
                            <i class="fas fa-credit-card me-2"></i>Make Payment
                        </a>
                        @endif
                        
                        <a href="{{ route('financial.statement', $student->id) }}" class="btn btn-outline-primary">
                            <i class="fas fa-file-alt me-2"></i>View Statement
                        </a>
                        
                        @if(!$paymentPlan && $account->balance > 500)
                        <a href="{{ route('financial.payment-plans') }}" class="btn btn-outline-warning">
                            <i class="fas fa-calendar-check me-2"></i>Request Payment Plan
                        </a>
                        @endif
                        
                        <a href="{{ route('financial.payment-history') }}" class="btn btn-outline-info">
                            <i class="fas fa-history me-2"></i>Payment History
                        </a>
                        
                        <a href="{{ route('financial.financial-aid') }}" class="btn btn-outline-success">
                            <i class="fas fa-hand-holding-usd me-2"></i>Financial Aid
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Current Charges -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Current Charges</h5>
                </div>
                <div class="card-body">
                    @if($pendingCharges->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th>Due Date</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-end">Balance</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingCharges as $charge)
                                <tr>
                                    <td>{{ $charge->description }}</td>
                                    <td>{{ Carbon\Carbon::parse($charge->due_date)->format('M d, Y') }}</td>
                                    <td class="text-end">${{ number_format($charge->amount, 2) }}</td>
                                    <td class="text-end">${{ number_format($charge->balance, 2) }}</td>
                                    <td>
                                        @if($charge->status == 'paid')
                                            <span class="badge bg-success">Paid</span>
                                        @elseif($charge->status == 'partial')
                                            <span class="badge bg-warning">Partial</span>
                                        @elseif($charge->due_date < now())
                                            <span class="badge bg-danger">Overdue</span>
                                        @else
                                            <span class="badge bg-info">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold">
                                    <td colspan="3">Total Due</td>
                                    <td class="text-end">${{ number_format($pendingCharges->sum('balance'), 2) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @else
                    <p class="text-muted text-center py-4">
                        <i class="fas fa-check-circle fa-3x mb-3 text-success"></i><br>
                        No outstanding charges
                    </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Recent Activity</h5>
                </div>
                <div class="card-body">
                    @if($recentTransactions->count() > 0)
                    <div class="activity-feed">
                        @foreach($recentTransactions as $transaction)
                        <div class="feed-item">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    @if($transaction->transaction_type == 'payment')
                                        <div class="avatar-sm bg-success text-white rounded-circle">
                                            <i class="fas fa-arrow-down"></i>
                                        </div>
                                    @elseif($transaction->transaction_type == 'charge')
                                        <div class="avatar-sm bg-danger text-white rounded-circle">
                                            <i class="fas fa-arrow-up"></i>
                                        </div>
                                    @else
                                        <div class="avatar-sm bg-info text-white rounded-circle">
                                            <i class="fas fa-exchange-alt"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="mb-1">{{ $transaction->description }}</p>
                                    <small class="text-muted">
                                        {{ Carbon\Carbon::parse($transaction->created_at)->diffForHumans() }}
                                    </small>
                                </div>
                                <div class="text-end">
                                    <strong class="{{ $transaction->transaction_type == 'payment' ? 'text-success' : 'text-danger' }}">
                                        {{ $transaction->transaction_type == 'payment' ? '-' : '+' }}
                                        ${{ number_format(abs($transaction->amount), 2) }}
                                    </strong>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-muted text-center py-4">No recent activity</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Plan Details (if active) -->
    @if($paymentPlan)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Active Payment Plan</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Total Amount</p>
                            <h5>${{ number_format($paymentPlan->total_amount, 2) }}</h5>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Paid to Date</p>
                            <h5>${{ number_format($paymentPlan->total_paid, 2) }}</h5>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Next Payment</p>
                            <h5>${{ number_format($paymentPlan->installment_amount, 2) }}</h5>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Next Due Date</p>
                            <h5>{{ Carbon\Carbon::parse($paymentPlan->next_payment_date)->format('M d, Y') }}</h5>
                        </div>
                    </div>
                    
                    <!-- Payment Schedule -->
                    <div class="mt-4">
                        <h6>Payment Schedule</h6>
                        <div class="progress mb-2" style="height: 25px;">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: {{ ($paymentPlan->total_paid / $paymentPlan->total_amount) * 100 }}%">
                                {{ round(($paymentPlan->total_paid / $paymentPlan->total_amount) * 100) }}% Paid
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@section('styles')
<style>
.avatar-sm {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.feed-item {
    padding: 15px 0;
    border-bottom: 1px solid #e9ecef;
}

.feed-item:last-child {
    border-bottom: none;
}
</style>
@endsection