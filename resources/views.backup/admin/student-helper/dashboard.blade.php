@extends('layouts.app')

@section('title', 'Student Assistance Dashboard - ' . ($student->full_name ?? 'Student'))

@section('content')
<div class="container-fluid py-4">
    <!-- Header with Student Info -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-2">
                                <i class="fas fa-user-graduate me-2"></i>
                                Assisting: {{ $student->full_name ?? 'Student Name' }}
                            </h4>
                            <div class="row">
                                <div class="col-md-4">
                                    <small>Student ID:</small><br>
                                    <strong>{{ $student->student_id ?? 'N/A' }}</strong>
                                </div>
                                <div class="col-md-4">
                                    <small>Program:</small><br>
                                    <strong>{{ $student->program ?? 'N/A' }}</strong>
                                </div>
                                <div class="col-md-4">
                                    <small>Email:</small><br>
                                    <strong>{{ $student->email ?? 'N/A' }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="mb-2">
                                <small>Session Started:</small><br>
                                <strong>{{ $session->started_at->format('h:i A') ?? now()->format('h:i A') }}</strong>
                            </div>
                            <form action="{{ route('admin.student-helper.stop') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-stop me-1"></i>End Session
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2 mb-2">
                            <button class="btn btn-success btn-block" data-bs-toggle="modal" data-bs-target="#paymentModal">
                                <i class="fas fa-dollar-sign me-1"></i>Process Payment
                            </button>
                        </div>
                        <div class="col-md-2 mb-2">
                            <button class="btn btn-info btn-block" data-bs-toggle="modal" data-bs-target="#registrationModal">
                                <i class="fas fa-book me-1"></i>Register Courses
                            </button>
                        </div>
                        <div class="col-md-2 mb-2">
                            <button class="btn btn-warning btn-block" data-bs-toggle="modal" data-bs-target="#dropCourseModal">
                                <i class="fas fa-minus-circle me-1"></i>Drop Course
                            </button>
                        </div>
                        <div class="col-md-2 mb-2">
                            <button class="btn btn-primary btn-block" onclick="viewTranscript()">
                                <i class="fas fa-file-alt me-1"></i>View Transcript
                            </button>
                        </div>
                        <div class="col-md-2 mb-2">
                            <button class="btn btn-secondary btn-block" onclick="printSchedule()">
                                <i class="fas fa-calendar me-1"></i>Print Schedule
                            </button>
                        </div>
                        <div class="col-md-2 mb-2">
                            <button class="btn btn-dark btn-block" data-bs-toggle="modal" data-bs-target="#updateInfoModal">
                                <i class="fas fa-edit me-1"></i>Update Info
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Tabs -->
    <div class="card shadow">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="assistanceTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="academic-tab" data-bs-toggle="tab" href="#academic" role="tab">
                        <i class="fas fa-graduation-cap me-1"></i>Academic
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="financial-tab" data-bs-toggle="tab" href="#financial" role="tab">
                        <i class="fas fa-dollar-sign me-1"></i>Financial
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="registration-tab" data-bs-toggle="tab" href="#registration" role="tab">
                        <i class="fas fa-clipboard-list me-1"></i>Registration
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="personal-tab" data-bs-toggle="tab" href="#personal" role="tab">
                        <i class="fas fa-user me-1"></i>Personal Info
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="activity-tab" data-bs-toggle="tab" href="#activity" role="tab">
                        <i class="fas fa-history me-1"></i>Activity Log
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="assistanceTabContent">
                <!-- Academic Tab -->
                <div class="tab-pane fade show active" id="academic" role="tabpanel">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3">Current Enrollment</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Course Code</th>
                                            <th>Course Name</th>
                                            <th>Credits</th>
                                            <th>Grade</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($currentEnrollments ?? [] as $enrollment)
                                        <tr>
                                            <td>{{ $enrollment->course_code }}</td>
                                            <td>{{ $enrollment->course_name }}</td>
                                            <td>{{ $enrollment->credits }}</td>
                                            <td>{{ $enrollment->grade ?? 'In Progress' }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-muted text-center">No current enrollments</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Academic Standing</h6>
                            <div class="row">
                                <div class="col-6">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h5 class="mb-0">{{ $student->current_gpa ?? '0.00' }}</h5>
                                            <small>Current GPA</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h5 class="mb-0">{{ $student->total_credits ?? '0' }}</h5>
                                            <small>Total Credits</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <strong>Academic Status:</strong> 
                                <span class="badge bg-{{ $student->academic_status === 'good_standing' ? 'success' : 'warning' }}">
                                    {{ ucfirst(str_replace('_', ' ', $student->academic_status ?? 'N/A')) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financial Tab -->
                <div class="tab-pane fade" id="financial" role="tabpanel">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Account Balance</h6>
                                    <h3 class="mb-0 {{ ($account->balance ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                                        ${{ number_format(abs($account->balance ?? 0), 2) }}
                                    </h3>
                                    <small class="text-muted">
                                        {{ ($account->balance ?? 0) > 0 ? 'Amount Due' : 'Credit Balance' }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h6 class="mb-3">Recent Transactions</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Description</th>
                                            <th>Type</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentTransactions ?? [] as $transaction)
                                        <tr>
                                            <td>{{ $transaction->created_at->format('M d, Y') }}</td>
                                            <td>{{ $transaction->description }}</td>
                                            <td>
                                                <span class="badge bg-{{ $transaction->type === 'payment' ? 'success' : 'warning' }}">
                                                    {{ ucfirst($transaction->type) }}
                                                </span>
                                            </td>
                                            <td class="{{ $transaction->type === 'payment' ? 'text-success' : 'text-danger' }}">
                                                {{ $transaction->type === 'payment' ? '-' : '+' }}${{ number_format($transaction->amount, 2) }}
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-muted text-center">No recent transactions</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Registration Tab -->
                <div class="tab-pane fade" id="registration" role="tabpanel">
                    <h6 class="mb-3">Registration History</h6>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Term</th>
                                    <th>Course</th>
                                    <th>Section</th>
                                    <th>Credits</th>
                                    <th>Status</th>
                                    <th>Grade</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($registrationHistory ?? [] as $registration)
                                <tr>
                                    <td>{{ $registration->term }}</td>
                                    <td>{{ $registration->course_code }} - {{ $registration->course_name }}</td>
                                    <td>{{ $registration->section }}</td>
                                    <td>{{ $registration->credits }}</td>
                                    <td>
                                        <span class="badge bg-{{ $registration->status === 'completed' ? 'success' : 'info' }}">
                                            {{ ucfirst($registration->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $registration->grade ?? '-' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-muted text-center">No registration history found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Personal Info Tab -->
                <div class="tab-pane fade" id="personal" role="tabpanel">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3">Contact Information</h6>
                            <dl class="row">
                                <dt class="col-sm-4">Phone:</dt>
                                <dd class="col-sm-8">{{ $student->phone ?? 'N/A' }}</dd>
                                
                                <dt class="col-sm-4">Email:</dt>
                                <dd class="col-sm-8">{{ $student->email ?? 'N/A' }}</dd>
                                
                                <dt class="col-sm-4">Address:</dt>
                                <dd class="col-sm-8">{{ $student->address ?? 'N/A' }}</dd>
                                
                                <dt class="col-sm-4">Emergency Contact:</dt>
                                <dd class="col-sm-8">{{ $student->emergency_contact ?? 'N/A' }}</dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Academic Information</h6>
                            <dl class="row">
                                <dt class="col-sm-4">Program:</dt>
                                <dd class="col-sm-8">{{ $student->program ?? 'N/A' }}</dd>
                                
                                <dt class="col-sm-4">Year Level:</dt>
                                <dd class="col-sm-8">{{ $student->year_level ?? 'N/A' }}</dd>
                                
                                <dt class="col-sm-4">Advisor:</dt>
                                <dd class="col-sm-8">{{ $student->advisor ?? 'N/A' }}</dd>
                                
                                <dt class="col-sm-4">Expected Graduation:</dt>
                                <dd class="col-sm-8">{{ $student->expected_graduation ?? 'N/A' }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- Activity Log Tab -->
                <div class="tab-pane fade" id="activity" role="tabpanel">
                    <h6 class="mb-3">Session Activity Log</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Action</th>
                                    <th>Details</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="activityLog">
                                @forelse($sessionActivities ?? [] as $activity)
                                <tr>
                                    <td>{{ $activity->created_at->format('h:i:s A') }}</td>
                                    <td>{{ $activity->action }}</td>
                                    <td>{{ $activity->details }}</td>
                                    <td>
                                        <span class="badge bg-{{ $activity->status === 'success' ? 'success' : 'warning' }}">
                                            {{ ucfirst($activity->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-muted text-center">No activities logged yet</td>
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

<!-- Payment Processing Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="paymentForm" action="{{ route('admin.student-helper.process-payment') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Process Payment for {{ $student->full_name ?? 'Student' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Current Balance: <strong>${{ number_format($account->balance ?? 0, 2) }}</strong>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="payment_amount" class="form-label">Payment Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="payment_amount" name="amount" 
                                       step="0.01" min="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="payment_method" class="form-label">Payment Method</label>
                            <select class="form-select" id="payment_method" name="payment_method" required>
                                <option value="">Select Method</option>
                                <option value="cash">Cash</option>
                                <option value="check">Check</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="debit_card">Debit Card</option>
                                <option value="ach">ACH Transfer</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="reference_number" class="form-label">Reference Number (Optional)</label>
                        <input type="text" class="form-control" id="reference_number" name="reference_number">
                    </div>
                    
                    <div class="mb-3">
                        <label for="payment_notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="payment_notes" name="notes" rows="2"></textarea>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="sendReceipt" name="send_receipt" value="1">
                        <label class="form-check-label" for="sendReceipt">
                            Send receipt to student's email
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i>Process Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Course Registration Modal -->
<div class="modal fade" id="registrationModal" tabindex="-1" aria-labelledby="registrationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="registrationForm" action="{{ route('admin.student-helper.register-courses') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="registrationModalLabel">Register Courses for {{ $student->full_name ?? 'Student' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Available Courses</h6>
                            <div class="form-group mb-3">
                                <input type="text" class="form-control" id="courseSearch" placeholder="Search courses...">
                            </div>
                            <div class="list-group" id="availableCourses" style="max-height: 400px; overflow-y: auto;">
                                @foreach($availableCourses ?? [] as $course)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0">{{ $course->code }} - {{ $course->name }}</h6>
                                            <small>{{ $course->credits }} credits | {{ $course->available_seats }} seats available</small>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-primary add-course" 
                                                data-course-id="{{ $course->id }}"
                                                data-course-code="{{ $course->code }}"
                                                data-course-name="{{ $course->name }}"
                                                data-course-credits="{{ $course->credits }}">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>Selected Courses</h6>
                            <div class="alert alert-info">
                                Total Credits: <span id="totalCredits">0</span>
                            </div>
                            <div class="list-group" id="selectedCourses" style="max-height: 400px; overflow-y: auto;">
                                <!-- Selected courses will appear here -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="registerButton" disabled>
                        <i class="fas fa-check me-1"></i>Register Selected Courses
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let selectedCourses = [];
let totalCredits = 0;

$(document).ready(function() {
    // Handle payment form submission
    $('#paymentForm').on('submit', function(e) {
        e.preventDefault();
        
        if (!confirm('Are you sure you want to process this payment?')) {
            return;
        }
        
        // Add activity to log
        addActivityToLog('Processing payment', 'Amount: $' + $('#payment_amount').val());
        
        // Submit form
        this.submit();
    });
    
    // Handle course addition
    $('.add-course').on('click', function() {
        const courseId = $(this).data('course-id');
        const courseCode = $(this).data('course-code');
        const courseName = $(this).data('course-name');
        const courseCredits = parseInt($(this).data('course-credits'));
        
        // Check if already selected
        if (selectedCourses.find(c => c.id === courseId)) {
            alert('This course is already selected');
            return;
        }
        
        // Add to selected courses
        selectedCourses.push({
            id: courseId,
            code: courseCode,
            name: courseName,
            credits: courseCredits
        });
        
        // Update UI
        updateSelectedCourses();
        $(this).prop('disabled', true).removeClass('btn-primary').addClass('btn-secondary');
    });
    
    // Course search
    $('#courseSearch').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('#availableCourses .list-group-item').each(function() {
            const text = $(this).text().toLowerCase();
            $(this).toggle(text.includes(searchTerm));
        });
    });
    
    // Handle registration form submission
    $('#registrationForm').on('submit', function(e) {
        e.preventDefault();
        
        if (selectedCourses.length === 0) {
            alert('Please select at least one course');
            return;
        }
        
        // Add selected courses to form
        selectedCourses.forEach(course => {
            $('<input>').attr({
                type: 'hidden',
                name: 'courses[]',
                value: course.id
            }).appendTo(this);
        });
        
        // Add activity to log
        addActivityToLog('Registering courses', selectedCourses.length + ' courses selected');
        
        // Submit form
        this.submit();
    });
});

function updateSelectedCourses() {
    let html = '';
    totalCredits = 0;
    
    selectedCourses.forEach((course, index) => {
        totalCredits += course.credits;
        html += `
            <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">${course.code} - ${course.name}</h6>
                        <small>${course.credits} credits</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeCourse(${index})">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
    });
    
    $('#selectedCourses').html(html || '<div class="text-muted text-center py-3">No courses selected</div>');
    $('#totalCredits').text(totalCredits);
    $('#registerButton').prop('disabled', selectedCourses.length === 0);
}

function removeCourse(index) {
    const course = selectedCourses[index];
    selectedCourses.splice(index, 1);
    
    // Re-enable the add button
    $(`.add-course[data-course-id="${course.id}"]`)
        .prop('disabled', false)
        .removeClass('btn-secondary')
        .addClass('btn-primary');
    
    updateSelectedCourses();
}

function addActivityToLog(action, details) {
    const now = new Date();
    const time = now.toLocaleTimeString();
    
    const row = `
        <tr>
            <td>${time}</td>
            <td>${action}</td>
            <td>${details}</td>
            <td><span class="badge bg-info">Processing</span></td>
        </tr>
    `;
    
    $('#activityLog').prepend(row);
}

function viewTranscript() {
    window.open('/transcripts/view/' + {{ $student->id ?? 0 }}, '_blank');
}

function printSchedule() {
    window.open('/registration/schedule/print?student_id=' + {{ $student->id ?? 0 }}, '_blank');
}
</script>
@endpush
@endsection