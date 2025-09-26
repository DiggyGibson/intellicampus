{{-- resources/views/exams/public/statistics.blade.php --}}
@extends('layouts.app')

@section('title', 'Entrance Exam Statistics')

@section('content')
<div class="container-fluid py-4">
    {{-- Header Section --}}
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exams.information') }}">Entrance Exams</a></li>
                    <li class="breadcrumb-item active">Statistics</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- Page Title --}}
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card shadow-sm bg-gradient bg-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="h2 mb-3">
                                <i class="fas fa-chart-bar me-2"></i>
                                Entrance Examination Statistics & Analytics
                            </h1>
                            <p class="mb-0 opacity-90">
                                Comprehensive analysis of entrance examination performance and trends
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <select class="form-select" id="examYearFilter" onchange="filterByYear(this.value)">
                                <option value="2025" selected>2025</option>
                                <option value="2024">2024</option>
                                <option value="2023">2023</option>
                                <option value="2022">2022</option>
                                <option value="all">All Years</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Key Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 p-3 rounded">
                                <i class="fas fa-users fa-2x text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Candidates</h6>
                            <h3 class="mb-0">45,678</h3>
                            <small class="text-success">
                                <i class="fas fa-arrow-up"></i> 12% from last year
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 p-3 rounded">
                                <i class="fas fa-user-check fa-2x text-success"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Qualified</h6>
                            <h3 class="mb-0">28,456</h3>
                            <small class="text-muted">62.3% Pass Rate</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 p-3 rounded">
                                <i class="fas fa-trophy fa-2x text-warning"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Highest Score</h6>
                            <h3 class="mb-0">358/360</h3>
                            <small class="text-muted">99.44%</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 p-3 rounded">
                                <i class="fas fa-percentage fa-2x text-info"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Average Score</h6>
                            <h3 class="mb-0">68.5%</h3>
                            <small class="text-info">
                                <i class="fas fa-arrow-up"></i> 3.2% improvement
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row mb-4">
        {{-- Score Distribution Chart --}}
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-area me-2"></i>Score Distribution
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="scoreDistributionChart" height="100"></canvas>
                </div>
            </div>
        </div>

        {{-- Pass/Fail Pie Chart --}}
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>Result Overview
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="passFailChart" height="200"></canvas>
                    <div class="mt-3">
                        <div class="d-flex justify-content-around text-center">
                            <div>
                                <h4 class="text-success mb-0">62.3%</h4>
                                <small class="text-muted">Qualified</small>
                            </div>
                            <div>
                                <h4 class="text-danger mb-0">37.7%</h4>
                                <small class="text-muted">Not Qualified</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Subject-wise Performance --}}
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-book me-2"></i>Subject-wise Performance Analysis
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Subject</th>
                                    <th class="text-center">Total Questions</th>
                                    <th class="text-center">Average Score</th>
                                    <th class="text-center">Highest Score</th>
                                    <th class="text-center">Lowest Score</th>
                                    <th class="text-center">Pass %</th>
                                    <th class="text-center">Difficulty</th>
                                    <th>Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Mathematics</strong></td>
                                    <td class="text-center">30</td>
                                    <td class="text-center">78.5 (94.2/120)</td>
                                    <td class="text-center">120/120</td>
                                    <td class="text-center">24/120</td>
                                    <td class="text-center">
                                        <span class="badge bg-success">65.8%</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-warning text-dark">Medium</span>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success" style="width: 78.5%">78.5%</div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Physics</strong></td>
                                    <td class="text-center">25</td>
                                    <td class="text-center">72.3 (72.3/100)</td>
                                    <td class="text-center">100/100</td>
                                    <td class="text-center">20/100</td>
                                    <td class="text-center">
                                        <span class="badge bg-warning text-dark">58.4%</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-danger">Hard</span>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-warning" style="width: 72.3%">72.3%</div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Chemistry</strong></td>
                                    <td class="text-center">25</td>
                                    <td class="text-center">75.6 (75.6/100)</td>
                                    <td class="text-center">100/100</td>
                                    <td class="text-center">28/100</td>
                                    <td class="text-center">
                                        <span class="badge bg-success">61.2%</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-warning text-dark">Medium</span>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-info" style="width: 75.6%">75.6%</div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>English & GK</strong></td>
                                    <td class="text-center">20</td>
                                    <td class="text-center">82.5 (33/40)</td>
                                    <td class="text-center">40/40</td>
                                    <td class="text-center">12/40</td>
                                    <td class="text-center">
                                        <span class="badge bg-success">71.5%</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success">Easy</span>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-primary" style="width: 82.5%">82.5%</div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Geographic Distribution --}}
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-map-marked-alt me-2"></i>Geographic Distribution
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Region</th>
                                <th class="text-center">Candidates</th>
                                <th class="text-center">Pass Rate</th>
                                <th>Distribution</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Montserrado</td>
                                <td class="text-center">15,234</td>
                                <td class="text-center">
                                    <span class="badge bg-success">68.5%</span>
                                </td>
                                <td>
                                    <div class="progress" style="height: 15px;">
                                        <div class="progress-bar" style="width: 33.4%"></div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Margibi</td>
                                <td class="text-center">8,456</td>
                                <td class="text-center">
                                    <span class="badge bg-success">65.2%</span>
                                </td>
                                <td>
                                    <div class="progress" style="height: 15px;">
                                        <div class="progress-bar" style="width: 18.5%"></div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Bong</td>
                                <td class="text-center">6,789</td>
                                <td class="text-center">
                                    <span class="badge bg-warning text-dark">58.9%</span>
                                </td>
                                <td>
                                    <div class="progress" style="height: 15px;">
                                        <div class="progress-bar" style="width: 14.9%"></div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Nimba</td>
                                <td class="text-center">5,432</td>
                                <td class="text-center">
                                    <span class="badge bg-warning text-dark">56.7%</span>
                                </td>
                                <td>
                                    <div class="progress" style="height: 15px;">
                                        <div class="progress-bar" style="width: 11.9%"></div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Others</td>
                                <td class="text-center">9,767</td>
                                <td class="text-center">
                                    <span class="badge bg-info">60.3%</span>
                                </td>
                                <td>
                                    <div class="progress" style="height: 15px;">
                                        <div class="progress-bar" style="width: 21.4%"></div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Gender Distribution --}}
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-venus-mars me-2"></i>Gender Distribution & Performance
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-6 border-end">
                            <div class="p-3">
                                <i class="fas fa-male fa-3x text-primary mb-2"></i>
                                <h4>Male</h4>
                                <h5 class="text-primary">26,789</h5>
                                <p class="mb-0">58.6% of total</p>
                                <span class="badge bg-success">Pass Rate: 64.2%</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3">
                                <i class="fas fa-female fa-3x text-danger mb-2"></i>
                                <h4>Female</h4>
                                <h5 class="text-danger">18,889</h5>
                                <p class="mb-0">41.4% of total</p>
                                <span class="badge bg-success">Pass Rate: 59.8%</span>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <canvas id="genderPerformanceChart" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Year-wise Trend --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>5-Year Performance Trend
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="yearTrendChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Additional Statistics --}}
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-clock me-2"></i>Time Analysis
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2 d-flex justify-content-between">
                            <span>Average Time Taken:</span>
                            <strong>2h 45m</strong>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span>Fastest Completion:</span>
                            <strong>1h 52m</strong>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span>Questions/Minute:</span>
                            <strong>0.61</strong>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span>Time Utilization:</span>
                            <strong>91.7%</strong>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-question-circle me-2"></i>Question Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2 d-flex justify-content-between">
                            <span>Most Attempted:</span>
                            <strong>Q.15 (98.5%)</strong>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span>Least Attempted:</span>
                            <strong>Q.87 (45.2%)</strong>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span>Highest Accuracy:</span>
                            <strong>Q.22 (92.3%)</strong>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span>Lowest Accuracy:</span>
                            <strong>Q.64 (23.1%)</strong>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-graduation-cap me-2"></i>Cut-off Marks
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2 d-flex justify-content-between">
                            <span>General:</span>
                            <strong>180/360 (50%)</strong>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span>Engineering:</span>
                            <strong>216/360 (60%)</strong>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span>Medical:</span>
                            <strong>252/360 (70%)</strong>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span>Business:</span>
                            <strong>162/360 (45%)</strong>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// Score Distribution Chart
const scoreDistCtx = document.getElementById('scoreDistributionChart').getContext('2d');
new Chart(scoreDistCtx, {
    type: 'bar',
    data: {
        labels: ['0-20%', '21-40%', '41-60%', '61-80%', '81-100%'],
        datasets: [{
            label: 'Number of Candidates',
            data: [2345, 5678, 12456, 18234, 6965],
            backgroundColor: [
                'rgba(220, 53, 69, 0.8)',
                'rgba(255, 193, 7, 0.8)',
                'rgba(23, 162, 184, 0.8)',
                'rgba(40, 167, 69, 0.8)',
                'rgba(13, 110, 253, 0.8)'
            ]
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

// Pass/Fail Pie Chart
const passFailCtx = document.getElementById('passFailChart').getContext('2d');
new Chart(passFailCtx, {
    type: 'doughnut',
    data: {
        labels: ['Qualified', 'Not Qualified'],
        datasets: [{
            data: [62.3, 37.7],
            backgroundColor: [
                'rgba(40, 167, 69, 0.8)',
                'rgba(220, 53, 69, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Gender Performance Chart
const genderCtx = document.getElementById('genderPerformanceChart').getContext('2d');
new Chart(genderCtx, {
    type: 'bar',
    data: {
        labels: ['Mathematics', 'Physics', 'Chemistry', 'English & GK'],
        datasets: [{
            label: 'Male',
            data: [75, 72, 78, 80],
            backgroundColor: 'rgba(13, 110, 253, 0.8)'
        }, {
            label: 'Female',
            data: [72, 68, 74, 85],
            backgroundColor: 'rgba(220, 53, 69, 0.8)'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                max: 100
            }
        }
    }
});

// Year Trend Chart
const trendCtx = document.getElementById('yearTrendChart').getContext('2d');
new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: ['2021', '2022', '2023', '2024', '2025'],
        datasets: [{
            label: 'Total Candidates',
            data: [35678, 38456, 41234, 43567, 45678],
            borderColor: 'rgba(13, 110, 253, 1)',
            tension: 0.1
        }, {
            label: 'Qualified',
            data: [18234, 21456, 24567, 26789, 28456],
            borderColor: 'rgba(40, 167, 69, 1)',
            tension: 0.1
        }, {
            label: 'Average Score (%)',
            data: [62.3, 64.5, 66.8, 67.2, 68.5],
            borderColor: 'rgba(255, 193, 7, 1)',
            tension: 0.1,
            yAxisID: 'percentage'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                position: 'left'
            },
            percentage: {
                beginAtZero: true,
                position: 'right',
                max: 100,
                grid: {
                    drawOnChartArea: false
                }
            }
        }
    }
});

function filterByYear(year) {
    // In production, this would reload data for selected year
    console.log('Filtering statistics for year:', year);
}
</script>
@endpush
@endsection