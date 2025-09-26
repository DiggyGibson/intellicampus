@extends('layouts.app')

@section('title', 'Manage Roles - ' . $user->name)

@section('breadcrumb')
    <a href="{{ route('users.index') }}">User Management</a>
    <i class="fas fa-chevron-right"></i>
    <a href="{{ route('users.show', $user) }}">{{ $user->name }}</a>
    <i class="fas fa-chevron-right"></i>
    <span>Manage Roles</span>
@endsection

@section('page-actions')
    <a href="{{ route('users.show', $user) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back to Profile
    </a>
@endsection

@section('content')
<div class="container-fluid px-4">
    <!-- User Info Card -->
    <div class="card shadow-sm mb-4">
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
                    <h4 class="mb-1">{{ $user->name }}</h4>
                    <p class="text-muted mb-0">{{ $user->email }}</p>
                    <span class="badge bg-info">{{ ucfirst($user->user_type ?? 'User') }}</span>
                    @if($user->status === 'active')
                        <span class="badge bg-success">Active</span>
                    @elseif($user->status === 'suspended')
                        <span class="badge bg-danger">Suspended</span>
                    @else
                        <span class="badge bg-secondary">{{ ucfirst($user->status) }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Current Roles Section -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary bg-gradient text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-shield me-2"></i>Current Roles
                        <span class="badge bg-white text-primary ms-2">{{ $userRoles->count() }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    @if($userRoles->count() > 0)
                        <div class="role-list">
                            @foreach($userRoles as $role)
                                <div class="role-item mb-3 p-3 border rounded hover-shadow">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-fill">
                                            <h6 class="mb-1 text-primary">
                                                <i class="fas fa-shield-alt me-1"></i>{{ $role->name }}
                                            </h6>
                                            @if($role->description)
                                                <p class="text-muted small mb-2">{{ $role->description }}</p>
                                            @endif
                                            <div class="role-meta">
                                                <span class="badge bg-light text-dark me-2">
                                                    <i class="fas fa-sort-amount-up me-1"></i>Priority: {{ $role->priority }}
                                                </span>
                                                <span class="badge bg-light text-dark me-2">
                                                    <i class="fas fa-key me-1"></i>{{ $role->permissions->count() }} permissions
                                                </span>
                                                @if($role->pivot && $role->pivot->assigned_at)
                                                    <span class="badge bg-light text-dark">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        {{ \Carbon\Carbon::parse($role->pivot->assigned_at)->format('M d, Y') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            @if($role->is_system)
                                                <span class="badge bg-warning text-dark">
                                                    <i class="fas fa-lock me-1"></i>System
                                                </span>
                                            @endif
                                            @if($role->pivot && $role->pivot->is_primary)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-star me-1"></i>Primary
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-user-shield fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No roles currently assigned to this user.</p>
                            <p class="text-muted small">Select roles from the available list to assign them.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Available Roles Section -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-success bg-gradient text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>Update Role Assignment
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('users.sync-roles', $user) }}" method="POST" id="rolesForm">
                        @csrf
                        
                        <!-- Quick Actions -->
                        <div class="mb-3">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAll()">
                                <i class="fas fa-check-square me-1"></i>Select All
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectNone()">
                                <i class="fas fa-square me-1"></i>Clear All
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-info" onclick="invertSelection()">
                                <i class="fas fa-exchange-alt me-1"></i>Invert
                            </button>
                        </div>
                        
                        <!-- Search Filter -->
                        <div class="mb-3">
                            <input type="text" 
                                   id="roleSearch" 
                                   class="form-control form-control-sm" 
                                   placeholder="Search roles...">
                        </div>
                        
                        <!-- Roles List -->
                        <div class="roles-container" style="max-height: 400px; overflow-y: auto;">
                            @foreach($allRoles as $role)
                                <div class="form-check role-option p-3 mb-2 border rounded hover-highlight">
                                    <input class="form-check-input role-checkbox" 
                                           type="checkbox" 
                                           name="roles[]" 
                                           value="{{ $role->id }}" 
                                           id="role_{{ $role->id }}"
                                           {{ $userRoles->contains('id', $role->id) ? 'checked' : '' }}>
                                    <label class="form-check-label w-100" for="role_{{ $role->id }}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <strong>{{ $role->name }}</strong>
                                                @if($role->description)
                                                    <br><small class="text-muted">{{ $role->description }}</small>
                                                @endif
                                                <div class="mt-1">
                                                    <span class="badge bg-light text-dark me-1">
                                                        Priority: {{ $role->priority }}
                                                    </span>
                                                    <span class="badge bg-light text-dark">
                                                        {{ $role->permissions->count() }} permissions
                                                    </span>
                                                </div>
                                            </div>
                                            @if($role->is_system)
                                                <span class="badge bg-warning text-dark">System</span>
                                            @endif
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        
                        @error('roles')
                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                        @enderror
                        
                        <div class="mt-4 d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Role Assignment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Permissions Preview -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="fas fa-key me-2"></i>Effective Permissions from Assigned Roles
            </h5>
        </div>
        <div class="card-body">
            @if($userRoles->count() > 0)
                @php
                    $allPermissions = collect();
                    foreach($userRoles as $role) {
                        $allPermissions = $allPermissions->merge($role->permissions);
                    }
                    $groupedPermissions = $allPermissions->unique('id')->groupBy('module');
                @endphp
                
                <div class="accordion" id="permissionsAccordion">
                    @foreach($groupedPermissions as $module => $permissions)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading{{ $loop->index }}">
                                <button class="accordion-button {{ !$loop->first ? 'collapsed' : '' }}" 
                                        type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#collapse{{ $loop->index }}" 
                                        aria-expanded="{{ $loop->first ? 'true' : 'false' }}">
                                    <i class="fas fa-folder me-2"></i>
                                    {{ ucfirst($module ?? 'General') }} Module
                                    <span class="badge bg-primary ms-2">{{ $permissions->count() }}</span>
                                </button>
                            </h2>
                            <div id="collapse{{ $loop->index }}" 
                                 class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" 
                                 data-bs-parent="#permissionsAccordion">
                                <div class="accordion-body">
                                    <div class="row g-2">
                                        @foreach($permissions as $permission)
                                            <div class="col-md-4 col-sm-6">
                                                <div class="permission-item p-2 bg-light rounded">
                                                    <i class="fas fa-check-circle text-success me-1"></i>
                                                    <span class="small">{{ $permission->display_name ?? $permission->name }}</span>
                                                    @if($permission->description)
                                                        <i class="fas fa-info-circle text-muted ms-1" 
                                                           data-bs-toggle="tooltip" 
                                                           title="{{ $permission->description }}"></i>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-lock fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No permissions available. Assign roles to grant permissions.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Important Notes -->
    <div class="alert alert-warning d-flex align-items-start">
        <i class="fas fa-exclamation-triangle me-3 mt-1"></i>
        <div>
            <h6 class="alert-heading">Important Notes</h6>
            <ul class="mb-0 small">
                <li>Changing roles will immediately affect the user's access to system features</li>
                <li>Users should have at least one role assigned for proper system access</li>
                <li>Super Administrator role grants full system access - assign with caution</li>
                <li>All role changes are logged for audit purposes</li>
                <li>Some roles may have dependencies on user type (e.g., Student role for student users)</li>
            </ul>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle me-2"></i>Roles Updated Successfully
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>The role assignment for <strong>{{ $user->name }}</strong> has been successfully updated.</p>
            </div>
            <div class="modal-footer">
                <a href="{{ route('users.show', $user) }}" class="btn btn-primary">
                    <i class="fas fa-user me-2"></i>View Profile
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .hover-shadow:hover {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transform: translateY(-1px);
        transition: all 0.2s;
    }
    
    .hover-highlight:hover {
        background-color: #f8f9fa;
        cursor: pointer;
    }
    
    .role-item {
        transition: all 0.2s;
        background-color: #f8f9fa;
    }
    
    .role-item:hover {
        background-color: #e9ecef;
    }
    
    .permission-item {
        font-size: 0.875rem;
        border: 1px solid #dee2e6;
    }
    
    .roles-container::-webkit-scrollbar {
        width: 8px;
    }
    
    .roles-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    .roles-container::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    
    .roles-container::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    
    .form-check-input:checked {
        background-color: #2563eb;
        border-color: #2563eb;
    }
    
    .accordion-button:not(.collapsed) {
        background-color: #f0f6ff;
        color: #2563eb;
    }
    
    .accordion-button:focus {
        box-shadow: none;
        border-color: #dee2e6;
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

    // Select all roles
    function selectAll() {
        document.querySelectorAll('.role-checkbox').forEach(checkbox => {
            checkbox.checked = true;
        });
    }

    // Clear all selections
    function selectNone() {
        document.querySelectorAll('.role-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
    }

    // Invert selection
    function invertSelection() {
        document.querySelectorAll('.role-checkbox').forEach(checkbox => {
            checkbox.checked = !checkbox.checked;
        });
    }

    // Role search functionality
    document.getElementById('roleSearch').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        document.querySelectorAll('.role-option').forEach(role => {
            const text = role.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                role.style.display = 'block';
            } else {
                role.style.display = 'none';
            }
        });
    });

    // Click on role item to check checkbox
    document.querySelectorAll('.role-option').forEach(item => {
        item.addEventListener('click', function(e) {
            if (e.target.type !== 'checkbox') {
                const checkbox = this.querySelector('.role-checkbox');
                checkbox.checked = !checkbox.checked;
            }
        });
    });

    // Form submission with confirmation
    document.getElementById('rolesForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const checkedRoles = document.querySelectorAll('.role-checkbox:checked').length;
        
        if (checkedRoles === 0) {
            if (!confirm('No roles selected. This will remove all roles from the user. Continue?')) {
                return false;
            }
        }
        
        // Show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';
        submitBtn.disabled = true;
        
        // Submit the form
        this.submit();
    });

    // Show success modal if there's a success message
    @if(session('success'))
        const successModal = new bootstrap.Modal(document.getElementById('successModal'));
        successModal.show();
    @endif
</script>
@endpush