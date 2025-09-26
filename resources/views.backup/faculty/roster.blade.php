@extends('layouts.app')

@section('title', 'Class Roster')

@section('breadcrumb')
    <a href="{{ route('faculty.dashboard') }}">Faculty Dashboard</a>
    <i class="fas fa-chevron-right"></i>
    <a href="{{ route('faculty.courses') }}">My Courses</a>
    <i class="fas fa-chevron-right"></i>
    <a href="{{ route('faculty.section.details', $section->id) }}">Section Details</a>
    <i class="fas fa-chevron-right"></i>
    <span>Roster</span>
@endsection

@section('page-actions')
    <button onclick="window.print()" class="btn btn-light me-2">
        <i class="fas fa-print me-1"></i> Print
    </button>
    <button onclick="exportRoster()" class="btn btn-success me-2">
        <i class="fas fa-download me-1"></i> Export
    </button>
    <a href="{{ route('faculty.section.details', $section->id) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Section
    </a>
@endsection

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="mb-4">
        <h2 class="fw-bold text-dark mb-1">Class Roster</h2>
        <p class="text-muted">
            {{ $section->course->course_code ?? '' }} - Section {{ $section->section_number ?? '' }}
            | {{ $section->course->title ?? '' }}
        </p>
    </div>

    <!-- Section Information -->
    <div class="alert alert-info mb-4">
        <div class="row">
            <div class="col-md-3">
                <small class="text-muted">Term</small>
                <div class="fw-bold">{{ $section->term->name ?? 'N/A' }}</div>
            </div>
            <div class="col-md-3">
                <small class="text-muted">Schedule</small>
                <div class="fw-bold">
                    {{ $section->days_of_week ?? 'TBA' }} 
                    {{ $section->start_time ? \Carbon\Carbon::parse($section->start_time)->format('g:i A') : '' }}
                </div>
            </div>
            <div class="col-md-3">
                <small class="text-muted">Location</small>
                <div class="fw-bold">{{ $section->room ?? 'Online' }}</div>
            </div>
            <div class="col-md-3">
                <small class="text-muted">Enrollment</small>
                <div class="fw-bold">
                    {{ $students->count() }} / {{ $section->enrollment_capacity ?? 30 }} Students
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Bar -->
    <div class="card shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="btn-group" role="group">
                <button onclick="emailAll()" class="btn btn-primary">
                    <i class="fas fa-envelope me-1"></i> Email All Students
                </button>
                <a href="{{ route('faculty.attendance', $section->id) }}" class="btn btn-warning">
                    <i class="fas fa-clipboard-check me-1"></i> Take Attendance
                </a>
                <a href="{{ route('faculty.gradebook', $section->id) }}" class="btn btn-purple">
                    <i class="fas fa-chart-line me-1"></i> Enter Grades
                </a>
                <button onclick="createGroups()" class="btn btn-info">
                    <i class="fas fa-users me-1"></i> Create Groups
                </button>
            </div>
            <div class="float-end">
                <label class="me-2">View:</label>
                <select onchange="changeView(this.value)" class="form-select form-select-sm d-inline-block" style="width: auto;">
                    <option value="table">Table View</option>
                    <option value="grid">Photo Grid</option>
                    <option value="list">Detailed List</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Student Roster Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-gradient-primary text-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i>Enrolled Students</h5>
                <span class="badge bg-white text-primary">{{ $students->count() }} Students</span>
            </div>
        </div>

        @if($students->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="40">
                                <input type="checkbox" onclick="selectAll(this)" class="form-check-input">
                            </th>
                            <th width="60">Photo</th>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Program</th>
                            <th>Year</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $index => $student)
                            <tr>
                                <td>
                                    <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" 
                                           class="form-check-input student-checkbox">
                                </td>
                                <td>
                                    @if($student->profile_photo)
                                        <img src="{{ $student->profile_photo }}" 
                                             alt="{{ $student->first_name }}"
                                             class="rounded-circle"
                                             style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                        <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center"
                                             style="width: 40px; height: 40px;">
                                            <span class="text-white small">
                                                {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                            </span>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-bold">{{ $student->student_id }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold">
                                        {{ $student->last_name }}, {{ $student->first_name }} 
                                        {{ $student->middle_name ? substr($student->middle_name, 0, 1) . '.' : '' }}
                                    </div>
                                    @if($student->preferred_name)
                                        <small class="text-muted">Prefers: {{ $student->preferred_name }}</small>
                                    @endif
                                </td>
                                <td>
                                    <a href="mailto:{{ $student->email }}" class="text-primary">
                                        {{ $student->email }}
                                    </a>
                                </td>
                                <td>{{ $student->program ?? 'Undeclared' }}</td>
                                <td>{{ $student->academic_level ?? 'N/A' }}</td>
                                <td>
                                    @php
                                        $statusClass = match($student->enrollment_status) {
                                            'active' => 'bg-success',
                                            'inactive' => 'bg-secondary',
                                            'suspended' => 'bg-danger',
                                            'graduated' => 'bg-primary',
                                            default => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }}">
                                        {{ ucfirst($student->enrollment_status ?? 'active') }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('students.show', $student->id) }}" 
                                           class="btn btn-outline-primary" title="View Profile">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button onclick="emailStudent('{{ $student->email }}')" 
                                                class="btn btn-outline-success" title="Send Email">
                                            <i class="fas fa-envelope"></i>
                                        </button>
                                        <button onclick="viewAttendance({{ $student->id }})" 
                                                class="btn btn-outline-warning" title="View Attendance">
                                            <i class="fas fa-clipboard-check"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Roster Summary -->
            <div class="card-footer bg-light">
                <div class="row text-center">
                    <div class="col-md-3">
                        <small class="text-muted">Total Students</small>
                        <div class="fw-bold">{{ $students->count() }}</div>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Active</small>
                        <div class="fw-bold text-success">
                            {{ $students->where('enrollment_status', 'active')->count() }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Class Distribution</small>
                        <div class="fw-bold">
                            @php
                                $levels = $students->pluck('academic_level')->filter()->countBy();
                                $mostCommon = $levels->sortDesc()->first();
                                $mostCommonLevel = $levels->search($mostCommon);
                            @endphp
                            @if($levels->isNotEmpty())
                                {{ $mostCommonLevel }} ({{ $mostCommon }})
                            @else
                                N/A
                            @endif
                        </div>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Last Updated</small>
                        <div class="fw-bold">{{ now()->format('M d, Y g:i A') }}</div>
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
    </div>

    <!-- Photo Grid View (Hidden by default, shown via JavaScript) -->
    <div id="photoGridView" class="card shadow-sm mt-4" style="display: none;">
        <div class="card-header bg-gradient-primary text-white py-3">
            <h5 class="mb-0"><i class="fas fa-th me-2"></i>Photo Grid</h5>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($students as $student)
                    <div class="col-md-2 col-sm-3 col-6 mb-3 text-center">
                        <div class="card">
                            <div class="card-body p-2">
                                @if($student->profile_photo)
                                    <img src="{{ $student->profile_photo }}" 
                                         alt="{{ $student->first_name }}"
                                         class="rounded-circle mb-2"
                                         style="width: 80px; height: 80px; object-fit: cover;">
                                @else
                                    <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mx-auto mb-2"
                                         style="width: 80px; height: 80px;">
                                        <span class="text-white fs-4">
                                            {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                        </span>
                                    </div>
                                @endif
                                <div class="small fw-bold">{{ $student->first_name }} {{ $student->last_name }}</div>
                                <div class="text-muted small">{{ $student->student_id }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
}
.btn-purple {
    background-color: #6f42c1;
    border-color: #6f42c1;
    color: white;
}
.btn-purple:hover {
    background-color: #5a32a3;
    border-color: #5a32a3;
    color: white;
}
</style>

<script>
function selectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.student-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
}

function emailStudent(email) {
    window.location.href = 'mailto:' + email;
}

function emailAll() {
    const checkboxes = document.querySelectorAll('.student-checkbox:checked');
    if (checkboxes.length === 0) {
        alert('Please select at least one student to email.');
        return;
    }
    alert('Email functionality will be implemented soon.');
}

function viewAttendance(studentId) {
    alert('Individual attendance view coming soon.');
}

function exportRoster() {
    if (confirm('Export roster as CSV?')) {
        alert('Export functionality will be implemented soon.');
    }
}

function createGroups() {
    const checkboxes = document.querySelectorAll('.student-checkbox:checked');
    if (checkboxes.length < 2) {
        alert('Please select at least 2 students to create groups.');
        return;
    }
    alert('Group creation functionality coming soon.');
}

function changeView(view) {
    const tableView = document.querySelector('.table-responsive').closest('.card');
    const gridView = document.getElementById('photoGridView');
    
    if (view === 'grid') {
        tableView.style.display = 'none';
        gridView.style.display = 'block';
    } else if (view === 'table') {
        tableView.style.display = 'block';
        gridView.style.display = 'none';
    } else {
        alert(`${view} view will be implemented soon.`);
    }
}
</script>
@endsection