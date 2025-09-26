@extends('layouts.app')

@section('title', 'Enrollment Management: ' . $student->display_name)

@section('breadcrumb')
    <a href="{{ route('students.index') }}">Students</a>
    <i class="fas fa-chevron-right"></i>
    <a href="{{ route('students.show', $student) }}">{{ $student->display_name }}</a>
    <i class="fas fa-chevron-right"></i>
    <span>Manage Enrollment</span>
@endsection

@section('page-actions')
    <a href="{{ route('students.show', $student) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Profile
    </a>
@endsection

@section('content')
    <div class="container-fluid px-4">
        <!-- Current Status Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="card-title mb-0">
                    <i class="fas fa-user-graduate me-2"></i>
                    Current Enrollment Status
                </h4>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="status-item">
                            <label class="status-label">Student ID</label>
                            <div class="status-value">
                                <strong>{{ $student->student_id }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="status-item">
                            <label class="status-label">Current Status</label>
                            <div class="status-value">
                                @if($student->enrollment_status == 'active')
                                    <span class="badge bg-success px-3 py-2">
                                        <i class="fas fa-check-circle me-1"></i> Active
                                    </span>
                                @elseif($student->enrollment_status == 'inactive')
                                    <span class="badge bg-secondary px-3 py-2">Inactive</span>
                                @elseif($student->enrollment_status == 'suspended')
                                    <span class="badge bg-danger px-3 py-2">
                                        <i class="fas fa-ban me-1"></i> Suspended
                                    </span>
                                @elseif($student->enrollment_status == 'graduated')
                                    <span class="badge bg-info px-3 py-2">
                                        <i class="fas fa-user-graduate me-1"></i> Graduated
                                    </span>
                                @elseif($student->enrollment_status == 'withdrawn')
                                    <span class="badge bg-warning text-dark px-3 py-2">Withdrawn</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="status-item">
                            <label class="status-label">Academic Standing</label>
                            <div class="status-value">
                                <span class="badge bg-{{ $student->academic_standing == 'good' ? 'success' : 'warning' }} px-3 py-2">
                                    {{ ucfirst($student->academic_standing) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="status-item">
                            <label class="status-label">Last Enrollment Date</label>
                            <div class="status-value">
                                <strong>{{ $student->last_enrollment_date ? $student->last_enrollment_date->format('M d, Y') : 'N/A' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                @if($student->enrollment_status == 'inactive' && $student->leave_end_date)
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>On Leave:</strong> Expected return on {{ $student->leave_end_date->format('M d, Y') }}
                        @if($student->leave_reason)
                            <br>Reason: {{ $student->leave_reason }}
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Available Actions -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h4 class="card-title mb-0">
                    <i class="fas fa-tasks text-primary me-2"></i>
                    Available Actions
                </h4>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    @if($student->enrollment_status == 'active')
                        <!-- Leave of Absence -->
                        <div class="col-md-6 col-lg-4">
                            <div class="action-card">
                                <div class="action-icon bg-warning bg-opacity-10">
                                    <i class="fas fa-calendar-times fa-2x text-warning"></i>
                                </div>
                                <h5 class="action-title">Request Leave of Absence</h5>
                                <p class="action-description">Temporarily pause enrollment for personal or medical reasons</p>
                                <button class="btn btn-warning w-100" data-bs-toggle="modal" data-bs-target="#leaveModal">
                                    <i class="fas fa-clock me-2"></i>Request Leave
                                </button>
                            </div>
                        </div>

                        <!-- Withdrawal -->
                        <div class="col-md-6 col-lg-4">
                            <div class="action-card">
                                <div class="action-icon bg-danger bg-opacity-10">
                                    <i class="fas fa-user-times fa-2x text-danger"></i>
                                </div>
                                <h5 class="action-title">Process Withdrawal</h5>
                                <p class="action-description">Permanently withdraw from the program</p>
                                <button class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#withdrawalModal">
                                    <i class="fas fa-sign-out-alt me-2"></i>Withdraw Student
                                </button>
                            </div>
                        </div>

                        <!-- Suspension -->
                        <div class="col-md-6 col-lg-4">
                            <div class="action-card">
                                <div class="action-icon bg-dark bg-opacity-10">
                                    <i class="fas fa-ban fa-2x text-dark"></i>
                                </div>
                                <h5 class="action-title">Academic Suspension</h5>
                                <p class="action-description">Suspend for academic or disciplinary reasons</p>
                                <button class="btn btn-dark w-100" data-bs-toggle="modal" data-bs-target="#suspensionModal">
                                    <i class="fas fa-ban me-2"></i>Suspend Student
                                </button>
                            </div>
                        </div>

                        @if($student->credits_earned >= $student->credits_required && $student->cumulative_gpa >= 2.0)
                        <!-- Graduation -->
                        <div class="col-md-6 col-lg-4">
                            <div class="action-card">
                                <div class="action-icon bg-success bg-opacity-10">
                                    <i class="fas fa-graduation-cap fa-2x text-success"></i>
                                </div>
                                <h5 class="action-title">Process Graduation</h5>
                                <p class="action-description">Mark student as graduated</p>
                                <button class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#graduationModal">
                                    <i class="fas fa-user-graduate me-2"></i>Graduate Student
                                </button>
                            </div>
                        </div>
                        @endif

                    @elseif($student->enrollment_status == 'inactive')
                        <!-- Return from Leave -->
                        <div class="col-md-6 col-lg-4">
                            <div class="action-card">
                                <div class="action-icon bg-success bg-opacity-10">
                                    <i class="fas fa-undo fa-2x text-success"></i>
                                </div>
                                <h5 class="action-title">Return from Leave</h5>
                                <p class="action-description">Reactivate enrollment after leave period</p>
                                <button class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#returnModal">
                                    <i class="fas fa-play me-2"></i>Return to Active
                                </button>
                            </div>
                        </div>

                    @elseif($student->enrollment_status == 'withdrawn' || $student->enrollment_status == 'suspended')
                        <!-- Readmission -->
                        <div class="col-md-6 col-lg-4">
                            <div class="action-card">
                                <div class="action-icon bg-primary bg-opacity-10">
                                    <i class="fas fa-user-plus fa-2x text-primary"></i>
                                </div>
                                <h5 class="action-title">Process Readmission</h5>
                                <p class="action-description">Readmit student to the program</p>
                                <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#readmissionModal">
                                    <i class="fas fa-user-check me-2"></i>Readmit Student
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                @if($student->enrollment_status == 'graduated')
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Student has graduated.</strong> 
                        @if($student->degree_awarded)
                            Degree awarded: {{ $student->degree_awarded }}
                        @endif
                        @if($student->graduation_date)
                            <br>Graduation Date: {{ $student->graduation_date->format('M d, Y') }}
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Enrollment History -->
        <div class="card shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">
                    <i class="fas fa-history text-primary me-2"></i>
                    Recent Enrollment History
                </h4>
                <a href="{{ route('students.enrollment.history', $student) }}" class="btn btn-sm btn-outline-primary">
                    View Full History <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body">
                @if($enrollmentHistory->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Action</th>
                                    <th>From Status</th>
                                    <th>To Status</th>
                                    <th>Reason</th>
                                    <th>By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($enrollmentHistory->take(5) as $history)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($history->changed_at)->format('M d, Y') }}</td>
                                    <td><span class="fw-semibold">Status Change</span></td>
                                    <td>
                                        @if($history->previous_status)
                                            <span class="badge bg-secondary">{{ ucfirst($history->previous_status) }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ ucfirst($history->new_status) }}</span>
                                    </td>
                                    <td>{{ $history->reason ?? '-' }}</td>
                                    <td>{{ $history->changed_by_name ?? 'System' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center py-3">No enrollment history available.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Modals -->
    
    <!-- Leave of Absence Modal -->
    <div class="modal fade" id="leaveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">
                        <i class="fas fa-calendar-times me-2"></i>Request Leave of Absence
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('students.enrollment.leave', $student) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Reason for Leave <span class="text-danger">*</span></label>
                            <textarea name="reason" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                    <input type="date" name="start_date" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Expected Return Date <span class="text-danger">*</span></label>
                                    <input type="date" name="end_date" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Additional Notes</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Withdrawal Modal -->
    <div class="modal fade" id="withdrawalModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-user-times me-2"></i>Process Withdrawal
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('students.enrollment.withdraw', $student) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            This action cannot be easily reversed. Student will need to apply for readmission.
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason for Withdrawal <span class="text-danger">*</span></label>
                            <textarea name="reason" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Effective Date</label>
                            <input type="date" name="effective_date" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Additional Notes</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Process Withdrawal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Return from Leave Modal -->
    <div class="modal fade" id="returnModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-undo me-2"></i>Return from Leave
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('students.enrollment.return', $student) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3" 
                                      placeholder="Any comments about the return..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Confirm Return</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Readmission Modal -->
    <div class="modal fade" id="readmissionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-user-plus me-2"></i>Process Readmission
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('students.enrollment.readmit', $student) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Readmission Notes</label>
                            <textarea name="notes" class="form-control" rows="3" 
                                      placeholder="Conditions, requirements, or comments about readmission..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Process Readmission</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Graduation Modal -->
    <div class="modal fade" id="graduationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-graduation-cap me-2"></i>Process Graduation
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('students.enrollment.graduate', $student) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Degree Awarded <span class="text-danger">*</span></label>
                            <input type="text" name="degree_awarded" class="form-control" required 
                                   placeholder="e.g., Bachelor of Science in Computer Science">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Graduation Date</label>
                            <input type="date" name="graduation_date" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Process Graduation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Suspension Modal -->
    <div class="modal fade" id="suspensionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-ban me-2"></i>Academic Suspension
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('students.enrollment.suspend', $student) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            This is a serious academic action. Ensure proper documentation.
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason for Suspension <span class="text-danger">*</span></label>
                            <textarea name="reason" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Effective Date</label>
                            <input type="date" name="effective_date" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-dark">Suspend Student</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Custom Styles -->
    <style>
        .status-item {
            text-align: center;
        }

        .status-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #6c757d;
            display: block;
            margin-bottom: 0.5rem;
        }

        .status-value {
            font-size: 1.1rem;
        }

        .action-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 1.5rem;
            height: 100%;
            transition: box-shadow 0.2s, transform 0.2s;
            display: flex;
            flex-direction: column;
        }

        .action-card:hover {
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .action-icon {
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            margin: 0 auto 1rem auto;
        }

        .action-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
            text-align: center;
        }

        .action-description {
            color: #6c757d;
            font-size: 0.9rem;
            text-align: center;
            flex-grow: 1;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .status-item {
                margin-bottom: 1rem;
            }
        }
    </style>
@endsection