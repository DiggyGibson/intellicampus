<?php
// ============================================
// 2. ADVISOR/APPROVER DASHBOARD VIEW
// ============================================

// resources/views/advisor/override-dashboard.blade.php
?>
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2>Override Request Management</h2>
    
    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-warning">{{ $stats['pending'] ?? 0 }}</h3>
                    <p class="mb-0">Pending Requests</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-success">{{ $stats['approved_this_week'] ?? 0 }}</h3>
                    <p class="mb-0">Approved This Week</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-danger">{{ $stats['denied_this_week'] ?? 0 }}</h3>
                    <p class="mb-0">Denied This Week</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-info">{{ $stats['average_response_time'] ?? 'N/A' }}</h3>
                    <p class="mb-0">Avg Response Time</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ request()->url() }}" class="form-inline">
                <div class="form-group mr-3">
                    <label class="mr-2">Type:</label>
                    <select name="type" class="form-control" onchange="this.form.submit()">
                        <option value="">All Types</option>
                        <option value="credit_overload" {{ request('type') == 'credit_overload' ? 'selected' : '' }}>Credit Overload</option>
                        <option value="prerequisite" {{ request('type') == 'prerequisite' ? 'selected' : '' }}>Prerequisite Waiver</option>
                        <option value="capacity" {{ request('type') == 'capacity' ? 'selected' : '' }}>Capacity Override</option>
                    </select>
                </div>
                <div class="form-group mr-3">
                    <label class="mr-2">Status:</label>
                    <select name="status" class="form-control" onchange="this.form.submit()">
                        <option value="pending" {{ request('status', 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="denied" {{ request('status') == 'denied' ? 'selected' : '' }}>Denied</option>
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary mr-2">Filter</button>
                <a href="{{ request()->url() }}" class="btn btn-secondary">Reset</a>
            </form>
        </div>
    </div>

    {{-- Requests Table --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h5 class="mb-0">Override Requests</h5>
            @if($requests->where('status', 'pending')->count() > 0)
            <button class="btn btn-sm btn-success" onclick="showBulkApprove()">
                Bulk Process Selected
            </button>
            @endif
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="selectAll">
                            </th>
                            <th>Priority</th>
                            <th>Request Date</th>
                            <th>Student</th>
                            <th>Type</th>
                            <th>Details</th>
                            <th>GPA</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $request)
                        <tr class="{{ $request->priority_level >= 8 ? 'table-warning' : '' }}">
                            <td>
                                @if($request->status == 'pending')
                                <input type="checkbox" class="request-checkbox" value="{{ $request->id }}">
                                @endif
                            </td>
                            <td>
                                @if($request->priority_level >= 8)
                                    <span class="badge badge-danger">High</span>
                                @elseif($request->priority_level >= 5)
                                    <span class="badge badge-warning">Medium</span>
                                @else
                                    <span class="badge badge-info">Normal</span>
                                @endif
                            </td>
                            <td>{{ $request->created_at->format('M d, Y') }}</td>
                            <td>
                                <strong>{{ $request->student->user->name ?? 'Unknown' }}</strong><br>
                                <small>ID: {{ $request->student->student_id }}</small>
                                @if($request->is_graduating_senior)
                                    <br><span class="badge badge-purple">Graduating</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $request->type_badge_class }}">
                                    {{ $request->type_label }}
                                </span>
                            </td>
                            <td>
                                @if($request->request_type == 'credit_overload')
                                    Current: {{ $request->current_credits ?? 0 }}<br>
                                    Requesting: {{ $request->requested_credits }}
                                @elseif($request->course)
                                    {{ $request->course->code }}<br>
                                    <small>{{ $request->course->title }}</small>
                                @elseif($request->section)
                                    {{ $request->section->course->code }} - Sec {{ $request->section->section_number }}
                                @endif
                            </td>
                            <td>{{ number_format($request->student->cumulative_gpa ?? 0, 2) }}</td>
                            <td>
                                @if($request->status == 'pending')
                                    <span class="badge badge-warning">Pending</span>
                                @elseif($request->status == 'approved')
                                    <span class="badge badge-success">Approved</span>
                                @else
                                    <span class="badge badge-danger">Denied</span>
                                @endif
                            </td>
                            <td>
                                @if($request->status == 'pending')
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-primary" onclick="reviewRequest({{ $request->id }})">
                                            Review
                                        </button>
                                        <button class="btn btn-sm btn-success" onclick="quickApprove({{ $request->id }})">
                                            Quick Approve
                                        </button>
                                    </div>
                                @else
                                    <button class="btn btn-sm btn-secondary" onclick="viewRequest({{ $request->id }})">
                                        View
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">
                                No override requests found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                
                {{ $requests->links() }}
            </div>
        </div>
    </div>
</div>

{{-- Review Modal --}}
<div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Review Override Request</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="reviewModalBody">
                <!-- Content loaded dynamically -->
            </div>
        </div>
    </div>
</div>

{{-- Bulk Process Modal --}}
<div class="modal fade" id="bulkModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Process Requests</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="bulkProcessForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Selected Requests: <span id="selectedCount">0</span></label>
                    </div>
                    <div class="form-group">
                        <label>Action</label>
                        <select class="form-control" name="action" required>
                            <option value="">Select action...</option>
                            <option value="approve">Approve All</option>
                            <option value="deny">Deny All</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea class="form-control" name="notes" rows="3" 
                                  placeholder="Add notes for all selected requests"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Process</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Select all checkboxes
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.request-checkbox');
    checkboxes.forEach(checkbox => checkbox.checked = this.checked);
});

function reviewRequest(requestId) {
    fetch(`/api/override-management/${requestId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const request = data.request;
                const context = data.context;
                
                let html = `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-info text-white">Student Information</div>
                                <div class="card-body">
                                    <p><strong>Name:</strong> ${request.student.user.name}</p>
                                    <p><strong>Student ID:</strong> ${request.student.student_id}</p>
                                    <p><strong>GPA:</strong> ${context.student_gpa}</p>
                                    <p><strong>Academic Standing:</strong> ${context.student_academic_standing}</p>
                                    <p><strong>Credits Completed:</strong> ${context.student_credits_completed}</p>
                                    <p><strong>Previous Overrides:</strong> ${context.previous_overrides}</p>
                                    ${context.is_graduating_senior ? '<p class="text-danger"><strong>⚠ Graduating Senior</strong></p>' : ''}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-primary text-white">Request Details</div>
                                <div class="card-body">
                                    <p><strong>Type:</strong> ${request.type_label}</p>
                                    <p><strong>Submitted:</strong> ${new Date(request.created_at).toLocaleDateString()}</p>
                                    <p><strong>Priority:</strong> ${request.priority_level}/10</p>
                                    ${getRequestSpecificDetails(request)}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mt-3">
                        <div class="card-header">Student's Justification</div>
                        <div class="card-body">
                            <p>${request.student_justification}</p>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <form id="approvalForm" onsubmit="processRequest(event, ${request.id})">
                        <div class="form-group">
                            <label>Decision <span class="text-danger">*</span></label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="decision" 
                                           value="approved" id="approveRadio" required>
                                    <label class="form-check-label text-success" for="approveRadio">
                                        <i class="fas fa-check"></i> Approve
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="decision" 
                                           value="denied" id="denyRadio" required>
                                    <label class="form-check-label text-danger" for="denyRadio">
                                        <i class="fas fa-times"></i> Deny
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Notes/Conditions</label>
                            <textarea class="form-control" name="notes" rows="3" 
                                      placeholder="Add any notes or conditions for this decision..."></textarea>
                        </div>
                        
                        <div class="form-group" id="conditionsGroup" style="display:none;">
                            <label>Standard Conditions (Optional)</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="conditions[]" 
                                       value="maintain_gpa" id="cond1">
                                <label class="form-check-label" for="cond1">
                                    Must maintain current GPA (${context.student_gpa})
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="conditions[]" 
                                       value="one_time_only" id="cond2">
                                <label class="form-check-label" for="cond2">
                                    One-time exception only
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="conditions[]" 
                                       value="advisor_monitoring" id="cond3">
                                <label class="form-check-label" for="cond3">
                                    Requires regular advisor check-ins
                                </label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Submit Decision</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </form>
                `;
                
                document.getElementById('reviewModalBody').innerHTML = html;
                $('#reviewModal').modal('show');
                
                // Show conditions when approve is selected
                document.querySelector('input[name="decision"]').addEventListener('change', function(e) {
                    document.getElementById('conditionsGroup').style.display = 
                        e.target.value === 'approved' ? 'block' : 'none';
                });
            }
        });
}

function getRequestSpecificDetails(request) {
    switch(request.request_type) {
        case 'credit_overload':
            return `
                <p><strong>Current Credits:</strong> ${request.current_credits || 0}</p>
                <p><strong>Requested Credits:</strong> ${request.requested_credits}</p>
            `;
        case 'prerequisite':
            return `
                <p><strong>Course:</strong> ${request.course ? request.course.code + ' - ' + request.course.title : 'N/A'}</p>
            `;
        case 'capacity':
            return `
                <p><strong>Section:</strong> ${request.section ? request.section.course.code + ' Sec ' + request.section.section_number : 'N/A'}</p>
            `;
        default:
            return '';
    }
}

function processRequest(event, requestId) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const decision = formData.get('decision');
    const url = decision === 'approved' 
        ? `/api/override-management/${requestId}/approve`
        : `/api/override-management/${requestId}/deny`;
    
    const conditions = [];
    formData.getAll('conditions[]').forEach(c => conditions.push(c));
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            notes: formData.get('notes'),
            conditions: conditions
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $('#reviewModal').modal('hide');
            alert('Request processed successfully');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    });
}

function quickApprove(requestId) {
    if (confirm('Are you sure you want to quick approve this request?')) {
        fetch(`/api/override-management/${requestId}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                notes: 'Quick approved'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Request approved successfully');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}

function showBulkApprove() {
    const selected = document.querySelectorAll('.request-checkbox:checked');
    document.getElementById('selectedCount').textContent = selected.length;
    
    if (selected.length === 0) {
        alert('Please select at least one request');
        return;
    }
    
    $('#bulkModal').modal('show');
}

document.getElementById('bulkProcessForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const selected = Array.from(document.querySelectorAll('.request-checkbox:checked')).map(cb => cb.value);
    const formData = new FormData(this);
    
    fetch('/api/override-management/bulk-process', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            request_ids: selected,
            action: formData.get('action'),
            notes: formData.get('notes')
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $('#bulkModal').modal('hide');
            alert(`${data.processed} requests processed successfully`);
            location.reload();
        } else {
            alert('Error processing requests');
        }
    });
});
</script>
@endsection