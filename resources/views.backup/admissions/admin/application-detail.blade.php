{{-- File: resources/views/admissions/admin/application-detail.blade.php --}}
@extends('layouts.app')

@section('title', 'Application Details - ' . $application->application_number)

@section('content')
<div class="container-fluid">
    {{-- Header with Actions --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">Application Details</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admissions.admin.dashboard') }}">Admissions</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admissions.admin.applications.index') }}">Applications</a></li>
                            <li class="breadcrumb-item active">{{ $application->application_number }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="btn-toolbar">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                            <i class="fas fa-print"></i> Print
                        </button>
                        <a href="{{ route('admissions.admin.applications.export', $application->id) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-download"></i> Export PDF
                        </a>
                    </div>
                    <div class="btn-group">
                        @if($application->status !== 'withdrawn')
                            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-cog"></i> Actions
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ route('admissions.admin.applications.review', $application->id) }}">
                                        <i class="fas fa-star"></i> Add Review
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#assignReviewerModal">
                                        <i class="fas fa-user-plus"></i> Assign Reviewer
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#updateStatusModal">
                                        <i class="fas fa-sync"></i> Update Status
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('admissions.admin.decisions.make', $application->id) }}">
                                        <i class="fas fa-gavel"></i> Make Decision
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#sendCommunicationModal">
                                        <i class="fas fa-envelope"></i> Send Communication
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="#" onclick="confirmWithdraw({{ $application->id }})">
                                        <i class="fas fa-times-circle"></i> Withdraw Application
                                    </a>
                                </li>
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Status Bar --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body py-2">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <small class="text-muted">Status</small>
                            <div>
                                @include('admissions.partials.status-badge', ['status' => $application->status])
                            </div>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Decision</small>
                            <div>
                                @if($application->decision)
                                    <span class="badge bg-{{ $application->getDecisionColor() }}">
                                        {{ ucfirst(str_replace('_', ' ', $application->decision)) }}
                                    </span>
                                @else
                                    <span class="text-muted">Pending</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Completion</small>
                            <div>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: {{ $application->completionPercentage() }}%"
                                         aria-valuenow="{{ $application->completionPercentage() }}" 
                                         aria-valuemin="0" aria-valuemax="100">
                                        {{ $application->completionPercentage() }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Submitted</small>
                            <div>
                                @if($application->submitted_at)
                                    {{ $application->submitted_at->format('M d, Y h:i A') }}
                                @else
                                    <span class="text-muted">Not submitted</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="row">
        {{-- Left Column - Application Details --}}
        <div class="col-lg-8">
            {{-- Personal Information --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user"></i> Personal Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-5">Full Name:</dt>
                                <dd class="col-sm-7">
                                    {{ $application->first_name }} {{ $application->middle_name }} {{ $application->last_name }}
                                    @if($application->preferred_name)
                                        <br><small class="text-muted">(Preferred: {{ $application->preferred_name }})</small>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-5">Date of Birth:</dt>
                                <dd class="col-sm-7">{{ $application->date_of_birth->format('M d, Y') }} 
                                    <small class="text-muted">(Age: {{ $application->date_of_birth->age }})</small>
                                </dd>
                                
                                <dt class="col-sm-5">Gender:</dt>
                                <dd class="col-sm-7">{{ ucfirst($application->gender ?? 'Not specified') }}</dd>
                                
                                <dt class="col-sm-5">Nationality:</dt>
                                <dd class="col-sm-7">{{ $application->nationality }}</dd>
                                
                                <dt class="col-sm-5">National ID:</dt>
                                <dd class="col-sm-7">{{ $application->national_id ?? 'Not provided' }}</dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-5">Email:</dt>
                                <dd class="col-sm-7">
                                    <a href="mailto:{{ $application->email }}">{{ $application->email }}</a>
                                </dd>
                                
                                <dt class="col-sm-5">Primary Phone:</dt>
                                <dd class="col-sm-7">{{ $application->phone_primary }}</dd>
                                
                                <dt class="col-sm-5">Secondary Phone:</dt>
                                <dd class="col-sm-7">{{ $application->phone_secondary ?? 'N/A' }}</dd>
                                
                                <dt class="col-sm-5">Current Address:</dt>
                                <dd class="col-sm-7">{{ $application->current_address }}</dd>
                                
                                <dt class="col-sm-5">City/State:</dt>
                                <dd class="col-sm-7">{{ $application->city }}, {{ $application->state_province ?? '' }} {{ $application->postal_code ?? '' }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Academic Information --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-graduation-cap"></i> Academic Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-5">Application Type:</dt>
                                <dd class="col-sm-7">{{ ucfirst(str_replace('_', ' ', $application->application_type)) }}</dd>
                                
                                <dt class="col-sm-5">Term:</dt>
                                <dd class="col-sm-7">{{ $application->term->name ?? 'N/A' }}</dd>
                                
                                <dt class="col-sm-5">Program:</dt>
                                <dd class="col-sm-7">{{ $application->program->name ?? 'N/A' }}</dd>
                                
                                <dt class="col-sm-5">Alternate Program:</dt>
                                <dd class="col-sm-7">{{ $application->alternateProgram->name ?? 'None' }}</dd>
                                
                                <dt class="col-sm-5">Entry Type:</dt>
                                <dd class="col-sm-7">{{ ucfirst($application->entry_type ?? 'N/A') }} {{ $application->entry_year }}</dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-5">Previous Institution:</dt>
                                <dd class="col-sm-7">{{ $application->previous_institution ?? 'N/A' }}</dd>
                                
                                <dt class="col-sm-5">Previous Degree:</dt>
                                <dd class="col-sm-7">{{ $application->previous_degree ?? 'N/A' }}</dd>
                                
                                <dt class="col-sm-5">Previous GPA:</dt>
                                <dd class="col-sm-7">
                                    @if($application->previous_gpa)
                                        {{ $application->previous_gpa }} / {{ $application->gpa_scale ?? '4.0' }}
                                    @else
                                        N/A
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-5">Class Rank:</dt>
                                <dd class="col-sm-7">
                                    @if($application->class_rank && $application->class_size)
                                        {{ $application->class_rank }} of {{ $application->class_size }}
                                    @else
                                        N/A
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Test Scores --}}
            @if($application->test_scores)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-line"></i> Test Scores</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Test</th>
                                    <th>Score</th>
                                    <th>Test Date</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($application->getFormattedTestScores() as $test => $score)
                                <tr>
                                    <td><strong>{{ $test }}</strong></td>
                                    <td colspan="3">{{ $score }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            {{-- Documents --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-file-alt"></i> Documents</h5>
                    <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#requestDocumentsModal">
                        <i class="fas fa-plus"></i> Request Documents
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Document Type</th>
                                    <th>File Name</th>
                                    <th>Uploaded</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($application->documents as $document)
                                <tr>
                                    <td>{{ ucfirst(str_replace('_', ' ', $document->document_type)) }}</td>
                                    <td>{{ $document->document_name }}</td>
                                    <td>{{ $document->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $document->getStatusColor() }}">
                                            {{ ucfirst($document->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admissions.admin.documents.view', $document->id) }}" 
                                               class="btn btn-outline-primary" target="_blank">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admissions.admin.documents.download', $document->id) }}" 
                                               class="btn btn-outline-secondary">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            @if($document->status === 'uploaded')
                                            <button class="btn btn-outline-success" 
                                                    onclick="verifyDocument({{ $document->id }})">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No documents uploaded</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Essays --}}
            @if($application->personal_statement || $application->statement_of_purpose)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-pen"></i> Essays & Statements</h5>
                </div>
                <div class="card-body">
                    @if($application->personal_statement)
                    <h6>Personal Statement</h6>
                    <div class="p-3 bg-light rounded mb-3">
                        <p>{{ $application->personal_statement }}</p>
                    </div>
                    @endif
                    
                    @if($application->statement_of_purpose)
                    <h6>Statement of Purpose</h6>
                    <div class="p-3 bg-light rounded">
                        <p>{{ $application->statement_of_purpose }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- Right Column - Reviews & Activity --}}
        <div class="col-lg-4">
            {{-- Quick Actions --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admissions.admin.applications.review', $application->id) }}" 
                           class="btn btn-primary">
                            <i class="fas fa-star"></i> Add Review
                        </a>
                        <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                            <i class="fas fa-sticky-note"></i> Add Note
                        </button>
                        <a href="{{ route('admissions.admin.communications.compose', ['application' => $application->id]) }}" 
                           class="btn btn-warning">
                            <i class="fas fa-envelope"></i> Send Message
                        </a>
                        @if(!$application->decision)
                        <a href="{{ route('admissions.admin.decisions.make', $application->id) }}" 
                           class="btn btn-success">
                            <i class="fas fa-gavel"></i> Make Decision
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Reviews Summary --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-star"></i> Reviews</h5>
                </div>
                <div class="card-body">
                    @if($application->reviews->count() > 0)
                        <div class="mb-3">
                            <small class="text-muted">Average Rating</small>
                            <h4 class="mb-0">
                                {{ number_format($application->reviews->avg('overall_rating'), 1) }} / 5.0
                            </h4>
                            <div class="rating">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star {{ $i <= round($application->reviews->avg('overall_rating')) ? 'text-warning' : 'text-muted' }}"></i>
                                @endfor
                            </div>
                        </div>

                        @foreach($application->reviews as $review)
                        <div class="border-bottom pb-2 mb-2">
                            <div class="d-flex justify-content-between">
                                <strong>{{ $review->reviewer->name }}</strong>
                                <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                            </div>
                            <div class="rating-sm">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star {{ $i <= $review->overall_rating ? 'text-warning' : 'text-muted' }} small"></i>
                                @endfor
                            </div>
                            <p class="mb-1 small">{{ Str::limit($review->additional_comments, 100) }}</p>
                            <div>
                                <span class="badge bg-{{ $review->getRecommendationColor() }}">
                                    {{ ucfirst(str_replace('_', ' ', $review->recommendation)) }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                        
                        <a href="{{ route('admissions.admin.reviews.all', $application->id) }}" class="btn btn-sm btn-outline-primary w-100">
                            View All Reviews ({{ $application->reviews->count() }})
                        </a>
                    @else
                        <p class="text-muted text-center mb-0">No reviews yet</p>
                    @endif
                </div>
            </div>

            {{-- Activity Timeline --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Activity Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach($application->getActivityTimeline() as $activity)
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <small class="text-muted">{{ $activity['timestamp']->diffForHumans() }}</small>
                                <p class="mb-0">{{ $activity['description'] }}</p>
                                @if($activity['user'])
                                    <small class="text-muted">by {{ $activity['user']->name }}</small>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            <div class="card shadow-sm">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="fas fa-sticky-note"></i> Internal Notes</h5>
                </div>
                <div class="card-body">
                    @forelse($application->notes as $note)
                    <div class="note-item mb-2 pb-2 border-bottom">
                        <div class="d-flex justify-content-between">
                            <strong>{{ $note->creator->name }}</strong>
                            <small class="text-muted">{{ $note->created_at->format('M d, Y g:i A') }}</small>
                        </div>
                        <p class="mb-0 small">{{ $note->note }}</p>
                    </div>
                    @empty
                    <p class="text-muted text-center mb-0">No notes added</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modals --}}
@include('admissions.admin.partials.modals.update-status')
@include('admissions.admin.partials.modals.assign-reviewer')
@include('admissions.admin.partials.modals.add-note')
@include('admissions.admin.partials.modals.request-documents')
@include('admissions.admin.partials.modals.send-communication')

@endsection

@push('styles')
<style>
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    .timeline-item {
        position: relative;
        padding-bottom: 20px;
    }
    .timeline-item:before {
        content: '';
        position: absolute;
        left: -21px;
        top: 5px;
        height: 100%;
        width: 2px;
        background: #dee2e6;
    }
    .timeline-item:last-child:before {
        display: none;
    }
    .timeline-marker {
        position: absolute;
        left: -25px;
        top: 5px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #007bff;
        border: 2px solid #fff;
    }
    .rating-sm i {
        font-size: 0.8rem;
    }
</style>
@endpush

@push('scripts')
<script>
function verifyDocument(documentId) {
    if(confirm('Are you sure you want to verify this document?')) {
        $.post(`/admissions/admin/documents/${documentId}/verify`, {
            _token: '{{ csrf_token() }}'
        }).done(function(response) {
            location.reload();
        });
    }
}

function confirmWithdraw(applicationId) {
    if(confirm('Are you sure you want to withdraw this application? This action cannot be undone.')) {
        $.post(`/admissions/admin/applications/${applicationId}/withdraw`, {
            _token: '{{ csrf_token() }}'
        }).done(function(response) {
            window.location.href = '{{ route("admissions.admin.applications.index") }}';
        });
    }
}
</script>
@endpush