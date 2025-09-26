// File: resources/views/department/dashboard.blade.php
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="page-title">Department Dashboard</h1>
                <p class="text-muted">{{ Auth::user()->department->name ?? 'Department Management' }}</p>
            </div>
        </div>
    </div>

    <!-- Department Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-primary">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="mb-0">{{ $facultyCount ?? 0 }}</h3>
                            <p class="text-muted mb-0">Faculty Members</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-success">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="mb-0">{{ $courseCount ?? 0 }}</h3>
                            <p class="text-muted mb-0">Active Courses</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-warning">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="mb-0">{{ $studentCount ?? 0 }}</h3>
                            <p class="text-muted mb-0">Enrolled Students</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-info">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="mb-0">{{ $sectionCount ?? 0 }}</h3>
                            <p class="text-muted mb-0">Course Sections</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Quick Actions -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('department.faculty.index') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-users me-2"></i> Manage Faculty
                        </a>
                        <a href="{{ route('department.courses.index') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-book me-2"></i> Course Management
                        </a>
                        <a href="{{ route('department.scheduling.index') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-calendar-alt me-2"></i> Class Scheduling
                        </a>
                        <a href="{{ route('department.curriculum.index') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-project-diagram me-2"></i> Curriculum Planning
                        </a>
                        <a href="{{ route('department.reports.index') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-chart-bar me-2"></i> Department Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Term Overview -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Current Term Overview</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Course</th>
                                <th>Sections</th>
                                <th>Enrollment</th>
                                <th>Faculty</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($currentCourses ?? [] as $course)
                            <tr>
                                <td>{{ $course->code }}</td>
                                <td>{{ $course->sections_count }}</td>
                                <td>{{ $course->enrolled }}/{{ $course->capacity }}</td>
                                <td>{{ $course->instructor }}</td>
                                <td>
                                    <span class="badge bg-success">Active</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No courses scheduled for current term</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.stat-card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
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
</style>
@endsection