@extends('layouts.app')

@section('title', 'Pending Payments & Overdue Items')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Pending Payments & Overdue Items</h1>
        <div>
            <a href="{{ route('financial.make-payment') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>Process Payment
            </a>
            <a href="{{ route('financial.reports.revenue') }}" class="btn btn-info">
                <i class="fas fa-chart-line me-2"></i>View Reports
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending Payments
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $pendingPayments->total() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Overdue Items
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $overdueItems->total() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Pending Amount
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format($pendingPayments->sum('amount'), 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Overdue Amount
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format($overdueItems->sum('amount'), 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-times fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Payments Section -->
    <div class="card mb-4">
        <div class="card-header bg-warning text-white">
            <h5 class="mb-0">
                <i class="fas fa-clock me-2"></i>Pending Payments
            </h5>
        </div>
        <div class="card-body">
            @if($pendingPayments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Payment ID</th>
                                <th>Student</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Date</th>
                                <th>Reference</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingPayments as $payment)
                            <tr>
                                <td>#{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</td>
                                <td>
                                    <strong>{{ $payment->student->full_name ?? 'N/A' }}</strong><br>
                                    <small class="text-muted">{{ $payment->student->student_id ?? '' }}</small>
                                </td>
                                <td>
                                    <span class="text-primary fw-bold">${{ number_format($payment->amount, 2) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ ucfirst($payment->payment_method) }}</span>
                                </td>
                                <td>{{ $payment->created_at->format('M d, Y') }}</td>
                                <td>{{ $payment->reference_number ?? '-' }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <form action="{{ route('financial.payment.process-manual') }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="payment_id" value="{{ $payment->id }}">
                                            <button type="submit" class="btn btn-success btn-sm" 
                                                    onclick="return confirm('Confirm this payment?')">
                                                <i class="fas fa-check"></i> Confirm
                                            </button>
                                        </form>
                                        <button class="btn btn-danger btn-sm ms-1" 
                                                onclick="rejectPayment({{ $payment->id }})">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                        <a href="{{ route('financial.student-account.view', $payment->student_id) }}" 
                                           class="btn btn-info btn-sm ms-1">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $pendingPayments->links() }}
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>No pending payments at this time.
                </div>
            @endif
        </div>
    </div>

    <!-- Overdue Items Section -->
    <div class="card">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0">
                <i class="fas fa-exclamation-triangle me-2"></i>Overdue Billing Items
            </h5>
        </div>
        <div class="card-body">
            @if($overdueItems->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Bill ID</th>
                                <th>Student</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Due Date</th>
                                <th>Days Overdue</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($overdueItems as $item)
                            <tr>
                                <td>#{{ str_pad($item->id, 6, '0', STR_PAD_LEFT) }}</td>
                                <td>
                                    <strong>{{ $item->studentAccount->student->full_name ?? 'N/A' }}</strong><br>
                                    <small class="text-muted">{{ $item->studentAccount->student->student_id ?? '' }}</small>
                                </td>
                                <td>{{ $item->description }}</td>
                                <td>
                                    <span class="text-danger fw-bold">${{ number_format($item->amount, 2) }}</span>
                                </td>
                                <td>
                                    <span class="text-danger">{{ \Carbon\Carbon::parse($item->due_date)->format('M d, Y') }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-danger">
                                        {{ \Carbon\Carbon::parse($item->due_date)->diffInDays(now()) }} days
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-warning btn-sm" 
                                                onclick="sendReminder({{ $item->id }})">
                                            <i class="fas fa-bell"></i> Remind
                                        </button>
                                        <a href="{{ route('financial.student-account.view', $item->student_id ?? $item->studentAccount->student_id) }}" 
                                           class="btn btn-info btn-sm ms-1">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <button class="btn btn-primary btn-sm ms-1" 
                                                onclick="createPaymentPlan({{ $item->id }})">
                                            <i class="fas fa-calendar-alt"></i> Plan
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $overdueItems->links() }}
                </div>
            @else
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>No overdue items!
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function rejectPayment(paymentId) {
    if (confirm('Are you sure you want to reject this payment?')) {
        // Add AJAX call to reject payment
        alert('Payment rejection functionality to be implemented');
    }
}

function sendReminder(itemId) {
    if (confirm('Send payment reminder to student?')) {
        // Add AJAX call to send reminder
        alert('Reminder functionality to be implemented');
    }
}

function createPaymentPlan(itemId) {
    // Redirect to payment plan creation
    window.location.href = '/financial/payment-plans/create?item=' + itemId;
}
</script>
@endpush
@endsection