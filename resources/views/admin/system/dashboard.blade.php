@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3">System Configuration Dashboard</h1>
            <p class="text-muted">Monitor and manage system-wide settings and configurations</p>
        </div>
    </div>

    {{-- System Health Alert --}}
    @if($stats['system_health'] < 100)
    <div class="alert alert-warning" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        System Health: {{ $stats['system_health'] }}% - Some components may need attention
    </div>
    @endif

    {{-- System Alerts --}}
    @if(count($alerts) > 0)
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

    {{-- Stats Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <h2 class="mb-0">{{ number_format($stats['total_users']) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Active Modules</h5>
                    <h2 class="mb-0">{{ $stats['active_modules'] }} / {{ $stats['total_modules'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">System Health</h5>
                    <h2 class="mb-0">{{ $stats['system_health'] }}%</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Environment</h5>
                    <h2 class="mb-0">{{ ucfirst(config('app.env')) }}</h2>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('system.settings.index') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-cog me-2"></i>General Settings
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('system.modules.index') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-puzzle-piece me-2"></i>Manage Modules
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('system.backups.index') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-database me-2"></i>Backups
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('system.logs.index') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-file-alt me-2"></i>System Logs
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Activities --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent System Activities</h5>
                </div>
                <div class="card-body">
                    @if($recentActivities->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Module</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentActivities as $activity)
                                <tr>
                                    <td>{{ $activity->created_at->diffForHumans() }}</td>
                                    <td>{{ $activity->user->name ?? 'System' }}</td>
                                    <td>{{ $activity->action }}</td>
                                    <td>{{ $activity->module ?? 'System' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted">No recent activities</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection