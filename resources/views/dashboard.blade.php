@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    {{-- Dashboard Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
            <p class="text-muted">Welcome to IntelliCampus Management System</p>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Students
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($studentCount ?? 0) }}
                            </div>
                            <small class="text-muted">
                                {{ number_format($activeStudentCount ?? 0) }} Active
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-graduate fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Courses
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($courseCount ?? 0) }}
                            </div>
                            <small class="text-muted">
                                {{ number_format($sectionCount ?? 0) }} Sections
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-book fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Faculty Members
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($facultyCount ?? 0) }}
                            </div>
                            <small class="text-muted">
                                Teaching Staff
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chalkboard-teacher fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Enrollments
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($totalEnrollments ?? 0) }}
                            </div>
                            <small class="text-muted">
                                {{ number_format($openSectionCount ?? 0) }} Open Sections
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts and Tables Row --}}
    <div class="row mb-4">
        {{-- Grade Distribution Chart --}}
        <div class="col-xl-4 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Grade Distribution</h6>
                </div>
                <div class="card-body">
                    <canvas id="gradeDistributionChart"></canvas>
                    @if($gradeDistribution ?? false)
                    <div class="mt-3 text-center small">
                        @foreach($gradeDistribution as $grade => $count)
                        <span class="mr-2">
                            <i class="fas fa-circle text-{{ $loop->index == 0 ? 'primary' : ($loop->index == 1 ? 'success' : ($loop->index == 2 ? 'info' : 'warning')) }}"></i> 
                            {{ $grade }}: {{ $count }}%
                        </span>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Academic Performance Metrics --}}
        <div class="col-xl-4 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Academic Performance</h6>
                </div>
                <div class="card-body">
                    @php
                        $metrics = $performanceMetrics ?? [
                            'averageGPA' => 0,
                            'passRate' => 0,
                            'retentionRate' => 0,
                            'graduationRate' => 0
                        ];
                    @endphp
                    
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Average GPA</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($metrics['averageGPA'], 2) }}</div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Pass Rate</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $metrics['passRate'] }}%</div>
                        </div>
                        <div class="col-6">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Retention Rate</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $metrics['retentionRate'] }}%</div>
                        </div>
                        <div class="col-6">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Graduation Rate</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $metrics['graduationRate'] }}%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Upcoming Deadlines --}}
        <div class="col-xl-4 col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Upcoming Deadlines</h6>
                </div>
                <div class="card-body">
                    @forelse($upcomingDeadlines ?? [] as $deadline)
                    <div class="mb-3">
                        <div class="small text-gray-500">{{ $deadline['date']->format('M d, Y') }}</div>
                        <div class="font-weight-bold">{{ $deadline['title'] }}</div>
                        <div class="small text-muted">{{ $deadline['description'] }}</div>
                        <div class="mt-1">
                            <span class="badge badge-{{ $deadline['type'] }}">
                                {{ $deadline['days_remaining'] }} days remaining
                            </span>
                        </div>
                    </div>
                    @if(!$loop->last)<hr>@endif
                    @empty
                    <p class="text-muted text-center">No upcoming deadlines</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Students Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Student Registrations</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Program</th>
                                    <th>Status</th>
                                    <th>Registration Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentStudents ?? [] as $student)
                                <tr>
                                    <td>{{ $student->student_id ?? 'N/A' }}</td>
                                    <td>
                                        @if($student->user)
                                            {{ $student->user->first_name ?? '' }} {{ $student->user->last_name ?? '' }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>{{ $student->user->email ?? 'N/A' }}</td>
                                    <td>{{ $student->program->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $student->enrollment_status == 'active' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($student->enrollment_status ?? 'pending') }}
                                        </span>
                                    </td>
                                    <td>{{ $student->created_at ? $student->created_at->format('M d, Y') : 'N/A' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No recent student registrations</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.border-left-primary {
    border-left: 4px solid #4e73df!important;
}
.border-left-success {
    border-left: 4px solid #1cc88a!important;
}
.border-left-info {
    border-left: 4px solid #36b9cc!important;
}
.border-left-warning {
    border-left: 4px solid #f6c23e!important;
}
.text-xs {
    font-size: .7rem;
}
.text-gray-800 {
    color: #5a5c69!important;
}
.text-gray-300 {
    color: #dddfeb!important;
}
.text-gray-500 {
    color: #b7b9cc!important;
}
.badge-danger { background-color: #e74a3b; }
.badge-warning { background-color: #f6c23e; color: #000; }
.badge-info { background-color: #36b9cc; }
.badge-success { background-color: #1cc88a; }
.badge-secondary { background-color: #858796; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Grade Distribution Chart
const gradeData = {!! json_encode($gradeDistribution ?? ['A' => 28, 'B' => 35, 'C' => 22, 'D' => 10, 'F' => 5]) !!};
const ctx = document.getElementById('gradeDistributionChart');

if (ctx) {
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(gradeData),
            datasets: [{
                data: Object.values(gradeData),
                backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
                hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#e4b22b', '#d52b1e'],
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}
</script>
@endpush