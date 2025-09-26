{{-- resources/views/admissions/portal/start-application.blade.php --}}
@extends('layouts.portal')

@section('title', 'Start New Application - IntelliCampus Admissions')

@section('styles')
<style>
    /* Application Type Cards */
    .application-type-card {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
        height: 100%;
        position: relative;
    }
    
    .application-type-card:hover {
        border-color: #007bff;
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,123,255,0.3);
        background: #f0f7ff;
    }
    
    .application-type-card.selected {
        border-color: #28a745;
        background: #f0fff4;
        box-shadow: 0 3px 10px rgba(40,167,69,0.2);
    }
    
    .application-type-card.selected::after {
        content: '\2713';
        position: absolute;
        top: 10px;
        right: 10px;
        background: #28a745;
        color: white;
        width: 25px;
        height: 25px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }
    
    .application-type-card .icon {
        font-size: 3rem;
        margin-bottom: 15px;
    }
    
    /* Selected Item Display */
    .selected-display {
        background: #e7f5ff;
        border: 1px solid #339af0;
        border-radius: 8px;
        padding: 12px;
        margin-top: 20px;
        display: none;
    }
    
    .selected-display.show {
        display: block;
    }
    
    .selected-display strong {
        color: #1971c2;
    }
    
    /* Program Selection List */
    .program-item {
        border: 1px solid #dee2e6;
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
    }
    
    .program-item:hover {
        border-color: #007bff;
        background: #f8f9fa;
        transform: translateX(5px);
    }
    
    .program-item.selected {
        border-color: #28a745;
        background: #f0fff4;
        border-left: 4px solid #28a745;
    }
    
    .program-item.selected .program-radio {
        background: #28a745;
        border-color: #28a745;
    }
    
    .program-item.selected .program-radio::after {
        content: '';
        position: absolute;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: white;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }
    
    .program-radio {
        width: 20px;
        height: 20px;
        border: 2px solid #dee2e6;
        border-radius: 50%;
        display: inline-block;
        position: relative;
        margin-right: 10px;
        transition: all 0.3s ease;
    }
    
    /* Step Indicator */
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
    
    /* Term Selectors */
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
        transform: scale(1.05);
    }
    
    .term-selector.selected {
        border-color: #28a745;
        background: #d4edda;
        color: #155724;
    }
    
    /* Fee Breakdown */
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
    
    .required-field {
        color: red;
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
                    <div class="small">Personal Info</div>
                </div>
                <div class="step" id="step-3">
                    <div class="step-number">3</div>
                    <div class="small">Select Program</div>
                </div>
                <div class="step" id="step-4">
                    <div class="step-number">4</div>
                    <div class="small">Term & Details</div>
                </div>
                <div class="step" id="step-5">
                    <div class="step-number">5</div>
                    <div class="small">Review & Start</div>
                </div>
            </div>
        </div>
    </div>

    <form id="start-application-form" method="POST" action="{{ route('admissions.portal.create') }}">
        @csrf
        <input type="hidden" name="save_and_exit" id="save_and_exit" value="false">
        
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
                                    <div class="application-type-card" data-type="freshman" data-title="First-Year Student">
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
                                    <div class="application-type-card" data-type="transfer" data-title="Transfer Student">
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
                                    <div class="application-type-card" data-type="graduate" data-title="Graduate Student">
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
                                    <div class="application-type-card" data-type="international" data-title="International Student">
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
                                    <div class="application-type-card" data-type="readmission" data-title="Readmission">
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
                            </div>
                            
                            <input type="hidden" name="application_type" id="application_type" required>
                            
                            {{-- Selected Display --}}
                            <div class="selected-display" id="type-selected-display">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <strong>Selected:</strong> <span id="selected-type-text"></span>
                            </div>
                            
                            <div class="text-end mt-4">
                                <button type="button" class="btn btn-primary next-step" data-step="1" disabled>
                                    Next: Personal Information <i class="fas fa-arrow-right ms-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 2: Personal Information (EXPANDED) --}}
        <div class="step-content" id="step-2-content" style="display: none;">
            <div class="row mb-4">
                <div class="col-lg-10 mx-auto">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-user me-2"></i>Personal Information
                            </h5>
                        </div>
                        <div class="card-body">
                            {{-- Name Information --}}
                            <h6 class="mb-3">Name Information</h6>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="first_name" class="form-label">First Name <span class="required-field">*</span></label>
                                    <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                           id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                                    @error('first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="middle_name" class="form-label">Middle Name</label>
                                    <input type="text" class="form-control" id="middle_name" name="middle_name" value="{{ old('middle_name') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="last_name" class="form-label">Last Name <span class="required-field">*</span></label>
                                    <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                           id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                                    @error('last_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Personal Details --}}
                            <hr>
                            <h6 class="mb-3">Personal Details</h6>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="date_of_birth" class="form-label">Date of Birth <span class="required-field">*</span></label>
                                    <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" 
                                           id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" required>
                                    @error('date_of_birth')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="gender" class="form-label">Gender</label>
                                    <select class="form-select" id="gender" name="gender">
                                        <option value="">Select Gender</option>
                                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="nationality" class="form-label">Nationality <span class="required-field">*</span></label>
                                    <input type="text" class="form-control @error('nationality') is-invalid @enderror" 
                                           id="nationality" name="nationality" value="{{ old('nationality', 'Liberian') }}" 
                                           placeholder="e.g., Liberian" required>
                                    @error('nationality')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="national_id" class="form-label">National ID Number</label>
                                    <input type="text" class="form-control" id="national_id" name="national_id" 
                                           value="{{ old('national_id') }}" placeholder="National identification number">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="passport_number" class="form-label">Passport Number (if applicable)</label>
                                    <input type="text" class="form-control" id="passport_number" name="passport_number" 
                                           value="{{ old('passport_number') }}" placeholder="For international applicants">
                                </div>
                            </div>

                            {{-- Contact Information --}}
                            <hr>
                            <h6 class="mb-3">Contact Information</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address <span class="required-field">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email') }}" required>
                                    <small class="text-muted">You'll use this email to access your application later</small>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone_primary" class="form-label">Primary Phone <span class="required-field">*</span></label>
                                    <input type="tel" class="form-control @error('phone_primary') is-invalid @enderror" 
                                           id="phone_primary" name="phone_primary" value="{{ old('phone_primary') }}" 
                                           placeholder="+231 77 123 4567" required>
                                    @error('phone_primary')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Address Information --}}
                            <hr>
                            <h6 class="mb-3">Address Information</h6>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="current_address" class="form-label">Current Address <span class="required-field">*</span></label>
                                    <textarea class="form-control @error('current_address') is-invalid @enderror" 
                                              id="current_address" name="current_address" rows="2" required>{{ old('current_address') }}</textarea>
                                    @error('current_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="city" class="form-label">City <span class="required-field">*</span></label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                           id="city" name="city" value="{{ old('city', 'Monrovia') }}" required>
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="state_province" class="form-label">State/Province</label>
                                    <input type="text" class="form-control" id="state_province" name="state_province" 
                                           value="{{ old('state_province', 'Montserrado') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="country" class="form-label">Country <span class="required-field">*</span></label>
                                    <input type="text" class="form-control @error('country') is-invalid @enderror" 
                                           id="country" name="country" value="{{ old('country', 'Liberia') }}" required>
                                    @error('country')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="alert alert-info mt-3">
                                <i class="fas fa-info-circle"></i> Fields marked with <span class="required-field">*</span> are required
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-secondary prev-step" data-step="2">
                                    <i class="fas fa-arrow-left me-1"></i> Previous
                                </button>
                                <div>
                                    <button type="button" class="btn btn-outline-success save-exit-btn me-2">
                                        <i class="fas fa-save me-1"></i> Save & Exit
                                    </button>
                                    <button type="button" class="btn btn-primary next-step" data-step="2">
                                        Next: Select Program <i class="fas fa-arrow-right ms-1"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 3: Select Program --}}
        <div class="step-content" id="step-3-content" style="display: none;">
            <div class="row mb-4">
                <div class="col-lg-10 mx-auto">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-book me-2"></i>Select Your Program
                            </h5>
                        </div>
                        <div class="card-body">
                            {{-- Search and Filter --}}
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
                                            <option value="{{ $department->name }}">{{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            {{-- Programs List --}}
                            <div class="programs-list" style="max-height: 400px; overflow-y: auto;">
                                @foreach($programs as $program)
                                <div class="program-item" data-program-id="{{ $program->id }}" data-department="{{ $program->department }}">
                                    <div class="d-flex align-items-center">
                                        <span class="program-radio"></span>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ $program->name }}</h6>
                                            <p class="text-muted small mb-0">
                                                {{ $program->degree_type ?? 'Bachelor' }} | Department: {{ $program->department }}
                                                <br>
                                                Duration: {{ $program->duration_years }} years | Credits: {{ $program->total_credits }}
                                            </p>
                                        </div>
                                        <div>
                                            <span class="badge bg-info">{{ $program->delivery_mode ?? 'On Campus' }}</span>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            
                            <input type="hidden" name="program_id" id="program_id" required>
                            
                            {{-- Selected Program Display --}}
                            <div class="selected-display mt-3" id="program-selected-display">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <strong>Selected Program:</strong> <span id="selected-program-text"></span>
                            </div>
                            
                            {{-- Alternate Program Option --}}
                            <div class="mt-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="add-alternate">
                                    <label class="form-check-label" for="add-alternate">
                                        Add an alternate program choice (optional)
                                    </label>
                                </div>
                                
                                <div id="alternate-program-section" style="display: none;" class="mt-3">
                                    <select class="form-select" name="alternate_program_id" id="alternate_program_id">
                                        <option value="">Select alternate program...</option>
                                        @foreach($programs as $program)
                                            <option value="{{ $program->id }}">{{ $program->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-secondary prev-step" data-step="3">
                                    <i class="fas fa-arrow-left me-1"></i> Previous
                                </button>
                                <button type="button" class="btn btn-primary next-step" data-step="3" disabled>
                                    Next: Term Selection <i class="fas fa-arrow-right ms-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 4: Term & Details --}}
        <div class="step-content" id="step-4-content" style="display: none;">
            <div class="row mb-4">
                <div class="col-lg-10 mx-auto">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-calendar-alt me-2"></i>Select Entry Term
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="entry_year" class="form-label">Year <span class="required-field">*</span></label>
                                    <select class="form-select" name="entry_year" id="entry_year" required>
                                        @for($year = date('Y'); $year <= date('Y') + 2; $year++)
                                            <option value="{{ $year }}">{{ $year }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Term <span class="required-field">*</span></label>
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
                                    <input type="hidden" name="entry_term" id="entry_term" required>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <label class="form-label">Available Academic Terms <span class="required-field">*</span></label>
                                <select class="form-select" name="term_id" id="term_id" required>
                                    <option value="">Select term...</option>
                                    @foreach($availableTerms as $term)
                                        <option value="{{ $term->id }}" data-deadline="{{ $term->admission_deadline->format('F d, Y') }}">
                                            {{ $term->name }} (Deadline: {{ $term->admission_deadline->format('M d, Y') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            {{-- Selected Term Display --}}
                            <div class="selected-display mt-3" id="term-selected-display">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <strong>Selected Term:</strong> <span id="selected-term-text"></span>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-secondary prev-step" data-step="4">
                                    <i class="fas fa-arrow-left me-1"></i> Previous
                                </button>
                                <button type="button" class="btn btn-primary next-step" data-step="4" disabled>
                                    Next: Review <i class="fas fa-arrow-right ms-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 5: Review & Start --}}
        <div class="step-content" id="step-5-content" style="display: none;">
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
                                            <td class="text-muted">Name:</td>
                                            <td id="review-name">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Date of Birth:</td>
                                            <td id="review-dob">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Email:</td>
                                            <td id="review-email">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Phone:</td>
                                            <td id="review-phone">-</td>
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
                                    
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="agree_terms" name="agree_terms" required>
                                        <label class="form-check-label" for="agree_terms">
                                            I certify that all information provided is accurate and agree to the terms
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="mb-3">Application Fees</h6>
                                            <div class="fee-breakdown">
                                                <div class="fee-item">
                                                    <span>Application Fee:</span>
                                                    <span>${{ number_format($applicationFee ?? 50, 2) }}</span>
                                                </div>
                                                <div class="fee-item">
                                                    <span>Processing Fee:</span>
                                                    <span>${{ number_format($processingFee ?? 0, 2) }}</span>
                                                </div>
                                                <div class="fee-item">
                                                    <span class="fw-bold">Total:</span>
                                                    <span class="fw-bold">${{ number_format(($applicationFee ?? 50) + ($processingFee ?? 0), 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-secondary prev-step" data-step="5">
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

{{-- Success Modal for UUID Display --}}
<div class="modal fade" id="applicationCreatedModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Application Created Successfully!</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-check-circle text-success mb-3" style="font-size: 3rem;"></i>
                <div class="alert alert-warning">
                    <strong>Important: Save these details!</strong>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">Application Number:</label>
                    <div class="bg-light p-2 rounded">
                        <span id="modal-app-number"></span>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">Application UUID:</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="modal-uuid" readonly>
                        <button class="btn btn-primary" onclick="copyModalUUID()">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                </div>
                <p class="text-muted small">Use these details to continue your application later</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let currentStep = 1;
    
    // Step 1: Application Type Selection
    $('.application-type-card').click(function() {
        $('.application-type-card').removeClass('selected');
        $(this).addClass('selected');
        $('#application_type').val($(this).data('type'));
        
        // Show selected display
        $('#selected-type-text').text($(this).data('title'));
        $('#type-selected-display').addClass('show');
        
        $('.next-step[data-step="1"]').prop('disabled', false);
    });
    
    // Step 2: Validate personal info
    function validateStep2() {
        const required = ['first_name', 'last_name', 'date_of_birth', 'nationality', 
                         'email', 'phone_primary', 'current_address', 'city', 'country'];
        let isValid = true;
        
        required.forEach(field => {
            if (!$('#' + field).val()) {
                isValid = false;
            }
        });
        
        $('.next-step[data-step="2"]').prop('disabled', !isValid);
    }
    
    // Add validation listeners
    $('#first_name, #last_name, #date_of_birth, #nationality, #email, #phone_primary, #current_address, #city, #country').on('input change', validateStep2);
    
    // Save & Exit button
    $('.save-exit-btn').click(function() {
        if (confirm('Your progress will be saved. You can continue later using your application link. Proceed?')) {
            $('#save_and_exit').val('true');
            $('#start-application-form').submit();
        }
    });
    
    // Step 3: Program Selection
    $('.program-item').click(function() {
        $('.program-item').removeClass('selected');
        $(this).addClass('selected');
        $('#program_id').val($(this).data('program-id'));
        
        // Show selected display
        const programName = $(this).find('h6').text();
        $('#selected-program-text').text(programName);
        $('#program-selected-display').addClass('show');
        
        $('.next-step[data-step="3"]').prop('disabled', false);
    });
    
    // Program Search
    $('#program-search').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('.program-item').each(function() {
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
        const department = $(this).val();
        if (department === '') {
            $('.program-item').show();
        } else {
            $('.program-item').each(function() {
                if ($(this).data('department') === department) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
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
    
    // Step 4: Term Selection
    $('.term-selector').click(function() {
        $('.term-selector').removeClass('selected');
        $(this).addClass('selected');
        $('#entry_term').val($(this).data('term'));
        checkStep4Completion();
    });
    
    $('#term_id').change(function() {
        const selectedOption = $(this).find('option:selected');
        if (selectedOption.val()) {
            $('#selected-term-text').text(selectedOption.text());
            $('#term-selected-display').addClass('show');
        }
        checkStep4Completion();
    });
    
    function checkStep4Completion() {
        if ($('#entry_term').val() && $('#term_id').val()) {
            $('.next-step[data-step="4"]').prop('disabled', false);
        }
    }
    
    // Step 5: Agreement
    $('#agree_terms').change(function() {
        $('#start-application-btn').prop('disabled', !$(this).is(':checked'));
    });
    
    // Navigation
    $('.next-step').click(function() {
        const step = parseInt($(this).data('step'));
        showStep(step + 1);
        updateReviewSection();
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
        $('html, body').animate({ scrollTop: 0 }, 300);
    }
    
    function updateReviewSection() {
        if (currentStep === 5) {
            $('#review-type').text($('.application-type-card.selected').data('title') || '-');
            $('#review-name').text($('#first_name').val() + ' ' + $('#middle_name').val() + ' ' + $('#last_name').val());
            $('#review-dob').text($('#date_of_birth').val());
            $('#review-email').text($('#email').val());
            $('#review-phone').text($('#phone_primary').val());
            $('#review-program').text($('#selected-program-text').text() || '-');
            $('#review-alternate').text($('#alternate_program_id option:selected').text() || 'None');
            $('#review-term').text($('#entry_term').val() + ' ' + $('#entry_year').val());
            $('#review-deadline').text($('#term_id option:selected').data('deadline') || '-');
        }
    }
    
    // Form submission
    $('#start-application-form').submit(function(e) {
        e.preventDefault();
        $('#start-application-btn').prop('disabled', true);
        $('#start-application-btn').html('<i class="fas fa-spinner fa-spin me-2"></i>Creating Application...');
        this.submit();
    });
    
    // Check for session flash data for UUID
    @if(session('application_uuid'))
        $('#modal-app-number').text('{{ session('application_number') }}');
        $('#modal-uuid').val('{{ session('application_uuid') }}');
        $('#applicationCreatedModal').modal('show');
    @endif
});

function copyModalUUID() {
    const field = document.getElementById('modal-uuid');
    field.select();
    document.execCommand('copy');
    
    // Show feedback
    alert('UUID copied to clipboard!');
}
</script>
@endsection