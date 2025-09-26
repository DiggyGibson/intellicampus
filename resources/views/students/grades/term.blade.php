{{-- resources/views/student/grades/term.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="h3 font-weight-bold text-gray-800">
                <i class="fas fa-calendar-check mr-2"></i>{{ $term->name }} Grades
            </h2>
            <p class="text-muted">{{ $term->code }} - {{ \Carbon\Carbon::parse($term->start_date)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($term->end_date)->format('M d, Y') }}</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('student.grades.history') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back to History
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header py-3 bg-gradient-primary text-white">
                    <h6 class="m-0 font-weight-bold">Term Summary</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <h4 class="font-weight-bold text-primary">{{ number_format($termGPA, 2) }}</h4>
                            <p class="text-muted">Term GPA</p>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4 class="font-weight-bold text-info">{{ $enrollments->count() }}</h4>
                            <p class="text-muted">Courses</p>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4 class="font-weight-bold text-success">
                                {{ $enrollments->sum(function($e) { return $e->section->course->credits; }) }}
                            </h4>
                            <p class="text-muted">Credits</p>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4 class="font-weight-bold text-warning">
                                {{ $enrollments->filter(function($e) { return !in_array($e->grade, ['F', 'W', null]); })->sum(function($e) { return $e->section->course->credits; }) }}
                            </h4>
                            <p class="text-muted">Credits Earned</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Course Details</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Course</th>
                            <th>Credits</th>
                            <th>Grade</th>
                            <th>Grade Points</th>
                            <th>Quality Points</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($enrollments as $enrollment)
                            <tr>
                                <td>
                                    <strong>{{ $enrollment->section->course->code }}</strong><br>
                                    {{ $enrollment->section->course->name }}
                                </td>
                                <td>{{ $enrollment->section->course->credits }}</td>
                                <td>
                                    <span class="badge badge-{{ $enrollment->grade == 'A' ? 'success' : ($enrollment->grade == 'F' ? 'danger' : 'primary') }} px-3 py-1">
                                        {{ $enrollment->grade ?? 'IP' }}
                                    </span>
                                </td>
                                <td>{{ number_format($enrollment->grade_points ?? 0, 2) }}</td>
                                <td>{{ number_format(($enrollment->grade_points ?? 0) * $enrollment->section->course->credits, 2) }}</td>
                                <td>
                                    @if(isset($enrollment->calculated_grade))
                                        <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#gradeDetail{{ $enrollment->id }}">
                                            <i class="fas fa-info-circle"></i> View
                                        </button>
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
@endsection

{{-- resources/views/student/grades/gpa-calculator.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="h3 font-weight-bold text-gray-800">
                <i class="fas fa-calculator mr-2"></i>GPA Calculator
            </h2>
            <p class="text-muted">Calculate your projected GPA based on expected grades</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('student.gpa') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back to GPA Details
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-plus-circle mr-2"></i>Add Courses
                    </h6>
                </div>
                <div class="card-body">
                    <div class="form-row mb-3">
                        <div class="col-md-5">
                            <input type="text" class="form-control" id="courseName" placeholder="Course Name">
                        </div>
                        <div class="col-md-2">
                            <input type="number" class="form-control" id="courseCredits" placeholder="Credits" min="1" max="6">
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="courseGrade">
                                <option value="">Expected Grade</option>
                                <option value="4.0">A (4.0)</option>
                                <option value="3.7">A- (3.7)</option>
                                <option value="3.3">B+ (3.3)</option>
                                <option value="3.0">B (3.0)</option>
                                <option value="2.7">B- (2.7)</option>
                                <option value="2.3">C+ (2.3)</option>
                                <option value="2.0">C (2.0)</option>
                                <option value="1.7">C- (1.7)</option>
                                <option value="1.3">D+ (1.3)</option>
                                <option value="1.0">D (1.0)</option>
                                <option value="0.0">F (0.0)</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary btn-block" onclick="addCourse()">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table" id="coursesTable">
                            <thead>
                                <tr>
                                    <th>Course</th>
                                    <th>Credits</th>
                                    <th>Grade</th>
                                    <th>Grade Points</th>
                                    <th>Quality Points</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="coursesList">
                                <!-- Courses will be added here dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar mr-2"></i>Calculation Results
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <h3 class="font-weight-bold text-primary" id="projectedGPA">0.00</h3>
                                <p class="text-muted">Projected Term GPA</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h3 class="font-weight-bold text-success" id="newCumulativeGPA">{{ number_format($currentGPA, 2) }}</h3>
                                <p class="text-muted">New Cumulative GPA</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h3 class="font-weight-bold text-info" id="totalCredits">0</h3>
                                <p class="text-muted">Total Credits</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle mr-2"></i>Current Standing
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label>Current Cumulative GPA:</label>
                        <input type="number" class="form-control" id="currentCumulativeGPA" value="{{ $currentGPA }}" step="0.01" min="0" max="4" readonly>
                    </div>
                    <div class="mb-3">
                        <label>Current Total Credits:</label>
                        <input type="number" class="form-control" id="currentTotalCredits" value="{{ $student->total_credits_earned ?? 0 }}" min="0">
                    </div>
                    <button class="btn btn-warning btn-block" onclick="resetCalculator()">
                        <i class="fas fa-redo"></i> Reset Calculator
                    </button>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-lightbulb mr-2"></i>Tips
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="small">
                        <li>Add all courses you're currently taking</li>
                        <li>Select your expected grade for each course</li>
                        <li>The calculator will show your projected GPA</li>
                        <li>Try different grade scenarios to see the impact</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let courses = [];

function addCourse() {
    const name = document.getElementById('courseName').value;
    const credits = parseFloat(document.getElementById('courseCredits').value);
    const gradePoints = parseFloat(document.getElementById('courseGrade').value);
    const gradeText = document.getElementById('courseGrade').options[document.getElementById('courseGrade').selectedIndex].text;
    
    if (!name || !credits || isNaN(gradePoints)) {
        alert('Please fill all fields');
        return;
    }
    
    const course = {
        id: Date.now(),
        name: name,
        credits: credits,
        gradePoints: gradePoints,
        gradeText: gradeText,
        qualityPoints: credits * gradePoints
    };
    
    courses.push(course);
    updateDisplay();
    clearForm();
}

function removeCourse(id) {
    courses = courses.filter(c => c.id !== id);
    updateDisplay();
}

function updateDisplay() {
    const tbody = document.getElementById('coursesList');
    tbody.innerHTML = '';
    
    let totalCredits = 0;
    let totalQualityPoints = 0;
    
    courses.forEach(course => {
        totalCredits += course.credits;
        totalQualityPoints += course.qualityPoints;
        
        tbody.innerHTML += `
            <tr>
                <td>${course.name}</td>
                <td>${course.credits}</td>
                <td>${course.gradeText}</td>
                <td>${course.gradePoints.toFixed(2)}</td>
                <td>${course.qualityPoints.toFixed(2)}</td>
                <td>
                    <button class="btn btn-sm btn-danger" onclick="removeCourse(${course.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    
    const projectedGPA = totalCredits > 0 ? (totalQualityPoints / totalCredits) : 0;
    document.getElementById('projectedGPA').textContent = projectedGPA.toFixed(2);
    document.getElementById('totalCredits').textContent = totalCredits;
    
    // Calculate new cumulative GPA
    const currentGPA = parseFloat(document.getElementById('currentCumulativeGPA').value);
    const currentCredits = parseFloat(document.getElementById('currentTotalCredits').value);
    
    const currentQualityPoints = currentGPA * currentCredits;
    const newTotalCredits = currentCredits + totalCredits;
    const newTotalQualityPoints = currentQualityPoints + totalQualityPoints;
    
    const newCumulativeGPA = newTotalCredits > 0 ? (newTotalQualityPoints / newTotalCredits) : 0;
    document.getElementById('newCumulativeGPA').textContent = newCumulativeGPA.toFixed(2);
}

function clearForm() {
    document.getElementById('courseName').value = '';
    document.getElementById('courseCredits').value = '';
    document.getElementById('courseGrade').value = '';
}

function resetCalculator() {
    courses = [];
    updateDisplay();
    clearForm();
}
</script>
@endpush

{{-- resources/views/student/grades/degree-audit.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="h3 font-weight-bold text-gray-800">
                <i class="fas fa-list-check mr-2"></i>Degree Audit
            </h2>
            <p class="text-muted">Track your progress toward degree completion</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('student.grades') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back to Grades
            </a>
            <button class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print mr-1"></i> Print
            </button>
        </div>
    </div>

    @if($student->program)
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-gradient-primary text-white">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-graduation-cap mr-2"></i>
                {{ $student->program->name }} - {{ $student->program->degree_type }}
            </h6>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-3">
                    <strong>Student ID:</strong> {{ $student->student_id }}
                </div>
                <div class="col-md-3">
                    <strong>Admission:</strong> {{ $student->admission_date ? \Carbon\Carbon::parse($student->admission_date)->format('M Y') : 'N/A' }}
                </div>
                <div class="col-md-3">
                    <strong>Expected Graduation:</strong> {{ $student->expected_graduation ?? 'TBD' }}
                </div>
                <div class="col-md-3">
                    <strong>Academic Advisor:</strong> {{ $student->advisor->name ?? 'Not Assigned' }}
                </div>
            </div>

            <div class="progress mb-4" style="height: 30px;">
                @php
                    $progress = ($student->total_credits_earned ?? 0) / ($student->program->credits_required ?? 120) * 100;
                @endphp
                <div class="progress-bar bg-success" role="progressbar" 
                     style="width: {{ min($progress, 100) }}%"
                     aria-valuenow="{{ $progress }}" 
                     aria-valuemin="0" 
                     aria-valuemax="100">
                    <strong>{{ number_format($progress, 1) }}% Complete</strong>
                </div>
            </div>

            <div class="row text-center">
                <div class="col-md-3">
                    <h4 class="font-weight-bold text-primary">{{ $student->total_credits_earned ?? 0 }}</h4>
                    <p class="text-muted">Credits Earned</p>
                </div>
                <div class="col-md-3">
                    <h4 class="font-weight-bold text-info">{{ $student->program->credits_required }}</h4>
                    <p class="text-muted">Credits Required</p>
                </div>
                <div class="col-md-3">
                    <h4 class="font-weight-bold text-warning">{{ max(0, $student->program->credits_required - ($student->total_credits_earned ?? 0)) }}</h4>
                    <p class="text-muted">Credits Remaining</p>
                </div>
                <div class="col-md-3">
                    <h4 class="font-weight-bold text-success">{{ number_format($student->cumulative_gpa ?? $student->gpa ?? 0, 2) }}</h4>
                    <p class="text-muted">Cumulative GPA</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-tasks mr-2"></i>Degree Requirements
            </h6>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle mr-2"></i>
                This is a preliminary degree audit. Please consult with your academic advisor for official degree planning.
            </div>

            {{-- This would typically show detailed requirement tracking --}}
            <h6 class="font-weight-bold mb-3">General Education Requirements</h6>
            <div class="table-responsive mb-4">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Requirement</th>
                            <th>Credits Required</th>
                            <th>Credits Completed</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>English Composition</td>
                            <td>6</td>
                            <td>6</td>
                            <td><span class="badge badge-success">Complete</span></td>
                        </tr>
                        <tr>
                            <td>Mathematics</td>
                            <td>6</td>
                            <td>3</td>
                            <td><span class="badge badge-warning">In Progress</span></td>
                        </tr>
                        <tr>
                            <td>Natural Sciences</td>
                            <td>8</td>
                            <td>8</td>
                            <td><span class="badge badge-success">Complete</span></td>
                        </tr>
                        <tr>
                            <td>Social Sciences</td>
                            <td>6</td>
                            <td>0</td>
                            <td><span class="badge badge-secondary">Not Started</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <h6 class="font-weight-bold mb-3">Major Requirements</h6>
            <p class="text-muted">Major-specific course requirements will be displayed here once configured.</p>
        </div>
    </div>
    @else
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        No program information found. Please contact your academic advisor.
    </div>
    @endif
</div>
@endsection