<!-- resources/views/financial/online-payment.blade.php -->
@extends('layouts.app')

@section('title', 'Make Online Payment')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Payment Header -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-1">Secure Online Payment</h4>
                            <p class="text-muted mb-0">
                                Account: {{ $student->full_name }} - {{ $student->student_id }}
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <h3 class="text-primary mb-0">${{ number_format($amount, 2) }}</h3>
                            <small class="text-muted">Payment Amount</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Form -->
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-lock me-2"></i>Payment Information</h5>
                </div>
                <div class="card-body">
                    <!-- Payment Summary -->
                    <div class="alert alert-info">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Payment ID:</strong> {{ $payment->payment_number }}<br>
                                <strong>Date:</strong> {{ now()->format('F d, Y') }}
                            </div>
                            <div class="col-md-6">
                                <strong>Amount Due:</strong> ${{ number_format($amount, 2) }}<br>
                                <strong>Processing Fee:</strong> Included
                            </div>
                        </div>
                    </div>

                    <!-- Stripe Payment Form -->
                    <form id="payment-form" data-payment-id="{{ $payment->id }}">
                        @csrf
                        
                        <!-- Cardholder Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Cardholder Name</label>
                                <input type="text" id="cardholder-name" class="form-control" 
                                       value="{{ $student->full_name }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email (for receipt)</label>
                                <input type="email" id="cardholder-email" class="form-control" 
                                       value="{{ $student->email }}" required>
                            </div>
                        </div>

                        <!-- Billing Address -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <label class="form-label">Billing Address</label>
                                <input type="text" id="billing-address" class="form-control" 
                                       placeholder="123 Main Street" required>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">City</label>
                                <input type="text" id="billing-city" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">State</label>
                                <input type="text" id="billing-state" class="form-control" 
                                       maxlength="2" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">ZIP Code</label>
                                <input type="text" id="billing-zip" class="form-control" 
                                       maxlength="10" required>
                            </div>
                        </div>

                        <!-- Stripe Card Element -->
                        <div class="mb-4">
                            <label class="form-label">Card Information</label>
                            <div id="card-element" class="form-control" style="padding: 12px;">
                                <!-- Stripe Elements will be inserted here -->
                            </div>
                            <div id="card-errors" class="text-danger mt-2" role="alert"></div>
                        </div>

                        <!-- Save Card Option -->
                        <div class="form-check mb-4">
                            <input type="checkbox" class="form-check-input" id="save-card">
                            <label class="form-check-label" for="save-card">
                                Save this card for future payments (secure & encrypted)
                            </label>
                        </div>

                        <!-- Security Notice -->
                        <div class="alert alert-secondary">
                            <i class="fas fa-shield-alt me-2"></i>
                            <strong>Secure Payment:</strong> Your payment information is encrypted and processed securely through Stripe. 
                            We never store your full card details.
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid gap-2">
                            <button type="submit" id="submit-payment" class="btn btn-primary btn-lg">
                                <span id="button-text">
                                    <i class="fas fa-lock me-2"></i>Pay ${{ number_format($amount, 2) }}
                                </span>
                                <span id="spinner" class="d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                    Processing...
                                </span>
                            </button>
                            <a href="{{ route('financial.student-dashboard') }}" class="btn btn-outline-secondary">
                                Cancel Payment
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Security Badges -->
            <div class="text-center mt-4">
                <img src="/images/stripe-badge.png" alt="Powered by Stripe" height="40" class="me-3">
                <img src="/images/ssl-secure.png" alt="SSL Secure" height="40" class="me-3">
                <img src="/images/pci-compliant.png" alt="PCI Compliant" height="40">
            </div>
        </div>
    </div>
</div>

<!-- Payment Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-5">
                <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                <h4 class="mt-3">Payment Successful!</h4>
                <p class="text-muted">Your payment has been processed successfully.</p>
                <div class="d-grid gap-2 mt-4">
                    <a href="#" id="receipt-link" class="btn btn-primary">View Receipt</a>
                    <a href="{{ route('financial.student-dashboard') }}" class="btn btn-outline-secondary">
                        Return to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Stripe JS -->
<script src="https://js.stripe.com/v3/"></script>

<script>
// Initialize Stripe
const stripe = Stripe('{{ $stripePublicKey }}');
const elements = stripe.elements();

// Custom styling for Stripe Elements
const style = {
    base: {
        fontSize: '16px',
        color: '#32325d',
        fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
        '::placeholder': {
            color: '#aab7c4'
        }
    },
    invalid: {
        color: '#fa755a',
        iconColor: '#fa755a'
    }
};

// Create card element
const cardElement = elements.create('card', {style: style});
cardElement.mount('#card-element');

// Handle real-time validation errors
cardElement.on('change', function(event) {
    const displayError = document.getElementById('card-errors');
    if (event.error) {
        displayError.textContent = event.error.message;
    } else {
        displayError.textContent = '';
    }
});

// Handle form submission
const form = document.getElementById('payment-form');
form.addEventListener('submit', async function(event) {
    event.preventDefault();
    
    const submitButton = document.getElementById('submit-payment');
    const buttonText = document.getElementById('button-text');
    const spinner = document.getElementById('spinner');
    
    // Disable button and show spinner
    submitButton.disabled = true;
    buttonText.classList.add('d-none');
    spinner.classList.remove('d-none');
    
    // Create payment method
    const {error, paymentMethod} = await stripe.createPaymentMethod({
        type: 'card',
        card: cardElement,
        billing_details: {
            name: document.getElementById('cardholder-name').value,
            email: document.getElementById('cardholder-email').value,
            address: {
                line1: document.getElementById('billing-address').value,
                city: document.getElementById('billing-city').value,
                state: document.getElementById('billing-state').value,
                postal_code: document.getElementById('billing-zip').value
            }
        }
    });
    
    if (error) {
        // Show error to customer
        const errorElement = document.getElementById('card-errors');
        errorElement.textContent = error.message;
        
        // Re-enable button
        submitButton.disabled = false;
        buttonText.classList.remove('d-none');
        spinner.classList.add('d-none');
    } else {
        // Confirm payment with our backend
        confirmPayment(paymentMethod.id);
    }
});

// Confirm payment with backend
async function confirmPayment(paymentMethodId) {
    const paymentId = form.dataset.paymentId;
    const clientSecret = '{{ $clientSecret }}';
    
    // Confirm the payment with Stripe
    const {error, paymentIntent} = await stripe.confirmCardPayment(clientSecret, {
        payment_method: paymentMethodId
    });
    
    if (error) {
        // Show error to customer
        const errorElement = document.getElementById('card-errors');
        errorElement.textContent = error.message;
        
        // Re-enable button
        const submitButton = document.getElementById('submit-payment');
        const buttonText = document.getElementById('button-text');
        const spinner = document.getElementById('spinner');
        
        submitButton.disabled = false;
        buttonText.classList.remove('d-none');
        spinner.classList.add('d-none');
    } else {
        // Payment succeeded, send to our server for confirmation
        fetch('{{ route("financial.confirm-payment") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                payment_id: paymentId,
                payment_intent_id: paymentIntent.id
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success modal
                document.getElementById('receipt-link').href = data.receipt_url;
                const modal = new bootstrap.Modal(document.getElementById('successModal'));
                modal.show();
            } else {
                alert('Payment confirmation failed. Please contact support.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
}
</script>
@endsection