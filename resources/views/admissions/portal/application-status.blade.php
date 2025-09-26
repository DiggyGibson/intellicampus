{{-- resources/views/admissions/portal/application-status.blade.php --}}
@extends('layouts.portal')

@section('title', 'Application Status - ' . $application->application_number)

@section('styles')
<style>
    .status-timeline {
        position: relative;
        padding: 20px 0;
    }
    
    .status-timeline::before {
        content: '';
        position: absolute;
        left: 50%;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #dee2e6;
        transform: translateX(-50%);
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 30px;
    }
    
    .timeline-icon {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #fff;
        border: 3px solid #dee2e6;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1;
    }
    
    .timeline-item.completed .timeline-icon {
        background: #28a745;
        border-color: #28a745;
        color: #fff;
    }
    
    .timeline-item.current .timeline-icon {
        background: #007bff;
        border-color: #007bff;
        color: #fff;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.7);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(0, 123, 255, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(0, 123, 255, 0);
        }
    }
    
    .timeline-content {
        width: 45%;
        padding: 15px;
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 8px;
    }
    
    .timeline-item:nth-child(odd) .timeline-content {
        margin-left: 5%;
    }
    
    .timeline-item:nth-child(even) .timeline-content {
        margin-left: 50%;
    }
    
    .status-card {
        border-left: 4px solid;
        transition: transform 0.3s;
    }
    
    .status-card:hover {
        transform: translateX(5px);
    }
    
    .status-card.submitted { border-left-color: #17a2b8; }
    .status-card.under_review { border-left-color: #ffc107; }
    .status-card.admitted { border-left-color: #28a745; }
    .status-card.waitlisted { border-left-color: #fd7e14; }
    .status-card.denied { border-left-color: #dc3545; }
    
    .action-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
    }
    
    .document-status {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-right: 5px;
    }
    
    .document-status.verified { background: #28a745; }
    .document-status.pending { background: #ffc107; }
    .document-status.missing { background: #dc3545; }
    
    @media (max-width: 768px) {
        .status-timeline::before {
            left: 30px;
        }
        
        .timeline-icon {
            left: 30px;
        }
        
        .timeline-content {
            width: calc(100% - 80px);
            margin-left: 80px !important;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="h3 font-weight-bold text-gray-800">
                        <i class="fas fa-chart-line me-2"></i>Application Status
                    </h2>
                    <p class="text-muted mb-0">
                        Track your application progress in real-time
                    </p>
                </div>
                <div>
                    <button class="btn btn-outline-primary" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Print Status
                    </button>
                    <a href="{{ route('admissions.portal.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-home me-1"></i> Portal Home
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Current Status Overview --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card shadow status-card {{ $application->status }}">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="display-4">
                                @switch($application->status)
                                    @case('draft')
                                        <i class="fas fa-edit text-secondary"></i>
                                        @break
                                    @case('submitted')
                                        <i class="fas fa-check-circle text-info"></i>
                                        @break
                                    @case('under_review')
                                        <i class="fas fa-hourglass-half text-warning"></i>
                                        @break
                                    @case('admitted')
                                        <i class="fas fa-graduation-cap text-success"></i>
                                        @break
                                    @case('waitlisted')
                                        <i class="fas fa-list-ol text-orange"></i>
                                        @break
                                    @case('denied')
                                        <i class="fas fa-times-circle text-danger"></i>
                                        @break
                                    @default
                                        <i class="fas fa-question-circle text-muted"></i>
                                @endswitch
                            </div>
                        </div>
                        <div class="col">
                            <h4 class="mb-1">Current Status: 
                                <span class="badge bg-{{ $application->status == 'draft' ? 'warning' : ($application->status == 'submitted' ? 'success' : 'info') }} px-3 py-2">
                                    {{ ucwords(str_replace('_', ' ', $application->status)) }}
                                </span>
                            </h4>
                            <p class="mb-2">
                                <strong>Application #:</strong> {{ $application->application_number }}<br>
                                <strong>Program:</strong> {{ $application->program->name ?? 'Not Selected' }}<br>
                                <strong>Term:</strong> {{ $application->term->name ?? 'Not Selected' }}
                            </p>
                            <div class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                Last Updated: {{ $application->updated_at?->diffForHumans() ?? 'Never' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            {{-- Next Steps Card --}}
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-tasks me-2"></i>Next Steps
                    </h6>
                </div>
                <div class="card-body">
                    @if($application->status == 'draft')
                        <p class="small">Complete and submit your application:</p>
                        <a href="{{ route('admissions.portal.continue', ['uuid' => $application->application_uuid]) }}"
                            class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-edit me-1"></i> Continue Application
                        </a>
                    @elseif($application->status == 'submitted')
                        <p class="small">Your application is being processed. Please check back regularly for updates.</p>
                        @if(!$application->application_fee_paid)
                            <div class="alert alert-warning mt-2">
                                <small>Application fee payment pending</small>
                            </div>
                        @endif
                    @elseif($application->status == 'under_review')
                        <p class="small">Your application is currently under review by our admissions committee.</p>
                    @elseif($application->status == 'admitted')
                        <div class="alert alert-success">
                            <strong>Congratulations!</strong> You have been admitted.
                        </div>
                        @if(!$application->enrollment_confirmed)
                            @if(Route::has('enrollment.portal.dashboard'))
                                <a href="{{ route('enrollment.portal.dashboard') }}"
                                    class="btn btn-success btn-sm w-100 mt-2">
                                    <i class="fas fa-check me-1"></i> Go to Enrollment Portal
                                </a>
                            @else
                                <p class="small text-muted">Enrollment portal will be available soon.</p>
                            @endif
                        @endif
                    @elseif($application->status == 'waitlisted')
                        <div class="alert alert-info">
                            You have been placed on the waitlist. We will notify you if a spot becomes available.
                        </div>
                    @elseif($application->status == 'denied')
                        <div class="alert alert-danger">
                            Unfortunately, your application was not successful. Thank you for your interest.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Status Timeline --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>Application Timeline
                    </h5>
                </div>
                <div class="card-body">
                    <div class="status-timeline">
                        {{-- Application Started --}}
                        <div class="timeline-item completed">
                            <div class="timeline-icon">
                                <i class="fas fa-play"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Application Started</h6>
                                <p class="text-muted mb-0">{{ $application->created_at->format('F d, Y g:i A') }}</p>
                            </div>
                        </div>

                        {{-- Application Submitted --}}
                        <div class="timeline-item {{ $application->submitted_at ? 'completed' : '' }}">
                            <div class="timeline-icon">
                                <i class="fas fa-paper-plane"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Application Submitted</h6>
                                @if($application->submitted_at)
                                    <p class="text-muted mb-0">{{ $application->submitted_at->format('F d, Y g:i A') }}</p>
                                @else
                                    <p class="text-muted mb-0">Pending submission</p>
                                @endif
                            </div>
                        </div>

                        {{-- Under Review --}}
                        <div class="timeline-item {{ in_array($application->status, ['under_review', 'committee_review', 'decision_pending', 'admitted', 'denied', 'waitlisted']) ? 'completed' : '' }} {{ $application->status == 'under_review' ? 'current' : '' }}">
                            <div class="timeline-icon">
                                <i class="fas fa-search"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Under Review</h6>
                                <p class="text-muted mb-0">
                                    @if(isset($application->reviews) && $application->reviews->count() > 0)
                                        {{ $application->reviews->count() }} review(s) completed
                                    @else
                                        Review pending
                                    @endif
                                </p>
                            </div>
                        </div>

                        {{-- Decision Made --}}
                        <div class="timeline-item {{ $application->decision ? 'completed' : '' }}">
                            <div class="timeline-icon">
                                <i class="fas fa-gavel"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Decision Made</h6>
                                @if($application->decision_date)
                                    <p class="text-muted mb-0">{{ $application->decision_date->format('F d, Y') }}</p>
                                    <p class="mb-0"><strong>{{ ucwords(str_replace('_', ' ', $application->decision)) }}</strong></p>
                                @else
                                    <p class="text-muted mb-0">Pending decision</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Documents Status --}}
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-file-alt me-2"></i>Document Status
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($application->documents) && $application->documents->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Document</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($application->documents as $document)
                                    <tr>
                                        <td>
                                            <span class="document-status {{ $document->status }}"></span>
                                            {{ ucwords(str_replace('_', ' ', $document->document_type)) }}
                                        </td>
                                        <td>
                                            @switch($document->status)
                                                @case('verified')
                                                    <span class="badge bg-success">Verified</span>
                                                    @break
                                                @case('pending_verification')
                                                    <span class="badge bg-warning">Under Review</span>
                                                    @break
                                                @case('rejected')
                                                    <span class="badge bg-danger">Rejected</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">Uploaded</span>
                                            @endswitch
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        @if($application->status == 'draft')
                        <a href="{{ route('admissions.portal.application.documents', ['uuid' => $application->application_uuid]) }}" 
                           class="btn btn-outline-primary btn-sm mt-2">
                            <i class="fas fa-upload me-1"></i> Manage Documents
                        </a>
                        @endif
                    @else
                        <p class="text-muted mb-0">No documents uploaded yet.</p>
                        @if($application->status == 'draft')
                        <a href="{{ route('admissions.portal.application.documents', ['uuid' => $application->application_uuid]) }}"
                           class="btn btn-primary btn-sm mt-3">
                            <i class="fas fa-upload me-1"></i> Upload Documents
                        </a>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        {{-- Additional Information --}}
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Fee Status
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Application Fee:</span>
                        @if($application->application_fee_paid)
                            <span class="badge bg-success">
                                <i class="fas fa-check"></i> Paid
                            </span>
                        @else
                            <span class="badge bg-warning">
                                <i class="fas fa-clock"></i> Pending
                            </span>
                        @endif
                    </div>
                    
                    @if(!$application->application_fee_paid && $application->status == 'draft')
                        <a href="{{ route('admissions.portal.application.payment', ['uuid' => $application->application_uuid]) }}"
                            class="btn btn-warning btn-sm w-100 mt-2">
                            <i class="fas fa-credit-card me-1"></i> Pay Application Fee
                        </a>
                    @endif
                    
                    @if($application->fee_waiver_approved)
                        <div class="alert alert-info mt-2">
                            <small>Fee waiver approved</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            @if($application->status == 'draft')
                                <a href="{{ route('admissions.portal.continue', ['uuid' => $application->application_uuid]) }}" 
                                   class="btn btn-primary">
                                    <i class="fas fa-edit me-1"></i> Continue Application
                                </a>
                            @endif
                            
                            @if($application->submitted_at)
                                <a href="{{ route('admissions.portal.application.print', ['uuid' => $application->application_uuid]) }}" 
                                   class="btn btn-outline-secondary">
                                    <i class="fas fa-download me-1"></i> Download Application
                                </a>
                            @endif
                        </div>
                        
                        <div>
                            @if($application->status == 'draft' || $application->status == 'submitted')
                                <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#withdrawModal">
                                    <i class="fas fa-times me-1"></i> Withdraw Application
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Withdraw Application Modal --}}
@if($application->status == 'draft' || $application->status == 'submitted')
<div class="modal fade" id="withdrawModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>Withdraw Application
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admissions.portal.withdraw', ['uuid' => $application->application_uuid]) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p><strong>Are you sure you want to withdraw your application?</strong></p>
                    <p class="text-muted">This action cannot be undone. You will need to submit a new application if you wish to apply again.</p>
                    
                    <div class="form-group">
                        <label for="withdrawal_reason">Reason for withdrawal (optional)</label>
                        <textarea class="form-control" id="withdrawal_reason" name="withdrawal_reason" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times me-1"></i> Withdraw Application
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Initialize tooltips if any
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
});
</script>
@endsection