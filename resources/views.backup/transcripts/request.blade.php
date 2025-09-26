@extends('layouts.app')

@section('title', 'Request Official Transcript')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Page Header -->
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-file-signature me-2"></i>Request Official Transcript</h4>
                </div>
                <div class="card-body">
                    <!-- Alert Messages -->
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Pending Requests Warning -->
                    @if($pendingRequests->isNotEmpty())
                        <div class="alert alert-warning">
                            <h6 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>You have pending transcript requests:</h6>
                            <ul class="mb-0">
                                @foreach($pendingRequests as $pending)
                                <li>
                                    Request #{{ $pending->request_number }} - {{ ucfirst($pending->type) }} 
                                    ({{ $pending->created_at->format('M d, Y') }})
                                    <a href="{{ route('transcripts.request.status', $pending) }}" class="btn btn-sm btn-link">View Status</a>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Request Form -->
                    <form action="{{ route('transcripts.request.submit') }}" method="POST" id="transcriptRequestForm">
                        @csrf
                        <input type="hidden" name="type" value="official">
                        
                        <!-- Delivery Information -->
                        <h5 class="mb-3">Delivery Information</h5>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="delivery_method" class="form-label">Delivery Method <span class="text-danger">*</span></label>
                                <select name="delivery_method" id="delivery_method" class="form-select @error('delivery_method') is-invalid @enderror" required>
                                    <option value="">Select delivery method...</option>
                                    <option value="electronic" {{ old('delivery_method') == 'electronic' ? 'selected' : '' }}>
                                        Electronic (Email) - No additional charge
                                    </option>
                                    <option value="mail" {{ old('delivery_method') == 'mail' ? 'selected' : '' }}>
                                        Mail (Postal) - $10 shipping
                                    </option>
                                    <option value="pickup" {{ old('delivery_method') == 'pickup' ? 'selected' : '' }}>
                                        Pickup (In Person) - No additional charge
                                    </option>
                                </select>
                                @error('delivery_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="copies" class="form-label">Number of Copies <span class="text-danger">*</span></label>
                                <input type="number" name="copies" id="copies" 
                                       class="form-control @error('copies') is-invalid @enderror" 
                                       min="1" max="10" value="{{ old('copies', 1) }}" required>
                                @error('copies')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">$10 per copy</small>
                            </div>
                        </div>
                        
                        <!-- Recipient Information -->
                        <h5 class="mb-3 mt-4">Recipient Information</h5>
                        
                        <div class="mb-3">
                            <label for="recipient_name" class="form-label">Recipient Name/Organization <span class="text-danger">*</span></label>
                            <input type="text" name="recipient_name" id="recipient_name" 
                                   class="form-control @error('recipient_name') is-invalid @enderror" 
                                   value="{{ old('recipient_name') }}" required>
                            @error('recipient_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3" id="email-field" style="display: none;">
                            <label for="recipient_email" class="form-label">Recipient Email <span class="text-danger">*</span></label>
                            <input type="email" name="recipient_email" id="recipient_email" 
                                   class="form-control @error('recipient_email') is-invalid @enderror" 
                                   value="{{ old('recipient_email') }}">
                            @error('recipient_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Transcript will be sent securely to this email address</small>
                        </div>
                        
                        <div class="mb-3" id="address-field" style="display: none;">
                            <label for="mailing_address" class="form-label">Mailing Address <span class="text-danger">*</span></label>
                            <textarea name="mailing_address" id="mailing_address" 
                                      class="form-control @error('mailing_address') is-invalid @enderror" 
                                      rows="4">{{ old('mailing_address') }}</textarea>
                            @error('mailing_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Include complete address with ZIP/Postal code</small>
                        </div>
                        
                        <!-- Request Details -->
                        <h5 class="mb-3 mt-4">Request Details</h5>
                        
                        <div class="mb-3">
                            <label for="purpose" class="form-label">Purpose of Request <span class="text-danger">*</span></label>
                            <select name="purpose" id="purpose" class="form-select @error('purpose') is-invalid @enderror" required>
                                <option value="">Select purpose...</option>
                                <option value="Employment" {{ old('purpose') == 'Employment' ? 'selected' : '' }}>Employment</option>
                                <option value="Graduate School" {{ old('purpose') == 'Graduate School' ? 'selected' : '' }}>Graduate School</option>
                                <option value="Transfer to Another Institution" {{ old('purpose') == 'Transfer to Another Institution' ? 'selected' : '' }}>Transfer to Another Institution</option>
                                <option value="Professional Licensing" {{ old('purpose') == 'Professional Licensing' ? 'selected' : '' }}>Professional Licensing</option>
                                <option value="Scholarship Application" {{ old('purpose') == 'Scholarship Application' ? 'selected' : '' }}>Scholarship Application</option>
                                <option value="Immigration" {{ old('purpose') == 'Immigration' ? 'selected' : '' }}>Immigration</option>
                                <option value="Personal Records" {{ old('purpose') == 'Personal Records' ? 'selected' : '' }}>Personal Records</option>
                                <option value="Other" {{ old('purpose') == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('purpose')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="special_instructions" class="form-label">Special Instructions (Optional)</label>
                            <textarea name="special_instructions" id="special_instructions" 
                                      class="form-control @error('special_instructions') is-invalid @enderror" 
                                      rows="3">{{ old('special_instructions') }}</textarea>
                            @error('special_instructions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-check mb-3">
                            <input type="checkbox" name="rush_order" id="rush_order" 
                                   class="form-check-input" value="1" {{ old('rush_order') ? 'checked' : '' }}>
                            <label for="rush_order" class="form-check-label">
                                <strong>Rush Order</strong> - Process within 1 business day (Additional $25 fee)
                            </label>
                        </div>
                        
                        <!-- Fee Summary -->
                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Fee Summary</h5>
                                <table class="table table-sm mb-0">
                                    <tr>
                                        <td>Transcript Fee:</td>
                                        <td class="text-end">$<span id="transcript-fee">10</span></td>
                                    </tr>
                                    <tr id="shipping-row" style="display: none;">
                                        <td>Shipping Fee:</td>
                                        <td class="text-end">$<span id="shipping-fee">0</span></td>
                                    </tr>
                                    <tr id="rush-row" style="display: none;">
                                        <td>Rush Processing:</td>
                                        <td class="text-end">$25</td>
                                    </tr>
                                    <tr class="fw-bold">
                                        <td>Total:</td>
                                        <td class="text-end">$<span id="total-fee">10</span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Terms and Submit -->
                        <div class="form-check mb-3">
                            <input type="checkbox" id="terms" class="form-check-input" required>
                            <label for="terms" class="form-check-label">
                                I understand that official transcripts will not be released if there are any holds on my account or outstanding balances.
                            </label>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Submit Request
                            </button>
                            <a href="{{ route('transcripts.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Transcript Services
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Request History -->
            @if($requestHistory->isNotEmpty())
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0">Request History</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Request #</th>
                                    <th>Type</th>
                                    <th>Recipient</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requestHistory as $history)
                                <tr>
                                    <td>{{ $history->request_number }}</td>
                                    <td>{{ ucfirst($history->type) }}</td>
                                    <td>{{ $history->recipient_name }}</td>
                                    <td>{{ $history->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $history->status == 'completed' ? 'success' : ($history->status == 'pending' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($history->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('transcripts.request.status', $history) }}" class="btn btn-sm btn-outline-primary">
                                            View
                                        </a>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const deliveryMethod = document.getElementById('delivery_method');
    const emailField = document.getElementById('email-field');
    const addressField = document.getElementById('address-field');
    const recipientEmail = document.getElementById('recipient_email');
    const mailingAddress = document.getElementById('mailing_address');
    const copies = document.getElementById('copies');
    const rushOrder = document.getElementById('rush_order');
    const shippingRow = document.getElementById('shipping-row');
    const rushRow = document.getElementById('rush-row');
    
    // Calculate fees
    function calculateFees() {
        let total = parseInt(copies.value) * 10;
        document.getElementById('transcript-fee').textContent = parseInt(copies.value) * 10;
        
        if (deliveryMethod.value === 'mail') {
            total += 10;
            document.getElementById('shipping-fee').textContent = '10';
            shippingRow.style.display = 'table-row';
        } else {
            shippingRow.style.display = 'none';
        }
        
        if (rushOrder.checked) {
            total += 25;
            rushRow.style.display = 'table-row';
        } else {
            rushRow.style.display = 'none';
        }
        
        document.getElementById('total-fee').textContent = total;
    }
    
    // Show/hide fields based on delivery method
    deliveryMethod.addEventListener('change', function() {
        emailField.style.display = 'none';
        addressField.style.display = 'none';
        recipientEmail.required = false;
        mailingAddress.required = false;
        
        if (this.value === 'electronic') {
            emailField.style.display = 'block';
            recipientEmail.required = true;
        } else if (this.value === 'mail') {
            addressField.style.display = 'block';
            mailingAddress.required = true;
        }
        
        calculateFees();
    });
    
    // Update fees when copies or rush order changes
    copies.addEventListener('change', calculateFees);
    rushOrder.addEventListener('change', calculateFees);
    
    // Initialize on page load
    if (deliveryMethod.value) {
        deliveryMethod.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
@endsection