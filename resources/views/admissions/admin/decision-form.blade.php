{{-- File: resources/views/admissions/admin/decision-form.blade.php --}}
@extends('layouts.app')

@section('title', 'Make Decision - ' . $application->application_number)

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-1">Make Admission Decision</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admissions.admin.dashboard') }}">Admissions</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admissions.admin.applications.index') }}">Applications</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admissions.admin.applications.show', $application->id) }}">{{ $application->application_number }}</a></li>
                    <li class="breadcrumb-item active">Decision</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- Alert for Previous Decision --}}
    @if($application->decision)
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle"></i> 
        <strong>Previous Decision:</strong> {{ ucfirst(str_replace('_', ' ', $application->decision)) }} 
        on {{ $application->decision_date->format('M d, Y') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form action="{{ route('admissions.admin.decisions.store', $application->id) }}" method="POST" id="decisionForm">
        @csrf
        
        <div class="row">
            {{-- Main Decision Panel --}}
            <div class="col-lg-8">
                {{-- Decision Selection Card --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-gavel"></i> Admission Decision</h5>
                    </div>
                    <div class="card-body">
                        <div class="decision-options">
                            {{-- Admit --}}
                            <div class="decision-option">
                                <input type="radio" class="btn-check" name="decision" id="admit" value="admit" 
                                       {{ old('decision') == 'admit' ? 'checked' : '' }} required>
                                <label class="btn btn-outline-success btn-lg w-100 mb-3" for="admit">
                                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                                    <h5>ADMIT</h5>
                                    <small>Full admission to the program</small>
                                </label>
                            </div>

                            {{-- Conditional Admit --}}
                            <div class="decision-option">
                                <input type="radio" class="btn-check" name="decision" id="conditional_admit" value="conditional_admit"
                                       {{ old('decision') == 'conditional_admit' ? 'checked' : '' }}>
                                <label class="btn btn-outline-info btn-lg w-100 mb-3" for="conditional_admit">
                                    <i class="fas fa-exclamation-circle fa-2x mb-2"></i>
                                    <h5>CONDITIONAL ADMIT</h5>
                                    <small>Admission with specific conditions</small>
                                </label>
                            </div>

                            {{-- Waitlist --}}
                            <div class="decision-option">
                                <input type="radio" class="btn-check" name="decision" id="waitlist" value="waitlist"
                                       {{ old('decision') == 'waitlist' ? 'checked' : '' }}>
                                <label class="btn btn-outline-warning btn-lg w-100 mb-3" for="waitlist">
                                    <i class="fas fa-clock fa-2x mb-2"></i>
                                    <h5>WAITLIST</h5>
                                    <small>Place on waiting list</small>
                                </label>
                            </div>

                            {{-- Deny --}}
                            <div class="decision-option">
                                <input type="radio" class="btn-check" name="decision" id="deny" value="deny"
                                       {{ old('decision') == 'deny' ? 'checked' : '' }}>
                                <label class="btn btn-outline-danger btn-lg w-100 mb-3" for="deny">
                                    <i class="fas fa-times-circle fa-2x mb-2"></i>
                                    <h5>DENY</h5>
                                    <small>Deny admission</small>
                                </label>
                            </div>

                            {{-- Defer --}}
                            <div class="decision-option">
                                <input type="radio" class="btn-check" name="decision" id="defer" value="defer"
                                       {{ old('decision') == 'defer' ? 'checked' : '' }}>
                                <label class="btn btn-outline-secondary btn-lg w-100 mb-3" for="defer">
                                    <i class="fas fa-forward fa-2x mb-2"></i>
                                    <h5>DEFER</h5>
                                    <small>Defer to future term</small>
                                </label>
                            </div>
                        </div>
                        @error('decision')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Conditional Admission Details (Hidden by default) --}}
                <div class="card shadow-sm mb-4" id="conditionalDetails" style="display: none;">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-list-check"></i> Admission Conditions</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="admission_conditions" class="form-label">Specify Conditions <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="admission_conditions" name="admission_conditions" rows="4"
                                      placeholder="List specific conditions that must be met...">{{ old('admission_conditions') }}</textarea>
                            <small class="text-muted">Example: Must complete English proficiency course, maintain minimum GPA, etc.</small>
                        </div>
                        <div class="mb-3">
                            <label for="condition_deadline" class="form-label">Deadline to Meet Conditions</label>
                            <input type="date" class="form-control" id="condition_deadline" name="condition_deadline" 
                                   value="{{ old('condition_deadline') }}" min="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                </div>

                {{-- Waitlist Details (Hidden by default) --}}
                <div class="card shadow-sm mb-4" id="waitlistDetails" style="display: none;">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0"><i class="fas fa-list-ol"></i> Waitlist Position</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="waitlist_rank" class="form-label">Waitlist Rank</label>
                                <input type="number" class="form-control" id="waitlist_rank" name="waitlist_rank" 
                                       min="1" value="{{ old('waitlist_rank', $suggestedWaitlistRank ?? 1) }}">
                                <small class="text-muted">Current waitlist size: {{ $waitlistCount ?? 0 }}</small>
                            </div>
                            <div class="col-md-6">
                                <label for="waitlist_expires" class="form-label">Waitlist Expires</label>
                                <input type="date" class="form-control" id="waitlist_expires" name="waitlist_expires" 
                                       value="{{ old('waitlist_expires', $defaultWaitlistExpiry ?? '') }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Deferral Details (Hidden by default) --}}
                <div class="card shadow-sm mb-4" id="deferDetails" style="display: none;">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Deferral Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="defer_to_term" class="form-label">Defer to Term <span class="text-danger">*</span></label>
                            <select class="form-select" id="defer_to_term" name="defer_to_term">
                                <option value="">Select Term</option>
                                @foreach($futureTerms as $term)
                                <option value="{{ $term->id }}" {{ old('defer_to_term') == $term->id ? 'selected' : '' }}>
                                    {{ $term->name }} ({{ $term->start_date->format('M Y') }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="defer_reason" class="form-label">Reason for Deferral</label>
                            <textarea class="form-control" id="defer_reason" name="defer_reason" rows="3"
                                      placeholder="Explain why the application is being deferred...">{{ old('defer_reason') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Decision Rationale Card --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-comment-alt"></i> Decision Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="decision_reason" class="form-label">Decision Rationale <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="decision_reason" name="decision_reason" rows="4" required
                                      placeholder="Provide detailed reasoning for this decision...">{{ old('decision_reason') }}</textarea>
                            <small class="text-muted">This will be part of the official record</small>
                            @error('decision_reason')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="internal_notes" class="form-label">Internal Notes (Optional)</label>
                            <textarea class="form-control" id="internal_notes" name="internal_notes" rows="3"
                                      placeholder="Additional notes for internal use only...">{{ old('internal_notes') }}</textarea>
                            <small class="text-muted">These notes will not be shared with the applicant</small>
                        </div>

                        {{-- Scholarship Consideration --}}
                        @if(in_array($application->application_type, ['freshman', 'transfer']))
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="consider_scholarship" name="consider_scholarship" value="1"
                                   {{ old('consider_scholarship') ? 'checked' : '' }}>
                            <label class="form-check-label" for="consider_scholarship">
                                Consider for merit-based scholarship
                            </label>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Notification Settings Card --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-envelope"></i> Notification Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="release_immediately" name="release_immediately" value="1"
                                           {{ old('release_immediately') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="release_immediately">
                                        <strong>Release decision immediately</strong>
                                        <small class="d-block text-muted">Send notification to applicant now</small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="notification_method" class="form-label">Notification Method</label>
                                <select class="form-select" id="notification_method" name="notification_method">
                                    <option value="email" {{ old('notification_method') == 'email' ? 'selected' : '' }}>Email Only</option>
                                    <option value="email_sms" {{ old('notification_method') == 'email_sms' ? 'selected' : '' }}>Email + SMS</option>
                                    <option value="portal" {{ old('notification_method') == 'portal' ? 'selected' : '' }}>Portal Only</option>
                                </select>
                            </div>
                        </div>

                        <div class="row" id="scheduledReleaseSection" style="display: none;">
                            <div class="col-md-6">
                                <label for="scheduled_release_date" class="form-label">Schedule Release Date</label>
                                <input type="date" class="form-control" id="scheduled_release_date" name="scheduled_release_date"
                                       value="{{ old('scheduled_release_date') }}" min="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="scheduled_release_time" class="form-label">Release Time</label>
                                <input type="time" class="form-control" id="scheduled_release_time" name="scheduled_release_time"
                                       value="{{ old('scheduled_release_time', '09:00') }}">
                            </div>
                        </div>

                        {{-- Enrollment Deadline (for admits) --}}
                        <div class="row mt-3" id="enrollmentDeadlineSection" style="display: none;">
                            <div class="col-md-6">
                                <label for="enrollment_deadline" class="form-label">Enrollment Confirmation Deadline</label>
                                <input type="date" class="form-control" id="enrollment_deadline" name="enrollment_deadline"
                                       value="{{ old('enrollment_deadline', $defaultEnrollmentDeadline ?? '') }}"
                                       min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                <small class="text-muted">Default: 30 days from notification</small>
                            </div>
                            <div class="col-md-6">
                                <label for="deposit_amount" class="form-label">Enrollment Deposit Required</label>
                                <input type="number" class="form-control" id="deposit_amount" name="deposit_amount"
                                       value="{{ old('deposit_amount', $defaultDepositAmount ?? 500) }}" min="0" step="0.01">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="card shadow-sm">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary btn-lg" id="submitDecision">
                            <i class="fas fa-gavel"></i> Submit Decision
                        </button>
                        <button type="button" class="btn btn-secondary btn-lg" onclick="previewDecision()">
                            <i class="fas fa-eye"></i> Preview Letter
                        </button>
                        <a href="{{ route('admissions.admin.applications.show', $application->id) }}" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </div>
            </div>

            {{-- Right Sidebar --}}
            <div class="col-lg-4">
                {{-- Application Summary Card --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-user"></i> Application Summary</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row small mb-0">
                            <dt class="col-6">Applicant:</dt>
                            <dd class="col-6">{{ $application->first_name }} {{ $application->last_name }}</dd>
                            
                            <dt class="col-6">Application #:</dt>
                            <dd class="col-6">{{ $application->application_number }}</dd>
                            
                            <dt class="col-6">Program:</dt>
                            <dd class="col-6">{{ $application->program->name ?? 'N/A' }}</dd>
                            
                            <dt class="col-6">Term:</dt>
                            <dd class="col-6">{{ $application->term->name ?? 'N/A' }}</dd>
                            
                            <dt class="col-6">Type:</dt>
                            <dd class="col-6">{{ ucfirst(str_replace('_', ' ', $application->application_type)) }}</dd>
                            
                            <dt class="col-6">GPA:</dt>
                            <dd class="col-6">{{ $application->previous_gpa ?? 'N/A' }}</dd>
                            
                            <dt class="col-6">Status:</dt>
                            <dd class="col-6">
                                @include('admissions.partials.status-badge', ['status' => $application->status])
                            </dd>
                        </dl>
                    </div>
                </div>

                {{-- Review Summary Card --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-star"></i> Review Summary</h5>
                    </div>
                    <div class="card-body">
                        @if($application->reviews->count() > 0)
                            <div class="text-center mb-3">
                                <h2 class="mb-0">{{ number_format($application->reviews->avg('overall_rating'), 1) }}</h2>
                                <div class="rating">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= round($application->reviews->avg('overall_rating')) ? 'text-warning' : 'text-muted' }}"></i>
                                    @endfor
                                </div>
                                <small class="text-muted">Average Rating ({{ $application->reviews->count() }} reviews)</small>
                            </div>

                            <h6>Recommendations:</h6>
                            <ul class="list-unstyled">
                                @foreach($application->reviews->groupBy('recommendation') as $recommendation => $reviews)
                                <li>
                                    <span class="badge bg-{{ $reviews->first()->getRecommendationColor() }}">
                                        {{ ucfirst(str_replace('_', ' ', $recommendation)) }}
                                    </span>
                                    <span class="text-muted">({{ $reviews->count() }})</span>
                                </li>
                                @endforeach
                            </ul>

                            <a href="{{ route('admissions.admin.reviews.compare', $application->id) }}" 
                               class="btn btn-sm btn-outline-primary w-100" target="_blank">
                                <i class="fas fa-balance-scale"></i> Compare All Reviews
                            </a>
                        @else
                            <p class="text-muted text-center mb-0">No reviews submitted</p>
                        @endif
                    </div>
                </div>

                {{-- Decision Guidelines Card --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Decision Guidelines</h5>
                    </div>
                    <div class="card-body small">
                        <h6>Admission Criteria:</h6>
                        <ul class="mb-3">
                            <li>Minimum GPA: {{ $admissionCriteria->min_gpa ?? '3.0' }}</li>
                            <li>Test Score Requirements: {{ $admissionCriteria->test_requirements ?? 'SAT 1200+ or ACT 25+' }}</li>
                            <li>English Proficiency: {{ $admissionCriteria->english_requirement ?? 'TOEFL 80+' }}</li>
                        </ul>

                        <h6>Consider:</h6>
                        <ul class="mb-0">
                            <li>Academic performance trend</li>
                            <li>Strength of recommendations</li>
                            <li>Essay quality and fit</li>
                            <li>Extracurricular involvement</li>
                            <li>Diversity contribution</li>
                            <li>Special circumstances</li>
                        </ul>
                    </div>
                </div>

                {{-- Quick Stats Card --}}
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Program Statistics</h5>
                    </div>
                    <div class="card-body small">
                        <dl class="row mb-0">
                            <dt class="col-7">Applications:</dt>
                            <dd class="col-5">{{ $programStats->total_applications ?? 0 }}</dd>
                            
                            <dt class="col-7">Admitted:</dt>
                            <dd class="col-5">{{ $programStats->admitted ?? 0 }}</dd>
                            
                            <dt class="col-7">Waitlisted:</dt>
                            <dd class="col-5">{{ $programStats->waitlisted ?? 0 }}</dd>
                            
                            <dt class="col-7">Denied:</dt>
                            <dd class="col-5">{{ $programStats->denied ?? 0 }}</dd>
                            
                            <dt class="col-7">Acceptance Rate:</dt>
                            <dd class="col-5">{{ $programStats->acceptance_rate ?? 0 }}%</dd>
                            
                            <dt class="col-7">Yield Rate:</dt>
                            <dd class="col-5">{{ $programStats->yield_rate ?? 0 }}%</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- Preview Modal --}}
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Decision Letter Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="letterPreview">
                {{-- Letter content will be loaded here --}}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .decision-option label {
        cursor: pointer;
        transition: all 0.3s;
        border-width: 2px;
    }
    .decision-option input[type="radio"]:checked + label {
        border-width: 3px;
        transform: scale(1.02);
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }
    .decision-option label:hover {
        transform: scale(1.01);
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Show/hide conditional sections based on decision
    $('input[name="decision"]').on('change', function() {
        const decision = $(this).val();
        
        // Hide all conditional sections first
        $('#conditionalDetails, #waitlistDetails, #deferDetails, #enrollmentDeadlineSection').hide();
        
        // Show relevant sections
        switch(decision) {
            case 'conditional_admit':
                $('#conditionalDetails').show();
                $('#enrollmentDeadlineSection').show();
                break;
            case 'waitlist':
                $('#waitlistDetails').show();
                break;
            case 'defer':
                $('#deferDetails').show();
                break;
            case 'admit':
                $('#enrollmentDeadlineSection').show();
                break;
        }
    });
    
    // Toggle scheduled release section
    $('#release_immediately').on('change', function() {
        if($(this).is(':checked')) {
            $('#scheduledReleaseSection').hide();
        } else {
            $('#scheduledReleaseSection').show();
        }
    });
    
    // Trigger change event on page load to show correct sections
    $('input[name="decision"]:checked').trigger('change');
    $('#release_immediately').trigger('change');
    
    // Form validation
    $('#decisionForm').on('submit', function(e) {
        const decision = $('input[name="decision"]:checked').val();
        
        if(decision === 'conditional_admit' && !$('#admission_conditions').val()) {
            e.preventDefault();
            alert('Please specify admission conditions for conditional admission.');
            $('#admission_conditions').focus();
            return false;
        }
        
        if(decision === 'defer' && !$('#defer_to_term').val()) {
            e.preventDefault();
            alert('Please select the term to defer to.');
            $('#defer_to_term').focus();
            return false;
        }
        
        if(!confirm('Are you sure you want to submit this decision? This action cannot be easily undone.')) {
            e.preventDefault();
            return false;
        }
    });
});

function previewDecision() {
    const decision = $('input[name="decision"]:checked').val();
    
    if(!decision) {
        alert('Please select a decision first.');
        return;
    }
    
    // Get form data
    const formData = $('#decisionForm').serialize();
    
    // Load preview via AJAX
    $.post('{{ route("admissions.admin.decisions.preview", $application->id) }}', formData)
        .done(function(response) {
            $('#letterPreview').html(response);
            $('#previewModal').modal('show');
        })
        .fail(function() {
            alert('Failed to generate preview. Please try again.');
        });
}
</script>
@endpush