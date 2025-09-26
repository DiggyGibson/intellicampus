@extends('layouts.app')

@section('title', 'Preview & Submit Grades')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('grades.index') }}">Grade Management</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('grades.entry', $section->id) }}">Grade Entry</a></li>
                    <li class="breadcrumb-item active">Preview & Submit</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0">Preview & Submit Final Grades</h1>
            <p class="text-muted">
                {{ $section->course->code }} - {{ $section->course->title }} | 
                Section {{ $section->section_code }} | 
                {{ $section->term->name }}
            </p>
        </div>
    </div>

    <!-- Warning Alert -->
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Important:</strong> Please carefully review all grades before submission. Once submitted, grades will require department approval to change.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    <!-- Grade Statistics Card -->
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Grade Distribution</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <canvas id="gradeChart" height="80"></canvas>
                </div>
                <div class="col-md-4">
                    <h6>Statistics</h6>
                    <table class="table table-sm">
                        <tr>
                            <td>Total Students:</td>
                            <td><strong>{{ $enrollments->count() }}</strong></td>
                        </tr>
                        <tr>
                            <td>Class Average:</td>
                            <td><strong>{{ number_format($enrollments->avg('final_percentage') ?? 0, 2) }}%</strong></td>
                        </tr>
                        <tr>
                            <td>Highest Grade:</td>
                            <td><strong>{{ $enrollments->max('final_letter') ?? 'N/A' }}</strong></td>
                        </tr>
                        <tr>
                            <td>Lowest Grade:</td>
                            <td><strong>{{ $enrollments->min('final_letter') ?? 'N/A' }}</strong></td>
                        </tr>
                        <tr>
                            <td>Pass Rate:</td>
                            <td>
                                @php
                                    $passCount = $enrollments->filter(function($e) {
                                        return !in_array($e->final_letter, ['F', 'W', 'I']);
                                    })->count();
                                    $passRate = $enrollments->count() > 0 ? ($passCount / $enrollments->count()) * 100 : 0;
                                @endphp
                                <strong class="{{ $passRate >= 70 ? 'text-success' : 'text-warning' }}">
                                    {{ number_format($passRate, 1) }}%
                                </strong>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Grade Preview Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Final Grades Preview</h5>
        </div>
        <div class="card-body">
            <form id="submitGradesForm" action="{{ route('grades.submit', $section->id) }}" method="POST">
                @csrf
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="50">#</th>
                                <th>Student ID</th>
                                <th>Student Name</th>
                                <th>Email</th>
                                <th class="text-center">Percentage</th>
                                <th class="text-center">Letter Grade</th>
                                <th class="text-center">Grade Points</th>
                                <th class="text-center">Status</th>
                                <th width="100">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($enrollments as $index => $enrollment)
                            <tr class="{{ $enrollment->final_letter == 'F' ? 'table-danger' : '' }}">
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $enrollment->student->student_id }}</td>
                                <td>
                                    <strong>{{ $enrollment->student->last_name }}, {{ $enrollment->student->first_name }}</strong>
                                    @if($enrollment->student->middle_name)
                                        {{ $enrollment->student->middle_name[0] }}.
                                    @endif
                                </td>
                                <td>{{ $enrollment->student->email }}</td>
                                <td class="text-center">
                                    <span class="badge bg-secondary">{{ number_format($enrollment->final_percentage, 2) }}%</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $enrollment->final_letter == 'F' ? 'danger' : ($enrollment->final_letter >= 'B' ? 'success' : 'warning') }} fs-6">
                                        {{ $enrollment->final_letter }}
                                    </span>
                                    <input type="hidden" name="grades[{{ $index }}][enrollment_id]" value="{{ $enrollment->id }}">
                                    <input type="hidden" name="grades[{{ $index }}][final_grade]" value="{{ $enrollment->final_letter }}">
                                </td>
                                <td class="text-center">{{ number_format($enrollment->grade_points, 2) }}</td>
                                <td class="text-center">
                                    @if($enrollment->final_letter == 'F')
                                        <span class="text-danger"><i class="fas fa-times-circle"></i> Failing</span>
                                    @elseif($enrollment->final_letter == 'I')
                                        <span class="text-warning"><i class="fas fa-clock"></i> Incomplete</span>
                                    @elseif($enrollment->final_letter == 'W')
                                        <span class="text-muted"><i class="fas fa-user-times"></i> Withdrawn</span>
                                    @else
                                        <span class="text-success"><i class="fas fa-check-circle"></i> Passing</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            onclick="editGrade({{ $enrollment->id }}, '{{ $enrollment->final_letter }}')">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Submission Confirmation -->
                <div class="card mt-4 border-primary">
                    <div class="card-body">
                        <h5 class="card-title">Submission Confirmation</h5>
                        <p class="card-text">
                            By submitting these grades, you confirm that:
                        </p>
                        <ul>
                            <li>All grades have been accurately calculated and reviewed</li>
                            <li>Students have been properly evaluated according to the course syllabus</li>
                            <li>You understand that submitted grades will require approval for changes</li>
                        </ul>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="confirmSubmission" name="confirm" required>
                            <label class="form-check-label" for="confirmSubmission">
                                <strong>I confirm that these grades are accurate and ready for submission</strong>
                            </label>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <a href="{{ route('grades.entry', $section->id) }}" class="btn btn-secondary w-100">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Grade Entry
                                </a>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-success w-100" id="submitButton" disabled>
                                    <i class="fas fa-check-circle me-2"></i>Submit Final Grades
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Grade Modal -->
<div class="modal fade" id="editGradeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Grade</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editGradeForm">
                    <input type="hidden" id="edit_enrollment_id">
                    <div class="mb-3">
                        <label for="edit_grade" class="form-label">Letter Grade</label>
                        <select class="form-select" id="edit_grade" required>
                            <option value="A">A (93-100%)</option>
                            <option value="A-">A- (90-92%)</option>
                            <option value="B+">B+ (87-89%)</option>
                            <option value="B">B (83-86%)</option>
                            <option value="B-">B- (80-82%)</option>
                            <option value="C+">C+ (77-79%)</option>
                            <option value="C">C (73-76%)</option>
                            <option value="C-">C- (70-72%)</option>
                            <option value="D+">D+ (67-69%)</option>
                            <option value="D">D (63-66%)</option>
                            <option value="F">F (Below 60%)</option>
                            <option value="I">I (Incomplete)</option>
                            <option value="W">W (Withdrawn)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_reason" class="form-label">Reason for Change</label>
                        <textarea class="form-control" id="edit_reason" rows="3" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveGradeChange()">Save Change</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Grade distribution chart
const ctx = document.getElementById('gradeChart').getContext('2d');
const gradeData = {
    labels: {!! json_encode(array_keys($sortedDistribution)) !!},
    datasets: [{
        label: 'Number of Students',
        data: {!! json_encode(array_values($sortedDistribution)) !!},
        backgroundColor: [
            'rgba(40, 167, 69, 0.8)',  // A
            'rgba(40, 167, 69, 0.6)',  // A-
            'rgba(32, 201, 151, 0.8)', // B+
            'rgba(32, 201, 151, 0.6)', // B
            'rgba(32, 201, 151, 0.4)', // B-
            'rgba(255, 193, 7, 0.8)',  // C+
            'rgba(255, 193, 7, 0.6)',  // C
            'rgba(255, 193, 7, 0.4)',  // C-
            'rgba(253, 126, 20, 0.8)', // D+
            'rgba(253, 126, 20, 0.6)', // D
            'rgba(220, 53, 69, 0.8)'   // F
        ],
        borderColor: 'rgba(0, 0, 0, 0.1)',
        borderWidth: 1
    }]
};

new Chart(ctx, {
    type: 'bar',
    data: gradeData,
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
        },
        plugins: {
            legend: {
                display: false
            },
            title: {
                display: true,
                text: 'Grade Distribution'
            }
        }
    }
});

// Enable submit button when checkbox is checked
document.getElementById('confirmSubmission').addEventListener('change', function() {
    document.getElementById('submitButton').disabled = !this.checked;
});

// Edit grade function
function editGrade(enrollmentId, currentGrade) {
    document.getElementById('edit_enrollment_id').value = enrollmentId;
    document.getElementById('edit_grade').value = currentGrade;
    document.getElementById('edit_reason').value = '';
    
    const modal = new bootstrap.Modal(document.getElementById('editGradeModal'));
    modal.show();
}

// Save grade change
function saveGradeChange() {
    const enrollmentId = document.getElementById('edit_enrollment_id').value;
    const newGrade = document.getElementById('edit_grade').value;
    const reason = document.getElementById('edit_reason').value;
    
    if (!reason) {
        alert('Please provide a reason for the grade change.');
        return;
    }
    
    // Update the grade in the form
    const inputs = document.querySelectorAll(`input[name*="[enrollment_id]"][value="${enrollmentId}"]`);
    inputs.forEach(input => {
        const index = input.name.match(/\[(\d+)\]/)[1];
        document.querySelector(`input[name="grades[${index}][final_grade]"]`).value = newGrade;
        
        // Update the display
        const row = input.closest('tr');
        row.querySelector('.badge.fs-6').textContent = newGrade;
        row.querySelector('.badge.fs-6').className = `badge fs-6 bg-${getGradeColor(newGrade)}`;
    });
    
    // Close modal
    bootstrap.Modal.getInstance(document.getElementById('editGradeModal')).hide();
    
    // Show success message
    showAlert('Grade updated. Remember to submit the form to save changes.', 'warning');
}

// Get grade color class
function getGradeColor(grade) {
    if (grade === 'F') return 'danger';
    if (['A', 'A-', 'B+', 'B'].includes(grade)) return 'success';
    return 'warning';
}

// Show alert message
function showAlert(message, type = 'success') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.querySelector('.container-fluid').insertBefore(alertDiv, document.querySelector('.card').parentNode);
}

// Form submission confirmation
document.getElementById('submitGradesForm').addEventListener('submit', function(e) {
    if (!confirm('Are you sure you want to submit these final grades? This action cannot be undone.')) {
        e.preventDefault();
    }
});
</script>
@endpush
@endsection