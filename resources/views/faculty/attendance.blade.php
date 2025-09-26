@extends('layouts.app')

@section('title', 'Attendance Management')

@section('breadcrumb')
    <a href="{{ route('faculty.dashboard') }}">Faculty Dashboard</a>
    <i class="fas fa-chevron-right"></i>
    <a href="{{ route('faculty.courses') }}">My Courses</a>
    <i class="fas fa-chevron-right"></i>
    <a href="{{ route('faculty.section.details', $section->id) }}">Section Details</a>
    <i class="fas fa-chevron-right"></i>
    <span>Attendance</span>
@endsection

@section('page-actions')
    <button onclick="window.print()" class="btn btn-light me-2">
        <i class="fas fa-print me-1"></i> Print
    </button>
    <a href="{{ route('faculty.section.details', $section->id) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Section
    </a>
@endsection

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="mb-4">
        <h2 class="fw-bold text-dark mb-1">Attendance Management</h2>
        <p class="text-muted">
            {{ $section->course->course_code ?? '' }} - Section {{ $section->section_number ?? '' }}
            | {{ $section->course->title ?? '' }}
        </p>
    </div>

    <div class="row mb-4">
        <!-- Date Selection Card -->
        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-gradient-primary text-white py-3">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Select Date</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('faculty.attendance', $section->id) }}" class="row align-items-end g-3">
                        <div class="col-md-6">
                            <label class="form-label">Date</label>
                            <input type="date" 
                                   name="date" 
                                   value="{{ request('date', now()->format('Y-m-d')) }}"
                                   max="{{ now()->format('Y-m-d') }}"
                                   class="form-control"
                                   onchange="this.form.submit()">
                        </div>
                        <div class="col-md-6">
                            <div class="btn-group w-100" role="group">
                                <button type="button" onclick="setToday()" class="btn btn-primary">
                                    <i class="fas fa-calendar-day me-1"></i> Today
                                </button>
                                <button type="button" onclick="quickAttendance()" class="btn btn-success">
                                    <i class="fas fa-bolt me-1"></i> Quick Mark All Present
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Today's Statistics Card -->
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-gradient-info text-white py-3">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Today's Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="fw-bold text-success fs-3">{{ $attendanceStats['present'] ?? 0 }}</div>
                            <small class="text-muted">Present</small>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="fw-bold text-danger fs-3">{{ $attendanceStats['absent'] ?? 0 }}</div>
                            <small class="text-muted">Absent</small>
                        </div>
                        <div class="col-6">
                            <div class="fw-bold text-warning fs-3">{{ $attendanceStats['late'] ?? 0 }}</div>
                            <small class="text-muted">Late</small>
                        </div>
                        <div class="col-6">
                            <div class="fw-bold text-info fs-3">{{ $attendanceStats['excused'] ?? 0 }}</div>
                            <small class="text-muted">Excused</small>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <small class="text-muted">Attendance Rate</small>
                        <div class="fw-bold text-primary fs-2">
                            @php
                                $total = $students->count();
                                $present = ($attendanceStats['present'] ?? 0) + ($attendanceStats['late'] ?? 0);
                                $rate = $total > 0 ? round(($present / $total) * 100, 1) : 0;
                            @endphp
                            {{ $rate }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Table -->
    <div class="card shadow-sm mb-4">
        <form method="POST" action="{{ route('faculty.attendance.mark', $section->id) }}" id="attendanceForm">
            @csrf
            <input type="hidden" name="date" value="{{ request('date', now()->format('Y-m-d')) }}">
            
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Mark Attendance</h5>
                    <div>
                        <span class="badge bg-primary">
                            {{ \Carbon\Carbon::parse(request('date', now()))->format('l, F d, Y') }}
                        </span>
                    </div>
                </div>
            </div>

            @if($students->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="50">#</th>
                                <th width="60">Photo</th>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th class="text-center" width="80">
                                    <span class="text-success">Present</span>
                                </th>
                                <th class="text-center" width="80">
                                    <span class="text-danger">Absent</span>
                                </th>
                                <th class="text-center" width="80">
                                    <span class="text-warning">Late</span>
                                </th>
                                <th class="text-center" width="80">
                                    <span class="text-info">Excused</span>
                                </th>
                                <th width="200">Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $index => $student)
                                @php
                                    $attendance = isset($existingAttendance[$student->id]) ? $existingAttendance[$student->id] : null;
                                    $status = $attendance ? $attendance->status : 'present';
                                    $notes = $attendance ? $attendance->notes : '';
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
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
                                        <div class="fw-bold">{{ $student->last_name }}, {{ $student->first_name }}</div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check d-flex justify-content-center">
                                            <input class="form-check-input attendance-radio" 
                                                   type="radio" 
                                                   name="attendance[{{ $student->id }}][status]" 
                                                   value="present"
                                                   {{ $status == 'present' ? 'checked' : '' }}
                                                   id="present-{{ $student->id }}">
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check d-flex justify-content-center">
                                            <input class="form-check-input attendance-radio" 
                                                   type="radio" 
                                                   name="attendance[{{ $student->id }}][status]" 
                                                   value="absent"
                                                   {{ $status == 'absent' ? 'checked' : '' }}
                                                   id="absent-{{ $student->id }}">
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check d-flex justify-content-center">
                                            <input class="form-check-input attendance-radio" 
                                                   type="radio" 
                                                   name="attendance[{{ $student->id }}][status]" 
                                                   value="late"
                                                   {{ $status == 'late' ? 'checked' : '' }}
                                                   id="late-{{ $student->id }}">
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check d-flex justify-content-center">
                                            <input class="form-check-input attendance-radio" 
                                                   type="radio" 
                                                   name="attendance[{{ $student->id }}][status]" 
                                                   value="excused"
                                                   {{ $status == 'excused' ? 'checked' : '' }}
                                                   id="excused-{{ $student->id }}">
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" 
                                               name="attendance[{{ $student->id }}][notes]" 
                                               value="{{ $notes }}"
                                               placeholder="Optional notes..."
                                               class="form-control form-control-sm">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Action Buttons -->
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-secondary">{{ $students->count() }} students</span>
                        </div>
                        <div>
                            <button type="button" onclick="resetForm()" class="btn btn-secondary">
                                <i class="fas fa-undo me-1"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Attendance
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

    <!-- Attendance History Summary -->
    <div class="card shadow-sm">
        <div class="card-header bg-gradient-success text-white py-3">
            <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Attendance Patterns (Last 30 Days)</h5>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-3 mb-3 mb-md-0">
                    <div class="stat-box">
                        <div class="fs-2 fw-bold text-success">85%</div>
                        <small class="text-muted">Average Attendance</small>
                    </div>
                </div>
                <div class="col-md-3 mb-3 mb-md-0">
                    <div class="stat-box">
                        <div class="fs-2 fw-bold text-danger">3</div>
                        <small class="text-muted">Chronic Absences</small>
                    </div>
                </div>
                <div class="col-md-3 mb-3 mb-md-0">
                    <div class="stat-box">
                        <div class="fs-2 fw-bold text-warning">12</div>
                        <small class="text-muted">Total Late Arrivals</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box">
                        <div class="fs-2 fw-bold text-info">8</div>
                        <small class="text-muted">Excused Absences</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
}
.bg-gradient-info {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
}
.bg-gradient-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}
.attendance-radio {
    cursor: pointer;
    width: 1.2em;
    height: 1.2em;
}
.attendance-radio:checked {
    transform: scale(1.1);
}
.stat-box {
    padding: 1rem;
    border-radius: 0.5rem;
    background: #f8f9fa;
    transition: transform 0.2s;
}
.stat-box:hover {
    transform: translateY(-2px);
}
</style>

<script>
function setToday() {
    const dateInput = document.querySelector('input[name="date"]');
    const today = new Date().toISOString().split('T')[0];
    dateInput.value = today;
    dateInput.form.submit();
}

function quickAttendance() {
    if (confirm('Mark all students as present for today?')) {
        document.querySelectorAll('input[value="present"]').forEach(radio => radio.checked = true);
    }
}

function resetForm() {
    document.querySelectorAll('input[value="present"]').forEach(radio => radio.checked = true);
    document.querySelectorAll('input[type="text"]').forEach(input => input.value = '');
}

// Auto-save draft
let saveTimer;
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.attendance-radio, input[type="text"]').forEach(input => {
        input.addEventListener('change', () => {
            clearTimeout(saveTimer);
            saveTimer = setTimeout(() => {
                console.log('Auto-saving draft...');
                // Implement auto-save functionality here
            }, 2000);
        });
    });
});
</script>
@endsection