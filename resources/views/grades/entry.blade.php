@extends('layouts.app')

@section('title', 'Grade Entry - ' . $section->course->code)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Grade Entry</h1>
            <p class="text-muted">
                {{ $section->course->code }} - {{ $section->course->title }}
                | Section {{ $section->section_code }}
                | {{ $section->term->name }}
            </p>
        </div>
        <div>
            <button type="button" class="btn btn-outline-secondary" onclick="saveProgress()">
                <i class="fas fa-save"></i> Save Progress
            </button>
            <a href="{{ route('grades.preview', $section->id) }}" class="btn btn-primary">
                <i class="fas fa-eye"></i> Preview & Submit
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Grade Components Setup -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Grade Components</h5>
                <a href="{{ route('grades.components', $section->id) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-cog"></i> Manage Components
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($components as $component)
                <div class="col-md-4 col-lg-3 mb-3">
                    <div class="border rounded p-3">
                        <h6 class="mb-1">{{ $component->name }}</h6>
                        <small class="text-muted">{{ ucfirst($component->type) }}</small>
                        <div class="mt-2">
                            <span class="badge bg-primary">{{ $component->weight }}%</span>
                            <span class="badge bg-secondary">{{ $component->max_points }} pts</span>
                        </div>
                        @if($component->due_date)
                        <small class="d-block mt-2 text-muted">
                            Due: {{ \Carbon\Carbon::parse($component->due_date)->format('M d, Y') }}
                        </small>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Grade Entry Methods -->
    <div class="card mb-4">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#manual-entry">
                        <i class="fas fa-keyboard"></i> Manual Entry
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#bulk-upload">
                        <i class="fas fa-file-excel"></i> Excel Upload
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#grade-stats">
                        <i class="fas fa-chart-bar"></i> Statistics
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                <!-- Manual Entry Tab -->
                <div class="tab-pane fade show active" id="manual-entry">
                    <!-- Component Selection -->
                    <div class="mb-4">
                        <label for="component-select" class="form-label">Select Component to Grade:</label>
                        <select id="component-select" class="form-select" onchange="loadComponentGrades()">
                            <option value="">-- Select Component --</option>
                            @foreach($components as $component)
                            <option value="{{ $component->id }}" data-max="{{ $component->max_points }}">
                                {{ $component->name }} ({{ $component->weight }}% - {{ $component->max_points }} pts)
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Grade Entry Table -->
                    <div id="grade-entry-table" style="display: none;">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="100">Student ID</th>
                                        <th>Student Name</th>
                                        <th width="120">Points</th>
                                        <th width="100">Percentage</th>
                                        <th width="80">Grade</th>
                                        <th width="200">Comments</th>
                                        <th width="100">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($enrollments as $enrollment)
                                    <tr data-enrollment="{{ $enrollment->enrollment_id }}">
                                        <td>{{ $enrollment->student->student_id }}</td>
                                        <td>
                                            {{ $enrollment->student->last_name }}, 
                                            {{ $enrollment->student->first_name }}
                                        </td>
                                        <td>
                                            <input type="number" 
                                                   class="form-control grade-input" 
                                                   id="points-{{ $enrollment->enrollment_id }}"
                                                   data-enrollment="{{ $enrollment->enrollment_id }}"
                                                   min="0" 
                                                   step="0.01"
                                                   onchange="calculateGrade(this)">
                                        </td>
                                        <td>
                                            <span id="percentage-{{ $enrollment->enrollment_id }}">-</span>
                                        </td>
                                        <td>
                                            <span id="letter-{{ $enrollment->enrollment_id }}" 
                                                  class="badge bg-secondary">-</span>
                                        </td>
                                        <td>
                                            <input type="text" 
                                                   class="form-control form-control-sm"
                                                   id="comments-{{ $enrollment->enrollment_id }}"
                                                   placeholder="Optional">
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-success" 
                                                    onclick="saveGrade({{ $enrollment->enrollment_id }})">
                                                <i class="fas fa-save"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger"
                                                    onclick="clearGrade({{ $enrollment->enrollment_id }})">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Quick Actions -->
                        <div class="mt-3">
                            <button class="btn btn-primary" onclick="saveAllGrades()">
                                <i class="fas fa-save"></i> Save All Grades
                            </button>
                            <button class="btn btn-outline-secondary" onclick="applyToAll()">
                                <i class="fas fa-copy"></i> Apply Same Grade to All
                            </button>
                            <button class="btn btn-outline-info" onclick="calculateStats()">
                                <i class="fas fa-calculator"></i> Calculate Statistics
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Excel Upload Tab -->
                <div class="tab-pane fade" id="bulk-upload">
                    <form action="{{ route('grades.upload', $section->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="upload-component" class="form-label">Select Component:</label>
                            <select name="component_id" id="upload-component" class="form-select" required>
                                <option value="">-- Select Component --</option>
                                @foreach($components as $component)
                                <option value="{{ $component->id }}">
                                    {{ $component->name }} ({{ $component->weight }}%)
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="file" class="form-label">Excel File:</label>
                            <input type="file" name="file" id="file" class="form-control" 
                                   accept=".xlsx,.xls,.csv" required>
                            <small class="text-muted">
                                Accepted formats: Excel (.xlsx, .xls) or CSV (.csv)
                            </small>
                        </div>

                        <div class="alert alert-info">
                            <h6>Instructions:</h6>
                            <ol class="mb-0">
                                <li>Download the template for the selected component</li>
                                <li>Enter grades in the 'Points' column</li>
                                <li>Save the file and upload it here</li>
                                <li>Review the imported grades before final submission</li>
                            </ol>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload"></i> Upload Grades
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="downloadTemplate()">
                                <i class="fas fa-download"></i> Download Template
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Statistics Tab -->
                <div class="tab-pane fade" id="grade-stats">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Overall Class Performance</h5>
                            <table class="table">
                                <tr>
                                    <td>Class Average:</td>
                                    <td><strong>{{ number_format($enrollments->avg('calculated_grade.percentage') ?? 0, 2) }}%</strong></td>
                                </tr>
                                <tr>
                                    <td>Highest Grade:</td>
                                    <td><strong>{{ number_format($enrollments->max('calculated_grade.percentage') ?? 0, 2) }}%</strong></td>
                                </tr>
                                <tr>
                                    <td>Lowest Grade:</td>
                                    <td><strong>{{ number_format($enrollments->min('calculated_grade.percentage') ?? 0, 2) }}%</strong></td>
                                </tr>
                                <tr>
                                    <td>Students Graded:</td>
                                    <td><strong>{{ $enrollments->count() }}</strong></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Grade Distribution</h5>
                            <canvas id="gradeDistributionChart"></canvas>
                        </div>
                    </div>

                    <!-- Student Grade Summary -->
                    <div class="mt-4">
                        <h5>Current Grade Summary</h5>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Current %</th>
                                        <th>Letter Grade</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($enrollments as $enrollment)
                                    <tr>
                                        <td>
                                            {{ $enrollment->student->last_name }}, 
                                            {{ $enrollment->student->first_name }}
                                        </td>
                                        <td>{{ number_format($enrollment->calculated_grade['percentage'] ?? 0, 2) }}%</td>
                                        <td>
                                            <span class="badge bg-{{ $enrollment->letter_grade == 'F' ? 'danger' : ($enrollment->letter_grade >= 'B' ? 'success' : 'warning') }}">
                                                {{ $enrollment->letter_grade ?? '-' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if(($enrollment->calculated_grade['percentage'] ?? 0) < 60)
                                                <span class="text-danger">At Risk</span>
                                            @else
                                                <span class="text-success">Passing</span>
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
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Global variables
let currentComponent = null;
let maxPoints = 0;

// Load grades for selected component
function loadComponentGrades() {
    const select = document.getElementById('component-select');
    const selectedOption = select.options[select.selectedIndex];
    
    if (!selectedOption.value) {
        document.getElementById('grade-entry-table').style.display = 'none';
        return;
    }
    
    currentComponent = selectedOption.value;
    maxPoints = parseFloat(selectedOption.dataset.max);
    
    // Show the table
    document.getElementById('grade-entry-table').style.display = 'block';
    
    // Load existing grades via AJAX
    fetch(`/api/grades/component/${currentComponent}/grades`)
        .then(response => response.json())
        .then(data => {
            data.grades.forEach(grade => {
                document.getElementById(`points-${grade.enrollment_id}`).value = grade.points_earned;
                calculateGrade(document.getElementById(`points-${grade.enrollment_id}`));
                document.getElementById(`comments-${grade.enrollment_id}`).value = grade.comments || '';
            });
        });
}

// Calculate grade percentage and letter
function calculateGrade(input) {
    const points = parseFloat(input.value) || 0;
    const enrollmentId = input.dataset.enrollment;
    
    // Calculate percentage
    const percentage = (points / maxPoints) * 100;
    document.getElementById(`percentage-${enrollmentId}`).textContent = percentage.toFixed(2) + '%';
    
    // Calculate letter grade
    let letterGrade = 'F';
    let badgeClass = 'bg-danger';
    
    if (percentage >= 93) { letterGrade = 'A'; badgeClass = 'bg-success'; }
    else if (percentage >= 90) { letterGrade = 'A-'; badgeClass = 'bg-success'; }
    else if (percentage >= 87) { letterGrade = 'B+'; badgeClass = 'bg-success'; }
    else if (percentage >= 83) { letterGrade = 'B'; badgeClass = 'bg-success'; }
    else if (percentage >= 80) { letterGrade = 'B-'; badgeClass = 'bg-info'; }
    else if (percentage >= 77) { letterGrade = 'C+'; badgeClass = 'bg-info'; }
    else if (percentage >= 73) { letterGrade = 'C'; badgeClass = 'bg-warning'; }
    else if (percentage >= 70) { letterGrade = 'C-'; badgeClass = 'bg-warning'; }
    else if (percentage >= 67) { letterGrade = 'D+'; badgeClass = 'bg-warning'; }
    else if (percentage >= 63) { letterGrade = 'D'; badgeClass = 'bg-warning'; }
    
    const letterSpan = document.getElementById(`letter-${enrollmentId}`);
    letterSpan.textContent = letterGrade;
    letterSpan.className = `badge ${badgeClass}`;
}

// Save individual grade
function saveGrade(enrollmentId) {
    const points = document.getElementById(`points-${enrollmentId}`).value;
    const comments = document.getElementById(`comments-${enrollmentId}`).value;
    
    if (!currentComponent) {
        alert('Please select a component first');
        return;
    }
    
    fetch('/grades/save', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            enrollment_id: enrollmentId,
            component_id: currentComponent,
            points_earned: points,
            comments: comments
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success indicator
            const row = document.querySelector(`tr[data-enrollment="${enrollmentId}"]`);
            row.classList.add('table-success');
            setTimeout(() => row.classList.remove('table-success'), 2000);
        } else {
            alert('Error saving grade: ' + data.message);
        }
    });
}

// Save all grades
function saveAllGrades() {
    const grades = [];
    document.querySelectorAll('.grade-input').forEach(input => {
        if (input.value) {
            grades.push({
                enrollment_id: input.dataset.enrollment,
                points: input.value
            });
        }
    });
    
    if (grades.length === 0) {
        alert('No grades to save');
        return;
    }
    
    fetch(`/grades/bulk/${{{ $section->id }}}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            component_id: currentComponent,
            grades: grades
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('All grades saved successfully');
            location.reload();
        } else {
            alert('Error saving grades');
        }
    });
}

// Clear individual grade
function clearGrade(enrollmentId) {
    if (confirm('Clear this grade?')) {
        document.getElementById(`points-${enrollmentId}`).value = '';
        document.getElementById(`percentage-${enrollmentId}`).textContent = '-';
        document.getElementById(`letter-${enrollmentId}`).textContent = '-';
        document.getElementById(`letter-${enrollmentId}`).className = 'badge bg-secondary';
        document.getElementById(`comments-${enrollmentId}`).value = '';
    }
}

// Apply same grade to all students
function applyToAll() {
    const value = prompt('Enter points to apply to all students:');
    if (value && !isNaN(value)) {
        document.querySelectorAll('.grade-input').forEach(input => {
            input.value = value;
            calculateGrade(input);
        });
    }
}

// Download Excel template
function downloadTemplate() {
    const componentId = document.getElementById('upload-component').value;
    if (!componentId) {
        alert('Please select a component first');
        return;
    }
    window.location.href = `/grades/template/${{{ $section->id }}}/${componentId}`;
}

// Initialize grade distribution chart
const ctx = document.getElementById('gradeDistributionChart');
if (ctx) {
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['A', 'B', 'C', 'D', 'F'],
            datasets: [{
                label: 'Number of Students',
                data: [
                    {{ $enrollments->where('letter_grade', 'A')->count() }},
                    {{ $enrollments->whereIn('letter_grade', ['B+', 'B', 'B-'])->count() }},
                    {{ $enrollments->whereIn('letter_grade', ['C+', 'C', 'C-'])->count() }},
                    {{ $enrollments->whereIn('letter_grade', ['D+', 'D'])->count() }},
                    {{ $enrollments->where('letter_grade', 'F')->count() }}
                ],
                backgroundColor: ['#28a745', '#17a2b8', '#ffc107', '#fd7e14', '#dc3545']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
}
</script>
@endpush
@endsection