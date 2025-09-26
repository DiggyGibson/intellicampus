@extends('layouts.app')

@section('title', 'Student Assistance Portal')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-user-cog me-2"></i>Student Assistance Portal
            </h1>
            <p class="text-muted mt-2">Process transactions and manage student accounts on behalf of students</p>
        </div>
    </div>

    <!-- Active Session Alert -->
    @if(session('assisting_student'))
    <div class="alert alert-info d-flex justify-content-between align-items-center mb-4">
        <div>
            <i class="fas fa-info-circle me-2"></i>
            Currently assisting: <strong>{{ session('assisting_student_name') }}</strong>
            (ID: {{ session('assisting_student_id') }})
        </div>
        <div>
            <a href="{{ route('admin.student-helper.dashboard') }}" class="btn btn-sm btn-primary me-2">
                <i class="fas fa-tachometer-alt me-1"></i>Go to Dashboard
            </a>
            <form action="{{ route('admin.student-helper.stop') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-danger">
                    <i class="fas fa-stop me-1"></i>End Session
                </button>
            </form>
        </div>
    </div>
    @endif

    <!-- Search Section -->
    <div class="card shadow mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-search me-2"></i>Search for Student</h5>
        </div>
        <div class="card-body">
            <form id="studentSearchForm" action="{{ route('admin.student-helper.search') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="search_term">Enter Student ID, Name, or Email</label>
                            <input type="text" 
                                   class="form-control form-control-lg" 
                                   id="search_term" 
                                   name="search_term" 
                                   placeholder="e.g., STU-2025001, John Smith, john@example.com"
                                   required
                                   autofocus>
                            <small class="form-text text-muted">
                                Enter at least 3 characters to search
                            </small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="d-block">&nbsp;</label>
                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                            <i class="fas fa-search me-2"></i>Search Student
                        </button>
                    </div>
                </div>
            </form>

            <!-- Search Results -->
            <div id="searchResults" class="mt-4" style="display: none;">
                <h6 class="mb-3">Search Results:</h6>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Program</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="resultsBody">
                            <!-- Results will be populated here via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Assistance Sessions -->
    <div class="card shadow">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Assistance Sessions</h5>
        </div>
        <div class="card-body">
            @if(isset($recentSessions) && $recentSessions->count() > 0)
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Date/Time</th>
                            <th>Student</th>
                            <th>Duration</th>
                            <th>Actions Performed</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentSessions as $session)
                        <tr>
                            <td>{{ $session->started_at->format('M d, Y H:i') }}</td>
                            <td>
                                <strong>{{ $session->student->full_name }}</strong><br>
                                <small class="text-muted">{{ $session->student->student_id }}</small>
                            </td>
                            <td>
                                @if($session->ended_at)
                                    {{ $session->duration }} mins
                                @else
                                    <span class="badge bg-success">Active</span>
                                @endif
                            </td>
                            <td>
                                {{ $session->actions_count }} action(s)
                            </td>
                            <td>
                                @if($session->ended_at)
                                    <span class="badge bg-secondary">Completed</span>
                                @else
                                    <span class="badge bg-success">In Progress</span>
                                @endif
                            </td>
                            <td>
                                @if(!$session->ended_at)
                                    <a href="{{ route('admin.student-helper.dashboard') }}" 
                                       class="btn btn-sm btn-primary">
                                        Continue
                                    </a>
                                @else
                                    <button class="btn btn-sm btn-outline-info" 
                                            onclick="viewSessionDetails({{ $session->id }})">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-muted text-center py-4">No recent assistance sessions found.</p>
            @endif
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Today's Sessions</h6>
                    <h3 class="mb-0">{{ $todaySessions ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Payments Processed</h6>
                    <h3 class="mb-0">${{ number_format($paymentsProcessed ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6 class="card-title">Registrations</h6>
                    <h3 class="mb-0">{{ $registrationsProcessed ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title">Avg Session Time</h6>
                    <h3 class="mb-0">{{ $avgSessionTime ?? '0' }} mins</h3>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Handle search form submission
    $('#studentSearchForm').on('submit', function(e) {
        e.preventDefault();
        
        const searchTerm = $('#search_term').val();
        if (searchTerm.length < 3) {
            alert('Please enter at least 3 characters to search');
            return;
        }

        // Show loading
        $('#searchResults').show();
        $('#resultsBody').html('<tr><td colspan="6" class="text-center"><i class="fas fa-spinner fa-spin"></i> Searching...</td></tr>');

        // Perform AJAX search
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.students && response.students.length > 0) {
                    let html = '';
                    response.students.forEach(function(student) {
                        html += `
                            <tr>
                                <td>${student.student_id}</td>
                                <td>${student.full_name}</td>
                                <td>${student.email}</td>
                                <td>${student.program || 'N/A'}</td>
                                <td>
                                    <span class="badge bg-${student.status === 'active' ? 'success' : 'secondary'}">
                                        ${student.status}
                                    </span>
                                </td>
                                <td>
                                    <form action="{{ route('admin.student-helper.start') }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="student_id" value="${student.id}">
                                        <button type="submit" class="btn btn-sm btn-primary">
                                            <i class="fas fa-user-cog"></i> Assist
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        `;
                    });
                    $('#resultsBody').html(html);
                } else {
                    $('#resultsBody').html('<tr><td colspan="6" class="text-center text-muted">No students found matching your search.</td></tr>');
                }
            },
            error: function() {
                $('#resultsBody').html('<tr><td colspan="6" class="text-center text-danger">An error occurred while searching. Please try again.</td></tr>');
            }
        });
    });
});

function viewSessionDetails(sessionId) {
    // Implement view session details modal
    alert('View session details for session ' + sessionId);
}
</script>
@endpush
@endsection