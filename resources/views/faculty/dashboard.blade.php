@extends('layouts.app')

@section('title', 'Faculty Dashboard')

@section('content')
<div class="container-fluid px-4">
    <!-- Welcome Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-body bg-gradient-primary text-white p-4">
            <h3 class="fw-bold mb-2">Welcome back, {{ Auth::user()->name }}!</h3>
            <p class="mb-0">
                {{ $currentTerm ? $currentTerm->name : 'No Active Term' }} - {{ now()->format('l, F j, Y') }}
            </p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-primary">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="ms-3">
                            <p class="text-muted small mb-1">Active Sections</p>
                            <h4 class="mb-0">{{ $stats['total_sections'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-success">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="ms-3">
                            <p class="text-muted small mb-1">Total Students</p>
                            <h4 class="mb-0">{{ $stats['total_students'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-purple">
                            <i class="fas fa-chalkboard"></i>
                        </div>
                        <div class="ms-3">
                            <p class="text-muted small mb-1">Unique Courses</p>
                            <h4 class="mb-0">{{ $stats['total_courses'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-warning">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="ms-3">
                            <p class="text-muted small mb-1">Office Hours Today</p>
                            <h4 class="mb-0">{{ $stats['office_hours_today'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column - Today's Classes & Current Sections -->
        <div class="col-lg-8">
            <!-- Today's Schedule -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-gradient-primary text-white py-3">
                    <h5 class="mb-0"><i class="fas fa-calendar-day me-2"></i>Today's Classes</h5>
                </div>
                <div class="card-body">
                    @if($todayClasses->count() > 0)
                        @foreach($todayClasses as $class)
                            <div class="border-start border-4 border-primary ps-3 py-3 mb-3 bg-light rounded">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="fw-bold mb-1">
                                            {{ $class->course->course_code }} - {{ $class->course->title }}
                                        </h6>
                                        <p class="text-muted small mb-1">
                                            Section {{ $class->section_number }} | 
                                            {{ $class->start_time ? date('g:i A', strtotime($class->start_time)) : 'TBA' }} - 
                                            {{ $class->end_time ? date('g:i A', strtotime($class->end_time)) : 'TBA' }}
                                        </p>
                                        <p class="text-muted small mb-0">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            {{ $class->room ?? 'Online' }}{{ $class->building ? ', ' . $class->building : '' }}
                                        </p>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-success-soft text-success mb-2">
                                            {{ $class->current_enrollment }}/{{ $class->enrollment_capacity }} Students
                                        </span>
                                        <div>
                                            <a href="{{ route('faculty.attendance', $class->id) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                Take Attendance <i class="fas fa-arrow-right ms-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-calendar-times fa-3x mb-3"></i>
                            <p>No classes scheduled for today</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Current Sections -->
            <div class="card shadow-sm">
                <div class="card-header bg-gradient-primary text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i>Current Sections</h5>
                        <a href="{{ route('faculty.courses') }}" class="btn btn-sm btn-light">
                            View All <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($currentSections->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Course</th>
                                        <th>Section</th>
                                        <th>Schedule</th>
                                        <th class="text-center">Enrolled</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($currentSections as $section)
                                        <tr>
                                            <td>
                                                <div class="fw-bold">{{ $section->course->course_code }}</div>
                                                <small class="text-muted">{{ Str::limit($section->course->title, 30) }}</small>
                                            </td>
                                            <td>{{ $section->section_number }}</td>
                                            <td>
                                                <small>
                                                    {{ $section->days_of_week ?? 'TBA' }}
                                                    @if($section->start_time)
                                                        <br>{{ date('g:i A', strtotime($section->start_time)) }}
                                                    @endif
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info">
                                                    {{ $section->current_enrollment }}/{{ $section->enrollment_capacity }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('faculty.section.details', $section->id) }}" 
                                                       class="btn btn-outline-primary" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('faculty.roster', $section->id) }}" 
                                                       class="btn btn-outline-success" title="View Roster">
                                                        <i class="fas fa-users"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-folder-open fa-3x mb-3"></i>
                            <p>No sections assigned for this term</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column - Quick Actions, Deadlines, Activity -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-gradient-info text-white py-3">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('faculty.courses') }}" class="btn btn-primary">
                            <i class="fas fa-book me-2"></i>View My Courses
                        </a>
                        <a href="{{ route('faculty.office.hours') }}" class="btn btn-success">
                            <i class="fas fa-door-open me-2"></i>Manage Office Hours
                        </a>
                        <button onclick="alert('Gradebook feature coming soon!')" class="btn btn-purple">
                            <i class="fas fa-chart-line me-2"></i>Access Gradebook
                        </button>
                        <button onclick="alert('Announcements feature coming soon!')" class="btn btn-warning">
                            <i class="fas fa-bullhorn me-2"></i>Post Announcement
                        </button>
                    </div>
                </div>
            </div>

            <!-- Upcoming Deadlines -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-gradient-danger text-white py-3">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Upcoming Deadlines</h5>
                </div>
                <div class="card-body">
                    @if(count($upcomingDeadlines) > 0)
                        <ul class="list-unstyled mb-0">
                            @foreach($upcomingDeadlines as $deadline)
                                <li class="d-flex align-items-start mb-3">
                                    <span class="badge bg-danger me-2 mt-1">!</span>
                                    <div>
                                        <div class="fw-bold small">{{ $deadline['title'] }}</div>
                                        <small class="text-muted">{{ $deadline['date'] }}</small>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted text-center mb-0">No upcoming deadlines</p>
                    @endif
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card shadow-sm">
                <div class="card-header bg-gradient-purple text-white py-3">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Activity</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex align-items-start mb-3">
                            <span class="badge bg-primary me-2 mt-1">
                                <i class="fas fa-info"></i>
                            </span>
                            <div>
                                <div class="small">Welcome to the Faculty Portal!</div>
                                <small class="text-muted">Just now</small>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
}
.bg-gradient-info {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
}
.bg-gradient-danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}
.bg-gradient-purple {
    background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);
}
.bg-purple {
    background-color: #6f42c1;
}
.btn-purple {
    background-color: #6f42c1;
    border-color: #6f42c1;
    color: white;
}
.btn-purple:hover {
    background-color: #5a32a3;
    border-color: #5a32a3;
    color: white;
}
.stat-card {
    transition: transform 0.2s;
}
.stat-card:hover {
    transform: translateY(-2px);
}
.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}
.bg-success-soft {
    background-color: rgba(16, 185, 129, 0.1);
}
.text-success {
    color: #10b981 !important;
}
</style>
@endsection