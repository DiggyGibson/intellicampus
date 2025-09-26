@extends('layouts.app')

@section('title', 'Section Details')

@section('breadcrumb')
    <a href="{{ route('faculty.dashboard') }}">Faculty Dashboard</a>
    <i class="fas fa-chevron-right"></i>
    <a href="{{ route('faculty.courses') }}">My Courses</a>
    <i class="fas fa-chevron-right"></i>
    <span>Section Details</span>
@endsection

@section('page-actions')
    <button onclick="window.print()" class="btn btn-light me-2">
        <i class="fas fa-print me-1"></i> Print
    </button>
    <a href="{{ route('faculty.courses') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Courses
    </a>
@endsection

@section('content')
<div class="container-fluid px-4">
    <!-- Header Section -->
    <div class="mb-4">
        <h2 class="fw-bold text-dark mb-1">Section Details</h2>
        <p class="text-muted">
            {{ $section->course->course_code ?? '' }} - Section {{ $section->section_number ?? '' }}
        </p>
    </div>

    <!-- Course Information Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-gradient-primary text-white py-3">
            <h4 class="mb-0">{{ $section->course->title ?? 'Course Title' }}</h4>
            <small>{{ $section->course->course_code ?? '' }} | {{ $section->course->credits ?? 3 }} Credits</small>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="text-muted small">Section Number</label>
                    <p class="fw-bold">{{ $section->section_number }}</p>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="text-muted small">CRN</label>
                    <p class="fw-bold">{{ $section->crn ?? 'N/A' }}</p>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="text-muted small">Term</label>
                    <p class="fw-bold">{{ $section->term->name ?? 'N/A' }}</p>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="text-muted small">Schedule</label>
                    <p class="fw-bold">
                        {{ $section->days_of_week ?? 'TBA' }}
                        @if($section->start_time)
                            <br>{{ \Carbon\Carbon::parse($section->start_time)->format('g:i A') }} - 
                            {{ \Carbon\Carbon::parse($section->end_time)->format('g:i A') }}
                        @endif
                    </p>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="text-muted small">Location</label>
                    <p class="fw-bold">{{ $section->room ?? 'Online' }}</p>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="text-muted small">Delivery Mode</label>
                    <p class="fw-bold text-capitalize">{{ $section->delivery_mode ?? 'Traditional' }}</p>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="text-muted small">Enrollment</label>
                    <p class="fw-bold">
                        {{ $section->current_enrollment ?? 0 }} / {{ $section->max_capacity ?? 30 }}
                        <span class="text-muted small ms-2">
                            ({{ $section->max_capacity - $section->current_enrollment }} seats available)
                        </span>
                    </p>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="text-muted small">Status</label>
                    <p>
                        @php
                            $statusClass = match($section->status) {
                                'open' => 'bg-success',
                                'closed' => 'bg-danger',
                                'cancelled' => 'bg-secondary',
                                default => 'bg-primary'
                            };
                        @endphp
                        <span class="badge {{ $statusClass }}">
                            {{ ucfirst($section->status ?? 'open') }}
                        </span>
                    </p>
                </div>
                @if($section->delivery_mode == 'online' || $section->delivery_mode == 'hybrid')
                <div class="col-md-4 mb-3">
                    <label class="text-muted small">Virtual Platform</label>
                    <p class="fw-bold">{{ $section->virtual_platform ?? 'Zoom' }}</p>
                </div>
                @endif
            </div>

            @if($section->course->description)
            <hr>
            <div>
                <label class="text-muted small">Course Description</label>
                <p class="mt-2">{{ $section->course->description }}</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Quick Actions Grid -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <a href="{{ route('faculty.roster', $section->id) }}" 
               class="card action-card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-3x text-primary mb-3"></i>
                    <h5 class="text-dark">Class Roster</h5>
                    <p class="text-muted small mb-0">{{ $enrolledStudents->count() }} Students</p>
                </div>
            </a>
        </div>

        <div class="col-md-3 mb-3">
            <a href="{{ route('faculty.attendance', $section->id) }}" 
               class="card action-card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="fas fa-clipboard-check fa-3x text-warning mb-3"></i>
                    <h5 class="text-dark">Attendance</h5>
                    <p class="text-muted small mb-0">Take/View</p>
                </div>
            </a>
        </div>

        <div class="col-md-3 mb-3">
            <a href="{{ route('faculty.gradebook', $section->id) }}" 
               class="card action-card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="fas fa-chart-line fa-3x text-purple mb-3"></i>
                    <h5 class="text-dark">Gradebook</h5>
                    <p class="text-muted small mb-0">Manage Grades</p>
                </div>
            </a>
        </div>

        <div class="col-md-3 mb-3">
            <div onclick="postAnnouncement()" 
                 class="card action-card h-100" style="cursor: pointer;">
                <div class="card-body text-center">
                    <i class="fas fa-bullhorn fa-3x text-success mb-3"></i>
                    <h5 class="text-dark">Announcements</h5>
                    <p class="text-muted small mb-0">Post Update</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column - Attendance & Activity -->
        <div class="col-lg-4 mb-4">
            <!-- Attendance Overview -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-gradient-info text-white py-3">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Attendance Overview</h5>
                </div>
                <div class="card-body">
                    @if($attendanceStats['total'] > 0)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span>Present</span>
                                <span class="fw-bold text-success">{{ $attendanceStats['present'] }}</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: {{ ($attendanceStats['present'] / $attendanceStats['total']) * 100 }}%"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span>Absent</span>
                                <span class="fw-bold text-danger">{{ $attendanceStats['absent'] }}</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-danger" style="width: {{ ($attendanceStats['absent'] / $attendanceStats['total']) * 100 }}%"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span>Late</span>
                                <span class="fw-bold text-warning">{{ $attendanceStats['late'] }}</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-warning" style="width: {{ ($attendanceStats['late'] / $attendanceStats['total']) * 100 }}%"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span>Excused</span>
                                <span class="fw-bold text-info">{{ $attendanceStats['excused'] }}</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-info" style="width: {{ ($attendanceStats['excused'] / $attendanceStats['total']) * 100 }}%"></div>
                            </div>
                        </div>
                        <hr>
                        <div class="text-center">
                            <p class="small text-muted mb-1">Average Attendance Rate</p>
                            <h3 class="text-primary">
                                {{ round((($attendanceStats['present'] + $attendanceStats['late']) / $attendanceStats['total']) * 100, 1) }}%
                            </h3>
                        </div>
                    @else
                        <p class="text-muted text-center py-3">No attendance data yet</p>
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
                            <div class="activity-icon bg-primary-soft">
                                <i class="fas fa-clipboard-check text-primary"></i>
                            </div>
                            <div class="ms-3">
                                <p class="mb-0 small">Attendance taken</p>
                                <small class="text-muted">Today at 9:00 AM</small>
                            </div>
                        </li>
                        <li class="d-flex align-items-start mb-3">
                            <div class="activity-icon bg-purple-soft">
                                <i class="fas fa-chart-line text-purple"></i>
                            </div>
                            <div class="ms-3">
                                <p class="mb-0 small">Grades updated</p>
                                <small class="text-muted">Yesterday at 3:30 PM</small>
                            </div>
                        </li>
                        <li class="d-flex align-items-start mb-3">
                            <div class="activity-icon bg-success-soft">
                                <i class="fas fa-bullhorn text-success"></i>
                            </div>
                            <div class="ms-3">
                                <p class="mb-0 small">Announcement posted</p>
                                <small class="text-muted">2 days ago</small>
                            </div>
                        </li>
                        <li class="d-flex align-items-start">
                            <div class="activity-icon bg-warning-soft">
                                <i class="fas fa-user-plus text-warning"></i>
                            </div>
                            <div class="ms-3">
                                <p class="mb-0 small">New student enrolled</p>
                                <small class="text-muted">3 days ago</small>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Middle Column - Stats & Deadlines -->
        <div class="col-lg-4 mb-4">
            <!-- Quick Stats -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-gradient-success text-white py-3">
                    <h5 class="mb-0"><i class="fas fa-tachometer-alt me-2"></i>Section Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Total Classes</span>
                        <span class="fw-bold">45</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Classes Completed</span>
                        <span class="fw-bold">12</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Average Grade</span>
                        <span class="fw-bold">B+ (85%)</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Assignments Posted</span>
                        <span class="fw-bold">8</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Announcements</span>
                        <span class="fw-bold">15</span>
                    </div>
                    <hr>
                    <button onclick="viewAnalytics()" class="btn btn-primary w-100">
                        <i class="fas fa-chart-bar me-1"></i> View Detailed Analytics
                    </button>
                </div>
            </div>

            <!-- Upcoming Deadlines -->
            <div class="card shadow-sm">
                <div class="card-header bg-gradient-danger text-white py-3">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Upcoming Deadlines</h5>
                </div>
                <div class="card-body">
                    <div class="deadline-item mb-3">
                        <div class="d-flex align-items-start">
                            <div class="date-box bg-danger text-white">
                                <div class="fw-bold">15</div>
                            </div>
                            <div class="ms-3 flex-grow-1">
                                <h6 class="mb-1">Midterm Exam</h6>
                                <small class="text-muted">March 15, 2024 - In Class</small>
                                <div class="text-danger small fw-bold">In 3 days</div>
                            </div>
                        </div>
                    </div>
                    <div class="deadline-item mb-3">
                        <div class="d-flex align-items-start">
                            <div class="date-box bg-warning text-white">
                                <div class="fw-bold">20</div>
                            </div>
                            <div class="ms-3 flex-grow-1">
                                <h6 class="mb-1">Assignment 3 Due</h6>
                                <small class="text-muted">March 20, 2024 - 11:59 PM</small>
                                <div class="text-warning small fw-bold">In 8 days</div>
                            </div>
                        </div>
                    </div>
                    <div class="deadline-item">
                        <div class="d-flex align-items-start">
                            <div class="date-box bg-info text-white">
                                <div class="fw-bold">25</div>
                            </div>
                            <div class="ms-3 flex-grow-1">
                                <h6 class="mb-1">Project Proposals Due</h6>
                                <small class="text-muted">March 25, 2024 - End of Day</small>
                                <div class="text-info small fw-bold">In 13 days</div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <button onclick="manageDates()" class="btn btn-sm btn-outline-primary w-100">
                        <i class="fas fa-plus me-1"></i> Add Important Date
                    </button>
                </div>
            </div>
        </div>

        <!-- Right Column - Course Materials -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-gradient-teal text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-folder-open me-2"></i>Course Materials</h5>
                        <button onclick="uploadMaterial()" class="btn btn-sm btn-light">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="material-item mb-3 p-3 border rounded">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-file-pdf fa-2x text-danger me-3"></i>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Syllabus.pdf</h6>
                                <small class="text-muted d-block">Uploaded Jan 15, 2024</small>
                                <a href="#" class="btn btn-sm btn-outline-primary mt-2">
                                    <i class="fas fa-download me-1"></i> Download
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="material-item mb-3 p-3 border rounded">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-file-alt fa-2x text-primary me-3"></i>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Lecture Notes Week 1</h6>
                                <small class="text-muted d-block">Uploaded Jan 20, 2024</small>
                                <a href="#" class="btn btn-sm btn-outline-primary mt-2">
                                    <i class="fas fa-download me-1"></i> Download
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="material-item mb-3 p-3 border rounded">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-video fa-2x text-success me-3"></i>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Recorded Lecture 1</h6>
                                <small class="text-muted d-block">Uploaded Jan 22, 2024</small>
                                <a href="#" class="btn btn-sm btn-outline-success mt-2">
                                    <i class="fas fa-play me-1"></i> Watch
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <a href="#" class="btn btn-sm btn-outline-primary">
                            View All Materials <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
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
.bg-gradient-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}
.bg-gradient-danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}
.bg-gradient-purple {
    background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);
}
.bg-gradient-teal {
    background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
}
.text-purple {
    color: #6f42c1 !important;
}
.action-card {
    transition: transform 0.2s, box-shadow 0.2s;
    cursor: pointer;
}
.action-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
.activity-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.bg-primary-soft {
    background-color: rgba(37, 99, 235, 0.1);
}
.bg-purple-soft {
    background-color: rgba(111, 66, 193, 0.1);
}
.bg-success-soft {
    background-color: rgba(16, 185, 129, 0.1);
}
.bg-warning-soft {
    background-color: rgba(245, 158, 11, 0.1);
}
.date-box {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.material-item:hover {
    background-color: #f8f9fa;
}
</style>

<script>
function postAnnouncement() {
    alert('Announcement posting feature will be implemented soon.');
}

function viewAnalytics() {
    alert('Detailed analytics view will be implemented soon.');
}

function manageDates() {
    alert('Important dates management will be implemented soon.');
}

function uploadMaterial() {
    alert('Material upload feature will be implemented soon.');
}
</script>
@endsection