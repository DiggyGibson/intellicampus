@extends('layouts.app')

@section('title', 'Edit Student: ' . $student->display_name)

@section('breadcrumb')
    <a href="{{ route('students.index') }}">Students</a>
    <i class="fas fa-chevron-right"></i>
    <a href="{{ route('students.show', $student) }}">{{ $student->display_name }}</a>
    <i class="fas fa-chevron-right"></i>
    <span>Edit</span>
@endsection

@section('content')
    <div class="container-fluid px-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title mb-0">
                    <i class="fas fa-user-edit me-2"></i>
                    Edit Student: {{ $student->first_name }} {{ $student->last_name }}
                </h3>
                <div class="student-id-badge">
                    <span class="badge bg-white text-primary">
                        <i class="fas fa-id-badge me-1"></i>
                        Student ID: {{ $student->student_id }}
                    </span>
                </div>
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

                <!-- Student Status Info -->
                <div class="alert alert-info">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Current Status:</strong>
                            <span class="badge bg-{{ $student->enrollment_status == 'active' ? 'success' : 'secondary' }} ms-2">
                                {{ ucfirst($student->enrollment_status) }}
                            </span>
                        </div>
                        <div class="col-md-3">
                            <strong>Created:</strong> {{ $student->created_at->format('M d, Y') }}
                        </div>
                        <div class="col-md-3">
                            <strong>Last Updated:</strong> {{ $student->updated_at->format('M d, Y') }}
                        </div>
                        <div class="col-md-3">
                            <strong>Progress:</strong> {{ $student->progress_percentage }}% Complete
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('students.update', $student) }}" enctype="multipart/form-data" id="editStudentForm">
                    @csrf
                    @method('PUT')
                    
                    <!-- Tab Navigation -->
                    <ul class="nav nav-tabs nav-tabs-custom mb-4" id="studentTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="personal-tab-btn" data-bs-toggle="tab" 
                                    data-bs-target="#personal-tab" type="button" role="tab">
                                <i class="fas fa-user me-2"></i>
                                Personal Information
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="academic-tab-btn" data-bs-toggle="tab" 
                                    data-bs-target="#academic-tab" type="button" role="tab">
                                <i class="fas fa-graduation-cap me-2"></i>
                                Academic Information
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="contact-tab-btn" data-bs-toggle="tab" 
                                    data-bs-target="#contact-tab" type="button" role="tab">
                                <i class="fas fa-address-book me-2"></i>
                                Contact & Emergency
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="medical-tab-btn" data-bs-toggle="tab" 
                                    data-bs-target="#medical-tab" type="button" role="tab">
                                <i class="fas fa-heartbeat me-2"></i>
                                Medical & Insurance
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="documents-tab-btn" data-bs-toggle="tab" 
                                    data-bs-target="#documents-tab" type="button" role="tab">
                                <i class="fas fa-folder-open me-2"></i>
                                Documents
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="status-tab-btn" data-bs-toggle="tab" 
                                    data-bs-target="#status-tab" type="button" role="tab">
                                <i class="fas fa-chart-line me-2"></i>
                                Status & Progress
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="studentTabContent">
                        
                        <!-- Personal Information Tab -->
                        <div class="tab-pane fade show active" id="personal-tab" role="tabpanel">
                            <h4 class="section-title mb-4">Personal Information</h4>
                            
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                                    <input type="text" name="first_name" value="{{ old('first_name', $student->first_name) }}" 
                                           class="form-control @error('first_name') is-invalid @enderror" required>
                                    @error('first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Middle Name</label>
                                    <input type="text" name="middle_name" value="{{ old('middle_name', $student->middle_name) }}"
                                           class="form-control">
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" name="last_name" value="{{ old('last_name', $student->last_name) }}" 
                                           class="form-control @error('last_name') is-invalid @enderror" required>
                                    @error('last_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Preferred Name</label>
                                    <input type="text" name="preferred_name" value="{{ old('preferred_name', $student->preferred_name) }}"
                                           class="form-control">
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                    <input type="date" name="date_of_birth" 
                                           value="{{ old('date_of_birth', $student->date_of_birth ? $student->date_of_birth->format('Y-m-d') : '') }}" 
                                           class="form-control @error('date_of_birth') is-invalid @enderror" required>
                                    @error('date_of_birth')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Place of Birth</label>
                                    <input type="text" name="place_of_birth" value="{{ old('place_of_birth', $student->place_of_birth) }}"
                                           class="form-control" placeholder="City, Country">
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label">Gender <span class="text-danger">*</span></label>
                                    <select name="gender" class="form-select @error('gender') is-invalid @enderror" required>
                                        <option value="">Select Gender</option>
                                        <option value="male" {{ $student->gender == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ $student->gender == 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="other" {{ $student->gender == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('gender')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label">Marital Status</label>
                                    <select name="marital_status" class="form-select">
                                        <option value="">Select Status</option>
                                        <option value="single" {{ $student->marital_status == 'single' ? 'selected' : '' }}>Single</option>
                                        <option value="married" {{ $student->marital_status == 'married' ? 'selected' : '' }}>Married</option>
                                        <option value="divorced" {{ $student->marital_status == 'divorced' ? 'selected' : '' }}>Divorced</option>
                                        <option value="widowed" {{ $student->marital_status == 'widowed' ? 'selected' : '' }}>Widowed</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label">Nationality</label>
                                    <input type="text" name="nationality" value="{{ old('nationality', $student->nationality) }}"
                                           class="form-control">
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label">National ID Number</label>
                                    <input type="text" name="national_id_number" value="{{ old('national_id_number', $student->national_id_number) }}"
                                           class="form-control">
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label">Religion</label>
                                    <input type="text" name="religion" value="{{ old('religion', $student->religion) }}"
                                           class="form-control">
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label">Ethnicity</label>
                                    <input type="text" name="ethnicity" value="{{ old('ethnicity', $student->ethnicity) }}"
                                           class="form-control">
                                </div>
                            </div>
                            
                            <!-- International Student Section -->
                            <div class="international-section mt-4">
                                <h5 class="section-subtitle">International Student Information</h5>
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <div class="form-check mt-4">
                                            <input type="checkbox" name="is_international" value="1" 
                                                   class="form-check-input" id="isInternational"
                                                   {{ $student->is_international ? 'checked' : '' }}
                                                   onchange="toggleInternationalFields(this)">
                                            <label class="form-check-label" for="isInternational">
                                                Is International Student
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label class="form-label">Passport Number</label>
                                        <input type="text" name="passport_number" value="{{ old('passport_number', $student->passport_number) }}"
                                               class="form-control international-field" {{ !$student->is_international ? 'disabled' : '' }}>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label class="form-label">Visa Status</label>
                                        <select name="visa_status" class="form-select international-field" {{ !$student->is_international ? 'disabled' : '' }}>
                                            <option value="">Select Visa Type</option>
                                            <option value="F-1" {{ $student->visa_status == 'F-1' ? 'selected' : '' }}>F-1 (Student)</option>
                                            <option value="J-1" {{ $student->visa_status == 'J-1' ? 'selected' : '' }}>J-1 (Exchange)</option>
                                            <option value="M-1" {{ $student->visa_status == 'M-1' ? 'selected' : '' }}>M-1 (Vocational)</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label class="form-label">Visa Expiry Date</label>
                                        <input type="date" name="visa_expiry" 
                                               value="{{ old('visa_expiry', $student->visa_expiry ? $student->visa_expiry->format('Y-m-d') : '') }}"
                                               class="form-control international-field" {{ !$student->is_international ? 'disabled' : '' }}>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Academic Information Tab -->
                        <div class="tab-pane fade" id="academic-tab" role="tabpanel">
                            <h4 class="section-title mb-4">Academic Information</h4>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Department <span class="text-danger">*</span></label>
                                    <select name="department" class="form-select @error('department') is-invalid @enderror" required>
                                        <option value="">Select Department</option>
                                        <option value="Computer Science" {{ $student->department == 'Computer Science' ? 'selected' : '' }}>Computer Science</option>
                                        <option value="Business" {{ $student->department == 'Business' ? 'selected' : '' }}>Business</option>
                                        <option value="Engineering" {{ $student->department == 'Engineering' ? 'selected' : '' }}>Engineering</option>
                                        <option value="Medical Sciences" {{ $student->department == 'Medical Sciences' ? 'selected' : '' }}>Medical Sciences</option>
                                        <option value="Law" {{ $student->department == 'Law' ? 'selected' : '' }}>Law</option>
                                        <option value="Liberal Arts" {{ $student->department == 'Liberal Arts' ? 'selected' : '' }}>Liberal Arts</option>
                                        <option value="Education" {{ $student->department == 'Education' ? 'selected' : '' }}>Education</option>
                                    </select>
                                    @error('department')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Program <span class="text-danger">*</span></label>
                                    <input type="text" name="program_name" value="{{ old('program_name', $student->program_name) }}" 
                                           class="form-control @error('program_name') is-invalid @enderror" required>
                                    @error('program_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Major</label>
                                    <input type="text" name="major" value="{{ old('major', $student->major) }}"
                                           class="form-control">
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Minor</label>
                                    <input type="text" name="minor" value="{{ old('minor', $student->minor) }}"
                                           class="form-control">
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Academic Level <span class="text-danger">*</span></label>
                                    <select name="academic_level" class="form-select @error('academic_level') is-invalid @enderror" required>
                                        <option value="">Select Level</option>
                                        <option value="freshman" {{ $student->academic_level == 'freshman' ? 'selected' : '' }}>Freshman</option>
                                        <option value="sophomore" {{ $student->academic_level == 'sophomore' ? 'selected' : '' }}>Sophomore</option>
                                        <option value="junior" {{ $student->academic_level == 'junior' ? 'selected' : '' }}>Junior</option>
                                        <option value="senior" {{ $student->academic_level == 'senior' ? 'selected' : '' }}>Senior</option>
                                        <option value="graduate" {{ $student->academic_level == 'graduate' ? 'selected' : '' }}>Graduate</option>
                                    </select>
                                    @error('academic_level')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Admission Date <span class="text-danger">*</span></label>
                                    <input type="date" name="admission_date" 
                                           value="{{ old('admission_date', $student->admission_date ? $student->admission_date->format('Y-m-d') : '') }}" 
                                           class="form-control @error('admission_date') is-invalid @enderror" required>
                                    @error('admission_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Expected Graduation Year</label>
                                    <input type="number" name="expected_graduation_year" 
                                           value="{{ old('expected_graduation_year', $student->expected_graduation_year) }}"
                                           class="form-control" min="2024" max="2030">
                                </div>
                                
                                <div class="col-md-12">
                                    <label class="form-label">Advisor Name</label>
                                    <input type="text" name="advisor_name" value="{{ old('advisor_name', $student->advisor_name) }}"
                                           class="form-control">
                                </div>
                            </div>
                            
                            <!-- Previous Education Section -->
                            <div class="previous-education-section mt-4">
                                <h5 class="section-subtitle">Previous Education</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">High School Name</label>
                                        <input type="text" name="high_school_name" value="{{ old('high_school_name', $student->high_school_name) }}"
                                               class="form-control">
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label class="form-label">Graduation Year</label>
                                        <input type="number" name="high_school_graduation_year" 
                                               value="{{ old('high_school_graduation_year', $student->high_school_graduation_year) }}"
                                               class="form-control" min="2015" max="2024">
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label class="form-label">High School GPA</label>
                                        <input type="number" name="high_school_gpa" value="{{ old('high_school_gpa', $student->high_school_gpa) }}"
                                               class="form-control" step="0.01" min="0" max="4">
                                    </div>
                                    
                                    <div class="col-md-12">
                                        <label class="form-label">Previous University (if transfer)</label>
                                        <input type="text" name="previous_university" value="{{ old('previous_university', $student->previous_university) }}"
                                               class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contact & Emergency Tab -->
                        <div class="tab-pane fade" id="contact-tab" role="tabpanel">
                            <h4 class="section-title mb-4">Contact Information</h4>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Primary Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" value="{{ old('email', $student->email) }}" 
                                           class="form-control @error('email') is-invalid @enderror" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Secondary Email</label>
                                    <input type="email" name="secondary_email" value="{{ old('secondary_email', $student->secondary_email) }}"
                                           class="form-control">
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Mobile Phone</label>
                                    <input type="text" name="phone" value="{{ old('phone', $student->phone) }}"
                                           class="form-control">
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Home Phone</label>
                                    <input type="text" name="home_phone" value="{{ old('home_phone', $student->home_phone) }}"
                                           class="form-control">
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label">Current Address</label>
                                    <textarea name="address" rows="2" class="form-control">{{ old('address', $student->address) }}</textarea>
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label">Permanent Address</label>
                                    <textarea name="permanent_address" rows="2" class="form-control">{{ old('permanent_address', $student->permanent_address) }}</textarea>
                                </div>
                            </div>
                            
                            <!-- Guardian Information -->
                            <div class="guardian-section mt-4">
                                <h5 class="section-subtitle">Guardian Information</h5>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Guardian Name</label>
                                        <input type="text" name="guardian_name" value="{{ old('guardian_name', $student->guardian_name) }}"
                                               class="form-control">
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label class="form-label">Guardian Phone</label>
                                        <input type="text" name="guardian_phone" value="{{ old('guardian_phone', $student->guardian_phone) }}"
                                               class="form-control">
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label class="form-label">Guardian Email</label>
                                        <input type="email" name="guardian_email" value="{{ old('guardian_email', $student->guardian_email) }}"
                                               class="form-control">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Emergency Contact -->
                            <div class="emergency-section mt-4">
                                <h5 class="section-subtitle text-danger">Emergency Contact</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Emergency Contact Name</label>
                                        <input type="text" name="emergency_contact_name" 
                                               value="{{ old('emergency_contact_name', $student->emergency_contact_name) }}"
                                               class="form-control">
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label">Emergency Contact Phone</label>
                                        <input type="text" name="emergency_contact_phone" 
                                               value="{{ old('emergency_contact_phone', $student->emergency_contact_phone) }}"
                                               class="form-control">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Next of Kin -->
                            <div class="next-of-kin-section mt-4">
                                <h5 class="section-subtitle">Next of Kin</h5>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Name</label>
                                        <input type="text" name="next_of_kin_name" value="{{ old('next_of_kin_name', $student->next_of_kin_name) }}"
                                               class="form-control">
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label class="form-label">Relationship</label>
                                        <input type="text" name="next_of_kin_relationship" 
                                               value="{{ old('next_of_kin_relationship', $student->next_of_kin_relationship) }}"
                                               class="form-control">
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label class="form-label">Phone</label>
                                        <input type="text" name="next_of_kin_phone" value="{{ old('next_of_kin_phone', $student->next_of_kin_phone) }}"
                                               class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Medical & Insurance Tab -->
                        <div class="tab-pane fade" id="medical-tab" role="tabpanel">
                            <h4 class="section-title mb-4">Medical Information</h4>
                            
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Blood Group</label>
                                    <select name="blood_group" class="form-select">
                                        <option value="">Select Blood Group</option>
                                        <option value="A+" {{ $student->blood_group == 'A+' ? 'selected' : '' }}>A+</option>
                                        <option value="A-" {{ $student->blood_group == 'A-' ? 'selected' : '' }}>A-</option>
                                        <option value="B+" {{ $student->blood_group == 'B+' ? 'selected' : '' }}>B+</option>
                                        <option value="B-" {{ $student->blood_group == 'B-' ? 'selected' : '' }}>B-</option>
                                        <option value="O+" {{ $student->blood_group == 'O+' ? 'selected' : '' }}>O+</option>
                                        <option value="O-" {{ $student->blood_group == 'O-' ? 'selected' : '' }}>O-</option>
                                        <option value="AB+" {{ $student->blood_group == 'AB+' ? 'selected' : '' }}>AB+</option>
                                        <option value="AB-" {{ $student->blood_group == 'AB-' ? 'selected' : '' }}>AB-</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-8">
                                    <label class="form-label">Medical Conditions / Allergies</label>
                                    <textarea name="medical_conditions" rows="3" class="form-control"
                                              placeholder="List any medical conditions, allergies, or medications">{{ old('medical_conditions', $student->medical_conditions) }}</textarea>
                                </div>
                            </div>
                            
                            <!-- Insurance Information -->
                            <div class="insurance-section mt-4">
                                <h5 class="section-subtitle">Insurance Information</h5>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Insurance Provider</label>
                                        <input type="text" name="insurance_provider" 
                                               value="{{ old('insurance_provider', $student->insurance_provider) }}"
                                               class="form-control">
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label class="form-label">Policy Number</label>
                                        <input type="text" name="insurance_policy_number" 
                                               value="{{ old('insurance_policy_number', $student->insurance_policy_number) }}"
                                               class="form-control">
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label class="form-label">Expiry Date</label>
                                        <input type="date" name="insurance_expiry" 
                                               value="{{ old('insurance_expiry', $student->insurance_expiry ? $student->insurance_expiry->format('Y-m-d') : '') }}"
                                               class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Documents Tab -->
                        <div class="tab-pane fade" id="documents-tab" role="tabpanel">
                            <h4 class="section-title mb-4">Documents</h4>
                            
                            <!-- Current Documents Status -->
                            <div class="document-status-section mb-4">
                                <h5 class="section-subtitle">Current Document Status</h5>
                                <div class="row g-3">
                                    @php
                                        $documents = [
                                            'profile_photo' => ['label' => 'Profile Photo', 'status' => $student->has_profile_photo],
                                            'national_id_copy' => ['label' => 'National ID Copy', 'status' => $student->has_national_id_copy],
                                            'high_school_certificate' => ['label' => 'High School Certificate', 'status' => $student->has_high_school_certificate],
                                            'high_school_transcript' => ['label' => 'High School Transcript', 'status' => $student->has_high_school_transcript],
                                            'immunization_records' => ['label' => 'Immunization Records', 'status' => $student->has_immunization_records],
                                        ];
                                    @endphp
                                    
                                    @foreach($documents as $key => $doc)
                                    <div class="col-md-6">
                                        <div class="document-status-item">
                                            <span class="status-indicator {{ $doc['status'] ? 'success' : 'danger' }}">
                                                <i class="fas fa-{{ $doc['status'] ? 'check-circle' : 'times-circle' }}"></i>
                                            </span>
                                            <span class="document-name">{{ $doc['label'] }}:</span>
                                            <span class="badge bg-{{ $doc['status'] ? 'success' : 'danger' }}">
                                                {{ $doc['status'] ? 'Uploaded' : 'Not Uploaded' }}
                                            </span>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            
                            <!-- Upload New Documents -->
                            <h5 class="section-subtitle">Upload/Replace Documents</h5>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="upload-card">
                                        <div class="upload-icon">
                                            <i class="fas fa-camera fa-2x text-muted"></i>
                                        </div>
                                        <label class="form-label">Profile Photo {{ $student->has_profile_photo ? '(Replace)' : '' }}</label>
                                        <input type="file" name="profile_photo" accept="image/*" class="form-control">
                                        <small class="text-muted">Accepted: JPG, PNG, GIF (Max 2MB)</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="upload-card">
                                        <div class="upload-icon">
                                            <i class="fas fa-id-card fa-2x text-muted"></i>
                                        </div>
                                        <label class="form-label">National ID Copy {{ $student->has_national_id_copy ? '(Replace)' : '' }}</label>
                                        <input type="file" name="national_id_copy" accept=".pdf,.jpg,.png" class="form-control">
                                        <small class="text-muted">Accepted: PDF, JPG, PNG (Max 5MB)</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="upload-card">
                                        <div class="upload-icon">
                                            <i class="fas fa-certificate fa-2x text-muted"></i>
                                        </div>
                                        <label class="form-label">High School Certificate {{ $student->has_high_school_certificate ? '(Replace)' : '' }}</label>
                                        <input type="file" name="high_school_certificate" accept=".pdf" class="form-control">
                                        <small class="text-muted">Accepted: PDF (Max 5MB)</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="upload-card">
                                        <div class="upload-icon">
                                            <i class="fas fa-file-alt fa-2x text-muted"></i>
                                        </div>
                                        <label class="form-label">High School Transcript {{ $student->has_high_school_transcript ? '(Replace)' : '' }}</label>
                                        <input type="file" name="high_school_transcript" accept=".pdf" class="form-control">
                                        <small class="text-muted">Accepted: PDF (Max 5MB)</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="upload-card">
                                        <div class="upload-icon">
                                            <i class="fas fa-syringe fa-2x text-muted"></i>
                                        </div>
                                        <label class="form-label">Immunization Records {{ $student->has_immunization_records ? '(Replace)' : '' }}</label>
                                        <input type="file" name="immunization_records" accept=".pdf" class="form-control">
                                        <small class="text-muted">Accepted: PDF (Max 5MB)</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Status & Progress Tab -->
                        <div class="tab-pane fade" id="status-tab" role="tabpanel">
                            <h4 class="section-title mb-4">Status & Academic Progress</h4>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Enrollment Status <span class="text-danger">*</span></label>
                                    <select name="enrollment_status" class="form-select @error('enrollment_status') is-invalid @enderror" required>
                                        <option value="">Select Status</option>
                                        <option value="active" {{ $student->enrollment_status == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ $student->enrollment_status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        <option value="suspended" {{ $student->enrollment_status == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                        <option value="graduated" {{ $student->enrollment_status == 'graduated' ? 'selected' : '' }}>Graduated</option>
                                        <option value="withdrawn" {{ $student->enrollment_status == 'withdrawn' ? 'selected' : '' }}>Withdrawn</option>
                                    </select>
                                    @error('enrollment_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Academic Standing</label>
                                    <select name="academic_standing" class="form-select">
                                        <option value="good" {{ $student->academic_standing == 'good' ? 'selected' : '' }}>Good Standing</option>
                                        <option value="probation" {{ $student->academic_standing == 'probation' ? 'selected' : '' }}>Probation</option>
                                        <option value="suspension" {{ $student->academic_standing == 'suspension' ? 'selected' : '' }}>Suspension</option>
                                        <option value="dismissal" {{ $student->academic_standing == 'dismissal' ? 'selected' : '' }}>Dismissal</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Current GPA</label>
                                    <input type="number" name="current_gpa" value="{{ old('current_gpa', $student->current_gpa) }}"
                                           class="form-control" step="0.01" min="0" max="4">
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Cumulative GPA</label>
                                    <input type="number" name="cumulative_gpa" value="{{ old('cumulative_gpa', $student->cumulative_gpa) }}"
                                           class="form-control" step="0.01" min="0" max="4">
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Credits Earned</label>
                                    <input type="number" name="credits_earned" value="{{ old('credits_earned', $student->credits_earned) }}"
                                           class="form-control" min="0">
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Credits Completed</label>
                                    <input type="number" name="credits_completed" value="{{ old('credits_completed', $student->credits_completed) }}"
                                           class="form-control" min="0">
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Credits Required</label>
                                    <input type="number" name="credits_required" value="{{ old('credits_required', $student->credits_required) }}"
                                           class="form-control" min="0">
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Progress</label>
                                    <div class="progress" style="height: 30px;">
                                        <div class="progress-bar bg-success" role="progressbar" 
                                             style="width: {{ $student->progress_percentage }}%">
                                            {{ $student->progress_percentage }}% Complete
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Audit Information -->
                            @if($student->created_at)
                            <div class="audit-section mt-4">
                                <h5 class="section-subtitle">Record Information</h5>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <strong>Created:</strong> {{ $student->created_at->format('M d, Y h:i A') }}
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Last Updated:</strong> {{ $student->updated_at->format('M d, Y h:i A') }}
                                    </div>
                                    @if($student->last_activity_at)
                                    <div class="col-md-4">
                                        <strong>Last Activity:</strong> {{ $student->last_activity_at->format('M d, Y h:i A') }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions mt-5">
                        <div class="row">
                            <div class="col-6">
                                <a href="{{ route('students.show', $student) }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                            </div>
                            <div class="col-6 text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update Student
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
        .student-id-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
        }

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
        .insurance-section,
        .document-status-section,
        .audit-section {
            padding: 1.25rem;
            background: #f8f9fa;
            border-radius: 0.5rem;
        }

        .emergency-section {
            background: #fef2f2;
        }

        .document-status-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            background: white;
            border-radius: 0.375rem;
        }

        .status-indicator.success {
            color: #10b981;
        }

        .status-indicator.danger {
            color: #ef4444;
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

        @media (max-width: 768px) {
            .student-id-badge {
                position: static;
                margin-top: 0.5rem;
            }
        }
    </style>

    <!-- JavaScript -->
    <script>
        function toggleInternationalFields(checkbox) {
            const fields = document.querySelectorAll('.international-field');
            fields.forEach(field => {
                field.disabled = !checkbox.checked;
                if (!checkbox.checked) {
                    field.value = '';
                }
            });
        }
        
        // Track unsaved changes
        let formChanged = false;
        document.getElementById('editStudentForm').addEventListener('change', function() {
            formChanged = true;
        });
        
        window.addEventListener('beforeunload', function (e) {
            if (formChanged) {
                e.preventDefault();
                e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
            }
        });
        
        document.getElementById('editStudentForm').addEventListener('submit', function() {
            formChanged = false;
        });
    </script>
@endsection