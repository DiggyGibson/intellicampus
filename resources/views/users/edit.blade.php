@extends('layouts.app')

@section('title', 'Edit User - ' . $user->name)

@section('breadcrumb')
    <a href="{{ route('users.index') }}">User Management</a>
    <i class="fas fa-chevron-right"></i>
    <a href="{{ route('users.show', $user) }}">{{ $user->name }}</a>
    <i class="fas fa-chevron-right"></i>
    <span>Edit</span>
@endsection

@section('page-actions')
    <a href="{{ route('users.show', $user) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back to Profile
    </a>
@endsection

@section('content')
<div class="container-fluid px-4">
    <!-- Flash Messages -->
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

    <form method="POST" action="{{ route('users.update', $user) }}" enctype="multipart/form-data" id="editUserForm">
        @csrf
        @method('PUT')

        <!-- User Info Header -->
        <div class="card shadow-sm mb-4 bg-light">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    @if($user->profile_photo)
                        <img src="{{ Storage::url($user->profile_photo) }}" 
                             alt="{{ $user->name }}" 
                             class="rounded-circle me-3"
                             style="width: 60px; height: 60px; object-fit: cover;">
                    @else
                        <div class="rounded-circle bg-primary bg-gradient text-white d-flex align-items-center justify-content-center me-3"
                             style="width: 60px; height: 60px; font-size: 1.5rem;">
                            {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <h4 class="mb-0">Editing: {{ $user->name }}</h4>
                        <small class="text-muted">User ID: #{{ $user->id }} | Created: {{ $user->created_at->format('M d, Y') }}</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Basic Information -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary bg-gradient text-white">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Basic Information</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                        <input type="text" name="first_name" id="first_name" 
                               value="{{ old('first_name', $user->first_name) }}" 
                               class="form-control @error('first_name') is-invalid @enderror" required>
                        @error('first_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="middle_name" class="form-label">Middle Name</label>
                        <input type="text" name="middle_name" id="middle_name" 
                               value="{{ old('middle_name', $user->middle_name) }}" 
                               class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                        <input type="text" name="last_name" id="last_name" 
                               value="{{ old('last_name', $user->last_name) }}" 
                               class="form-control @error('last_name') is-invalid @enderror" required>
                        @error('last_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" name="email" id="email" 
                                   value="{{ old('email', $user->email) }}" 
                                   class="form-control @error('email') is-invalid @enderror" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @if($user->email_verified_at)
                            <small class="text-success"><i class="fas fa-check-circle"></i> Email verified on {{ $user->email_verified_at->format('M d, Y') }}</small>
                        @else
                            <small class="text-warning"><i class="fas fa-exclamation-circle"></i> Email not verified</small>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text">@</span>
                            <input type="text" name="username" id="username" 
                                   value="{{ old('username', $user->username) }}" 
                                   class="form-control @error('username') is-invalid @enderror">
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                            <option value="admin" {{ old('user_type', $user->user_type) == 'admin' ? 'selected' : '' }}>
                                Administrator
                            </option>
                            <option value="faculty" {{ old('user_type', $user->user_type) == 'faculty' ? 'selected' : '' }}>
                                Faculty
                            </option>
                            <option value="staff" {{ old('user_type', $user->user_type) == 'staff' ? 'selected' : '' }}>
                                Staff
                            </option>
                            <option value="student" {{ old('user_type', $user->user_type) == 'student' ? 'selected' : '' }}>
                                Student
                            </option>
                            <option value="parent" {{ old('user_type', $user->user_type) == 'parent' ? 'selected' : '' }}>
                                Parent
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
                            <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>
                                Active
                            </option>
                            <option value="inactive" {{ old('status', $user->status) == 'inactive' ? 'selected' : '' }}>
                                Inactive
                            </option>
                            <option value="pending" {{ old('status', $user->status) == 'pending' ? 'selected' : '' }}>
                                Pending
                            </option>
                            <option value="suspended" {{ old('status', $user->status) == 'suspended' ? 'selected' : '' }}>
                                Suspended
                            </option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Assigned Roles <span class="text-danger">*</span></label>
                        <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto; background-color: #f8f9fa;">
                            <div class="row g-2">
                                @foreach($roles as $role)
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input type="checkbox" name="roles[]" value="{{ $role->id }}" 
                                                   class="form-check-input" id="role_{{ $role->id }}"
                                                   {{ in_array($role->id, old('roles', $userRoles)) ? 'checked' : '' }}>
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
                            <option value="Mr." {{ old('title', $user->title) == 'Mr.' ? 'selected' : '' }}>Mr.</option>
                            <option value="Ms." {{ old('title', $user->title) == 'Ms.' ? 'selected' : '' }}>Ms.</option>
                            <option value="Mrs." {{ old('title', $user->title) == 'Mrs.' ? 'selected' : '' }}>Mrs.</option>
                            <option value="Dr." {{ old('title', $user->title) == 'Dr.' ? 'selected' : '' }}>Dr.</option>
                            <option value="Prof." {{ old('title', $user->title) == 'Prof.' ? 'selected' : '' }}>Prof.</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="gender" class="form-label">Gender</label>
                        <select name="gender" id="gender" class="form-select">
                            <option value="">Select</option>
                            <option value="Male" {{ old('gender', $user->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('gender', $user->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                            <option value="Other" {{ old('gender', $user->gender) == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="date_of_birth" class="form-label">Date of Birth</label>
                        <input type="date" name="date_of_birth" id="date_of_birth" 
                               value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}" 
                               class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label for="phone" class="form-label">Phone Number</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input type="text" name="phone" id="phone" 
                                   value="{{ old('phone', $user->phone) }}" 
                                   class="form-control">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="nationality" class="form-label">Nationality</label>
                        <input type="text" name="nationality" id="nationality" 
                               value="{{ old('nationality', $user->nationality) }}" 
                               class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label for="profile_photo" class="form-label">Profile Photo</label>
                        @if($user->profile_photo)
                            <div class="d-flex align-items-center">
                                <img src="{{ Storage::url($user->profile_photo) }}" alt="Current Photo" 
                                     class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                <div class="flex-fill">
                                    <input type="file" name="profile_photo" id="profile_photo" 
                                           class="form-control" accept="image/*" onchange="previewImage(this)">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" name="remove_photo" value="1" id="removePhoto">
                                        <label class="form-check-label text-danger" for="removePhoto">
                                            <i class="fas fa-trash"></i> Remove current photo
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @else
                            <input type="file" name="profile_photo" id="profile_photo" 
                                   class="form-control" accept="image/*" onchange="previewImage(this)">
                        @endif
                        <small class="text-muted">Max 2MB, JPEG/PNG only</small>
                        <div id="imagePreview" class="mt-2"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employment Information -->
        <div class="card shadow-sm mb-4" id="employment-section" 
             style="{{ in_array($user->user_type, ['faculty', 'staff', 'admin']) ? '' : 'display: none;' }}">
            <div class="card-header bg-warning bg-gradient">
                <h5 class="mb-0"><i class="fas fa-briefcase me-2"></i>Employment Information</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="employee_id" class="form-label">Employee ID</label>
                        <input type="text" name="employee_id" id="employee_id" 
                               value="{{ old('employee_id', $user->employee_id) }}" 
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
                                <option value="{{ $dept }}" {{ old('department', $user->department) == $dept ? 'selected' : '' }}>
                                    {{ $dept }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="designation" class="form-label">Designation</label>
                        <input type="text" name="designation" id="designation" 
                               value="{{ old('designation', $user->designation) }}" 
                               class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label for="date_of_joining" class="form-label">Date of Joining</label>
                        <input type="date" name="date_of_joining" id="date_of_joining" 
                               value="{{ old('date_of_joining', $user->date_of_joining?->format('Y-m-d')) }}" 
                               class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label for="employment_status" class="form-label">Employment Status</label>
                        <select name="employment_status" id="employment_status" class="form-select">
                            <option value="">Select Status</option>
                            <option value="full-time" {{ old('employment_status', $user->employment_status) == 'full-time' ? 'selected' : '' }}>Full-time</option>
                            <option value="part-time" {{ old('employment_status', $user->employment_status) == 'part-time' ? 'selected' : '' }}>Part-time</option>
                            <option value="contract" {{ old('employment_status', $user->employment_status) == 'contract' ? 'selected' : '' }}>Contract</option>
                            <option value="visiting" {{ old('employment_status', $user->employment_status) == 'visiting' ? 'selected' : '' }}>Visiting</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Password Change -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-danger bg-gradient text-white">
                <h5 class="mb-0"><i class="fas fa-key me-2"></i>Password Management</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Leave password fields blank to keep the current password unchanged.
                </div>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="password" class="form-label">New Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" name="password" id="password" 
                                   class="form-control @error('password') is-invalid @enderror">
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
                        <label for="password_confirmation" class="form-label">Confirm New Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" name="password_confirmation" id="password_confirmation" 
                                   class="form-control">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="must_change_password" value="1" 
                                   id="mustChange" {{ old('must_change_password', $user->must_change_password) ? 'checked' : '' }}>
                            <label class="form-check-label" for="mustChange">
                                <i class="fas fa-exclamation-triangle text-warning me-1"></i>
                                User must change password on next login
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="d-flex justify-content-between mb-4">
            <div>
                @if($user->id !== auth()->id() && !method_exists($user, 'isSuperAdmin') || ($user->id !== auth()->id() && !$user->isSuperAdmin()))
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                        <i class="fas fa-trash me-2"></i>Delete User
                    </button>
                @endif
            </div>
            <div>
                <a href="{{ route('users.show', $user) }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Update User
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Delete Confirmation Modal -->
@if($user->id !== auth()->id())
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this user?</p>
                <div class="alert alert-warning">
                    <strong>User:</strong> {{ $user->name }}<br>
                    <strong>Email:</strong> {{ $user->email }}<br>
                    <strong>Type:</strong> {{ ucfirst($user->user_type) }}
                </div>
                <p class="text-danger">
                    <i class="fas fa-exclamation-circle"></i> 
                    This action cannot be undone. All user data will be permanently deleted.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>Delete User
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

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
    
    .alert-info {
        background-color: #d1ecf1;
        border-color: #bee5eb;
        color: #0c5460;
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