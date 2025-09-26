{{-- resources/views/exams/public/sample-papers.blade.php --}}
@extends('layouts.app')

@section('title', 'Sample Papers - ' . ($exam->exam_name ?? 'Entrance Exam'))

@section('content')
<div class="container-fluid py-4">
    {{-- Header Section --}}
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exams.information') }}">Entrance Exams</a></li>
                    <li class="breadcrumb-item active">Sample Papers</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- Page Title --}}
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="h2 mb-3 text-primary">
                                <i class="fas fa-file-alt me-2"></i>
                                Sample Papers & Previous Year Questions
                            </h1>
                            <p class="text-muted mb-0">
                                Practice with authentic sample papers and previous year questions for 
                                <strong>{{ $exam->exam_name ?? 'Entrance Examination' }}</strong>
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <a href="{{ route('exams.syllabus', $exam->id ?? 1) }}" class="btn btn-outline-primary">
                                <i class="fas fa-book me-2"></i>View Syllabus
                            </a>
                            <a href="{{ route('exams.portal.register', $exam->id ?? 1) }}" class="btn btn-primary">
                                <i class="fas fa-user-plus me-2"></i>Register Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Section --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="GET" action="{{ route('exams.sample-papers', $exam->id ?? 1) }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Year</label>
                                <select name="year" class="form-select">
                                    <option value="">All Years</option>
                                    @for($year = date('Y'); $year >= date('Y') - 5; $year--)
                                        <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Subject</label>
                                <select name="subject" class="form-select">
                                    <option value="">All Subjects</option>
                                    <option value="mathematics" {{ request('subject') == 'mathematics' ? 'selected' : '' }}>Mathematics</option>
                                    <option value="physics" {{ request('subject') == 'physics' ? 'selected' : '' }}>Physics</option>
                                    <option value="chemistry" {{ request('subject') == 'chemistry' ? 'selected' : '' }}>Chemistry</option>
                                    <option value="english" {{ request('subject') == 'english' ? 'selected' : '' }}>English</option>
                                    <option value="full" {{ request('subject') == 'full' ? 'selected' : '' }}>Full Paper</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Type</label>
                                <select name="type" class="form-select">
                                    <option value="">All Types</option>
                                    <option value="sample" {{ request('type') == 'sample' ? 'selected' : '' }}>Sample Papers</option>
                                    <option value="previous" {{ request('type') == 'previous' ? 'selected' : '' }}>Previous Year</option>
                                    <option value="mock" {{ request('type') == 'mock' ? 'selected' : '' }}>Mock Tests</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary d-block w-100">
                                    <i class="fas fa-filter me-2"></i>Filter Papers
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Sample Papers Grid --}}
    <div class="row">
        {{-- Sample Paper 1 - Latest --}}
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-star me-2"></i>Latest Sample Paper 2025
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="badge bg-success">NEW</span>
                        <span class="badge bg-info">Full Length</span>
                        <span class="badge bg-warning text-dark">With Solutions</span>
                    </div>
                    <h6 class="card-title">Complete Mock Test - Pattern 2025</h6>
                    <ul class="list-unstyled text-muted small">
                        <li><i class="fas fa-check text-success me-2"></i>100 Questions</li>
                        <li><i class="fas fa-check text-success me-2"></i>360 Marks</li>
                        <li><i class="fas fa-check text-success me-2"></i>3 Hours Duration</li>
                        <li><i class="fas fa-check text-success me-2"></i>Detailed Solutions</li>
                    </ul>
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary" onclick="downloadPaper('sample_2025_full')">
                            <i class="fas fa-download me-2"></i>Download PDF
                        </button>
                        <button class="btn btn-outline-primary" onclick="attemptOnline('sample_2025_full')">
                            <i class="fas fa-desktop me-2"></i>Attempt Online
                        </button>
                    </div>
                </div>
                <div class="card-footer text-muted small">
                    <i class="fas fa-download me-1"></i>Downloaded 2,345 times
                </div>
            </div>
        </div>

        {{-- Previous Year Paper 2024 --}}
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>Previous Year 2024
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="badge bg-secondary">Actual Paper</span>
                        <span class="badge bg-info">With Solutions</span>
                    </div>
                    <h6 class="card-title">2024 Entrance Exam - Actual Paper</h6>
                    <ul class="list-unstyled text-muted small">
                        <li><i class="fas fa-check text-success me-2"></i>100 Questions</li>
                        <li><i class="fas fa-check text-success me-2"></i>360 Marks</li>
                        <li><i class="fas fa-check text-success me-2"></i>Answer Key Included</li>
                        <li><i class="fas fa-check text-success me-2"></i>Explanations Available</li>
                    </ul>
                    <div class="d-grid gap-2">
                        <button class="btn btn-success" onclick="downloadPaper('previous_2024')">
                            <i class="fas fa-download me-2"></i>Download PDF
                        </button>
                        <button class="btn btn-outline-success" onclick="viewSolutions('previous_2024')">
                            <i class="fas fa-eye me-2"></i>View Solutions
                        </button>
                    </div>
                </div>
                <div class="card-footer text-muted small">
                    <i class="fas fa-download me-1"></i>Downloaded 5,678 times
                </div>
            </div>
        </div>

        {{-- Mathematics Section Paper --}}
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calculator me-2"></i>Mathematics Section
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="badge bg-primary">Subject-wise</span>
                        <span class="badge bg-warning text-dark">Practice Set</span>
                    </div>
                    <h6 class="card-title">Mathematics - 50 Important Questions</h6>
                    <ul class="list-unstyled text-muted small">
                        <li><i class="fas fa-check text-success me-2"></i>50 Questions</li>
                        <li><i class="fas fa-check text-success me-2"></i>Topic-wise Coverage</li>
                        <li><i class="fas fa-check text-success me-2"></i>Difficulty: Medium-Hard</li>
                        <li><i class="fas fa-check text-success me-2"></i>Step-by-step Solutions</li>
                    </ul>
                    <div class="d-grid gap-2">
                        <button class="btn btn-info" onclick="downloadPaper('math_practice')">
                            <i class="fas fa-download me-2"></i>Download PDF
                        </button>
                    </div>
                </div>
                <div class="card-footer text-muted small">
                    <i class="fas fa-download me-1"></i>Downloaded 3,456 times
                </div>
            </div>
        </div>

        {{-- Physics Section Paper --}}
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-atom me-2"></i>Physics Section
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="badge bg-primary">Subject-wise</span>
                        <span class="badge bg-success">Updated</span>
                    </div>
                    <h6 class="card-title">Physics - Conceptual Questions</h6>
                    <ul class="list-unstyled text-muted small">
                        <li><i class="fas fa-check text-success me-2"></i>40 Questions</li>
                        <li><i class="fas fa-check text-success me-2"></i>All Topics Covered</li>
                        <li><i class="fas fa-check text-success me-2"></i>Numerical Problems</li>
                        <li><i class="fas fa-check text-success me-2"></i>Detailed Explanations</li>
                    </ul>
                    <div class="d-grid gap-2">
                        <button class="btn btn-warning" onclick="downloadPaper('physics_practice')">
                            <i class="fas fa-download me-2"></i>Download PDF
                        </button>
                    </div>
                </div>
                <div class="card-footer text-muted small">
                    <i class="fas fa-download me-1"></i>Downloaded 2,890 times
                </div>
            </div>
        </div>

        {{-- Chemistry Section Paper --}}
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-flask me-2"></i>Chemistry Section
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="badge bg-primary">Subject-wise</span>
                        <span class="badge bg-info">Revised</span>
                    </div>
                    <h6 class="card-title">Chemistry - Complete Practice Set</h6>
                    <ul class="list-unstyled text-muted small">
                        <li><i class="fas fa-check text-success me-2"></i>45 Questions</li>
                        <li><i class="fas fa-check text-success me-2"></i>Organic & Inorganic</li>
                        <li><i class="fas fa-check text-success me-2"></i>Previous Year Trends</li>
                        <li><i class="fas fa-check text-success me-2"></i>Quick Revision Notes</li>
                    </ul>
                    <div class="d-grid gap-2">
                        <button class="btn btn-danger" onclick="downloadPaper('chemistry_practice')">
                            <i class="fas fa-download me-2"></i>Download PDF
                        </button>
                    </div>
                </div>
                <div class="card-footer text-muted small">
                    <i class="fas fa-download me-1"></i>Downloaded 2,567 times
                </div>
            </div>
        </div>

        {{-- Mock Test Series --}}
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-purple text-white" style="background-color: #6f42c1;">
                    <h5 class="mb-0">
                        <i class="fas fa-tasks me-2"></i>Mock Test Series
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="badge bg-danger">Premium</span>
                        <span class="badge bg-success">5 Tests</span>
                    </div>
                    <h6 class="card-title">Complete Mock Test Package</h6>
                    <ul class="list-unstyled text-muted small">
                        <li><i class="fas fa-check text-success me-2"></i>5 Full-length Tests</li>
                        <li><i class="fas fa-check text-success me-2"></i>Increasing Difficulty</li>
                        <li><i class="fas fa-check text-success me-2"></i>Performance Analysis</li>
                        <li><i class="fas fa-check text-success me-2"></i>All India Ranking</li>
                    </ul>
                    <div class="d-grid gap-2">
                        <button class="btn text-white" style="background-color: #6f42c1;" onclick="accessMockTests()">
                            <i class="fas fa-lock-open me-2"></i>Access Mock Tests
                        </button>
                    </div>
                </div>
                <div class="card-footer text-muted small">
                    <i class="fas fa-users me-1"></i>1,234 students enrolled
                </div>
            </div>
        </div>
    </div>

    {{-- Previous Years Archive --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-archive me-2"></i>Previous Years Archive
                    </h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Year</th>
                                    <th>Paper Type</th>
                                    <th>Subjects</th>
                                    <th>Questions</th>
                                    <th>Duration</th>
                                    <th>Downloads</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for($year = 2023; $year >= 2019; $year--)
                                <tr>
                                    <td><strong>{{ $year }}</strong></td>
                                    <td>
                                        <span class="badge bg-secondary">Actual Paper</span>
                                    </td>
                                    <td>Math, Physics, Chemistry, English</td>
                                    <td>100</td>
                                    <td>3 Hours</td>
                                    <td>
                                        <small class="text-muted">
                                            <i class="fas fa-download me-1"></i>{{ rand(1000, 5000) }}
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button class="btn btn-outline-primary" onclick="downloadPaper('previous_{{ $year }}')">
                                                <i class="fas fa-download me-1"></i>Question Paper
                                            </button>
                                            <button class="btn btn-outline-success" onclick="downloadSolution('previous_{{ $year }}')">
                                                <i class="fas fa-key me-1"></i>Answer Key
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Instructions Section --}}
    <div class="row mt-4">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>How to Use Sample Papers Effectively
                    </h4>
                </div>
                <div class="card-body">
                    <ol>
                        <li class="mb-2">
                            <strong>Start with Topic-wise Papers:</strong> Begin your preparation with subject-specific papers to strengthen individual topics.
                        </li>
                        <li class="mb-2">
                            <strong>Time Yourself:</strong> Always attempt papers within the specified time limit to improve speed and accuracy.
                        </li>
                        <li class="mb-2">
                            <strong>Analyze Your Performance:</strong> After each test, carefully review incorrect answers and understand the solutions.
                        </li>
                        <li class="mb-2">
                            <strong>Track Progress:</strong> Maintain a record of scores to monitor improvement over time.
                        </li>
                        <li class="mb-2">
                            <strong>Simulate Exam Conditions:</strong> Attempt full-length papers in a quiet environment without distractions.
                        </li>
                        <li class="mb-2">
                            <strong>Focus on Weak Areas:</strong> Identify and work on topics where you consistently score low.
                        </li>
                    </ol>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-download me-2"></i>Download Statistics
                    </h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Total Downloads</span>
                            <strong>45,678</strong>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-success" style="width: 100%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>This Month</span>
                            <strong>3,456</strong>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-info" style="width: 75%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>This Week</span>
                            <strong>892</strong>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-warning" style="width: 45%"></div>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <p class="text-muted mb-2">Most Popular</p>
                        <h5 class="text-primary">Previous Year 2024</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function downloadPaper(paperId) {
    // In production, this would trigger actual download
    alert('Downloading ' + paperId + '.pdf...');
    // window.location.href = '/downloads/papers/' + paperId + '.pdf';
}

function downloadSolution(paperId) {
    alert('Downloading solutions for ' + paperId + '...');
}

function viewSolutions(paperId) {
    alert('Opening solutions viewer for ' + paperId + '...');
    // window.open('/solutions/' + paperId, '_blank');
}

function attemptOnline(paperId) {
    if(confirm('Do you want to attempt this paper online?')) {
        window.location.href = '{{ route("exams.portal.available") }}';
    }
}

function accessMockTests() {
    if(confirm('Mock tests require registration. Would you like to register?')) {
        window.location.href = '{{ route("exams.portal.available") }}';
    }
}
</script>
@endpush

@push('styles')
<style>
    .card {
        transition: transform 0.2s;
    }
    .card:hover {
        transform: translateY(-5px);
    }
    .bg-purple {
        background-color: #6f42c1 !important;
    }
</style>
@endpush
@endsection