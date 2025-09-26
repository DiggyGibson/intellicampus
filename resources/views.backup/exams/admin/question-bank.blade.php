{{-- File: resources/views/exams/admin/question-bank.blade.php --}}
@extends('layouts.app')

@section('title', 'Question Bank Management')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.13.11/dist/katex.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.62.0/codemirror.min.css">
<style>
    .question-card {
        border-left: 4px solid #007bff;
        transition: all 0.3s;
        cursor: pointer;
    }
    .question-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .question-card.selected {
        background: #f0f8ff;
        border-left-color: #28a745;
    }
    .difficulty-easy { border-left-color: #28a745 !important; }
    .difficulty-medium { border-left-color: #ffc107 !important; }
    .difficulty-hard { border-left-color: #dc3545 !important; }
    .difficulty-expert { border-left-color: #6f42c1 !important; }
    
    .question-type-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
    
    .filter-sidebar {
        position: sticky;
        top: 20px;
        max-height: calc(100vh - 100px);
        overflow-y: auto;
    }
    
    .tag-badge {
        display: inline-block;
        padding: 4px 8px;
        margin: 2px;
        border-radius: 12px;
        background: #e9ecef;
        font-size: 0.85rem;
        cursor: pointer;
    }
    .tag-badge:hover {
        background: #007bff;
        color: white;
    }
    
    .question-preview {
        max-height: 100px;
        overflow: hidden;
        position: relative;
    }
    .question-preview::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 30px;
        background: linear-gradient(transparent, white);
    }
    
    .bulk-action-bar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: #343a40;
        color: white;
        padding: 15px;
        transform: translateY(100%);
        transition: transform 0.3s;
        z-index: 1000;
    }
    .bulk-action-bar.show {
        transform: translateY(0);
    }
    
    .stats-card {
        text-align: center;
        padding: 20px;
        border-radius: 10px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .import-zone {
        border: 3px dashed #dee2e6;
        border-radius: 10px;
        padding: 40px;
        text-align: center;
        background: #fafafa;
        cursor: pointer;
        transition: all 0.3s;
    }
    .import-zone:hover {
        border-color: #007bff;
        background: #f0f8ff;
    }
    .import-zone.dragover {
        border-color: #28a745;
        background: #e8f5e9;
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
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-database me-2"></i>Question Bank
                    </h1>
                    <p class="text-muted mb-0">Manage and organize exam questions</p>
                </div>
                <div class="btn-toolbar">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="fas fa-upload"></i> Import
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="exportQuestions()">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addQuestionModal">
                        <i class="fas fa-plus"></i> Add Question
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stats-card border-0">
                <div class="card-body">
                    <h2 class="mb-1">{{ number_format($stats['total_questions'] ?? 0) }}</h2>
                    <p class="mb-0">Total Questions</p>
                    <small>{{ $stats['new_this_month'] ?? 0 }} added this month</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                <div class="card-body text-center">
                    <h2 class="mb-1">{{ $stats['subjects_count'] ?? 0 }}</h2>
                    <p class="mb-0">Subjects</p>
                    <small>{{ $stats['topics_count'] ?? 0 }} topics</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
                <div class="card-body text-center">
                    <h2 class="mb-1">{{ $stats['active_exams'] ?? 0 }}</h2>
                    <p class="mb-0">Active Exams</p>
                    <small>Using {{ $stats['questions_in_use'] ?? 0 }} questions</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white;">
                <div class="card-body text-center">
                    <h2 class="mb-1">{{ number_format($stats['avg_difficulty'] ?? 0, 1) }}</h2>
                    <p class="mb-0">Avg. Difficulty</p>
                    <small>Scale of 1-4</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Filter Sidebar --}}
        <div class="col-lg-3 mb-4">
            <div class="card shadow filter-sidebar">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-filter"></i> Filters</h5>
                </div>
                <div class="card-body">
                    {{-- Search --}}
                    <div class="mb-4">
                        <label class="form-label">Search</label>
                        <input type="text" class="form-control" id="questionSearch" placeholder="Search questions...">
                    </div>

                    {{-- Subject Filter --}}
                    <div class="mb-4">
                        <label class="form-label">Subject</label>
                        <select class="form-select" id="subjectFilter">
                            <option value="">All Subjects</option>
                            @foreach($subjects as $subject)
                            <option value="{{ $subject }}">{{ $subject }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Topic Filter --}}
                    <div class="mb-4">
                        <label class="form-label">Topic</label>
                        <select class="form-select" id="topicFilter">
                            <option value="">All Topics</option>
                        </select>
                    </div>

                    {{-- Question Type --}}
                    <div class="mb-4">
                        <label class="form-label">Question Type</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="multiple_choice" id="mcq">
                            <label class="form-check-label" for="mcq">Multiple Choice</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="true_false" id="tf">
                            <label class="form-check-label" for="tf">True/False</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="short_answer" id="sa">
                            <label class="form-check-label" for="sa">Short Answer</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="essay" id="essay">
                            <label class="form-check-label" for="essay">Essay</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="numerical" id="numerical">
                            <label class="form-check-label" for="numerical">Numerical</label>
                        </div>
                    </div>

                    {{-- Difficulty Level --}}
                    <div class="mb-4">
                        <label class="form-label">Difficulty Level</label>
                        <div class="btn-group-vertical w-100" role="group">
                            <input type="checkbox" class="btn-check" id="easy" value="easy">
                            <label class="btn btn-outline-success" for="easy">Easy</label>
                            
                            <input type="checkbox" class="btn-check" id="medium" value="medium">
                            <label class="btn btn-outline-warning" for="medium">Medium</label>
                            
                            <input type="checkbox" class="btn-check" id="hard" value="hard">
                            <label class="btn btn-outline-danger" for="hard">Hard</label>
                            
                            <input type="checkbox" class="btn-check" id="expert" value="expert">
                            <label class="btn btn-outline-dark" for="expert">Expert</label>
                        </div>
                    </div>

                    {{-- Marks Range --}}
                    <div class="mb-4">
                        <label class="form-label">Marks Range</label>
                        <div class="row">
                            <div class="col-6">
                                <input type="number" class="form-control form-control-sm" id="minMarks" placeholder="Min" min="0">
                            </div>
                            <div class="col-6">
                                <input type="number" class="form-control form-control-sm" id="maxMarks" placeholder="Max" min="0">
                            </div>
                        </div>
                    </div>

                    {{-- Status Filter --}}
                    <div class="mb-4">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>

                    {{-- Apply/Reset Buttons --}}
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary" onclick="applyFilters()">
                            <i class="fas fa-check"></i> Apply Filters
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="resetFilters()">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Questions List --}}
        <div class="col-lg-9">
            {{-- Toolbar --}}
            <div class="card shadow mb-3">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAllQuestions">
                                <label class="form-check-label" for="selectAllQuestions">
                                    Select All (<span id="selectedCount">0</span> selected)
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" id="sortBy">
                                <option value="newest">Newest First</option>
                                <option value="oldest">Oldest First</option>
                                <option value="most_used">Most Used</option>
                                <option value="least_used">Least Used</option>
                                <option value="difficulty_asc">Difficulty (Low to High)</option>
                                <option value="difficulty_desc">Difficulty (High to Low)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div class="btn-group float-end">
                                <button type="button" class="btn btn-outline-secondary" id="listView">
                                    <i class="fas fa-list"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary active" id="gridView">
                                    <i class="fas fa-th"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Questions Grid/List --}}
            <div id="questionsContainer">
                @forelse($questions as $question)
                <div class="card shadow-sm mb-3 question-card difficulty-{{ $question->difficulty_level }}" 
                     data-question-id="{{ $question->id }}">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-auto">
                                <input type="checkbox" class="form-check-input question-select" value="{{ $question->id }}">
                            </div>
                            <div class="col-auto">
                                @php
                                    $typeIcons = [
                                        'multiple_choice' => 'fa-list-ul',
                                        'true_false' => 'fa-check-circle',
                                        'short_answer' => 'fa-pen',
                                        'essay' => 'fa-align-left',
                                        'numerical' => 'fa-calculator',
                                        'matching' => 'fa-arrows-alt-h',
                                        'fill_blanks' => 'fa-text-width',
                                        'ordering' => 'fa-sort'
                                    ];
                                    $typeColors = [
                                        'multiple_choice' => 'bg-primary',
                                        'true_false' => 'bg-success',
                                        'short_answer' => 'bg-info',
                                        'essay' => 'bg-warning',
                                        'numerical' => 'bg-danger',
                                        'matching' => 'bg-secondary',
                                        'fill_blanks' => 'bg-dark',
                                        'ordering' => 'bg-purple'
                                    ];
                                @endphp
                                <div class="question-type-icon {{ $typeColors[$question->question_type] ?? 'bg-secondary' }} text-white">
                                    <i class="fas {{ $typeIcons[$question->question_type] ?? 'fa-question' }}"></i>
                                </div>
                            </div>
                            <div class="col">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1">
                                            <a href="{{ route('exams.admin.questions.show', $question->id) }}" class="text-decoration-none">
                                                {{ $question->question_code }}
                                            </a>
                                        </h6>
                                        <div class="mb-2">
                                            <span class="badge bg-light text-dark">{{ $question->subject }}</span>
                                            @if($question->topic)
                                            <span class="badge bg-light text-dark">{{ $question->topic }}</span>
                                            @endif
                                            <span class="badge bg-{{ $question->difficulty_level == 'easy' ? 'success' : ($question->difficulty_level == 'medium' ? 'warning' : ($question->difficulty_level == 'hard' ? 'danger' : 'dark')) }}">
                                                {{ ucfirst($question->difficulty_level) }}
                                            </span>
                                            <span class="badge bg-info">{{ $question->marks }} marks</span>
                                        </div>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('exams.admin.questions.show', $question->id) }}">
                                                    <i class="fas fa-eye me-2"></i>View
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('exams.admin.questions.edit', $question->id) }}">
                                                    <i class="fas fa-edit me-2"></i>Edit
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#" onclick="duplicateQuestion({{ $question->id }})">
                                                    <i class="fas fa-copy me-2"></i>Duplicate
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="#" onclick="deleteQuestion({{ $question->id }})">
                                                    <i class="fas fa-trash me-2"></i>Delete
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <div class="question-preview">
                                    {!! Str::limit(strip_tags($question->question_text), 200) !!}
                                </div>
                                
                                @if($question->question_type == 'multiple_choice' && $question->options)
                                <div class="mt-2">
                                    @foreach(array_slice((array)$question->options, 0, 2) as $key => $option)
                                    <div class="small text-muted">
                                        <span class="badge bg-light text-dark me-1">{{ strtoupper($key) }}</span>
                                        {{ Str::limit($option, 50) }}
                                    </div>
                                    @endforeach
                                    @if(count((array)$question->options) > 2)
                                    <small class="text-muted">... and {{ count((array)$question->options) - 2 }} more options</small>
                                    @endif
                                </div>
                                @endif

                                <div class="mt-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-muted">
                                            <i class="fas fa-history me-1"></i>Used {{ $question->times_used }} times
                                            @if($question->average_score)
                                            | <i class="fas fa-chart-line me-1"></i>Avg. Score: {{ number_format($question->average_score, 1) }}%
                                            @endif
                                        </small>
                                    </div>
                                    <div>
                                        @if($question->question_image)
                                        <span class="badge bg-light text-dark"><i class="fas fa-image"></i> Has Image</span>
                                        @endif
                                        @if($question->question_audio)
                                        <span class="badge bg-light text-dark"><i class="fas fa-volume-up"></i> Has Audio</span>
                                        @endif
                                        @if($question->question_video)
                                        <span class="badge bg-light text-dark"><i class="fas fa-video"></i> Has Video</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-question-circle fa-4x text-muted mb-3"></i>
                        <h5>No Questions Found</h5>
                        <p class="text-muted">Try adjusting your filters or add new questions to the bank.</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addQuestionModal">
                            <i class="fas fa-plus"></i> Add First Question
                        </button>
                    </div>
                </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if($questions->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $questions->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Bulk Action Bar --}}
<div class="bulk-action-bar" id="bulkActionBar">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-6">
                <span id="bulkSelectedCount">0</span> questions selected
            </div>
            <div class="col-md-6 text-end">
                <button type="button" class="btn btn-outline-light me-2" onclick="bulkAddToExam()">
                    <i class="fas fa-plus"></i> Add to Exam
                </button>
                <button type="button" class="btn btn-outline-light me-2" onclick="bulkExport()">
                    <i class="fas fa-download"></i> Export
                </button>
                <button type="button" class="btn btn-outline-light me-2" onclick="bulkChangeStatus()">
                    <i class="fas fa-toggle-on"></i> Change Status
                </button>
                <button type="button" class="btn btn-outline-danger" onclick="bulkDelete()">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Add Question Modal --}}
<div class="modal fade" id="addQuestionModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Question</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addQuestionForm" method="POST" action="{{ route('exams.admin.questions.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Question Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="question_type" id="questionType" required>
                                <option value="">Select Type</option>
                                <option value="multiple_choice">Multiple Choice</option>
                                <option value="multiple_answer">Multiple Answer</option>
                                <option value="true_false">True/False</option>
                                <option value="fill_blanks">Fill in the Blanks</option>
                                <option value="short_answer">Short Answer</option>
                                <option value="essay">Essay</option>
                                <option value="numerical">Numerical</option>
                                <option value="matching">Matching</option>
                                <option value="ordering">Ordering</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="subject" required list="subjectsList">
                            <datalist id="subjectsList">
                                @foreach($subjects as $subject)
                                <option value="{{ $subject }}">
                                @endforeach
                            </datalist>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Topic</label>
                            <input type="text" class="form-control" name="topic" list="topicsList">
                            <datalist id="topicsList"></datalist>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Difficulty <span class="text-danger">*</span></label>
                            <select class="form-select" name="difficulty_level" required>
                                <option value="easy">Easy</option>
                                <option value="medium" selected>Medium</option>
                                <option value="hard">Hard</option>
                                <option value="expert">Expert</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Marks <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="marks" min="1" value="1" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Negative Marks</label>
                            <input type="number" class="form-control" name="negative_marks" min="0" step="0.25" value="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Time Limit (sec)</label>
                            <input type="number" class="form-control" name="time_limit_seconds" min="0" placeholder="Optional">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Question Text <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="question_text" id="questionText" rows="4" required></textarea>
                        <small class="text-muted">You can use HTML tags and LaTeX for mathematical expressions (wrap in $$)</small>
                    </div>

                    {{-- Dynamic Options Section (shown for MCQ) --}}
                    <div id="optionsSection" style="display: none;">
                        <label class="form-label">Answer Options</label>
                        <div id="optionsContainer">
                            <div class="input-group mb-2">
                                <span class="input-group-text">A</span>
                                <input type="text" class="form-control" name="options[a]" placeholder="Option A">
                                <div class="input-group-text">
                                    <input class="form-check-input" type="checkbox" name="correct_answer[]" value="a">
                                </div>
                            </div>
                            <div class="input-group mb-2">
                                <span class="input-group-text">B</span>
                                <input type="text" class="form-control" name="options[b]" placeholder="Option B">
                                <div class="input-group-text">
                                    <input class="form-check-input" type="checkbox" name="correct_answer[]" value="b">
                                </div>
                            </div>
                            <div class="input-group mb-2">
                                <span class="input-group-text">C</span>
                                <input type="text" class="form-control" name="options[c]" placeholder="Option C">
                                <div class="input-group-text">
                                    <input class="form-check-input" type="checkbox" name="correct_answer[]" value="c">
                                </div>
                            </div>
                            <div class="input-group mb-2">
                                <span class="input-group-text">D</span>
                                <input type="text" class="form-control" name="options[d]" placeholder="Option D">
                                <div class="input-group-text">
                                    <input class="form-check-input" type="checkbox" name="correct_answer[]" value="d">
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addOption()">
                            <i class="fas fa-plus"></i> Add Option
                        </button>
                    </div>

                    {{-- Answer Section (for other types) --}}
                    <div id="answerSection" style="display: none;">
                        <label class="form-label">Correct Answer</label>
                        <textarea class="form-control" name="correct_answer_text" rows="2"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Answer Explanation</label>
                        <textarea class="form-control" name="answer_explanation" rows="3"></textarea>
                    </div>

                    {{-- Media Attachments --}}
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Question Image</label>
                            <input type="file" class="form-control" name="question_image" accept="image/*">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Question Audio</label>
                            <input type="file" class="form-control" name="question_audio" accept="audio/*">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Question Video</label>
                            <input type="file" class="form-control" name="question_video" accept="video/*">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Question
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Import Modal --}}
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Questions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="import-zone" id="importZone">
                    <i class="fas fa-cloud-upload-alt fa-4x text-muted mb-3"></i>
                    <h5>Drop files here or click to browse</h5>
                    <p class="text-muted">Supported formats: Excel (.xlsx), CSV, JSON</p>
                    <input type="file" id="importFile" class="d-none" accept=".xlsx,.csv,.json" multiple>
                </div>
                
                <div class="mt-3">
                    <a href="{{ route('exams.admin.questions.template') }}" class="btn btn-outline-primary">
                        <i class="fas fa-download"></i> Download Template
                    </a>
                </div>

                <div id="importPreview" class="mt-4" style="display: none;">
                    <h6>Import Preview</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Question</th>
                                    <th>Type</th>
                                    <th>Subject</th>
                                    <th>Difficulty</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="previewTable"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmImport" style="display: none;">
                    <i class="fas fa-upload"></i> Import Questions
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/katex@0.13.11/dist/katex.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/katex@0.13.11/dist/contrib/auto-render.min.js"></script>
<script>
    // Initialize KaTeX for math rendering
    renderMathInElement(document.body, {
        delimiters: [
            {left: '$$', right: '$$', display: true},
            {left: '$', right: '$', display: false}
        ]
    });

    // Question type change handler
    document.getElementById('questionType')?.addEventListener('change', function() {
        const type = this.value;
        const optionsSection = document.getElementById('optionsSection');
        const answerSection = document.getElementById('answerSection');
        
        if (type === 'multiple_choice' || type === 'multiple_answer') {
            optionsSection.style.display = 'block';
            answerSection.style.display = 'none';
        } else if (type === 'true_false') {
            optionsSection.style.display = 'none';
            answerSection.style.display = 'block';
        } else {
            optionsSection.style.display = 'none';
            answerSection.style.display = 'block';
        }
    });

    // Selection handling
    let selectedQuestions = new Set();
    
    document.querySelectorAll('.question-select').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                selectedQuestions.add(this.value);
            } else {
                selectedQuestions.delete(this.value);
            }
            updateBulkActionBar();
        });
    });

    function updateBulkActionBar() {
        const bar = document.getElementById('bulkActionBar');
        const count = selectedQuestions.size;
        
        if (count > 0) {
            bar.classList.add('show');
            document.getElementById('bulkSelectedCount').textContent = count;
            document.getElementById('selectedCount').textContent = count;
        } else {
            bar.classList.remove('show');
        }
    }

    // Import functionality
    const importZone = document.getElementById('importZone');
    const importFile = document.getElementById('importFile');
    
    importZone?.addEventListener('click', () => importFile.click());
    
    importZone?.addEventListener('dragover', (e) => {
        e.preventDefault();
        importZone.classList.add('dragover');
    });
    
    importZone?.addEventListener('dragleave', () => {
        importZone.classList.remove('dragover');
    });
    
    importZone?.addEventListener('drop', (e) => {
        e.preventDefault();
        importZone.classList.remove('dragover');
        handleFiles(e.dataTransfer.files);
    });
    
    importFile?.addEventListener('change', (e) => {
        handleFiles(e.target.files);
    });
    
    function handleFiles(files) {
        // Implementation for file handling and preview
        document.getElementById('importPreview').style.display = 'block';
        document.getElementById('confirmImport').style.display = 'inline-block';
    }

    // Filter functions
    function applyFilters() {
        // Implementation for applying filters
        const filters = {
            search: document.getElementById('questionSearch').value,
            subject: document.getElementById('subjectFilter').value,
            topic: document.getElementById('topicFilter').value,
            // ... collect other filters
        };
        
        // Make AJAX request with filters
        window.location.href = '{{ route("exams.admin.questions.index") }}?' + new URLSearchParams(filters);
    }

    function resetFilters() {
        document.getElementById('questionSearch').value = '';
        document.getElementById('subjectFilter').value = '';
        document.getElementById('topicFilter').value = '';
        // Reset other filters
        applyFilters();
    }

    // Bulk actions
    function bulkAddToExam() {
        // Implementation
    }

    function bulkExport() {
        if (selectedQuestions.size === 0) return;
        window.location.href = '{{ route("exams.admin.questions.export") }}?ids=' + Array.from(selectedQuestions).join(',');
    }

    function bulkDelete() {
        if (!confirm(`Delete ${selectedQuestions.size} questions?`)) return;
        // Implementation
    }

    // Individual actions
    function duplicateQuestion(id) {
        // Implementation
    }

    function deleteQuestion(id) {
        if (!confirm('Delete this question?')) return;
        // Implementation
    }
</script>
@endsection