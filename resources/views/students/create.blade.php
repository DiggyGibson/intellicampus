@extends('layouts.app')

@section('title', 'Add New Student')

@section('breadcrumb')
    <a href="{{ route('students.index') }}">Students</a>
    <i class="fas fa-chevron-right"></i>
    <span>Add New Student</span>
@endsection

@section('content')
    <div class="container-fluid px-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title mb-0">
                    <i class="fas fa-user-plus me-2"></i>
                    Add New Student
                </h3>
            </div>
            <div class="card-body">
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-times-circle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Please correct the following errors:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('students.store') }}" enctype="multipart/form-data" id="createStudentForm">
                    @csrf
                    
                    <!-- Progress Indicator -->
                    <div class="progress mb-4" style="height: 3px;">
                        <div class="progress-bar bg-primary" id="formProgress" role="progressbar" style="width: 20%"></div>
                    </div>
                    
                    <!-- Tab Navigation -->
                    <ul class="nav nav-tabs nav-tabs-custom mb-4" id="studentTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="personal-tab-btn" data-bs-toggle="tab" 
                                    data-bs-target="#personal-tab" type="button" role="tab" data-tab-index="0">
                                <i class="fas fa-user me-2"></i>
                                <span class="d-none d-sm-inline">Personal Information</span>
                                <span class="d-sm-none">Personal</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="academic-tab-btn" data-bs-toggle="tab" 
                                    data-bs-target="#academic-tab" type="button" role="tab" data-tab-index="1">
                                <i class="fas fa-graduation-cap me-2"></i>
                                <span class="d-none d-sm-inline">Academic Information</span>
                                <span class="d-sm-none">Academic</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="contact-tab-btn" data-bs-toggle="tab" 
                                    data-bs-target="#contact-tab" type="button" role="tab" data-tab-index="2">
                                <i class="fas fa-address-book me-2"></i>
                                <span class="d-none d-sm-inline">Contact & Emergency</span>
                                <span class="d-sm-none">Contact</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="medical-tab-btn" data-bs-toggle="tab" 
                                    data-bs-target="#medical-tab" type="button" role="tab" data-tab-index="3">
                                <i class="fas fa-heartbeat me-2"></i>
                                <span class="d-none d-sm-inline">Medical & Insurance</span>
                                <span class="d-sm-none">Medical</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="documents-tab-btn" data-bs-toggle="tab" 
                                    data-bs-target="#documents-tab" type="button" role="tab" data-tab-index="4">
                                <i class="fas fa-folder-open me-2"></i>
                                Documents
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="studentTabContent">
                        
                        <!-- Personal Information Tab -->
                        <div class="tab-pane fade show active" id="personal-tab" role="tabpanel">
                            <h4 class="section-title mb-4">
                                <i class="fas fa-user-circle text-primary me-2"></i>
                                Personal Information
                            </h4>
                            
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">
                                        First Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="first_name" value="{{ old('first_name') }}" 
                                           class="form-control @error('first_name') is-invalid @enderror" required>
                                    @error('first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Middle Name</label>
                                    <input type="text" name="middle_name" value="{{ old('middle_name') }}" 
                                           class="form-control">
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">
                                        Last Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="last_name" value="{{ old('last_name') }}" 
                                           class="form-control @error('last_name') is-invalid @enderror" required>
                                    @error('last_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Preferred Name</label>
                                    <input type="text" name="preferred_name" value="{{ old('preferred_name') }}" 
                                           class="form-control"
                                           placeholder="Name to be called by">
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">
                                        Date of Birth <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" 
                                           class="form-control @error('date_of_birth') is-invalid @enderror" required>
                                    @error('date_of_birth')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Place of Birth</label>
                                    <input type="text" name="place_of_birth" value="{{ old('place_of_birth') }}"
                                           class="form-control" placeholder="City, Country">
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label">
                                        Gender <span class="text-danger">*</span>
                                    </label>
                                    <select name="gender" class="form-select @error('gender') is-invalid @enderror" required>
                                        <option value="">Select Gender</option>
                                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('gender')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label">Marital Status</label>
                                    <select name="marital_status" class="form-select">
                                        <option value="">Select Status</option>
                                        <option value="single" {{ old('marital_status') == 'single' ? 'selected' : '' }}>Single</option>
                                        <option value="married" {{ old('marital_status') == 'married' ? 'selected' : '' }}>Married</option>
                                        <option value="divorced" {{ old('marital_status') == 'divorced' ? 'selected' : '' }}>Divorced</option>
                                        <option value="widowed" {{ old('marital_status') == 'widowed' ? 'selected' : '' }}>Widowed</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label">Nationality</label>
                                    <input type="text" name="nationality" value="{{ old('nationality') }}"
                                           class="form-control" placeholder="e.g., American">
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label">National ID Number</label>
                                    <input type="text" name="national_id_number" value="{{ old('national_id_number') }}"
                                           class="form-control">
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label">Religion</label>
                                    <input type="text" name="religion" value="{{ old('religion') }}"
                                           class="form-control">
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label">Ethnicity</label>
                                    <input type="text" name="ethnicity" value="{{ old('ethnicity') }}"
                                           class="form-control">
                                </div>
                            </div>
                            
                            <!-- International Student Section -->
                            <div class="international-section mt-4">
                                <h5 class="section-subtitle">
                                    <i class="fas fa-globe text-info me-2"></i>
                                    International Student Information
                                </h5>
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <div class="form-check mt-4">
                                            <input type="checkbox" name="is_international" value="1" 
                                                   class="form-check-input" id="isInternational"
                                                   {{ old('is_international') ? 'checked' : '' }}
                                                   onchange="toggleInternationalFields(this)">
                                            <label class="form-check-label" for="isInternational">
                                                Is International Student
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label class="form-label">Passport Number</label>
                                        <input type="text" name="passport_number" value="{{ old('passport_number') }}"
                                               class="form-control international-field" disabled>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label class="form-label">Visa Status</label>
                                        <select name="visa_status" class="form-select international-field" disabled>
                                            <option value="">Select Visa Type</option>
                                            <option value="F-1" {{ old('visa_status') == 'F-1' ? 'selected' : '' }}>F-1 (Student)</option>
                                            <option value="J-1" {{ old('visa_status') == 'J-1' ? 'selected' : '' }}>J-1 (Exchange)</option>
                                            <option value="M-1" {{ old('visa_status') == 'M-1' ? 'selected' : '' }}>M-1 (Vocational)</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label class="form-label">Visa Expiry Date</label>
                                        <input type="date" name="visa_expiry" value="{{ old('visa_expiry') }}"
                                               class="form-control international-field" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Academic Information Tab -->
                        <div class="tab-pane fade" id="academic-tab" role="tabpanel">
                            <h4 class="section-title mb-4">
                                <i class="fas fa-university text-primary me-2"></i>
                                Academic Information
                            </h4>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">
                                        Department <span class="text-danger">*</span>
                                    </label>
                                    <select name="department" class="form-select @error('department') is-invalid @enderror" required>
                                        <option value="">Select Department</option>
                                        <option value="Computer Science">Computer Science</option>
                                        <option value="Business">Business</option>
                                        <option value="Engineering">Engineering</option>
                                        <option value="Medical Sciences">Medical Sciences</option>
                                        <option value="Law">Law</option>
                                        <option value="Liberal Arts">Liberal Arts</option>
                                        <option value="Education">Education</option>
                                    </select>
                                    @error('department')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">
                                        Program <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="program_name" value="{{ old('program_name') }}" 
                                           class="form-control @error('program_name') is-invalid @enderror" required
                                           placeholder="e.g., Computer Science, Business Administration">
                                    @error('program_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Major</label>
                                    <input type="text" name="major" value="{{ old('major') }}"
                                           class="form-control" placeholder="e.g., Software Engineering, Finance">
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Minor</label>
                                    <input type="text" name="minor" value="{{ old('minor') }}"
                                           class="form-control" placeholder="e.g., Mathematics, Psychology">
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">
                                        Academic Level <span class="text-danger">*</span>
                                    </label>
                                    <select name="academic_level" class="form-select @error('academic_level') is-invalid @enderror" required>
                                        <option value="">Select Level</option>
                                        <option value="freshman" {{ old('academic_level') == 'freshman' ? 'selected' : '' }}>Freshman</option>
                                        <option value="sophomore" {{ old('academic_level') == 'sophomore' ? 'selected' : '' }}>Sophomore</option>
                                        <option value="junior" {{ old('academic_level') == 'junior' ? 'selected' : '' }}>Junior</option>
                                        <option value="senior" {{ old('academic_level') == 'senior' ? 'selected' : '' }}>Senior</option>
                                        <option value="graduate" {{ old('academic_level') == 'graduate' ? 'selected' : '' }}>Graduate</option>
                                    </select>
                                    @error('academic_level')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">
                                        Enrollment Status <span class="text-danger">*</span>
                                    </label>
                                    <select name="enrollment_status" class="form-select @error('enrollment_status') is-invalid @enderror" required>
                                        <option value="">Select Status</option>
                                        <option value="active" {{ old('enrollment_status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('enrollment_status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        <option value="suspended" {{ old('enrollment_status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                        <option value="graduated" {{ old('enrollment_status') == 'graduated' ? 'selected' : '' }}>Graduated</option>
                                        <option value="withdrawn" {{ old('enrollment_status') == 'withdrawn' ? 'selected' : '' }}>Withdrawn</option>
                                    </select>
                                    @error('enrollment_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">
                                        Admission Date <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="admission_date" value="{{ old('admission_date') }}" 
                                           class="form-control @error('admission_date') is-invalid @enderror" required>
                                    @error('admission_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Expected Graduation Year</label>
                                    <input type="number" name="expected_graduation_year" value="{{ old('expected_graduation_year') }}"
                                           class="form-control" min="2024" max="2030">
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Advisor Name</label>
                                    <input type="text" name="advisor_name" value="{{ old('advisor_name') }}"
                                           class="form-control" placeholder="Dr. John Smith">
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Credits Required</label>
                                    <input type="number" name="credits_required" value="{{ old('credits_required', 120) }}"
                                           class="form-control" min="0">
                                </div>
                            </div>
                            
                            <!-- Previous Education Section -->
                            <div class="previous-education-section mt-4">
                                <h5 class="section-subtitle">
                                    <i class="fas fa-school text-info me-2"></i>
                                    Previous Education
                                </h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">High School Name</label>
                                        <input type="text" name="high_school_name" value="{{ old('high_school_name') }}"
                                               class="form-control">
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label class="form-label">Graduation Year</label>
                                        <input type="number" name="high_school_graduation_year" value="{{ old('high_school_graduation_year') }}"
                                               class="form-control" min="2015" max="2024">
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label class="form-label">High School GPA</label>
                                        <input type="number" name="high_school_gpa" value="{{ old('high_school_gpa') }}"
                                               class="form-control" step="0.01" min="0" max="4">
                                    </div>
                                    
                                    <div class="col-md-12">
                                        <label class="form-label">Previous University (if transfer)</label>
                                        <input type="text" name="previous_university" value="{{ old('previous_university') }}"
                                               class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contact & Emergency Tab -->
                        <div class="tab-pane fade" id="contact-tab" role="tabpanel">
                            <h4 class="section-title mb-4">
                                <i class="fas fa-phone-alt text-primary me-2"></i>
                                Contact Information
                            </h4>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">
                                        Primary Email <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" name="email" value="{{ old('email') }}" 
                                           class="form-control @error('email') is-invalid @enderror" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Secondary Email</label>
                                    <input type="email" name="secondary_email" value="{{ old('secondary_email') }}"
                                           class="form-control">
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Mobile Phone</label>
                                    <input type="text" name="phone" value="{{ old('phone') }}"
                                           class="form-control" placeholder="+1 234 567 8900">
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Home Phone</label>
                                    <input type="text" name="home_phone" value="{{ old('home_phone') }}"
                                           class="form-control">
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label">Current Address</label>
                                    <textarea name="address" rows="2" class="form-control">{{ old('address') }}</textarea>
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label">Permanent Address</label>
                                    <textarea name="permanent_address" rows="2" class="form-control">{{ old('permanent_address') }}</textarea>
                                </div>
                            </div>
                            
                            <!-- Guardian Information -->
                            <div class="guardian-section mt-4">
                                <h5 class="section-subtitle">
                                    <i class="fas fa-user-shield text-success me-2"></i>
                                    Guardian Information
                                </h5>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Guardian Name</label>
                                        <input type="text" name="guardian_name" value="{{ old('guardian_name') }}"
                                               class="form-control">
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label class="form-label">Guardian Phone</label>
                                        <input type="text" name="guardian_phone" value="{{ old('guardian_phone') }}"
                                               class="form-control">
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label class="form-label">Guardian Email</label>
                                        <input type="email" name="guardian_email" value="{{ old('guardian_email') }}"
                                               class="form-control">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Emergency Contact -->
                            <div class="emergency-section mt-4">
                                <h5 class="section-subtitle text-danger">
                                    <i class="fas fa-ambulance me-2"></i>
                                    Emergency Contact
                                </h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Emergency Contact Name</label>
                                        <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}"
                                               class="form-control">
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label">Emergency Contact Phone</label>
                                        <input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}"
                                               class="form-control">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Next of Kin -->
                            <div class="next-of-kin-section mt-4">
                                <h5 class="section-subtitle">
                                    <i class="fas fa-users text-info me-2"></i>
                                    Next of Kin
                                </h5>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Name</label>
                                        <input type="text" name="next_of_kin_name" value="{{ old('next_of_kin_name') }}"
                                               class="form-control">
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label class="form-label">Relationship</label>
                                        <select name="next_of_kin_relationship" class="form-select">
                                            <option value="">Select Relationship</option>
                                            <option value="Parent">Parent</option>
                                            <option value="Mother">Mother</option>
                                            <option value="Father">Father</option>
                                            <option value="Sibling">Sibling</option>
                                            <option value="Spouse">Spouse</option>
                                            <option value="Guardian">Guardian</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label class="form-label">Phone</label>
                                        <input type="text" name="next_of_kin_phone" value="{{ old('next_of_kin_phone') }}"
                                               class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Medical & Insurance Tab -->
                        <div class="tab-pane fade" id="medical-tab" role="tabpanel">
                            <h4 class="section-title mb-4">
                                <i class="fas fa-heartbeat text-primary me-2"></i>
                                Medical Information
                            </h4>
                            
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Blood Group</label>
                                    <select name="blood_group" class="form-select">
                                        <option value="">Select Blood Group</option>
                                        <option value="A+">A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="O+">O+</option>
                                        <option value="O-">O-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-8">
                                    <label class="form-label">Medical Conditions / Allergies</label>
                                    <textarea name="medical_conditions" rows="3" class="form-control"
                                              placeholder="List any medical conditions, allergies, or medications">{{ old('medical_conditions') }}</textarea>
                                </div>
                            </div>
                            
                            <!-- Insurance Information -->
                            <div class="insurance-section mt-4">
                                <h5 class="section-subtitle">
                                    <i class="fas fa-shield-alt text-info me-2"></i>
                                    Insurance Information
                                </h5>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Insurance Provider</label>
                                        <input type="text" name="insurance_provider" value="{{ old('insurance_provider') }}"
                                               class="form-control">
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label class="form-label">Policy Number</label>
                                        <input type="text" name="insurance_policy_number" value="{{ old('insurance_policy_number') }}"
                                               class="form-control">
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label class="form-label">Expiry Date</label>
                                        <input type="date" name="insurance_expiry" value="{{ old('insurance_expiry') }}"
                                               class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Documents Tab -->
                        <div class="tab-pane fade" id="documents-tab" role="tabpanel">
                            <h4 class="section-title mb-4">
                                <i class="fas fa-file-alt text-primary me-2"></i>
                                Document Upload
                            </h4>
                            
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="upload-card">
                                        <div class="upload-icon">
                                            <i class="fas fa-camera fa-2x text-muted"></i>
                                        </div>
                                        <label class="form-label">Profile Photo</label>
                                        <input type="file" name="profile_photo" accept="image/*" class="form-control">
                                        <small class="text-muted">Accepted: JPG, PNG, GIF (Max 2MB)</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="upload-card">
                                        <div class="upload-icon">
                                            <i class="fas fa-id-card fa-2x text-muted"></i>
                                        </div>
                                        <label class="form-label">National ID Copy</label>
                                        <input type="file" name="national_id_copy" accept=".pdf,.jpg,.png" class="form-control">
                                        <small class="text-muted">Accepted: PDF, JPG, PNG (Max 5MB)</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="upload-card">
                                        <div class="upload-icon">
                                            <i class="fas fa-certificate fa-2x text-muted"></i>
                                        </div>
                                        <label class="form-label">High School Certificate</label>
                                        <input type="file" name="high_school_certificate" accept=".pdf" class="form-control">
                                        <small class="text-muted">Accepted: PDF (Max 5MB)</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="upload-card">
                                        <div class="upload-icon">
                                            <i class="fas fa-file-alt fa-2x text-muted"></i>
                                        </div>
                                        <label class="form-label">High School Transcript</label>
                                        <input type="file" name="high_school_transcript" accept=".pdf" class="form-control">
                                        <small class="text-muted">Accepted: PDF (Max 5MB)</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="upload-card">
                                        <div class="upload-icon">
                                            <i class="fas fa-syringe fa-2x text-muted"></i>
                                        </div>
                                        <label class="form-label">Immunization Records</label>
                                        <input type="file" name="immunization_records" accept=".pdf" class="form-control">
                                        <small class="text-muted">Accepted: PDF (Max 5MB)</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="upload-card">
                                        <div class="upload-icon">
                                            <i class="fas fa-folder-plus fa-2x text-muted"></i>
                                        </div>
                                        <label class="form-label">Other Documents</label>
                                        <input type="file" name="other_documents[]" multiple accept=".pdf" class="form-control">
                                        <small class="text-muted">You can select multiple PDF files</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info mt-4">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Note:</strong> Documents can also be uploaded later from the student's profile page. 
                                Only profile photo will be displayed publicly. All other documents are kept confidential.
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions mt-5">
                        <div class="row">
                            <div class="col-6">
                                <a href="{{ route('students.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                            </div>
                            <div class="col-6 text-end">
                                <button type="button" class="btn btn-outline-primary me-2" id="prevBtn" style="display: none;">
                                    <i class="fas fa-arrow-left me-2"></i>Previous
                                </button>
                                <button type="button" class="btn btn-primary" id="nextBtn">
                                    Next<i class="fas fa-arrow-right ms-2"></i>
                                </button>
                                <button type="submit" class="btn btn-success" id="submitBtn" style="display: none;">
                                    <i class="fas fa-check me-2"></i>Create Student
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Custom Styles -->
    <style>
        .nav-tabs-custom .nav-link {
            color: #6c757d;
            border: none;
            padding: 0.75rem 1.25rem;
            font-weight: 500;
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

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 0.5rem;
        }

        .section-subtitle {
            font-size: 1.1rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 1rem;
            padding: 0.5rem;
            background: #f8f9fa;
            border-radius: 0.375rem;
        }

        .international-section,
        .previous-education-section,
        .guardian-section,
        .emergency-section,
        .next-of-kin-section,
        .insurance-section {
            padding: 1.25rem;
            background: #f8f9fa;
            border-radius: 0.5rem;
        }

        .emergency-section {
            background: #fef2f2;
        }

        .upload-card {
            padding: 1.5rem;
            border: 2px dashed #dee2e6;
            border-radius: 0.5rem;
            text-align: center;
            transition: border-color 0.2s;
        }

        .upload-card:hover {
            border-color: #2563eb;
        }

        .upload-icon {
            margin-bottom: 1rem;
        }

        .form-actions {
            padding-top: 1.5rem;
            border-top: 2px solid #e5e7eb;
        }

        @media (max-width: 576px) {
            .nav-tabs-custom .nav-link {
                padding: 0.5rem 0.75rem;
                font-size: 0.875rem;
            }
        }
    </style>

    <!-- JavaScript -->
    <script>
        let currentTab = 0;
        const tabs = ['personal', 'academic', 'contact', 'medical', 'documents'];
        
        // Tab navigation with Bootstrap 5
        document.addEventListener('DOMContentLoaded', function() {
            // Update progress bar on tab change
            const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');
            tabButtons.forEach(button => {
                button.addEventListener('shown.bs.tab', function (e) {
                    currentTab = parseInt(e.target.dataset.tabIndex);
                    updateProgressBar();
                    updateNavigationButtons();
                });
            });
            
            // Initialize
            updateProgressBar();
            updateNavigationButtons();
        });
        
        function updateProgressBar() {
            const progress = ((currentTab + 1) / tabs.length) * 100;
            document.getElementById('formProgress').style.width = progress + '%';
        }
        
        function updateNavigationButtons() {
            document.getElementById('prevBtn').style.display = currentTab === 0 ? 'none' : 'inline-block';
            
            if (currentTab === tabs.length - 1) {
                document.getElementById('nextBtn').style.display = 'none';
                document.getElementById('submitBtn').style.display = 'inline-block';
            } else {
                document.getElementById('nextBtn').style.display = 'inline-block';
                document.getElementById('submitBtn').style.display = 'none';
            }
        }
        
        document.getElementById('nextBtn').addEventListener('click', function() {
            if (currentTab < tabs.length - 1) {
                currentTab++;
                const nextTab = document.querySelector(`#${tabs[currentTab]}-tab-btn`);
                const bsTab = new bootstrap.Tab(nextTab);
                bsTab.show();
            }
        });
        
        document.getElementById('prevBtn').addEventListener('click', function() {
            if (currentTab > 0) {
                currentTab--;
                const prevTab = document.querySelector(`#${tabs[currentTab]}-tab-btn`);
                const bsTab = new bootstrap.Tab(prevTab);
                bsTab.show();
            }
        });
        
        function toggleInternationalFields(checkbox) {
            const fields = document.querySelectorAll('.international-field');
            fields.forEach(field => {
                field.disabled = !checkbox.checked;
                if (!checkbox.checked) {
                    field.value = '';
                }
            });
        }
        
        // Auto-save draft (optional enhancement)
        let autoSaveTimer;
        document.querySelectorAll('input, select, textarea').forEach(element => {
            element.addEventListener('change', function() {
                clearTimeout(autoSaveTimer);
                autoSaveTimer = setTimeout(function() {
                    // Implement auto-save logic here if needed
                    console.log('Auto-saving draft...');
                }, 2000);
            });
        });
    </script>
@endsection