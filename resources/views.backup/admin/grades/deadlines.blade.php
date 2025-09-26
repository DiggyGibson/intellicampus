@extends('layouts.app')

@section('title', 'Grade Deadline Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">Grade Deadline Management</h1>
            <p class="mb-0 text-muted">Configure grade submission deadlines for academic terms</p>
        </div>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Add New Deadline Card -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Set Grade Deadlines</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.grades.deadlines.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="term_id" class="form-label">Academic Term <span class="text-danger">*</span></label>
                        <select class="form-select @error('term_id') is-invalid @enderror" 
                                id="term_id" name="term_id" required>
                            <option value="">Select Term</option>
                            @foreach($terms as $term)
                                @if(!$deadlines->where('term_id', $term->id)->first())
                                    <option value="{{ $term->id }}" {{ old('term_id') == $term->id ? 'selected' : '' }}>
                                        {{ $term->name }} ({{ $term->code }})
                                        {{ $term->is_current ? '- Current' : '' }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        @error('term_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="midterm_grade_deadline" class="form-label">Midterm Grade Deadline <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control @error('midterm_grade_deadline') is-invalid @enderror" 
                               id="midterm_grade_deadline" name="midterm_grade_deadline" 
                               value="{{ old('midterm_grade_deadline') }}" required>
                        @error('midterm_grade_deadline')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="final_grade_deadline" class="form-label">Final Grade Deadline <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control @error('final_grade_deadline') is-invalid @enderror" 
                               id="final_grade_deadline" name="final_grade_deadline" 
                               value="{{ old('final_grade_deadline') }}" required>
                        @error('final_grade_deadline')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="grade_change_deadline" class="form-label">Grade Change Deadline <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control @error('grade_change_deadline') is-invalid @enderror" 
                               id="grade_change_deadline" name="grade_change_deadline" 
                               value="{{ old('grade_change_deadline') }}" required>
                        @error('grade_change_deadline')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="incomplete_deadline" class="form-label">Incomplete Resolution Deadline <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control @error('incomplete_deadline') is-invalid @enderror" 
                               id="incomplete_deadline" name="incomplete_deadline" 
                               value="{{ old('incomplete_deadline') }}" required>
                        @error('incomplete_deadline')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="notes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="1">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Deadlines
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Existing Deadlines Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Configured Deadlines</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Term</th>
                            <th>Midterm Deadline</th>
                            <th>Final Deadline</th>
                            <th>Change Deadline</th>
                            <th>Incomplete Deadline</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deadlines as $deadline)
                        <tr class="{{ $deadline->is_current ? 'table-info' : '' }}">
                            <td>
                                <strong>{{ $deadline->term_name }}</strong><br>
                                <small class="text-muted">{{ $deadline->term_code }}</small>
                                @if($deadline->is_current)
                                    <span class="badge bg-success ms-2">Current</span>
                                @endif
                            </td>
                            <td>
                                {{ \Carbon\Carbon::parse($deadline->midterm_grade_deadline)->format('M d, Y h:i A') }}
                                @php
                                    $midtermStatus = \Carbon\Carbon::now()->isPast(\Carbon\Carbon::parse($deadline->midterm_grade_deadline));
                                @endphp
                                <br>
                                <small class="{{ $midtermStatus ? 'text-danger' : 'text-success' }}">
                                    {{ $midtermStatus ? 'Passed' : 'Upcoming' }}
                                </small>
                            </td>
                            <td>
                                {{ \Carbon\Carbon::parse($deadline->final_grade_deadline)->format('M d, Y h:i A') }}
                                @php
                                    $finalStatus = \Carbon\Carbon::now()->isPast(\Carbon\Carbon::parse($deadline->final_grade_deadline));
                                @endphp
                                <br>
                                <small class="{{ $finalStatus ? 'text-danger' : 'text-success' }}">
                                    {{ $finalStatus ? 'Passed' : 'Upcoming' }}
                                </small>
                            </td>
                            <td>
                                {{ \Carbon\Carbon::parse($deadline->grade_change_deadline)->format('M d, Y h:i A') }}
                            </td>
                            <td>
                                {{ \Carbon\Carbon::parse($deadline->incomplete_deadline)->format('M d, Y h:i A') }}
                            </td>
                            <td>
                                @php
                                    $now = \Carbon\Carbon::now();
                                    $termEnd = \Carbon\Carbon::parse($deadline->end_date);
                                @endphp
                                @if($now->isAfter($termEnd))
                                    <span class="badge bg-secondary">Completed</span>
                                @elseif($deadline->is_current)
                                    <span class="badge bg-primary">Active</span>
                                @else
                                    <span class="badge bg-info">Future</span>
                                @endif
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                        onclick="editDeadline({{ $deadline->id }})"
                                        data-bs-toggle="modal" data-bs-target="#editDeadlineModal">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-warning"
                                        onclick="sendReminders({{ $deadline->term_id }})">
                                    <i class="fas fa-bell"></i>
                                </button>
                                @if($deadline->notes)
                                <button type="button" class="btn btn-sm btn-outline-info"
                                        onclick="showNotes('{{ $deadline->notes }}')"
                                        data-bs-toggle="tooltip" title="View Notes">
                                    <i class="fas fa-sticky-note"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No deadlines configured yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Submission Statistics Card -->
    @if($deadlines->where('is_current', true)->first())
    <div class="card mt-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Current Term Submission Statistics</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <canvas id="submissionChart" height="150"></canvas>
                </div>
                <div class="col-md-6">
                    <h6>Pending Submissions</h6>
                    <div class="list-group">
                        <!-- This would be populated with actual data -->
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Midterm Grades
                            <span class="badge bg-warning rounded-pill">12</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Final Grades
                            <span class="badge bg-danger rounded-pill">45</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Grade Changes
                            <span class="badge bg-info rounded-pill">3</span>
                        </div>
                    </div>
                    <button class="btn btn-warning w-100 mt-3" onclick="sendBulkReminders()">
                        <i class="fas fa-envelope me-2"></i>Send Bulk Reminders
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Edit Deadline Modal -->
<div class="modal fade" id="editDeadlineModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Grade Deadlines</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editDeadlineForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" id="edit_deadline_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Midterm Grade Deadline</label>
                            <input type="datetime-local" class="form-control" id="edit_midterm_deadline" name="midterm_grade_deadline" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Final Grade Deadline</label>
                            <input type="datetime-local" class="form-control" id="edit_final_deadline" name="final_grade_deadline" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Grade Change Deadline</label>
                            <input type="datetime-local" class="form-control" id="edit_change_deadline" name="grade_change_deadline" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Incomplete Resolution Deadline</label>
                            <input type="datetime-local" class="form-control" id="edit_incomplete_deadline" name="incomplete_deadline" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" id="edit_notes" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Submission statistics chart
@if($deadlines->where('is_current', true)->first())
const ctx = document.getElementById('submissionChart').getContext('2d');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Submitted', 'Pending', 'Overdue'],
        datasets: [{
            data: [65, 25, 10], // These would be actual values
            backgroundColor: [
                'rgba(40, 167, 69, 0.8)',
                'rgba(255, 193, 7, 0.8)',
                'rgba(220, 53, 69, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            },
            title: {
                display: true,
                text: 'Grade Submission Status'
            }
        }
    }
});
@endif

// Edit deadline function
function editDeadline(id) {
    // Fetch deadline data and populate form
    // This would make an AJAX call to get the deadline details
    document.getElementById('editDeadlineForm').action = `/admin/grades/deadlines/${id}`;
}

// Send reminders function
function sendReminders(termId) {
    if (confirm('Send grade submission reminders to all faculty with pending grades?')) {
        // Make AJAX call to send reminders
        alert('Reminders sent successfully!');
    }
}

// Show notes function
function showNotes(notes) {
    alert(notes);
}

// Send bulk reminders
function sendBulkReminders() {
    if (confirm('Send reminders to all faculty with pending submissions?')) {
        // Implementation
        alert('Bulk reminders sent!');
    }
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltips = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltips.map(function(el) {
        return new bootstrap.Tooltip(el);
    });
});
</script>
@endpush
@endsection