@extends('layouts.app')

@section('title', 'My Waitlisted Courses')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i class="fas fa-chevron-right"></i>
    <a href="{{ route('registration.catalog') }}">Course Catalog</a>
    <i class="fas fa-chevron-right"></i>
    <span>Waitlist</span>
@endsection

@section('page-actions')
    <a href="{{ route('registration.catalog') }}" class="btn btn-primary me-2">
        <i class="fas fa-book me-1"></i> Browse Catalog
    </a>
    <a href="{{ route('registration.schedule') }}" class="btn btn-success">
        <i class="fas fa-calendar-alt me-1"></i> My Schedule
    </a>
@endsection

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="mb-4">
        <h2 class="fw-bold text-dark mb-1">My Waitlisted Courses</h2>
        <p class="text-muted">Track your position on course waitlists</p>
    </div>
    
    @if($waitlisted->count() > 0)
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-gradient-primary text-white py-3">
                <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Waitlisted Courses</h5>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="100">Position</th>
                            <th>Course</th>
                            <th>Section</th>
                            <th>Credits</th>
                            <th>Schedule</th>
                            <th>Instructor</th>
                            <th>Added On</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($waitlisted as $entry)
                            <tr>
                                <td>
                                    @php
                                        $positionClass = match(true) {
                                            $entry->position <= 3 => 'bg-success',
                                            $entry->position <= 10 => 'bg-warning',
                                            default => 'bg-secondary'
                                        };
                                        $positionText = match(true) {
                                            $entry->position <= 3 => 'High chance',
                                            $entry->position <= 10 => 'Moderate chance',
                                            default => 'Low chance'
                                        };
                                    @endphp
                                    <span class="badge {{ $positionClass }} position-badge">
                                        #{{ $entry->position }}
                                    </span>
                                    <small class="d-block text-muted mt-1">{{ $positionText }}</small>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $entry->course_code }}</div>
                                    <small class="text-muted">{{ $entry->title }}</small>
                                </td>
                                <td>{{ $entry->section_number }}</td>
                                <td>{{ $entry->credits }}</td>
                                <td>
                                    @if($entry->days_of_week && $entry->start_time)
                                        <div>{{ $entry->days_of_week }}</div>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($entry->start_time)->format('g:i A') }} -
                                            {{ \Carbon\Carbon::parse($entry->end_time)->format('g:i A') }}
                                        </small>
                                        @if($entry->room)
                                            <small class="d-block text-muted">
                                                {{ $entry->building }} {{ $entry->room }}
                                            </small>
                                        @endif
                                    @else
                                        <span class="text-muted">Online/Async</span>
                                    @endif
                                </td>
                                <td>{{ $entry->instructor_name ?? 'TBA' }}</td>
                                <td>
                                    <small>{{ \Carbon\Carbon::parse($entry->created_at ?? $entry->added_at ?? now())->format('M j, Y') }}</small>
                                    <small class="d-block text-muted">
                                        {{ \Carbon\Carbon::parse($entry->created_at ?? $entry->added_at ?? now())->diffForHumans() }}
                                    </small>
                                </td>
                                <td class="text-center">
                                    <form method="POST" action="{{ route('registration.waitlist.leave') }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="waitlist_id" value="{{ $entry->id }}">
                                        <button type="submit" 
                                                onclick="return confirm('Are you sure you want to leave the waitlist for {{ $entry->course_code }}?')"
                                                class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-times me-1"></i> Leave
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Waitlist Information Cards -->
        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-gradient-info text-white py-3">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Waitlist Information</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-3">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <strong>Automatic Enrollment:</strong> You'll be enrolled automatically when a spot opens
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-envelope text-primary me-2"></i>
                                <strong>Email Notifications:</strong> You'll receive an email when enrolled from waitlist
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-sync text-info me-2"></i>
                                <strong>Real-time Updates:</strong> Positions update as students drop courses
                            </li>
                            <li>
                                <i class="fas fa-calendar-alt text-warning me-2"></i>
                                <strong>Deadline:</strong> Waitlists close after the add/drop period ends
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-gradient-success text-white py-3">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Position Guide</h5>
                    </div>
                    <div class="card-body">
                        <div class="position-guide">
                            <div class="position-item mb-3">
                                <span class="badge bg-success me-2">Position 1-3</span>
                                <div class="progress flex-grow-1" style="height: 20px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: 90%">
                                        High chance (90%)
                                    </div>
                                </div>
                            </div>
                            <div class="position-item mb-3">
                                <span class="badge bg-warning me-2">Position 4-10</span>
                                <div class="progress flex-grow-1" style="height: 20px;">
                                    <div class="progress-bar bg-warning" role="progressbar" style="width: 50%">
                                        Moderate chance (50%)
                                    </div>
                                </div>
                            </div>
                            <div class="position-item">
                                <span class="badge bg-secondary me-2">Position 11+</span>
                                <div class="progress flex-grow-1" style="height: 20px;">
                                    <div class="progress-bar bg-secondary" role="progressbar" style="width: 20%">
                                        Low chance (20%)
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">No Waitlisted Courses</h4>
                <p class="text-muted">You are not currently on any course waitlists.</p>
                <a href="{{ route('registration.catalog') }}" class="btn btn-primary mt-3">
                    <i class="fas fa-book me-1"></i> Browse Course Catalog
                </a>
            </div>
        </div>
    @endif
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
.position-badge {
    font-size: 1.1em;
    padding: 0.5em 0.75em;
}
.position-item {
    display: flex;
    align-items: center;
    gap: 1rem;
}
.position-item .badge {
    min-width: 100px;
}
</style>
@endsection