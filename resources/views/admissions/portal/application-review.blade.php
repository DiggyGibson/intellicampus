{{-- resources/views/admissions/portal/application-review.blade.php --}}
@extends('layouts.portal')

@section('title', 'Review Application - #' . $application->application_number)

@section('styles')
<style>
    .review-section {
        border-left: 3px solid #007bff;
        padding-left: 20px;
        margin-bottom: 30px;
    }
    
    .review-section.complete {
        border-left-color: #28a745;
    }
    
    .review-section.incomplete {
        border-left-color: #dc3545;
    }
    
    .review-item {
        padding: 10px 0;
        border-bottom: 1px solid #e9ecef;
    }
    
    .review-item:last-child {
        border-bottom: none;
    }
    
    .review-label {
        font-weight: 600;
        color: #6c757d;
        min-width: 150px;
        display: inline-block;
    }
    
    .review-value {
        color: #212529;
    }
    
    .edit-link {
        opacity: 0;
        transition: opacity 0.3s;
    }
    
    .review-item:hover .edit-link {
        opacity: 1;
    }
    
    .checklist-item {
        padding: 8px 12px;
        margin-bottom: 8px;
        border-radius: 4px;
        background: #f8f9fa;
    }
    
    .checklist-item.checked {
        background: #d4edda;
        color: #155724;
    }
    
    .checklist-item.missing {
        background: #f8d7da;
        color: #721c24;
    }
    
    .declaration-box {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 20px;
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
                        <i class="fas fa-clipboard-check me-2"></i>Review Your Application
                    </h2>
                    <p class="text-muted mb-0">
                        Please review all information carefully before submitting
                    </p>
                </div>
                <div>
                    <span class="badge bg-info px-3 py-2">
                        <i class="fas fa-clock me-1"></i>
                        Deadline: {{ $application->term->admission_deadline->format('F d, Y') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Completion Status Alert --}}
    @if($application->completionPercentage() < 100)
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Application Incomplete!</strong> 
        Your application is {{ $application->completionPercentage() }}% complete. 
        Please complete all required sections before submitting.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @else
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <strong>Application Complete!</strong> 
        Your application is ready for submission. Please review all information below.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        {{-- Main Review Content --}}
        <div class="col-lg-8">
            {{-- Program Information --}}
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-graduation-cap me-2"></i>Program Information
                    </h5>
                    <a href="{{ route('admissions.form.show', ['id' => $application->id, 'section' => 'program']) }}" 
                       class="btn btn-sm btn-light edit-link">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </div>
                <div class="card-body">
                    <div class="review-section {{ $application->program_id ? 'complete' : 'incomplete' }}">
                        <div class="review-item">
                            <span class="review-label">Application Type:</span>
                            <span class="review-value">{{ ucwords(str_replace('_', ' ', $application->application_type)) }}</span>
                        </div>
                        <div class="review-item">
                            <span class="review-label">Term:</span>
                            <span class="review-value">{{ $application->term->name }}</span>
                        </div>
                        <div class="review-item">
                            <span class="review-label">Program:</span>
                            <span class="review-value">{{ $application->program->name ?? 'Not Selected' }}</span>
                        </div>
                        @if($application->alternate_program_id)
                        <div class="review-item">
                            <span class="review-label">Alternate Program:</span>
                            <span class="review-value">{{ $application->alternateProgram->name }}</span>
                        </div>
                        @endif
                        <div class="review-item">
                            <span class="review-label">Entry Term:</span>
                            <span class="review-value">{{ ucfirst($application->entry_type) }} {{ $application->entry_year }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Personal Information --}}
            <div class="card shadow mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i>Personal Information
                    </h5>
                    <a href="{{ route('admissions.form.show', ['id' => $application->id, 'section' => 'personal']) }}" 
                       class="btn btn-sm btn-outline-primary edit-link">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </div>
                <div class="card-body">
                    <div class="review-section complete">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="review-item">
                                    <span class="review-label">Full Name:</span>
                                    <span class="review-value">
                                        {{ $application->first_name }} 
                                        {{ $application->middle_name }} 
                                        {{ $application->last_name }}
                                    </span>
                                </div>
                                <div class="review-item">
                                    <span class="review-label">Date of Birth:</span>
                                    <span class="review-value">{{ $application->date_of_birth?->format('F d, Y') }}</span>
                                </div>
                                <div class="review-item">
                                    <span class="review-label">Gender:</span>
                                    <span class="review-value">{{ ucfirst($application->gender ?? 'Not specified') }}</span>
                                </div>
                                <div class="review-item">
                                    <span class="review-label">Nationality:</span>
                                    <span class="review-value">{{ $application->nationality }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="review-item">
                                    <span class="review-label">Email:</span>
                                    <span class="review-value">{{ $application->email }}</span>
                                </div>
                                <div class="review-item">
                                    <span class="review-label">Phone:</span>
                                    <span class="review-value">{{ $application->phone_primary }}</span>
                                </div>
                                <div class="review-item">
                                    <span class="review-label">National ID:</span>
                                    <span class="review-value">{{ $application->national_id ?? 'Not provided' }}</span>
                                </div>
                                <div class="review-item">
                                    <span class="review-label">Passport:</span>
                                    <span class="review-value">{{ $application->passport_number ?? 'Not provided' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Educational Background --}}
            <div class="card shadow mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-school me-2"></i>Educational Background
                    </h5>
                    <a href="{{ route('admissions.form.show', ['id' => $application->id, 'section' => 'education']) }}" 
                       class="btn btn-sm btn-outline-primary edit-link">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </div>
                <div class="card-body">
                    <div class="review-section {{ $application->previous_institution ? 'complete' : 'incomplete' }}">
                        @if($application->application_type == 'freshman' && $application->high_school_name)
                        <h6 class="text-muted mb-3">High School Information</h6>
                        <div class="review-item">
                            <span class="review-label">School Name:</span>
                            <span class="review-value">{{ $application->high_school_name }}</span>
                        </div>
                        <div class="review-item">
                            <span class="review-label">Graduation Date:</span>
                            <span class="review-value">{{ $application->high_school_graduation_date?->format('F Y') }}</span>
                        </div>
                        <div class="review-item">
                            <span class="review-label">Diploma Type:</span>
                            <span class="review-value">{{ $application->high_school_diploma_type }}</span>
                        </div>
                        <hr>
                        @endif
                        
                        @if($application->previous_institution)
                        <h6 class="text-muted mb-3">Previous Institution</h6>
                        <div class="review-item">
                            <span class="review-label">Institution:</span>
                            <span class="review-value">{{ $application->previous_institution }}</span>
                        </div>
                        <div class="review-item">
                            <span class="review-label">Degree:</span>
                            <span class="review-value">{{ $application->previous_degree ?? 'N/A' }}</span>
                        </div>
                        <div class="review-item">
                            <span class="review-label">Major:</span>
                            <span class="review-value">{{ $application->previous_major ?? 'N/A' }}</span>
                        </div>
                        <div class="review-item">
                            <span class="review-label">GPA:</span>
                            <span class="review-value">
                                {{ $application->previous_gpa ?? 'N/A' }} 
                                @if($application->gpa_scale)
                                    / {{ $application->gpa_scale }}
                                @endif
                            </span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Test Scores --}}
            @if($application->test_scores)
            <div class="card shadow mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>Test Scores
                    </h5>
                    <a href="{{ route('admissions.form.show', ['id' => $application->id, 'section' => 'test-scores']) }}" 
                       class="btn btn-sm btn-outline-primary edit-link">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </div>
                <div class="card-body">
                    <div class="review-section complete">
                        @foreach($application->getFormattedTestScores() as $test => $score)
                        <div class="review-item">
                            <span class="review-label">{{ $test }}:</span>
                            <span class="review-value">{{ $score }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Documents --}}
            <div class="card shadow mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-paperclip me-2"></i>Documents
                    </h5>
                    <a href="{{ route('admissions.document.upload', $application->id) }}" 
                       class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-upload"></i> Manage
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($application->documents as $document)
                        <div class="col-md-6 mb-2">
                            <div class="checklist-item {{ $document->status == 'verified' ? 'checked' : '' }}">
                                <i class="fas {{ $document->status == 'verified' ? 'fa-check-circle text-success' : 'fa-clock text-warning' }} me-2"></i>
                                {{ ucwords(str_replace('_', ' ', $document->document_type)) }}
                                <span class="float-end">
                                    <a href="{{ route('admissions.document.preview', $document->id) }}" 
                                       target="_blank" 
                                       class="text-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </span>
                            </div>
                        </div>
                        @endforeach
                        
                        @if($application->documents->count() == 0)
                        <div class="col-12">
                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                No documents uploaded yet. Please upload required documents.
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Declaration & Submission --}}
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-file-signature me-2"></i>Declaration & Submission
                    </h5>
                </div>
                <div class="card-body">
                    <div class="declaration-box">
                        <h6 class="mb-3">Applicant Declaration</h6>
                        <p>I hereby declare that:</p>
                        <ul>
                            <li>All information provided in this application is true, complete, and accurate to the best of my knowledge</li>
                            <li>I understand that any false information may result in the cancellation of my application or admission</li>
                            <li>I have read and understood the admission requirements and policies</li>
                            <li>I authorize the university to verify all information provided</li>
                            <li>I understand that submission of this application does not guarantee admission</li>
                            <li>I agree to abide by the university's rules and regulations if admitted</li>
                        </ul>
                        
                        <form id="submit-form" action="{{ route('admissions.application.submit', $application->id) }}" method="POST">
                            @csrf
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="agree-terms" required>
                                <label class="form-check-label" for="agree-terms">
                                    <strong>I agree to the above declaration and terms</strong>
                                </label>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="confirm-accuracy" required>
                                <label class="form-check-label" for="confirm-accuracy">
                                    <strong>I confirm that all information is accurate</strong>
                                </label>
                            </div>
                            
                            <hr>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="signature">Digital Signature (Type your full name)</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="signature" 
                                               name="signature" 
                                               placeholder="Enter your full name as signature"
                                               required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Date</label>
                                        <input type="text" 
                                               class="form-control" 
                                               value="{{ now()->format('F d, Y') }}" 
                                               readonly>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4 text-center">
                                @if($application->completionPercentage() == 100)
                                <button type="submit" 
                                        class="btn btn-success btn-lg px-5" 
                                        id="submit-btn"
                                        disabled>
                                    <i class="fas fa-paper-plane me-2"></i>Submit Application
                                </button>
                                @else
                                <button type="button" class="btn btn-secondary btn-lg px-5" disabled>
                                    <i class="fas fa-lock me-2"></i>Complete Application First
                                </button>
                                @endif
                                
                                <a href="{{ route('admissions.form.show', $application->id) }}" 
                                   class="btn btn-outline-secondary btn-lg px-5 ms-2">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Form
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Sidebar --}}
        <div class="col-lg-4">
            {{-- Application Summary --}}
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Application Summary
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Application Number</small>
                        <div class="h5">{{ $application->application_number }}</div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Status</small>
                        <div>
                            <span class="badge bg-{{ $application->getStatusColor() }}">
                                {{ ucwords(str_replace('_', ' ', $application->status)) }}
                            </span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Completion</small>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-{{ $application->completionPercentage() == 100 ? 'success' : 'warning' }}" 
                                 role="progressbar" 
                                 style="width: {{ $application->completionPercentage() }}%">
                                {{ $application->completionPercentage() }}%
                            </div>
                        </div>
                    </div>
                    <div>
                        <small class="text-muted">Started On</small>
                        <div>{{ $application->started_at->format('F d, Y') }}</div>
                    </div>
                </div>
            </div>

            {{-- Checklist --}}
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-tasks me-2"></i>Submission Checklist
                    </h6>
                </div>
                <div class="card-body">
                    @foreach($application->checklistItems as $item)
                    <div class="checklist-item {{ $item->is_completed ? 'checked' : 'missing' }}">
                        <i class="fas {{ $item->is_completed ? 'fa-check-circle' : 'fa-times-circle' }} me-2"></i>
                        {{ $item->item_name }}
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Important Notes --}}
            <div class="card shadow">
                <div class="card-header bg-warning">
                    <h6 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>Important Notes
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="small mb-0">
                        <li class="mb-2">Once submitted, you cannot edit your application</li>
                        <li class="mb-2">Ensure all documents are uploaded</li>
                        <li class="mb-2">Application fee must be paid within 48 hours</li>
                        <li class="mb-2">You will receive a confirmation email</li>
                        <li>Keep your application number for future reference</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Submission Confirmation Modal --}}
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirm Submission
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>Are you sure you want to submit your application?</strong></p>
                <p>Please note:</p>
                <ul>
                    <li>You will not be able to make any changes after submission</li>
                    <li>Application fee payment will be required</li>
                    <li>You will receive a confirmation email</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Review Again</button>
                <button type="button" class="btn btn-success" id="confirm-submit">
                    <i class="fas fa-check me-2"></i>Yes, Submit Application
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Enable submit button only when checkboxes are checked
    $('#agree-terms, #confirm-accuracy').change(function() {
        if ($('#agree-terms').is(':checked') && $('#confirm-accuracy').is(':checked')) {
            $('#submit-btn').prop('disabled', false);
        } else {
            $('#submit-btn').prop('disabled', true);
        }
    });
    
    // Show confirmation modal before submission
    $('#submit-form').submit(function(e) {
        e.preventDefault();
        
        // Validate signature
        if ($('#signature').val().trim() === '') {
            toastr.error('Please enter your digital signature');
            return false;
        }
        
        // Show confirmation modal
        $('#confirmModal').modal('show');
    });
    
    // Confirm submission
    $('#confirm-submit').click(function() {
        // Disable button to prevent double submission
        $(this).prop('disabled', true);
        $(this).html('<i class="fas fa-spinner fa-spin me-2"></i>Submitting...');
        
        // Submit the form
        $('#submit-form')[0].submit();
    });
    
    // Print application
    $('#print-application').click(function() {
        window.print();
    });
    
    // Smooth scroll to sections
    $('.review-section').click(function() {
        const target = $(this).data('target');
        if (target) {
            $('html, body').animate({
                scrollTop: $(target).offset().top - 100
            }, 500);
        }
    });
});
</script>
@endsection