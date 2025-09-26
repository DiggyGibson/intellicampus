{{-- File: resources/views/admin/system-config/academic.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-1">Academic Configuration</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.system-config.index') }}">System Configuration</a></li>
                    <li class="breadcrumb-item active">Academic Configuration</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        {{-- Credit System Configuration --}}
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-graduation-cap"></i> Credit System</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.system-config.academic.update', 'credit') }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label class="form-label">Credit System Type</label>
                            <select name="credit_system" class="form-select" required>
                                <option value="semester" {{ ($creditConfig->credit_system ?? '') == 'semester' ? 'selected' : '' }}>Semester</option>
                                <option value="quarter" {{ ($creditConfig->credit_system ?? '') == 'quarter' ? 'selected' : '' }}>Quarter</option>
                                <option value="annual" {{ ($creditConfig->credit_system ?? '') == 'annual' ? 'selected' : '' }}>Annual</option>
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Min Credits (Full-time)</label>
                                <input type="number" name="min_credits_full_time" class="form-control" 
                                       value="{{ $creditConfig->min_credits_full_time ?? 12 }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Max Credits (Regular)</label>
                                <input type="number" name="max_credits_regular" class="form-control" 
                                       value="{{ $creditConfig->max_credits_regular ?? 18 }}" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Max Credits (Overload)</label>
                                <input type="number" name="max_credits_overload" class="form-control" 
                                       value="{{ $creditConfig->max_credits_overload ?? 21 }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Min Credits (Graduation)</label>
                                <input type="number" name="min_credits_graduation" class="form-control" 
                                       value="{{ $creditConfig->min_credits_graduation ?? 120 }}" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Hours per Credit</label>
                            <input type="number" name="hours_per_credit" class="form-control" step="0.5"
                                   value="{{ $creditConfig->hours_per_credit ?? 1 }}" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Save Credit Configuration</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Grading Configuration --}}
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-line"></i> Grading System</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.system-config.academic.update', 'grading') }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label class="form-label">Grading System</label>
                            <select name="grading_system" class="form-select" required>
                                <option value="letter" {{ ($gradingConfig->grading_system ?? '') == 'letter' ? 'selected' : '' }}>Letter Grade (A-F)</option>
                                <option value="percentage" {{ ($gradingConfig->grading_system ?? '') == 'percentage' ? 'selected' : '' }}>Percentage</option>
                                <option value="points" {{ ($gradingConfig->grading_system ?? '') == 'points' ? 'selected' : '' }}>Points</option>
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Max GPA</label>
                                <input type="number" name="max_gpa" class="form-control" step="0.1"
                                       value="{{ $gradingConfig->max_gpa ?? 4.0 }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Passing GPA</label>
                                <input type="number" name="passing_gpa" class="form-control" step="0.1"
                                       value="{{ $gradingConfig->passing_gpa ?? 2.0 }}" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Probation GPA</label>
                                <input type="number" name="probation_gpa" class="form-control" step="0.1"
                                       value="{{ $gradingConfig->probation_gpa ?? 2.0 }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Honors GPA</label>
                                <input type="number" name="honors_gpa" class="form-control" step="0.1"
                                       value="{{ $gradingConfig->honors_gpa ?? 3.5 }}" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">High Honors GPA</label>
                            <input type="number" name="high_honors_gpa" class="form-control" step="0.1"
                                   value="{{ $gradingConfig->high_honors_gpa ?? 3.8 }}" required>
                        </div>
                        
                        <div class="form-check mb-2">
                            <input type="checkbox" name="use_plus_minus" class="form-check-input" value="1"
                                   {{ ($gradingConfig->use_plus_minus ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label">Use Plus/Minus Grades</label>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input type="checkbox" name="include_failed_in_gpa" class="form-check-input" value="1"
                                   {{ ($gradingConfig->include_failed_in_gpa ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label">Include Failed Courses in GPA</label>
                        </div>
                        
                        <button type="submit" class="btn btn-success">Save Grading Configuration</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Attendance Configuration --}}
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-calendar-check"></i> Attendance System</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.system-config.academic.update', 'attendance') }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-check mb-3">
                            <input type="checkbox" name="track_attendance" class="form-check-input" value="1"
                                   {{ ($attendanceConfig->track_attendance ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label">Track Attendance</label>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Max Absences Allowed</label>
                            <input type="number" name="max_absences_allowed" class="form-control"
                                   value="{{ $attendanceConfig->max_absences_allowed ?? 3 }}">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Attendance Weight in Grade (%)</label>
                            <input type="number" name="attendance_weight_in_grade" class="form-control" min="0" max="100"
                                   value="{{ $attendanceConfig->attendance_weight_in_grade ?? 10 }}" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Calculation Method</label>
                            <select name="attendance_calculation_method" class="form-select" required>
                                <option value="percentage" {{ ($attendanceConfig->attendance_calculation_method ?? '') == 'percentage' ? 'selected' : '' }}>Percentage</option>
                                <option value="points" {{ ($attendanceConfig->attendance_calculation_method ?? '') == 'points' ? 'selected' : '' }}>Points</option>
                            </select>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input type="checkbox" name="notify_on_absence" class="form-check-input" value="1"
                                   {{ ($attendanceConfig->notify_on_absence ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label">Notify on Absence</label>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Notification Threshold</label>
                            <input type="number" name="absence_notification_threshold" class="form-control" min="1"
                                   value="{{ $attendanceConfig->absence_notification_threshold ?? 2 }}" required>
                        </div>
                        
                        <button type="submit" class="btn btn-info">Save Attendance Configuration</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Registration Configuration --}}
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="fas fa-clipboard-list"></i> Registration System</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.system-config.academic.update', 'registration') }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-check mb-3">
                            <input type="checkbox" name="allow_online_registration" class="form-check-input" value="1"
                                   {{ ($registrationConfig->allow_online_registration ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label">Allow Online Registration</label>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Registration Priority Days</label>
                            <input type="number" name="registration_priority_days" class="form-control" min="0"
                                   value="{{ $registrationConfig->registration_priority_days ?? 7 }}" required>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input type="checkbox" name="enforce_prerequisites" class="form-check-input" value="1"
                                   {{ ($registrationConfig->enforce_prerequisites ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label">Enforce Prerequisites</label>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input type="checkbox" name="allow_time_conflicts" class="form-check-input" value="1"
                                   {{ ($registrationConfig->allow_time_conflicts ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label">Allow Time Conflicts</label>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input type="checkbox" name="allow_waitlist" class="form-check-input" value="1"
                                   {{ ($registrationConfig->allow_waitlist ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label">Allow Waitlist</label>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Max Waitlist Size</label>
                            <input type="number" name="max_waitlist_size" class="form-control" min="0"
                                   value="{{ $registrationConfig->max_waitlist_size ?? 10 }}" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Drop Deadline (weeks)</label>
                                <input type="number" name="drop_deadline_weeks" class="form-control" min="1"
                                       value="{{ $registrationConfig->drop_deadline_weeks ?? 2 }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Withdraw Deadline (weeks)</label>
                                <input type="number" name="withdraw_deadline_weeks" class="form-control" min="1"
                                       value="{{ $registrationConfig->withdraw_deadline_weeks ?? 8 }}" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Late Registration Fee</label>
                            <input type="number" name="late_registration_fee" class="form-control" min="0" step="0.01"
                                   value="{{ $registrationConfig->late_registration_fee ?? 50 }}" required>
                        </div>
                        
                        <button type="submit" class="btn btn-warning">Save Registration Configuration</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection