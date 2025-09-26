{{-- File: C:\IntelliCampus\development\backend\resources\views\admin\permissions\index.blade.php --}}
@extends('layouts.app')

@section('title', 'Permission Management')

@section('breadcrumb')
    <a href="{{ route('users.index') }}">User Management</a>
    <i class="fas fa-chevron-right"></i>
    <span>Permission Management</span>
@endsection

@section('page-actions')
    <button type="button" class="btn btn-warning" onclick="runHealthCheck()">
        <i class="fas fa-heartbeat"></i> Health Check
    </button>
    <a href="{{ route('admin.permissions.export') }}" class="btn btn-secondary">
        <i class="fas fa-download"></i> Export Config
    </a>
    <button type="button" class="btn btn-success" onclick="syncAllPermissions()">
        <i class="fas fa-sync"></i> Sync All
    </button>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPermissionModal">
        <i class="fas fa-plus"></i> Add Permission
    </button>
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
        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Permissions</h6>
                            <h3 class="mb-0 text-primary">{{ $stats['total_permissions'] ?? 0 }}</h3>
                            <small class="text-muted">{{ $stats['system_permissions'] ?? 0 }} system</small>
                        </div>
                        <div class="stat-icon bg-primary bg-gradient">
                            <i class="fas fa-key"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Roles</h6>
                            <h3 class="mb-0 text-success">{{ $stats['total_roles'] ?? 0 }}</h3>
                        </div>
                        <div class="stat-icon bg-success bg-gradient">
                            <i class="fas fa-user-shield"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Modules</h6>
                            <h3 class="mb-0 text-info">{{ $stats['modules_count'] ?? 0 }}</h3>
                        </div>
                        <div class="stat-icon bg-info bg-gradient">
                            <i class="fas fa-cube"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Unassigned</h6>
                            <h3 class="mb-0 text-warning">{{ $stats['unassigned_permissions'] ?? 0 }}</h3>
                        </div>
                        <div class="stat-icon bg-warning bg-gradient">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Users</h6>
                            <h3 class="mb-0 text-purple">{{ $stats['total_users'] ?? 0 }}</h3>
                        </div>
                        <div class="stat-icon bg-purple bg-gradient">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Custom</h6>
                            <h3 class="mb-0 text-secondary">{{ $stats['custom_permissions'] ?? 0 }}</h3>
                        </div>
                        <div class="stat-icon bg-secondary bg-gradient">
                            <i class="fas fa-cog"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Missing Permissions Alert -->
    @if(isset($missingPermissions) && count($missingPermissions) > 0)
    <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
        <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Missing Permissions Detected</h5>
        <p>The following modules have missing permissions that should be installed:</p>
        <div class="d-flex flex-wrap gap-2">
            @foreach($missingPermissions as $module => $permissions)
            <button onclick="installModule('{{ $module }}')" class="btn btn-sm btn-warning">
                Install {{ ucfirst($module) }} ({{ count($permissions) }} permissions)
            </button>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Module Status Grid -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-cube me-2"></i>System Modules</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @if(isset($moduleConfig))
                    @foreach($moduleConfig as $moduleKey => $module)
                    @php
                        $installedCount = isset($permissionsByModule[$moduleKey]) ? $permissionsByModule[$moduleKey]['count'] : 0;
                        $totalCount = count($module['permissions']);
                        $percentage = $totalCount > 0 ? ($installedCount / $totalCount * 100) : 0;
                        $isComplete = $installedCount === $totalCount;
                    @endphp
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="card h-100 {{ $isComplete ? 'border-success' : 'border-secondary' }}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="module-icon text-primary">
                                        <i class="{{ $module['icon'] }} fa-2x"></i>
                                    </div>
                                    <div>
                                        @if($isComplete)
                                        <span class="badge bg-success"><i class="fas fa-check"></i></span>
                                        @elseif($installedCount > 0)
                                        <span class="badge bg-warning"><i class="fas fa-exclamation"></i></span>
                                        @else
                                        <span class="badge bg-secondary"><i class="fas fa-times"></i></span>
                                        @endif
                                    </div>
                                </div>
                                <h6 class="card-title">{{ $module['name'] }}</h6>
                                <p class="card-text small text-muted">{{ $installedCount }}/{{ $totalCount }} permissions</p>
                                <div class="progress mb-2" style="height: 5px;">
                                    <div class="progress-bar {{ $isComplete ? 'bg-success' : 'bg-primary' }}" 
                                         style="width: {{ $percentage }}%"></div>
                                </div>
                                @if(!$isComplete)
                                <button onclick="installModule('{{ $moduleKey }}')" 
                                        class="btn btn-sm btn-outline-primary w-100">
                                    <i class="fas fa-download"></i> Install
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    <!-- Main Content Tabs -->
    <div class="card shadow-sm">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#permissions-tab">
                        <i class="fas fa-key me-2"></i>Permissions
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#roles-tab">
                        <i class="fas fa-user-shield me-2"></i>Roles
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#matrix-tab">
                        <i class="fas fa-table me-2"></i>Permission Matrix
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                <!-- Permissions Tab -->
                <div class="tab-pane fade show active" id="permissions-tab">
                    <div class="mb-3">
                        <input type="text" id="permissionSearch" class="form-control" 
                               placeholder="Search permissions..." onkeyup="filterPermissions()">
                    </div>
                    
                    @if(isset($permissionsByModule))
                        @foreach($permissionsByModule as $moduleData)
                        <div class="permission-module mb-4">
                            <h5 class="text-primary mb-3">
                                @if(isset($moduleData['config']) && $moduleData['config'])
                                <i class="{{ $moduleData['config']['icon'] }} me-2"></i>
                                @endif
                                {{ isset($moduleData['config']['name']) ? $moduleData['config']['name'] : ucfirst($moduleData['module'] ?? 'General') }}
                                <span class="badge bg-secondary ms-2">{{ $moduleData['count'] }}</span>
                            </h5>
                            
                            <div class="row g-3">
                                @foreach($moduleData['permissions'] as $permission)
                                <div class="col-lg-4 col-md-6 permission-item" data-permission="{{ $permission->name }}">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="card-subtitle mb-2 text-primary">{{ $permission->display_name }}</h6>
                                                    <p class="card-text small text-monospace text-muted">{{ $permission->name }}</p>
                                                    @if($permission->description)
                                                    <p class="card-text small">{{ $permission->description }}</p>
                                                    @endif
                                                    <div class="mt-2">
                                                        <span class="badge bg-light text-dark">
                                                            <i class="fas fa-users"></i> {{ $permission->roles->count() }} roles
                                                        </span>
                                                        @if($permission->is_system)
                                                        <span class="badge bg-info">System</span>
                                                        @else
                                                        <span class="badge bg-secondary">Custom</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="#" onclick="editPermission({{ $permission->id }})">
                                                            <i class="fas fa-edit me-2"></i>Edit</a></li>
                                                        @if(!$permission->is_system)
                                                        <li><a class="dropdown-item text-danger" href="#" onclick="deletePermission({{ $permission->id }})">
                                                            <i class="fas fa-trash me-2"></i>Delete</a></li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    @endif
                </div>

                <!-- Roles Tab -->
                <div class="tab-pane fade" id="roles-tab">
                    <div class="row g-3">
                        @if(isset($roles))
                            @foreach($roles as $role)
                            <div class="col-lg-6">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h5 class="card-title">
                                                    {{ $role->name }}
                                                    @if($role->is_system)
                                                    <span class="badge bg-info ms-2">System</span>
                                                    @endif
                                                </h5>
                                                <p class="card-text text-muted">{{ $role->description }}</p>
                                            </div>
                                            <div class="text-center">
                                                <h3 class="text-primary">{{ $role->permissions_count }}</h3>
                                                <small class="text-muted">permissions</small>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between small mb-1">
                                                <span>Permission Coverage</span>
                                                <span>{{ $role->permission_coverage ?? 0 }}%</span>
                                            </div>
                                            <div class="progress" style="height: 10px;">
                                                <div class="progress-bar bg-primary" style="width: {{ $role->permission_coverage ?? 0 }}%"></div>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-muted">
                                                <i class="fas fa-users me-1"></i>{{ $role->users_count ?? 0 }} users
                                            </span>
                                            <a href="{{ route('users.index', ['role' => $role->slug]) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-users me-1"></i>View Users
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                <!-- Matrix Tab -->
                <div class="tab-pane fade" id="matrix-tab">
                    <div class="mb-3">
                        <select id="matrixModuleFilter" class="form-select" onchange="filterMatrix()">
                            <option value="">All Modules</option>
                            @if(isset($moduleConfig))
                                @foreach($moduleConfig as $key => $module)
                                <option value="{{ $key }}">{{ $module['name'] }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Permission</th>
                                    @if(isset($roles))
                                        @foreach($roles as $role)
                                        <th class="text-center" style="min-width: 100px;">{{ $role->name }}</th>
                                        @endforeach
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($permissionsByModule))
                                    @foreach($permissionsByModule as $moduleData)
                                        @foreach($moduleData['permissions'] as $permission)
                                        <tr class="matrix-row" data-module="{{ $permission->module }}">
                                            <td class="small">{{ $permission->name }}</td>
                                            @if(isset($roles))
                                                @foreach($roles as $role)
                                                <td class="text-center">
                                                    <input type="checkbox" class="form-check-input" 
                                                        {{ $role->permissions->contains($permission) ? 'checked' : '' }}
                                                        onchange="togglePermission({{ $role->id }}, {{ $permission->id }}, this.checked)">
                                                </td>
                                                @endforeach
                                            @endif
                                        </tr>
                                        @endforeach
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Permission Modal -->
<div class="modal fade" id="createPermissionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.permissions.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Create Custom Permission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Module</label>
                        <select name="module" class="form-select" required>
                            @if(isset($moduleConfig))
                                @foreach($moduleConfig as $key => $module)
                                <option value="{{ $key }}">{{ $module['name'] }}</option>
                                @endforeach
                            @endif
                            <option value="custom">Custom Module</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Permission Name</label>
                        <input type="text" name="name" class="form-control" required 
                               pattern="^[a-z]+\.[a-z_]+$" placeholder="module.action_name">
                        <small class="form-text text-muted">Format: module.action (lowercase, underscore for spaces)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Display Name</label>
                        <input type="text" name="display_name" class="form-control" required 
                               placeholder="Human Readable Name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2" 
                                  placeholder="What does this permission allow?"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Create Permission
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
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
    
    .bg-purple {
        background-color: #6f42c1 !important;
    }
    
    .text-monospace {
        font-family: 'Courier New', monospace;
    }
    
    .module-icon {
        opacity: 0.8;
    }
    
    .permission-item {
        transition: all 0.3s;
    }
    
    .permission-item.d-none {
        display: none !important;
    }
</style>
@endpush

@push('scripts')
<script>
    // Filter permissions
    function filterPermissions() {
        const searchTerm = document.getElementById('permissionSearch').value.toLowerCase();
        document.querySelectorAll('.permission-item').forEach(item => {
            const permName = item.dataset.permission.toLowerCase();
            const display = item.querySelector('.text-primary').textContent.toLowerCase();
            if (permName.includes(searchTerm) || display.includes(searchTerm)) {
                item.classList.remove('d-none');
            } else {
                item.classList.add('d-none');
            }
        });
    }

    // Filter matrix
    function filterMatrix() {
        const module = document.getElementById('matrixModuleFilter').value;
        document.querySelectorAll('.matrix-row').forEach(row => {
            if (!module || row.dataset.module === module) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Install module
    function installModule(module) {
        if (confirm(`Install all permissions for ${module} module?`)) {
            window.location.href = `/admin/permissions/install-module/${module}`;
        }
    }

    // Sync all permissions
    function syncAllPermissions() {
        if (confirm('This will sync ALL system permissions. Continue?')) {
            window.location.href = '{{ route("admin.permissions.sync-all") }}';
        }
    }

    // Toggle permission for role
    function togglePermission(roleId, permissionId, checked) {
        fetch(`/admin/roles/${roleId}/permissions/${permissionId}`, {
            method: checked ? 'POST' : 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        }).then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert(data.message || 'Error updating permission');
                event.target.checked = !checked;
            }
        });
    }

    // Delete permission
    function deletePermission(id) {
        if (confirm('Are you sure you want to delete this permission?')) {
            fetch(`/admin/permissions/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }).then(() => location.reload());
        }
    }

    // Edit permission (placeholder)
    function editPermission(id) {
        alert('Edit permission functionality to be implemented');
    }

    // Health check
    function runHealthCheck() {
        fetch('{{ route("admin.permissions.health-check") }}')
            .then(response => response.json())
            .then(data => {
                let message = data.healthy ? '✅ System is healthy\n\n' : '❌ System has issues\n\n';
                if (data.issues) {
                    data.issues.forEach(issue => {
                        message += `${issue.type.toUpperCase()}: ${issue.message}\n`;
                    });
                }
                alert(message);
            });
    }
</script>
@endpush