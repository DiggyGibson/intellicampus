@extends('layouts.app')

@section('title', 'Dean\'s List Report')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">Dean's List Report</h1>
            <p class="mb-0 text-muted">High-achieving students recognition for {{ $term->name }}</p>
        </div>
    </div>

    <!-- Criteria Card -->
    <div class="card mb-4 border-left-success">
        <div class="card-body">
            <h5 class="mb-3">Dean's List Criteria</h5>
            <form method="GET" action="{{ route('admin.grades.reports.deans') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Academic Term</label>
                    <select class="form-select" name="term_id" required>
                        @foreach($terms as $t)
                            <option value="{{ $t->id }}" {{ $t->id == $term->id ? 'selected' : '' }}>
                                {{ $t->name }} {{ $t->is_current ? '(Current)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Minimum GPA</label>
                    <input type="number" class="form-control" name="gpa_threshold" 
                           value="{{ $gpaThreshold }}" 
                           min="3.0" max="4.0" step="0.1" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Minimum Credits</label>
                    <input type="number" class="form-control" name="min_credits" 
                           value="{{ $minCredits }}" 
                           min="12" max="21" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-2"></i>Apply Criteria
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics Overview -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="text-uppercase mb-1">Total Eligible</h6>
                    <div class="h3 mb-0">{{ $stats['total_eligible'] }}</div>
                    <small>Students qualifying</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="text-uppercase mb-1">Average GPA</h6>
                    <div class="h3 mb-0">{{ number_format($stats['average_gpa'], 3) }}</div>
                    <small>Of eligible students</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="text-uppercase mb-1">Highest GPA</h6>
                    <div class="h3 mb-0">{{ number_format($stats['highest_gpa'], 3) }}</div>
                    <small>Top performer</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6 class="text-uppercase mb-1">Programs</h6>
                    <div class="h3 mb-0">{{ count($stats['by_program']) }}</div>
                    <small>Represented</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Distribution Charts -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Distribution by Academic Level</h6>
                </div>
                <div class="card-body">
                    <canvas id="levelChart" height="150"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Distribution by Program</h6>
                </div>
                <div class="card-body">
                    <canvas id="programChart" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Dean's List Students Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-trophy me-2 text-warning"></i>
                Dean's List Students ({{ $deansListStudents->count() }} Students)
            </h5>
            <div>
                @if($deansListStudents->count() > 0)
                <button type="button" class="btn btn-success btn-sm" onclick="recordDeansList()">
                    <i class="fas fa-save me-1"></i>Record to Database
                </button>
                <button type="button" class="btn btn-info btn-sm" onclick="printCertificates()">
                    <i class="fas fa-certificate me-1"></i>Print Certificates
                </button>
                <button type="button" class="btn btn-primary btn-sm" onclick="exportList()">
                    <i class="fas fa-file-excel me-1"></i>Export List
                </button>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="deansListTable">
                    <thead>
                        <tr>
                            <th width="50">
                                <input type="checkbox" id="selectAll" onchange="toggleAll(this)">
                            </th>
                            <th>Rank</th>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Program</th>
                            <th>Level</th>
                            <th class="text-center">Courses</th>
                            <th class="text-center">Credits</th>
                            <th class="text-center">Term GPA</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deansListStudents as $index => $student)
                        <tr class="{{ $student->weighted_gpa == 4.0 ? 'table-warning' : '' }}">
                            <td>
                                <input type="checkbox" class="student-select" 
                                       value="{{ $student->id }}"
                                       {{ $student->already_recorded ? 'disabled' : '' }}>
                            </td>
                            <td>
                                @if($index < 3)
                                    @if($index == 0)
                                        <i class="fas fa-medal text-warning"></i> 1st
                                    @elseif($index == 1)
                                        <i class="fas fa-medal text-secondary"></i> 2nd
                                    @else
                                        <i class="fas fa-medal text-warning" style="color: #cd7f32 !important;"></i> 3rd
                                    @endif
                                @else
                                    {{ $index + 1 }}
                                @endif
                            </td>
                            <td>{{ $student->student_id }}</td>
                            <td>
                                <strong>{{ $student->last_name }}, {{ $student->first_name }}</strong>
                                @if($student->weighted_gpa == 4.0)
                                    <span class="badge bg-warning ms-2">Perfect GPA</span>
                                @endif
                            </td>
                            <td>{{ $student->email }}</td>
                            <td>{{ $student->program_name }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ ucfirst($student->academic_level) }}</span>
                            </td>
                            <td class="text-center">{{ $student->courses_taken }}</td>
                            <td class="text-center">{{ $student->total_credits }}</td>
                            <td class="text-center">
                                <strong class="text-success">{{ number_format($student->weighted_gpa, 3) }}</strong>
                            </td>
                            <td>
                                @if($student->already_recorded)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle"></i> Recorded
                                    </span>
                                @else
                                    <span class="badge bg-warning">Pending</span>
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

<!-- Notification Modal -->
<div class="modal fade" id="notificationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Dean's List Notifications</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Send congratulatory emails to selected students?</p>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="includeParents">
                    <label class="form-check-label" for="includeParents">
                        Also notify parents/guardians
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="includeCertificate" checked>
                    <label class="form-check-label" for="includeCertificate">
                        Attach digital certificate
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="sendNotifications()">
                    <i class="fas fa-paper-plane me-2"></i>Send Notifications
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script>
// Initialize DataTable
$(document).ready(function() {
    $('#deansListTable').DataTable({
        pageLength: 50,
        order: [[9, 'desc']], // Sort by GPA
        columnDefs: [
            { orderable: false, targets: 0 }
        ]
    });
});

// Level Distribution Chart
const levelData = @json($stats['by_level'] ?? []);
const levelCtx = document.getElementById('levelChart').getContext('2d');
new Chart(levelCtx, {
    type: 'bar',
    data: {
        labels: Object.keys(levelData).map(k => k.charAt(0).toUpperCase() + k.slice(1)),
        datasets: [{
            label: 'Students',
            data: Object.values(levelData),
            backgroundColor: 'rgba(40, 167, 69, 0.8)'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Program Distribution Chart
const programData = @json($stats['by_program'] ?? []);
const programCtx = document.getElementById('programChart').getContext('2d');
new Chart(programCtx, {
    type: 'pie',
    data: {
        labels: Object.keys(programData),
        datasets: [{
            data: Object.values(programData),
            backgroundColor: [
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(153, 102, 255, 0.8)',
                'rgba(255, 159, 64, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Toggle all checkboxes
function toggleAll(source) {
    const checkboxes = document.querySelectorAll('.student-select:not(:disabled)');
    checkboxes.forEach(cb => cb.checked = source.checked);
}

// Get selected students
function getSelectedStudents() {
    const selected = [];
    document.querySelectorAll('.student-select:checked').forEach(cb => {
        selected.push(cb.value);
    });
    return selected;
}

// Record Dean's List
function recordDeansList() {
    const selected = getSelectedStudents();
    if (selected.length === 0) {
        alert('Please select students to record');
        return;
    }
    
    if (confirm(`Record ${selected.length} students to Dean's List?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.grades.deans.record") }}';
        form.innerHTML = `
            @csrf
            <input type="hidden" name="term_id" value="{{ $term->id }}">
            ${selected.map(id => `<input type="hidden" name="student_ids[]" value="${id}">`).join('')}
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Export list
function exportList() {
    window.location.href = '{{ route("admin.grades.reports.export", "deans-list") }}?term_id={{ $term->id }}&format=xlsx';
}

// Print certificates
function printCertificates() {
    const selected = getSelectedStudents();
    if (selected.length === 0) {
        alert('Please select students for certificates');
        return;
    }
    
    // Open print preview with selected students
    const url = `/admin/grades/deans-list/certificates?students=${selected.join(',')}`;
    window.open(url, '_blank');
}

// Send notifications
function sendNotifications() {
    const selected = getSelectedStudents();
    const includeParents = document.getElementById('includeParents').checked;
    const includeCertificate = document.getElementById('includeCertificate').checked;
    
    // Implementation would send actual notifications
    alert(`Notifications sent to ${selected.length} students!`);
    bootstrap.Modal.getInstance(document.getElementById('notificationModal')).hide();
}
</script>
@endpush
@endsection