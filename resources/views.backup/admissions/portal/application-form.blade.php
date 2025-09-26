{{-- resources/views/admissions/portal/application-form.blade.php --}}
@extends('layouts.app')

@section('title', 'Application Form - ' . $application->application_number)

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="h3 font-weight-bold text-gray-800">
                        <i class="fas fa-file-alt me-2"></i>Application Form
                    </h2>
                    <p class="text-muted mb-0">
                        Application #{{ $application->application_number }} | 
                        {{ $application->program->name }} | 
                        {{ $application->term->name }}
                    </p>
                </div>
                <div>
                    <button type="button" class="btn btn-outline-primary" id="save-draft">
                        <i class="fas fa-save me-1"></i> Save Draft
                    </button>
                    <a href="{{ route('admissions.portal.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Exit
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Progress Bar --}}
    @include('admissions.partials.application-progress', ['application' => $application])

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Main Form --}}
    <form id="application-form" method="POST" action="{{ route('admissions.form.save', $application->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="row">
            {{-- Left Sidebar - Navigation --}}
            <div class="col-md-3 mb-4">
                <div class="card shadow sticky-top" style="top: 20px;">
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush" id="form-navigation">
                            <a href="#personal-info" class="list-group-item list-group-item-action active">
                                <i class="fas fa-user me-2"></i> Personal Information
                                <span class="badge bg-success float-end" id="personal-badge" style="display: none;">✓</span>
                            </a>
                            <a href="#contact-info" class="list-group-item list-group-item-action">
                                <i class="fas fa-address-card me-2"></i> Contact Details
                                <span class="badge bg-success float-end" id="contact-badge" style="display: none;">✓</span>
                            </a>
                            <a href="#education-bg" class="list-group-item list-group-item-action">
                                <i class="fas fa-graduation-cap me-2"></i> Educational Background
                                <span class="badge bg-success float-end" id="education-badge" style="display: none;">✓</span>
                            </a>
                            <a href="#test-scores" class="list-group-item list-group-item-action">
                                <i class="fas fa-clipboard-check me-2"></i> Test Scores
                                <span class="badge bg-success float-end" id="test-badge" style="display: none;">✓</span>
                            </a>
                            <a href="#essays" class="list-group-item list-group-item-action">
                                <i class="fas fa-pen me-2"></i> Essays & Statements
                                <span class="badge bg-success float-end" id="essays-badge" style="display: none;">✓</span>
                            </a>
                            <a href="#activities" class="list-group-item list-group-item-action">
                                <i class="fas fa-trophy me-2"></i> Activities & Awards
                                <span class="badge bg-success float-end" id="activities-badge" style="display: none;">✓</span>
                            </a>
                            <a href="#references" class="list-group-item list-group-item-action">
                                <i class="fas fa-user-friends me-2"></i> References
                                <span class="badge bg-success float-end" id="references-badge" style="display: none;">✓</span>
                            </a>
                            <a href="#documents" class="list-group-item list-group-item-action">
                                <i class="fas fa-folder-open me-2"></i> Documents
                                <span class="badge bg-success float-end" id="documents-badge" style="display: none;">✓</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Main Content Area --}}
            <div class="col-md-9">
                {{-- Personal Information Section --}}
                <div class="card shadow mb-4" id="personal-info">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i>Personal Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                       id="first_name" name="first_name" value="{{ old('first_name', $application->first_name) }}" required>
                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="middle_name" class="form-label">Middle Name</label>
                                <input type="text" class="form-control @error('middle_name') is-invalid @enderror" 
                                       id="middle_name" name="middle_name" value="{{ old('middle_name', $application->middle_name) }}">
                                @error('middle_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                       id="last_name" name="last_name" value="{{ old('last_name', $application->last_name) }}" required>
                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="date_of_birth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" 
                                       id="date_of_birth" name="date_of_birth" 
                                       value="{{ old('date_of_birth', $application->date_of_birth?->format('Y-m-d')) }}" required>
                                @error('date_of_birth')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                                <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="male" {{ old('gender', $application->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender', $application->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ old('gender', $application->gender) == 'other' ? 'selected' : '' }}>Other</option>
                                    <option value="prefer_not_to_say" {{ old('gender', $application->gender) == 'prefer_not_to_say' ? 'selected' : '' }}>Prefer not to say</option>
                                </select>
                                @error('gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="nationality" class="form-label">Nationality <span class="text-danger">*</span></label>
                                <select class="form-select @error('nationality') is-invalid @enderror" id="nationality" name="nationality" required>
                                    <option value="">Select Nationality</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->name }}" {{ old('nationality', $application->nationality) == $country->name ? 'selected' : '' }}>
                                            {{ $country->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('nationality')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="national_id" class="form-label">National ID/SSN</label>
                                <input type="text" class="form-control @error('national_id') is-invalid @enderror" 
                                       id="national_id" name="national_id" value="{{ old('national_id', $application->national_id) }}">
                                @error('national_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="passport_number" class="form-label">Passport Number</label>
                                <input type="text" class="form-control @error('passport_number') is-invalid @enderror" 
                                       id="passport_number" name="passport_number" value="{{ old('passport_number', $application->passport_number) }}">
                                @error('passport_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Contact Information Section --}}
                <div class="card shadow mb-4" id="contact-info">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-address-card me-2"></i>Contact Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email', $application->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="phone_primary" class="form-label">Primary Phone <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control @error('phone_primary') is-invalid @enderror" 
                                       id="phone_primary" name="phone_primary" value="{{ old('phone_primary', $application->phone_primary) }}" required>
                                @error('phone_primary')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="current_address" class="form-label">Current Address <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('current_address') is-invalid @enderror" 
                                      id="current_address" name="current_address" rows="2" required>{{ old('current_address', $application->current_address) }}</textarea>
                            @error('current_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                       id="city" name="city" value="{{ old('city', $application->city) }}" required>
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="state_province" class="form-label">State/Province</label>
                                <input type="text" class="form-control @error('state_province') is-invalid @enderror" 
                                       id="state_province" name="state_province" value="{{ old('state_province', $application->state_province) }}">
                                @error('state_province')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="country" class="form-label">Country <span class="text-danger">*</span></label>
                                <select class="form-select @error('country') is-invalid @enderror" id="country" name="country" required>
                                    <option value="">Select Country</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->name }}" {{ old('country', $application->country) == $country->name ? 'selected' : '' }}>
                                            {{ $country->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Emergency Contact --}}
                        <h6 class="mt-3 mb-3 text-muted">Emergency Contact</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="emergency_contact_name" class="form-label">Contact Name</label>
                                <input type="text" class="form-control @error('emergency_contact_name') is-invalid @enderror" 
                                       id="emergency_contact_name" name="emergency_contact_name" 
                                       value="{{ old('emergency_contact_name', $application->emergency_contact_name) }}">
                                @error('emergency_contact_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="emergency_contact_phone" class="form-label">Contact Phone</label>
                                <input type="tel" class="form-control @error('emergency_contact_phone') is-invalid @enderror" 
                                       id="emergency_contact_phone" name="emergency_contact_phone" 
                                       value="{{ old('emergency_contact_phone', $application->emergency_contact_phone) }}">
                                @error('emergency_contact_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Educational Background Section --}}
                <div class="card shadow mb-4" id="education-bg">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Educational Background</h5>
                    </div>
                    <div class="card-body">
                        @if($application->application_type == 'freshman')
                            {{-- High School Information --}}
                            <h6 class="mb-3 text-muted">High School Information</h6>
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label for="high_school_name" class="form-label">High School Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('high_school_name') is-invalid @enderror" 
                                           id="high_school_name" name="high_school_name" 
                                           value="{{ old('high_school_name', $application->high_school_name) }}" required>
                                    @error('high_school_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="high_school_graduation_date" class="form-label">Graduation Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('high_school_graduation_date') is-invalid @enderror" 
                                           id="high_school_graduation_date" name="high_school_graduation_date" 
                                           value="{{ old('high_school_graduation_date', $application->high_school_graduation_date?->format('Y-m-d')) }}" required>
                                    @error('high_school_graduation_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @else
                            {{-- Previous Institution Information --}}
                            <h6 class="mb-3 text-muted">Previous Institution</h6>
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label for="previous_institution" class="form-label">Institution Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('previous_institution') is-invalid @enderror" 
                                           id="previous_institution" name="previous_institution" 
                                           value="{{ old('previous_institution', $application->previous_institution) }}" required>
                                    @error('previous_institution')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="previous_degree" class="form-label">Degree/Certificate</label>
                                    <input type="text" class="form-control @error('previous_degree') is-invalid @enderror" 
                                           id="previous_degree" name="previous_degree" 
                                           value="{{ old('previous_degree', $application->previous_degree) }}">
                                    @error('previous_degree')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @endif
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="previous_gpa" class="form-label">GPA <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" max="5" class="form-control @error('previous_gpa') is-invalid @enderror" 
                                       id="previous_gpa" name="previous_gpa" 
                                       value="{{ old('previous_gpa', $application->previous_gpa) }}" required>
                                @error('previous_gpa')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="gpa_scale" class="form-label">GPA Scale <span class="text-danger">*</span></label>
                                <select class="form-select @error('gpa_scale') is-invalid @enderror" id="gpa_scale" name="gpa_scale" required>
                                    <option value="">Select Scale</option>
                                    <option value="4.0" {{ old('gpa_scale', $application->gpa_scale) == '4.0' ? 'selected' : '' }}>4.0</option>
                                    <option value="5.0" {{ old('gpa_scale', $application->gpa_scale) == '5.0' ? 'selected' : '' }}>5.0</option>
                                    <option value="100" {{ old('gpa_scale', $application->gpa_scale) == '100' ? 'selected' : '' }}>100</option>
                                </select>
                                @error('gpa_scale')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="class_rank" class="form-label">Class Rank (if available)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('class_rank') is-invalid @enderror" 
                                           id="class_rank" name="class_rank" placeholder="Rank"
                                           value="{{ old('class_rank', $application->class_rank) }}">
                                    <span class="input-group-text">of</span>
                                    <input type="number" class="form-control @error('class_size') is-invalid @enderror" 
                                           id="class_size" name="class_size" placeholder="Total"
                                           value="{{ old('class_size', $application->class_size) }}">
                                </div>
                                @error('class_rank')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Form Navigation Buttons --}}
                <div class="card shadow">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" id="prev-section" style="display: none;">
                                <i class="fas fa-arrow-left me-1"></i> Previous
                            </button>
                            <button type="button" class="btn btn-primary ms-auto" id="next-section">
                                Next <i class="fas fa-arrow-right ms-1"></i>
                            </button>
                            <button type="submit" class="btn btn-success ms-2" id="submit-application" style="display: none;">
                                <i class="fas fa-paper-plane me-1"></i> Submit Application
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-save functionality
    let autoSaveTimer;
    const form = document.getElementById('application-form');
    
    function autoSave() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(function() {
            const formData = new FormData(form);
            formData.append('auto_save', 'true');
            
            fetch('{{ route("admissions.form.autosave", $application->id) }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show a subtle save indicator
                    showNotification('Draft saved', 'success');
                }
            })
            .catch(error => console.error('Auto-save error:', error));
        }, 3000); // Save after 3 seconds of inactivity
    }
    
    // Attach auto-save to form inputs
    form.querySelectorAll('input, select, textarea').forEach(element => {
        element.addEventListener('input', autoSave);
        element.addEventListener('change', autoSave);
    });
    
    // Section navigation
    const sections = document.querySelectorAll('.card[id]');
    let currentSection = 0;
    
    function showSection(index) {
        sections.forEach((section, i) => {
            section.style.display = i === index ? 'block' : 'none';
        });
        
        // Update navigation buttons
        document.getElementById('prev-section').style.display = index > 0 ? 'inline-block' : 'none';
        document.getElementById('next-section').style.display = index < sections.length - 1 ? 'inline-block' : 'none';
        document.getElementById('submit-application').style.display = index === sections.length - 1 ? 'inline-block' : 'none';
        
        // Update sidebar navigation
        document.querySelectorAll('#form-navigation a').forEach((link, i) => {
            link.classList.toggle('active', i === index);
        });
        
        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    
    // Navigation button handlers
    document.getElementById('next-section').addEventListener('click', function() {
        if (validateSection(currentSection)) {
            currentSection++;
            showSection(currentSection);
        }
    });
    
    document.getElementById('prev-section').addEventListener('click', function() {
        currentSection--;
        showSection(currentSection);
    });
    
    // Sidebar navigation clicks
    document.querySelectorAll('#form-navigation a').forEach((link, index) => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            if (validateSection(currentSection)) {
                currentSection = index;
                showSection(currentSection);
            }
        });
    });
    
    // Section validation
    function validateSection(sectionIndex) {
        const section = sections[sectionIndex];
        const requiredFields = section.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        if (!isValid) {
            showNotification('Please fill in all required fields', 'error');
        } else {
            // Mark section as complete
            const badge = document.querySelector(`#form-navigation a:nth-child(${sectionIndex + 1}) .badge`);
            if (badge) {
                badge.style.display = 'inline';
            }
        }
        
        return isValid;
    }
    
    // Notification helper
    function showNotification(message, type) {
        // Implementation for showing notifications
        console.log(type + ': ' + message);
    }
    
    // Initialize first section
    showSection(0);
});
</script>
@endpush