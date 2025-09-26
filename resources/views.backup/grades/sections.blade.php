@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="h3 font-weight-bold text-gray-800">
                <i class="fas fa-list-alt mr-2"></i>All Course Sections
            </h2>
            <p class="text-muted">Manage grades for all course sections</p>
        </div>
        <div class="col-md-4 text-right">
            @if($currentTerm)
                <span class="badge badge-primary p-2">
                    <i class="fas fa-calendar"></i> {{ $currentTerm->name }}
                </span>
            @endif
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Sections
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $sections->total() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chalkboard fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Grades Submitted
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $sections->filter(function($s) { return $s->submission_status['percentage'] == 100; })->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Partially Graded
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $sections->filter(function($s) { 
                                    return $s->submission_status['percentage'] > 0 && 
                                           $s->submission_status['percentage'] < 100; 
                                })->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hourglass-half fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                No Grades
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $sections->filter(function($s) { return $s->submission_status['percentage'] == 0; })->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sections Table --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Course Sections</h6>
            <div>
                <button class="btn btn-sm btn-secondary" onclick="window.print()">
                    <i class="fas fa-print"></i> Print
                </button>
                <a href="{{ route('grades.export', ['all' => true]) }}" class="btn btn-sm btn-success">
                    <i class="fas fa-download"></i> Export All
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Section</th>
                            <th>Instructor</th>
                            <th>Enrolled</th>
                            <th>Graded</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sections as $section)
                            <tr>
                                <td>
                                    <strong>{{ $section->course->code }}</strong><br>
                                    <small class="text-muted">{{ $section->course->name }}</small>
                                </td>
                                <td>{{ $section->section_number }}</td>
                                <td>
                                    @if($section->instructor)
                                        {{ $section->instructor->name }}
                                    @else
                                        <span class="text-muted">Not Assigned</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        {{ $section->submission_status['total'] }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-success">
                                        {{ $section->submission_status['submitted'] }}
                                    </span>
                                </td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar 
                                            @if($section->submission_status['percentage'] == 100) bg-success
                                            @elseif($section->submission_status['percentage'] > 0) bg-warning
                                            @else bg-danger
                                            @endif" 
                                            role="progressbar" 
                                            style="width: {{ $section->submission_status['percentage'] }}%"
                                            aria-valuenow="{{ $section->submission_status['percentage'] }}" 
                                            aria-valuemin="0" 
                                            aria-valuemax="100">
                                            {{ $section->submission_status['percentage'] }}%
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('grades.entry', $section->id) }}" 
                                           class="btn btn-sm btn-primary" 
                                           title="Enter Grades">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('grades.preview', $section->id) }}" 
                                           class="btn btn-sm btn-info" 
                                           title="Preview">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('grades.statistics', $section->id) }}" 
                                           class="btn btn-sm btn-secondary" 
                                           title="Statistics">
                                            <i class="fas fa-chart-bar"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>No sections found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination --}}
            <div class="d-flex justify-content-center">
                {{ $sections->links() }}
            </div>
        </div>
    </div>
</div>
@endsection