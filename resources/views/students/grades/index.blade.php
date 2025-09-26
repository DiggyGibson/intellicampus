{{-- resources/views/student/grades/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    {{-- Header Section --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="h3 font-weight-bold text-gray-800">
                <i class="fas fa-graduation-cap mr-2"></i>My Academic Record
            </h2>
            <p class="text-muted">View your grades, GPA, and academic progress</p>
        </div>
        <div class="col-md-4 text-right">
            <div class="btn-group" role="group">
                <a href="{{ route('student.grades.current') }}" class="btn btn-primary">
                    <i class="fas fa-calendar-alt mr-1"></i> Current Term
                </a>
                <a href="{{ route('student.grades.history') }}" class="btn btn-secondary">
                    <i class="fas fa-history mr-1"></i> History
                </a>
                <a href="{{ route('transcripts.index') }}" class="btn btn-info">
                    <i class="fas fa-file-alt mr-1"></i> Transcript
                </a>
            </div>
        </div>
    </div>

    {{-- GPA Summary Cards --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Current Term GPA
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($currentGPA, 2) }}
                            </div>
                            @if($currentTerm)
                                <small class="text-muted">{{ $currentTerm->name }}</small>
                            @endif
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
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
                                Cumulative GPA
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($cumulativeGPA, 2) }}
                            </div>
                            <small class="text-muted">Overall</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-award fa-2x text-gray-300"></i>
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
                                Credits Earned
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $student->total_credits_earned ?? 0 }}
                            </div>
                            <small class="text-muted">of {{ $student->program->credits_required ?? 120 }}</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-book fa-2x text-gray-300"></i>
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
                                Academic Standing
                            </div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                @if($student->academic_standing == 'excellent')
                                    <span class="text-success">Excellent</span>
                                @elseif($student->academic_standing == 'good')
                                    <span class="text-primary">Good Standing</span>
                                @elseif($student->academic_standing == 'probation')
                                    <span class="text-warning">Probation</span>
                                @else
                                    <span class="text-danger">{{ ucfirst($student->academic_standing ?? 'Pending') }}</span>
                                @endif
                            </div>
                            @if($cumulativeGPA >= 3.5)
                                <small class="text-success"><i class="fas fa-star"></i> Dean's List Eligible</small>
                            @endif
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-graduate fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Current Term Courses --}}
    @if($currentTerm && $enrollments->where('section.term_id', $currentTerm->id)->count() > 0)
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-book-open mr-2"></i>Current Term Courses - {{ $currentTerm->name }}
            </h6>
            <a href="{{ route('student.grades.current') }}" class="btn btn-sm btn-primary">
                View Details <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Course Code</th>
                            <th>Course Name</th>
                            <th>Credits</th>
                            <th>Instructor</th>
                            <th>Current Grade</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($enrollments->where('section.term_id', $currentTerm->id) as $enrollment)
                            <tr>
                                <td>
                                    <strong>{{ $enrollment->section->course->code }}</strong>
                                    <br>
                                    <small class="text-muted">Section {{ $enrollment->section->section_number }}</small>
                                </td>
                                <td>{{ $enrollment->section->course->name }}</td>
                                <td>{{ $enrollment->section->course->credits }}</td>
                                <td>{{ $enrollment->section->instructor->name ?? 'TBA' }}</td>
                                <td>
                                    @if($enrollment->grade)
                                        <span class="badge badge-{{ $enrollment->grade == 'A' ? 'success' : ($enrollment->grade == 'F' ? 'danger' : 'primary') }} px-3 py-1">
                                            {{ $enrollment->grade }}
                                        </span>
                                    @else
                                        <span class="badge badge-secondary px-3 py-1">In Progress</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ $enrollment->enrollment_status == 'enrolled' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($enrollment->enrollment_status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Quick Actions --}}
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-calculator mr-2"></i>GPA Calculator
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted">Calculate your projected GPA based on expected grades.</p>
                    <a href="{{ route('student.gpa.calculator') }}" class="btn btn-primary">
                        <i class="fas fa-calculator mr-2"></i>Open Calculator
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-graduation-cap mr-2"></i>Degree Progress
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted">Track your progress toward degree completion.</p>
                    <div class="progress mb-3" style="height: 25px;">
                        @php
                            $progress = ($student->total_credits_earned ?? 0) / ($student->program->credits_required ?? 120) * 100;
                        @endphp
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: {{ min($progress, 100) }}%"
                             aria-valuenow="{{ $progress }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            {{ number_format($progress, 1) }}%
                        </div>
                    </div>
                    <a href="{{ route('student.degree.audit') }}" class="btn btn-info">
                        <i class="fas fa-list-check mr-2"></i>View Degree Audit
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Grades --}}
    @if($enrollments->where('grade', '!=', null)->count() > 0)
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-clock mr-2"></i>Recently Posted Grades
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Term</th>
                            <th>Course</th>
                            <th>Credits</th>
                            <th>Grade</th>
                            <th>Grade Points</th>
                            <th>Quality Points</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($enrollments->where('grade', '!=', null)->sortByDesc('updated_at')->take(5) as $enrollment)
                            <tr>
                                <td>{{ $enrollment->section->term->name }}</td>
                                <td>
                                    <strong>{{ $enrollment->section->course->code }}</strong> - 
                                    {{ $enrollment->section->course->name }}
                                </td>
                                <td>{{ $enrollment->section->course->credits }}</td>
                                <td>
                                    <span class="badge badge-{{ $enrollment->grade == 'A' ? 'success' : ($enrollment->grade == 'F' ? 'danger' : 'primary') }} px-3 py-1">
                                        {{ $enrollment->grade }}
                                    </span>
                                </td>
                                <td>{{ number_format($enrollment->grade_points ?? 0, 2) }}</td>
                                <td>{{ number_format(($enrollment->grade_points ?? 0) * $enrollment->section->course->credits, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="text-center mt-3">
                    <a href="{{ route('student.grades.history') }}" class="btn btn-outline-primary">
                        View Complete Grade History <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    // Add any JavaScript for grade calculations or interactive features
</script>
@endpush