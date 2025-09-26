@extends('layouts.app')

@section('title', 'GPA Report')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">GPA Analysis Report</h1>
            <p class="mb-0 text-muted">Comprehensive GPA distribution and statistics</p>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Report Filters</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.grades.reports.gpa') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Academic Term</label>
                        <select class="form-select" name="term_id">
                            <option value="">All Terms</option>
                            @foreach($terms as $term)
                                <option value="{{ $term->id }}" {{ request('term_id') == $term->id ? 'selected' : '' }}>
                                    {{ $term->name }} {{ $term->is_current ? '(Current)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Program</label>
                        <select class="form-select" name="program_id">
                            <option value="">All Programs</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>
                                    {{ $program->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Academic Level</label>
                        <select class="form-select" name="academic_level">
                            <option value="">All Levels</option>
                            <option value="freshman" {{ request('academic_level') == 'freshman' ? 'selected' : '' }}>Freshman</option>
                            <option value="sophomore" {{ request('academic_level') == 'sophomore' ? 'selected' : '' }}>Sophomore</option>
                            <option value="junior" {{ request('academic_level') == 'junior' ? 'selected' : '' }}>Junior</option>
                            <option value="senior" {{ request('academic_level') == 'senior' ? 'selected' : '' }}>Senior</option>
                            <option value="graduate" {{ request('academic_level') == 'graduate' ? 'selected' : '' }}>Graduate</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Min GPA</label>
                        <input type="number" class="form-control" name="gpa_min" 
                               value="{{ request('gpa_min') }}" 
                               min="0" max="4" step="0.1" placeholder="0.0">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Max GPA</label>
                        <input type="number" class="form-control" name="gpa_max" 
                               value="{{ request('gpa_max') }}" 
                               min="0" max="4" step="0.1" placeholder="4.0">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Apply Filters
                        </button>
                        <a href="{{ route('admin.grades.reports.gpa') }}" class="btn btn-secondary">
                            <i class="fas fa-redo me-2"></i>Reset
                        </a>
                        <button type="button" class="btn btn-success float-end" onclick="exportReport()">
                            <i class="fas fa-file-excel me-2"></i>Export to Excel
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics Overview -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Average GPA</div>
                    <div class="h5 mb-0 font-weight-bold">{{ number_format($statistics['mean'] ?? 0, 3) }}</div>
                    <small class="text-muted">Mean of all students</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Median GPA</div>
                    <div class="h5 mb-0 font-weight-bold">{{ number_format($statistics['median'] ?? 0, 3) }}</div>
                    <small class="text-muted">Middle value</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Highest GPA</div>
                    <div class="h5 mb-0 font-weight-bold">{{ number_format($statistics['max'] ?? 0, 3) }}</div>
                    <small class="text-muted">Top performer</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Std Deviation</div>
                    <div class="h5 mb-0 font-weight-bold">{{ number_format($statistics['std_dev'] ?? 0, 3) }}</div>
                    <small class="text-muted">Spread of GPAs</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Distribution Chart -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 font-weight-bold text-primary">GPA Distribution</h6>
                </div>
                <div class="card-body">
                    <canvas id="distributionChart" height="200"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Pie Chart by Range -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 font-weight-bold text-primary">Students by GPA Range</h6>
                </div>
                <div class="card-body">
                    <canvas id="rangeChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Student List -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-users me-2"></i>Student GPA Details ({{ $students->count() }} students)</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="gpaTable">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Program</th>
                            <th>Level</th>
                            <th class="text-center">Cumulative GPA</th>
                            <th class="text-center">Semester GPA</th>
                            <th class="text-center">Major GPA</th>
                            <th class="text-center">Credits Earned</th>
                            <th class="text-center">Credits Attempted</th>
                            <th>Standing</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                        <tr class="{{ $student->cumulative_gpa < 2.0 ? 'table-warning' : '' }}">
                            <td>{{ $student->student_id }}</td>
                            <td>
                                <strong>{{ $student->last_name }}, {{ $student->first_name }}</strong>
                            </td>
                            <td>{{ $student->program_name }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ ucfirst($student->academic_level) }}</span>
                            </td>
                            <td class="text-center">
                                <strong class="{{ $student->cumulative_gpa >= 3.5 ? 'text-success' : ($student->cumulative_gpa < 2.0 ? 'text-danger' : '') }}">
                                    {{ number_format($student->cumulative_gpa, 3) }}
                                </strong>
                            </td>
                            <td class="text-center">{{ number_format($student->semester_gpa ?? 0, 3) }}</td>
                            <td class="text-center">{{ number_format($student->major_gpa ?? 0, 3) }}</td>
                            <td class="text-center">{{ $student->total_credits_earned }}</td>
                            <td class="text-center">{{ $student->total_credits_attempted }}</td>
                            <td>
                                @php
                                    $standing = 'Good Standing';
                                    if ($student->cumulative_gpa < 2.0) $standing = 'Probation';
                                    if ($student->cumulative_gpa < 1.0) $standing = 'Suspension';
                                    if ($student->cumulative_gpa >= 3.5) $standing = 'Dean\'s List';
                                @endphp
                                <span class="badge bg-{{ $standing == 'Dean\'s List' ? 'success' : ($standing == 'Good Standing' ? 'primary' : 'danger') }}">
                                    {{ $standing }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Summary Statistics by Program -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>GPA by Program</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Program</th>
                            <th class="text-center">Students</th>
                            <th class="text-center">Average GPA</th>
                            <th class="text-center">Highest GPA</th>
                            <th class="text-center">Lowest GPA</th>
                            <th class="text-center">Dean's List</th>
                            <th class="text-center">On Probation</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $byProgram = $students->groupBy('program_name');
                        @endphp
                        @foreach($byProgram as $program => $programStudents)
                        <tr>
                            <td><strong>{{ $program }}</strong></td>
                            <td class="text-center">{{ $programStudents->count() }}</td>
                            <td class="text-center">{{ number_format($programStudents->avg('cumulative_gpa'), 3) }}</td>
                            <td class="text-center">{{ number_format($programStudents->max('cumulative_gpa'), 3) }}</td>
                            <td class="text-center">{{ number_format($programStudents->min('cumulative_gpa'), 3) }}</td>
                            <td class="text-center">
                                <span class="badge bg-success">
                                    {{ $programStudents->where('cumulative_gpa', '>=', 3.5)->count() }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger">
                                    {{ $programStudents->where('cumulative_gpa', '<', 2.0)->count() }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .border-left-primary { border-left: 4px solid #4e73df !important; }
    .border-left-success { border-left: 4px solid #1cc88a !important; }
    .border-left-info { border-left: 4px solid #36b9cc !important; }
    .border-left-warning { border-left: 4px solid #f6c23e !important; }
    .text-xs { font-size: .7rem; }
    .font-weight-bold { font-weight: 700 !important; }
    .text-uppercase { text-transform: uppercase !important; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script>
// Initialize DataTable
$(document).ready(function() {
    $('#gpaTable').DataTable({
        pageLength: 25,
        order: [[4, 'desc']], // Sort by cumulative GPA
        dom: 'Bfrtip'
    });
});

// Distribution Chart
const distributionData = @json($distribution);
const distributionCtx = document.getElementById('distributionChart').getContext('2d');
new Chart(distributionCtx, {
    type: 'bar',
    data: {
        labels: Object.keys(distributionData),
        datasets: [{
            label: 'Number of Students',
            data: Object.values(distributionData),
            backgroundColor: 'rgba(78, 115, 223, 0.8)'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

// Range Pie Chart
const rangeCtx = document.getElementById('rangeChart').getContext('2d');
new Chart(rangeCtx, {
    type: 'doughnut',
    data: {
        labels: Object.keys(distributionData),
        datasets: [{
            data: Object.values(distributionData),
            backgroundColor: [
                'rgba(40, 167, 69, 0.8)',   // 4.0
                'rgba(40, 167, 69, 0.6)',   // 3.5-3.99
                'rgba(32, 201, 151, 0.8)',  // 3.0-3.49
                'rgba(255, 193, 7, 0.8)',   // 2.5-2.99
                'rgba(255, 193, 7, 0.6)',   // 2.0-2.49
                'rgba(253, 126, 20, 0.8)',  // 1.5-1.99
                'rgba(253, 126, 20, 0.6)',  // 1.0-1.49
                'rgba(220, 53, 69, 0.8)'    // Below 1.0
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right'
            }
        }
    }
});

// Export function
function exportReport() {
    const params = new URLSearchParams(window.location.search);
    params.append('format', 'xlsx');
    window.location.href = `{{ route('admin.grades.reports.export', 'gpa') }}?${params.toString()}`;
}
</script>
@endpush
@endsection