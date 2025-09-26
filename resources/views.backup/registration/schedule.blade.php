@extends('layouts.app')

@section('title', 'My Schedule')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i class="fas fa-chevron-right"></i>
    <span>My Schedule</span>
@endsection

@section('page-actions')
    <a href="{{ route('registration.catalog') }}" class="btn btn-primary me-2">
        <i class="fas fa-plus me-1"></i> Add Courses
    </a>
    <button onclick="window.print()" class="btn btn-secondary me-2">
        <i class="fas fa-print me-1"></i> Print
    </button>
    <div class="btn-group" role="group">
        <button type="button" class="btn btn-outline-info dropdown-toggle" data-bs-toggle="dropdown">
            <i class="fas fa-ellipsis-v me-1"></i> More
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('registration.history') }}">
                <i class="fas fa-history me-2"></i>Registration History
            </a></li>
            <li><a class="dropdown-item" href="{{ route('registration.holds') }}">
                <i class="fas fa-ban me-2"></i>View Holds
            </a></li>
            <li><a class="dropdown-item" href="{{ route('registration.waitlist') }}">
                <i class="fas fa-clock me-2"></i>Waitlist Status
            </a></li>
        </ul>
    </div>
@endsection

@section('content')
<div class="container-fluid px-4">
    <!-- Flash Message for Admin/Faculty -->
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            {{ session('info') }}
            <div class="mt-2">
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-decoration-underline">
                    Logout
                </a> and login with student credentials:
                <strong>student@intellicampus.edu / Student123!</strong>
            </div>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Header -->
    <div class="mb-4">
        <h2 class="fw-bold text-dark mb-1">My Schedule</h2>
        <p class="text-muted">
            @if($currentTerm)
                {{ $currentTerm->name }} - {{ $currentTerm->academic_year }}
            @else
                Current Term
            @endif
        </p>
    </div>

    <!-- Schedule Summary -->
    <div class="card shadow-sm mb-4">
        <div class="card-body bg-primary-soft">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="mb-0">Schedule Summary</h5>
                    <p class="mb-0 mt-1">
                        <strong>{{ $enrollments->count() }}</strong> course(s) | 
                        <strong>{{ $totalCredits }}</strong> total credits
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    @if($totalCredits < 12)
                        <span class="badge bg-warning text-dark p-2">
                            Part-Time Status (Less than 12 credits)
                        </span>
                    @else
                        <span class="badge bg-success p-2">
                            Full-Time Status ({{ $totalCredits }} credits)
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($enrollments->isEmpty())
        <!-- No Enrollments -->
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No courses registered</h5>
                <p class="text-muted">You haven't registered for any courses yet</p>
                <a href="{{ route('registration.catalog') }}" class="btn btn-primary mt-3">
                    <i class="fas fa-book me-1"></i> Browse Course Catalog
                </a>
            </div>
        </div>
    @else
        <!-- Weekly Schedule Grid -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-gradient-primary text-white py-3">
                <h5 class="mb-0"><i class="fas fa-calendar-week me-2"></i>Weekly Schedule</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered schedule-table">
                        <thead class="table-light">
                            <tr>
                                <th width="8%" class="text-center">Time</th>
                                <th width="15%" class="text-center">Monday</th>
                                <th width="15%" class="text-center">Tuesday</th>
                                <th width="15%" class="text-center">Wednesday</th>
                                <th width="15%" class="text-center">Thursday</th>
                                <th width="15%" class="text-center">Friday</th>
                                <th width="17%" class="text-center bg-info-soft">Saturday</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                // Extended hours from 7 AM to 9 PM
                                $timeSlots = [];
                                for($h = 7; $h <= 21; $h++) {
                                    $timeSlots[] = sprintf('%02d:00', $h);
                                }
                                
                                // Build the weekly schedule with actual course times
                                $scheduleGrid = [];
                                foreach($enrollments as $enrollment) {
                                    if($enrollment->days_of_week && $enrollment->start_time) {
                                        $days = explode(',', str_replace(' ', '', $enrollment->days_of_week));
                                        $startHour = date('H:00', strtotime($enrollment->start_time));
                                        $endHour = date('H:00', strtotime($enrollment->end_time));
                                        
                                        foreach($days as $dayAbbr) {
                                            $dayName = match(trim($dayAbbr)) {
                                                'M', 'Mo' => 'Monday',
                                                'T', 'Tu' => 'Tuesday',
                                                'W', 'We' => 'Wednesday',
                                                'Th', 'R' => 'Thursday',
                                                'F', 'Fr' => 'Friday',
                                                'S', 'Sa' => 'Saturday',
                                                default => null
                                            };
                                            
                                            if($dayName) {
                                                // Calculate duration in hours
                                                $startTimeObj = new DateTime($enrollment->start_time);
                                                $endTimeObj = new DateTime($enrollment->end_time);
                                                $duration = $startTimeObj->diff($endTimeObj)->h;
                                                
                                                if(!isset($scheduleGrid[$dayName])) {
                                                    $scheduleGrid[$dayName] = [];
                                                }
                                                
                                                $scheduleGrid[$dayName][$startHour] = [
                                                    'course_code' => $enrollment->course_code,
                                                    'room' => $enrollment->room ?: 'Online',
                                                    'start_time' => date('g:i A', strtotime($enrollment->start_time)),
                                                    'end_time' => date('g:i A', strtotime($enrollment->end_time)),
                                                    'duration' => max(1, $duration), // Minimum 1 hour
                                                    'section' => $enrollment->section_number
                                                ];
                                            }
                                        }
                                    }
                                }
                            @endphp
                            
                            @foreach($timeSlots as $hour)
                                <tr>
                                    <td class="time-slot">
                                        <small class="fw-bold">{{ date('g:i A', strtotime($hour)) }}</small>
                                    </td>
                                    @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $day)
                                        @php
                                            $hasClass = isset($scheduleGrid[$day][$hour]);
                                            $classInfo = $hasClass ? $scheduleGrid[$day][$hour] : null;
                                            
                                            // Check if this cell should be skipped due to rowspan
                                            $skipCell = false;
                                            foreach($scheduleGrid[$day] ?? [] as $classHour => $class) {
                                                if($classHour < $hour) {
                                                    $classEndHour = date('H:00', strtotime($classHour) + ($class['duration'] * 3600));
                                                    if($classEndHour > $hour) {
                                                        $skipCell = true;
                                                        break;
                                                    }
                                                }
                                            }
                                        @endphp
                                        
                                        @if(!$skipCell)
                                            <td class="text-center {{ $day == 'Saturday' ? 'bg-info-soft' : '' }}" 
                                                {{ $hasClass && $classInfo['duration'] > 1 ? 'rowspan=' . $classInfo['duration'] : '' }}>
                                                @if($hasClass)
                                                    <div class="schedule-item {{ $classInfo['duration'] > 1 ? 'schedule-item-tall' : '' }}">
                                                        <div class="fw-bold">{{ $classInfo['course_code'] }}</div>
                                                        <small class="d-block">Sec {{ $classInfo['section'] }}</small>
                                                        <small class="d-block">{{ $classInfo['room'] }}</small>
                                                        <small class="text-muted">
                                                            {{ $classInfo['start_time'] }} - {{ $classInfo['end_time'] }}
                                                        </small>
                                                    </div>
                                                @endif
                                            </td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-2">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Showing classes from 7:00 AM to 10:00 PM. Saturday classes are highlighted in blue.
                    </small>
                </div>
            </div>
        </div>

        <!-- Course List -->
        <div class="card shadow-sm">
            <div class="card-header bg-gradient-success text-white py-3">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Course Details</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Course</th>
                            <th>Section</th>
                            <th>Schedule</th>
                            <th>Instructor</th>
                            <th>Credits</th>
                            <th>Mode</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($enrollments as $enrollment)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $enrollment->course_code }}</div>
                                    <small class="text-muted">{{ $enrollment->title }}</small>
                                </td>
                                <td>
                                    <div>Section {{ $enrollment->section_number }}</div>
                                    <small class="text-muted">CRN: {{ $enrollment->crn }}</small>
                                </td>
                                <td>
                                    @if($enrollment->days_of_week && $enrollment->start_time && $enrollment->end_time)
                                        <div>{{ $enrollment->days_of_week }}</div>
                                        <small>
                                            @php
                                                // Handle time display with proper formatting
                                                $startTime = $enrollment->start_time;
                                                $endTime = $enrollment->end_time;
                                                
                                                // Check if times are already in proper format or need parsing
                                                if (strpos($startTime, ':') === false) {
                                                    // Assume it's in a format that needs parsing
                                                    $startFormatted = date('g:i A', strtotime($startTime));
                                                    $endFormatted = date('g:i A', strtotime($endTime));
                                                } else {
                                                    // Parse as time
                                                    $startFormatted = date('g:i A', strtotime($startTime));
                                                    // For end time, ensure it's not showing AM when it should be PM
                                                    $endFormatted = date('g:i A', strtotime($endTime));
                                                    
                                                    // Check if end time is before start time (indicating next day or PM)
                                                    if (strtotime($endTime) < strtotime($startTime)) {
                                                        // Likely a PM class ending, adjust accordingly
                                                        $endFormatted = date('g:i A', strtotime($endTime . ' +12 hours'));
                                                    }
                                                }
                                            @endphp
                                            {{ $startFormatted }} - {{ $endFormatted }}
                                        </small>
                                        <small class="d-block text-muted">{{ $enrollment->room ?: 'Online' }}</small>
                                    @else
                                        <span class="text-muted">TBA</span>
                                    @endif
                                </td>
                                <td>{{ $enrollment->instructor_name ?? 'TBA' }}</td>
                                <td>{{ $enrollment->credits }}</td>
                                <td>
                                    @php
                                        $modeClass = match($enrollment->delivery_mode) {
                                            'traditional' => 'bg-success',
                                            'online_sync' => 'bg-primary',
                                            'online_async' => 'bg-purple',
                                            'hybrid' => 'bg-warning',
                                            default => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $modeClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $enrollment->delivery_mode)) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button onclick="dropCourse({{ $enrollment->id }})"
                                            class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-times me-1"></i> Drop
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-light">
                <div class="text-end">
                    <strong>Total Credits:</strong> {{ $totalCredits }}
                </div>
            </div>
        </div>
    @endif

    <!-- Additional Information -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-gradient-info text-white py-3">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Important Dates</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-calendar-check text-success me-2"></i>
                            <strong>Add/Drop Deadline:</strong> 
                            <span class="text-muted">{{ now()->addDays(14)->format('M d, Y') }}</span>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-calendar-times text-warning me-2"></i>
                            <strong>Withdrawal Deadline:</strong> 
                            <span class="text-muted">{{ now()->addDays(60)->format('M d, Y') }}</span>
                        </li>
                        <li>
                            <i class="fas fa-graduation-cap text-primary me-2"></i>
                            <strong>Finals Week:</strong> 
                            <span class="text-muted">{{ now()->addDays(120)->format('M d-') }}{{ now()->addDays(125)->format('d, Y') }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-gradient-warning text-white py-3">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Academic Policies</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Minimum 12 credits for full-time status
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Maximum 18 credits without override
                        </li>
                        <li>
                            <i class="fas fa-check text-success me-2"></i>
                            Drops after deadline result in 'W' grade
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
}
.bg-gradient-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}
.bg-gradient-info {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
}
.bg-gradient-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}
.bg-purple {
    background-color: #6f42c1;
}
.bg-primary-soft {
    background-color: rgba(37, 99, 235, 0.1);
}
.bg-info-soft {
    background-color: rgba(23, 162, 184, 0.05);
}
.schedule-table td {
    min-height: 60px;
    vertical-align: middle;
    position: relative;
}
.time-slot {
    font-weight: bold;
    background-color: #f8f9fa;
    text-align: center;
}
.schedule-item {
    background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
    color: white;
    padding: 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.875rem;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.schedule-item-tall {
    min-height: 100px;
}
@media print {
    .no-print {
        display: none !important;
    }
    body {
        font-size: 12pt;
    }
    .card {
        border: 1px solid #000 !important;
    }
}
</style>

<script>
function dropCourse(enrollmentId) {
    if (confirm('Are you sure you want to drop this course? This action cannot be undone.')) {
        fetch('{{ route("registration.drop") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                enrollment_id: enrollmentId
            })
        })
        .then(response => {
            if (response.redirected) {
                window.location.href = response.url;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to drop course. Please try again.');
        });
    }
}
</script>
@endsection