@extends('layouts.app')

@section('title', 'Office Hours Management')

@section('breadcrumb')
    <a href="{{ route('faculty.dashboard') }}">Faculty Dashboard</a>
    <i class="fas fa-chevron-right"></i>
    <span>Office Hours</span>
@endsection

@section('page-actions')
    <a href="{{ route('faculty.dashboard') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
    </a>
@endsection

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="mb-4">
        <h2 class="fw-bold text-dark mb-1">Office Hours Management</h2>
        <p class="text-muted">Schedule your availability for student consultations</p>
    </div>

    <div class="row">
        <!-- Add Office Hours Form -->
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-gradient-primary text-white py-3">
                    <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Add Office Hours</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('faculty.office.hours.create') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Day of Week</label>
                            <select name="day_of_week" required class="form-select">
                                <option value="">Select Day</option>
                                <option value="Monday">Monday</option>
                                <option value="Tuesday">Tuesday</option>
                                <option value="Wednesday">Wednesday</option>
                                <option value="Thursday">Thursday</option>
                                <option value="Friday">Friday</option>
                            </select>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label">Start Time</label>
                                <input type="time" name="start_time" required class="form-control">
                            </div>
                            <div class="col-6">
                                <label class="form-label">End Time</label>
                                <input type="time" name="end_time" required class="form-control">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" required 
                                   placeholder="e.g., Office 312, Building A"
                                   class="form-control">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <select name="type" required class="form-select" onchange="toggleMeetingUrl(this)">
                                <option value="in-person">In-Person Only</option>
                                <option value="virtual">Virtual Only</option>
                                <option value="both">Both In-Person & Virtual</option>
                            </select>
                        </div>
                        
                        <div id="meetingUrlField" class="mb-3" style="display: none;">
                            <label class="form-label">Virtual Meeting URL</label>
                            <input type="url" name="meeting_url" 
                                   placeholder="https://zoom.us/j/..."
                                   class="form-control">
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-plus me-2"></i>Add Office Hours
                        </button>
                    </form>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow-sm">
                <div class="card-header bg-gradient-info text-white py-3">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button onclick="cancelToday()" class="btn btn-danger">
                            Cancel Today's Hours
                        </button>
                        <button onclick="addSpecialSession()" class="btn btn-success">
                            Add Special Session
                        </button>
                        <button onclick="notifyStudents()" class="btn btn-purple">
                            Notify Students
                        </button>
                        <button onclick="exportSchedule()" class="btn btn-secondary">
                            Export Schedule
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Office Hours Schedule -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-gradient-primary text-white py-3">
                    <h5 class="mb-0"><i class="fas fa-calendar-week me-2"></i>Weekly Office Hours Schedule</h5>
                </div>
                <div class="card-body">
                    @if($officeHours->isNotEmpty())
                        <!-- Weekly Calendar View -->
                        <div class="row mb-4">
                            @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day)
                                <div class="col">
                                    <div class="card h-100 {{ $officeHours->where('day_of_week', $day)->isNotEmpty() ? 'border-primary' : '' }}">
                                        <div class="card-header bg-light py-2">
                                            <h6 class="mb-0 text-center">{{ substr($day, 0, 3) }}</h6>
                                        </div>
                                        <div class="card-body p-2">
                                            @php
                                                $dayHours = $officeHours->where('day_of_week', $day);
                                            @endphp
                                            @if($dayHours->isNotEmpty())
                                                @foreach($dayHours as $hour)
                                                    <div class="mb-2 p-2 bg-primary-soft rounded">
                                                        <div class="text-primary fw-bold small">
                                                            {{ \Carbon\Carbon::parse($hour->start_time)->format('g:i A') }}
                                                        </div>
                                                        <div class="text-muted small">
                                                            to {{ \Carbon\Carbon::parse($hour->end_time)->format('g:i A') }}
                                                        </div>
                                                        <div class="text-muted small">
                                                            <i class="fas fa-map-marker-alt me-1"></i>{{ $hour->location }}
                                                        </div>
                                                        @if($hour->type == 'virtual' || $hour->type == 'both')
                                                            <span class="badge bg-info small">
                                                                <i class="fas fa-video me-1"></i>Virtual
                                                            </span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            @else
                                                <p class="text-muted text-center small">No hours</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- List View -->
                        <hr>
                        <h6 class="fw-bold mb-3">All Office Hours</h6>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Day</th>
                                        <th>Time</th>
                                        <th>Location</th>
                                        <th>Type</th>
                                        <th>Meeting URL</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($officeHours->sortBy('day_of_week')->sortBy('start_time') as $hour)
                                        <tr>
                                            <td>{{ $hour->day_of_week }}</td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($hour->start_time)->format('g:i A') }} - 
                                                {{ \Carbon\Carbon::parse($hour->end_time)->format('g:i A') }}
                                            </td>
                                            <td>
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                {{ $hour->location }}
                                            </td>
                                            <td>
                                                @if($hour->type == 'virtual')
                                                    <span class="badge bg-info">Virtual</span>
                                                @elseif($hour->type == 'both')
                                                    <span class="badge bg-success">Hybrid</span>
                                                @else
                                                    <span class="badge bg-secondary">In-Person</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($hour->meeting_url)
                                                    <a href="{{ $hour->meeting_url }}" target="_blank" class="text-primary">
                                                        <i class="fas fa-external-link-alt"></i> Link
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <form method="POST" action="{{ route('faculty.office.hours.delete', $hour->id) }}" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            onclick="return confirm('Delete this office hour slot?')"
                                                            class="btn btn-sm btn-link text-danger p-0">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Office Hours Scheduled</h5>
                            <p class="text-muted">Add your first office hours using the form.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Upcoming Appointments -->
            <div class="card shadow-sm">
                <div class="card-header bg-gradient-purple text-white py-3">
                    <h5 class="mb-0"><i class="fas fa-user-clock me-2"></i>Upcoming Appointments</h5>
                </div>
                <div class="card-body">
                    @if($appointments->isNotEmpty())
                        <div class="list-group">
                            @foreach($appointments as $appointment)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">
                                                {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('l, M d') }}
                                                at {{ \Carbon\Carbon::parse($appointment->start_time)->format('g:i A') }}
                                            </h6>
                                            <p class="mb-1">
                                                <strong>Student:</strong> {{ $appointment->student_name ?? 'TBD' }}
                                            </p>
                                            <p class="mb-0 text-muted">
                                                <strong>Topic:</strong> {{ $appointment->topic ?? 'General consultation' }}
                                            </p>
                                        </div>
                                        <div>
                                            <button onclick="viewAppointment({{ $appointment->id }})" 
                                                    class="btn btn-sm btn-outline-primary me-1">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button onclick="cancelAppointment({{ $appointment->id }})" 
                                                    class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-center text-muted py-3">No upcoming appointments scheduled.</p>
                    @endif
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
.bg-gradient-purple {
    background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);
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
.bg-primary-soft {
    background-color: rgba(37, 99, 235, 0.1);
}
</style>

<script>
function toggleMeetingUrl(select) {
    const meetingUrlField = document.getElementById('meetingUrlField');
    if (select.value === 'virtual' || select.value === 'both') {
        meetingUrlField.style.display = 'block';
        meetingUrlField.querySelector('input').required = true;
    } else {
        meetingUrlField.style.display = 'none';
        meetingUrlField.querySelector('input').required = false;
    }
}

function cancelToday() {
    if (confirm("Cancel all of today's office hours?")) {
        alert('Cancellation functionality will be implemented soon.');
    }
}

function addSpecialSession() {
    alert('Special session scheduling will be implemented soon.');
}

function notifyStudents() {
    alert('Student notification system will be implemented soon.');
}

function exportSchedule() {
    alert('Schedule export functionality will be implemented soon.');
}

function viewAppointment(id) {
    alert(`View appointment ${id} details - coming soon.`);
}

function cancelAppointment(id) {
    if (confirm('Cancel this appointment?')) {
        alert(`Cancel appointment ${id} - coming soon.`);
    }
}
</script>
@endsection