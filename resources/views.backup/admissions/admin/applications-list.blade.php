{{-- resources/views/admissions/admin/applications-list.blade.php --}}
@extends('layouts.app')

@section('title', 'Applications Management')

@section('content')
<div class="container-fluid py-4">
    {{-- Header Section --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-clipboard-list me-2"></i>Applications Management
                    </h1>
                    <p class="text-muted mb-0">
                        {{ $applications->total() }} total applications | 
                        {{ $currentTerm->name ?? 'All Terms' }}
                    </p>
                </div>
                <div class="btn-toolbar" role="toolbar">
                    <div class="btn-group me-2" role="group">
                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#filterModal">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                        <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-download me-1"></i> Export
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="exportApplications('excel')">
                                <i class="fas fa-file-excel me-2"></i>Excel
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="exportApplications('csv')">
                                <i class="fas fa-file-csv me-2"></i>CSV
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="exportApplications('pdf')">
                                <i class="fas fa-file-pdf me-2"></i>PDF
                            </a></li>
                        </ul>
                    </div>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#bulkActionModal">
                            <i class="fas fa-tasks me-1"></i> Bulk Actions
                        </button>
                        <a href="{{ route('admissions.admin.applications.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> New Application
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Active Filters Display --}}
    @if(request()->hasAny(['status', 'program', 'term', 'search', 'date_from', 'date_to']))
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-info py-2">
                <strong>Active Filters:</strong>
                @if(request('status'))
                    <span class="badge bg-primary ms-2">Status: {{ ucwords(str_replace('_', ' ', request('status'))) }}</span>
                @endif
                @if(request('program'))
                    <span class="badge bg-primary ms-2">Program: {{ $programs->find(request('program'))->name ?? 'Unknown' }}</span>
                @endif
                @if(request('search'))
                    <span class="badge bg-primary ms-2">Search: {{ request('search') }}</span>
                @endif
                @if(request('date_from') || request('date_to'))
                    <span class="badge bg-primary ms-2">
                        Date: {{ request('date_from') }} - {{ request('date_to') }}
                    </span>
                @endif
                <a href="{{ route('admissions.admin.applications.index') }}" class="btn btn-sm btn-outline-secondary ms-3">
                    <i class="fas fa-times"></i> Clear All
                </a>
            </div>
        </div>
    </div>
    @endif

    {{-- Quick Stats Bar --}}
    <div class="row mb-4">
        <div class="col-md-2 col-sm-6 mb-3">
            <div class="card bg-light">
                <div class="card-body py-2 px-3">
                    <div class="text-uppercase text-muted small">Submitted</div>
                    <div class="h5 mb-0">{{ $stats['submitted'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-6 mb-3">
            <div class="card bg-warning bg-opacity-10">
                <div class="card-body py-2 px-3">
                    <div class="text-uppercase text-muted small">Under Review</div>
                    <div class="h5 mb-0">{{ $stats['under_review'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-6 mb-3">
            <div class="card bg-info bg-opacity-10">
                <div class="card-body py-2 px-3">
                    <div class="text-uppercase text-muted small">Interview</div>
                    <div class="h5 mb-0">{{ $stats['interview_scheduled'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-6 mb-3">
            <div class="card bg-success bg-opacity-10">
                <div class="card-body py-2 px-3">
                    <div class="text-uppercase text-muted small">Admitted</div>
                    <div class="h5 mb-0">{{ $stats['admitted'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-6 mb-3">
            <div class="card bg-danger bg-opacity-10">
                <div class="card-body py-2 px-3">
                    <div class="text-uppercase text-muted small">Denied</div>
                    <div class="h5 mb-0">{{ $stats['denied'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-6 mb-3">
            <div class="card bg-secondary bg-opacity-10">
                <div class="card-body py-2 px-3">
                    <div class="text-uppercase text-muted small">Waitlisted</div>
                    <div class="h5 mb-0">{{ $stats['waitlisted'] ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Applications Table --}}
    <div class="card shadow">
        <div class="card-body">
            {{-- Search Bar --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <form method="GET" action="{{ route('admissions.admin.applications.index') }}" class="d-flex">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Search by name, email, or application number..." 
                                   value="{{ request('search') }}">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </form>
                </div>
                <div class="col-md-6 text-end">
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-secondary {{ request('view') != 'grid' ? 'active' : '' }}" 
                                onclick="changeView('list')">
                            <i class="fas fa-list"></i> List
                        </button>
                        <button type="button" class="btn btn-outline-secondary {{ request('view') == 'grid' ? 'active' : '' }}" 
                                onclick="changeView('grid')">
                            <i class="fas fa-th"></i> Grid
                        </button>
                    </div>
                </div>
            </div>

            {{-- Table View --}}
            @if(request('view') != 'grid')
            <div class="table-responsive">
                <table class="table table-hover" id="applicationsTable">
                    <thead>
                        <tr>
                            <th width="30">
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th>
                                <a href="{{ route('admissions.admin.applications.index', array_merge(request()->all(), ['sort' => 'application_number', 'order' => request('order') == 'asc' ? 'desc' : 'asc'])) }}">
                                    Application # 
                                    @if(request('sort') == 'application_number')
                                        <i class="fas fa-sort-{{ request('order') == 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('admissions.admin.applications.index', array_merge(request()->all(), ['sort' => 'last_name', 'order' => request('order') == 'asc' ? 'desc' : 'asc'])) }}">
                                    Applicant Name
                                    @if(request('sort') == 'last_name')
                                        <i class="fas fa-sort-{{ request('order') == 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Email</th>
                            <th>Program</th>
                            <th>Type</th>
                            <th>
                                <a href="{{ route('admissions.admin.applications.index', array_merge(request()->all(), ['sort' => 'submitted_at', 'order' => request('order') == 'asc' ? 'desc' : 'asc'])) }}">
                                    Submitted
                                    @if(request('sort') == 'submitted_at')
                                        <i class="fas fa-sort-{{ request('order') == 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Completion</th>
                            <th>Status</th>
                            <th>Reviewer</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applications as $application)
                        <tr class="application-row" data-id="{{ $application->id }}">
                            <td>
                                <input type="checkbox" class="form-check-input select-item" value="{{ $application->id }}">
                            </td>
                            <td>
                                <a href="{{ route('admissions.admin.applications.show', $application->id) }}" class="text-primary">
                                    {{ $application->application_number }}
                                </a>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-2">
                                        <span class="avatar-title rounded-circle bg-primary text-white">
                                            {{ substr($application->first_name, 0, 1) }}{{ substr($application->last_name, 0, 1) }}
                                        </span>
                                    </div>
                                    <div>
                                        <strong>{{ $application->first_name }} {{ $application->last_name }}</strong>
                                        @if($application->preferred_name)
                                            <br><small class="text-muted">({{ $application->preferred_name }})</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a href="mailto:{{ $application->email }}">{{ $application->email }}</a>
                                <br><small class="text-muted">{{ $application->phone_primary }}</small>
                            </td>
                            <td>
                                {{ $application->program->code ?? 'N/A' }}
                                @if($application->alternate_program_id)
                                    <br><small class="text-muted">Alt: {{ $application->alternateProgram->code ?? 'N/A' }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ ucfirst($application->application_type) }}
                                </span>
                            </td>
                            <td>
                                @if($application->submitted_at)
                                    {{ $application->submitted_at->format('M d, Y') }}
                                    <br><small class="text-muted">{{ $application->submitted_at->diffForHumans() }}</small>
                                @else
                                    <span class="text-muted">Not submitted</span>
                                @endif
                            </td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-{{ $application->completionPercentage() >= 100 ? 'success' : ($application->completionPercentage() >= 75 ? 'info' : 'warning') }}" 
                                         role="progressbar" 
                                         style="width: {{ $application->completionPercentage() }}%"
                                         aria-valuenow="{{ $application->completionPercentage() }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        {{ $application->completionPercentage() }}%
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $application->getStatusColor() }}">
                                    {{ ucwords(str_replace('_', ' ', $application->status)) }}
                                </span>
                                @if($application->decision)
                                    <br>
                                    <span class="badge bg-{{ $application->decision == 'admit' ? 'success' : ($application->decision == 'deny' ? 'danger' : 'warning') }} mt-1">
                                        {{ ucfirst($application->decision) }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($application->reviews->count() > 0)
                                    <small>
                                        {{ $application->reviews->first()->reviewer->name ?? 'Assigned' }}
                                        @if($application->reviews->count() > 1)
                                            <br>+{{ $application->reviews->count() - 1 }} more
                                        @endif
                                    </small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('admissions.admin.applications.show', $application->id) }}" 
                                       class="btn btn-outline-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admissions.admin.applications.edit', $application->id) }}" 
                                       class="btn btn-outline-secondary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-info" 
                                            onclick="assignReviewer({{ $application->id }})" title="Assign Reviewer">
                                        <i class="fas fa-user-check"></i>
                                    </button>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="#" onclick="sendNotification({{ $application->id }})">
                                                    <i class="fas fa-envelope me-2"></i>Send Notification
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#" onclick="addNote({{ $application->id }})">
                                                    <i class="fas fa-sticky-note me-2"></i>Add Note
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admissions.admin.applications.history', $application->id) }}">
                                                    <i class="fas fa-history me-2"></i>View History
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="#" onclick="confirmDelete({{ $application->id }})">
                                                    <i class="fas fa-trash me-2"></i>Delete
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No applications found matching your criteria.</p>
                                <a href="{{ route('admissions.admin.applications.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i> Create New Application
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($applications->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div>
                    Showing {{ $applications->firstItem() }} to {{ $applications->lastItem() }} 
                    of {{ $applications->total() }} applications
                </div>
                <div>
                    {{ $applications->appends(request()->query())->links() }}
                </div>
            </div>
            @endif
            @else
            {{-- Grid View --}}
            <div class="row">
                @forelse($applications as $application)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 application-card">
                        <div class="card-header bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>{{ $application->application_number }}</strong>
                                <span class="badge bg-{{ $application->getStatusColor() }}">
                                    {{ ucwords(str_replace('_', ' ', $application->status)) }}
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">
                                {{ $application->first_name }} {{ $application->last_name }}
                            </h5>
                            <p class="card-text">
                                <small class="text-muted">
                                    <i class="fas fa-envelope me-1"></i> {{ $application->email }}<br>
                                    <i class="fas fa-phone me-1"></i> {{ $application->phone_primary }}<br>
                                    <i class="fas fa-graduation-cap me-1"></i> {{ $application->program->name ?? 'N/A' }}<br>
                                    <i class="fas fa-calendar me-1"></i> {{ $application->submitted_at ? $application->submitted_at->format('M d, Y') : 'Not submitted' }}
                                </small>
                            </p>
                            <div class="progress mb-3" style="height: 10px;">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: {{ $application->completionPercentage() }}%">
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-light">
                            <div class="btn-group btn-group-sm w-100" role="group">
                                <a href="{{ route('admissions.admin.applications.show', $application->id) }}" 
                                   class="btn btn-outline-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="{{ route('admissions.admin.applications.edit', $application->id) }}" 
                                   class="btn btn-outline-secondary">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <button type="button" class="btn btn-outline-info" 
                                        onclick="quickAction({{ $application->id }})">
                                    <i class="fas fa-bolt"></i> Action
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12 text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No applications found.</p>
                </div>
                @endforelse
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Filter Modal --}}
<div class="modal fade" id="filterModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="GET" action="{{ route('admissions.admin.applications.index') }}">
                <div class="modal-header">
                    <h5 class="modal-title">Filter Applications</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                @foreach(['draft', 'submitted', 'under_review', 'documents_pending', 'committee_review', 'interview_scheduled', 'decision_pending', 'admitted', 'waitlisted', 'denied', 'withdrawn'] as $status)
                                    <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                        {{ ucwords(str_replace('_', ' ', $status)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Program</label>
                            <select name="program" class="form-select">
                                <option value="">All Programs</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}" {{ request('program') == $program->id ? 'selected' : '' }}>
                                        {{ $program->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Term</label>
                            <select name="term" class="form-select">
                                <option value="">All Terms</option>
                                @foreach($terms as $term)
                                    <option value="{{ $term->id }}" {{ request('term') == $term->id ? 'selected' : '' }}>
                                        {{ $term->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Application Type</label>
                            <select name="type" class="form-select">
                                <option value="">All Types</option>
                                @foreach(['freshman', 'transfer', 'graduate', 'international', 'readmission'] as $type)
                                    <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                                        {{ ucfirst($type) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date From</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date To</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Bulk Action Modal --}}
<div class="modal fade" id="bulkActionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Actions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Select applications and choose an action:</p>
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary" onclick="bulkAction('update_status')">
                        <i class="fas fa-sync me-2"></i>Update Status
                    </button>
                    <button class="btn btn-outline-info" onclick="bulkAction('assign_reviewer')">
                        <i class="fas fa-user-check me-2"></i>Assign Reviewer
                    </button>
                    <button class="btn btn-outline-warning" onclick="bulkAction('send_notification')">
                        <i class="fas fa-envelope me-2"></i>Send Notification
                    </button>
                    <button class="btn btn-outline-success" onclick="bulkAction('export')">
                        <i class="fas fa-download me-2"></i>Export Selected
                    </button>
                    <button class="btn btn-outline-danger" onclick="bulkAction('delete')">
                        <i class="fas fa-trash me-2"></i>Delete Selected
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Select all checkbox
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.select-item');
        checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    });

    // Change view
    function changeView(view) {
        const url = new URL(window.location);
        url.searchParams.set('view', view);
        window.location = url;
    }

    // Bulk actions
    function bulkAction(action) {
        const selected = Array.from(document.querySelectorAll('.select-item:checked')).map(cb => cb.value);
        if (selected.length === 0) {
            alert('Please select at least one application.');
            return;
        }
        
        // Implement bulk action logic
        console.log('Bulk action:', action, 'Selected:', selected);
    }

    // Export applications
    function exportApplications(format) {
        const url = new URL('{{ route("admissions.admin.applications.export") }}');
        url.searchParams.set('format', format);
        window.location = url;
    }

    // Quick actions
    function assignReviewer(id) {
        // Implement assign reviewer modal
        console.log('Assign reviewer for:', id);
    }

    function sendNotification(id) {
        // Implement send notification modal
        console.log('Send notification for:', id);
    }

    function addNote(id) {
        // Implement add note modal
        console.log('Add note for:', id);
    }

    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this application?')) {
            // Implement delete logic
            console.log('Delete application:', id);
        }
    }

    function quickAction(id) {
        // Implement quick action menu
        console.log('Quick action for:', id);
    }
</script>
@endpush
@endsection