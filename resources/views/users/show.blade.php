@extends('layouts.app')

@section('title', 'User Profile - ' . $user->name)

@section('breadcrumb')
    <a href="{{ route('users.index') }}">User Management</a>
    <i class="fas fa-chevron-right"></i>
    <span>{{ $user->name }}</span>
@endsection

@section('page-actions')
    <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
        <i class="fas fa-edit"></i> Edit User
    </a>
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back to Users
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

    <div class="row">
        <!-- Left Column - Profile Card -->
        <div class="col-lg-4 mb-4">
            <!-- Profile Information Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <!-- Profile Photo -->
                    @if($user->profile_photo)
                        <img src="{{ Storage::url($user->profile_photo) }}" 
                             alt="{{ $user->name }}" 
                             class="rounded-circle mb-3 border border-3 border-primary"
                             style="width: 150px; height: 150px; object-fit: cover;">
                    @else
                        <div class="rounded-circle bg-primary bg-gradient text-white d-inline-flex align-items-center justify-content-center mb-3"
                             style="width: 150px; height: 150px; font-size: 3rem;">
                            {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                        </div>
                    @endif
                    
                    <h4 class="mb-1">{{ $user->name }}</h4>
                    <p class="text-muted mb-2">{{ $user->email }}</p>
                    @if($user->username)
                        <p class="text-muted small mb-3">
                            <i class="fas fa-at me-1"></i>{{ $user->username }}
                        </p>
                    @endif
                    
                    <!-- Status Badge -->
                    <div class="mb-3">
                        @if($user->status == 'active')
                            <span class="badge bg-success fs-6">
                                <i class="fas fa-check-circle me-1"></i>Active
                            </span>
                        @elseif($user->status == 'inactive')
                            <span class="badge bg-secondary fs-6">
                                <i class="fas fa-minus-circle me-1"></i>Inactive
                            </span>
                        @elseif($user->status == 'suspended')
                            <span class="badge bg-danger fs-6">
                                <i class="fas fa-ban me-1"></i>Suspended
                            </span>
                        @elseif($user->status == 'pending')
                            <span class="badge bg-warning text-dark fs-6">
                                <i class="fas fa-clock me-1"></i>Pending
                            </span>
                        @endif
                    </div>
                    
                    <hr>
                    
                    <!-- Key Information -->
                    <div class="text-start">
                        <div class="mb-2">
                            <small class="text-muted d-block">User Type</small>
                            <span class="fw-semibold">{{ ucfirst($user->user_type ?? 'Not Specified') }}</span>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">Member Since</small>
                            <span class="fw-semibold">{{ $user->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">Last Login</small>
                            <span class="fw-semibold">
                                {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                            </span>
                        </div>
                        @if($user->last_login_ip)
                            <div class="mb-2">
                                <small class="text-muted d-block">Last IP</small>
                                <span class="fw-semibold">{{ $user->last_login_ip }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($user->id !== auth()->id())
                            <form action="{{ route('users.toggle-status', $user) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-warning w-100">
                                    @if($user->status === 'active')
                                        <i class="fas fa-user-slash me-2"></i>Suspend User
                                    @else
                                        <i class="fas fa-user-check me-2"></i>Activate User
                                    @endif
                                </button>
                            </form>
                        @endif
                        
                        <a href="{{ route('users.manage-roles', $user) }}" class="btn btn-info">
                            <i class="fas fa-user-tag me-2"></i>Manage Roles
                        </a>
                        
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#resetPasswordModal">
                            <i class="fas fa-key me-2"></i>Reset Password
                        </button>
                        
                        @if($user->user_type === 'student')
                            <a href="{{ route('students.show', $user->student_id ?? $user->id) }}" class="btn btn-success">
                                <i class="fas fa-user-graduate me-2"></i>View Student Profile
                            </a>
                        @elseif($user->user_type === 'faculty')
                            <a href="{{ route('faculty.show', $user->id) }}" class="btn btn-primary">
                                <i class="fas fa-chalkboard-teacher me-2"></i>View Faculty Profile
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Assigned Roles Card -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Assigned Roles</h6>
                </div>
                <div class="card-body">
                    @forelse($user->roles as $role)
                        <div class="d-flex justify-content-between align-items-center p-2 mb-2 bg-light rounded">
                            <span class="fw-semibold">{{ $role->name }}</span>
                            <span class="badge bg-secondary">Priority: {{ $role->priority }}</span>
                        </div>
                    @empty
                        <p class="text-muted text-center mb-0">No roles assigned</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Column - Detailed Information -->
        <div class="col-lg-8">
            <!-- Navigation Tabs -->
            <ul class="nav nav-tabs mb-4" id="userTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button">
                        <i class="fas fa-user me-2"></i>Personal Info
                    </button>
                </li>
                @if(in_array($user->user_type, ['faculty', 'staff', 'admin']))
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="employment-tab" data-bs-toggle="tab" data-bs-target="#employment" type="button">
                        <i class="fas fa-briefcase me-2"></i>Employment
                    </button>
                </li>
                @endif
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="permissions-tab" data-bs-toggle="tab" data-bs-target="#permissions" type="button">
                        <i class="fas fa-lock me-2"></i>Permissions
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity" type="button">
                        <i class="fas fa-history me-2"></i>Activity Log
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="sessions-tab" data-bs-toggle="tab" data-bs-target="#sessions" type="button">
                        <i class="fas fa-desktop me-2"></i>Sessions
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="userTabsContent">
                <!-- Personal Information Tab -->
                <div class="tab-pane fade show active" id="personal" role="tabpanel">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-id-card me-2"></i>Personal Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="text-muted small">Full Name</label>
                                    <p class="fw-semibold">{{ $user->full_name ?: $user->name }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small">Gender</label>
                                    <p class="fw-semibold">{{ ucfirst($user->gender ?? 'Not Specified') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small">Date of Birth</label>
                                    <p class="fw-semibold">
                                        {{ $user->date_of_birth ? $user->date_of_birth->format('M d, Y') : 'Not Specified' }}
                                        @if($user->date_of_birth)
                                            <span class="text-muted">({{ $user->date_of_birth->age }} years old)</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small">Phone Number</label>
                                    <p class="fw-semibold">
                                        @if($user->phone)
                                            <a href="tel:{{ $user->phone }}">{{ $user->phone }}</a>
                                        @else
                                            Not Specified
                                        @endif
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small">Nationality</label>
                                    <p class="fw-semibold">{{ $user->nationality ?? 'Not Specified' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small">Identification Type</label>
                                    <p class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $user->id_type ?? 'Not Specified')) }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small">Identification Number</label>
                                    <p class="fw-semibold">{{ $user->id_number ?? 'Not Specified' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small">Alternate Email</label>
                                    <p class="fw-semibold">
                                        @if($user->alternate_email)
                                            <a href="mailto:{{ $user->alternate_email }}">{{ $user->alternate_email }}</a>
                                        @else
                                            Not Specified
                                        @endif
                                    </p>
                                </div>
                                <div class="col-12">
                                    <label class="text-muted small">Address</label>
                                    <p class="fw-semibold">
                                        @if($user->address || $user->city || $user->state || $user->country)
                                            {{ $user->address ?? '' }}<br>
                                            {{ implode(', ', array_filter([
                                                $user->city,
                                                $user->state,
                                                $user->postal_code,
                                                $user->country
                                            ])) }}
                                        @else
                                            Not Specified
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Employment Information Tab (conditional) -->
                @if(in_array($user->user_type, ['faculty', 'staff', 'admin']))
                <div class="tab-pane fade" id="employment" role="tabpanel">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-building me-2"></i>Employment Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="text-muted small">Employee ID</label>
                                    <p class="fw-semibold">{{ $user->employee_id ?? 'Not Assigned' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small">Department</label>
                                    <p class="fw-semibold">{{ $user->department ?? 'Not Assigned' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small">Designation</label>
                                    <p class="fw-semibold">{{ $user->designation ?? 'Not Specified' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small">Date of Joining</label>
                                    <p class="fw-semibold">
                                        {{ $user->date_of_joining ? $user->date_of_joining->format('M d, Y') : 'Not Specified' }}
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small">Employment Status</label>
                                    <p class="fw-semibold">
                                        <span class="badge bg-info">
                                            {{ ucfirst(str_replace('-', ' ', $user->employment_status ?? 'Not Specified')) }}
                                        </span>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small">Office Location</label>
                                    <p class="fw-semibold">{{ $user->office_location ?? 'Not Assigned' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small">Office Phone</label>
                                    <p class="fw-semibold">
                                        @if($user->office_phone)
                                            <a href="tel:{{ $user->office_phone }}">{{ $user->office_phone }}</a>
                                        @else
                                            Not Specified
                                        @endif
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small">Reports To</label>
                                    <p class="fw-semibold">{{ $user->reports_to ?? 'Not Specified' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Permissions Tab -->
                <div class="tab-pane fade" id="permissions" role="tabpanel">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Permissions</h5>
                        </div>
                        <div class="card-body">
                            @if($permissionsByModule->count() > 0)
                                @foreach($permissionsByModule as $module => $permissions)
                                    <div class="mb-4">
                                        <h6 class="text-primary mb-3">
                                            <i class="fas fa-folder me-2"></i>{{ ucfirst($module) }} Module
                                        </h6>
                                        <div class="row g-2">
                                            @foreach($permissions as $permission)
                                                <div class="col-md-4">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" checked disabled>
                                                        <label class="form-check-label">
                                                            {{ $permission->display_name ?? $permission->name }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @if(!$loop->last)
                                        <hr>
                                    @endif
                                @endforeach
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-lock fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No permissions assigned to this user.</p>
                                    <a href="{{ route('users.manage-roles', $user) }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Assign Roles
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Activity Log Tab -->
                <div class="tab-pane fade" id="activity" role="tabpanel">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Activity</h5>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                @forelse($user->activityLogs()->latest()->limit(20)->get() as $activity)
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-primary"></div>
                                        <div class="timeline-content">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1">{{ $activity->description }}</h6>
                                                    <small class="text-muted">
                                                        <i class="fas fa-clock me-1"></i>
                                                        {{ $activity->performed_at->format('M d, Y h:i A') }}
                                                        ({{ $activity->performed_at->diffForHumans() }})
                                                    </small>
                                                    @if($activity->ip_address)
                                                        <br>
                                                        <small class="text-muted">
                                                            <i class="fas fa-globe me-1"></i>
                                                            IP: {{ $activity->ip_address }}
                                                        </small>
                                                    @endif
                                                    @if($activity->user_agent)
                                                        <br>
                                                        <small class="text-muted">
                                                            <i class="fas fa-desktop me-1"></i>
                                                            {{ Str::limit($activity->user_agent, 50) }}
                                                        </small>
                                                    @endif
                                                </div>
                                                <span class="badge bg-{{ $activity->getTypeBadgeClass() }}">
                                                    {{ ucfirst($activity->type) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-4">
                                        <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No activity recorded for this user.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sessions Tab -->
                <div class="tab-pane fade" id="sessions" role="tabpanel">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-desktop me-2"></i>Active Sessions</h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center py-4">
                                <i class="fas fa-desktop fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Session management feature coming soon.</p>
                                <small class="text-muted">This will allow viewing and managing active login sessions.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reset Password for {{ $user->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('users.reset-password', $user) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" class="form-control" name="password" id="password" required minlength="8">
                        <small class="text-muted">Minimum 8 characters</small>
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" required>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="must_change_password" value="1" id="mustChange">
                        <label class="form-check-label" for="mustChange">
                            User must change password on next login
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="send_email" value="1" id="sendEmail" checked>
                        <label class="form-check-label" for="sendEmail">
                            Send password reset notification via email
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-key me-2"></i>Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .nav-tabs .nav-link {
        padding: 0.5rem 0.75rem;
        color: #6c757d;
        border: none;
        border-bottom: 2px solid transparent;
    }
    
    .nav-tabs .nav-link.active {
        color: #2563eb;
        background: none;
        border-bottom: 2px solid #2563eb;
    }
    
    .nav-tabs .nav-link:hover {
        color: #2563eb;
        border-bottom: 2px solid #ddd;
    }
    
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    
    .timeline::before {
        content: '';
        position: absolute;
        left: 9px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }
    
    .timeline-item {
        position: relative;
        padding-bottom: 1.5rem;
    }
    
    .timeline-marker {
        position: absolute;
        left: -21px;
        top: 5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 2px solid #fff;
        box-shadow: 0 0 0 1px #dee2e6;
    }
    
    .card {
        border: none;
        border-radius: 10px;
    }
    
    .card-header {
        border-bottom: 1px solid #e9ecef;
        font-weight: 600;
    }
    
    label.text-muted {
        font-size: 0.875rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
</style>
@endpush

@push('scripts')
<script>
    function terminateSession(sessionId) {
        if (confirm('Are you sure you want to terminate this session?')) {
            // For now, show alert - implement when route is ready
            alert('This feature will be available soon.');
            
            // When ready, implement with: /users/{user}/sessions/{session}/terminate
        }
    }
    
    function terminateAllSessions() {
        if (confirm('Are you sure you want to terminate all other sessions? This will log the user out from all other devices.')) {
            // For now, show alert - implement when route is ready
            alert('This feature will be available soon.');
            
            // When ready, implement with: /users/{user}/sessions/terminate-all
        }
    }
</script>
@endpush