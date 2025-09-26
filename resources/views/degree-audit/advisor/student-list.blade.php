{{-- resources/views/degree-audit/advisor/student-list.blade.php --}}
@extends('layouts.app')

@section('title', 'My Students - Degree Audit')

@section('styles')
<style>
    .student-card {
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .student-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .progress-badge {
        font-size: 1.2rem;
        font-weight: bold;
    }
    
    .at-risk { background-color: #fee2e2; border-left: 4px solid #ef4444; }
    .on-track { background-color: #d1fae5; border-left: 4px solid #10b981; }
    .graduating { background-color: #dbeafe; border-left: 4px solid #3b82f6; }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>My Advisees</h2>
                <div>
                    <button class="btn btn-primary" onclick="generateBatchReports()">
                        <i class="fas fa-file-export"></i> Batch Reports
                    </button>
                    <button class="btn btn-outline-secondary" onclick="exportList()">
                        <i class="fas fa-download"></i> Export List
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <input type="text" class="form-control" placeholder="Search students..." id="studentSearch">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" id="statusFilter">
                                <option value="">All Status</option>
                                <option value="at-risk">At Risk</option>
                                <option value="on-track">On Track</option>
                                <option value="graduating">Graduating</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" id="yearFilter">
                                <option value="">All Years</option>
                                <option value="1">Freshman</option>
                                <option value="2">Sophomore</option>
                                <option value="3">Junior</option>
                                <option value="4">Senior</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" id="majorFilter">
                                <option value="">All Majors</option>
                                @foreach($majors ?? [] as $major)
                                    <option value="{{ $major }}">{{ $major }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-secondary w-100" onclick="resetFilters()">
                                <i class="fas fa-undo"></i> Reset Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-primary">{{ $totalStudents ?? 0 }}</h3>
                    <p class="text-muted mb-0">Total Students</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center at-risk">
                <div class="card-body">
                    <h3 class="text-danger">{{ $atRiskCount ?? 0 }}</h3>
                    <p class="text-muted mb-0">At Risk</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center on-track">
                <div class="card-body">
                    <h3 class="text-success">{{ $onTrackCount ?? 0 }}</h3>
                    <p class="text-muted mb-0">On Track</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center graduating">
                <div class="card-body">
                    <h3 class="text-info">{{ $graduatingCount ?? 0 }}</h3>
                    <p class="text-muted mb-0">Graduating Soon</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Students List -->
    <div class="row" id="studentsList">
        @foreach($students ?? [] as $student)
        <div class="col-lg-6 mb-4 student-item" 
             data-status="{{ $student->audit_status }}"
             data-year="{{ $student->academic_year }}"
             data-major="{{ $student->program->name ?? '' }}">
            <div class="card student-card {{ $student->audit_status }}" 
                 onclick="viewStudentAudit({{ $student->id }})">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="mb-1">{{ $student->user->name }}</h5>
                            <p class="text-muted mb-1">
                                <strong>ID:</strong> {{ $student->student_id }} | 
                                <strong>Major:</strong> {{ $student->program->name ?? 'Undeclared' }}
                            </p>
                            <p class="text-muted mb-0">
                                <strong>Year:</strong> {{ $student->academic_year_name }} | 
                                <strong>GPA:</strong> {{ number_format($student->cumulative_gpa ?? 0, 2) }}
                            </p>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="progress-badge">
                                {{ number_format($student->degree_progress ?? 0, 0) }}%
                            </div>
                            <small class="text-muted">Complete</small>
                            
                            @if($student->has_holds)
                                <div class="mt-2">
                                    <span class="badge bg-danger">Has Holds</span>
                                </div>
                            @endif
                            
                            @if($student->last_advised_date)
                                <div class="mt-1">
                                    <small class="text-muted">
                                        Last Advised: {{ $student->last_advised_date->format('M d') }}
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="mt-3 pt-3 border-top">
                        <button class="btn btn-sm btn-outline-primary me-2" 
                                onclick="event.stopPropagation(); sendMessage({{ $student->id }})">
                            <i class="fas fa-envelope"></i> Message
                        </button>
                        <button class="btn btn-sm btn-outline-info me-2"
                                onclick="event.stopPropagation(); scheduleAppointment({{ $student->id }})">
                            <i class="fas fa-calendar"></i> Schedule
                        </button>
                        <button class="btn btn-sm btn-outline-success"
                                onclick="event.stopPropagation(); addNote({{ $student->id }})">
                            <i class="fas fa-sticky-note"></i> Note
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="row">
        <div class="col-12">
            {{ $students->links() }}
        </div>
    </div>
</div>

<!-- Add Note Modal -->
<div class="modal fade" id="noteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Advising Note</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <textarea class="form-control" rows="4" id="noteContent" 
                          placeholder="Enter your advising note here..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveNote()">Save Note</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function viewStudentAudit(studentId) {
    window.location.href = `/advisor/student/${studentId}/audit`;
}

function sendMessage(studentId) {
    // Implementation for sending message
    console.log('Send message to student:', studentId);
}

function scheduleAppointment(studentId) {
    // Implementation for scheduling
    console.log('Schedule appointment with student:', studentId);
}

let currentStudentId = null;
function addNote(studentId) {
    currentStudentId = studentId;
    $('#noteModal').modal('show');
}

function saveNote() {
    const note = document.getElementById('noteContent').value;
    // Save note implementation
    console.log('Save note for student:', currentStudentId, note);
    $('#noteModal').modal('hide');
}

// Filtering functionality
document.getElementById('studentSearch').addEventListener('input', filterStudents);
document.getElementById('statusFilter').addEventListener('change', filterStudents);
document.getElementById('yearFilter').addEventListener('change', filterStudents);
document.getElementById('majorFilter').addEventListener('change', filterStudents);

function filterStudents() {
    const search = document.getElementById('studentSearch').value.toLowerCase();
    const status = document.getElementById('statusFilter').value;
    const year = document.getElementById('yearFilter').value;
    const major = document.getElementById('majorFilter').value;
    
    document.querySelectorAll('.student-item').forEach(item => {
        const text = item.textContent.toLowerCase();
        const itemStatus = item.dataset.status;
        const itemYear = item.dataset.year;
        const itemMajor = item.dataset.major;
        
        let show = true;
        
        if (search && !text.includes(search)) show = false;
        if (status && itemStatus !== status) show = false;
        if (year && itemYear !== year) show = false;
        if (major && itemMajor !== major) show = false;
        
        item.style.display = show ? 'block' : 'none';
    });
}

function resetFilters() {
    document.getElementById('studentSearch').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('yearFilter').value = '';
    document.getElementById('majorFilter').value = '';
    filterStudents();
}

function generateBatchReports() {
    window.location.href = '{{ route("advisor.batch-reports") }}';
}

function exportList() {
    // Export functionality
    alert('Exporting student list...');
}
</script>
@endsection