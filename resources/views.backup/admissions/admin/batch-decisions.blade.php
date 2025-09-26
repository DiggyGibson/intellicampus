{{-- File: resources/views/admissions/admin/batch-decisions.blade.php --}}
@extends('layouts.app')

@section('title', 'Batch Decisions - Admissions')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">Batch Decision Processing</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admissions.admin.dashboard') }}">Admissions</a></li>
                            <li class="breadcrumb-item active">Batch Decisions</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <button class="btn btn-outline-secondary" onclick="window.print()">
                        <i class="fas fa-print"></i> Print List
                    </button>
                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importDecisionsModal">
                        <i class="fas fa-upload"></i> Import Decisions
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="text-uppercase">Selected Applications</h6>
                    <h2 class="mb-0" id="selectedCount">0</h2>
                    <small>Ready for decision</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="text-uppercase">Pending Review</h6>
                    <h2 class="mb-0">{{ $statistics['pending_review'] ?? 0 }}</h2>
                    <small>Awaiting committee review</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6 class="text-uppercase">Ready for Decision</h6>
                    <h2 class="mb-0">{{ $statistics['ready_for_decision'] ?? 0 }}</h2>
                    <small>Reviews complete</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="text-uppercase">Decided Today</h6>
                    <h2 class="mb-0">{{ $statistics['decided_today'] ?? 0 }}</h2>
                    <small>Decisions made today</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Batch Decision Form --}}
    <form action="{{ route('admissions.admin.decisions.batch.process') }}" method="POST" id="batchDecisionForm">
        @csrf
        
        {{-- Action Bar --}}
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label for="batchDecision" class="form-label">Decision Type</label>
                        <select class="form-select" id="batchDecision" name="batch_decision" required>
                            <option value="">Select Decision</option>
                            <option value="admit">Admit</option>
                            <option value="conditional_admit">Conditional Admit</option>
                            <option value="waitlist">Waitlist</option>
                            <option value="deny">Deny</option>
                            <option value="defer">Defer</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="notificationMethod" class="form-label">Notification</label>
                        <select class="form-select" id="notificationMethod" name="notification_method">
                            <option value="none">Do Not Send</option>
                            <option value="email">Email</option>
                            <option value="email_sms">Email + SMS</option>
                            <option value="portal">Portal Only</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="releaseDate" class="form-label">Release Date</label>
                        <input type="date" class="form-control" id="releaseDate" name="release_date" 
                               value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}">
                    </div>
                    
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100" id="applyDecisionBtn" disabled>
                            <i class="fas fa-gavel"></i> Apply Decision to Selected
                        </button>
                    </div>
                </div>
                
                {{-- Additional Options (Hidden by default) --}}
                <div class="row mt-3" id="additionalOptions" style="display: none;">
                    <div class="col-md-6" id="conditionalAdmitOptions" style="display: none;">
                        <label for="admissionConditions" class="form-label">Admission Conditions</label>
                        <textarea class="form-control" id="admissionConditions" name="admission_conditions" rows="2"
                                  placeholder="Standard conditions for all selected applicants..."></textarea>
                    </div>
                    
                    <div class="col-md-3" id="waitlistOptions" style="display: none;">
                        <label for="waitlistStartRank" class="form-label">Starting Waitlist Rank</label>
                        <input type="number" class="form-control" id="waitlistStartRank" name="waitlist_start_rank" 
                               min="1" value="{{ $nextWaitlistRank ?? 1 }}">
                    </div>
                    
                    <div class="col-md-3" id="deferOptions" style="display: none;">
                        <label for="deferToTerm" class="form-label">Defer to Term</label>
                        <select class="form-select" id="deferToTerm" name="defer_to_term">
                            <option value="">Select Term</option>
                            @foreach($futureTerms as $term)
                            <option value="{{ $term->id }}">{{ $term->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-filter"></i> Filters</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <label for="filterProgram" class="form-label">Program</label>
                        <select class="form-select" id="filterProgram">
                            <option value="">All Programs</option>
                            @foreach($programs as $program)
                            <option value="{{ $program->id }}">{{ $program->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="filterStatus" class="form-label">Status</label>
                        <select class="form-select" id="filterStatus">
                            <option value="">All Statuses</option>
                            <option value="committee_review">Committee Review</option>
                            <option value="decision_pending">Decision Pending</option>
                            <option value="interview_complete">Interview Complete</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label for="filterGpaMin" class="form-label">Min GPA</label>
                        <input type="number" class="form-control" id="filterGpaMin" 
                               min="0" max="4" step="0.1" placeholder="0.0">
                    </div>
                    
                    <div class="col-md-2">
                        <label for="filterRating" class="form-label">Min Rating</label>
                        <select class="form-select" id="filterRating">
                            <option value="">Any Rating</option>
                            <option value="4">4+ Stars</option>
                            <option value="3">3+ Stars</option>
                            <option value="2">2+ Stars</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-secondary w-100" onclick="applyFilters()">
                            <i class="fas fa-search"></i> Apply
                        </button>
                    </div>
                </div>
                
                {{-- Quick Filters --}}
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="quickFilter('high_achievers')">
                                <i class="fas fa-star"></i> High Achievers (GPA 3.7+)
                            </button>
                            <button type="button" class="btn btn-outline-info btn-sm" onclick="quickFilter('unanimous_recommend')">
                                <i class="fas fa-thumbs-up"></i> Unanimous Recommend
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm" onclick="quickFilter('scholarship_eligible')">
                                <i class="fas fa-award"></i> Scholarship Eligible
                            </button>
                            <button type="button" class="btn btn-outline-warning btn-sm" onclick="quickFilter('borderline')">
                                <i class="fas fa-exclamation-triangle"></i> Borderline Cases
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearFilters()">
                                <i class="fas fa-times"></i> Clear Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Applications Table --}}
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Applications for Decision</h5>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="selectAll">
                            <label class="form-check-label" for="selectAll">Select All</label>
                        </div>
                        <span class="badge bg-secondary" id="visibleCount">{{ $applications->count() }} visible</span>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="applicationsTable">
                        <thead class="table-light">
                            <tr>
                                <th width="40">
                                    <input type="checkbox" class="form-check-input" id="checkAll">
                                </th>
                                <th>Application #</th>
                                <th>Name</th>
                                <th>Program</th>
                                <th>Type</th>
                                <th>GPA</th>
                                <th>Test Score</th>
                                <th>Rating</th>
                                <th>Reviews</th>
                                <th>Recommendation</th>
                                <th>Status</th>
                                <th width="100">Individual</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($applications as $application)
                            <tr class="application-row" 
                                data-id="{{ $application->id }}"
                                data-program="{{ $application->program_id }}"
                                data-gpa="{{ $application->previous_gpa }}"
                                data-rating="{{ $application->reviews->avg('overall_rating') }}">
                                <td>
                                    <input type="checkbox" class="form-check-input select-application" 
                                           name="application_ids[]" value="{{ $application->id }}">
                                </td>
                                <td>
                                    <a href="{{ route('admissions.admin.applications.show', $application->id) }}" 
                                       target="_blank" class="text-primary">
                                        {{ $application->application_number }}
                                    </a>
                                </td>
                                <td>
                                    <strong>{{ $application->first_name }} {{ $application->last_name }}</strong>
                                    @if($application->preferred_name)
                                        <br><small class="text-muted">({{ $application->preferred_name }})</small>
                                    @endif
                                </td>
                                <td>{{ $application->program->code ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ ucfirst(str_replace('_', ' ', $application->application_type)) }}
                                    </span>
                                </td>
                                <td>
                                    @if($application->previous_gpa)
                                        <strong>{{ number_format($application->previous_gpa, 2) }}</strong>
                                        <small>/{{ $application->gpa_scale ?? '4.0' }}</small>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($application->test_scores)
                                        @php
                                            $scores = $application->test_scores;
                                            $primaryTest = $scores['SAT'] ?? $scores['ACT'] ?? $scores['GRE'] ?? null;
                                        @endphp
                                        @if($primaryTest)
                                            <small>{{ array_key_first($scores) }}: {{ $primaryTest['total'] ?? $primaryTest['composite'] ?? 'N/A' }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($application->reviews->count() > 0)
                                        <div class="rating-sm">
                                            @php $avgRating = $application->reviews->avg('overall_rating') @endphp
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star {{ $i <= round($avgRating) ? 'text-warning' : 'text-muted' }} small"></i>
                                            @endfor
                                            <br>
                                            <small>{{ number_format($avgRating, 1) }}</small>
                                        </div>
                                    @else
                                        <span class="text-muted">No reviews</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $application->reviews->count() }}</span>
                                </td>
                                <td>
                                    @if($application->reviews->count() > 0)
                                        @php
                                            $recommendations = $application->reviews->groupBy('recommendation');
                                            $topRecommendation = $recommendations->sortByDesc(function($group) {
                                                return $group->count();
                                            })->keys()->first();
                                        @endphp
                                        <span class="badge bg-{{ $application->reviews->first()->getRecommendationColor($topRecommendation) }}">
                                            {{ ucfirst(str_replace('_', ' ', $topRecommendation)) }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @include('admissions.partials.status-badge', ['status' => $application->status])
                                </td>
                                <td>
                                    <a href="{{ route('admissions.admin.decisions.make', $application->id) }}" 
                                       class="btn btn-sm btn-outline-primary" target="_blank">
                                        <i class="fas fa-gavel"></i> Decide
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="12" class="text-center py-4 text-muted">
                                    No applications found matching the criteria
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-md-6">
                        Showing {{ $applications->firstItem() ?? 0 }} to {{ $applications->lastItem() ?? 0 }} 
                        of {{ $applications->total() }} applications
                    </div>
                    <div class="col-md-6">
                        <div class="float-end">
                            {{ $applications->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- Import Decisions Modal --}}
<div class="modal fade" id="importDecisionsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admissions.admin.decisions.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Import Decisions from CSV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="csvFile" class="form-label">Select CSV File</label>
                        <input type="file" class="form-control" id="csvFile" name="csv_file" accept=".csv" required>
                        <small class="text-muted">
                            CSV should contain columns: application_number, decision, decision_reason
                        </small>
                    </div>
                    <div class="mb-3">
                        <a href="{{ route('admissions.admin.decisions.template') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-download"></i> Download Template
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Confirmation Modal --}}
<div class="modal fade" id="confirmationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Confirm Batch Decision</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>You are about to apply the following decision:</p>
                <ul>
                    <li>Decision: <strong id="confirmDecision"></strong></li>
                    <li>Applications: <strong id="confirmCount"></strong></li>
                    <li>Notification: <strong id="confirmNotification"></strong></li>
                </ul>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> 
                    This action cannot be easily undone. Please review carefully.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmBatchBtn">
                    <i class="fas fa-gavel"></i> Confirm and Process
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .rating-sm i {
        font-size: 0.7rem;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(0,123,255,0.05);
    }
    .application-row.selected {
        background-color: rgba(0,123,255,0.1);
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Update selected count
    function updateSelectedCount() {
        const count = $('.select-application:checked').length;
        $('#selectedCount').text(count);
        $('#applyDecisionBtn').prop('disabled', count === 0 || !$('#batchDecision').val());
        
        // Highlight selected rows
        $('.application-row').removeClass('selected');
        $('.select-application:checked').each(function() {
            $(this).closest('tr').addClass('selected');
        });
    }
    
    // Select all functionality
    $('#checkAll, #selectAll').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.select-application').prop('checked', isChecked);
        $('#checkAll, #selectAll').prop('checked', isChecked);
        updateSelectedCount();
    });
    
    // Individual checkbox change
    $('.select-application').on('change', function() {
        updateSelectedCount();
    });
    
    // Decision type change
    $('#batchDecision').on('change', function() {
        const decision = $(this).val();
        
        // Hide all conditional fields
        $('#conditionalAdmitOptions, #waitlistOptions, #deferOptions').hide();
        
        // Show relevant fields
        if (decision === 'conditional_admit') {
            $('#additionalOptions').show();
            $('#conditionalAdmitOptions').show();
        } else if (decision === 'waitlist') {
            $('#additionalOptions').show();
            $('#waitlistOptions').show();
        } else if (decision === 'defer') {
            $('#additionalOptions').show();
            $('#deferOptions').show();
        } else {
            $('#additionalOptions').hide();
        }
        
        updateSelectedCount();
    });
    
    // Form submission
    $('#batchDecisionForm').on('submit', function(e) {
        e.preventDefault();
        
        const selectedCount = $('.select-application:checked').length;
        const decision = $('#batchDecision').val();
        const notification = $('#notificationMethod').val();
        
        if (selectedCount === 0) {
            alert('Please select at least one application.');
            return false;
        }
        
        // Show confirmation modal
        $('#confirmDecision').text(decision.replace('_', ' ').toUpperCase());
        $('#confirmCount').text(selectedCount);
        $('#confirmNotification').text(notification === 'none' ? 'No notification' : notification.replace('_', ' ').toUpperCase());
        $('#confirmationModal').modal('show');
    });
    
    // Confirm batch processing
    $('#confirmBatchBtn').on('click', function() {
        $('#confirmationModal').modal('hide');
        $('#batchDecisionForm')[0].submit();
    });
    
    // Initialize
    updateSelectedCount();
});

// Filter functions
function applyFilters() {
    const program = $('#filterProgram').val();
    const status = $('#filterStatus').val();
    const minGpa = $('#filterGpaMin').val();
    const minRating = $('#filterRating').val();
    
    $('.application-row').each(function() {
        const row = $(this);
        let show = true;
        
        if (program && row.data('program') != program) show = false;
        if (minGpa && parseFloat(row.data('gpa')) < parseFloat(minGpa)) show = false;
        if (minRating && parseFloat(row.data('rating')) < parseFloat(minRating)) show = false;
        
        row.toggle(show);
    });
    
    updateVisibleCount();
}

function quickFilter(type) {
    $('.application-row').show();
    
    switch(type) {
        case 'high_achievers':
            $('.application-row').each(function() {
                const gpa = parseFloat($(this).data('gpa'));
                $(this).toggle(gpa >= 3.7);
            });
            break;
        case 'unanimous_recommend':
            // Filter logic for unanimous recommendations
            break;
        case 'scholarship_eligible':
            $('.application-row').each(function() {
                const gpa = parseFloat($(this).data('gpa'));
                const rating = parseFloat($(this).data('rating'));
                $(this).toggle(gpa >= 3.5 && rating >= 4);
            });
            break;
        case 'borderline':
            $('.application-row').each(function() {
                const gpa = parseFloat($(this).data('gpa'));
                $(this).toggle(gpa >= 2.8 && gpa <= 3.2);
            });
            break;
    }
    
    updateVisibleCount();
}

function clearFilters() {
    $('#filterProgram, #filterStatus, #filterRating').val('');
    $('#filterGpaMin').val('');
    $('.application-row').show();
    updateVisibleCount();
}

function updateVisibleCount() {
    const count = $('.application-row:visible').length;
    $('#visibleCount').text(count + ' visible');
}
</script>
@endpush