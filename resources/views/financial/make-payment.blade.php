<!-- resources/views/financial/make-payment.blade.php -->
@extends('layouts.app')

@section('title', 'Make Payment')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Payment Header -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-1">Make a Payment</h4>
                            <p class="text-muted mb-0">
                                Student: {{ $student->full_name }} ({{ $student->student_id }})
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <h3 class="text-primary mb-0">${{ number_format($account->balance, 2) }}</h3>
                            <small class="text-muted">Current Balance</small>
                        </div>
                    </div>
                </div>
            </div>

            @if($account->balance <= 0)
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                You have no outstanding balance. Thank you for keeping your account current!
            </div>
            @else

            <!-- Payment Options -->
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Payment Options</h5>
                </div>
                <div class="card-body">
                    <!-- Payment Amount Selection -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Select Payment Amount</label>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="form-check payment-option">
                                    <input class="form-check-input" type="radio" name="payment_type" 
                                           id="full_balance" value="full_balance" checked>
                                    <label class="form-check-label w-100" for="full_balance">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>Full Balance</span>
                                            <strong>${{ number_format($account->balance, 2) }}</strong>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            @if($minimumPayment)
                            <div class="col-md-4">
                                <div class="form-check payment-option">
                                    <input class="form-check-input" type="radio" name="payment_type" 
                                           id="minimum_due" value="minimum_due">
                                    <label class="form-check-label w-100" for="minimum_due">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>Minimum Due</span>
                                            <strong>${{ number_format($minimumPayment, 2) }}</strong>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            @endif
                            <div class="col-md-4">
                                <div class="form-check payment-option">
                                    <input class="form-check-input" type="radio" name="payment_type" 
                                           id="custom_amount" value="custom_amount">
                                    <label class="form-check-label w-100" for="custom_amount">
                                        <span>Custom Amount</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Custom Amount Input -->
                    <div class="mb-4" id="custom-amount-container" style="display: none;">
                        <label class="form-label">Enter Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="custom_amount_input" 
                                   min="1" max="{{ $account->balance }}" step="0.01">
                        </div>
                    </div>

                    <!-- Payment Method Selection -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Payment Method</label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <button type="button" class="btn btn-outline-primary w-100 payment-method-btn active" 
                                        data-method="card">
                                    <i class="fas fa-credit-card fa-2x mb-2"></i><br>
                                    Credit/Debit Card
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button type="button" class="btn btn-outline-primary w-100 payment-method-btn" 
                                        data-method="bank_transfer">
                                    <i class="fas fa-university fa-2x mb-2"></i><br>
                                    Bank Transfer
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Form -->
                    <form action="{{ route('financial.process-payment') }}" method="POST" id="payment-form">
                        @csrf
                        <input type="hidden" name="amount" id="payment_amount" value="{{ $account->balance }}">
                        <input type="hidden" name="payment_method" id="payment_method" value="card">

                        <!-- Continue to Payment Button -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-lock me-2"></i>
                                Continue to Secure Payment
                            </button>
                            <a href="{{ route('financial.student-dashboard') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <!-- Recent Payments -->
            @if($recentPayments->count() > 0)
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">Recent Payments</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Method</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentPayments as $payment)
                                <tr>
                                    <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                                    <td>{{ ucfirst($payment->payment_method) }}</td>
                                    <td>${{ number_format($payment->amount, 2) }}</td>
                                    <td>
                                        <span class="badge bg-success">{{ ucfirst($payment->status) }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle payment type selection
    const paymentTypes = document.querySelectorAll('input[name="payment_type"]');
    const customAmountContainer = document.getElementById('custom-amount-container');
    const customAmountInput = document.getElementById('custom_amount_input');
    const paymentAmountField = document.getElementById('payment_amount');
    
    paymentTypes.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'custom_amount') {
                customAmountContainer.style.display = 'block';
                customAmountInput.required = true;
            } else {
                customAmountContainer.style.display = 'none';
                customAmountInput.required = false;
                
                // Set payment amount based on selection
                if (this.value === 'full_balance') {
                    paymentAmountField.value = '{{ $account->balance }}';
                } else if (this.value === 'minimum_due') {
                    paymentAmountField.value = '{{ $minimumPayment }}';
                }
            }
        });
    });
    
    // Update payment amount when custom amount changes
    customAmountInput.addEventListener('input', function() {
        paymentAmountField.value = this.value;
    });
    
    // Handle payment method selection
    const methodButtons = document.querySelectorAll('.payment-method-btn');
    const paymentMethodField = document.getElementById('payment_method');
    
    methodButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            methodButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            paymentMethodField.value = this.dataset.method;
        });
    });
});
</script>
@endsection

@section('styles')
<style>
.payment-option {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 1rem;
    transition: all 0.2s;
}

.payment-option:hover {
    border-color: var(--bs-primary);
    background-color: var(--bs-light);
}

.form-check-input:checked ~ .form-check-label .payment-option {
    border-color: var(--bs-primary);
    background-color: var(--bs-primary-bg-subtle);
}

.payment-method-btn {
    padding: 1.5rem;
    transition: all 0.2s;
}

.payment-method-btn.active {
    background-color: var(--bs-primary);
    color: white;
}
</style>
@endsection