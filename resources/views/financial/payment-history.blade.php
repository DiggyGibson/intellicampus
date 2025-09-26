@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-history me-2"></i>Payment History
            </h1>
            <nav aria-label="breadcrumb" class="mt-2">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('financial.student-dashboard') }}">Financial Dashboard</a></li>
                    <li class="breadcrumb-item active">Payment History</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card shadow mb-4">
        <div class="card-header">
            <h6 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Payments</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('financial.payment-history') }}">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="date_from" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" 
                               value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="date_to" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" 
                               value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="payment_method" class="form-label">Payment Method</label>
                        <select class="form-select" id="payment_method" name="payment_method">
                            <option value="">All Methods</option>
                            <option value="credit_card" {{ request('payment_method') == 'credit_card' ? 'selected' : '' }}>
                                Credit Card
                            </option>
                            <option value="debit_card" {{ request('payment_method') == 'debit_card' ? 'selected' : '' }}>
                                Debit Card
                            </option>
                            <option value="ach" {{ request('payment_method') == 'ach' ? 'selected' : '' }}>
                                Bank Transfer
                            </option>
                            <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>
                                Cash
                            </option>
                            <option value="check" {{ request('payment_method') == 'check' ? 'selected' : '' }}>
                                Check
                            </option>
                            <option value="financial_aid" {{ request('payment_method') == 'financial_aid' ? 'selected' : '' }}>
                                Financial Aid
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>
                                Completed
                            </option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>
                                Pending
                            </option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>
                                Failed
                            </option>
                            <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>
                                Refunded
                            </option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Apply Filter
                        </button>
                        <a href="{{ route('financial.payment-history') }}" class="btn btn-secondary ms-2">
                            <i class="fas fa-redo me-2"></i>Reset
                        </a>
                        <button type="button" class="btn btn-success ms-2" onclick="exportPayments()">
                            <i class="fas fa-download me-2"></i>Export to Excel
                        </button>
                        <button type="button" class="btn btn-info ms-2" onclick="printPayments()">
                            <i class="fas fa-print me-2"></i>Print
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Payment Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Payments
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format($totalPayments ?? 15750.00, 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                This Term
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format($termPayments ?? 5700.00, 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format($pendingPayments ?? 0, 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Payment Count
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $paymentCount ?? 8 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-receipt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card shadow">
        <div class="card-header">
            <h6 class="mb-0"><i class="fas fa-list me-2"></i>Payment Transactions</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="paymentsTable">
                    <thead>
                        <tr>
                            <th>Receipt #</th>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Payment Method</th>
                            <th class="text-end">Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments ?? [] as $payment)
                        <tr>
                            <td>
                                <code>{{ $payment->receipt_number }}</code>
                            </td>
                            <td>
                                {{ $payment->created_at->format('M d, Y') }}<br>
                                <small class="text-muted">{{ $payment->created_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                {{ $payment->description }}<br>
                                @if($payment->reference_number)
                                    <small class="text-muted">Ref: {{ $payment->reference_number }}</small>
                                @endif
                            </td>
                            <td>
                                @if($payment->payment_method == 'credit_card')
                                    <i class="fas fa-credit-card text-primary"></i> Credit Card
                                @elseif($payment->payment_method == 'debit_card')
                                    <i class="fas fa-credit-card text-info"></i> Debit Card
                                @elseif($payment->payment_method == 'ach')
                                    <i class="fas fa-university text-success"></i> Bank Transfer
                                @elseif($payment->payment_method == 'cash')
                                    <i class="fas fa-money-bill text-success"></i> Cash
                                @elseif($payment->payment_method == 'check')
                                    <i class="fas fa-file-invoice text-warning"></i> Check
                                @elseif($payment->payment_method == 'financial_aid')
                                    <i class="fas fa-hands-helping text-info"></i> Financial Aid
                                @else
                                    <i class="fas fa-question-circle"></i> {{ ucfirst($payment->payment_method) }}
                                @endif
                                @if($payment->last_four)
                                    <br><small class="text-muted">****{{ $payment->last_four }}</small>
                                @endif
                            </td>
                            <td class="text-end">
                                <strong>${{ number_format($payment->amount, 2) }}</strong>
                            </td>
                            <td>
                                @if($payment->status == 'completed')
                                    <span class="badge bg-success">
                                        <i class="fas fa-check"></i> Completed
                                    </span>
                                @elseif($payment->status == 'pending')
                                    <span class="badge bg-warning">
                                        <i class="fas fa-clock"></i> Pending
                                    </span>
                                @elseif($payment->status == 'failed')
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times"></i> Failed
                                    </span>
                                @elseif($payment->status == 'refunded')
                                    <span class="badge bg-info">
                                        <i class="fas fa-undo"></i> Refunded
                                    </span>
                                    @if($payment->refund_amount)
                                        <br><small>${{ number_format($payment->refund_amount, 2) }}</small>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($payment->status) }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-outline-primary" onclick="viewReceipt({{ $payment->id }})" 
                                            title="View Receipt">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a href="{{ route('financial.payment.receipt', $payment->id ?? 1) }}" 
                                       class="btn btn-outline-success" target="_blank" title="Download Receipt">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <button class="btn btn-outline-info" onclick="printReceipt({{ $payment->id }})"
                                            title="Print Receipt">
                                        <i class="fas fa-print"></i>
                                    </button>
                                    @if($payment->status == 'completed' && $payment->created_at->gt(now()->subDays(30)))
                                    <button class="btn btn-outline-warning" onclick="requestRefund({{ $payment->id }})"
                                            title="Request Refund">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <!-- Sample Payment History Data -->
                        <tr>
                            <td><code>RCP-2025-001</code></td>
                            <td>
                                Jan 15, 2025<br>
                                <small class="text-muted">10:30 AM</small>
                            </td>
                            <td>
                                Spring 2025 Tuition Payment<br>
                                <small class="text-muted">Ref: TXN-20250115-001</small>
                            </td>
                            <td>
                                <i class="fas fa-credit-card text-primary"></i> Credit Card<br>
                                <small class="text-muted">****4242</small>
                            </td>
                            <td class="text-end"><strong>$2,850.00</strong></td>
                            <td><span class="badge bg-success"><i class="fas fa-check"></i> Completed</span></td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-outline-primary" onclick="viewReceipt(1)" title="View Receipt">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a href="#" class="btn btn-outline-success" title="Download Receipt">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <button class="btn btn-outline-info" onclick="printReceipt(1)" title="Print Receipt">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><code>RCP-2025-002</code></td>
                            <td>
                                Jan 10, 2025<br>
                                <small class="text-muted">2:15 PM</small>
                            </td>
                            <td>
                                Registration Fee<br>
                                <small class="text-muted">Ref: TXN-20250110-045</small>
                            </td>
                            <td>
                                <i class="fas fa-university text-success"></i> Bank Transfer<br>
                                <small class="text-muted">****8901</small>
                            </td>
                            <td class="text-end"><strong>$150.00</strong></td>
                            <td><span class="badge bg-success"><i class="fas fa-check"></i> Completed</span></td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-outline-primary" onclick="viewReceipt(2)" title="View Receipt">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a href="#" class="btn btn-outline-success" title="Download Receipt">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <button class="btn btn-outline-info" onclick="printReceipt(2)" title="Print Receipt">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><code>RCP-2024-089</code></td>
                            <td>
                                Dec 20, 2024<br>
                                <small class="text-muted">11:45 AM</small>
                            </td>
                            <td>
                                Fall 2024 Tuition Payment<br>
                                <small class="text-muted">Ref: TXN-20241220-112</small>
                            </td>
                            <td>
                                <i class="fas fa-hands-helping text-info"></i> Financial Aid<br>
                                <small class="text-muted">Grant #FA2024-156</small>
                            </td>
                            <td class="text-end"><strong>$1,500.00</strong></td>
                            <td><span class="badge bg-success"><i class="fas fa-check"></i> Completed</span></td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-outline-primary" onclick="viewReceipt(3)" title="View Receipt">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a href="#" class="btn btn-outline-success" title="Download Receipt">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <button class="btn btn-outline-info" onclick="printReceipt(3)" title="Print Receipt">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><code>RCP-2024-088</code></td>
                            <td>
                                Dec 15, 2024<br>
                                <small class="text-muted">3:30 PM</small>
                            </td>
                            <td>
                                Lab Fee - Chemistry<br>
                                <small class="text-muted">Ref: TXN-20241215-089</small>
                            </td>
                            <td>
                                <i class="fas fa-credit-card text-info"></i> Debit Card<br>
                                <small class="text-muted">****6789</small>
                            </td>
                            <td class="text-end"><strong>$75.00</strong></td>
                            <td><span class="badge bg-success"><i class="fas fa-check"></i> Completed</span></td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-outline-primary" onclick="viewReceipt(4)" title="View Receipt">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a href="#" class="btn btn-outline-success" title="Download Receipt">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <button class="btn btn-outline-info" onclick="printReceipt(4)" title="Print Receipt">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if(isset($payments) && $payments->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Showing {{ $payments->firstItem() }} to {{ $payments->lastItem() }} of {{ $payments->total() }} payments
                </div>
                <div>
                    {{ $payments->links() }}
                </div>
            </div>
            @else
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Showing 1 to 4 of 4 payments
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- View Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="receiptModalLabel">Payment Receipt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="receiptContent">
                <!-- Receipt content will be loaded here -->
                <div class="text-center p-5">
                    <div class="mb-4">
                        <h3>IntelliCampus University</h3>
                        <p>123 University Avenue, City, State 12345</p>
                    </div>
                    <hr>
                    <h5>Payment Receipt</h5>
                    <div class="row mt-4">
                        <div class="col-6 text-start">
                            <p><strong>Receipt Number:</strong> RCP-2025-001</p>
                            <p><strong>Date:</strong> January 15, 2025</p>
                            <p><strong>Student ID:</strong> STU-2025001</p>
                        </div>
                        <div class="col-6 text-end">
                            <p><strong>Payment Method:</strong> Credit Card</p>
                            <p><strong>Reference:</strong> TXN-20250115-001</p>
                            <p><strong>Status:</strong> Completed</p>
                        </div>
                    </div>
                    <div class="table-responsive mt-4">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Spring 2025 Tuition Payment</td>
                                    <td class="text-end">$2,850.00</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>Total Paid:</th>
                                    <th class="text-end">$2,850.00</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="mt-4 text-muted">
                        <small>This is an official receipt for your payment. Please keep this for your records.</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printModalContent()">
                    <i class="fas fa-print me-2"></i>Print
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Request Refund Modal -->
<div class="modal fade" id="refundModal" tabindex="-1" aria-labelledby="refundModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="refundForm" method="POST" action="{{ route('financial.payment.refund', ':id') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="refundModalLabel">Request Refund</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="refund_payment_id" name="payment_id">
                    
                    <div class="mb-3">
                        <label for="refund_amount" class="form-label">Refund Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="refund_amount" name="amount" 
                                   step="0.01" min="0.01" required>
                        </div>
                        <small class="text-muted">Original payment: $<span id="original_amount">0.00</span></small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="refund_reason" class="form-label">Reason for Refund</label>
                        <select class="form-select" id="refund_reason" name="reason" required>
                            <option value="">Select reason</option>
                            <option value="duplicate_payment">Duplicate Payment</option>
                            <option value="overpayment">Overpayment</option>
                            <option value="withdrawal">Course Withdrawal</option>
                            <option value="error">Payment Error</option>
                            <option value="scholarship">Scholarship Received</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="refund_notes" class="form-label">Additional Notes</label>
                        <textarea class="form-control" id="refund_notes" name="notes" rows="3"></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Refund requests are subject to review and approval by the Bursar's Office.
                        Processing typically takes 5-7 business days.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Submit Refund Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// View receipt in modal
function viewReceipt(paymentId) {
    // In production, this would make an AJAX call to get the receipt
    $('#receiptModal').modal('show');
}

// Print receipt
function printReceipt(paymentId) {
    window.open('/financial/payment/receipt/' + paymentId + '/print', '_blank');
}

// Print modal content
function printModalContent() {
    var printContent = document.getElementById('receiptContent').innerHTML;
    var originalContent = document.body.innerHTML;
    document.body.innerHTML = printContent;
    window.print();
    document.body.innerHTML = originalContent;
    location.reload();
}

// Request refund
function requestRefund(paymentId) {
    // Set payment ID
    $('#refund_payment_id').val(paymentId);
    
    // In production, get payment details via AJAX
    // For demo, using sample data
    $('#refund_amount').val('2850.00');
    $('#refund_amount').attr('max', '2850.00');
    $('#original_amount').text('2,850.00');
    
    // Update form action
    var form = $('#refundForm');
    var action = form.attr('action').replace(':id', paymentId);
    form.attr('action', action);
    
    $('#refundModal').modal('show');
}

// Handle refund form submission
$('#refundForm').on('submit', function(e) {
    e.preventDefault();
    
    // In production, this would submit via AJAX
    alert('Refund request submitted successfully! You will receive a confirmation email shortly.');
    $('#refundModal').modal('hide');
    
    // Reload page to show updated status
    setTimeout(function() {
        location.reload();
    }, 1000);
});

// Export to Excel
function exportPayments() {
    var params = window.location.search;
    window.location.href = '{{ route("financial.payment-history.export") }}' + params;
}

// Print payments
function printPayments() {
    window.print();
}

// Initialize on document ready
$(document).ready(function() {
    // Add date picker initialization if needed
    
    // Add tooltip initialization
    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>
@endpush

@push('styles')
<style>
@media print {
    /* Hide elements when printing */
    .btn, .breadcrumb, .card-header, .pagination, nav, .modal-footer {
        display: none !important;
    }
    
    /* Adjust table for printing */
    .table {
        font-size: 12px;
    }
    
    /* Remove shadows and borders for cleaner print */
    .card {
        border: none !important;
        box-shadow: none !important;
    }
}

/* Custom styles for payment status badges */
.badge {
    padding: 0.35em 0.65em;
    font-size: 0.75em;
}

/* Border colors for summary cards */
.border-left-primary {
    border-left: 4px solid #4e73df;
}
.border-left-success {
    border-left: 4px solid #1cc88a;
}
.border-left-info {
    border-left: 4px solid #36b9cc;
}
.border-left-warning {
    border-left: 4px solid #f6c23e;
}

/* Table hover effect */
.table-hover tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.03);
}

/* Button group in actions column */
.btn-group-sm > .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
</style>
@endpush
@endsection