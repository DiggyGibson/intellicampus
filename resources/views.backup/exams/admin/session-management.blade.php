{{-- File: resources/views/exams/admin/session-management.blade.php --}}
@extends('layouts.app')

@section('title', 'Session Management - ' . $session->session_code)

@section('styles')
<style>
    .seat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(60px, 1fr));
        gap: 10px;
        padding: 20px;
    }
    .seat {
        width: 60px;
        height: 60px;
        border: 2px solid #dee2e6;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s;
        font-weight: bold;
    }
    .seat.occupied {
        background: #28a745;
        color: white;
        border-color: #28a745;
    }
    .seat.empty {
        background: #f8f9fa;
    }
    .seat.selected {
        background: #007bff;
        color: white;
        border-color: #007bff;
    }
    .seat.blocked {
        background: #dc3545;
        color: white;
        border-color: #dc3545;
        cursor: not-allowed;
    }
    .seat:hover:not(.blocked) {
        transform: scale(1.1);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    
    .timeline {
        position: relative;
        padding-left: 40px;
    }
    .timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #dee2e6;
    }
    .timeline-item {
        position: relative;
        padding-bottom: 20px;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -25px;
        top: 5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #007bff;
        border: 2px solid #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .timeline-item.completed::before {
        background: #28a745;
    }
    .timeline-item.current::before {
        background: #ffc107;
        animation: pulse 2s infinite;
    }
    
    .proctor-card {
        border-left: 4px solid #007bff;
        transition: all 0.3s;
    }
    .proctor-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .stat-widget {
        text-align: center;
        padding: 20px;
        border-radius: 10px;
        background: white;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .stat-widget .stat-value {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 5px;
    }
    .stat-widget .stat-label {
        color: #6c757d;
        font-size: 0.9rem;
    }
    
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(255, 193, 7, 0); }
        100% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0); }
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-calendar-check me-2"></i>Session Management
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('exams.admin.dashboard') }}">Exam Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('exams.admin.manage', $session->exam_id) }}">{{ $session->exam->exam_code }}</a></li>
                            <li class="breadcrumb-item active">{{ $session->session_code }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="btn-toolbar">
                    <button type="button" class="btn btn-outline-primary me-2" onclick="printSessionDetails()">
                        <i class="fas fa-print"></i> Print
                    </button>
                    @if($session->status == 'scheduled')
                    <button type="button" class="btn btn-success" onclick="startSession()">
                        <i class="fas fa-play"></i> Start Session
                    </button>
                    @elseif($session->status == 'in_progress')
                    <button type="button" class="btn btn-warning" onclick="endSession()">
                        <i class="fas fa-stop"></i> End Session
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Session Overview --}}
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Session Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-5">Session Code:</dt>
                                <dd class="col-7"><strong>{{ $session->session_code }}</strong></dd>
                                
                                <dt class="col-5">Exam:</dt>
                                <dd class="col-7">{{ $session->exam->exam_name }}</dd>
                                
                                <dt class="col-5">Date:</dt>
                                <dd class="col-7">{{ \Carbon\Carbon::parse($session->session_date)->format('l, M d, Y') }}</dd>
                                
                                <dt class="col-5">Time:</dt>
                                <dd class="col-7">{{ $session->start_time }} - {{ $session->end_time }}</dd>
                                
                                <dt class="col-5">Duration:</dt>
                                <dd class="col-7">{{ $session->exam->duration_minutes }} minutes</dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-5">Center:</dt>
                                <dd class="col-7">{{ $session->center->center_name }}</dd>
                                
                                <dt class="col-5">Type:</dt>
                                <dd class="col-7">{{ ucfirst(str_replace('_', ' ', $session->exam->delivery_mode)) }}</dd>
                                
                                <dt class="col-5">Capacity:</dt>
                                <dd class="col-7">{{ $session->capacity }} seats</dd>
                                
                                <dt class="col-5">Registered:</dt>
                                <dd class="col-7">
                                    <span class="badge bg-{{ $session->registered_count >= $session->capacity ? 'danger' : 'success' }}">
                                        {{ $session->registered_count }} / {{ $session->capacity }}
                                    </span>
                                </dd>
                                
                                <dt class="col-5">Status:</dt>
                                <dd class="col-7">
                                    <span class="badge bg-{{ $session->status == 'scheduled' ? 'info' : ($session->status == 'in_progress' ? 'warning' : 'success') }}">
                                        {{ ucfirst(str_replace('_', ' ', $session->status)) }}
                                    </span>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            {{-- Statistics Widgets --}}
            <div class="row">
                <div class="col-6 mb-3">
                    <div class="stat-widget">
                        <div class="stat-value text-success">{{ $stats['present'] ?? 0 }}</div>
                        <div class="stat-label">Present</div>
                    </div>
                </div>
                <div class="col-6 mb-3">
                    <div class="stat-widget">
                        <div class="stat-value text-danger">{{ $stats['absent'] ?? 0 }}</div>
                        <div class="stat-label">Absent</div>
                    </div>
                </div>
                <div class="col-6 mb-3">
                    <div class="stat-widget">
                        <div class="stat-value text-warning">{{ $stats['late'] ?? 0 }}</div>
                        <div class="stat-label">Late Arrivals</div>
                    </div>
                </div>
                <div class="col-6 mb-3">
                    <div class="stat-widget">
                        <div class="stat-value text-info">{{ $stats['issues'] ?? 0 }}</div>
                        <div class="stat-label">Issues</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content Tabs --}}
    <div class="card shadow">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#candidates">
                        <i class="fas fa-users"></i> Candidates
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#seating">
                        <i class="fas fa-th"></i> Seating Arrangement
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#proctors">
                        <i class="fas fa-user-tie"></i> Proctors
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#timeline">
                        <i class="fas fa-clock"></i> Timeline
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#issues">
                        <i class="fas fa-exclamation-triangle"></i> Issues
                        @if($issueCount > 0)
                        <span class="badge bg-danger">{{ $issueCount }}</span>
                        @endif
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                {{-- Candidates Tab --}}
                <div class="tab-pane fade show active" id="candidates">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" placeholder="Search candidates..." id="searchCandidates">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="filterAttendance">
                                <option value="">All Attendance</option>
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="pending">Not Marked</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="filterRoom">
                                <option value="">All Rooms</option>
                                @foreach($rooms ?? [] as $room)
                                <option value="{{ $room }}">Room {{ $room }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-primary w-100" onclick="markAllPresent()">
                                Mark All Present
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover" id="candidatesTable">
                            <thead>
                                <tr>
                                    <th width="50">
                                        <input type="checkbox" class="form-check-input" id="selectAllCandidates">
                                    </th>
                                    <th>Registration #</th>
                                    <th>Hall Ticket</th>
                                    <th>Name</th>
                                    <th>Seat #</th>
                                    <th>Room</th>
                                    <th>Attendance</th>
                                    <th>Check-in Time</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($candidates as $candidate)
                                <tr data-candidate-id="{{ $candidate->id }}">
                                    <td>
                                        <input type="checkbox" class="form-check-input candidate-check" value="{{ $candidate->id }}">
                                    </td>
                                    <td>{{ $candidate->registration->registration_number }}</td>
                                    <td>{{ $candidate->registration->hall_ticket_number }}</td>
                                    <td>
                                        <strong>{{ $candidate->registration->candidate_name }}</strong>
                                        <br><small class="text-muted">{{ $candidate->registration->candidate_email }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $candidate->seat_number }}</span>
                                    </td>
                                    <td>{{ $candidate->room_number }}</td>
                                    <td>
                                        @if($candidate->attendance_marked)
                                            <span class="badge bg-success">Present</span>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-success" 
                                                    onclick="markPresent({{ $candidate->id }})">
                                                Mark Present
                                            </button>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $candidate->check_in_time ? \Carbon\Carbon::parse($candidate->check_in_time)->format('h:i A') : '-' }}
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-info" onclick="viewCandidate({{ $candidate->id }})">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-warning" onclick="reportIssue({{ $candidate->id }})">
                                                <i class="fas fa-exclamation"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Seating Arrangement Tab --}}
                <div class="tab-pane fade" id="seating">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6>Room Layout - {{ $session->center->center_name }}</h6>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="d-inline-flex align-items-center">
                                <div class="me-3">
                                    <span class="seat empty d-inline-block" style="width: 20px; height: 20px;"></span> Empty
                                </div>
                                <div class="me-3">
                                    <span class="seat occupied d-inline-block" style="width: 20px; height: 20px;"></span> Occupied
                                </div>
                                <div class="me-3">
                                    <span class="seat blocked d-inline-block" style="width: 20px; height: 20px;"></span> Blocked
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="seat-grid" id="seatGrid">
                                @for($i = 1; $i <= $session->capacity; $i++)
                                    @php
                                        $seatData = $seatAllocations[$i] ?? null;
                                        $seatClass = $seatData ? 'occupied' : 'empty';
                                        if (in_array($i, $blockedSeats ?? [])) {
                                            $seatClass = 'blocked';
                                        }
                                    @endphp
                                    <div class="seat {{ $seatClass }}" 
                                         data-seat="{{ $i }}"
                                         @if($seatData)
                                         data-candidate="{{ $seatData->candidate_name }}"
                                         data-registration="{{ $seatData->registration_number }}"
                                         @endif>
                                        {{ $i }}
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="button" class="btn btn-primary" onclick="autoAssignSeats()">
                            <i class="fas fa-random"></i> Auto-Assign Seats
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="exportSeatingChart()">
                            <i class="fas fa-download"></i> Export Seating Chart
                        </button>
                    </div>
                </div>

                {{-- Proctors Tab --}}
                <div class="tab-pane fade" id="proctors">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <h6>Assigned Proctors</h6>
                        </div>
                        <div class="col-md-4 text-end">
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#assignProctorModal">
                                <i class="fas fa-plus"></i> Assign Proctor
                            </button>
                        </div>
                    </div>

                    <div class="row">
                        @forelse($proctors ?? [] as $proctor)
                        <div class="col-md-6 mb-3">
                            <div class="card proctor-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-lg me-3">
                                            <span class="avatar-title rounded-circle bg-primary text-white" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                                {{ substr($proctor->name, 0, 2) }}
                                            </span>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ $proctor->name }}</h6>
                                            <p class="text-muted mb-0">
                                                <i class="fas fa-id-badge me-1"></i>{{ $proctor->employee_id }}<br>
                                                <i class="fas fa-phone me-1"></i>{{ $proctor->phone }}<br>
                                                <i class="fas fa-users me-1"></i>Monitoring {{ $proctor->assigned_candidates ?? 30 }} candidates
                                            </p>
                                        </div>
                                        <div>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeProctor({{ $proctor->id }})">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                No proctors assigned yet. Please assign proctors for this session.
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>

                {{-- Timeline Tab --}}
                <div class="tab-pane fade" id="timeline">
                    <div class="timeline">
                        @foreach($timelineEvents ?? [] as $event)
                        <div class="timeline-item {{ $event->status }}">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="mb-1">{{ $event->title }}</h6>
                                    <p class="text-muted mb-0">{{ $event->description }}</p>
                                </div>
                                <div class="text-muted">
                                    {{ $event->time }}
                                </div>
                            </div>
                        </div>
                        @endforeach
                        
                        {{-- Default timeline if no events --}}
                        @if(empty($timelineEvents))
                        <div class="timeline-item completed">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="mb-1">Session Created</h6>
                                    <p class="text-muted mb-0">Session scheduled and candidates assigned</p>
                                </div>
                                <div class="text-muted">
                                    {{ $session->created_at->format('h:i A') }}
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item current">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="mb-1">Waiting for Session Start</h6>
                                    <p class="text-muted mb-0">Session will begin at {{ $session->start_time }}</p>
                                </div>
                                <div class="text-muted">
                                    Current
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="mb-1">Session Start</h6>
                                    <p class="text-muted mb-0">Candidates enter examination hall</p>
                                </div>
                                <div class="text-muted">
                                    {{ $session->start_time }}
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="mb-1">Exam Begins</h6>
                                    <p class="text-muted mb-0">Question papers distributed</p>
                                </div>
                                <div class="text-muted">
                                    -
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="mb-1">Session End</h6>
                                    <p class="text-muted mb-0">Collection of answer sheets</p>
                                </div>
                                <div class="text-muted">
                                    {{ $session->end_time }}
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Issues Tab --}}
                <div class="tab-pane fade" id="issues">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <h6>Reported Issues</h6>
                        </div>
                        <div class="col-md-4 text-end">
                            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#reportIssueModal">
                                <i class="fas fa-exclamation-triangle"></i> Report Issue
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Candidate</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($issues ?? [] as $issue)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($issue->created_at)->format('h:i A') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $issue->type == 'technical' ? 'danger' : 'warning' }}">
                                            {{ ucfirst($issue->type) }}
                                        </span>
                                    </td>
                                    <td>{{ $issue->description }}</td>
                                    <td>{{ $issue->candidate_name ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $issue->status == 'resolved' ? 'success' : 'warning' }}">
                                            {{ ucfirst($issue->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($issue->status != 'resolved')
                                        <button type="button" class="btn btn-sm btn-success" onclick="resolveIssue({{ $issue->id }})">
                                            Resolve
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        No issues reported yet
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Assign Proctor Modal --}}
<div class="modal fade" id="assignProctorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Proctor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="assignProctorForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Staff Member</label>
                        <select class="form-select" name="proctor_id" required>
                            <option value="">Choose...</option>
                            @foreach($availableProctors ?? [] as $staff)
                            <option value="{{ $staff->id }}">{{ $staff->name }} - {{ $staff->employee_id }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assign to Room (Optional)</label>
                        <select class="form-select" name="room_number">
                            <option value="">All Rooms</option>
                            @foreach($rooms ?? [] as $room)
                            <option value="{{ $room }}">Room {{ $room }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Special Instructions</label>
                        <textarea class="form-control" name="instructions" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign Proctor</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Report Issue Modal --}}
<div class="modal fade" id="reportIssueModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Report Issue</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="reportIssueForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Issue Type</label>
                        <select class="form-select" name="issue_type" required>
                            <option value="">Select Type</option>
                            <option value="technical">Technical Issue</option>
                            <option value="medical">Medical Emergency</option>
                            <option value="misconduct">Misconduct</option>
                            <option value="facility">Facility Issue</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Related Candidate (Optional)</label>
                        <select class="form-select" name="candidate_id">
                            <option value="">Not Specific to Candidate</option>
                            @foreach($candidates as $candidate)
                            <option value="{{ $candidate->id }}">
                                {{ $candidate->registration->candidate_name }} - {{ $candidate->seat_number }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Priority</label>
                        <select class="form-select" name="priority" required>
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Report Issue</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Session management functions
    function startSession() {
        if (confirm('Start this examination session? This will allow candidates to begin.')) {
            // Implementation
            window.location.href = '{{ route("exams.admin.session.start", $session->id) }}';
        }
    }

    function endSession() {
        if (confirm('End this examination session? Make sure all answer sheets are collected.')) {
            // Implementation
            window.location.href = '{{ route("exams.admin.session.end", $session->id) }}';
        }
    }

    // Attendance functions
    function markPresent(candidateId) {
        // AJAX call to mark present
        fetch(`/api/exams/sessions/{{ $session->id }}/attendance/${candidateId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ status: 'present' })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }

    function markAllPresent() {
        if (confirm('Mark all candidates as present?')) {
            // Implementation
        }
    }

    // Seating functions
    function autoAssignSeats() {
        if (confirm('Auto-assign seats to all candidates?')) {
            // Implementation
        }
    }

    function exportSeatingChart() {
        window.location.href = '{{ route("exams.admin.session.seating.export", $session->id) }}';
    }

    // Proctor functions
    function removeProctor(proctorId) {
        if (confirm('Remove this proctor from the session?')) {
            // Implementation
        }
    }

    // Issue functions
    function reportIssue(candidateId = null) {
        if (candidateId) {
            document.querySelector('#reportIssueModal select[name="candidate_id"]').value = candidateId;
        }
        new bootstrap.Modal(document.getElementById('reportIssueModal')).show();
    }

    function resolveIssue(issueId) {
        // Implementation
    }

    // Form submissions
    document.getElementById('assignProctorForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        // Implementation
    });

    document.getElementById('reportIssueForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        // Implementation
    });

    // Print function
    function printSessionDetails() {
        window.print();
    }

    // Candidate search/filter
    document.getElementById('searchCandidates')?.addEventListener('input', function() {
        // Implementation for search
    });

    // Auto-refresh for live updates
    if ('{{ $session->status }}' === 'in_progress') {
        setInterval(() => {
            // Refresh statistics
            fetch(`/api/exams/sessions/{{ $session->id }}/stats`)
                .then(response => response.json())
                .then(data => {
                    // Update statistics
                });
        }, 30000); // Every 30 seconds
    }
</script>
@endsection