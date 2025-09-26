@extends('layouts.app')

@section('title', 'User Management')

@section('breadcrumb')
    <span>User Management</span>
@endsection

@section('page-actions')
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New User
    </a>
    {{-- Import/Export button - uncomment when route is available --}}
    {{-- <a href="{{ route('admin.users.import-export') }}" class="btn btn-outline-secondary">
        <i class="fas fa-file-import"></i> Import/Export
    </a> --}}
    <a href="{{ route('admin.permissions.index') }}" class="btn btn-warning">
        <i class="fas fa-shield-alt"></i> Manage Permissions
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

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Users</h6>
                            <h3 class="mb-0">{{ number_format(\App\Models\User::count()) }}</h3>
                        </div>
                        <div class="stat-icon bg-primary bg-gradient">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Active Users</h6>
                            <h3 class="mb-0 text-success">{{ number_format(\App\Models\User::where('status', 'active')->count()) }}</h3>
                        </div>
                        <div class="stat-icon bg-success bg-gradient">
                            <i class="fas fa-user-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Suspended</h6>
                            <h3 class="mb-0 text-danger">{{ number_format(\App\Models\User::where('status', 'suspended')->count()) }}</h3>
                        </div>
                        <div class="stat-icon bg-danger bg-gradient">
                            <i class="fas fa-user-slash"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Pending</h6>
                            <h3 class="mb-0 text-warning">{{ number_format(\App\Models\User::where('status', 'pending')->count()) }}</h3>
                        </div>
                        <div class="stat-icon bg-warning bg-gradient">
                            <i class="fas fa-user-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Users</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.users.index') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   class="form-control" placeholder="Name, email, username...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">User Type</label>
                        <select name="user_type" class="form-select">
                            <option value="">All Types</option>
                            <option value="admin" {{ request('user_type') == 'admin' ? 'selected' : '' }}>
                                <i class="fas fa-user-shield"></i> Admin
                            </option>
                            <option value="faculty" {{ request('user_type') == 'faculty' ? 'selected' : '' }}>
                                <i class="fas fa-chalkboard-teacher"></i> Faculty
                            </option>
                            <option value="staff" {{ request('user_type') == 'staff' ? 'selected' : '' }}>
                                <i class="fas fa-user-tie"></i> Staff
                            </option>
                            <option value="student" {{ request('user_type') == 'student' ? 'selected' : '' }}>
                                <i class="fas fa-user-graduate"></i> Student
                            </option>
                            <option value="parent" {{ request('user_type') == 'parent' ? 'selected' : '' }}>
                                <i class="fas fa-user-friends"></i> Parent
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select">
                            <option value="">All Roles</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ request('role') == $role->id ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary" data-bs-toggle="tooltip" title="Apply Filters">
                                <i class="fas fa-search"></i>
                            </button>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary" data-bs-toggle="tooltip" title="Clear Filters">
                                <i class="fas fa-undo"></i>
                            </a>
                            @if($users->count() > 0)
                                <button type="button" class="btn btn-info" id="exportBtn" data-bs-toggle="tooltip" title="Export Users">
                                    <i class="fas fa-download"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">Users ({{ $users->total() }} total)</h5>
                </div>
                @if($users->count() > 0)
                <div class="col-auto">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="selectAll">
                        <label class="form-check-label" for="selectAll">
                            Select All
                        </label>
                    </div>
                </div>
                @endif
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th width="40">
                                <input type="checkbox" class="form-check-input" id="selectAllHeader" style="display:none;">
                            </th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Type</th>
                            <th>Roles</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input user-checkbox" 
                                           value="{{ $user->id }}" name="selected_users[]">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-3">
                                            @if($user->profile_photo)
                                                <img src="{{ Storage::url($user->profile_photo) }}" 
                                                     alt="{{ $user->name }}" 
                                                     class="rounded-circle" 
                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                            @else
                                                <div class="avatar-initials rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" 
                                                     style="width: 40px; height: 40px;">
                                                    {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $user->name }}</div>
                                            @if($user->username)
                                                <small class="text-muted">@{{ $user->username }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                                </td>
                                <td>
                                    @if($user->user_type == 'admin')
                                        <span class="badge bg-purple"><i class="fas fa-user-shield me-1"></i>Admin</span>
                                    @elseif($user->user_type == 'faculty')
                                        <span class="badge bg-primary"><i class="fas fa-chalkboard-teacher me-1"></i>Faculty</span>
                                    @elseif($user->user_type == 'staff')
                                        <span class="badge bg-info"><i class="fas fa-user-tie me-1"></i>Staff</span>
                                    @elseif($user->user_type == 'student')
                                        <span class="badge bg-success"><i class="fas fa-user-graduate me-1"></i>Student</span>
                                    @elseif($user->user_type == 'parent')
                                        <span class="badge bg-secondary"><i class="fas fa-user-friends me-1"></i>Parent</span>
                                    @else
                                        <span class="badge bg-light text-dark">{{ ucfirst($user->user_type ?? 'Unknown') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @forelse($user->roles as $role)
                                        <span class="badge bg-indigo me-1">{{ $role->name }}</span>
                                    @empty
                                        <span class="text-muted">No roles</span>
                                    @endforelse
                                </td>
                                <td>
                                    @if($user->status == 'active')
                                        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Active</span>
                                    @elseif($user->status == 'inactive')
                                        <span class="badge bg-secondary"><i class="fas fa-minus-circle me-1"></i>Inactive</span>
                                    @elseif($user->status == 'suspended')
                                        <span class="badge bg-danger"><i class="fas fa-ban me-1"></i>Suspended</span>
                                    @elseif($user->status == 'pending')
                                        <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Pending</span>
                                    @else
                                        <span class="badge bg-light text-dark">{{ ucfirst($user->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->last_login_at)
                                        <small class="text-muted">{{ $user->last_login_at->diffForHumans() }}</small>
                                    @else
                                        <small class="text-muted">Never</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('admin.users.show', $user) }}" 
                                           class="btn btn-outline-info" 
                                           data-bs-toggle="tooltip" 
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.users.edit', $user) }}" 
                                           class="btn btn-outline-success" 
                                           data-bs-toggle="tooltip" 
                                           title="Edit User">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($user->id !== auth()->id() && !$user->isSuperAdmin())
                                            <form action="{{ route('admin.users.toggle-status', $user) }}"
                                                method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit"
                                                        class="btn btn-outline-warning btn-sm"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ $user->is_active ? 'Deactivate' : 'Activate' }} User">
                                                    <i class="fas fa-power-off"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>No users found matching your criteria.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($users->hasPages())
            <div class="card-footer bg-white">
                <!-- Using custom pagination view -->
                {{ $users->appends(request()->query())->links('custom.pagination') }}
            </div>
        @endif
    </div>

    <!-- Bulk Actions (Hidden by default) -->
    <div class="position-fixed bottom-0 start-50 translate-middle-x mb-3" 
         id="bulkActions" 
         style="display: none; z-index: 1050;">
        <div class="card shadow-lg">
            <div class="card-body py-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary" id="selectedCount">0</span>
                    <span>users selected</span>
                    <div class="vr mx-2"></div>
                    <button type="button" class="btn btn-sm btn-success" onclick="bulkAction('activate')">
                        <i class="fas fa-check"></i> Activate
                    </button>
                    <button type="button" class="btn btn-sm btn-warning" onclick="bulkAction('suspend')">
                        <i class="fas fa-ban"></i> Suspend
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="bulkAction('delete')">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="clearSelection()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Export Modal - Uncomment when export route is ready -->
{{-- 
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Export Users</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.users.export') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Export Format</label>
                        <select name="format" class="form-select" required>
                            <option value="csv">CSV (.csv)</option>
                            <option value="excel">Excel (.xlsx)</option>
                            <option value="pdf">PDF (.pdf)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Include Fields</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="fields[]" value="basic" id="basicFields" checked>
                            <label class="form-check-label" for="basicFields">
                                Basic Information (Name, Email, Username)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="fields[]" value="contact" id="contactFields">
                            <label class="form-check-label" for="contactFields">
                                Contact Information (Phone, Address)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="fields[]" value="roles" id="rolesFields">
                            <label class="form-check-label" for="rolesFields">
                                Roles & Permissions
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="fields[]" value="activity" id="activityFields">
                            <label class="form-check-label" for="activityFields">
                                Activity Information (Last Login, Created Date)
                            </label>
                        </div>
                    </div>
                    <!-- Include current filters -->
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="user_type" value="{{ request('user_type') }}">
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    <input type="hidden" name="role" value="{{ request('role') }}">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-download me-1"></i> Export
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
--}}

@endsection

@push('styles')
<style>
    .stat-card {
        border: none;
        border-radius: 10px;
        transition: transform 0.2s;
    }
    
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
    }
    
    .avatar-initials {
        font-weight: 600;
        font-size: 1rem;
    }
    
    .bg-purple {
        background-color: #6f42c1 !important;
    }
    
    .bg-indigo {
        background-color: #6610f2 !important;
    }
    
    .table > :not(caption) > * > * {
        padding: 0.75rem 0.75rem;
    }
    
    .badge {
        padding: 0.35em 0.65em;
        font-weight: 500;
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

    // Select all functionality
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.user-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActions();
    });

    // Update bulk actions visibility
    function updateBulkActions() {
        const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
        const bulkActionsDiv = document.getElementById('bulkActions');
        const selectedCount = document.getElementById('selectedCount');
        
        if (checkedBoxes.length > 0) {
            bulkActionsDiv.style.display = 'block';
            selectedCount.textContent = checkedBoxes.length;
        } else {
            bulkActionsDiv.style.display = 'none';
        }
    }

    // Individual checkbox change
    document.querySelectorAll('.user-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });

    // Clear selection
    function clearSelection() {
        document.querySelectorAll('.user-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        document.getElementById('selectAll').checked = false;
        updateBulkActions();
    }

    // Bulk actions
    function bulkAction(action) {
        const selectedIds = Array.from(document.querySelectorAll('.user-checkbox:checked'))
            .map(checkbox => checkbox.value);
        
        if (selectedIds.length === 0) return;
        
        let message = '';
        switch(action) {
            case 'activate':
                message = `Are you sure you want to activate ${selectedIds.length} user(s)?`;
                break;
            case 'suspend':
                message = `Are you sure you want to suspend ${selectedIds.length} user(s)?`;
                break;
            case 'delete':
                message = `Are you sure you want to delete ${selectedIds.length} user(s)? This action cannot be undone.`;
                break;
        }
        
        if (confirm(message)) {
            // For now, just show alert - implement when route is ready
            alert('Bulk action feature will be available soon.');
            
            // Uncomment below when bulk-action route is ready
            /* 
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("admin.users.bulk-action") }}';
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = action;
            form.appendChild(actionInput);
            
            selectedIds.forEach(id => {
                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'user_ids[]';
                idInput.value = id;
                form.appendChild(idInput);
            });
            
            document.body.appendChild(form);
            form.submit();
            */
        }
    }

    // Export button
    document.getElementById('exportBtn')?.addEventListener('click', function() {
        // For now, just export current filtered results as CSV
        window.location.href = '{{ route("admin.users.index") }}?export=csv&' + new URLSearchParams(new FormData(document.getElementById('filterForm'))).toString();
        
        // Uncomment below when export modal route is ready
        // const modal = new bootstrap.Modal(document.getElementById('exportModal'));
        // modal.show();
    });
</script>
@endpush