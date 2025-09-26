{{-- 
    File: resources/views/admin/permissions/matrix.blade.php
    Permission Matrix Management Interface
--}}
@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- Header Section --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Permission Matrix</h1>
            <p class="text-muted">Manage role permissions across all system modules</p>
        </div>
        <div>
            <button class="btn btn-primary" onclick="saveAllChanges()">
                <i class="fas fa-save me-2"></i>Save All Changes
            </button>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Roles</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $roles->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-shield fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Permissions</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $permissions->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-key fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">System Roles</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $roles->where('is_system', true)->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-lock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Modules</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $permissions->pluck('module')->unique()->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-folder fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Controls --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Options</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <label for="moduleFilter">Filter by Module:</label>
                    <select id="moduleFilter" class="form-control" onchange="filterByModule()">
                        <option value="">All Modules</option>
                        @foreach($permissions->pluck('module')->unique()->sort() as $module)
                            <option value="{{ $module }}">{{ ucfirst($module) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="roleFilter">Filter by Role:</label>
                    <select id="roleFilter" class="form-control" onchange="filterByRole()">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="searchPermission">Search Permission:</label>
                    <input type="text" id="searchPermission" class="form-control" placeholder="Type to search..." onkeyup="searchPermissions()">
                </div>
            </div>
        </div>
    </div>

    {{-- Permission Matrix Table --}}
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Permission Assignment Matrix</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-sm" id="permissionMatrix">
                    <thead>
                        <tr>
                            <th style="width: 250px; position: sticky; left: 0; background: white; z-index: 10;">
                                Module / Permission
                            </th>
                            @foreach($roles as $role)
                                <th class="text-center role-column role-{{ $role->id }}" style="min-width: 120px;">
                                    <div class="role-header">
                                        <strong>{{ $role->name }}</strong>
                                        @if($role->is_system)
                                            <span class="badge badge-warning">System</span>
                                        @endif
                                        <br>
                                        <small class="text-muted">{{ $role->users->count() }} users</small>
                                        <br>
                                        <button class="btn btn-xs btn-link" onclick="toggleRolePermissions({{ $role->id }})">
                                            Toggle All
                                        </button>
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $groupedPermissions = $permissions->groupBy('module');
                        @endphp
                        
                        @foreach($groupedPermissions as $module => $modulePermissions)
                            {{-- Module Header Row --}}
                            <tr class="module-header bg-light">
                                <td colspan="{{ $roles->count() + 1 }}">
                                    <strong>
                                        <i class="fas fa-folder me-2"></i>
                                        {{ ucfirst($module ?? 'General') }} Module
                                        <span class="badge badge-secondary ms-2">{{ $modulePermissions->count() }} permissions</span>
                                    </strong>
                                </td>
                            </tr>
                            
                            {{-- Permission Rows --}}
                            @foreach($modulePermissions as $permission)
                                <tr class="permission-row" data-module="{{ $module }}" data-permission="{{ $permission->slug }}">
                                    <td style="position: sticky; left: 0; background: white;">
                                        <div class="permission-info">
                                            <strong>{{ $permission->display_name ?? $permission->name }}</strong>
                                            @if($permission->description)
                                                <br><small class="text-muted">{{ $permission->description }}</small>
                                            @endif
                                            <br><code class="small">{{ $permission->slug }}</code>
                                        </div>
                                    </td>
                                    
                                    @foreach($roles as $role)
                                        <td class="text-center role-column role-{{ $role->id }}">
                                            @php
                                                $hasPermission = $role->permissions->contains('id', $permission->id);
                                            @endphp
                                            
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" 
                                                       class="custom-control-input permission-checkbox" 
                                                       id="perm_{{ $role->id }}_{{ $permission->id }}"
                                                       data-role-id="{{ $role->id }}"
                                                       data-permission-id="{{ $permission->id }}"
                                                       {{ $hasPermission ? 'checked' : '' }}
                                                       {{ $role->slug === 'super-administrator' ? 'disabled' : '' }}
                                                       onchange="togglePermission({{ $role->id }}, {{ $permission->id }}, this)">
                                                <label class="custom-control-label" for="perm_{{ $role->id }}_{{ $permission->id }}">
                                                    @if($role->slug === 'super-administrator')
                                                        <span class="text-muted" title="Super Admin has all permissions">
                                                            <i class="fas fa-lock"></i>
                                                        </span>
                                                    @endif
                                                </label>
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Floating Action Button for Quick Actions --}}
<div class="position-fixed" style="bottom: 20px; right: 20px;">
    <div class="btn-group-vertical">
        <button class="btn btn-success btn-circle btn-lg mb-2" onclick="saveAllChanges()" title="Save Changes">
            <i class="fas fa-save"></i>
        </button>
        <button class="btn btn-info btn-circle btn-lg mb-2" onclick="exportMatrix()" title="Export Matrix">
            <i class="fas fa-download"></i>
        </button>
        <button class="btn btn-warning btn-circle btn-lg" onclick="resetChanges()" title="Reset Changes">
            <i class="fas fa-undo"></i>
        </button>
    </div>
</div>

@endsection

@section('styles')
<style>
    .permission-checkbox {
        width: 20px;
        height: 20px;
        cursor: pointer;
    }
    
    .permission-checkbox:disabled {
        cursor: not-allowed;
        opacity: 0.5;
    }
    
    .module-header {
        font-weight: bold;
        background-color: #f8f9fa;
    }
    
    .role-header {
        writing-mode: vertical-lr;
        text-orientation: mixed;
        height: 100px;
    }
    
    .permission-row:hover {
        background-color: #f1f3f4;
    }
    
    .permission-info {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 250px;
    }
    
    .btn-circle {
        width: 60px;
        height: 60px;
        padding: 10px 16px;
        border-radius: 50%;
        font-size: 24px;
        line-height: 1.33;
    }
    
    .custom-control-checkbox .custom-control-input:checked ~ .custom-control-label::before {
        background-color: #28a745;
        border-color: #28a745;
    }
    
    .border-left-primary {
        border-left: 4px solid #4e73df;
    }
    
    .border-left-success {
        border-left: 4px solid #1cc88a;
    }
    
    .border-left-info {
        border-left: 4px solid #36b9cc;
    }
    
    .border-left-warning {
        border-left: 4px solid #f6c23e;
    }
</style>
@endsection

@section('scripts')
<script>
    // Track changes for bulk save
    let pendingChanges = [];
    
    // Toggle individual permission
    function togglePermission(roleId, permissionId, checkbox) {
        const change = {
            role_id: roleId,
            permission_id: permissionId,
            action: checkbox.checked ? 'grant' : 'revoke'
        };
        
        // Add to pending changes
        const existingIndex = pendingChanges.findIndex(
            c => c.role_id === roleId && c.permission_id === permissionId
        );
        
        if (existingIndex > -1) {
            pendingChanges[existingIndex] = change;
        } else {
            pendingChanges.push(change);
        }
        
        // Visual feedback
        $(checkbox).closest('td').addClass('table-warning');
        
        // Update UI to show pending changes
        updatePendingChangesCount();
    }
    
    // Toggle all permissions for a role
    function toggleRolePermissions(roleId) {
        const checkboxes = document.querySelectorAll(`.permission-checkbox[data-role-id="${roleId}"]:not(:disabled)`);
        const checkedCount = document.querySelectorAll(`.permission-checkbox[data-role-id="${roleId}"]:checked`).length;
        const shouldCheck = checkedCount < checkboxes.length / 2;
        
        checkboxes.forEach(checkbox => {
            if (checkbox.checked !== shouldCheck) {
                checkbox.checked = shouldCheck;
                togglePermission(roleId, checkbox.dataset.permissionId, checkbox);
            }
        });
    }
    
    // Save all pending changes
    function saveAllChanges() {
        if (pendingChanges.length === 0) {
            Swal.fire('No Changes', 'No permissions have been modified', 'info');
            return;
        }
        
        Swal.fire({
            title: 'Save Changes?',
            text: `You have ${pendingChanges.length} pending changes. Save them now?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, save changes!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Saving...',
                    text: 'Please wait while we save your changes',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Send AJAX request
                $.ajax({
                    url: '{{ route("admin.permissions.bulk-update") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        changes: pendingChanges
                    },
                    success: function(response) {
                        Swal.fire(
                            'Saved!',
                            'All permission changes have been saved.',
                            'success'
                        );
                        
                        // Clear pending changes
                        pendingChanges = [];
                        $('.table-warning').removeClass('table-warning');
                        updatePendingChangesCount();
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Error!',
                            'Failed to save changes: ' + xhr.responseJSON.message,
                            'error'
                        );
                    }
                });
            }
        });
    }
    
    // Reset all pending changes
    function resetChanges() {
        if (pendingChanges.length === 0) {
            Swal.fire('No Changes', 'No changes to reset', 'info');
            return;
        }
        
        Swal.fire({
            title: 'Reset Changes?',
            text: 'This will discard all unsaved changes',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, reset!'
        }).then((result) => {
            if (result.isConfirmed) {
                location.reload();
            }
        });
    }
    
    // Filter by module
    function filterByModule() {
        const selectedModule = document.getElementById('moduleFilter').value;
        const rows = document.querySelectorAll('.permission-row');
        
        rows.forEach(row => {
            if (selectedModule === '' || row.dataset.module === selectedModule) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
        
        // Show/hide module headers
        const headers = document.querySelectorAll('.module-header');
        headers.forEach(header => {
            const module = header.nextElementSibling?.dataset.module;
            if (selectedModule === '' || module === selectedModule) {
                header.style.display = '';
            } else {
                header.style.display = 'none';
            }
        });
    }
    
    // Filter by role
    function filterByRole() {
        const selectedRole = document.getElementById('roleFilter').value;
        const columns = document.querySelectorAll('.role-column');
        
        columns.forEach(column => {
            if (selectedRole === '' || column.classList.contains('role-' + selectedRole)) {
                column.style.display = '';
            } else {
                column.style.display = 'none';
            }
        });
    }
    
    // Search permissions
    function searchPermissions() {
        const searchTerm = document.getElementById('searchPermission').value.toLowerCase();
        const rows = document.querySelectorAll('.permission-row');
        
        rows.forEach(row => {
            const permissionText = row.querySelector('.permission-info').textContent.toLowerCase();
            if (permissionText.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
    
    // Update pending changes count
    function updatePendingChangesCount() {
        const count = pendingChanges.length;
        if (count > 0) {
            $('#pendingCount').text(`(${count} pending)`).show();
        } else {
            $('#pendingCount').hide();
        }
    }
    
    // Export matrix to CSV
    function exportMatrix() {
        window.location.href = '{{ route("admin.permissions.export") }}';
    }
    
    // Initialize tooltips
    $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip();
        
        // Add pending changes indicator
        $('.card-header:first').append('<span id="pendingCount" class="badge badge-warning ml-2" style="display:none;"></span>');
    });
    
    // Warn before leaving with unsaved changes
    window.addEventListener('beforeunload', function(e) {
        if (pendingChanges.length > 0) {
            e.preventDefault();
            e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
        }
    });
</script>
@endsection