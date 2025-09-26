@extends('layouts.app')

@section('title', 'Enrollment History: ' . $student->display_name)

@section('breadcrumb')
    <a href="{{ route('students.index') }}">Students</a>
    <i class="fas fa-chevron-right"></i>
    <a href="{{ route('students.show', $student) }}">{{ $student->display_name }}</a>
    <i class="fas fa-chevron-right"></i>
    <span>Enrollment History</span>
@endsection

@section('page-actions')
    <div class="page-actions-group">
        <a href="{{ route('students.enrollment.manage', $student) }}" class="btn btn-primary">
            <i class="fas fa-cog"></i> Manage Enrollment
        </a>
        <a href="{{ route('students.show', $student) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Profile
        </a>
    </div>
@endsection

@section('content')
    <div class="container-fluid px-4">
        <!-- Student Info Summary -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="info-item">
                            <label class="info-label">Student ID</label>
                            <div class="info-value">
                                <i class="fas fa-id-card text-primary me-2"></i>
                                <strong>{{ $student->student_id }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-item">
                            <label class="info-label">Name</label>
                            <div class="info-value">
                                <strong>{{ $student->first_name }} {{ $student->last_name }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-item">
                            <label class="info-label">Current Status</label>
                            <div class="info-value">
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
                        <div class="info-item">
                            <label class="info-label">Admission Date</label>
                            <div class="info-value">
                                <strong>{{ $student->admission_date ? $student->admission_date->format('M d, Y') : 'N/A' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Complete History -->
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h4 class="card-title mb-0">
                    <i class="fas fa-history text-primary me-2"></i>
                    Complete Enrollment History
                </h4>
            </div>
            <div class="card-body">
                @if(isset($history) && $history->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Action</th>
                                    <th>Status Change</th>
                                    <th>Reason</th>
                                    <th>Duration</th>
                                    <th>Notes</th>
                                    <th>By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($history as $record)
                                <tr>
                                    <td>
                                        <div>
                                            <strong>{{ $record->effective_date->format('M d, Y') }}</strong>
                                            <small class="text-muted d-block">{{ $record->effective_date->format('h:i A') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-primary">
                                            {{ ucwords(str_replace('_', ' ', $record->action_type)) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($record->from_status)
                                            <span class="badge bg-secondary">{{ ucfirst($record->from_status) }}</span>
                                            <i class="fas fa-arrow-right mx-2 text-muted"></i>
                                        @endif
                                        <span class="badge bg-{{ 
                                            $record->to_status == 'active' ? 'success' : 
                                            ($record->to_status == 'suspended' ? 'danger' : 
                                            ($record->to_status == 'graduated' ? 'info' : 
                                            ($record->to_status == 'withdrawn' ? 'warning' : 'secondary'))) 
                                        }}">
                                            {{ ucfirst($record->to_status) }}
                                        </span>
                                    </td>
                                    <td>{{ $record->reason ?? '-' }}</td>
                                    <td>
                                        @if($record->end_date)
                                            <div>
                                                <small>{{ $record->effective_date->format('M d') }} - {{ $record->end_date->format('M d, Y') }}</small>
                                                <span class="badge bg-light text-dark ms-2">
                                                    {{ $record->effective_date->diffInDays($record->end_date) }} days
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($record->notes)
                                            <div class="notes-cell" title="{{ $record->notes }}">
                                                {{ Str::limit($record->notes, 50) }}
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            {{ $record->created_by }}
                                            @if($record->approved_by)
                                                <small class="text-muted d-block">
                                                    Approved: {{ $record->approved_by }}
                                                </small>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $history->links('custom.pagination') }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No enrollment history available for this student.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Statistics Summary -->
        @if(isset($history) && $history->count() > 0)
        <div class="row g-3 mt-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-primary bg-opacity-10">
                            <i class="fas fa-exchange-alt fa-2x text-primary"></i>
                        </div>
                        <div class="stat-details">
                            <h3 class="stat-value">{{ $history->total() }}</h3>
                            <p class="stat-label">Total Status Changes</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-success bg-opacity-10">
                            <i class="fas fa-calendar-times fa-2x text-success"></i>
                        </div>
                        <div class="stat-details">
                            <h3 class="stat-value text-success">
                                {{ $history->where('action_type', 'leave_request')->count() }}
                            </h3>
                            <p class="stat-label">Leaves of Absence</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-info bg-opacity-10">
                            <i class="fas fa-undo fa-2x text-info"></i>
                        </div>
                        <div class="stat-details">
                            <h3 class="stat-value text-info">
                                {{ $history->where('action_type', 'return_from_leave')->count() }}
                            </h3>
                            <p class="stat-label">Returns from Leave</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-secondary bg-opacity-10">
                            <i class="fas fa-calendar-check fa-2x text-secondary"></i>
                        </div>
                        <div class="stat-details">
                            <h5 class="stat-value" style="font-size: 1rem;">
                                {{ $history->first() ? $history->first()->created_at->format('M d, Y') : 'N/A' }}
                            </h5>
                            <p class="stat-label">Last Status Change</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Custom Styles -->
    <style>
        .page-actions-group {
            display: flex;
            gap: 0.5rem;
        }

        .info-item {
            text-align: center;
        }

        .info-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #6c757d;
            display: block;
            margin-bottom: 0.25rem;
        }

        .info-value {
            font-size: 1rem;
        }

        .notes-cell {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            cursor: help;
        }

        .stat-card {
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .stat-card-body {
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
        }

        .stat-details {
            flex: 1;
        }

        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
            line-height: 1;
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.875rem;
            margin: 0.25rem 0 0 0;
        }

        @media (max-width: 768px) {
            .info-item {
                margin-bottom: 1rem;
            }
        }
    </style>
@endsection