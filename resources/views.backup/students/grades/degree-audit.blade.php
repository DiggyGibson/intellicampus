@extends('layouts.app')

@section('title', 'Degree Audit')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">Degree Audit</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('student.grades') }}">Grades</a></li>
                    <li class="breadcrumb-item active">Degree Audit</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Student Information -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Student:</strong> {{ Auth::user()->name ?? 'N/A' }}<br>
                            <strong>ID:</strong> {{ isset($student) ? $student->student_id : 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Program:</strong> {{ isset($student) && $student->program ? $student->program->name : 'Not Declared' }}<br>
                            <strong>Major:</strong> {{ isset($student) ? $student->major : 'Not Declared' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Catalog Year:</strong> {{ $catalogYear ?? date('Y') }}<br>
                            <strong>Expected Graduation:</strong> {{ isset($student) && $student->expected_graduation_date ? $student->expected_graduation_date->format('Y') : 'TBD' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Credits Earned:</strong> {{ $creditsEarned ?? 0 }}<br>
                            <strong>Cumulative GPA:</strong> {{ number_format($cumulativeGPA ?? 0, 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Degree Progress Overview -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Degree Progress</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Overall Progress</span>
                            <span>{{ $creditsEarned ?? 0 }} / {{ $totalCreditsRequired ?? 120 }} Credits</span>
                        </div>
                        <div class="progress" style="height: 25px;">
                            @php
                                $progressPercent = ($totalCreditsRequired ?? 120) > 0 ? (($creditsEarned ?? 0) / ($totalCreditsRequired ?? 120)) * 100 : 0;
                            @endphp
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: {{ min($progressPercent, 100) }}%">
                                {{ round($progressPercent) }}%
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-4">
                            <h6>General Education</h6>
                            <div class="progress mb-2">
                                <div class="progress-bar bg-info" style="width: {{ $genEdProgress ?? 0 }}%">
                                    {{ $genEdProgress ?? 0 }}%
                                </div>
                            </div>
                            <small>{{ $genEdCompleted ?? 0 }} / {{ $genEdRequired ?? 45 }} Credits</small>
                        </div>
                        <div class="col-md-4">
                            <h6>Major Requirements</h6>
                            <div class="progress mb-2">
                                <div class="progress-bar bg-warning" style="width: {{ $majorProgress ?? 0 }}%">
                                    {{ $majorProgress ?? 0 }}%
                                </div>
                            </div>
                            <small>{{ $majorCompleted ?? 0 }} / {{ $majorRequired ?? 60 }} Credits</small>
                        </div>
                        <div class="col-md-4">
                            <h6>Electives</h6>
                            <div class="progress mb-2">
                                <div class="progress-bar bg-secondary" style="width: {{ $electiveProgress ?? 0 }}%">
                                    {{ $electiveProgress ?? 0 }}%
                                </div>
                            </div>
                            <small>{{ $electiveCompleted ?? 0 }} / {{ $electiveRequired ?? 15 }} Credits</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- GPA Requirements -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">GPA Requirements</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Cumulative GPA:</span>
                            <span class="{{ ($cumulativeGPA ?? 0) >= 2.0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($cumulativeGPA ?? 0, 2) }}
                            </span>
                        </div>
                        <small class="text-muted">Minimum Required: 2.00</small>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Major GPA:</span>
                            <span class="{{ ($majorGPA ?? 0) >= 2.0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($majorGPA ?? 0, 2) }}
                            </span>
                        </div>
                        <small class="text-muted">Minimum Required: 2.00</small>
                    </div>

                    <hr>
                    
                    <div class="alert {{ ($cumulativeGPA ?? 0) >= 2.0 ? 'alert-success' : 'alert-warning' }} mb-0">
                        <i class="fas {{ ($cumulativeGPA ?? 0) >= 2.0 ? 'fa-check-circle' : 'fa-exclamation-circle' }}"></i>
                        {{ ($cumulativeGPA ?? 0) >= 2.0 ? 'Meeting GPA Requirements' : 'GPA Below Requirement' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Requirements Breakdown -->
    <div class="row">
        <!-- General Education Requirements -->
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-book"></i> General Education Requirements
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $genEdCategories = [
                            'English Composition' => ['required' => 6, 'completed' => 6, 'courses' => ['ENG 101', 'ENG 102']],
                            'Mathematics' => ['required' => 3, 'completed' => 3, 'courses' => ['MATH 151']],
                            'Natural Sciences' => ['required' => 8, 'completed' => 4, 'courses' => ['BIO 101']],
                            'Social Sciences' => ['required' => 9, 'completed' => 6, 'courses' => ['PSY 101', 'SOC 101']],
                            'Humanities' => ['required' => 9, 'completed' => 0, 'courses' => []],
                            'Fine Arts' => ['required' => 3, 'completed' => 0, 'courses' => []],
                            'Foreign Language' => ['required' => 6, 'completed' => 0, 'courses' => []],
                        ];
                    @endphp

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Required</th>
                                    <th>Completed</th>
                                    <th>Status</th>
                                    <th>Courses Taken</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($genEdCategories as $category => $details)
                                <tr>
                                    <td><strong>{{ $category }}</strong></td>
                                    <td>{{ $details['required'] }} credits</td>
                                    <td>{{ $details['completed'] }} credits</td>
                                    <td>
                                        @if($details['completed'] >= $details['required'])
                                            <span class="badge badge-success"><i class="fas fa-check"></i> Complete</span>
                                        @elseif($details['completed'] > 0)
                                            <span class="badge badge-warning">In Progress</span>
                                        @else
                                            <span class="badge badge-secondary">Not Started</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(count($details['courses']) > 0)
                                            {{ implode(', ', $details['courses']) }}
                                        @else
                                            <em class="text-muted">None</em>
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

        <!-- Major Requirements -->
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-graduation-cap"></i> Major Requirements
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $majorCourses = [
                            'Core Courses' => [
                                ['code' => 'CS 101', 'name' => 'Introduction to Programming', 'credits' => 3, 'grade' => 'A', 'status' => 'completed'],
                                ['code' => 'CS 201', 'name' => 'Data Structures', 'credits' => 3, 'grade' => 'B+', 'status' => 'completed'],
                                ['code' => 'CS 301', 'name' => 'Algorithms', 'credits' => 3, 'grade' => '-', 'status' => 'in-progress'],
                                ['code' => 'CS 302', 'name' => 'Database Systems', 'credits' => 3, 'grade' => '-', 'status' => 'planned'],
                                ['code' => 'CS 401', 'name' => 'Software Engineering', 'credits' => 3, 'grade' => '-', 'status' => 'planned'],
                            ],
                            'Major Electives' => [
                                ['code' => 'CS 350', 'name' => 'Web Development', 'credits' => 3, 'grade' => 'A-', 'status' => 'completed'],
                                ['code' => 'CS 360', 'name' => 'Mobile Development', 'credits' => 3, 'grade' => '-', 'status' => 'planned'],
                                ['code' => 'CS 370', 'name' => 'Machine Learning', 'credits' => 3, 'grade' => '-', 'status' => 'planned'],
                            ]
                        ];
                    @endphp

                    @foreach($majorCourses as $category => $courses)
                    <h6 class="font-weight-bold mb-3">{{ $category }}</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Course</th>
                                    <th>Credits</th>
                                    <th>Grade</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($courses as $course)
                                <tr>
                                    <td>
                                        <strong>{{ $course['code'] }}</strong> - {{ $course['name'] }}
                                    </td>
                                    <td>{{ $course['credits'] }}</td>
                                    <td>
                                        @if($course['status'] == 'completed')
                                            <span class="badge badge-light">{{ $course['grade'] }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($course['status'] == 'completed')
                                            <span class="badge badge-success"><i class="fas fa-check"></i> Completed</span>
                                        @elseif($course['status'] == 'in-progress')
                                            <span class="badge badge-primary"><i class="fas fa-clock"></i> In Progress</span>
                                        @else
                                            <span class="badge badge-secondary"><i class="fas fa-calendar"></i> Planned</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Remaining Requirements -->
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-tasks"></i> Remaining Requirements
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="font-weight-bold">Still Needed</h6>
                            <ul>
                                <li>Natural Sciences: 4 more credits</li>
                                <li>Humanities: 9 credits</li>
                                <li>Fine Arts: 3 credits</li>
                                <li>Foreign Language: 6 credits</li>
                                <li>Major Core: CS 302, CS 401</li>
                                <li>Major Electives: 6 credits</li>
                                <li>Free Electives: 15 credits</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="font-weight-bold">Recommendations</h6>
                            <div class="alert alert-info">
                                <i class="fas fa-lightbulb"></i> <strong>Next Semester:</strong><br>
                                Consider taking CS 302 (Database Systems) and fulfilling Humanities requirements.
                            </div>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> <strong>Important:</strong><br>
                                Foreign Language requirement must be started soon to complete sequence on time.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Information -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Additional Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <h6>Graduation Requirements</h6>
                            <ul class="small">
                                <li>Minimum 120 credits</li>
                                <li>2.00 cumulative GPA</li>
                                <li>2.00 major GPA</li>
                                <li>Residency requirement met</li>
                            </ul>
                        </div>
                        <div class="col-md-3">
                            <h6>Academic Policies</h6>
                            <ul class="small">
                                <li>Maximum 18 credits per semester</li>
                                <li>Pass/Fail limit: 12 credits</li>
                                <li>Transfer credit limit: 60</li>
                                <li>Grade replacement available</li>
                            </ul>
                        </div>
                        <div class="col-md-3">
                            <h6>Important Dates</h6>
                            <ul class="small">
                                <li>Apply for graduation: 2 semesters prior</li>
                                <li>Degree audit review: Each semester</li>
                                <li>Advisor meeting: Required yearly</li>
                            </ul>
                        </div>
                        <div class="col-md-3">
                            <h6>Actions</h6>
                            <button class="btn btn-primary btn-sm btn-block mb-2" onclick="window.print();">
                                <i class="fas fa-print"></i> Print Audit
                            </button>
                            <button class="btn btn-secondary btn-sm btn-block mb-2">
                                <i class="fas fa-download"></i> Download PDF
                            </button>
                            <button class="btn btn-info btn-sm btn-block">
                                <i class="fas fa-envelope"></i> Email to Advisor
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    @media print {
        .btn, .breadcrumb, nav {
            display: none !important;
        }
        .card {
            border: 1px solid #000 !important;
        }
    }
</style>
@endpush
@endsection