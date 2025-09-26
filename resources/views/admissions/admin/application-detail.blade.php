{{-- File: resources/views/admissions/admin/application-detail.blade.php --}}
@extends('layouts.admin')

@section('title', 'Application Details - ' . $application->application_number)

@section('content')
<div class="container-fluid py-4">
    {{-- Header Section --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-2">Application Details</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.admissions.dashboard') }}">Admissions</a></li>
                            <li class="breadcrumb-item active">{{ $application->application_number }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-cog"></i> Actions
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#" onclick="printApplication()">
                            <i class="fas fa-print"></i> Print Application</a></li>
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#exportModal">
                            <i class="fas fa-download"></i> Export as PDF</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="#" onclick="flagApplication()">
                            <i class="fas fa-flag"></i> Flag for Review</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Status Bar --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body py-3">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <strong>Status:</strong>
                            <span class="badge bg-{{ $application->status === 'submitted' ? 'info' : 
                                                    ($application->status === 'under_review' ? 'warning' : 
                                                    ($application->status === 'admitted' ? 'success' : 'secondary')) }} ms-2">
                                {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                            </span>
                        </div>
                        <div class="col-md-3">
                            <strong>Decision:</strong>
                            @if($application->decision)
                                <span class="badge bg-{{ $application->decision === 'admitted' ? 'success' : 
                                                        ($application->decision === 'denied' ? 'danger' : 'warning') }} ms-2">
                                    {{ ucfirst(str_replace('_', ' ', $application->decision)) }}
                                </span>
                            @else
                                <span class="text-muted ms-2">Pending</span>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <strong>Submitted:</strong>
                            <span class="ms-2">{{ $application->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Days in Review:</strong>
                            <span class="ms-2">{{ $application->created_at->diffInDays(now()) }} days</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Left Column - Applicant Information --}}
        <div class="col-lg-8">
            {{-- Basic Information --}}
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user"></i> Applicant Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-5">Full Name:</dt>
                                <dd class="col-sm-7">{{ $application->first_name }} {{ $application->middle_name }} {{ $application->last_name }}</dd>
                                
                                <dt class="col-sm-5">Application #:</dt>
                                <dd class="col-sm-7"><code>{{ $application->application_number }}</code></dd>
                                
                                <dt class="col-sm-5">Email:</dt>
                                <dd class="col-sm-7">
                                    <a href="mailto:{{ $application->email }}">{{ $application->email }}</a>
                                </dd>
                                
                                <dt class="col-sm-5">Phone:</dt>
                                <dd class="col-sm-7">{{ $application->phone ?? 'Not provided' }}</dd>
                                
                                <dt class="col-sm-5">Date of Birth:</dt>
                                <dd class="col-sm-7">{{ $application->date_of_birth ? Carbon\Carbon::parse($application->date_of_birth)->format('M d, Y') : 'Not provided' }}</dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-5">Application Type:</dt>
                                <dd class="col-sm-7">{{ ucfirst($application->application_type) }}</dd>
                                
                                <dt class="col-sm-5">Program:</dt>
                                <dd class="col-sm-7">{{ $application->program->name }}</dd>
                                
                                <dt class="col-sm-5">Term:</dt>
                                <dd class="col-sm-7">{{ $application->term->name }}</dd>
                                
                                <dt class="col-sm-5">Previous Institution:</dt>
                                <dd class="col-sm-7">{{ $application->previous_institution ?? 'N/A' }}</dd>
                                
                                <dt class="col-sm-5">Previous GPA:</dt>
                                <dd class="col-sm-7">{{ $application->previous_gpa ?? 'N/A' }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Academic Information --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-graduation-cap"></i> Academic Information</h5>
                </div>
                <div class="card-body">
                    @if($application->test_scores)
                        <h6>Test Scores:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Test</th>
                                        <th>Score</th>
                                        <th>Date Taken</th>
                                        <th>Percentile</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($application->test_scores as $test => $scores)
                                    <tr>
                                        <td>{{ $test }}</td>
                                        <td>{{ $scores['total'] ?? $scores['composite'] ?? 'N/A' }}</td>
                                        <td>{{ $scores['date'] ?? 'N/A' }}</td>
                                        <td>{{ $scores['percentile'] ?? 'N/A' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No test scores provided</p>
                    @endif

                    @if($examRequired)
                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-exclamation-triangle"></i> 
                            <strong>Entrance Exam Required</strong>
                            @if($examRegistration)
                                - Registered (Exam Date: {{ $examRegistration->exam->exam_date->format('M d, Y') }})
                            @else
                                - Not yet registered
                                <button class="btn btn-sm btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#examRegistrationModal">
                                    Register for Exam
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Documents --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-file-alt"></i> Documents</h5>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#requestDocumentModal">
                        <i class="fas fa-plus"></i> Request Document
                    </button>
                </div>
                <div class="card-body">
                    @if($application->documents->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Document Type</th>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th>Uploaded</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($application->documents as $document)
                                    <tr>
                                        <td>{{ ucfirst(str_replace('_', ' ', $document->document_type)) }}</td>
                                        <td>{{ $document->document_name }}</td>
                                        <td>
                                            @if($document->is_verified)
                                                <span class="badge bg-success">Verified</span>
                                            @else
                                                <span class="badge bg-warning">Pending Verification</span>
                                            @endif
                                        </td>
                                        <td>{{ $document->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('admin.admissions.documents.view', $document->id) }}" 
                                                   class="btn btn-outline-primary" target="_blank">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if(!$document->is_verified)
                                                <button class="btn btn-outline-success" 
                                                        onclick="verifyDocument({{ $document->id }})">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                @endif
                                                <button class="btn btn-outline-danger" 
                                                        onclick="rejectDocument({{ $document->id }})">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No documents uploaded yet</p>
                    @endif
                </div>
            </div>

            {{-- Reviews --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-clipboard-check"></i> Reviews ({{ $reviewStats['completed_reviews'] }}/{{ $reviewStats['total_reviews'] }})</h5>
                    @if($canAssignReviewer)
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#assignReviewerModal">
                        <i class="fas fa-user-plus"></i> Assign Reviewer
                    </button>
                    @endif
                </div>
                <div class="card-body">
                    @if($reviewStats['total_reviews'] > 0)
                        <div class="mb-3">
                            <strong>Average Rating:</strong>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-{{ $reviewStats['average_rating'] >= 4 ? 'success' : ($reviewStats['average_rating'] >= 3 ? 'warning' : 'danger') }}" 
                                     style="width: {{ ($reviewStats['average_rating'] / 5) * 100 }}%">
                                    {{ number_format($reviewStats['average_rating'], 1) }}/5.0
                                </div>
                            </div>
                        </div>

                        <div class="accordion" id="reviewsAccordion">
                            @foreach($application->reviews as $index => $review)
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" 
                                            type="button" 
                                            data-bs-toggle="collapse" 
                                            data-bs-target="#review{{ $review->id }}">
                                        <span class="me-2">
                                            @if($review->status === 'completed')
                                                <i class="fas fa-check-circle text-success"></i>
                                            @else
                                                <i class="fas fa-clock text-warning"></i>
                                            @endif
                                        </span>
                                        {{ $review->reviewer->name }} - {{ ucfirst(str_replace('_', ' ', $review->review_stage)) }}
                                        @if($review->status === 'completed')
                                            <span class="badge bg-primary ms-2">{{ $review->overall_rating }}/5</span>
                                        @endif
                                    </button>
                                </h2>
                                <div id="review{{ $review->id }}" 
                                     class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}"
                                     data-bs-parent="#reviewsAccordion">
                                    <div class="accordion-body">
                                        @if($review->status === 'completed')
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <table class="table table-sm">
                                                        <tr>
                                                            <td>Academic:</td>
                                                            <td>{{ $review->academic_rating }}/5</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Extracurricular:</td>
                                                            <td>{{ $review->extracurricular_rating }}/5</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Essay:</td>
                                                            <td>{{ $review->essay_rating }}/5</td>
                                                        </tr>
                                                    </table>
                                                </div>
                                                <div class="col-md-6">
                                                    <table class="table table-sm">
                                                        <tr>
                                                            <td>Recommendations:</td>
                                                            <td>{{ $review->recommendation_rating }}/5</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Overall:</td>
                                                            <td><strong>{{ $review->overall_rating }}/5</strong></td>
                                                        </tr>
                                                        <tr>
                                                            <td>Decision:</td>
                                                            <td>
                                                                <span class="badge bg-{{ $review->recommendation === 'admit' || $review->recommendation === 'strong_admit' ? 'success' : 
                                                                                        ($review->recommendation === 'deny' ? 'danger' : 'warning') }}">
                                                                    {{ ucfirst(str_replace('_', ' ', $review->recommendation)) }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-2">
                                                <strong>Comments:</strong>
                                                <p class="mt-1">{{ $review->comments }}</p>
                                            </div>
                                            
                                            @if($review->strengths)
                                            <div class="mb-2">
                                                <strong>Strengths:</strong>
                                                <p class="mt-1">{{ $review->strengths }}</p>
                                            </div>
                                            @endif
                                            
                                            @if($review->weaknesses)
                                            <div class="mb-2">
                                                <strong>Weaknesses:</strong>
                                                <p class="mt-1">{{ $review->weaknesses }}</p>
                                            </div>
                                            @endif
                                        @else
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle"></i> Review pending
                                                <br>
                                                <small>Assigned: {{ $review->assigned_at->format('M d, Y') }}</small>
                                                <br>
                                                <small>Deadline: {{ $review->deadline->format('M d, Y') }}</small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No reviews assigned yet</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right Column - Actions & Timeline --}}
        <div class="col-lg-4">
            {{-- Quick Actions --}}
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    @if(!$application->decision && $canMakeDecision)
                    <button class="btn btn-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#decisionModal">
                        <i class="fas fa-gavel"></i> Make Decision
                    </button>
                    @endif
                    
                    @if($canReview)
                    <button class="btn btn-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#reviewModal">
                        <i class="fas fa-edit"></i> Submit Review
                    </button>
                    @endif
                    
                    <button class="btn btn-info w-100 mb-2" data-bs-toggle="modal" data-bs-target="#communicationModal">
                        <i class="fas fa-envelope"></i> Send Communication
                    </button>
                    
                    <button class="btn btn-warning w-100 mb-2" data-bs-toggle="modal" data-bs-target="#noteModal">
                        <i class="fas fa-sticky-note"></i> Add Note
                    </button>
                    
                    @if($examRequired && !$examRegistration)
                    <button class="btn btn-danger w-100 mb-2" data-bs-toggle="modal" data-bs-target="#examRegistrationModal">
                        <i class="fas fa-clipboard-list"></i> Register for Exam
                    </button>
                    @endif
                </div>
            </div>

            {{-- Enrollment Status (if admitted) --}}
            @if($enrollmentStatus)
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-user-graduate"></i> Enrollment Status</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-6">Status:</dt>
                        <dd class="col-sm-6">
                            <span class="badge bg-{{ $enrollmentStatus->decision === 'accept' ? 'success' : 
                                                    ($enrollmentStatus->decision === 'decline' ? 'danger' : 'warning') }}">
                                {{ ucfirst($enrollmentStatus->decision ?? 'Pending') }}
                            </span>
                        </dd>
                        
                        <dt class="col-sm-6">Deposit:</dt>
                        <dd class="col-sm-6">
                            @if($enrollmentStatus->deposit_paid)
                                <span class="text-success">Paid</span>
                            @else
                                <span class="text-danger">Not Paid</span>
                            @endif
                        </dd>
                        
                        <dt class="col-sm-6">Deadline:</dt>
                        <dd class="col-sm-6">{{ $enrollmentStatus->enrollment_deadline->format('M d, Y') }}</dd>
                        
                        @if($enrollmentStatus->orientation_registered)
                        <dt class="col-sm-6">Orientation:</dt>
                        <dd class="col-sm-6">
                            <span class="text-success">Registered</span>
                        </dd>
                        @endif
                    </dl>
                </div>
            </div>
            @endif

            {{-- Communication History --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-comments"></i> Communications</h5>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @if($application->communications->count() > 0)
                        <div class="timeline">
                            @foreach($application->communications->take(5) as $comm)
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <p class="mb-1">
                                        <strong>{{ $comm->subject }}</strong><br>
                                        <small>{{ $comm->created_at->format('M d, Y g:i A') }}</small>
                                    </p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No communications yet</p>
                    @endif
                </div>
            </div>

            {{-- Notes --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-sticky-note"></i> Internal Notes</h5>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @if($application->notes->count() > 0)
                        @foreach($application->notes->take(5) as $note)
                        <div class="mb-3 pb-3 border-bottom">
                            <small class="text-muted">
                                {{ $note->createdBy->name }} - {{ $note->created_at->format('M d, Y g:i A') }}
                            </small>
                            <p class="mb-0 mt-1">{{ $note->note }}</p>
                        </div>
                        @endforeach
                    @else
                        <p class="text-muted">No notes yet</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Decision Modal --}}
@if($canMakeDecision)
<div class="modal fade" id="decisionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.admissions.applications.decide', $application->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Make Admission Decision</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Decision *</label>
                        <select name="decision" class="form-select" required onchange="toggleDecisionFields(this.value)">
                            <option value="">Select Decision</option>
                            @foreach($decisionOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Decision Reason *</label>
                        <textarea name="decision_reason" class="form-control" rows="3" required></textarea>
                    </div>
                    
                    <div id="conditionalFields" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">Conditions</label>
                            <textarea name="conditions" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    
                    <div id="waitlistFields" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">Waitlist Rank</label>
                            <input type="number" name="waitlist_rank" class="form-control" min="1">
                        </div>
                    </div>
                    
                    <div id="deferFields" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">Defer to Term</label>
                            <select name="defer_to_term" class="form-select">
                                <option value="">Select Term</option>
                                {{-- Add terms here --}}
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Merit Scholarship Amount</label>
                                <input type="number" name="merit_scholarship" class="form-control" min="0" step="100">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Need-Based Aid Amount</label>
                                <input type="number" name="need_based_aid" class="form-control" min="0" step="100">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="entrance_exam_required" class="form-check-input" id="examRequired">
                            <label class="form-check-label" for="examRequired">
                                Entrance Exam Required
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="generate_letter" class="form-check-input" id="generateLetter" checked>
                            <label class="form-check-label" for="generateLetter">
                                Generate Decision Letter
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="notify_applicant" class="form-check-input" id="notifyApplicant" checked>
                            <label class="form-check-label" for="notifyApplicant">
                                Send Notification to Applicant
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Record Decision</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Assign Reviewer Modal --}}
@if($canAssignReviewer)
<div class="modal fade" id="assignReviewerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.admissions.applications.assign', $application->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Assign Reviewer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Reviewer *</label>
                        <select name="reviewer_id" class="form-select" required>
                            <option value="">Select Reviewer</option>
                            @foreach($availableReviewers as $reviewer)
                            <option value="{{ $reviewer->id }}">{{ $reviewer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Review Stage *</label>
                        <select name="review_stage" class="form-select" required>
                            <option value="">Select Stage</option>
                            <option value="initial_review">Initial Review</option>
                            <option value="academic_review">Academic Review</option>
                            <option value="department_review">Department Review</option>
                            <option value="committee_review">Committee Review</option>
                            <option value="final_review">Final Review</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Deadline</label>
                        <input type="date" name="deadline" class="form-control" min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Priority</label>
                        <select name="priority" class="form-select">
                            <option value="normal">Normal</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Instructions</label>
                        <textarea name="instructions" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign Reviewer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@section('scripts')
<script>
function toggleDecisionFields(value) {
    // Hide all conditional fields first
    document.getElementById('conditionalFields').style.display = 'none';
    document.getElementById('waitlistFields').style.display = 'none';
    document.getElementById('deferFields').style.display = 'none';
    
    // Show relevant fields based on decision
    if (value === 'conditional_admit') {
        document.getElementById('conditionalFields').style.display = 'block';
    } else if (value === 'waitlisted') {
        document.getElementById('waitlistFields').style.display = 'block';
    } else if (value === 'deferred') {
        document.getElementById('deferFields').style.display = 'block';
    }
}

function verifyDocument(documentId) {
    if (confirm('Are you sure you want to verify this document?')) {
        fetch(`/admin/admissions/documents/${documentId}/verify`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to verify document');
            }
        });
    }
}

function rejectDocument(documentId) {
    const reason = prompt('Please provide a reason for rejection:');
    if (reason) {
        fetch(`/admin/admissions/documents/${documentId}/reject`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ reason: reason })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to reject document');
            }
        });
    }
}

function flagApplication() {
    const reason = prompt('Please provide a reason for flagging:');
    if (reason) {
        fetch(`/admin/admissions/applications/{{ $application->id }}/flag`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ reason: reason })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Application flagged successfully');
                location.reload();
            }
        });
    }
}

function printApplication() {
    window.print();
}
</script>