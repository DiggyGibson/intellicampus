@extends('layouts.app')

@section('title', 'Transcript Request Status')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Request Status Card -->
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-file-alt me-2"></i>Transcript Request #{{ $transcriptRequest->request_number }}
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Status Badge -->
                    <div class="text-center mb-4">
                        @switch($transcriptRequest->status)
                            @case('pending')
                                <span class="badge bg-warning fs-5 p-3">
                                    <i class="fas fa-clock me-2"></i>PENDING
                                </span>
                                <p class="text-muted mt-2">Your request is awaiting processing</p>
                                @break
                            @case('processing')
                                <span class="badge bg-info fs-5 p-3">
                                    <i class="fas fa-spinner fa-spin me-2"></i>PROCESSING
                                </span>
                                <p class="text-muted mt-2">Your transcript is being prepared</p>
                                @break
                            @case('completed')
                                <span class="badge bg-success fs-5 p-3">
                                    <i class="fas fa-check-circle me-2"></i>COMPLETED
                                </span>
                                <p class="text-muted mt-2">Your transcript has been processed and sent</p>
                                @break
                            @case('cancelled')
                                <span class="badge bg-danger fs-5 p-3">
                                    <i class="fas fa-times-circle me-2"></i>CANCELLED
                                </span>
                                <p class="text-muted mt-2">This request has been cancelled</p>
                                @break
                            @case('on_hold')
                                <span class="badge bg-secondary fs-5 p-3">
                                    <i class="fas fa-pause-circle me-2"></i>ON HOLD
                                </span>
                                <p class="text-muted mt-2">Your request is on hold. Please contact the registrar's office.</p>
                                @break
                        @endswitch
                    </div>

                    <!-- Request Details -->
                    <h5 class="mb-3">Request Details</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <td class="fw-bold">Student:</td>
                                    <td>
                                        {{ $transcriptRequest->student->user->name ?? 'Unknown' }}<br>
                                        <small class="text-muted">ID: {{ $transcriptRequest->student->student_id }}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Type:</td>
                                    <td>
                                        <span class="badge bg-{{ $transcriptRequest->type == 'official' ? 'primary' : 'secondary' }}">
                                            {{ ucfirst($transcriptRequest->type) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Copies:</td>
                                    <td>{{ $transcriptRequest->copies }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Delivery Method:</td>
                                    <td>{{ ucfirst($transcriptRequest->delivery_method) }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <td class="fw-bold">Recipient:</td>
                                    <td>{{ $transcriptRequest->recipient_name }}</td>
                                </tr>
                                @if($transcriptRequest->recipient_email)
                                <tr>
                                    <td class="fw-bold">Email:</td>
                                    <td>{{ $transcriptRequest->recipient_email }}</td>
                                </tr>
                                @endif
                                @if($transcriptRequest->mailing_address)
                                <tr>
                                    <td class="fw-bold">Address:</td>
                                    <td>{{ $transcriptRequest->mailing_address }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="fw-bold">Purpose:</td>
                                    <td>{{ $transcriptRequest->purpose }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($transcriptRequest->special_instructions)
                    <div class="alert alert-info">
                        <strong>Special Instructions:</strong><br>
                        {{ $transcriptRequest->special_instructions }}
                    </div>
                    @endif

                    @if($transcriptRequest->rush_order)
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i><strong>Rush Order</strong> - 
                        This request requires expedited processing within 1 business day.
                    </div>
                    @endif

                    <!-- Timeline -->
                    <h5 class="mb-3 mt-4">Processing Timeline</h5>
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <strong>Request Submitted</strong><br>
                                {{ $transcriptRequest->requested_at->format('M d, Y h:i A') }}<br>
                                <small class="text-muted">By: {{ $transcriptRequest->requestedBy->name ?? 'Unknown' }}</small>
                            </div>
                        </div>
                        
                        @if($transcriptRequest->processed_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <strong>Processing Started</strong><br>
                                {{ $transcriptRequest->processed_at->format('M d, Y h:i A') }}<br>
                                <small class="text-muted">By: {{ $transcriptRequest->processedBy->name ?? 'System' }}</small>
                            </div>
                        </div>
                        @endif
                        
                        @if($transcriptRequest->completed_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <strong>Completed</strong><br>
                                {{ $transcriptRequest->completed_at->format('M d, Y h:i A') }}
                                @if($transcriptRequest->tracking_number)
                                <br><small class="text-muted">Tracking: {{ $transcriptRequest->tracking_number }}</small>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Payment Information -->
                    @if($transcriptRequest->fee > 0)
                    <h5 class="mb-3 mt-4">Payment Information</h5>
                    <div class="card bg-light">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Fee Amount:</strong> ${{ number_format($transcriptRequest->fee, 2) }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Payment Status:</strong> 
                                        @switch($transcriptRequest->payment_status)
                                            @case('paid')
                                                <span class="badge bg-success">Paid</span>
                                                @break
                                            @case('pending')
                                                <span class="badge bg-warning">Pending</span>
                                                @break
                                            @case('waived')
                                                <span class="badge bg-info">Waived</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ ucfirst($transcriptRequest->payment_status) }}</span>
                                        @endswitch
                                    </p>
                                </div>
                            </div>
                            
                            @if($transcriptRequest->payment_status == 'pending' && Auth::user()->student && Auth::user()->student->id == $transcriptRequest->student_id)
                            <a href="{{ route('transcripts.payment', $transcriptRequest) }}" class="btn btn-success">
                                <i class="fas fa-credit-card me-2"></i>Make Payment
                            </a>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Admin Actions -->
                    @if(Auth::user()->hasRole(['super-administrator', 'admin', 'registrar']))
                    <div class="card border-warning mt-4">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0">Admin Actions</h6>
                        </div>
                        <div class="card-body">
                            @if($transcriptRequest->status == 'pending')
                            <form action="{{ route('transcripts.process', $transcriptRequest) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-info">
                                    <i class="fas fa-play me-2"></i>Start Processing
                                </button>
                            </form>
                            @endif
                            
                            @if($transcriptRequest->status == 'processing')
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#completeModal">
                                <i class="fas fa-check me-2"></i>Mark Complete
                            </button>
                            @endif
                            
                            <a href="{{ route('transcripts.generate-pdf', $transcriptRequest->student_id) }}?type={{ $transcriptRequest->type }}" 
                               class="btn btn-primary">
                                <i class="fas fa-file-pdf me-2"></i>Generate Transcript
                            </a>
                            
                            @if($transcriptRequest->notes)
                            <div class="alert alert-secondary mt-3">
                                <strong>Admin Notes:</strong><br>
                                {{ $transcriptRequest->notes }}
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-grid gap-2">
                <a href="{{ route('transcripts.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Transcript Services
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Complete Modal (Admin Only) -->
@if(Auth::user()->hasRole(['super-administrator', 'admin', 'registrar']))
<div class="modal fade" id="completeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('transcripts.complete', $transcriptRequest) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Complete Transcript Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="tracking_number" class="form-label">Tracking Number (Optional)</label>
                        <input type="text" name="tracking_number" id="tracking_number" class="form-control">
                        <small class="form-text text-muted">For mailed transcripts</small>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes (Optional)</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Mark as Complete</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 40px;
}
.timeline-item {
    position: relative;
    padding-bottom: 20px;
}
.timeline-item:not(:last-child):after {
    content: '';
    position: absolute;
    left: -30px;
    top: 20px;
    height: calc(100% - 20px);
    width: 2px;
    background: #dee2e6;
}
.timeline-marker {
    position: absolute;
    left: -35px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
}
</style>
@endpush
@endsection