
// ============================================
// 1. STUDENT VIEWS
// ============================================

// resources/views/student/override-requests.blade.php

@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>My Override Requests</h2>
        <a href="{{ route('student.override.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Override Request
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    {{-- Active Override Codes --}}
    @if($activeOverrides ?? false)
    <div class="card mb-4 border-success">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Active Override Codes</h5>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($activeOverrides as $override)
                <div class="col-md-6 mb-3">
                    <div class="alert alert-success">
                        <h6>{{ $override->type_label }}</h6>
                        <p class="mb-1">Code: <strong><code>{{ $override->override_code }}</code></strong></p>
                        <small>Expires: {{ $override->override_expires_at->format('M d, Y h:i A') }}</small>
                        @if($override->request_type == 'credit_overload')
                            <br><small>Approved for: {{ $override->requested_credits }} credits</small>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Request History --}}
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Request History</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Request Date</th>
                            <th>Type</th>
                            <th>Details</th>
                            <th>Status</th>
                            <th>Decision Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests ?? [] as $request)
                        <tr>
                            <td>{{ $request->created_at->format('M d, Y') }}</td>
                            <td>
                                <span class="badge badge-{{ $request->type_badge_class }}">
                                    {{ $request->type_label }}
                                </span>
                            </td>
                            <td>
                                @if($request->request_type == 'credit_overload')
                                    Requested: {{ $request->requested_credits }} credits
                                @elseif($request->course)
                                    Course: {{ $request->course->code }} - {{ $request->course->title }}
                                @elseif($request->section)
                                    {{ $request->section->course->code }} Section {{ $request->section->section_number }}
                                @endif
                            </td>
                            <td>
                                @if($request->status == 'pending')
                                    <span class="badge badge-warning">Pending</span>
                                @elseif($request->status == 'approved')
                                    <span class="badge badge-success">Approved</span>
                                    @if($request->override_code && !$request->override_used)
                                        <br><small>Code: <code>{{ $request->override_code }}</code></small>
                                    @endif
                                @elseif($request->status == 'denied')
                                    <span class="badge badge-danger">Denied</span>
                                @endif
                            </td>
                            <td>
                                {{ $request->approval_date ? $request->approval_date->format('M d, Y') : '-' }}
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="viewDetails({{ $request->id }})">
                                    View Details
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                No override requests found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                
                {{ $requests->links() ?? '' }}
            </div>
        </div>
    </div>
</div>

{{-- Details Modal --}}
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Override Request Details</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalContent">
                <!-- Content loaded via JavaScript -->
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function viewDetails(requestId) {
    fetch(`/api/registration/override-requests/${requestId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const request = data.request;
                let html = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Request Information</h6>
                            <p><strong>Type:</strong> ${request.type_label}</p>
                            <p><strong>Submitted:</strong> ${new Date(request.created_at).toLocaleDateString()}</p>
                            <p><strong>Status:</strong> <span class="badge badge-${getStatusBadge(request.status)}">${request.status}</span></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Decision Details</h6>
                            <p><strong>Decided By:</strong> ${request.approver ? request.approver.name : 'Pending'}</p>
                            <p><strong>Decision Date:</strong> ${request.approval_date ? new Date(request.approval_date).toLocaleDateString() : 'N/A'}</p>
                        </div>
                    </div>
                    <hr>
                    <div>
                        <h6>Your Justification</h6>
                        <p class="bg-light p-3">${request.student_justification}</p>
                    </div>
                `;
                
                if (request.approver_notes) {
                    html += `
                        <div class="mt-3">
                            <h6>Decision Notes</h6>
                            <p class="bg-light p-3">${request.approver_notes}</p>
                        </div>
                    `;
                }
                
                if (request.override_code && !request.override_used) {
                    html += `
                        <div class="alert alert-success mt-3">
                            <h6>Override Code</h6>
                            <p>Use this code during registration: <strong><code>${request.override_code}</code></strong></p>
                            <small>Expires: ${new Date(request.override_expires_at).toLocaleDateString()}</small>
                        </div>
                    `;
                }
                
                document.getElementById('modalContent').innerHTML = html;
                $('#detailsModal').modal('show');
            }
        });
}

function getStatusBadge(status) {
    const badges = {
        'pending': 'warning',
        'approved': 'success',
        'denied': 'danger',
        'expired': 'secondary'
    };
    return badges[status] || 'light';
}
</script>
@endsection