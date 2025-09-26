@extends('layouts.app')

@section('title', 'Enrollment Deposit Payment')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <!-- Progress Bar -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center">
                <div class="bg-green-500 text-white rounded-full w-10 h-10 flex items-center justify-center">
                    <i class="fas fa-check"></i>
                </div>
                <span class="ml-3 font-semibold">Accept Offer</span>
            </div>
            <div class="flex-1 mx-4">
                <div class="h-1 bg-green-500"></div>
            </div>
            <div class="flex items-center">
                <div class="bg-blue-500 text-white rounded-full w-10 h-10 flex items-center justify-center">
                    2
                </div>
                <span class="ml-3 font-semibold">Pay Deposit</span>
            </div>
            <div class="flex-1 mx-4">
                <div class="h-1 bg-gray-300"></div>
            </div>
            <div class="flex items-center">
                <div class="bg-gray-300 text-gray-600 rounded-full w-10 h-10 flex items-center justify-center">
                    3
                </div>
                <span class="ml-3 text-gray-600">Complete</span>
            </div>
        </div>
    </div>

    <!-- Payment Header -->
    <div class="bg-white rounded-lg shadow-lg mb-6">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-6 rounded-t-lg">
            <h1 class="text-2xl font-bold">Enrollment Deposit Payment</h1>
            <p class="mt-2 text-blue-100">Secure your spot for {{ $application->term->name }}</p>
        </div>

        <!-- Deposit Information -->
        <div class="p-6">
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">About the Enrollment Deposit</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p>The enrollment deposit confirms your intent to enroll and reserves your place in the incoming class.</p>
                            <ul class="list-disc list-inside mt-2 space-y-1">
                                <li>This deposit will be credited to your student account</li>
                                <li>It will be applied toward your first semester tuition</li>
                                <li>Deadline: <strong>{{ $enrollment->enrollment_deadline->format('F j, Y') }}</strong></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Summary -->
            <div class="border rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Payment Summary</h2>
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Enrollment Deposit</span>
                        <span class="font-semibold">${{ number_format($depositAmount, 2) }}</span>
                    </div>
                    
                    @if($application->fee_waiver_approved)
                    <div class="flex justify-between text-green-600">
                        <span>Fee Waiver Applied</span>
                        <span>-${{ number_format($waiverAmount, 2) }}</span>
                    </div>
                    @endif
                    
                    <div class="border-t pt-3">
                        <div class="flex justify-between text-lg font-bold">
                            <span>Total Due</span>
                            <span class="text-blue-600">${{ number_format($totalDue, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="mb-6">
                <h2 class="text-xl font-semibold mb-4">Select Payment Method</h2>
                
                <form id="paymentForm" action="{{ route('enrollment.process-deposit', $enrollment->id) }}" method="POST">
                    @csrf
                    
                    <div class="space-y-3">
                        <!-- Credit/Debit Card -->
                        <label class="flex items-start p-4 border rounded-lg cursor-pointer hover:bg-gray-50 payment-method-option">
                            <input type="radio" name="payment_method" value="credit_card" class="mt-1" checked>
                            <div class="ml-3 flex-1">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="font-semibold">Credit/Debit Card</span>
                                        <p class="text-sm text-gray-600">Visa, MasterCard, American Express, Discover</p>
                                    </div>
                                    <div class="flex space-x-2">
                                        <i class="fab fa-cc-visa text-2xl text-gray-400"></i>
                                        <i class="fab fa-cc-mastercard text-2xl text-gray-400"></i>
                                        <i class="fab fa-cc-amex text-2xl text-gray-400"></i>
                                        <i class="fab fa-cc-discover text-2xl text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                        </label>

                        <!-- Bank Transfer -->
                        <label class="flex items-start p-4 border rounded-lg cursor-pointer hover:bg-gray-50 payment-method-option">
                            <input type="radio" name="payment_method" value="bank_transfer" class="mt-1">
                            <div class="ml-3 flex-1">
                                <span class="font-semibold">Bank Transfer (ACH)</span>
                                <p class="text-sm text-gray-600">Direct transfer from your bank account</p>
                            </div>
                        </label>

                        <!-- Mobile Money -->
                        <label class="flex items-start p-4 border rounded-lg cursor-pointer hover:bg-gray-50 payment-method-option">
                            <input type="radio" name="payment_method" value="mobile_money" class="mt-1">
                            <div class="ml-3 flex-1">
                                <span class="font-semibold">Mobile Money</span>
                                <p class="text-sm text-gray-600">Orange Money, MTN Mobile Money</p>
                            </div>
                        </label>

                        <!-- Payment Plan -->
                        @if($paymentPlanAvailable)
                        <label class="flex items-start p-4 border rounded-lg cursor-pointer hover:bg-gray-50 payment-method-option">
                            <input type="radio" name="payment_method" value="payment_plan" class="mt-1">
                            <div class="ml-3 flex-1">
                                <span class="font-semibold">Payment Plan</span>
                                <p class="text-sm text-gray-600">Pay in 3 installments over 3 months</p>
                                <div class="mt-2 text-sm">
                                    <div class="flex justify-between">
                                        <span>Today:</span>
                                        <span>${{ number_format($totalDue / 3, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>{{ now()->addMonth()->format('M j') }}:</span>
                                        <span>${{ number_format($totalDue / 3, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>{{ now()->addMonths(2)->format('M j') }}:</span>
                                        <span>${{ number_format($totalDue / 3, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </label>
                        @endif
                    </div>

                    <!-- Payment Details Forms (shown based on selected method) -->
                    
                    <!-- Credit Card Form -->
                    <div id="credit_card_form" class="mt-6 payment-details-form">
                        <h3 class="font-semibold mb-4">Card Information</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cardholder Name</label>
                                <input type="text" name="card_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="John Doe">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Card Number</label>
                                <input type="text" name="card_number" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="1234 5678 9012 3456" maxlength="19">
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Expiration Date</label>
                                    <input type="text" name="card_expiry" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="MM/YY" maxlength="5">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">CVV</label>
                                    <input type="text" name="card_cvv" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="123" maxlength="4">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bank Transfer Form -->
                    <div id="bank_transfer_form" class="mt-6 payment-details-form hidden">
                        <h3 class="font-semibold mb-4">Bank Account Information</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Account Holder Name</label>
                                <input type="text" name="account_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Routing Number</label>
                                <input type="text" name="routing_number" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" maxlength="9">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Account Number</label>
                                <input type="text" name="account_number" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Account Type</label>
                                <select name="account_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="checking">Checking</option>
                                    <option value="savings">Savings</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Money Form -->
                    <div id="mobile_money_form" class="mt-6 payment-details-form hidden">
                        <h3 class="font-semibold mb-4">Mobile Money Details</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Provider</label>
                                <select name="mobile_provider" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Select Provider</option>
                                    <option value="orange_money">Orange Money</option>
                                    <option value="mtn_mobile_money">MTN Mobile Money</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                <input type="tel" name="mobile_number" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="+231 770 123 456">
                            </div>
                        </div>
                    </div>

                    <!-- Billing Address -->
                    <div class="mt-6 border-t pt-6">
                        <h3 class="font-semibold mb-4">Billing Address</h3>
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                                    <input type="text" name="billing_first_name" value="{{ $application->first_name }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                                    <input type="text" name="billing_last_name" value="{{ $application->last_name }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Street Address</label>
                                <input type="text" name="billing_address" value="{{ $application->current_address }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                                    <input type="text" name="billing_city" value="{{ $application->city }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">State/Province</label>
                                    <input type="text" name="billing_state" value="{{ $application->state_province }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Postal Code</label>
                                    <input type="text" name="billing_postal" value="{{ $application->postal_code }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                                <select name="billing_country" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="{{ $application->country }}" selected>{{ $application->country }}</option>
                                    <!-- Add more countries as needed -->
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Agreement Checkbox -->
                    <div class="mt-6">
                        <label class="flex items-start">
                            <input type="checkbox" name="agreement" required class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-600">
                                I authorize {{ config('app.name') }} to charge the payment method provided for the enrollment deposit. 
                                I understand that this deposit is non-refundable after {{ $refundDeadline->format('F j, Y') }} and will be 
                                applied to my student account.
                            </span>
                        </label>
                    </div>

                    <!-- Error Messages -->
                    @if($errors->any())
                        <div class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                            <ul class="list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Submit Buttons -->
                    <div class="mt-8 flex justify-between">
                        <a href="{{ route('enrollment.confirmation', $enrollment->id) }}" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            <i class="fas fa-arrow-left mr-2"></i> Back
                        </a>
                        
                        <button type="submit" id="submitPayment" class="px-8 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-lock mr-2"></i> Pay ${{ number_format($totalDue, 2) }} Securely
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Security Notice -->
    <div class="bg-gray-50 rounded-lg p-6 mt-6">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i class="fas fa-shield-alt text-green-500 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-semibold text-gray-900">Secure Payment Processing</h3>
                <p class="mt-1 text-sm text-gray-600">
                    Your payment information is encrypted and processed securely. We use industry-standard SSL encryption 
                    to protect your personal and financial information. {{ config('app.name') }} does not store credit card 
                    information on our servers.
                </p>
            </div>
        </div>
    </div>

    <!-- Contact Information -->
    <div class="text-center mt-8 text-sm text-gray-600">
        <p>Questions about payment? Contact the Bursar's Office</p>
        <p class="mt-1">
            <i class="fas fa-phone mr-2"></i> (555) 123-4567
            <span class="mx-3">|</span>
            <i class="fas fa-envelope mr-2"></i> bursar@university.edu
        </p>
    </div>
</div>

<!-- Processing Modal -->
<div id="processingModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 mb-4">
                <i class="fas fa-spinner fa-spin text-blue-600 text-xl"></i>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900">Processing Payment</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    Please wait while we process your payment. Do not close this window or press the back button.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Payment method selection
    const paymentOptions = document.querySelectorAll('input[name="payment_method"]');
    const paymentForms = document.querySelectorAll('.payment-details-form');
    
    paymentOptions.forEach(option => {
        option.addEventListener('change', function() {
            // Hide all payment forms
            paymentForms.forEach(form => form.classList.add('hidden'));
            
            // Show selected payment form
            const selectedForm = document.getElementById(this.value + '_form');
            if (selectedForm) {
                selectedForm.classList.remove('hidden');
            }
        });
    });
    
    // Format card number input
    const cardNumberInput = document.querySelector('input[name="card_number"]');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            e.target.value = formattedValue;
        });
    }
    
    // Format expiry date input
    const expiryInput = document.querySelector('input[name="card_expiry"]');
    if (expiryInput) {
        expiryInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.slice(0, 2) + '/' + value.slice(2, 4);
            }
            e.target.value = value;
        });
    }
    
    // Form submission
    const paymentForm = document.getElementById('paymentForm');
    paymentForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate agreement checkbox
        const agreement = document.querySelector('input[name="agreement"]');
        if (!agreement.checked) {
            alert('Please agree to the terms before proceeding.');
            return;
        }
        
        // Show processing modal
        document.getElementById('processingModal').classList.remove('hidden');
        
        // Disable submit button
        document.getElementById('submitPayment').disabled = true;
        
        // Submit form
        this.submit();
    });
});
</script>
@endpush

@push('styles')
<style>
    .payment-method-option:has(input:checked) {
        @apply border-blue-500 bg-blue-50;
    }
</style>
@endpush