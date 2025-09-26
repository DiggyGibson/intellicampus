{{-- resources/views/admissions/portal/start-application.blade.php --}}
@extends('layouts.app')

@section('title', 'Start New Application - IntelliCampus Admissions')

@section('styles')
<style>
    .application-type-card {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
        height: 100%;
    }
    
    .application-type-card:hover {
        border-color: #007bff;
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,123,255,0.3);
    }
    
    .application-type-card.selected {
        border-color: #28a745;
        background: #f0fff4;
    }
    
    .application-type-card .icon {
        font-size: 3rem;
        margin-bottom: 15px;
    }
    
    .program-card {
        border-left: 4px solid transparent;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .program-card:hover {
        border-left-color: #007bff;
        background: #f8f9fa;
    }
    
    .program-card.selected {
        border-left-color: #28a745;
        background: #f0fff4;
    }
    
    .step-indicator {
        display: flex;
        justify-content: space-between;
        margin-bottom: 30px;
        position: relative;
    }
    
    .step-indicator::before {
        content: '';
        position: absolute;
        top: 20px;
        left: 0;
        right: 0;
        height: 2px;
        background: #dee2e6;
        z-index: -1;
    }
    
    .step {
        background: #fff;
        padding: 0 15px;
        text-align: center;
        flex: 1;
    }
    
    .step-number {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #dee2e6;
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .step.active .step-number {
        background: #007bff;
    }
    
    .step.completed .step-number {
        background: #28a745;
    }
    
    .requirement-item {
        padding: 10px;
        border-left: 3px solid #17a2b8;
        background: #f8f9fa;
        margin-bottom: 10px;
    }
    
    .term-selector {
        display: inline-block;
        padding: 10px 20px;
        border: 2px solid #dee2e6;
        border-radius: 5px;
        cursor: pointer;
        margin: 5px;
        transition: all 0.3s;
    }
    
    .term-selector:hover {
        border-color: #007bff;
        background: #e7f1ff;
    }
    
    .term-selector.selected {
        border-color: #28a745;
        background: #d4edda;
        color: #155724;
    }
    
    .fee-breakdown {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
    }
    
    .fee-item {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #dee2e6;
    }
    
    .fee-item:last-child {
        border-bottom: none;
        font-weight: bold;
        font-size: 1.1rem;
        margin-top: 10px;
        padding-top: 15px;
        border-top: 2px solid #dee2e6;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="text-center">
                <h2 class="h2 font-weight-bold text-gray-800 mb-3">
                    <i class="fas fa-graduation-cap me-2"></i>Start Your Application
                </h2>
                <p class="lead text-muted">
                    Begin your journey at IntelliCampus. Select your application type and program to get started.
                </p>
            </div>
        </div>
    </div>

    {{-- Step Indicator --}}
    <div class="row mb-4">
        <div class="col-lg-10 mx-auto">
            <div class="step-indicator">
                <div class="step active">
                    <div class="step-number">1</div>
                    <div class="small">Application Type</div>
                </div>
                <div class="step" id="step-2">
                    <div class="step-number">2</div>
                    <div class="small">Select Program</div>
                </div>
                <div class="step" id="step-3">
                    <div class="step-number">3</div>
                    <div class="small">Term & Details</div>
                </div>
                <div class="step" id="step-4">
                    <div class="step-number">4</div>
                    <div class="small">Review & Start</div>
                </div>
            </div>
        </div>
    </div>

    <form id="start-application-form" method="POST" action="{{ route('admissions.portal.create') }}">
        @csrf
        
        {{-- Step 1: Application Type --}}
        <div class="step-content" id="step-1-content">
            <div class="row mb-4">
                <div class="col-lg-10 mx-auto">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-user-graduate me-2"></i>Select Application Type
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-4">Choose the category that best describes you:</p>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="application-type-card" data-type="freshman">
                                        <div class="text-center">
                                            <div class="icon text-primary">
                                                <i class="fas fa-user-graduate"></i>
                                            </div>
                                            <h5>First-Year Student</h5>
                                            <p class="text-muted small">
                                                I'm currently in high school or have graduated but haven't attended college yet
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <div class="application-type-card" data-type="transfer">
                                        <div class="text-center">
                                            <div class="icon text-info">
                                                <i class="fas fa-exchange-alt"></i>
                                            </div>
                                            <h5>Transfer Student</h5>
                                            <p class="text-muted small">
                                                I've completed courses at another college/university and want to transfer
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <div class="application-type-card" data-type="graduate">
                                        <div class="text-center">
                                            <div class="icon text-success">
                                                <i class="fas fa-graduation-cap"></i>
                                            </div>
                                            <h5>Graduate Student</h5>
                                            <p class="text-muted small">
                                                I have a bachelor's degree and want to pursue a master's or doctoral degree
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <div class="application-type-card" data-type="international">
                                        <div class="text-center">
                                            <div class="icon text-warning">
                                                <i class="fas fa-globe"></i>
                                            </div>
                                            <h5>International Student</h5>
                                            <p class="text-muted small">
                                                I'm not a citizen or permanent resident and need a student visa
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <div class="application-type-card" data-type="readmission">
                                        <div class="text-center">
                                            <div class="icon text-secondary">
                                                <i class="fas fa-redo"></i>
                                            </div>
                                            <h5>Readmission</h5>
                                            <p class="text-muted small">
                                                I was previously enrolled at IntelliCampus and want to return
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <div class="application-type-card" data-type="non_degree">
                                        <div class="text-center">
                                            <div class="icon text-purple">
                                                <i class="fas fa-book-open"></i>
                                            </div>
                                            <h5>Non-Degree Seeking</h5>
                                            <p class="text-muted small">
                                                I want to take courses without pursuing a degree
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <input type="hidden" name="application_type" id="application_type" required>
                            
                            <div class="text-end mt-4">
                                <button type="button" class="btn btn-primary next-step" data-step="1" disabled>
                                    Next: Select Program <i class="fas fa-arrow-right ms-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 2: Select Program --}}
        <div class="step-content" id="step-2-content" style="display: none;">
            <div class="row mb-4">
                <div class="col-lg-10 mx-auto">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-book me-2"></i>Select Your Program
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <input type="text" 
                                           class="form-control" 
                                           id="program-search" 
                                           placeholder="Search programs...">
                                </div>
                                <div class="col-md-6">
                                    <select class="form-select" id="program-filter">
                                        <option value="">All Departments</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="programs-list" style="max-height: 400px; overflow-y: auto;">
                                @foreach($programs as $program)
                                <div class="card program-card mb-2" data-program-id="{{ $program->id }}" data-department="{{ $program->department_id }}">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <h6 class="mb-1">{{ $program->name }}</h6>
                                                <p class="text-muted small mb-0">
                                                    {{ $program->degree_type }} | {{ $program->department->name }}
                                                </p>
                                                <p class="small mb-0">
                                                    Duration: {{ $program->duration_years }} years | 
                                                    Credits: {{ $program->total_credits }}
                                                </p>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <span class="badge bg-info">{{ $program->delivery_mode }}</span>
                                                @if($program->is_accredited)
                                                    <span class="badge bg-success">Accredited</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            
                            <input type="hidden" name="program_id" id="program_id" required>
                            
                            {{-- Alternate Program (Optional) --}}
                            <div class="mt-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="add-alternate">
                                    <label class="form-check-label" for="add-alternate">
                                        Add an alternate program choice (optional)
                                    </label>
                                </div>
                                
                                <div id="alternate-program-section" style="display: none;" class="mt-3">
                                    <label for="alternate_program_id" class="form-label">Alternate Program</label>
                                    <select class="form-select" name="alternate_program_id" id="alternate_program_id">
                                        <option value="">Select alternate program...</option>
                                        @foreach($programs as $program)
                                            <option value="{{ $program->id }}">{{ $program->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-secondary prev-step" data-step="2">
                                    <i class="fas fa-arrow-left me-1"></i> Previous
                                </button>
                                <button type="button" class="btn btn-primary next-step" data-step="2" disabled>
                                    Next: Term Selection <i class="fas fa-arrow-right ms-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 3: Term & Details --}}
        <div class="step-content" id="step-3-content" style="display: none;">
            <div class="row mb-4">
                <div class="col-lg-10 mx-auto">
                    <div class="row">
                        {{-- Term Selection --}}
                        <div class="col-md-8 mb-4">
                            <div class="card shadow">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-calendar-alt me-2"></i>Select Entry Term
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">When do you plan to start your studies?</p>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="entry_year" class="form-label">Year</label>
                                            <select class="form-select" name="entry_year" id="entry_year" required>
                                                @for($year = date('Y'); $year <= date('Y') + 2; $year++)
                                                    <option value="{{ $year }}">{{ $year }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Term</label>
                                            <div>
                                                <div class="term-selector" data-term="fall">
                                                    <i class="fas fa-leaf text-orange me-1"></i> Fall
                                                </div>
                                                <div class="term-selector" data-term="spring">
                                                    <i class="fas fa-flower text-success me-1"></i> Spring
                                                </div>
                                                <div class="term-selector" data-term="summer">
                                                    <i class="fas fa-sun text-warning me-1"></i> Summer
                                                </div>
                                            </div>
                                            <input type="hidden" name="entry_type" id="entry_type" required>
                                        </div>
                                    </div>
                                    
                                    {{-- Academic Term Selection --}}
                                    <div class="mt-4">
                                        <label class="form-label">Available Academic Terms</label>
                                        <select class="form-select" name="term_id" id="term_id" required>
                                            <option value="">Select term...</option>
                                            @foreach($availableTerms as $term)
                                                <option value="{{ $term->id }}" 
                                                        data-deadline="{{ $term->admission_deadline->format('F d, Y') }}">
                                                    {{ $term->name }} (Deadline: {{ $term->admission_deadline->format('M d, Y') }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    {{-- Additional Options --}}
                                    <div class="mt-4">
                                        <h6>Additional Options</h6>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="intended_major_minor" id="intended_major_minor">
                                            <label class="form-check-label" for="intended_major_minor">
                                                I plan to declare a minor
                                            </label>
                                        </div>
                                        
                                        <div id="minor-section" style="display: none;" class="mt-2 ms-4">
                                            <input type="text" 
                                                   class="form-control" 
                                                   name="intended_minor" 
                                                   placeholder="Enter intended minor (e.g., Business, Psychology)">
                                        </div>
                                        
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="housing_interest" id="housing_interest">
                                            <label class="form-check-label" for="housing_interest">
                                                I'm interested in on-campus housing
                                            </label>
                                        </div>
                                        
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="financial_aid_interest" id="financial_aid_interest">
                                            <label class="form-check-label" for="financial_aid_interest">
                                                I plan to apply for financial aid
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Requirements Preview --}}
                        <div class="col-md-4 mb-4">
                            <div class="card shadow">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">
                                        <i class="fas fa-clipboard-list me-2"></i>Requirements
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div id="requirements-list">
                                        <p class="text-muted small">Requirements will appear based on your selections</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card shadow mt-3">
                                <div class="card-header bg-warning">
                                    <h6 class="mb-0">
                                        <i class="fas fa-clock me-2"></i>Important Dates
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div id="important-dates">
                                        <p class="text-muted small">Select a term to see important dates</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary prev-step" data-step="3">
                            <i class="fas fa-arrow-left me-1"></i> Previous
                        </button>
                        <button type="button" class="btn btn-primary next-step" data-step="3" disabled>
                            Next: Review <i class="fas fa-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 4: Review & Start --}}
        <div class="step-content" id="step-4-content" style="display: none;">
            <div class="row mb-4">
                <div class="col-lg-10 mx-auto">
                    <div class="card shadow">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-check-circle me-2"></i>Review & Start Application
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h6 class="mb-3">Application Summary</h6>
                                    
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="text-muted" width="200">Application Type:</td>
                                            <td id="review-type">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Program:</td>
                                            <td id="review-program">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Alternate Program:</td>
                                            <td id="review-alternate">None</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Entry Term:</td>
                                            <td id="review-term">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Application Deadline:</td>
                                            <td id="review-deadline" class="text-danger fw-bold">-</td>
                                        </tr>
                                    </table>
                                    
                                    <hr>
                                    
                                    <h6 class="mb-3">What Happens Next?</h6>
                                    <ol class="text-muted">
                                        <li class="mb-2">You'll be redirected to the application form</li>
                                        <li class="mb-2">Complete all required sections</li>
                                        <li class="mb-2">Upload necessary documents</li>
                                        <li class="mb-2">Pay the application fee (or request a waiver)</li>
                                        <li class="mb-2">Submit your application before the deadline</li>
                                    </ol>
                                    
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Note:</strong> You can save your progress and return to complete your application at any time before the deadline.
                                    </div>
                                    
                                    {{-- Agreement --}}
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="agree_start" required>
                                        <label class="form-check-label" for="agree_start">
                                            I understand the application process and am ready to begin
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    {{-- Fee Information --}}
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="mb-3">Application Fees</h6>
                                            <div class="fee-breakdown">
                                                <div class="fee-item">
                                                    <span>Application Fee:</span>
                                                    <span>${{ number_format($applicationFee, 2) }}</span>
                                                </div>
                                                <div class="fee-item">
                                                    <span>Processing Fee:</span>
                                                    <span>${{ number_format($processingFee, 2) }}</span>
                                                </div>
                                                <div class="fee-item">
                                                    <span class="fw-bold">Total:</span>
                                                    <span class="fw-bold">${{ number_format($applicationFee + $processingFee, 2) }}</span>
                                                </div>
                                            </div>
                                            
                                            <p class="text-muted small mt-3 mb-0">
                                                <i class="fas fa-info-circle"></i>
                                                Fee waivers available for eligible students
                                            </p>
                                        </div>
                                    </div>
                                    
                                    {{-- Contact Information --}}
                                    <div class="card mt-3">
                                        <div class="card-body">
                                            <h6 class="mb-3">Need Help?</h6>
                                            <p class="small mb-2">
                                                <i class="fas fa-phone me-2"></i>
                                                +231 77 000 0000
                                            </p>
                                            <p class="small mb-2">
                                                <i class="fas fa-envelope me-2"></i>
                                                admissions@intellicampus.edu
                                            </p>
                                            <p class="small mb-0">
                                                <i class="fas fa-clock me-2"></i>
                                                Mon-Fri: 8:00 AM - 5:00 PM
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-secondary prev-step" data-step="4">
                                    <i class="fas fa-arrow-left me-1"></i> Previous
                                </button>
                                <button type="submit" class="btn btn-success btn-lg" id="start-application-btn" disabled>
                                    <i class="fas fa-rocket me-2"></i>Start My Application
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let currentStep = 1;
    const totalSteps = 4;
    
    // Step 1: Application Type Selection
    $('.application-type-card').click(function() {
        $('.application-type-card').removeClass('selected');
        $(this).addClass('selected');
        $('#application_type').val($(this).data('type'));
        $('.next-step[data-step="1"]').prop('disabled', false);
        
        // Update requirements based on type
        updateRequirements($(this).data('type'));
    });
    
    // Step 2: Program Selection
    $('.program-card').click(function() {
        $('.program-card').removeClass('selected');
        $(this).addClass('selected');
        $('#program_id').val($(this).data('program-id'));
        $('.next-step[data-step="2"]').prop('disabled', false);
    });
    
    // Program Search
    $('#program-search').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('.program-card').each(function() {
            const programName = $(this).find('h6').text().toLowerCase();
            if (programName.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // Program Filter by Department
    $('#program-filter').change(function() {
        const departmentId = $(this).val();
        if (departmentId === '') {
            $('.program-card').show();
        } else {
            $('.program-card').hide();
            $(`.program-card[data-department="${departmentId}"]`).show();
        }
    });
    
    // Alternate Program Toggle
    $('#add-alternate').change(function() {
        if ($(this).is(':checked')) {
            $('#alternate-program-section').slideDown();
        } else {
            $('#alternate-program-section').slideUp();
            $('#alternate_program_id').val('');
        }
    });
    
    // Step 3: Term Selection
    $('.term-selector').click(function() {
        $('.term-selector').removeClass('selected');
        $(this).addClass('selected');
        $('#entry_type').val($(this).data('term'));
        checkStep3Completion();
    });
    
    $('#term_id').change(function() {
        const selectedOption = $(this).find('option:selected');
        const deadline = selectedOption.data('deadline');
        
        if (deadline) {
            $('#important-dates').html(`
                <ul class="list-unstyled small">
                    <li><strong>Application Deadline:</strong><br>${deadline}</li>
                    <li class="mt-2"><strong>Decision Release:</strong><br>4-6 weeks after deadline</li>
                    <li class="mt-2"><strong>Enrollment Deadline:</strong><br>30 days after admission</li>
                </ul>
            `);
        }
        
        checkStep3Completion();
    });
    
    function checkStep3Completion() {
        if ($('#entry_type').val() && $('#term_id').val()) {
            $('.next-step[data-step="3"]').prop('disabled', false);
        }
    }
    
    // Minor checkbox toggle
    $('#intended_major_minor').change(function() {
        if ($(this).is(':checked')) {
            $('#minor-section').slideDown();
        } else {
            $('#minor-section').slideUp();
        }
    });
    
    // Step 4: Agreement checkbox
    $('#agree_start').change(function() {
        $('#start-application-btn').prop('disabled', !$(this).is(':checked'));
    });
    
    // Navigation between steps
    $('.next-step').click(function() {
        const step = parseInt($(this).data('step'));
        if (validateStep(step)) {
            showStep(step + 1);
            updateReviewSection();
        }
    });
    
    $('.prev-step').click(function() {
        const step = parseInt($(this).data('step'));
        showStep(step - 1);
    });
    
    function showStep(step) {
        $('.step-content').hide();
        $(`#step-${step}-content`).show();
        
        // Update step indicators
        $('.step').removeClass('active completed');
        for (let i = 1; i < step; i++) {
            $(`.step:nth-child(${i})`).addClass('completed');
        }
        $(`.step:nth-child(${step})`).addClass('active');
        
        currentStep = step;
        
        // Scroll to top
        $('html, body').animate({ scrollTop: 0 }, 300);
    }
    
    function validateStep(step) {
        switch(step) {
            case 1:
                if (!$('#application_type').val()) {
                    toastr.error('Please select an application type');
                    return false;
                }
                break;
            case 2:
                if (!$('#program_id').val()) {
                    toastr.error('Please select a program');
                    return false;
                }
                break;
            case 3:
                if (!$('#entry_type').val() || !$('#term_id').val()) {
                    toastr.error('Please complete all required fields');
                    return false;
                }
                break;
        }
        return true;
    }
    
    function updateReviewSection() {
        // Update review summary
        $('#review-type').text($('.application-type-card.selected h5').text() || '-');
        $('#review-program').text($('.program-card.selected h6').text() || '-');
        $('#review-alternate').text($('#alternate_program_id option:selected').text() || 'None');
        $('#review-term').text($('#entry_type').val() + ' ' + $('#entry_year').val());
        $('#review-deadline').text($('#term_id option:selected').data('deadline') || '-');
    }
    
    function updateRequirements(type) {
        let requirements = [];
        
        switch(type) {
            case 'freshman':
                requirements = [
                    'High School Transcript',
                    'SAT/ACT Scores (optional)',
                    'Personal Essay',
                    'One Letter of Recommendation'
                ];
                break;
            case 'transfer':
                requirements = [
                    'College Transcripts',
                    'High School Transcript',
                    'Personal Statement',
                    'Dean\'s Letter (if applicable)'
                ];
                break;
            case 'graduate':
                requirements = [
                    'Bachelor\'s Degree Transcript',
                    'GRE/GMAT Scores (program specific)',
                    'Statement of Purpose',
                    'Three Letters of Recommendation',
                    'Resume/CV'
                ];
                break;
            case 'international':
                requirements = [
                    'All Academic Transcripts',
                    'TOEFL/IELTS Scores',
                    'Financial Documentation',
                    'Passport Copy',
                    'Personal Statement'
                ];
                break;
            default:
                requirements = ['Requirements will be specified based on your selections'];
        }
        
        let html = '<ul class="list-unstyled small">';
        requirements.forEach(req => {
            html += `<li class="requirement-item mb-2"><i class="fas fa-check-circle text-info me-2"></i>${req}</li>`;
        });
        html += '</ul>';
        
        $('#requirements-list').html(html);
    }
    
    // Form submission
    $('#start-application-form').submit(function(e) {
        e.preventDefault();
        
        // Show loading state
        $('#start-application-btn').prop('disabled', true);
        $('#start-application-btn').html('<i class="fas fa-spinner fa-spin me-2"></i>Creating Application...');
        
        // Submit form
        this.submit();
    });
});
</script>
@endsection