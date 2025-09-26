@extends('layouts.app')

@section('title', 'Create New User')

@section('breadcrumb')
    <a href="{{ route('users.index') }}">User Management</a>
    <i class="fas fa-chevron-right"></i>
    <span>Create User</span>
@endsection

@section('page-actions')
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back to Users
    </a>
@endsection

@section('content')
<div class="container-fluid px-4">
    <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data" id="createUserForm">
        @csrf

        <!-- Basic Information -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary bg-gradient text-white">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Basic Information</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                        <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" 
                               class="form-control @error('first_name') is-invalid @enderror" required>
                        @error('first_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="middle_name" class="form-label">Middle Name</label>
                        <input type="text" name="middle_name" id="middle_name" value="{{ old('middle_name') }}" 
                               class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                        <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}" 
                               class="form-control @error('last_name') is-invalid @enderror" required>
                        @error('last_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" 
                                   class="form-control @error('email') is-invalid @enderror" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text">@</span>
                            <input type="text" name="username" id="username" value="{{ old('username') }}" 
                                   class="form-control @error('username') is-invalid @enderror" 
                                   placeholder="Leave blank to auto-generate">
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="text-muted">Username will be auto-generated if left blank</small>
                    </div>

                    <div class="col-md-6">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" name="password" id="password" 
                                   class="form-control @error('password') is-invalid @enderror" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                <i class="fas fa-eye"></i>
                            </button>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="text-muted">Minimum 8 characters</small>
                    </div>

                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" name="password_confirmation" id="password_confirmation" 
                                   class="form-control" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Type and Roles -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success bg-gradient text-white">
                <h5 class="mb-0"><i class="fas fa-user-tag me-2"></i>User Type and Roles</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="user_type" class="form-label">User Type <span class="text-danger">*</span></label>
                        <select name="user_type" id="user_type" 
                                class="form-select @error('user_type') is-invalid @enderror" required>
                            <option value="">Select Type</option>
                            <option value="admin" {{ old('user_type') == 'admin' ? 'selected' : '' }}>
                                <i class="fas fa-user-shield"></i> Administrator
                            </option>
                            <option value="faculty" {{ old('user_type') == 'faculty' ? 'selected' : '' }}>
                                <i class="fas fa-chalkboard-teacher"></i> Faculty
                            </option>
                            <option value="staff" {{ old('user_type') == 'staff' ? 'selected' : '' }}>
                                <i class="fas fa-user-tie"></i> Staff
                            </option>
                            <option value="student" {{ old('user_type') == 'student' ? 'selected' : '' }}>
                                <i class="fas fa-user-graduate"></i> Student
                            </option>
                            <option value="parent" {{ old('user_type') == 'parent' ? 'selected' : '' }}>
                                <i class="fas fa-user-friends"></i> Parent
                            </option>
                        </select>
                        @error('user_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="status" class="form-label">Account Status <span class="text-danger">*</span></label>
                        <select name="status" id="status" 
                                class="form-select @error('status') is-invalid @enderror" required>
                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>
                                <i class="fas fa-check-circle"></i> Active
                            </option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>
                                <i class="fas fa-minus-circle"></i> Inactive
                            </option>
                            <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>
                                <i class="fas fa-clock"></i> Pending
                            </option>
                            <option value="suspended" {{ old('status') == 'suspended' ? 'selected' : '' }}>
                                <i class="fas fa-ban"></i> Suspended
                            </option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Assign Roles <span class="text-danger">*</span></label>
                        <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                            <div class="row g-2">
                                @foreach($roles as $role)
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input type="checkbox" name="roles[]" value="{{ $role->id }}" 
                                                   class="form-check-input" id="role_{{ $role->id }}"
                                                   {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="role_{{ $role->id }}">
                                                {{ $role->name }}
                                                @if($role->description)
                                                    <i class="fas fa-info-circle text-muted ms-1" 
                                                       data-bs-toggle="tooltip" 
                                                       title="{{ $role->description }}"></i>
                                                @endif
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @error('roles')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Personal Information -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-info bg-gradient text-white">
                <h5 class="mb-0"><i class="fas fa-id-card me-2"></i>Personal Information</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label for="title" class="form-label">Title</label>
                        <select name="title" id="title" class="form-select">
                            <option value="">Select</option>
                            <option value="Mr." {{ old('title') == 'Mr.' ? 'selected' : '' }}>Mr.</option>
                            <option value="Ms." {{ old('title') == 'Ms.' ? 'selected' : '' }}>Ms.</option>
                            <option value="Mrs." {{ old('title') == 'Mrs.' ? 'selected' : '' }}>Mrs.</option>
                            <option value="Dr." {{ old('title') == 'Dr.' ? 'selected' : '' }}>Dr.</option>
                            <option value="Prof." {{ old('title') == 'Prof.' ? 'selected' : '' }}>Prof.</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="gender" class="form-label">Gender</label>
                        <select name="gender" id="gender" class="form-select">
                            <option value="">Select</option>
                            <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                            <option value="Other" {{ old('gender') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="date_of_birth" class="form-label">Date of Birth</label>
                        <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth') }}" 
                               class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label for="phone" class="form-label">Phone Number</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input type="text" name="phone" id="phone" value="{{ old('phone') }}" 
                                   class="form-control" placeholder="+1 (555) 123-4567">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="nationality" class="form-label">Nationality</label>
                        <input type="text" name="nationality" id="nationality" value="{{ old('nationality') }}" 
                               class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label for="profile_photo" class="form-label">Profile Photo</label>
                        <input type="file" name="profile_photo" id="profile_photo" 
                               class="form-control" accept="image/*" onchange="previewImage(this)">
                        <small class="text-muted">Max 2MB, JPEG/PNG only</small>
                        <div id="imagePreview" class="mt-2"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employment Information (conditional) -->
        <div class="card shadow-sm mb-4" id="employment-section" style="display: none;">
            <div class="card-header bg-warning bg-gradient">
                <h5 class="mb-0"><i class="fas fa-briefcase me-2"></i>Employment Information</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="employee_id" class="form-label">Employee ID</label>
                        <input type="text" name="employee_id" id="employee_id" value="{{ old('employee_id') }}" 
                               class="form-control @error('employee_id') is-invalid @enderror">
                        @error('employee_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-5">
                        <label for="department" class="form-label">Department</label>
                        <select name="department" id="department" class="form-select">
                            <option value="">Select Department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept }}" {{ old('department') == $dept ? 'selected' : '' }}>
                                    {{ $dept }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="designation" class="form-label">Designation</label>
                        <input type="text" name="designation" id="designation" value="{{ old('designation') }}" 
                               class="form-control" placeholder="e.g., Professor, Assistant">
                    </div>

                    <div class="col-md-4">
                        <label for="date_of_joining" class="form-label">Date of Joining</label>
                        <input type="date" name="date_of_joining" id="date_of_joining" value="{{ old('date_of_joining') }}" 
                               class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label for="employment_status" class="form-label">Employment Status</label>
                        <select name="employment_status" id="employment_status" class="form-select">
                            <option value="">Select Status</option>
                            <option value="full-time" {{ old('employment_status') == 'full-time' ? 'selected' : '' }}>Full-time</option>
                            <option value="part-time" {{ old('employment_status') == 'part-time' ? 'selected' : '' }}>Part-time</option>
                            <option value="contract" {{ old('employment_status') == 'contract' ? 'selected' : '' }}>Contract</option>
                            <option value="visiting" {{ old('employment_status') == 'visiting' ? 'selected' : '' }}>Visiting</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Options -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-secondary bg-gradient text-white">
                <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Account Options</h5>
            </div>
            <div class="card-body">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="must_change_password" value="1" 
                           id="mustChange" {{ old('must_change_password') ? 'checked' : '' }}>
                    <label class="form-check-label" for="mustChange">
                        <i class="fas fa-key text-warning me-1"></i>
                        User must change password on first login
                    </label>
                </div>
                
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="send_welcome_email" value="1" 
                           id="sendEmail" {{ old('send_welcome_email', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="sendEmail">
                        <i class="fas fa-envelope text-info me-1"></i>
                        Send welcome email with login credentials
                    </label>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="d-flex justify-content-between">
            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                <i class="fas fa-times me-2"></i>Cancel
            </a>
            <div>
                <button type="submit" name="action" value="save_and_new" class="btn btn-outline-primary">
                    <i class="fas fa-plus me-2"></i>Save & Create Another
                </button>
                <button type="submit" name="action" value="save" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Create User
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .card-header {
        font-weight: 600;
    }
    
    .form-label {
        font-weight: 500;
        color: #495057;
    }
    
    .text-danger {
        font-weight: bold;
    }
    
    #imagePreview img {
        max-width: 150px;
        max-height: 150px;
        border: 2px solid #dee2e6;
        border-radius: 8px;
        padding: 4px;
    }
</style>
@endpush

@push('scripts')
<script>
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Show/hide employment section based on user type
    document.getElementById('user_type').addEventListener('change', function() {
        var employmentSection = document.getElementById('employment-section');
        if (this.value === 'faculty' || this.value === 'staff' || this.value === 'admin') {
            employmentSection.style.display = 'block';
        } else {
            employmentSection.style.display = 'none';
        }
    });

    // Trigger on page load if old value exists
    window.addEventListener('DOMContentLoaded', function() {
        var userType = document.getElementById('user_type').value;
        if (userType === 'faculty' || userType === 'staff' || userType === 'admin') {
            document.getElementById('employment-section').style.display = 'block';
        }
    });

    // Toggle password visibility
    function togglePassword(fieldId) {
        var field = document.getElementById(fieldId);
        var button = event.currentTarget;
        var icon = button.querySelector('i');
        
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    // Preview image
    function previewImage(input) {
        var preview = document.getElementById('imagePreview');
        preview.innerHTML = '';
        
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush