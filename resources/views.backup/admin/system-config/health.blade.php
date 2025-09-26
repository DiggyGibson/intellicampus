{{-- File: resources/views/admin/system-config/health.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-1">System Health Check</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.system-config.index') }}">System Configuration</a></li>
                    <li class="breadcrumb-item active">Health Check</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        {{-- System Status --}}
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-heartbeat"></i> System Status</h5>
                </div>
                <div class="card-body">
                    @foreach($checks as $service => $check)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <strong>{{ ucfirst($service) }}</strong>
                                <br>
                                <small class="text-muted">{{ $check['message'] }}</small>
                            </div>
                            <div>
                                @if($check['status'] == 'ok')
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle"></i> OK
                                    </span>
                                @elseif($check['status'] == 'warning')
                                    <span class="badge bg-warning">
                                        <i class="fas fa-exclamation-triangle"></i> Warning
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times-circle"></i> Error
                                    </span>
                                @endif
                            </div>
                        </div>
                        @if(!$loop->last)
                            <hr>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        {{-- System Information --}}
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> System Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <td><strong>PHP Version:</strong></td>
                                <td>{{ $systemInfo['php_version'] }}</td>
                            </tr>
                            <tr>
                                <td><strong>Laravel Version:</strong></td>
                                <td>{{ $systemInfo['laravel_version'] }}</td>
                            </tr>
                            <tr>
                                <td><strong>Timezone:</strong></td>
                                <td>{{ $systemInfo['timezone'] }}</td>
                            </tr>
                            <tr>
                                <td><strong>Locale:</strong></td>
                                <td>{{ $systemInfo['locale'] }}</td>
                            </tr>
                            <tr>
                                <td><strong>Environment:</strong></td>
                                <td>
                                    <span class="badge bg-{{ $systemInfo['environment'] == 'production' ? 'success' : 'warning' }}">
                                        {{ $systemInfo['environment'] }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Debug Mode:</strong></td>
                                <td>
                                    <span class="badge bg-{{ $systemInfo['debug_mode'] ? 'warning' : 'success' }}">
                                        {{ $systemInfo['debug_mode'] ? 'Enabled' : 'Disabled' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-tools"></i> Maintenance Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-outline-primary btn-block" onclick="clearCache()">
                                <i class="fas fa-sync"></i> Clear Cache
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-outline-info btn-block" onclick="runMigrations()">
                                <i class="fas fa-database"></i> Run Migrations
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-outline-warning btn-block" onclick="optimizeApp()">
                                <i class="fas fa-rocket"></i> Optimize App
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-outline-danger btn-block" onclick="toggleMaintenance()">
                                <i class="fas fa-power-off"></i> Maintenance Mode
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- System Logs Preview --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-file-alt"></i> Recent System Activity</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">System logs and recent activity would be displayed here.</p>
                    <small class="text-info">This feature will be implemented with log monitoring integration.</small>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function clearCache() {
    if (confirm('Clear all system cache?')) {
        fetch('{{ route('admin.system-config.clear-cache') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        }).then(response => response.json())
        .then(data => {
            alert(data.message || 'Cache cleared');
            location.reload();
        });
    }
}

function toggleMaintenance() {
    if (confirm('Toggle maintenance mode?')) {
        fetch('{{ route('admin.system-config.toggle-maintenance') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        }).then(response => response.json())
        .then(data => {
            alert(data.message || 'Maintenance mode toggled');
            location.reload();
        });
    }
}

function runMigrations() {
    alert('Migration runner not implemented in production. Use command line.');
}

function optimizeApp() {
    alert('App optimization should be run from command line.');
}
</script>
@endpush
@endsection