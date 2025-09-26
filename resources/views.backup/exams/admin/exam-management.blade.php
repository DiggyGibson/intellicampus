{{-- File: resources/views/exams/admin/exam-management.blade.php --}}
@extends('layouts.app')

@section('title', 'Manage Exam - ' . $exam->exam_code)

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .nav-pills .nav-link {
        border-radius: 20px;
        padding: 10px 20px;
        margin-right: 10px;
        background: #f8f9fa;
        color: #495057;
        transition: all 0.3s;
    }
    .nav-pills .nav-link.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    .section-card {
        border-left: 4px solid #007bff;
        transition: all 0.3s;
    }
    .section-card:hover {
        transform: translateX(5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .question-type-badge {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 0.85rem;
    }
    .status-indicator {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 5px;
    }
    .status-indicator.active {
        background: #28a745;
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(40, 167, 69, 0); }
        100% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0); }
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-cogs me-2"></i>Manage Examination
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('exams.admin.dashboard') }}">Exam Dashboard</a></li>
                            <li class="breadcrumb-item active">{{ $exam->exam_code }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="btn-toolbar">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                            <i class="fas fa-print"></i> Print
                        </button>
                        <a href="{{ route('exams.admin.export', $exam->id) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-download"></i> Export
                        </a>
                    </div>
                    <button type="button" class="btn btn-{{ $exam->status == 'published' ? 'warning' : 'success' }}" 
                            onclick="toggleExamStatus()">
                        <i class="fas fa-{{ $exam->status == 'published' ? 'pause' : 'play' }}"></i>
                        {{ $exam->status == 'published' ? 'Unpublish' : 'Publish' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Exam Overview Card --}}
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h4 class="mb-3">{{ $exam->exam_name }}</h4>
                    <p class="text-muted">{{ $exam->description }}</p>
                    <div class="row g-3">
                        <div class="col-auto">
                            <span class="badge bg-primary fs-6">{{ ucfirst($exam->exam_type) }}</span>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-info fs-6">
                                <i class="fas fa-desktop me-1"></i>{{ ucfirst(str_replace('_', ' ', $exam->delivery_mode)) }}
                            </span>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-secondary fs-6">
                                <i class="fas fa-clock me-1"></i>{{ $exam->duration_minutes }} minutes
                            </span>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-success fs-6">
                                <i class="fas fa-question-circle me-1"></i>{{ $exam->total_questions }} questions
                            </span>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-warning text-dark fs-6">
                                <i class="fas fa-star me-1"></i>{{ $exam->total_marks }} marks
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="mb-3">
                        <span class="status-indicator {{ $exam->status == 'published' ? 'active' : '' }}"></span>
                        <span class="text-muted">Status:</span> 
                        <strong class="text-{{ $exam->status == 'published' ? 'success' : 'secondary' }}">
                            {{ ucfirst($exam->status) }}
                        </strong>
                    </div>
                    <div class="mb-3">
                        <span class="text-muted">Registrations:</span>
                        <h4>{{ $exam->registrations_count ?? 0 }} / {{ $exam->max_registrations ?? '∞' }}</h4>
                    </div>
                    <div>
                        <span class="text-muted">Exam Period:</span><br>
                        @if($exam->exam_date)
                            <strong>{{ \Carbon\Carbon::parse($exam->exam_date)->format('M d, Y') }}</strong>
                        @else
                            <strong>{{ \Carbon\Carbon::parse($exam->exam_window_start)->format('M d') }} - 
                            {{ \Carbon\Carbon::parse($exam->exam_window_end)->format('M d, Y') }}</strong>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Navigation Tabs --}}
    <ul class="nav nav-pills mb-4" id="examTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button">
                <i class="fas fa-info-circle me-2"></i>Details
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="structure-tab" data-bs-toggle="tab" data-bs-target="#structure" type="button">
                <i class="fas fa-sitemap me-2"></i>Structure
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="questions-tab" data-bs-toggle="tab" data-bs-target="#questions" type="button">
                <i class="fas fa-question me-2"></i>Questions
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="registrations-tab" data-bs-toggle="tab" data-bs-target="#registrations" type="button">
                <i class="fas fa-users me-2"></i>Registrations
                @if($exam->pending_registrations_count > 0)
                <span class="badge bg-danger ms-1">{{ $exam->pending_registrations_count }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="sessions-tab" data-bs-toggle="tab" data-bs-target="#sessions" type="button">
                <i class="fas fa-calendar-alt me-2"></i>Sessions
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings" type="button">
                <i class="fas fa-cog me-2"></i>Settings
            </button>
        </li>
    </ul>

    {{-- Tab Content --}}
    <div class="tab-content" id="examTabContent">
        {{-- Details Tab --}}
        <div class="tab-pane fade show active" id="details" role="tabpanel">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0">Examination Details</h5>
                </div>
                <div class="card-body">
                    <form id="examDetailsForm" method="POST" action="{{ route('exams.admin.update', $exam->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Exam Code</label>
                                <input type="text" class="form-control" name="exam_code" value="{{ $exam->exam_code }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Exam Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="exam_name" value="{{ $exam->exam_name }}" required>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="3">{{ $exam->description }}</textarea>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Exam Type <span class="text-danger">*</span></label>
                                <select class="form-select" name="exam_type" required>
                                    <option value="entrance" {{ $exam->exam_type == 'entrance' ? 'selected' : '' }}>Entrance</option>
                                    <option value="placement" {{ $exam->exam_type == 'placement' ? 'selected' : '' }}>Placement</option>
                                    <option value="diagnostic" {{ $exam->exam_type == 'diagnostic' ? 'selected' : '' }}>Diagnostic</option>
                                    <option value="scholarship" {{ $exam->exam_type == 'scholarship' ? 'selected' : '' }}>Scholarship</option>
                                    <option value="transfer_credit" {{ $exam->exam_type == 'transfer_credit' ? 'selected' : '' }}>Transfer Credit</option>
                                    <option value="exemption" {{ $exam->exam_type == 'exemption' ? 'selected' : '' }}>Exemption</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Delivery Mode <span class="text-danger">*</span></label>
                                <select class="form-select" name="delivery_mode" required>
                                    <option value="paper_based" {{ $exam->delivery_mode == 'paper_based' ? 'selected' : '' }}>Paper Based</option>
                                    <option value="computer_based" {{ $exam->delivery_mode == 'computer_based' ? 'selected' : '' }}>Computer Based (CBT)</option>
                                    <option value="online_proctored" {{ $exam->delivery_mode == 'online_proctored' ? 'selected' : '' }}>Online Proctored</option>
                                    <option value="online_unproctored" {{ $exam->delivery_mode == 'online_unproctored' ? 'selected' : '' }}>Online Unproctored</option>
                                    <option value="hybrid" {{ $exam->delivery_mode == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                                    <option value="take_home" {{ $exam->delivery_mode == 'take_home' ? 'selected' : '' }}>Take Home</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Duration (minutes) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="duration_minutes" value="{{ $exam->duration_minutes }}" min="1" required>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Total Questions <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="total_questions" value="{{ $exam->total_questions }}" min="1" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Total Marks <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="total_marks" value="{{ $exam->total_marks }}" min="1" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Passing Marks <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="passing_marks" value="{{ $exam->passing_marks }}" min="0" required>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="negative_marking" id="negativeMarking" 
                                           {{ $exam->negative_marking ? 'checked' : '' }}>
                                    <label class="form-check-label" for="negativeMarking">
                                        Enable Negative Marking
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6" id="negativeMarkValueDiv" style="{{ !$exam->negative_marking ? 'display:none' : '' }}">
                                <label class="form-label">Negative Mark Value</label>
                                <input type="number" class="form-control" name="negative_mark_value" 
                                       value="{{ $exam->negative_mark_value }}" step="0.25" min="0">
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="form-label">General Instructions</label>
                                <textarea class="form-control" name="general_instructions" rows="4">{{ $exam->general_instructions }}</textarea>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="form-label">Exam Rules</label>
                                <textarea class="form-control" name="exam_rules" rows="4">{{ $exam->exam_rules }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-secondary me-2" onclick="resetForm()">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Structure Tab --}}
        <div class="tab-pane fade" id="structure" role="tabpanel">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Exam Structure</h5>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSectionModal">
                        <i class="fas fa-plus"></i> Add Section
                    </button>
                </div>
                <div class="card-body">
                    @if($exam->sections && count($exam->sections) > 0)
                    <div class="accordion" id="sectionsAccordion">
                        @foreach($exam->sections as $index => $section)
                        <div class="accordion-item section-card mb-3">
                            <h2 class="accordion-header" id="heading{{ $index }}">
                                <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" type="button" 
                                        data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}">
                                    <div class="d-flex justify-content-between w-100 me-3">
                                        <span>
                                            <i class="fas fa-layer-group me-2"></i>
                                            <strong>{{ $section['name'] }}</strong>
                                        </span>
                                        <span>
                                            <span class="badge bg-info">{{ $section['questions'] }} Questions</span>
                                            <span class="badge bg-success">{{ $section['marks'] }} Marks</span>
                                            <span class="badge bg-secondary">{{ $section['duration'] }} Minutes</span>
                                        </span>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse{{ $index }}" class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}"
                                 data-bs-parent="#sectionsAccordion">
                                <div class="accordion-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <strong>Questions:</strong> {{ $section['questions'] }}
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Marks:</strong> {{ $section['marks'] }}
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Duration:</strong> {{ $section['duration'] }} minutes
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Mandatory:</strong> 
                                            <span class="badge bg-{{ $section['is_mandatory'] ? 'success' : 'warning' }}">
                                                {{ $section['is_mandatory'] ? 'Yes' : 'No' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="editSection({{ $index }})">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteSection({{ $index }})">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-layer-group fa-4x text-muted mb-3"></i>
                        <p class="text-muted">No sections defined yet</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSectionModal">
                            <i class="fas fa-plus"></i> Add First Section
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Questions Tab --}}
        <div class="tab-pane fade" id="questions" role="tabpanel">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Question Management</h5>
                    <div>
                        <button type="button" class="btn btn-outline-primary btn-sm me-2" onclick="importQuestions()">
                            <i class="fas fa-upload"></i> Import
                        </button>
                        <a href="{{ route('exams.admin.questions.create', $exam->id) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add Question
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="50">
                                        <input type="checkbox" class="form-check-input" id="selectAll">
                                    </th>
                                    <th>Question Code</th>
                                    <th>Question</th>
                                    <th>Type</th>
                                    <th>Section</th>
                                    <th>Difficulty</th>
                                    <th>Marks</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($exam->questions as $question)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input question-check" value="{{ $question->id }}">
                                    </td>
                                    <td>{{ $question->question_code }}</td>
                                    <td>{{ Str::limit(strip_tags($question->question_text), 50) }}</td>
                                    <td>
                                        <span class="question-type-badge bg-light text-dark">
                                            {{ ucfirst(str_replace('_', ' ', $question->question_type)) }}
                                        </span>
                                    </td>
                                    <td>{{ $question->subject }}</td>
                                    <td>
                                        @php
                                            $difficultyColors = [
                                                'easy' => 'success',
                                                'medium' => 'warning',
                                                'hard' => 'danger',
                                                'expert' => 'dark'
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $difficultyColors[$question->difficulty_level] ?? 'secondary' }}">
                                            {{ ucfirst($question->difficulty_level) }}
                                        </span>
                                    </td>
                                    <td>{{ $question->marks }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('exams.admin.questions.edit', [$exam->id, $question->id]) }}" 
                                               class="btn btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger" 
                                                    onclick="deleteQuestion({{ $question->id }})" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No questions added yet</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Registrations Tab --}}
        <div class="tab-pane fade" id="registrations" role="tabpanel">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0">Exam Registrations</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" class="form-control" placeholder="Search by name, email, or registration number..." id="searchRegistrations">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="filterStatus">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-primary w-100" onclick="exportRegistrations()">
                                <i class="fas fa-download"></i> Export List
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Registration #</th>
                                    <th>Candidate Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Fee Status</th>
                                    <th>Hall Ticket</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="registrationsTable">
                                @forelse($exam->registrations as $registration)
                                <tr>
                                    <td>{{ $registration->registration_number }}</td>
                                    <td>{{ $registration->candidate_name ?? $registration->application->first_name . ' ' . $registration->application->last_name }}</td>
                                    <td>{{ $registration->candidate_email ?? $registration->application->email }}</td>
                                    <td>{{ $registration->candidate_phone ?? $registration->application->phone_primary }}</td>
                                    <td>
                                        <span class="badge bg-{{ $registration->registration_status == 'confirmed' ? 'success' : 'warning' }}">
                                            {{ ucfirst($registration->registration_status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($registration->fee_paid)
                                            <span class="badge bg-success">Paid</span>
                                        @else
                                            <span class="badge bg-danger">Unpaid</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($registration->hall_ticket_number)
                                            <a href="{{ route('exams.admin.hall-ticket.download', $registration->id) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                    onclick="generateHallTicket({{ $registration->id }})">
                                                Generate
                                            </button>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('exams.admin.registrations.show', $registration->id) }}" 
                                               class="btn btn-outline-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <p class="text-muted">No registrations yet</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sessions Tab --}}
        <div class="tab-pane fade" id="sessions" role="tabpanel">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Exam Sessions</h5>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSessionModal">
                        <i class="fas fa-plus"></i> Add Session
                    </button>
                </div>
                <div class="card-body">
                    @if($exam->sessions && $exam->sessions->count() > 0)
                    <div class="row">
                        @foreach($exam->sessions as $session)
                        <div class="col-md-6 mb-3">
                            <div class="card border">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $session->session_code }}</h6>
                                    <p class="card-text">
                                        <i class="fas fa-calendar me-2"></i>{{ \Carbon\Carbon::parse($session->session_date)->format('M d, Y') }}<br>
                                        <i class="fas fa-clock me-2"></i>{{ $session->start_time }} - {{ $session->end_time }}<br>
                                        <i class="fas fa-building me-2"></i>{{ $session->center->center_name }}<br>
                                        <i class="fas fa-users me-2"></i>{{ $session->registered_count }}/{{ $session->capacity }}
                                    </p>
                                    <div class="progress mb-2" style="height: 20px;">
                                        @php
                                            $percentage = ($session->registered_count / $session->capacity) * 100;
                                        @endphp
                                        <div class="progress-bar" role="progressbar" style="width: {{ $percentage }}%">
                                            {{ round($percentage) }}%
                                        </div>
                                    </div>
                                    <a href="{{ route('exams.admin.sessions.manage', $session->id) }}" class="btn btn-sm btn-primary">
                                        Manage Session
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                        <p class="text-muted">No sessions scheduled yet</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Settings Tab --}}
        <div class="tab-pane fade" id="settings" role="tabpanel">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0">Exam Settings</h5>
                </div>
                <div class="card-body">
                    <form id="examSettingsForm" method="POST" action="{{ route('exams.admin.settings.update', $exam->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <h6 class="mb-3">Registration Settings</h6>
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Registration Start Date</label>
                                <input type="date" class="form-control" name="registration_start_date" 
                                       value="{{ $exam->registration_start_date }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Registration End Date</label>
                                <input type="date" class="form-control" name="registration_end_date" 
                                       value="{{ $exam->registration_end_date }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Max Registrations</label>
                                <input type="number" class="form-control" name="max_registrations" 
                                       value="{{ $exam->max_registrations }}" min="1">
                            </div>
                        </div>

                        <h6 class="mb-3">Result Settings</h6>
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Result Publish Date</label>
                                <input type="date" class="form-control" name="result_publish_date" 
                                       value="{{ $exam->result_publish_date }}">
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" name="show_detailed_results" 
                                           id="showDetailedResults" {{ $exam->show_detailed_results ? 'checked' : '' }}>
                                    <label class="form-check-label" for="showDetailedResults">
                                        Show Detailed Results
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" name="allow_result_review" 
                                           id="allowResultReview" {{ $exam->allow_result_review ? 'checked' : '' }}>
                                    <label class="form-check-label" for="allowResultReview">
                                        Allow Result Review
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add Section Modal --}}
<div class="modal fade" id="addSectionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Exam Section</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addSectionForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Section Name</label>
                        <input type="text" class="form-control" name="section_name" required>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">Questions</label>
                            <input type="number" class="form-control" name="questions" min="1" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Marks</label>
                            <input type="number" class="form-control" name="marks" min="1" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Duration (min)</label>
                            <input type="number" class="form-control" name="duration" min="1" required>
                        </div>
                    </div>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" name="is_mandatory" id="isMandatory" checked>
                        <label class="form-check-label" for="isMandatory">
                            Mandatory Section
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Section</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add Session Modal --}}
<div class="modal fade" id="addSessionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Schedule Exam Session</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addSessionForm">
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Session Date</label>
                            <input type="date" class="form-control" name="session_date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Session Type</label>
                            <select class="form-select" name="session_type" required>
                                <option value="morning">Morning</option>
                                <option value="afternoon">Afternoon</option>
                                <option value="evening">Evening</option>
                                <option value="full_day">Full Day</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Start Time</label>
                            <input type="time" class="form-control" name="start_time" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">End Time</label>
                            <input type="time" class="form-control" name="end_time" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Exam Center</label>
                            <select class="form-select" name="center_id" required>
                                <option value="">Select Center</option>
                                @foreach($centers ?? [] as $center)
                                <option value="{{ $center->id }}">{{ $center->center_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Capacity</label>
                            <input type="number" class="form-control" name="capacity" min="1" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Special Instructions</label>
                        <textarea class="form-control" name="special_instructions" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Schedule Session</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    // Initialize date pickers
    flatpickr("input[type=date]", {
        dateFormat: "Y-m-d",
    });

    // Negative marking toggle
    document.getElementById('negativeMarking')?.addEventListener('change', function() {
        document.getElementById('negativeMarkValueDiv').style.display = this.checked ? 'block' : 'none';
    });

    // Toggle exam status
    function toggleExamStatus() {
        if (confirm('Are you sure you want to change the exam status?')) {
            // Implementation
        }
    }

    // Reset form
    function resetForm() {
        document.getElementById('examDetailsForm').reset();
    }

    // Question management
    function importQuestions() {
        // Implementation
    }

    function deleteQuestion(id) {
        if (confirm('Delete this question?')) {
            // Implementation
        }
    }

    // Section management
    function editSection(index) {
        // Implementation
    }

    function deleteSection(index) {
        if (confirm('Delete this section?')) {
            // Implementation
        }
    }

    // Registration management
    function exportRegistrations() {
        // Implementation
    }

    function generateHallTicket(id) {
        // Implementation
    }

    // Form submissions
    document.getElementById('addSectionForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        // Implementation
    });

    document.getElementById('addSessionForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        // Implementation
    });
</script>
@endsection