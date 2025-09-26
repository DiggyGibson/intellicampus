{{-- File: resources/views/admin/system-config/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- Header Section --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">System Configuration</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">System Configuration</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('system.health') }}" class="btn btn-outline-primary">
                        <i class="fas fa-heartbeat"></i> Health Check
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Stats Cards --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-left-primary h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Institution</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $institution->institution_name ?? 'Not Configured' }}
                            </div>
                            <small class="text-muted">{{ $institution->institution_code ?? 'N/A' }}</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-university fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-left-success h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Calendar</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $activeCalendar->name ?? 'Not Set' }}
                            </div>
                            <small class="text-muted">
                                @if($activeCalendar)
                                    Academic Year {{ $activeCalendar->academic_year }}
                                @else
                                    Configure calendar
                                @endif
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-left-info h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Current Term</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $currentTerm->name ?? 'Not Set' }}
                            </div>
                            <small class="text-muted">
                                @if($currentTerm)
                                    {{ $currentTerm->code }}
                                @else
                                    Set current term
                                @endif
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-left-warning h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Active Modules</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $modules->where('is_enabled', true)->count() }} / {{ $modules->count() }}
                            </div>
                            <small class="text-muted">Modules enabled</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-puzzle-piece fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- System Health Alert (if passed from controller) --}}
    @if(isset($systemHealth) && $systemHealth < 100)
    <div class="alert alert-warning" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        System Health: {{ $systemHealth }}% - Some components may need attention
    </div>
    @endif

    {{-- System Alerts (if passed from controller) --}}
    @if(isset($alerts) && count($alerts) > 0)
    <div class="row mb-4">
        <div class="col-12">
            @foreach($alerts as $alert)
            <div class="alert alert-{{ $alert['type'] }} alert-dismissible fade show" role="alert">
                {{ $alert['message'] }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Configuration Sections --}}
    <div class="row">
        {{-- Core Settings Column --}}
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-cogs"></i> Core Settings</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('system.institution.index') }}" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <div>
                                    <i class="fas fa-university text-primary me-2"></i>
                                    <strong>Institution Settings</strong>
                                    <p class="mb-0 small text-muted">Configure institution details and branding</p>
                                </div>
                                <i class="fas fa-chevron-right align-self-center"></i>
                            </div>
                        </a>

                        <a href="{{ route('system.settings.index') }}" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <div>
                                    <i class="fas fa-sliders-h text-info me-2"></i>
                                    <strong>System Settings</strong>
                                    <p class="mb-0 small text-muted">General system configuration and preferences</p>
                                </div>
                                <i class="fas fa-chevron-right align-self-center"></i>
                            </div>
                        </a>

                        <a href="{{ route('system.modules.index') }}" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <div>
                                    <i class="fas fa-puzzle-piece text-warning me-2"></i>
                                    <strong>Module Management</strong>
                                    <p class="mb-0 small text-muted">Enable or disable system modules</p>
                                </div>
                                <i class="fas fa-chevron-right align-self-center"></i>
                            </div>
                        </a>

                        <a href="{{ route('system.email.index') }}" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <div>
                                    <i class="fas fa-envelope text-success me-2"></i>
                                    <strong>Email Templates</strong>
                                    <p class="mb-0 small text-muted">Manage system email templates</p>
                                </div>
                                <i class="fas fa-chevron-right align-self-center"></i>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Academic Settings Column --}}
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-graduation-cap"></i> Academic Settings</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('system.calendar.index') }}" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <div>
                                    <i class="fas fa-calendar-alt text-primary me-2"></i>
                                    <strong>Academic Calendar</strong>
                                    <p class="mb-0 small text-muted">Manage academic years and calendar events</p>
                                </div>
                                <i class="fas fa-chevron-right align-self-center"></i>
                            </div>
                        </a>

                        <a href="{{ route('system.academic.index') }}" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <div>
                                    <i class="fas fa-book text-info me-2"></i>
                                    <strong>Academic Configuration</strong>
                                    <p class="mb-0 small text-muted">Credit system, grading, and attendance settings</p>
                                </div>
                                <i class="fas fa-chevron-right align-self-center"></i>
                            </div>
                        </a>

                        <a href="#" class="list-group-item list-group-item-action disabled">
                            <div class="d-flex w-100 justify-content-between">
                                <div>
                                    <i class="fas fa-chalkboard-teacher text-secondary me-2"></i>
                                    <strong>Period Types</strong>
                                    <p class="mb-0 small text-muted">Configure semester, quarter, or custom periods</p>
                                </div>
                                <i class="fas fa-chevron-right align-self-center"></i>
                            </div>
                        </a>

                        <a href="#" class="list-group-item list-group-item-action disabled">
                            <div class="d-flex w-100 justify-content-between">
                                <div>
                                    <i class="fas fa-clipboard-check text-secondary me-2"></i>
                                    <strong>Registration Rules</strong>
                                    <p class="mb-0 small text-muted">Configure registration policies and deadlines</p>
                                </div>
                                <i class="fas fa-chevron-right align-self-center"></i>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-outline-primary btn-block w-100" onclick="clearCache()">
                                <i class="fas fa-sync"></i> Clear Cache
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-outline-warning btn-block w-100" onclick="toggleMaintenance()">
                                <i class="fas fa-tools"></i> Toggle Maintenance
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('system.health') }}" class="btn btn-outline-success btn-block w-100">
                                <i class="fas fa-heartbeat"></i> System Health
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-outline-info btn-block w-100" onclick="exportSettings()">
                                <i class="fas fa-download"></i> Export Settings
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .border-left-primary {
        border-left: 4px solid #007bff !important;
    }
    .border-left-success {
        border-left: 4px solid #28a745 !important;
    }
    .border-left-info {
        border-left: 4px solid #17a2b8 !important;
    }
    .border-left-warning {
        border-left: 4px solid #ffc107 !important;
    }
    .list-group-item:hover:not(.disabled) {
        background-color: #f8f9fa;
    }
    .list-group-item.disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
</style>
@endpush

@push('scripts')
<script>
function clearCache() {
    if (confirm('Are you sure you want to clear all system cache?')) {
        fetch('/system/cache/clear', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        }).then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Cache cleared successfully');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        }).catch(error => {
            alert('Error clearing cache: ' + error);
        });
    }
}

function toggleMaintenance() {
    if (confirm('Are you sure you want to toggle maintenance mode?')) {
        fetch('/system/maintenance/toggle', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        }).then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        }).catch(error => {
            alert('Error toggling maintenance: ' + error);
        });
    }
}

function exportSettings() {
    window.location.href = '/system/settings/export';
}
</script>
@endpush
@endsection