{{-- resources/views/exams/public/results.blade.php --}}
@extends('layouts.app')

@section('title', 'Entrance Exam Results')

@section('content')
<div class="container-fluid py-4">
    {{-- Header Section --}}
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exams.information') }}">Entrance Exams</a></li>
                    <li class="breadcrumb-item active">Results</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- Page Title --}}
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card shadow-sm bg-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="h2 mb-3">
                                <i class="fas fa-trophy me-2"></i>
                                Entrance Examination Results
                            </h1>
                            <p class="mb-0 opacity-90">
                                Check your entrance examination results and download scorecards
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-light" onclick="showResultSearch()">
                                    <i class="fas fa-search me-2"></i>Check Result
                                </button>
                                <a href="{{ route('exams.statistics') }}" class="btn btn-outline-light">
                                    <i class="fas fa-chart-bar me-2"></i>Statistics
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Result Search Section --}}
    <div class="row mb-4">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h4 class="mb-0">
                        <i class="fas fa-search me-2"></i>Check Your Result
                    </h4>
                </div>
                <div class="card-body">
                    <form id="resultSearchForm" onsubmit="searchResult(event)">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="registrationNumber" class="form-label">Registration Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="registrationNumber" 
                                       placeholder="e.g., REG-2025-000001" required>
                                <small class="text-muted">Enter your exam registration number</small>
                            </div>
                            <div class="col-md-6">
                                <label for="dateOfBirth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="dateOfBirth" required>
                                <small class="text-muted">Enter your date of birth for verification</small>
                            </div>
                            <div class="col-12">
                                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-search me-2"></i>Search Result
                                    </button>
                                    <button type="reset" class="btn btn-outline-secondary btn-lg">
                                        <i class="fas fa-redo me-2"></i>Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Sample Result Display (Hidden by default) --}}
    <div class="row mb-4" id="resultDisplay" style="display: none;">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow-lg border-success">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-check-circle me-2"></i>Result Found - Congratulations!
                    </h4>
                </div>
                <div class="card-body">
                    {{-- Candidate Information --}}
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <h5 class="text-primary mb-3">Candidate Information</h5>
                            <table class="table table-sm">
                                <tr>
                                    <td class="fw-bold" width="40%">Registration Number:</td>
                                    <td>REG-2025-000001</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Candidate Name:</td>
                                    <td>John Doe</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Exam Name:</td>
                                    <td>University Entrance Examination 2025</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Exam Date:</td>
                                    <td>January 15, 2025</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Center:</td>
                                    <td>Main Campus, Exam Hall A</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h2 class="display-4 text-success fw-bold">85.5%</h2>
                                    <p class="text-muted mb-2">Overall Percentage</p>
                                    <h4 class="text-primary">Rank: 145</h4>
                                    <span class="badge bg-success fs-6">QUALIFIED</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Score Details --}}
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="text-primary mb-3">Score Details</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Subject</th>
                                            <th class="text-center">Questions Attempted</th>
                                            <th class="text-center">Correct Answers</th>
                                            <th class="text-center">Wrong Answers</th>
                                            <th class="text-center">Marks Obtained</th>
                                            <th class="text-center">Max Marks</th>
                                            <th class="text-center">Percentage</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><strong>Mathematics</strong></td>
                                            <td class="text-center">28</td>
                                            <td class="text-center">25</td>
                                            <td class="text-center">3</td>
                                            <td class="text-center">97</td>
                                            <td class="text-center">120</td>
                                            <td class="text-center"><span class="badge bg-success">80.8%</span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Physics</strong></td>
                                            <td class="text-center">24</td>
                                            <td class="text-center">22</td>
                                            <td class="text-center">2</td>
                                            <td class="text-center">86</td>
                                            <td class="text-center">100</td>
                                            <td class="text-center"><span class="badge bg-success">86.0%</span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Chemistry</strong></td>
                                            <td class="text-center">25</td>
                                            <td class="text-center">23</td>
                                            <td class="text-center">2</td>
                                            <td class="text-center">90</td>
                                            <td class="text-center">100</td>
                                            <td class="text-center"><span class="badge bg-success">90.0%</span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>English & GK</strong></td>
                                            <td class="text-center">20</td>
                                            <td class="text-center">18</td>
                                            <td class="text-center">2</td>
                                            <td class="text-center">35</td>
                                            <td class="text-center">40</td>
                                            <td class="text-center"><span class="badge bg-success">87.5%</span></td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <th>Total</th>
                                            <th class="text-center">97</th>
                                            <th class="text-center">88</th>
                                            <th class="text-center">9</th>
                                            <th class="text-center">308</th>
                                            <th class="text-center">360</th>
                                            <th class="text-center"><span class="badge bg-primary fs-6">85.5%</span></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="row">
                        <div class="col-12 text-center">
                            <button class="btn btn-primary btn-lg me-2" onclick="downloadScorecard()">
                                <i class="fas fa-download me-2"></i>Download Scorecard
                            </button>
                            <button class="btn btn-success btn-lg me-2" onclick="downloadCertificate()">
                                <i class="fas fa-certificate me-2"></i>Download Certificate
                            </button>
                            <button class="btn btn-info btn-lg" onclick="printResult()">
                                <i class="fas fa-print me-2"></i>Print Result
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Results Announcement --}}
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-bullhorn me-2"></i>Recent Result Announcements
                    </h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Exam Name</th>
                                    <th>Exam Date</th>
                                    <th>Result Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <strong>University Entrance Exam - Spring 2025</strong>
                                        <br><small class="text-muted">Computer Science & Engineering</small>
                                    </td>
                                    <td>January 15, 2025</td>
                                    <td>
                                        <span class="badge bg-success">Published</span>
                                        <br>January 25, 2025
                                    </td>
                                    <td>
                                        <span class="text-success"><i class="fas fa-check-circle"></i> Available</span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" onclick="checkResult('CSE2025')">
                                            Check Result
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong>Medical Entrance Test - 2025</strong>
                                        <br><small class="text-muted">MBBS/BDS Programs</small>
                                    </td>
                                    <td>January 10, 2025</td>
                                    <td>
                                        <span class="badge bg-success">Published</span>
                                        <br>January 20, 2025
                                    </td>
                                    <td>
                                        <span class="text-success"><i class="fas fa-check-circle"></i> Available</span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" onclick="checkResult('MED2025')">
                                            Check Result
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong>Business Administration Entrance</strong>
                                        <br><small class="text-muted">MBA/BBA Programs</small>
                                    </td>
                                    <td>January 5, 2025</td>
                                    <td>
                                        <span class="badge bg-warning text-dark">Processing</span>
                                        <br>Expected: January 30, 2025
                                    </td>
                                    <td>
                                        <span class="text-warning"><i class="fas fa-clock"></i> Under Evaluation</span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-secondary" disabled>
                                            Coming Soon
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong>Law Entrance Examination</strong>
                                        <br><small class="text-muted">LLB/BA.LLB Programs</small>
                                    </td>
                                    <td>December 20, 2024</td>
                                    <td>
                                        <span class="badge bg-success">Published</span>
                                        <br>January 5, 2025
                                    </td>
                                    <td>
                                        <span class="text-success"><i class="fas fa-check-circle"></i> Available</span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" onclick="checkResult('LAW2024')">
                                            Check Result
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Merit Lists --}}
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-medal me-2"></i>Top Performers - Engineering
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Registration No.</th>
                                    <th>Score</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="table-warning">
                                    <td><i class="fas fa-trophy text-warning"></i> 1</td>
                                    <td>REG-2025-000456</td>
                                    <td>352/360</td>
                                    <td><strong>97.78%</strong></td>
                                </tr>
                                <tr class="table-light">
                                    <td><i class="fas fa-medal text-secondary"></i> 2</td>
                                    <td>REG-2025-000789</td>
                                    <td>348/360</td>
                                    <td><strong>96.67%</strong></td>
                                </tr>
                                <tr class="table-light">
                                    <td><i class="fas fa-medal" style="color: #CD7F32;"></i> 3</td>
                                    <td>REG-2025-000234</td>
                                    <td>345/360</td>
                                    <td><strong>95.83%</strong></td>
                                </tr>
                                <tr>
                                    <td>4</td>
                                    <td>REG-2025-000567</td>
                                    <td>342/360</td>
                                    <td>95.00%</td>
                                </tr>
                                <tr>
                                    <td>5</td>
                                    <td>REG-2025-000890</td>
                                    <td>340/360</td>
                                    <td>94.44%</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="text-center mt-3">
                            <a href="#" class="btn btn-sm btn-outline-primary">View Full Merit List</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-medal me-2"></i>Top Performers - Medical
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Registration No.</th>
                                    <th>Score</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="table-success">
                                    <td><i class="fas fa-trophy text-warning"></i> 1</td>
                                    <td>REG-2025-001234</td>
                                    <td>355/360</td>
                                    <td><strong>98.61%</strong></td>
                                </tr>
                                <tr class="table-light">
                                    <td><i class="fas fa-medal text-secondary"></i> 2</td>
                                    <td>REG-2025-001567</td>
                                    <td>351/360</td>
                                    <td><strong>97.50%</strong></td>
                                </tr>
                                <tr class="table-light">
                                    <td><i class="fas fa-medal" style="color: #CD7F32;"></i> 3</td>
                                    <td>REG-2025-001890</td>
                                    <td>348/360</td>
                                    <td><strong>96.67%</strong></td>
                                </tr>
                                <tr>
                                    <td>4</td>
                                    <td>REG-2025-001345</td>
                                    <td>346/360</td>
                                    <td>96.11%</td>
                                </tr>
                                <tr>
                                    <td>5</td>
                                    <td>REG-2025-001678</td>
                                    <td>344/360</td>
                                    <td>95.56%</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="text-center mt-3">
                            <a href="#" class="btn btn-sm btn-outline-success">View Full Merit List</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Important Notice --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="alert alert-info">
                <h5 class="alert-heading">
                    <i class="fas fa-info-circle me-2"></i>Important Information
                </h5>
                <ul class="mb-0">
                    <li>Results are provisional and subject to verification of documents.</li>
                    <li>Candidates must bring original documents for verification during admission.</li>
                    <li>In case of any discrepancy, contact the examination cell immediately.</li>
                    <li>Merit list will be updated after document verification.</li>
                    <li>For queries, email: exams@university.edu or call: +231-77-XXX-XXXX</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function searchResult(event) {
    event.preventDefault();
    
    // Show loading
    const btn = event.target.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Searching...';
    btn.disabled = true;
    
    // Simulate search delay
    setTimeout(() => {
        // Show result
        document.getElementById('resultDisplay').style.display = 'block';
        
        // Scroll to result
        document.getElementById('resultDisplay').scrollIntoView({ behavior: 'smooth' });
        
        // Reset button
        btn.innerHTML = originalText;
        btn.disabled = false;
    }, 1500);
}

function checkResult(examCode) {
    document.getElementById('registrationNumber').focus();
    alert('Please enter your registration number for ' + examCode);
}

function downloadScorecard() {
    alert('Downloading scorecard PDF...');
    // In production: window.location.href = '/results/scorecard/download';
}

function downloadCertificate() {
    alert('Downloading merit certificate...');
    // In production: window.location.href = '/results/certificate/download';
}

function printResult() {
    window.print();
}

function showResultSearch() {
    document.getElementById('registrationNumber').focus();
}
</script>
@endpush

@push('styles')
<style>
    @media print {
        .btn, .breadcrumb, .alert {
            display: none !important;
        }
    }
</style>
@endpush
@endsection