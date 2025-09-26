{{-- File: resources/views/exams/admin/result-processing.blade.php --}}
@extends('layouts.app')

@section('title', 'Result Processing - ' . $exam->exam_name)

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.css">
<style>
    .result-card {
        border-left: 4px solid #007bff;
        transition: all 0.3s;
    }
    .result-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .score-badge {
        font-size: 1.5rem;
        font-weight: bold;
        padding: 10px 20px;
        border-radius: 10px;
    }
    .score-badge.pass {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        color: white;
    }
    .score-badge.fail {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
    }
    
    .evaluation-progress {
        position: relative;
        height: 30px;
        background: #f8f9fa;
        border-radius: 15px;
        overflow: hidden;
    }
    .evaluation-progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        transition: width 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
    }
    
    .answer-sheet-preview {
        border: 2px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        background: #fafafa;
        max-height: 400px;
        overflow-y: auto;
    }
    
    .question-response {
        padding: 10px;
        margin-bottom: 10px;
        border-radius: 5px;
        background: white;
        border: 1px solid #dee2e6;
    }
    .question-response.correct {
        border-left: 4px solid #28a745;
        background: #f0fff4;
    }
    .question-response.wrong {
        border-left: 4px solid #dc3545;
        background: #fff5f5;
    }
    .question-response.pending {
        border-left: 4px solid #ffc107;
        background: #fffdf0;
    }
    
    .rank-badge {
        display: inline-block;
        padding: 5px 15px;
        border-radius: 20px;
        font-weight: bold;
    }
    .rank-badge.gold {
        background: linear-gradient(135deg, #f9d423 0%, #ff4e50 100%);
        color: white;
    }
    .rank-badge.silver {
        background: linear-gradient(135deg, #c0c0c0 0%, #808080 100%);
        color: white;
    }
    .rank-badge.bronze {
        background: linear-gradient(135deg, #cd7f32 0%, #8b4513 100%);
        color: white;
    }
    
    .stats-widget {
        text-align: center;
        padding: 20px;
        border-radius: 10px;
        background: white;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        transition: all 0.3s;
    }
    .stats-widget:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    .stats-widget .value {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 5px;
    }
    .stats-widget .label {
        color: #6c757d;
        font-size: 0.9rem;
    }
    
    .distribution-chart {
        height: 300px;
        position: relative;
    }
    
    .bulk-action-toolbar {
        position: sticky;
        top: 0;
        z-index: 100;
        background: white;
        padding: 15px;
        border-bottom: 2px solid #dee2e6;
        display: none;
    }
    .bulk-action-toolbar.show {
        display: block;
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
                        <i class="fas fa-chart-line me-2"></i>Result Processing
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('exams.admin.dashboard') }}">Exam Dashboard</a></li>
                            <li class="breadcrumb-item active">{{ $exam->exam_name }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="btn-toolbar">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-outline-primary" onclick="autoEvaluate()">
                            <i class="fas fa-robot"></i> Auto-Evaluate
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="generateRankList()">
                            <i class="fas fa-trophy"></i> Generate Ranks
                        </button>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="publishResults()">
                        <i class="fas fa-broadcast-tower"></i> Publish Results
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Processing Status Overview --}}
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-widget">
                <div class="value text-primary">{{ number_format($stats['total_candidates'] ?? 0) }}</div>
                <div class="label">Total Candidates</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-widget">
                <div class="value text-success">{{ number_format($stats['evaluated'] ?? 0) }}</div>
                <div class="label">Evaluated</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-widget">
                <div class="value text-warning">{{ number_format($stats['pending'] ?? 0) }}</div>
                <div class="label">Pending Evaluation</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-widget">
                <div class="value text-info">{{ number_format($stats['published'] ?? 0) }}</div>
                <div class="label">Results Published</div>
            </div>
        </div>
    </div>

    {{-- Overall Progress --}}
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5 class="mb-0">Overall Evaluation Progress</h5>
        </div>
        <div class="card-body">
            <div class="evaluation-progress">
                @php
                    $progress = $stats['total_candidates'] > 0 
                        ? ($stats['evaluated'] / $stats['total_candidates']) * 100 
                        : 0;
                @endphp
                <div class="evaluation-progress-bar" style="width: {{ $progress }}%">
                    {{ number_format($progress, 1) }}%
                </div>
            </div>
            <div class="mt-3 d-flex justify-content-between text-muted">
                <span>Started: {{ $exam->exam_date ? \Carbon\Carbon::parse($exam->exam_date)->format('M d, Y') : 'N/A' }}</span>
                <span>Est. Completion: {{ $stats['estimated_completion'] ?? 'Calculating...' }}</span>
            </div>
        </div>
    </div>

    {{-- Main Content Tabs --}}
    <div class="card shadow">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#evaluation">
                        <i class="fas fa-check-double"></i> Evaluation
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#results">
                        <i class="fas fa-poll"></i> Results
                        @if($stats['pending'] > 0)
                        <span class="badge bg-warning">{{ $stats['pending'] }}</span>
                        @endif
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#analytics">
                        <i class="fas fa-chart-bar"></i> Analytics
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#ranking">
                        <i class="fas fa-trophy"></i> Ranking
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#answer-key">
                        <i class="fas fa-key"></i> Answer Key
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#publish">
                        <i class="fas fa-upload"></i> Publish
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                {{-- Evaluation Tab --}}
                <div class="tab-pane fade show active" id="evaluation">
                    {{-- Bulk Action Toolbar --}}
                    <div class="bulk-action-toolbar" id="bulkActionToolbar">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <span id="selectedCount">0</span> responses selected
                            </div>
                            <div class="col-md-6 text-end">
                                <button type="button" class="btn btn-sm btn-primary" onclick="bulkEvaluate()">
                                    <i class="fas fa-check"></i> Evaluate Selected
                                </button>
                                <button type="button" class="btn btn-sm btn-warning" onclick="bulkReview()">
                                    <i class="fas fa-eye"></i> Mark for Review
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Filters --}}
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select class="form-select" id="filterStatus">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="evaluated">Evaluated</option>
                                <option value="reviewing">Under Review</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="filterSession">
                                <option value="">All Sessions</option>
                                @foreach($sessions as $session)
                                <option value="{{ $session->id }}">{{ $session->session_code }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="filterQuestionType">
                                <option value="">All Question Types</option>
                                <option value="objective">Objective Only</option>
                                <option value="subjective">Subjective Only</option>
                                <option value="mixed">Mixed</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" placeholder="Search by registration/name..." id="searchResponse">
                        </div>
                    </div>

                    {{-- Response List --}}
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="30">
                                        <input type="checkbox" class="form-check-input" id="selectAll">
                                    </th>
                                    <th>Registration #</th>
                                    <th>Candidate</th>
                                    <th>Session</th>
                                    <th>Questions</th>
                                    <th>Objective Score</th>
                                    <th>Subjective Score</th>
                                    <th>Total Score</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($responses as $response)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input response-check" value="{{ $response->id }}">
                                    </td>
                                    <td>{{ $response->registration->registration_number }}</td>
                                    <td>
                                        <strong>{{ $response->registration->candidate_name }}</strong>
                                        <br><small class="text-muted">{{ $response->registration->candidate_email }}</small>
                                    </td>
                                    <td>{{ $response->session->session_code }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $response->total_questions_attempted }}/{{ $exam->total_questions }}</span>
                                    </td>
                                    <td>
                                        @if($response->objective_evaluated)
                                            <span class="badge bg-success">{{ $response->objective_score ?? 0 }}</span>
                                        @else
                                            <span class="badge bg-warning">Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($response->subjective_evaluated)
                                            <span class="badge bg-success">{{ $response->subjective_score ?? 0 }}</span>
                                        @else
                                            <span class="badge bg-warning">Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($response->total_score !== null)
                                            <strong>{{ $response->total_score }}/{{ $exam->total_marks }}</strong>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($response->status == 'evaluated')
                                            <span class="badge bg-success">Evaluated</span>
                                        @elseif($response->status == 'reviewing')
                                            <span class="badge bg-info">Under Review</span>
                                        @else
                                            <span class="badge bg-warning">Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary" 
                                                    onclick="evaluateResponse({{ $response->id }})">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-info" 
                                                    onclick="viewResponse({{ $response->id }})">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Results Tab --}}
                <div class="tab-pane fade" id="results">
                    <div class="row mb-3">
                        <div class="col-md-9">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-primary active" data-view="all">All</button>
                                <button type="button" class="btn btn-outline-success" data-view="pass">Pass</button>
                                <button type="button" class="btn btn-outline-danger" data-view="fail">Fail</button>
                                <button type="button" class="btn btn-outline-warning" data-view="borderline">Borderline</button>
                            </div>
                        </div>
                        <div class="col-md-3 text-end">
                            <button type="button" class="btn btn-primary" onclick="exportResults()">
                                <i class="fas fa-download"></i> Export Results
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Registration #</th>
                                    <th>Candidate Name</th>
                                    <th>Score</th>
                                    <th>Percentage</th>
                                    <th>Percentile</th>
                                    <th>Result</th>
                                    <th>Certificate</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($results as $result)
                                <tr>
                                    <td>
                                        @if($result->overall_rank <= 3)
                                            <span class="rank-badge {{ $result->overall_rank == 1 ? 'gold' : ($result->overall_rank == 2 ? 'silver' : 'bronze') }}">
                                                #{{ $result->overall_rank }}
                                            </span>
                                        @else
                                            #{{ $result->overall_rank }}
                                        @endif
                                    </td>
                                    <td>{{ $result->registration->registration_number }}</td>
                                    <td>
                                        <strong>{{ $result->registration->candidate_name }}</strong>
                                    </td>
                                    <td>
                                        <span class="score-badge {{ $result->result_status == 'pass' ? 'pass' : 'fail' }}">
                                            {{ $result->final_score }}/{{ $exam->total_marks }}
                                        </span>
                                    </td>
                                    <td>{{ number_format($result->percentage, 2) }}%</td>
                                    <td>{{ number_format($result->percentile, 2) }}</td>
                                    <td>
                                        @if($result->result_status == 'pass')
                                            <span class="badge bg-success">PASS</span>
                                        @elseif($result->result_status == 'fail')
                                            <span class="badge bg-danger">FAIL</span>
                                        @else
                                            <span class="badge bg-secondary">{{ strtoupper($result->result_status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($result->certificate_generated)
                                            <a href="{{ route('exams.admin.certificate', $result->id) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-certificate"></i>
                                            </a>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                    onclick="generateCertificate({{ $result->id }})">
                                                Generate
                                            </button>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-info" 
                                                    onclick="viewResult({{ $result->id }})">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-primary" 
                                                    onclick="sendResult({{ $result->id }})">
                                                <i class="fas fa-envelope"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Analytics Tab --}}
                <div class="tab-pane fade" id="analytics">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Score Distribution</h6>
                                </div>
                                <div class="card-body">
                                    <div class="distribution-chart">
                                        <canvas id="scoreDistributionChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Pass/Fail Ratio</h6>
                                </div>
                                <div class="card-body">
                                    <div class="distribution-chart">
                                        <canvas id="passFailChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Question-wise Performance</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Question #</th>
                                                    <th>Type</th>
                                                    <th>Difficulty</th>
                                                    <th>Attempts</th>
                                                    <th>Correct</th>
                                                    <th>Success Rate</th>
                                                    <th>Avg. Time</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($questionAnalytics as $qa)
                                                <tr>
                                                    <td>Q{{ $qa->question_number }}</td>
                                                    <td>{{ ucfirst($qa->question_type) }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $qa->difficulty == 'easy' ? 'success' : ($qa->difficulty == 'medium' ? 'warning' : 'danger') }}">
                                                            {{ ucfirst($qa->difficulty) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $qa->attempts }}</td>
                                                    <td>{{ $qa->correct_answers }}</td>
                                                    <td>
                                                        <div class="progress" style="height: 20px;">
                                                            <div class="progress-bar" style="width: {{ $qa->success_rate }}%">
                                                                {{ number_format($qa->success_rate, 1) }}%
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>{{ gmdate('i:s', $qa->avg_time_seconds) }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h6>Average Score</h6>
                                    <h2 class="text-primary">{{ number_format($analytics['avg_score'], 2) }}</h2>
                                    <small class="text-muted">Out of {{ $exam->total_marks }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h6>Highest Score</h6>
                                    <h2 class="text-success">{{ $analytics['highest_score'] }}</h2>
                                    <small class="text-muted">{{ $analytics['topper_name'] }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h6>Pass Percentage</h6>
                                    <h2 class="text-info">{{ number_format($analytics['pass_percentage'], 1) }}%</h2>
                                    <small class="text-muted">{{ $analytics['passed'] }}/{{ $analytics['total'] }} passed</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Ranking Tab --}}
                <div class="tab-pane fade" id="ranking">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6>Merit List</h6>
                        </div>
                        <div class="col-md-6 text-end">
                            <button type="button" class="btn btn-primary" onclick="generateMeritList()">
                                <i class="fas fa-list-ol"></i> Generate Merit List
                            </button>
                        </div>
                    </div>

                    {{-- Top Performers --}}
                    <div class="row mb-4">
                        @foreach($topPerformers as $index => $performer)
                        <div class="col-md-4 mb-3">
                            <div class="card result-card">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        @if($index == 0)
                                            <i class="fas fa-trophy fa-3x text-warning"></i>
                                        @elseif($index == 1)
                                            <i class="fas fa-medal fa-3x text-secondary"></i>
                                        @else
                                            <i class="fas fa-award fa-3x" style="color: #cd7f32;"></i>
                                        @endif
                                    </div>
                                    <h5>{{ $performer->registration->candidate_name }}</h5>
                                    <p class="text-muted">{{ $performer->registration->registration_number }}</p>
                                    <h3 class="text-primary">{{ $performer->final_score }}/{{ $exam->total_marks }}</h3>
                                    <p class="mb-0">
                                        <span class="badge bg-success">{{ number_format($performer->percentage, 2) }}%</span>
                                        <span class="badge bg-info">Rank #{{ $performer->overall_rank }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Category-wise Ranking --}}
                    <div class="card">
                        <div class="card-header">
                            <ul class="nav nav-tabs card-header-tabs">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#overall-rank">Overall</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#category-rank">Category</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#center-rank">Center-wise</a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="overall-rank">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Rank</th>
                                                    <th>Registration #</th>
                                                    <th>Name</th>
                                                    <th>Score</th>
                                                    <th>Percentage</th>
                                                    <th>Percentile</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($rankedResults as $result)
                                                <tr>
                                                    <td>{{ $result->overall_rank }}</td>
                                                    <td>{{ $result->registration->registration_number }}</td>
                                                    <td>{{ $result->registration->candidate_name }}</td>
                                                    <td>{{ $result->final_score }}</td>
                                                    <td>{{ number_format($result->percentage, 2) }}%</td>
                                                    <td>{{ number_format($result->percentile, 2) }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Answer Key Tab --}}
                <div class="tab-pane fade" id="answer-key">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <h6>Official Answer Key</h6>
                        </div>
                        <div class="col-md-4 text-end">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadAnswerKeyModal">
                                <i class="fas fa-upload"></i> Upload Answer Key
                            </button>
                            <button type="button" class="btn btn-outline-primary" onclick="publishAnswerKey()">
                                <i class="fas fa-share"></i> Publish Key
                            </button>
                        </div>
                    </div>

                    @if($answerKey)
                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Key Type:</strong> 
                                    <span class="badge bg-{{ $answerKey->key_type == 'final' ? 'success' : 'warning' }}">
                                        {{ ucfirst($answerKey->key_type) }}
                                    </span>
                                </div>
                                <div class="col-md-6 text-end">
                                    <strong>Published:</strong> 
                                    {{ $answerKey->is_published ? 'Yes' : 'No' }}
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th width="10%">Q.No</th>
                                            <th width="60%">Question</th>
                                            <th width="15%">Correct Answer</th>
                                            <th width="15%">Challenges</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($answerKey->answers as $qNo => $answer)
                                        <tr>
                                            <td>{{ $qNo }}</td>
                                            <td>{{ Str::limit($questions[$qNo]->question_text ?? '', 100) }}</td>
                                            <td>
                                                <span class="badge bg-success">{{ $answer }}</span>
                                            </td>
                                            <td>
                                                @if($challenges[$qNo] ?? 0 > 0)
                                                <span class="badge bg-warning">{{ $challenges[$qNo] }} challenges</span>
                                                @else
                                                -
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No answer key uploaded yet.
                    </div>
                    @endif
                </div>

                {{-- Publish Tab --}}
                <div class="tab-pane fade" id="publish">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="mb-4">Publish Results</h5>
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="publishResults" {{ $exam->results_published ? 'checked' : '' }}>
                                        <label class="form-check-label" for="publishResults">
                                            Publish Results to Candidates
                                        </label>
                                    </div>
                                    
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="showDetailedResults" {{ $exam->show_detailed_results ? 'checked' : '' }}>
                                        <label class="form-check-label" for="showDetailedResults">
                                            Show Detailed Results (Question-wise scores)
                                        </label>
                                    </div>
                                    
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="allowResultReview" {{ $exam->allow_result_review ? 'checked' : '' }}>
                                        <label class="form-check-label" for="allowResultReview">
                                            Allow Result Review/Re-evaluation Requests
                                        </label>
                                    </div>
                                    
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="sendNotifications">
                                        <label class="form-check-label" for="sendNotifications">
                                            Send Email/SMS Notifications
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Result Publish Date</label>
                                        <input type="datetime-local" class="form-control" id="publishDate" 
                                               value="{{ $exam->result_publish_date ? \Carbon\Carbon::parse($exam->result_publish_date)->format('Y-m-d\TH:i') : '' }}">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Review Period (Days)</label>
                                        <input type="number" class="form-control" id="reviewPeriod" 
                                               value="{{ $exam->review_period_days ?? 7 }}" min="0">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> 
                                <strong>Important:</strong> Once results are published, they cannot be unpublished. 
                                Make sure all evaluations are complete and verified.
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <h6 class="mb-3">Pre-publish Checklist</h6>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="check1">
                                        <label class="form-check-label" for="check1">
                                            All responses have been evaluated
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="check2">
                                        <label class="form-check-label" for="check2">
                                            Answer key has been finalized
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="check3">
                                        <label class="form-check-label" for="check3">
                                            Ranking has been generated
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="check4">
                                        <label class="form-check-label" for="check4">
                                            Results have been reviewed and approved
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-center mt-4">
                                <button type="button" class="btn btn-lg btn-success" onclick="finalPublish()">
                                    <i class="fas fa-broadcast-tower"></i> Publish Results Now
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Evaluate Response Modal --}}
<div class="modal fade" id="evaluateModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Evaluate Response</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="answer-sheet-preview" id="answerSheetPreview">
                            <!-- Response details loaded via AJAX -->
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Evaluation Panel</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label>Candidate:</label>
                                    <p id="evalCandidateName" class="fw-bold"></p>
                                </div>
                                <div class="mb-3">
                                    <label>Registration #:</label>
                                    <p id="evalRegistrationNo"></p>
                                </div>
                                <hr>
                                <div class="mb-3">
                                    <label>Objective Score:</label>
                                    <input type="number" class="form-control" id="objectiveScore" readonly>
                                </div>
                                <div class="mb-3">
                                    <label>Subjective Score:</label>
                                    <input type="number" class="form-control" id="subjectiveScore">
                                </div>
                                <div class="mb-3">
                                    <label>Negative Marks:</label>
                                    <input type="number" class="form-control" id="negativeMarks" readonly>
                                </div>
                                <div class="mb-3">
                                    <label>Total Score:</label>
                                    <input type="number" class="form-control" id="totalScore" readonly>
                                </div>
                                <div class="mb-3">
                                    <label>Remarks:</label>
                                    <textarea class="form-control" id="evaluationRemarks" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="saveForReview()">Save for Review</button>
                <button type="button" class="btn btn-success" onclick="submitEvaluation()">Submit Evaluation</button>
            </div>
        </div>
    </div>
</div>

{{-- Upload Answer Key Modal --}}
<div class="modal fade" id="uploadAnswerKeyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Answer Key</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Key Type</label>
                    <select class="form-select" id="keyType">
                        <option value="provisional">Provisional</option>
                        <option value="final">Final</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Upload Method</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="uploadMethod" id="manual" checked>
                        <label class="form-check-label" for="manual">Manual Entry</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="uploadMethod" id="fileUpload">
                        <label class="form-check-label" for="fileUpload">File Upload (CSV/Excel)</label>
                    </div>
                </div>
                <div id="fileUploadSection" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label">Select File</label>
                        <input type="file" class="form-control" accept=".csv,.xlsx">
                    </div>
                    <a href="{{ route('exams.admin.answer-key.template') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-download"></i> Download Template
                    </a>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="uploadAnswerKey()">Upload</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Score Distribution Chart
    const scoreCtx = document.getElementById('scoreDistributionChart')?.getContext('2d');
    if (scoreCtx) {
        new Chart(scoreCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($scoreRanges ?? []) !!},
                datasets: [{
                    label: 'Number of Students',
                    data: {!! json_encode($scoreDistribution ?? []) !!},
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Pass/Fail Chart
    const passFailCtx = document.getElementById('passFailChart')?.getContext('2d');
    if (passFailCtx) {
        new Chart(passFailCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pass', 'Fail', 'Absent'],
                datasets: [{
                    data: [
                        {{ $analytics['passed'] ?? 0 }},
                        {{ $analytics['failed'] ?? 0 }},
                        {{ $analytics['absent'] ?? 0 }}
                    ],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.5)',
                        'rgba(255, 99, 132, 0.5)',
                        'rgba(201, 203, 207, 0.5)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    // Functions
    function autoEvaluate() {
        if (confirm('This will automatically evaluate all objective questions. Continue?')) {
            // Implementation
        }
    }

    function generateRankList() {
        // Implementation
    }

    function publishResults() {
        // Implementation
    }

    function evaluateResponse(id) {
        // Load response details via AJAX and show modal
        $('#evaluateModal').modal('show');
    }

    function viewResponse(id) {
        // Implementation
    }

    function exportResults() {
        window.location.href = `{{ route('exams.admin.results.export', $exam->id) }}`;
    }

    function finalPublish() {
        // Check all checkboxes are checked
        const allChecked = document.querySelectorAll('#check1, #check2, #check3, #check4')
            .length === document.querySelectorAll('#check1:checked, #check2:checked, #check3:checked, #check4:checked').length;
        
        if (!allChecked) {
            alert('Please complete all checklist items before publishing');
            return;
        }
        
        if (confirm('Are you sure you want to publish the results? This action cannot be undone.')) {
            // Implementation
        }
    }

    // Selection handling
    document.getElementById('selectAll')?.addEventListener('change', function() {
        document.querySelectorAll('.response-check').forEach(cb => {
            cb.checked = this.checked;
        });
        updateBulkToolbar();
    });

    function updateBulkToolbar() {
        const selected = document.querySelectorAll('.response-check:checked').length;
        const toolbar = document.getElementById('bulkActionToolbar');
        
        if (selected > 0) {
            toolbar.classList.add('show');
            document.getElementById('selectedCount').textContent = selected;
        } else {
            toolbar.classList.remove('show');
        }
    }
</script>
@endsection