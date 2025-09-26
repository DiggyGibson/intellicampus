@extends('layouts.app')

@section('title', 'System Analysis Tool')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">System Route Analysis Tool</h1>
    
    <!-- Enhanced Summary Cards with Usage Stats -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-title text-muted">Total Routes</h6>
                    <p class="display-4 mb-0">{{ count($namedRoutes) + count(array_filter($routesByPrefix, function($routes) { return !empty($routes); })) }}</p>
                    <small class="text-muted">All registered routes</small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-title text-muted">Named Routes</h6>
                    <p class="display-4 mb-0">{{ count(array_filter($namedRoutes, function($r) { return !empty($r['name']); })) }}</p>
                    <small class="text-success">Can be referenced</small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card h-100 border-primary">
                <div class="card-body">
                    <h6 class="card-title text-primary">Routes in Use</h6>
                    <p class="display-4 mb-0 text-primary">
                        {{ count($expectedRoutes) - count($missingRoutes) }}
                    </p>
                    <small class="text-primary">Used in navigation</small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card h-100 border-warning">
                <div class="card-body">
                    <h6 class="card-title text-warning">Unused Routes</h6>
                    <p class="display-4 mb-0 text-warning">
                        {{ count($unusedNamedRoutes) }}
                    </p>
                    <small class="text-warning">Not in navigation</small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card h-100 {{ count($missingRoutes) > 0 ? 'border-danger' : 'border-success' }}">
                <div class="card-body">
                    <h6 class="card-title {{ count($missingRoutes) > 0 ? 'text-danger' : 'text-success' }}">
                        Missing Routes
                    </h6>
                    <p class="display-4 mb-0 {{ count($missingRoutes) > 0 ? 'text-danger' : 'text-success' }}">
                        {{ count($missingRoutes) }}
                    </p>
                    <small class="{{ count($missingRoutes) > 0 ? 'text-danger' : 'text-success' }}">
                        {{ count($missingRoutes) > 0 ? 'Need to be created' : 'All routes exist!' }}
                    </small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-title text-muted">Route Prefixes</h6>
                    <p class="display-4 mb-0">{{ count($routesByPrefix) }}</p>
                    <small class="text-muted">Grouped routes</small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Route Usage Percentage Bar -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Route Usage Statistics</h5>
            @php
                $totalNamedRoutes = count(array_filter($namedRoutes, function($r) { return !empty($r['name']); }));
                $usedRoutes = count($expectedRoutes) - count($missingRoutes);
                $unusedCount = count($unusedNamedRoutes);
                $usagePercentage = $totalNamedRoutes > 0 ? round(($usedRoutes / $totalNamedRoutes) * 100, 1) : 0;
            @endphp
            
            <div class="progress mb-3" style="height: 30px;">
                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $usagePercentage }}%">
                    Used: {{ $usedRoutes }} ({{ $usagePercentage }}%)
                </div>
                <div class="progress-bar bg-warning" role="progressbar" style="width: {{ 100 - $usagePercentage }}%">
                    Unused: {{ $unusedCount }} ({{ round(100 - $usagePercentage, 1) }}%)
                </div>
            </div>
            
            <div class="row text-center">
                <div class="col-md-4">
                    <h6>Total Named Routes</h6>
                    <p class="h4">{{ $totalNamedRoutes }}</p>
                </div>
                <div class="col-md-4">
                    <h6>Routes in Navigation</h6>
                    <p class="h4 text-success">{{ $usedRoutes }}</p>
                </div>
                <div class="col-md-4">
                    <h6>Available for Use</h6>
                    <p class="h4 text-warning">{{ $unusedCount }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- User Analysis -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Current User Analysis</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr>
                            <th width="150">User ID:</th>
                            <td>{{ $userAnalysis['id'] }}</td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td>{{ $userAnalysis['email'] }}</td>
                        </tr>
                        <tr>
                            <th>User Type:</th>
                            <td><span class="badge bg-info">{{ $userAnalysis['user_type'] }}</span></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr>
                            <th width="150">Roles:</th>
                            <td>
                                @foreach($userAnalysis['roles'] as $role)
                                    <span class="badge bg-primary me-1">{{ $role }}</span>
                                @endforeach
                            </td>
                        </tr>
                        <tr>
                            <th>Direct Permissions:</th>
                            <td><span class="badge bg-secondary">{{ count($userAnalysis['permissions']) }}</span></td>
                        </tr>
                        <tr>
                            <th>Total Permissions:</th>
                            <td><span class="badge bg-success">{{ count($userAnalysis['all_permissions']) }}</span></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Missing Routes -->
    @if(count($missingRoutes) > 0)
    <div class="card mb-4 border-danger">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Missing Routes ({{ count($missingRoutes) }})
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>Route Name</th>
                            <th>Likely Module</th>
                            <th>Suggested Fix</th>
                            <th>Priority</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($missingRoutes as $routeName)
                        <tr>
                            <td><code class="text-danger">{{ $routeName }}</code></td>
                            <td>
                                @php
                                    $parts = explode('.', $routeName);
                                    $module = $parts[0];
                                    echo ucfirst($module);
                                @endphp
                            </td>
                            <td>
                                @php
                                    $suggestions = [];
                                    
                                    // Check for similar routes
                                    foreach($namedRoutes as $existingRoute => $details) {
                                        if (str_contains($existingRoute, implode('.', array_slice($parts, 1)))) {
                                            $suggestions[] = "Similar: {$existingRoute}";
                                            break;
                                        }
                                    }
                                    
                                    // Suggest the likely file
                                    $routeFile = $parts[0] . '.php';
                                    if (in_array($parts[0], ['student', 'faculty', 'admin', 'registrar', 'system', 'financial', 'grades'])) {
                                        $suggestions[] = "Add to routes/{$routeFile}";
                                    }
                                @endphp
                                
                                @if(!empty($suggestions))
                                    <small>{{ implode(' | ', $suggestions) }}</small>
                                @else
                                    <small>Check route file and controller</small>
                                @endif
                            </td>
                            <td>
                                @php
                                    // Determine priority based on route name
                                    if (str_contains($routeName, 'dashboard') || str_contains($routeName, 'index')) {
                                        echo '<span class="badge bg-danger">High</span>';
                                    } elseif (str_contains($routeName, 'create') || str_contains($routeName, 'edit')) {
                                        echo '<span class="badge bg-warning">Medium</span>';
                                    } else {
                                        echo '<span class="badge bg-secondary">Low</span>';
                                    }
                                @endphp
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Unused Named Routes (Potential for Navigation) -->
    @if(count($unusedNamedRoutes) > 0)
    <div class="card mb-4 border-warning">
        <div class="card-header bg-warning">
            <h5 class="mb-0">
                <i class="fas fa-info-circle me-2"></i>
                Unused Named Routes ({{ count($unusedNamedRoutes) }}) - Available for Navigation
            </h5>
        </div>
        <div class="card-body">
            <p class="text-muted">These routes exist but are not referenced in the navigation configuration. They could be added to menus if needed.</p>
            
            <!-- Group unused routes by prefix -->
            @php
                $unusedByPrefix = [];
                foreach($unusedNamedRoutes as $routeName) {
                    if (isset($namedRoutes[$routeName])) {
                        $prefix = explode('.', $routeName)[0];
                        if (!isset($unusedByPrefix[$prefix])) {
                            $unusedByPrefix[$prefix] = [];
                        }
                        $unusedByPrefix[$prefix][] = $routeName;
                    }
                }
                ksort($unusedByPrefix);
            @endphp
            
            <div class="accordion" id="unusedRoutesAccordion">
                @foreach($unusedByPrefix as $prefix => $routes)
                <div class="accordion-item">
                    <h2 class="accordion-header" id="unused-heading-{{ Str::slug($prefix) }}">
                        <button class="accordion-button collapsed" type="button" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#unused-{{ Str::slug($prefix) }}" 
                                aria-expanded="false">
                            <strong>{{ ucfirst($prefix) }}</strong>
                            <span class="badge bg-warning ms-2">{{ count($routes) }} unused</span>
                        </button>
                    </h2>
                    <div id="unused-{{ Str::slug($prefix) }}" 
                         class="accordion-collapse collapse" 
                         data-bs-parent="#unusedRoutesAccordion">
                        <div class="accordion-body">
                            <div class="row">
                                @foreach(array_chunk($routes, ceil(count($routes) / 3)) as $chunk)
                                <div class="col-md-4">
                                    <ul class="list-unstyled">
                                        @foreach($chunk as $route)
                                        <li><code>{{ $route }}</code></li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Routes by Prefix (Detailed View) -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Routes by Module/Prefix</h5>
        </div>
        <div class="card-body">
            <div class="accordion" id="routesAccordion">
                @foreach($routesByPrefix as $prefix => $routes)
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading-{{ Str::slug($prefix) }}">
                        <button class="accordion-button collapsed" type="button" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#collapse-{{ Str::slug($prefix) }}" 
                                aria-expanded="false">
                            <strong>{{ ucfirst($prefix) }}</strong>
                            <span class="badge bg-secondary ms-2">{{ count($routes) }} routes</span>
                            @php
                                // Count how many are named
                                $namedCount = count(array_filter($routes, function($r) { return !empty($r['name']); }));
                                if ($namedCount > 0) {
                                    echo '<span class="badge bg-primary ms-1">' . $namedCount . ' named</span>';
                                }
                            @endphp
                        </button>
                    </h2>
                    <div id="collapse-{{ Str::slug($prefix) }}" 
                         class="accordion-collapse collapse" 
                         data-bs-parent="#routesAccordion">
                        <div class="accordion-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>URI</th>
                                            <th>Name</th>
                                            <th>Methods</th>
                                            <th>Action</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($routes as $route)
                                        <tr>
                                            <td><code>{{ $route['uri'] }}</code></td>
                                            <td>
                                                @if($route['name'])
                                                    <code>{{ $route['name'] }}</code>
                                                    @if(in_array($route['name'], $expectedRoutes))
                                                        <span class="badge bg-success ms-1" title="Used in navigation">
                                                            <i class="fas fa-check"></i>
                                                        </span>
                                                    @elseif(in_array($route['name'], $unusedNamedRoutes))
                                                        <span class="badge bg-warning ms-1" title="Not in navigation">
                                                            <i class="fas fa-exclamation"></i>
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td><span class="badge bg-info">{{ $route['methods'] }}</span></td>
                                            <td class="small">
                                                @php
                                                    $action = $route['action'];
                                                    if (strpos($action, '@') !== false) {
                                                        $parts = explode('@', $action);
                                                        $controller = class_basename($parts[0]);
                                                        $method = $parts[1];
                                                        echo "<span class='text-primary'>{$controller}</span>@<span class='text-success'>{$method}</span>";
                                                    } else {
                                                        echo "<span class='text-muted'>Closure</span>";
                                                    }
                                                @endphp
                                            </td>
                                            <td>
                                                @if($route['name'] && in_array($route['name'], $expectedRoutes))
                                                    <span class="badge bg-success">In Use</span>
                                                @elseif($route['name'] && in_array($route['name'], $unusedNamedRoutes))
                                                    <span class="badge bg-warning">Available</span>
                                                @elseif($route['name'])
                                                    <span class="badge bg-secondary">Named</span>
                                                @else
                                                    <span class="badge bg-light text-dark">Anonymous</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Module Health Check -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Module Health Check</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Module</th>
                            <th>Total Routes</th>
                            <th>Named Routes</th>
                            <th>In Navigation</th>
                            <th>Missing</th>
                            <th>Health</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $modules = ['student', 'faculty', 'admin', 'registrar', 'financial', 'system', 'grades', 'department', 'admissions'];
                        @endphp
                        @foreach($modules as $module)
                        <tr>
                            <td><strong>{{ ucfirst($module) }}</strong></td>
                            <td>
                                @php
                                    $moduleRoutes = $routesByPrefix[$module] ?? [];
                                    echo count($moduleRoutes);
                                @endphp
                            </td>
                            <td>
                                @php
                                    $namedModuleRoutes = array_filter($moduleRoutes, function($r) { return !empty($r['name']); });
                                    echo count($namedModuleRoutes);
                                @endphp
                            </td>
                            <td>
                                @php
                                    $inNav = 0;
                                    foreach($expectedRoutes as $expected) {
                                        if (strpos($expected, $module.'.') === 0) {
                                            if (!in_array($expected, $missingRoutes)) {
                                                $inNav++;
                                            }
                                        }
                                    }
                                    echo $inNav;
                                @endphp
                            </td>
                            <td>
                                @php
                                    $moduleMissing = 0;
                                    foreach($missingRoutes as $missing) {
                                        if (strpos($missing, $module.'.') === 0) {
                                            $moduleMissing++;
                                        }
                                    }
                                    if ($moduleMissing > 0) {
                                        echo '<span class="text-danger">' . $moduleMissing . '</span>';
                                    } else {
                                        echo '<span class="text-success">0</span>';
                                    }
                                @endphp
                            </td>
                            <td>
                                @php
                                    if ($moduleMissing > 0) {
                                        echo '<span class="badge bg-danger">Issues</span>';
                                    } elseif (count($moduleRoutes) == 0) {
                                        echo '<span class="badge bg-secondary">Empty</span>';
                                    } elseif ($inNav > 0) {
                                        echo '<span class="badge bg-success">Good</span>';
                                    } else {
                                        echo '<span class="badge bg-warning">Unused</span>';
                                    }
                                @endphp
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">Quick Actions & Tools</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Navigation Tools</h6>
                    <a href="{{ route('nav.debug') }}" class="btn btn-info me-2 mb-2" target="_blank">
                        <i class="fas fa-bug"></i> Navigation Debug
                    </a>
                    <a href="{{ route('nav.clear-cache') }}" class="btn btn-warning me-2 mb-2">
                        <i class="fas fa-broom"></i> Clear Nav Cache
                    </a>
                    <button onclick="window.location.reload()" class="btn btn-secondary mb-2">
                        <i class="fas fa-sync"></i> Refresh Analysis
                    </button>
                </div>
                <div class="col-md-6">
                    <h6>Export Options</h6>
                    <button onclick="exportRouteData('csv')" class="btn btn-outline-primary me-2 mb-2">
                        <i class="fas fa-file-csv"></i> Export to CSV
                    </button>
                    <button onclick="exportRouteData('json')" class="btn btn-outline-primary mb-2">
                        <i class="fas fa-file-code"></i> Export to JSON
                    </button>
                    <button onclick="window.print()" class="btn btn-outline-secondary mb-2">
                        <i class="fas fa-print"></i> Print Report
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Export route data functionality
    function exportRouteData(format) {
        const data = {
            summary: {
                total_routes: {{ count($namedRoutes) + count(array_filter($routesByPrefix, function($routes) { return !empty($routes); })) }},
                named_routes: {{ count(array_filter($namedRoutes, function($r) { return !empty($r['name']); })) }},
                routes_in_use: {{ count($expectedRoutes) - count($missingRoutes) }},
                unused_routes: {{ count($unusedNamedRoutes) }},
                missing_routes: {{ count($missingRoutes) }}
            },
            missing_routes: @json($missingRoutes),
            unused_routes: @json($unusedNamedRoutes)
        };
        
        if (format === 'json') {
            const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
            downloadFile(blob, 'route-analysis.json');
        } else if (format === 'csv') {
            let csv = 'Category,Count\n';
            for (const [key, value] of Object.entries(data.summary)) {
                csv += `${key.replace(/_/g, ' ')},${value}\n`;
            }
            const blob = new Blob([csv], { type: 'text/csv' });
            downloadFile(blob, 'route-analysis.csv');
        }
    }
    
    function downloadFile(blob, filename) {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
    }
    
    // Search functionality for routes
    document.addEventListener('DOMContentLoaded', function() {
        // Could add search functionality here if needed
    });
</script>
@endpush

@push('styles')
<style>
    @media print {
        .btn, .card-header, .accordion-button {
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }
        .card {
            break-inside: avoid;
        }
    }
    
    .badge {
        font-weight: normal;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(0,0,0,0.02);
    }
    
    code {
        padding: 2px 4px;
        font-size: 87.5%;
        color: #e83e8c;
        background-color: #f8f9fa;
        border-radius: 3px;
    }
    
    .progress {
        background-color: #e9ecef;
    }
</style>
@endpush