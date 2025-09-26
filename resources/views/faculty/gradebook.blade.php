@extends('layouts.app')

@section('title', 'Gradebook')

@section('breadcrumb')
    <a href="{{ route('faculty.dashboard') }}">Faculty Dashboard</a>
    <i class="fas fa-chevron-right"></i>
    <a href="{{ route('faculty.courses') }}">My Courses</a>
    <i class="fas fa-chevron-right"></i>
    <a href="{{ route('faculty.section.details', $section->id) }}">Section Details</a>
    <i class="fas fa-chevron-right"></i>
    <span>Gradebook</span>
@endsection

@section('page-actions')
    <button onclick="exportGrades()" class="btn btn-success me-2">
        <i class="fas fa-download me-1"></i> Export
    </button>
    <button onclick="importGrades()" class="btn btn-info me-2">
        <i class="fas fa-upload me-1"></i> Import
    </button>
    <a href="{{ route('faculty.section.details', $section->id) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Section
    </a>
@endsection

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="mb-4">
        <h2 class="fw-bold text-dark mb-1">Gradebook</h2>
        <p class="text-muted">
            {{ $section->course->course_code ?? '' }} - Section {{ $section->section_number ?? '' }}
            | {{ $section->course->title ?? '' }}
        </p>
    </div>

    <!-- Grade Components Setup -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-gradient-primary text-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Grade Components</h5>
                <button onclick="addComponent()" class="btn btn-sm btn-light">
                    <i class="fas fa-plus me-1"></i> Add Component
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                @php
                    $components = [
                        ['name' => 'Assignments', 'weight' => 30, 'count' => 5, 'color' => 'primary'],
                        ['name' => 'Midterm Exam', 'weight' => 25, 'count' => 1, 'color' => 'info'],
                        ['name' => 'Final Exam', 'weight' => 30, 'count' => 1, 'color' => 'danger'],
                        ['name' => 'Participation', 'weight' => 15, 'count' => 1, 'color' => 'success'],
                    ];
                @endphp
                @foreach($components as $component)
                    <div class="col-md-3 mb-3">
                        <div class="card border-{{ $component['color'] }}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="card-title mb-0">{{ $component['name'] }}</h6>
                                    <button onclick="editComponent('{{ $component['name'] }}')" class="btn btn-sm btn-link p-0">
                                        <i class="fas fa-edit text-muted"></i>
                                    </button>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-{{ $component['color'] }}">{{ $component['weight'] }}%</span>
                                    <small class="text-muted">{{ $component['count'] }} {{ Str::plural('item', $component['count']) }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="alert alert-{{ array_sum(array_column($components, 'weight')) == 100 ? 'success' : 'warning' }} mb-0">
                <i class="fas fa-{{ array_sum(array_column($components, 'weight')) == 100 ? 'check-circle' : 'exclamation-triangle' }} me-2"></i>
                Total Weight: <strong>{{ array_sum(array_column($components, 'weight')) }}%</strong>
                @if(array_sum(array_column($components, 'weight')) != 100)
                    - Must equal 100%
                @endif
            </div>
        </div>
    </div>

    <!-- Grading Scale -->
    <div class="alert alert-info d-flex justify-content-between align-items-center mb-4">
        <div>
            <i class="fas fa-info-circle me-2"></i>
            <strong>Grading Scale:</strong> A (90-100%) | B (80-89%) | C (70-79%) | D (60-69%) | F (Below 60%)
        </div>
        <button onclick="changeGradingScale()" class="btn btn-sm btn-outline-info">
            <i class="fas fa-edit me-1"></i> Change Scale
        </button>
    </div>

    <!-- Gradebook Table -->
    <div class="card shadow-sm">
        <form method="POST" action="{{ route('faculty.gradebook.save', $section->id) }}" id="gradesForm">
            @csrf
            
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Student Grades</h5>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="showPercentages" onchange="togglePercentages(this)">
                            <label class="form-check-label small" for="showPercentages">
                                Show as Percentages
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="showLetterGrades" onchange="toggleLetterGrades(this)" checked>
                            <label class="form-check-label small" for="showLetterGrades">
                                Show Letter Grades
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            @if($students->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="sticky-column bg-light">Student</th>
                                <th class="text-center">
                                    Assignments
                                    <br><small class="text-muted fw-normal">(30%)</small>
                                </th>
                                <th class="text-center">
                                    Midterm
                                    <br><small class="text-muted fw-normal">(25%)</small>
                                </th>
                                <th class="text-center">
                                    Final
                                    <br><small class="text-muted fw-normal">(30%)</small>
                                </th>
                                <th class="text-center">
                                    Participation
                                    <br><small class="text-muted fw-normal">(15%)</small>
                                </th>
                                <th class="text-center table-warning">
                                    Current<br>Average
                                </th>
                                <th class="text-center table-success">
                                    Final<br>Grade
                                </th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $index => $student)
                                @php
                                    $grades = [
                                        'assignments' => rand(75, 95),
                                        'midterm' => rand(70, 90),
                                        'final' => rand(65, 95),
                                        'participation' => rand(80, 100),
                                    ];
                                    $average = ($grades['assignments'] * 0.3) + 
                                              ($grades['midterm'] * 0.25) + 
                                              ($grades['final'] * 0.3) + 
                                              ($grades['participation'] * 0.15);
                                    $letterGrade = $average >= 90 ? 'A' : 
                                                  ($average >= 80 ? 'B' : 
                                                  ($average >= 70 ? 'C' : 
                                                  ($average >= 60 ? 'D' : 'F')));
                                    $gradeColor = match($letterGrade) {
                                        'A' => 'success',
                                        'B' => 'primary',
                                        'C' => 'warning',
                                        'D' => 'orange',
                                        'F' => 'danger',
                                        default => 'secondary'
                                    };
                                @endphp
                                <tr>
                                    <td class="sticky-column bg-white">
                                        <div class="d-flex align-items-center">
                                            @if($student->profile_photo)
                                                <img src="{{ $student->profile_photo }}" 
                                                     alt="{{ $student->first_name }}"
                                                     class="rounded-circle me-2"
                                                     style="width: 32px; height: 32px; object-fit: cover;">
                                            @else
                                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center me-2"
                                                     style="width: 32px; height: 32px;">
                                                    <span class="text-white small">
                                                        {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                                    </span>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-bold">{{ $student->last_name }}, {{ $student->first_name }}</div>
                                                <small class="text-muted">{{ $student->student_id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <input type="number" 
                                               name="grades[{{ $student->id }}][assignments]" 
                                               value="{{ $grades['assignments'] }}"
                                               min="0" max="100" step="0.1"
                                               class="form-control form-control-sm text-center mx-auto"
                                               style="width: 80px;"
                                               onchange="calculateAverage({{ $student->id }})">
                                    </td>
                                    <td class="text-center">
                                        <input type="number" 
                                               name="grades[{{ $student->id }}][midterm]" 
                                               value="{{ $grades['midterm'] }}"
                                               min="0" max="100" step="0.1"
                                               class="form-control form-control-sm text-center mx-auto"
                                               style="width: 80px;"
                                               onchange="calculateAverage({{ $student->id }})">
                                    </td>
                                    <td class="text-center">
                                        <input type="number" 
                                               name="grades[{{ $student->id }}][final]" 
                                               value="{{ $grades['final'] }}"
                                               min="0" max="100" step="0.1"
                                               class="form-control form-control-sm text-center mx-auto"
                                               style="width: 80px;"
                                               onchange="calculateAverage({{ $student->id }})">
                                    </td>
                                    <td class="text-center">
                                        <input type="number" 
                                               name="grades[{{ $student->id }}][participation]" 
                                               value="{{ $grades['participation'] }}"
                                               min="0" max="100" step="0.1"
                                               class="form-control form-control-sm text-center mx-auto"
                                               style="width: 80px;"
                                               onchange="calculateAverage({{ $student->id }})">
                                    </td>
                                    <td class="text-center table-warning">
                                        <span id="average-{{ $student->id }}" class="fw-bold">
                                            {{ number_format($average, 1) }}%
                                        </span>
                                    </td>
                                    <td class="text-center table-success">
                                        <span id="letter-{{ $student->id }}" class="badge bg-{{ $gradeColor }}">
                                            {{ $letterGrade }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" onclick="viewDetails({{ $student->id }})" 
                                                class="btn btn-sm btn-link p-1" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Grade Summary and Actions -->
                <div class="card-footer bg-light">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="row text-center">
                                <div class="col-6 col-md-3">
                                    <small class="text-muted">Class Average</small>
                                    <div class="fw-bold">82.5%</div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <small class="text-muted">Highest</small>
                                    <div class="fw-bold text-success">95.2%</div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <small class="text-muted">Lowest</small>
                                    <div class="fw-bold text-danger">68.4%</div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <small class="text-muted">Passing</small>
                                    <div class="fw-bold text-primary">{{ $students->count() - 2 }}/{{ $students->count() }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end mt-3 mt-md-0">
                            <button type="button" onclick="previewGrades()" class="btn btn-secondary">
                                <i class="fas fa-eye me-1"></i> Preview
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Grades
                            </button>
                            <button type="button" onclick="postGrades()" class="btn btn-success">
                                <i class="fas fa-check me-1"></i> Post Final
                            </button>
                        </div>
                    </div>
                </div>
            @else
                <div class="card-body text-center py-5">
                    <i class="fas fa-user-graduate fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Students Enrolled</h5>
                    <p class="text-muted">No students are currently enrolled in this section.</p>
                </div>
            @endif
        </form>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
}
.sticky-column {
    position: sticky;
    left: 0;
    z-index: 10;
}
.bg-orange {
    background-color: #f59e0b !important;
}
</style>

<script>
function calculateAverage(studentId) {
    const row = document.querySelector(`[id="average-${studentId}"]`).closest('tr');
    const assignments = parseFloat(row.querySelector('input[name*="assignments"]').value) || 0;
    const midterm = parseFloat(row.querySelector('input[name*="midterm"]').value) || 0;
    const final = parseFloat(row.querySelector('input[name*="final"]').value) || 0;
    const participation = parseFloat(row.querySelector('input[name*="participation"]').value) || 0;
    
    const average = (assignments * 0.3) + (midterm * 0.25) + (final * 0.3) + (participation * 0.15);
    
    document.getElementById(`average-${studentId}`).textContent = average.toFixed(1) + '%';
    
    // Update letter grade
    let letterGrade = 'F';
    let gradeColor = 'danger';
    if (average >= 90) { letterGrade = 'A'; gradeColor = 'success'; }
    else if (average >= 80) { letterGrade = 'B'; gradeColor = 'primary'; }
    else if (average >= 70) { letterGrade = 'C'; gradeColor = 'warning'; }
    else if (average >= 60) { letterGrade = 'D'; gradeColor = 'orange'; }
    
    const letterElement = document.getElementById(`letter-${studentId}`);
    letterElement.textContent = letterGrade;
    letterElement.className = `badge bg-${gradeColor}`;
}

function addComponent() {
    alert('Grade component editor will be implemented soon.');
}

function editComponent(name) {
    alert(`Edit ${name} component - coming soon.`);
}

function changeGradingScale() {
    alert('Grading scale editor will be implemented soon.');
}

function togglePercentages(checkbox) {
    // Implementation for toggling percentage view
}

function toggleLetterGrades(checkbox) {
    // Implementation for toggling letter grade view
}

function viewDetails(studentId) {
    alert(`View detailed grades for student ${studentId} - coming soon.`);
}

function previewGrades() {
    alert('Grade preview functionality will be implemented soon.');
}

function postGrades() {
    if (confirm('Are you sure you want to post final grades? This action cannot be undone.')) {
        alert('Final grade posting will be implemented soon.');
    }
}

function exportGrades() {
    if (confirm('Export gradebook as CSV?')) {
        alert('Export functionality will be implemented soon.');
    }
}

function importGrades() {
    alert('Import grades from CSV - coming soon.');
}
</script>
@endsection