{{-- resources/views/admissions/portal/application-status.blade.php --}}
@extends('layouts.app')

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
                    <a href="{{ route('admissions.portal.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-home me-1"></i> Dashboard
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
                                <span class="badge bg-{{ $application->getStatusColor() }} px-3 py-2">
                                    {{ ucwords(str_replace('_', ' ', $application->status)) }}
                                </span>
                            </h4>
                            <p class="mb-2">
                                <strong>Application #:</strong> {{ $application->application_number }}<br>
                                <strong>Program:</strong> {{ $application->program->name }}<br>
                                <strong>Term:</strong> {{ $application->term->name }}
                            </p>
                            <div class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                Last Updated: {{ $application->last_updated_at?->diffForHumans() ?? 'Never' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            {{-- Decision Card (if available) --}}
            @if($application->decision)
            <div class="card shadow action-card">
                <div class="card-body text-center">
                    <h5 class="text-white mb-3">Admission Decision</h5>
                    <div class="display-6 mb-3">
                        @switch($application->decision)
                            @case('admit')
                                <i class="fas fa-check-circle"></i>
                                @break
                            @case('conditional_admit')
                                <i class="fas fa-exclamation-circle"></i>
                                @break
                            @case('waitlist')
                                <i class="fas fa-list"></i>
                                @break
                            @case('deny')
                                <i class="fas fa-times-circle"></i>
                                @break
                            @case('defer')
                                <i class="fas fa-clock"></i>
                                @break
                        @endswitch
                    </div>
                    <h4 class="text-white">{{ ucwords(str_replace('_', ' ', $application->decision)) }}</h4>
                    @if($application->decision_date)
                    <p class="mb-0">Decision Date: {{ $application->decision_date->format('F d, Y') }}</p>
                    @endif
                    
                    @if($application->decision == 'admit' || $application->decision == 'conditional_admit')
                    <hr class="bg-white">
                    <a href="{{ route('admissions.enrollment.confirm', $application->id) }}" 
                       class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-right me-1"></i> Proceed to Enrollment
                    </a>
                    @endif
                </div>
            </div>
            @else
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
                        <a href="{{ route('admissions.form.show', $application->id) }}" 
                           class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-edit me-1"></i> Continue Application
                        </a>
                    @elseif($application->status == 'submitted')
                        <p class="small">Your application is being processed. Please check back regularly for updates.</p>
                        @if(!$application->application_fee_paid)
                        <div class="alert alert-warning small mt-2 mb-0">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Application fee payment pending
                        </div>
                        @endif
                    @elseif($application->status == 'documents_pending')
                        <p class="small">Additional documents required:</p>
                        <a href="{{ route('admissions.document.upload', $application->id) }}" 
                           class="btn btn-warning btn-sm w-100">
                            <i class="fas fa-upload me-1"></i> Upload Documents
                        </a>
                    @elseif($application->status == 'interview_scheduled')
                        <p class="small">Interview scheduled for:</p>
                        <div class="alert alert-info small">
                            {{ $application->interview?->scheduled_at->format('F d, Y g:i A') }}
                        </div>
                    @endif
                </div>
            </div>
            @endif
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
                                <p class="text-muted mb-0">{{ $application->started_at->format('F d, Y g:i A') }}</p>
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
                                    @if($application->reviews->count() > 0)
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

                        {{-- Enrollment (if admitted) --}}
                        @if(in_array($application->decision, ['admit', 'conditional_admit']))
                        <div class="timeline-item {{ $application->enrollment_confirmed ? 'completed' : '' }}">
                            <div class="timeline-icon">
                                <i class="fas fa-university"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Enrollment Confirmation</h6>
                                @if($application->enrollment_confirmed)
                                    <p class="text-muted mb-0">{{ $application->enrollment_confirmation_date->format('F d, Y') }}</p>
                                @else
                                    <p class="text-muted mb-0">Awaiting confirmation</p>
                                    @if($application->enrollment_deadline)
                                        <p class="small text-danger mb-0">Deadline: {{ $application->enrollment_deadline->format('F d, Y') }}</p>
                                    @endif
                                @endif
                            </div>
                        </div>
                        @endif
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
                    @if($application->documents->count() > 0)
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
                        
                        @if($application->status != 'draft')
                        <a href="{{ route('admissions.document.upload', $application->id) }}" 
                           class="btn btn-outline-primary btn-sm mt-2">
                            <i class="fas fa-upload me-1"></i> Manage Documents
                        </a>
                        @endif
                    @else
                        <p class="text-muted mb-0">No documents uploaded yet.</p>
                        @if($application->status == 'draft')
                        <a href="{{ route('admissions.document.upload', $application->id) }}" 
                           class="btn btn-primary btn-sm mt-3">
                            <i class="fas fa-upload me-1"></i> Upload Documents
                        </a>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        {{-- Communications --}}
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-envelope me-2"></i>Recent Communications
                    </h5>
                </div>
                <div class="card-body">
                    @if($application->communications->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($application->communications->take(5) as $communication)
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between">
                                    <h6 class="mb-1">{{ $communication->subject }}</h6>
                                    <small>{{ $communication->created_at->format('M d') }}</small>
                                </div>
                                <p class="mb-1 small text-muted">{{ Str::limit($communication->message, 100) }}</p>
                                <small>
                                    <span class="badge bg-{{ $communication->direction == 'outbound' ? 'info' : 'secondary' }}">
                                        {{ ucfirst($communication->direction) }}
                                    </span>
                                    <span class="badge bg-{{ $communication->status == 'sent' ? 'success' : 'warning' }}">
                                        {{ ucfirst($communication->status) }}
                                    </span>
                                </small>
                            </div>
                            @endforeach
                        </div>
                        
                        @if($application->communications->count() > 5)
                        <a href="{{ route('admissions.communications', $application->id) }}" 
                           class="btn btn-link btn-sm mt-2">
                            View All Communications →
                        </a>
                        @endif
                    @else
                        <p class="text-muted mb-0">No communications yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Additional Information --}}
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Additional Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="text-muted small">Application Fee</label>
                            <div>
                                @if($application->application_fee_paid)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check"></i> Paid
                                    </span>
                                    <small class="text-muted">
                                        ({{ $application->application_fee_date?->format('M d, Y') }})
                                    </small>
                                @else
                                    <span class="badge bg-warning">
                                        <i class="fas fa-clock"></i> Pending
                                    </span>
                                    @if($application->fee_waiver_approved)
                                        <span class="badge bg-info ms-1">Fee Waived</span>
                                    @else
                                        <a href="{{ route('admissions.fee.pay', $application->id) }}" 
                                           class="btn btn-link btn-sm p-0">
                                            Pay Now →
                                        </a>
                                    @endif
                                @endif
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label class="text-muted small">Interview Status</label>
                            <div>
                                @if($application->interviews->count() > 0)
                                    @php $interview = $application->interviews->first(); @endphp
                                    <span class="badge bg-{{ $interview->status == 'completed' ? 'success' : 'info' }}">
                                        {{ ucfirst($interview->status) }}
                                    </span>
                                    @if($interview->scheduled_at)
                                        <small class="text-muted d-block">
                                            {{ $interview->scheduled_at->format('M d, Y g:i A') }}
                                        </small>
                                    @endif
                                @else
                                    <span class="text-muted">Not required</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label class="text-muted small">Waitlist Position</label>
                            <div>
                                @if($application->waitlist)
                                    <span class="badge bg-warning">
                                        #{{ $application->waitlist->rank }}
                                    </span>
                                    <small class="text-muted">
                                        (as of {{ $application->waitlist->updated_at->format('M d') }})
                                    </small>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label class="text-muted small">Application Expires</label>
                            <div>
                                @if($application->expires_at)
                                    @if($application->expires_at->isPast())
                                        <span class="badge bg-danger">Expired</span>
                                    @else
                                        <span>{{ $application->expires_at->format('F d, Y') }}</span>
                                        <small class="text-muted d-block">
                                            ({{ $application->expires_at->diffForHumans() }})
                                        </small>
                                    @endif
                                @else
                                    <span class="text-muted">No expiry</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    {{-- Action Buttons --}}
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            @if($application->status == 'draft')
                                <a href="{{ route('admissions.form.show', $application->id) }}" 
                                   class="btn btn-primary">
                                    <i class="fas fa-edit me-1"></i> Continue Application
                                </a>
                            @elseif(in_array($application->decision, ['admit', 'conditional_admit']) && !$application->enrollment_confirmed)
                                <a href="{{ route('admissions.enrollment.confirm', $application->id) }}" 
                                   class="btn btn-success">
                                    <i class="fas fa-check me-1"></i> Confirm Enrollment
                                </a>
                            @endif
                            
                            <a href="{{ route('admissions.application.download', $application->id) }}" 
                               class="btn btn-outline-secondary">
                                <i class="fas fa-download me-1"></i> Download Application
                            </a>
                        </div>
                        
                        <div>
                            @if($application->status != 'draft' && !in_array($application->status, ['admitted', 'denied']))
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
<div class="modal fade" id="withdrawModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>Withdraw Application
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admissions.application.withdraw', $application->id) }}" method="POST">
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
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Auto-refresh status every 30 seconds if application is under review
    @if(in_array($application->status, ['submitted', 'under_review', 'committee_review', 'decision_pending']))
    setInterval(function() {
        $.ajax({
            url: '{{ route("admissions.status.check", $application->id) }}',
            method: 'GET',
            success: function(response) {
                if (response.status !== '{{ $application->status }}') {
                    // Status has changed, reload page
                    location.reload();
                }
            }
        });
    }, 30000); // 30 seconds
    @endif
    
    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>
@endsection