@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="page-title">Student Dashboard</h1>
            <p class="text-muted">Welcome back, {{ $student->first_name ?? Auth::user()->name }}!</p>
        </div>
    </div>

    {{-- Quick Stats --}}
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-primary">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $gpaData['current_gpa'] }}</h3>
                    <p>Current GPA</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-success">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $currentEnrollments->count() }}</h3>
                    <p>Enrolled Courses</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-warning">
                    <i class="fas fa-trophy"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $gpaData['credits_earned'] }}</h3>
                    <p>Credits Earned</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-info">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $academicProgress['percentage'] }}%</h3>
                    <p>Degree Progress</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        {{-- Current Courses --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Current Courses</h5>
                </div>
                <div class="card-body">
                    @if($currentEnrollments->count() > 0)
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Course Code</th>
                                        <th>Course Title</th>
                                        <th>Section</th>
                                        <th>Credits</th>
                                        <th>Instructor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($currentEnrollments as $enrollment)
                                    <tr>
                                        <td>{{ $enrollment->section->course->code ?? 'N/A' }}</td>
                                        <td>{{ $enrollment->section->course->title ?? 'N/A' }}</td>
                                        <td>{{ $enrollment->section->section_number ?? 'N/A' }}</td>
                                        <td>{{ $enrollment->section->course->credits ?? 'N/A' }}</td>
                                        <td>{{ $enrollment->section->instructor->name ?? 'TBA' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p>You are not currently enrolled in any courses.</p>
                        <a href="{{ route('registration.catalog') }}" class="btn btn-primary">Browse Course Catalog</a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Announcements --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Announcements</h5>
                </div>
                <div class="card-body">
                    @if($announcements->count() > 0)
                        @foreach($announcements as $announcement)
                        <div class="announcement-item">
                            <h6>{{ $announcement->title }}</h6>
                            <p class="text-muted small">{{ $announcement->publish_date->format('M d, Y') }}</p>
                            <p>{{ Str::limit($announcement->content, 100) }}</p>
                        </div>
                        @endforeach
                    @else
                        <p>No announcements at this time.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection