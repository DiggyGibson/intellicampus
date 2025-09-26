@extends('layouts.app')

@section('title', 'Student Profile: ' . $student->display_name)

@section('breadcrumb')
    <a href="{{ route('students.index') }}">Students</a>
    <i class="fas fa-chevron-right"></i>
    <span>{{ $student->display_name }}</span>
@endsection

@section('page-actions')
    <div class="page-actions-group">
        @if($student->enrollment_status === 'active')
            <a href="{{ route('students.enrollment.manage', $student) }}" class="btn btn-warning">
                <i class="fas fa-graduation-cap"></i> Manage Enrollment
            </a>
        @endif
        <a href="{{ route('students.edit', $student) }}" class="btn btn-primary">
            <i class="fas fa-edit"></i> Edit Student
        </a>
        <form action="{{ route('students.destroy', $student) }}" method="POST" class="d-inline"
              onsubmit="return confirm('Are you sure you want to delete this student? This action cannot be undone.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash"></i> Delete
            </button>
        </form>
    </div>
@endsection

@section('content')
    <div class="container-fluid px-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Student Status Bar -->
        <div class="student-status-bar card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="status-item">
                            <label class="status-label">Student ID</label>
                            <div class="status-value">
                                <i class="fas fa-id-card text-primary me-2"></i>
                                <strong>{{ $student->student_id }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="status-item">
                            <label class="status-label">Enrollment Status</label>
                            <div class="status-value">
                                @if($student->enrollment_status == 'active')
                                    <span class="badge bg-success px-3 py-2">
                                        <i class="fas fa-check-circle me-1"></i> Active
                                    </span>
                                @elseif($student->enrollment_status == 'graduated')
                                    <span class="badge bg-info px-3 py-2">
                                        <i class="fas fa-user-graduate me-1"></i> Graduated
                                    </span>
                                @elseif($student->enrollment_status == 'suspended')
                                    <span class="badge bg-danger px-3 py-2">
                                        <i class="fas fa-ban me-1"></i> Suspended
                                    </span>
                                @else
                                    <span class="badge bg-secondary px-3 py-2">
                                        {{ ucfirst($student->enrollment_status) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="status-item">
                            <label class="status-label">Academic Standing</label>
                            <div class="status-value">
                                @if($student->academic_standing == 'good')
                                    <span class="badge bg-success px-3 py-2">Good Standing</span>
                                @elseif($student->academic_standing == 'probation')
                                    <span class="badge bg-warning text-dark px-3 py-2">Probation</span>
                                @else
                                    <span class="badge bg-danger px-3 py-2">{{ ucfirst($student->academic_standing) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="status-item">
                            <label class="status-label">Academic Level</label>
                            <div class="status-value">
                                <span class="badge bg-primary px-3 py-2">
                                    {{ ucfirst($student->academic_level) }}
                                </span>
                                @if($student->is_international)
                                    <span class="badge bg-info ms-2 px-2 py-1">
                                        <i class="fas fa-globe me-1"></i> International
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Tab Navigation -->
        <div class="card">
            <div class="card-header p-0">
                <ul class="nav nav-tabs nav-tabs-custom" id="studentTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" 
                                data-bs-target="#overview" type="button" role="tab">
                            <i class="fas fa-home me-2"></i> Overview
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="personal-tab" data-bs-toggle="tab" 
                                data-bs-target="#personal" type="button" role="tab">
                            <i class="fas fa-user me-2"></i> Personal Info
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="academic-tab" data-bs-toggle="tab" 
                                data-bs-target="#academic" type="button" role="tab">
                            <i class="fas fa-graduation-cap me-2"></i> Academic
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="contacts-tab" data-bs-toggle="tab" 
                                data-bs-target="#contacts" type="button" role="tab">
                            <i class="fas fa-address-book me-2"></i> Contacts
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="medical-tab" data-bs-toggle="tab" 
                                data-bs-target="#medical" type="button" role="tab">
                            <i class="fas fa-heartbeat me-2"></i> Medical
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="documents-tab" data-bs-toggle="tab" 
                                data-bs-target="#documents" type="button" role="tab">
                            <i class="fas fa-folder-open me-2"></i> Documents
                        </button>
                    </li>
                </ul>
            </div>
            
            <div class="card-body">
                <div class="tab-content" id="studentTabContent">
                    <!-- Overview Tab -->
                    <div class="tab-pane fade show active" id="overview" role="tabpanel">
                        <div class="row g-4">
                            <!-- Quick Info Card -->
                            <div class="col-lg-4">
                                <div class="info-card">
                                    <div class="info-card-header">
                                        <h5><i class="fas fa-info-circle me-2"></i> Quick Information</h5>
                                    </div>
                                    <div class="info-card-body">
                                        <dl class="info-list">
                                            <div class="info-item">
                                                <dt>Full Name</dt>
                                                <dd>{{ $student->first_name }} {{ $student->middle_name }} {{ $student->last_name }}</dd>
                                            </div>
                                            <div class="info-item">
                                                <dt>Email</dt>
                                                <dd><a href="mailto:{{ $student->email }}">{{ $student->email }}</a></dd>
                                            </div>
                                            <div class="info-item">
                                                <dt>Phone</dt>
                                                <dd>{{ $student->phone ?? 'Not provided' }}</dd>
                                            </div>
                                            <div class="info-item">
                                                <dt>Date of Birth</dt>
                                                <dd>
                                                    {{ $student->date_of_birth ? $student->date_of_birth->format('M d, Y') : 'Not provided' }}
                                                    @if($student->age)
                                                        <span class="text-muted">({{ $student->age }} years)</span>
                                                    @endif
                                                </dd>
                                            </div>
                                            <div class="info-item">
                                                <dt>Gender</dt>
                                                <dd>{{ ucfirst($student->gender) }}</dd>
                                            </div>
                                        </dl>
                                    </div>
                                </div>
                            </div>

                            <!-- Academic Summary Card -->
                            <div class="col-lg-4">
                                <div class="info-card">
                                    <div class="info-card-header bg-primary text-white">
                                        <h5><i class="fas fa-book me-2"></i> Academic Summary</h5>
                                    </div>
                                    <div class="info-card-body">
                                        <dl class="info-list">
                                            <div class="info-item">
                                                <dt>Program</dt>
                                                <dd class="fw-bold">{{ $student->program_name }}</dd>
                                            </div>
                                            <div class="info-item">
                                                <dt>Department</dt>
                                                <dd>{{ $student->department ?? 'Not assigned' }}</dd>
                                            </div>
                                            <div class="info-item">
                                                <dt>Major</dt>
                                                <dd>{{ $student->major ?? 'Undeclared' }}</dd>
                                            </div>
                                            <div class="info-item">
                                                <dt>Advisor</dt>
                                                <dd>{{ $student->advisor_name ?? 'Not assigned' }}</dd>
                                            </div>
                                            <div class="info-item">
                                                <dt>Expected Graduation</dt>
                                                <dd>{{ $student->expected_graduation_year ?? 'Not set' }}</dd>
                                            </div>
                                        </dl>
                                    </div>
                                </div>
                            </div>

                            <!-- GPA & Progress Card -->
                            <div class="col-lg-4">
                                <div class="info-card">
                                    <div class="info-card-header bg-success text-white">
                                        <h5><i class="fas fa-chart-line me-2"></i> Academic Progress</h5>
                                    </div>
                                    <div class="info-card-body">
                                        <!-- GPA Display -->
                                        <div class="gpa-display mb-4">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span>Current GPA</span>
                                                <span class="fs-3 fw-bold">{{ number_format($student->current_gpa, 2) }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span>Cumulative GPA</span>
                                                <span class="fs-4 fw-semibold">{{ number_format($student->cumulative_gpa, 2) }}</span>
                                            </div>
                                        </div>

                                        <!-- Credits Progress -->
                                        <div class="credits-progress">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Credits Progress</span>
                                                <span class="fw-semibold">{{ $student->credits_earned }} / {{ $student->credits_required }}</span>
                                            </div>
                                            <div class="progress" style="height: 10px;">
                                                <div class="progress-bar bg-success" role="progressbar" 
                                                     style="width: {{ $student->progress_percentage }}%"
                                                     aria-valuenow="{{ $student->progress_percentage }}" 
                                                     aria-valuemin="0" aria-valuemax="100">
                                                </div>
                                            </div>
                                            <p class="text-center mt-2 mb-0 text-muted">
                                                {{ $student->progress_percentage }}% Complete
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Emergency Contact Alert -->
                        <div class="alert alert-danger alert-important mt-4">
                            <h6 class="alert-heading">
                                <i class="fas fa-exclamation-triangle me-2"></i> Emergency Contact
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Name:</strong> {{ $student->emergency_contact_name ?? 'Not provided' }}
                                </div>
                                <div class="col-md-6">
                                    <strong>Phone:</strong> {{ $student->emergency_contact_phone ?? 'Not provided' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Personal Information Tab -->
                    <div class="tab-pane fade" id="personal" role="tabpanel">
                        <h4 class="mb-4">Personal Information</h4>
                        
                        <div class="row g-4">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <div class="detail-section">
                                    <h5 class="section-title">Basic Information</h5>
                                    <dl class="detail-list">
                                        <div class="detail-item">
                                            <dt>Full Name</dt>
                                            <dd>{{ $student->first_name }} {{ $student->middle_name }} {{ $student->last_name }}</dd>
                                        </div>
                                        @if($student->preferred_name)
                                        <div class="detail-item">
                                            <dt>Preferred Name</dt>
                                            <dd>{{ $student->preferred_name }}</dd>
                                        </div>
                                        @endif
                                        <div class="detail-item">
                                            <dt>Date of Birth</dt>
                                            <dd>{{ $student->date_of_birth ? $student->date_of_birth->format('F d, Y') : 'Not provided' }}</dd>
                                        </div>
                                        <div class="detail-item">
                                            <dt>Place of Birth</dt>
                                            <dd>{{ $student->place_of_birth ?? 'Not provided' }}</dd>
                                        </div>
                                        <div class="detail-item">
                                            <dt>Gender</dt>
                                            <dd>{{ ucfirst($student->gender) }}</dd>
                                        </div>
                                        <div class="detail-item">
                                            <dt>Marital Status</dt>
                                            <dd>{{ ucfirst($student->marital_status ?? 'Not provided') }}</dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>

                            <!-- Identity Information -->
                            <div class="col-md-6">
                                <div class="detail-section">
                                    <h5 class="section-title">Identity & Nationality</h5>
                                    <dl class="detail-list">
                                        <div class="detail-item">
                                            <dt>Nationality</dt>
                                            <dd>{{ $student->nationality ?? 'Not provided' }}</dd>
                                        </div>
                                        <div class="detail-item">
                                            <dt>National ID</dt>
                                            <dd>{{ $student->national_id_number ?? 'Not provided' }}</dd>
                                        </div>
                                        @if($student->religion)
                                        <div class="detail-item">
                                            <dt>Religion</dt>
                                            <dd>{{ $student->religion }}</dd>
                                        </div>
                                        @endif
                                        @if($student->ethnicity)
                                        <div class="detail-item">
                                            <dt>Ethnicity</dt>
                                            <dd>{{ $student->ethnicity }}</dd>
                                        </div>
                                        @endif
                                    </dl>
                                </div>
                            </div>
                        </div>

                        @if($student->is_international)
                        <!-- International Student Information -->
                        <div class="international-info-card mt-4">
                            <h5 class="card-title">
                                <i class="fas fa-passport me-2"></i> International Student Information
                            </h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <strong>Passport Number:</strong>
                                    <p>{{ $student->passport_number }}</p>
                                </div>
                                <div class="col-md-4">
                                    <strong>Visa Status:</strong>
                                    <p>{{ $student->visa_status }}</p>
                                </div>
                                <div class="col-md-4">
                                    <strong>Visa Expiry:</strong>
                                    <p>{{ $student->visa_expiry ? $student->visa_expiry->format('F d, Y') : 'Not provided' }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Academic Tab -->
                    <div class="tab-pane fade" id="academic" role="tabpanel">
                        <h4 class="mb-4">Academic Information</h4>
                        
                        <div class="row g-4">
                            <!-- Current Academic Status -->
                            <div class="col-md-6">
                                <div class="detail-section">
                                    <h5 class="section-title">Current Status</h5>
                                    <dl class="detail-list">
                                        <div class="detail-item">
                                            <dt>Department</dt>
                                            <dd>{{ $student->department ?? 'Not assigned' }}</dd>
                                        </div>
                                        <div class="detail-item">
                                            <dt>Program</dt>
                                            <dd>{{ $student->program_name }}</dd>
                                        </div>
                                        <div class="detail-item">
                                            <dt>Major</dt>
                                            <dd>{{ $student->major ?? 'Undeclared' }}</dd>
                                        </div>
                                        <div class="detail-item">
                                            <dt>Minor</dt>
                                            <dd>{{ $student->minor ?? 'None' }}</dd>
                                        </div>
                                        <div class="detail-item">
                                            <dt>Academic Level</dt>
                                            <dd>{{ ucfirst($student->academic_level) }}</dd>
                                        </div>
                                        <div class="detail-item">
                                            <dt>Admission Date</dt>
                                            <dd>{{ $student->admission_date ? $student->admission_date->format('F d, Y') : 'Not provided' }}</dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>

                            <!-- Previous Education -->
                            <div class="col-md-6">
                                <div class="detail-section">
                                    <h5 class="section-title">Previous Education</h5>
                                    <dl class="detail-list">
                                        <div class="detail-item">
                                            <dt>High School</dt>
                                            <dd>{{ $student->high_school_name ?? 'Not provided' }}</dd>
                                        </div>
                                        <div class="detail-item">
                                            <dt>Graduation Year</dt>
                                            <dd>{{ $student->high_school_graduation_year ?? 'Not provided' }}</dd>
                                        </div>
                                        <div class="detail-item">
                                            <dt>High School GPA</dt>
                                            <dd>{{ $student->high_school_gpa ? number_format($student->high_school_gpa, 2) : 'Not provided' }}</dd>
                                        </div>
                                        @if($student->previous_university)
                                        <div class="detail-item">
                                            <dt>Previous University</dt>
                                            <dd>{{ $student->previous_university }}</dd>
                                        </div>
                                        @endif
                                    </dl>
                                </div>
                            </div>
                        </div>

                        <!-- Academic Performance Dashboard -->
                        <div class="performance-dashboard mt-4">
                            <h5 class="mb-3">Academic Performance</h5>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="performance-card text-center">
                                        <i class="fas fa-star fa-2x text-warning mb-2"></i>
                                        <h3 class="mb-0">{{ number_format($student->current_gpa, 2) }}</h3>
                                        <small class="text-muted">Current GPA</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="performance-card text-center">
                                        <i class="fas fa-chart-bar fa-2x text-info mb-2"></i>
                                        <h3 class="mb-0">{{ number_format($student->cumulative_gpa, 2) }}</h3>
                                        <small class="text-muted">Cumulative GPA</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="performance-card text-center">
                                        <i class="fas fa-book-open fa-2x text-success mb-2"></i>
                                        <h3 class="mb-0">{{ $student->credits_earned }}</h3>
                                        <small class="text-muted">Credits Earned</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="performance-card text-center">
                                        <i class="fas fa-flag-checkered fa-2x text-primary mb-2"></i>
                                        <h3 class="mb-0">{{ $student->credits_required }}</h3>
                                        <small class="text-muted">Credits Required</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contacts Tab -->
                    <div class="tab-pane fade" id="contacts" role="tabpanel">
                        <h4 class="mb-4">Contact Information</h4>
                        
                        <div class="row g-4">
                            <!-- Primary Contact -->
                            <div class="col-md-6">
                                <div class="detail-section">
                                    <h5 class="section-title">Primary Contact</h5>
                                    <dl class="detail-list">
                                        <div class="detail-item">
                                            <dt>Email</dt>
                                            <dd>{{ $student->email }}</dd>
                                        </div>
                                        @if($student->secondary_email)
                                        <div class="detail-item">
                                            <dt>Secondary Email</dt>
                                            <dd>{{ $student->secondary_email }}</dd>
                                        </div>
                                        @endif
                                        <div class="detail-item">
                                            <dt>Mobile Phone</dt>
                                            <dd>{{ $student->phone ?? 'Not provided' }}</dd>
                                        </div>
                                        @if($student->home_phone)
                                        <div class="detail-item">
                                            <dt>Home Phone</dt>
                                            <dd>{{ $student->home_phone }}</dd>
                                        </div>
                                        @endif
                                    </dl>
                                </div>
                            </div>

                            <!-- Addresses -->
                            <div class="col-md-6">
                                <div class="detail-section">
                                    <h5 class="section-title">Addresses</h5>
                                    <div class="mb-3">
                                        <strong>Current Address</strong>
                                        <p class="mb-0">{{ $student->address ?? 'Not provided' }}</p>
                                    </div>
                                    @if($student->permanent_address)
                                    <div>
                                        <strong>Permanent Address</strong>
                                        <p class="mb-0">{{ $student->permanent_address }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Guardian Information -->
                        @if($student->guardian_name)
                        <div class="contact-card guardian-card mt-4">
                            <h5 class="card-title">
                                <i class="fas fa-user-shield me-2"></i> Guardian Information
                            </h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <strong>Name:</strong>
                                    <p>{{ $student->guardian_name }}</p>
                                </div>
                                @if($student->guardian_phone)
                                <div class="col-md-4">
                                    <strong>Phone:</strong>
                                    <p>{{ $student->guardian_phone }}</p>
                                </div>
                                @endif
                                @if($student->guardian_email)
                                <div class="col-md-4">
                                    <strong>Email:</strong>
                                    <p>{{ $student->guardian_email }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- Emergency Contact -->
                        <div class="contact-card emergency-card mt-4">
                            <h5 class="card-title">
                                <i class="fas fa-ambulance me-2"></i> Emergency Contact
                            </h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <strong>Name:</strong>
                                    <p>{{ $student->emergency_contact_name ?? 'Not provided' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Phone:</strong>
                                    <p>{{ $student->emergency_contact_phone ?? 'Not provided' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Next of Kin -->
                        @if($student->next_of_kin_name)
                        <div class="contact-card mt-4">
                            <h5 class="card-title">
                                <i class="fas fa-users me-2"></i> Next of Kin
                            </h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <strong>Name:</strong>
                                    <p>{{ $student->next_of_kin_name }}</p>
                                </div>
                                @if($student->next_of_kin_relationship)
                                <div class="col-md-4">
                                    <strong>Relationship:</strong>
                                    <p>{{ $student->next_of_kin_relationship }}</p>
                                </div>
                                @endif
                                @if($student->next_of_kin_phone)
                                <div class="col-md-4">
                                    <strong>Phone:</strong>
                                    <p>{{ $student->next_of_kin_phone }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Medical Tab -->
                    <div class="tab-pane fade" id="medical" role="tabpanel">
                        <h4 class="mb-4">Medical Information</h4>
                        
                        <div class="row g-4">
                            <!-- Medical Details -->
                            <div class="col-md-6">
                                <div class="detail-section">
                                    <h5 class="section-title">Medical Details</h5>
                                    <dl class="detail-list">
                                        <div class="detail-item">
                                            <dt>Blood Group</dt>
                                            <dd class="text-danger fw-bold">{{ $student->blood_group ?? 'Not provided' }}</dd>
                                        </div>
                                        <div class="detail-item">
                                            <dt>Medical Conditions/Allergies</dt>
                                            <dd>{{ $student->medical_conditions ?? 'None reported' }}</dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>

                            <!-- Insurance Information -->
                            <div class="col-md-6">
                                <div class="detail-section">
                                    <h5 class="section-title">Insurance Information</h5>
                                    <dl class="detail-list">
                                        <div class="detail-item">
                                            <dt>Provider</dt>
                                            <dd>{{ $student->insurance_provider ?? 'Not provided' }}</dd>
                                        </div>
                                        @if($student->insurance_policy_number)
                                        <div class="detail-item">
                                            <dt>Policy Number</dt>
                                            <dd>{{ $student->insurance_policy_number }}</dd>
                                        </div>
                                        @endif
                                        @if($student->insurance_expiry)
                                        <div class="detail-item">
                                            <dt>Expiry Date</dt>
                                            <dd>{{ $student->insurance_expiry->format('F d, Y') }}</dd>
                                        </div>
                                        @endif
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Documents Tab -->
                    <div class="tab-pane fade" id="documents" role="tabpanel">
                        <h4 class="mb-4">Documents</h4>
                        
                        <div class="row g-3">
                            <!-- Document Status Cards -->
                            <div class="col-md-4">
                                <div class="document-card {{ $student->has_profile_photo ? 'success' : 'danger' }}">
                                    <div class="document-icon">
                                        <i class="fas {{ $student->has_profile_photo ? 'fa-check-circle' : 'fa-times-circle' }} fa-3x"></i>
                                    </div>
                                    <h5>Profile Photo</h5>
                                    <p class="status">{{ $student->has_profile_photo ? 'Uploaded' : 'Not Uploaded' }}</p>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="document-card {{ $student->has_national_id_copy ? 'success' : 'danger' }}">
                                    <div class="document-icon">
                                        <i class="fas {{ $student->has_national_id_copy ? 'fa-check-circle' : 'fa-times-circle' }} fa-3x"></i>
                                    </div>
                                    <h5>National ID Copy</h5>
                                    <p class="status">{{ $student->has_national_id_copy ? 'Uploaded' : 'Not Uploaded' }}</p>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="document-card {{ $student->has_high_school_certificate ? 'success' : 'danger' }}">
                                    <div class="document-icon">
                                        <i class="fas {{ $student->has_high_school_certificate ? 'fa-check-circle' : 'fa-times-circle' }} fa-3x"></i>
                                    </div>
                                    <h5>High School Certificate</h5>
                                    <p class="status">{{ $student->has_high_school_certificate ? 'Uploaded' : 'Not Uploaded' }}</p>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="document-card {{ $student->has_high_school_transcript ? 'success' : 'danger' }}">
                                    <div class="document-icon">
                                        <i class="fas {{ $student->has_high_school_transcript ? 'fa-check-circle' : 'fa-times-circle' }} fa-3x"></i>
                                    </div>
                                    <h5>High School Transcript</h5>
                                    <p class="status">{{ $student->has_high_school_transcript ? 'Uploaded' : 'Not Uploaded' }}</p>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="document-card {{ $student->has_immunization_records ? 'success' : 'danger' }}">
                                    <div class="document-icon">
                                        <i class="fas {{ $student->has_immunization_records ? 'fa-check-circle' : 'fa-times-circle' }} fa-3x"></i>
                                    </div>
                                    <h5>Immunization Records</h5>
                                    <p class="status">{{ $student->has_immunization_records ? 'Uploaded' : 'Not Uploaded' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info mt-4">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note:</strong> To upload or update documents, please use the Edit Student function.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Information -->
            <div class="card-footer bg-light">
                <div class="row text-muted small">
                    <div class="col-md-4">
                        <i class="fas fa-calendar-plus me-1"></i>
                        <strong>Created:</strong> {{ $student->created_at->format('F d, Y h:i A') }}
                    </div>
                    <div class="col-md-4">
                        <i class="fas fa-calendar-check me-1"></i>
                        <strong>Last Updated:</strong> {{ $student->updated_at->format('F d, Y h:i A') }}
                    </div>
                    @if($student->last_activity_at)
                    <div class="col-md-4">
                        <i class="fas fa-clock me-1"></i>
                        <strong>Last Activity:</strong> {{ $student->last_activity_at->format('F d, Y h:i A') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Styles -->
    <style>
        .student-status-bar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .status-item {
            text-align: center;
        }

        .status-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            opacity: 0.9;
            display: block;
            margin-bottom: 0.5rem;
        }

        .status-value {
            font-size: 1.1rem;
        }

        .nav-tabs-custom {
            border-bottom: none;
        }

        .nav-tabs-custom .nav-link {
            color: #6c757d;
            border: none;
            padding: 1rem 1.5rem;
            font-weight: 500;
            position: relative;
        }

        .nav-tabs-custom .nav-link:hover {
            color: #2563eb;
            background: #f8f9fa;
        }

        .nav-tabs-custom .nav-link.active {
            color: #2563eb;
            background: white;
            border-bottom: 3px solid #2563eb;
        }

        .info-card {
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            height: 100%;
            transition: box-shadow 0.2s;
        }

        .info-card:hover {
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .info-card-header {
            padding: 1rem;
            background: #f8f9fa;
            border-bottom: 1px solid #e5e7eb;
            border-radius: 0.5rem 0.5rem 0 0;
        }

        .info-card-header h5 {
            margin: 0;
            font-size: 1rem;
            font-weight: 600;
        }

        .info-card-body {
            padding: 1rem;
        }

        .info-list {
            margin: 0;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #f1f3f4;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-item dt {
            font-weight: 500;
            color: #6c757d;
            font-size: 0.875rem;
        }

        .info-item dd {
            margin: 0;
            font-weight: 500;
            color: #1f2937;
        }

        .detail-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 0.5rem;
            height: 100%;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #1f2937;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 0.5rem;
        }

        .detail-list {
            margin: 0;
        }

        .detail-item {
            display: flex;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-item dt {
            width: 40%;
            font-weight: 500;
            color: #6c757d;
            font-size: 0.9rem;
        }

        .detail-item dd {
            width: 60%;
            margin: 0;
            color: #1f2937;
        }

        .performance-dashboard {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 0.5rem;
        }

        .performance-card {
            background: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .international-info-card,
        .contact-card {
            background: #e0e7ff;
            padding: 1.5rem;
            border-radius: 0.5rem;
            border: 1px solid #c7d2fe;
        }

        .guardian-card {
            background: #f0fdf4;
            border-color: #bbf7d0;
        }

        .emergency-card {
            background: #fef2f2;
            border-color: #fecaca;
        }

        .document-card {
            background: white;
            padding: 2rem;
            border-radius: 0.5rem;
            text-align: center;
            border: 2px solid;
            transition: transform 0.2s;
        }

        .document-card:hover {
            transform: translateY(-2px);
        }

        .document-card.success {
            border-color: #10b981;
        }

        .document-card.success .document-icon {
            color: #10b981;
        }

        .document-card.danger {
            border-color: #ef4444;
        }

        .document-card.danger .document-icon {
            color: #ef4444;
        }

        .document-card h5 {
            margin: 1rem 0 0.5rem 0;
            font-size: 1rem;
            font-weight: 600;
        }

        .document-card .status {
            margin: 0;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .alert-important {
            border-left: 4px solid #dc3545;
        }

        .page-actions-group {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .nav-tabs-custom .nav-link {
                padding: 0.75rem 1rem;
                font-size: 0.875rem;
            }

            .detail-item {
                flex-direction: column;
            }

            .detail-item dt,
            .detail-item dd {
                width: 100%;
            }

            .detail-item dt {
                margin-bottom: 0.25rem;
            }

            .status-item {
                margin-bottom: 1rem;
            }
        }
    </style>
@endsection