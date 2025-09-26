@extends('layouts.app')

@section('title', 'Grade History')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('grades.index') }}">Grade Management</a></li>
                    <li class="breadcrumb-item active">Grade History</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0">Grade Change History</h1>
            <p class="text-muted">Audit trail of all grade modifications</p>
        </div>
    </div>

    <!-- Student & Course Info -->
    <div class="card mb-4 border-left-info">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <small class="text-muted">Student</small>
                    <p class="mb-0"><strong>{{ $enrollment->student->first_name }} {{ $enrollment->student->last_name }}</strong></p>
                    <small>ID: {{ $enrollment->student->student_id }}</small>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Course</small>
                    <p class="mb-0"><strong>{{ $enrollment->section->course->code }}</strong></p>
                    <small>{{ $enrollment->section->course->title }}</small>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Section</small>
                    <p class="mb-0"><strong>{{ $enrollment->section->section_code }}</strong></p>
                    <small>{{ $enrollment->section->term->name }}</small>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Current Grade</small>
                    <p class="mb-0">
                        <span class="badge bg-primary fs-5">
                            {{ $enrollment->grade ?? 'Not Graded' }}
                        </span>
                    </p>
                    <small>GPA Points: {{ $enrollment->grade_points ?? 'N/A' }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- History Timeline -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Grade Change Timeline</h5>
        </div>
        <div class="card-body">
            @forelse($gradeHistory as $history)
            <div class="timeline-item mb-4 pb-4 border-bottom">
                <div class="row">
                    <div class="col-md-2">
                        <small class="text-muted">{{ \Carbon\Carbon::parse($history->changed_at)->format('M d, Y') }}</small><br>
                        <strong>{{ \Carbon\Carbon::parse($history->changed_at)->format('h:i A') }}</strong>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <span class="badge bg-secondary me-2">{{ $history->old_grade ?? 'None' }}</span>
                            <i class="fas fa-arrow-right text-muted mx-2"></i>
                            <span class="badge bg-{{ $history->new_grade == 'F' ? 'danger' : 'success' }}">{{ $history->new_grade }}</span>
                        </div>
                        <small class="text-muted d-block mt-1">
                            @if($history->old_percentage && $history->new_percentage)
                                ({{ number_format($history->old_percentage, 1) }}% → {{ number_format($history->new_percentage, 1) }}%)
                            @endif
                        </small>
                    </div>
                    <div class="col-md-2">
                        <small class="text-muted">Changed By</small><br>
                        <strong>{{ $history->changed_by_name }}</strong>
                        <span class="badge bg-info ms-1">{{ ucfirst($history->changed_by_role) }}</span>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Reason</small><br>
                        {{ $history->reason ?? 'Grade entry/update' }}
                    </div>
                    <div class="col-md-2">
                        <small class="text-muted">Type</small><br>
                        @if($history->change_type == 'initial')
                            <span class="badge bg-primary">Initial Entry</span>
                        @elseif($history->change_type == 'correction')
                            <span class="badge bg-warning">Correction</span>
                        @elseif($history->change_type == 'appeal')
                            <span class="badge bg-info">Appeal</span>
                        @elseif($history->change_type == 'final')
                            <span class="badge bg-success">Final Submission</span>
                        @else
                            <span class="badge bg-secondary">Update</span>
                        @endif
                    </div>
                </div>
                @if($history->comments)
                <div class="row mt-2">
                    <div class="col-12">
                        <div class="alert alert-light mb-0">
                            <small><strong>Comments:</strong> {{ $history->comments }}</small>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @empty
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                No grade changes recorded for this enrollment.
            </div>
            @endforelse
        </div>
    </div>

    <!-- Component Grades History -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i>Component Grade History</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Component</th>
                            <th>Type</th>
                            <th>Points</th>
                            <th>Percentage</th>
                            <th>Grade</th>
                            <th>Submitted</th>
                            <th>By</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($enrollment->grades as $grade)
                        <tr>
                            <td><strong>{{ $grade->component->name }}</strong></td>
                            <td>
                                <span class="badge bg-secondary">{{ ucfirst($grade->component->type) }}</span>
                            </td>
                            <td>{{ $grade->points_earned ?? '-' }} / {{ $grade->max_points }}</td>
                            <td>{{ number_format($grade->percentage ?? 0, 1) }}%</td>
                            <td>
                                <span class="badge bg-{{ $grade->letter_grade == 'F' ? 'danger' : 'primary' }}">
                                    {{ $grade->letter_grade ?? '-' }}
                                </span>
                            </td>
                            <td>{{ $grade->submitted_at ? $grade->submitted_at->format('M d, Y') : '-' }}</td>
                            <td>{{ $grade->grader->name ?? '-' }}</td>
                            <td>
                                @if($grade->is_final)
                                    <span class="badge bg-success">Final</span>
                                @else
                                    <span class="badge bg-warning">Draft</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="row mt-4">
        <div class="col-md-4">
            <a href="{{ route('grades.entry', $enrollment->section_id) }}" class="btn btn-primary w-100">
                <i class="fas fa-arrow-left me-2"></i>Back to Grade Entry
            </a>
        </div>
        <div class="col-md-4">
            <button class="btn btn-info w-100" onclick="printHistory()">
                <i class="fas fa-print me-2"></i>Print History
            </button>
        </div>
        <div class="col-md-4">
            <button class="btn btn-success w-100" onclick="exportHistory()">
                <i class="fas fa-download me-2"></i>Export as PDF
            </button>
        </div>
    </div>
</div>

@push('styles')
<style>
    .timeline-item:last-child {
        border-bottom: none !important;
    }
    .border-left-info {
        border-left: 4px solid #36b9cc !important;
    }
</style>
@endpush

@push('scripts')
<script>
function printHistory() {
    window.print();
}

function exportHistory() {
    alert('Exporting grade history as PDF...');
    // Implementation would generate PDF
}
</script>
@endpush
@endsection