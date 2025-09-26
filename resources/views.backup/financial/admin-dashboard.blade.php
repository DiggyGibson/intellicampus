@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-chart-pie me-2"></i>Financial Administration Dashboard
            </h1>
            <nav aria-label="breadcrumb" class="mt-2">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Financial Dashboard</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Revenue (This Term)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format($termRevenue ?? 1250000, 2) }}
                            </div>
                            <div class="text-xs text-muted">
                                <i class="fas fa-arrow-up text-success"></i> 
                                {{ $revenueChange ?? 12.5 }}% from last term
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Outstanding Balance
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format($totalOutstanding ?? 425000, 2) }}
                            </div>
                            <div class="text-xs text-muted">
                                {{ $studentsWithBalance ?? 156 }} students
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Collection Rate
                            </div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">
                                        {{ $collectionRate ?? 87 }}%
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="progress progress-sm mr-2">
                                        <div class="progress-bar bg-info" role="progressbar"
                                            style="width: {{ $collectionRate ?? 87 }}%" 
                                            aria-valuenow="{{ $collectionRate ?? 87 }}"
                                            aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Financial Aid Awarded
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format($totalFinancialAid ?? 325000, 2) }}
                            </div>
                            <div class="text-xs text-muted">
                                {{ $aidRecipients ?? 89 }} recipients
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hands-helping fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2 mb-2">
                            <button class="btn btn-success btn-block" data-bs-toggle="modal" data-bs-target="#generateFeesModal">
                                <i class="fas fa-file-invoice-dollar me-2"></i>Generate Term Fees
                            </button>
                        </div>
                        <div class="col-md-2 mb-2">
                            <a href="{{ route('financial.fee-structure') }}" class="btn btn-info btn-block">
                                <i class="fas fa-coins me-2"></i>Fee Structure
                            </a>
                        </div>
                        <div class="col-md-2 mb-2">
                            <a href="{{ route('financial.pending-payments') }}" class="btn btn-warning btn-block">
                                <i class="fas fa-clock me-2"></i>Pending Payments
                            </a>
                        </div>
                        <div class="col-md-2 mb-2">
                            <button class="btn btn-danger btn-block" data-bs-toggle="modal" data-bs-target="#addChargeModal">
                                <i class="fas fa-plus-circle me-2"></i>Add Charge
                            </button>
                        </div>
                        <div class="col-md-2 mb-2">
                            <a href="{{ route('financial.reports.daily-cash') }}" class="btn btn-secondary btn-block">
                                <i class="fas fa-cash-register me-2"></i>Daily Report
                            </a>
                        </div>
                        <div class="col-md-2 mb-2">
                            <button class="btn btn-primary btn-block" data-bs-toggle="modal" data-bs-target="#exportModal">
                                <i class="fas fa-download me-2"></i>Export Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Tables -->
    <div class="row">
        <!-- Revenue Chart -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>Revenue Trend (Last 12 Months)</h6>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" 
                                id="revenueDropdown" data-bs-toggle="dropdown">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="revenueDropdown">
                            <li><a class="dropdown-item" href="#" onclick="updateRevenueChart('year')">This Year</a></li>
                            <li><a class="dropdown-item" href="#" onclick="updateRevenueChart('lastYear')">Last Year</a></li>
                            <li><a class="dropdown-item" href="#" onclick="updateRevenueChart('twoYears')">Last 2 Years</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Payment Distribution -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Payment Methods Distribution</h6>
                </div>
                <div class="card-body">
                    <canvas id="paymentMethodChart" height="200"></canvas>
                    <div class="mt-3">
                        <div class="small text-muted">
                            <div><span class="badge bg-primary me-2"></span>Credit Card: 45%</div>
                            <div><span class="badge bg-success me-2"></span>ACH Transfer: 30%</div>
                            <div><span class="badge bg-warning me-2"></span>Cash/Check: 15%</div>
                            <div><span class="badge bg-info me-2"></span>Financial Aid: 10%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions and Alerts -->
    <div class="row">
        <!-- Recent Large Transactions -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-exchange-alt me-2"></i>Recent Transactions</h6>
                    <a href="{{ route('financial.reports.collections') }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Student Name</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th class="text-end">Amount</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTransactions ?? [] as $transaction)
                                <tr>
                                    <td>{{ $transaction->student->student_id }}</td>
                                    <td>{{ $transaction->student->user->name }}</td>
                                    <td>
                                        @if($transaction->type == 'payment')
                                            <span class="badge bg-success">Payment</span>
                                        @elseif($transaction->type == 'charge')
                                            <span class="badge bg-warning">Charge</span>
                                        @elseif($transaction->type == 'refund')
                                            <span class="badge bg-info">Refund</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($transaction->type) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ Str::limit($transaction->description, 30) }}</td>
                                    <td class="text-end">
                                        @if($transaction->type == 'payment' || $transaction->type == 'credit')
                                            <span class="text-success">+${{ number_format($transaction->amount, 2) }}</span>
                                        @else
                                            <span class="text-danger">-${{ number_format($transaction->amount, 2) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $transaction->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge bg-success">Completed</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        No recent transactions found
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts & Notifications -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0"><i class="fas fa-bell me-2"></i>Alerts & Actions Required</h6>
                </div>
                <div class="card-body">
                    @if(($overdueAccounts ?? 23) > 0)
                    <div class="alert alert-danger alert-sm" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>{{ $overdueAccounts ?? 23 }} accounts</strong> are overdue by 30+ days
                        <a href="{{ route('financial.reports.outstanding') }}" class="alert-link d-block mt-1">
                            View Overdue Accounts →
                        </a>
                    </div>
                    @endif

                    @if(($pendingPaymentPlans ?? 5) > 0)
                    <div class="alert alert-warning alert-sm" role="alert">
                        <i class="fas fa-clock me-2"></i>
                        <strong>{{ $pendingPaymentPlans ?? 5 }} payment plans</strong> pending approval
                        <a href="{{ route('financial.payment-plans.pending') }}" class="alert-link d-block mt-1">
                            Review Plans →
                        </a>
                    </div>
                    @endif

                    @if(($pendingAidApplications ?? 12) > 0)
                    <div class="alert alert-info alert-sm" role="alert">
                        <i class="fas fa-hands-helping me-2"></i>
                        <strong>{{ $pendingAidApplications ?? 12 }} financial aid</strong> applications pending
                        <a href="{{ route('financial.aid.applications') }}" class="alert-link d-block mt-1">
                            Review Applications →
                        </a>
                    </div>
                    @endif

                    @if(($lowBalanceAccounts ?? 45) > 0)
                    <div class="alert alert-success alert-sm" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>{{ $lowBalanceAccounts ?? 45 }} students</strong> have cleared their balance
                    </div>
                    @endif
                </div>
            </div>

            <!-- Top Debtors -->
            <div class="card shadow mt-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-list me-2"></i>Top Outstanding Balances</h6>
                </div>
                <div class="card-body">
                    @forelse($topDebtors ?? [] as $debtor)
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <strong>{{ $debtor->student->user->name }}</strong><br>
                            <small class="text-muted">ID: {{ $debtor->student->student_id }}</small>
                        </div>
                        <div class="text-end">
                            <span class="text-danger font-weight-bold">
                                ${{ number_format($debtor->balance, 2) }}
                            </span><br>
                            <small class="text-muted">{{ $debtor->days_overdue }} days</small>
                        </div>
                    </div>
                    @empty
                    <!-- Default sample data for demonstration -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <strong>John Smith</strong><br>
                            <small class="text-muted">ID: STU-2025001</small>
                        </div>
                        <div class="text-end">
                            <span class="text-danger font-weight-bold">$8,450.00</span><br>
                            <small class="text-muted">45 days</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <strong>Sarah Johnson</strong><br>
                            <small class="text-muted">ID: STU-2025045</small>
                        </div>
                        <div class="text-end">
                            <span class="text-danger font-weight-bold">$6,200.00</span><br>
                            <small class="text-muted">32 days</small>
                        </div>
                    </div>
                    @endforelse
                    
                    <div class="text-center mt-3">
                        <a href="{{ route('financial.reports.outstanding') }}" class="btn btn-sm btn-outline-danger">
                            View All Overdue Accounts
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Statistics Row -->
    <div class="row mt-4">
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Term Comparison</h6>
                </div>
                <div class="card-body">
                    <canvas id="termComparisonChart" height="200"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-university me-2"></i>Department Revenue</h6>
                </div>
                <div class="card-body">
                    <canvas id="departmentRevenueChart" height="200"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-user-graduate me-2"></i>Student Type Distribution</h6>
                </div>
                <div class="card-body">
                    <canvas id="studentTypeChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Generate Term Fees Modal -->
<div class="modal fade" id="generateFeesModal" tabindex="-1" aria-labelledby="generateFeesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('financial.generate-fees') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="generateFeesModalLabel">Generate Term Fees</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="term_id" class="form-label">Academic Term</label>
                        <select class="form-select" id="term_id" name="term_id" required>
                            <option value="">Select Term</option>
                            @foreach($academicTerms ?? [] as $term)
                            <option value="{{ $term->id }}">{{ $term->name }} {{ $term->year }}</option>
                            @endforeach
                            <!-- Default options if no terms -->
                            <option value="1">Spring 2025</option>
                            <option value="2">Fall 2025</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="fee_types" class="form-label">Fee Types to Generate</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="fee_types[]" value="tuition" id="tuition" checked>
                            <label class="form-check-label" for="tuition">Tuition Fees</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="fee_types[]" value="registration" id="registration" checked>
                            <label class="form-check-label" for="registration">Registration Fees</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="fee_types[]" value="technology" id="technology" checked>
                            <label class="form-check-label" for="technology">Technology Fees</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="fee_types[]" value="lab" id="lab">
                            <label class="form-check-label" for="lab">Lab Fees</label>
                        </div>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This will generate fees for all enrolled students for the selected term.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Generate Fees</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Charge Modal -->
<div class="modal fade" id="addChargeModal" tabindex="-1" aria-labelledby="addChargeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('financial.add-charge') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addChargeModalLabel">Add Charge to Student Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="student_search" class="form-label">Search Student</label>
                        <input type="text" class="form-control" id="student_search" 
                               placeholder="Enter student ID or name...">
                        <select class="form-select mt-2" id="student_id" name="student_id" required>
                            <option value="">Select Student</option>
                            <!-- Will be populated via AJAX -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="charge_type" class="form-label">Charge Type</label>
                        <select class="form-select" id="charge_type" name="charge_type" required>
                            <option value="">Select Type</option>
                            <option value="late_fee">Late Registration Fee</option>
                            <option value="transcript_fee">Transcript Fee</option>
                            <option value="replacement_id">Replacement ID Card</option>
                            <option value="parking_fine">Parking Fine</option>
                            <option value="library_fine">Library Fine</option>
                            <option value="damage_fee">Damage Fee</option>
                            <option value="lab_breakage">Lab Breakage</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="amount" name="amount" 
                                   step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="2" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="due_date" class="form-label">Due Date</label>
                        <input type="date" class="form-control" id="due_date" name="due_date" 
                               value="{{ date('Y-m-d', strtotime('+30 days')) }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Add Charge</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Export Data Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('financial.reports.export') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="exportModalLabel">Export Financial Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="export_type" class="form-label">Data Type</label>
                        <select class="form-select" id="export_type" name="export_type" required>
                            <option value="">Select Data Type</option>
                            <option value="transactions">All Transactions</option>
                            <option value="payments">Payments Only</option>
                            <option value="charges">Charges Only</option>
                            <option value="outstanding">Outstanding Balances</option>
                            <option value="financial_aid">Financial Aid</option>
                            <option value="payment_plans">Payment Plans</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="date_range" class="form-label">Date Range</label>
                        <select class="form-select" id="date_range" name="date_range">
                            <option value="current_term">Current Term</option>
                            <option value="last_term">Last Term</option>
                            <option value="current_year">Current Year</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>
                    <div id="customDateRange" style="display: none;">
                        <div class="mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date">
                        </div>
                        <div class="mb-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="format" class="form-label">Export Format</label>
                        <select class="form-select" id="format" name="format" required>
                            <option value="excel">Excel (.xlsx)</option>
                            <option value="csv">CSV</option>
                            <option value="pdf">PDF Report</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-download me-2"></i>Export
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Sample data for charts - replace with actual data from controller
const revenueLabels = {!! json_encode($revenueLabels ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']) !!};
const revenueData = {!! json_encode($revenueData ?? [95000, 102000, 98000, 115000, 125000, 118000, 135000, 142000, 138000, 145000, 152000, 165000]) !!};

// Revenue Chart
var ctx = document.getElementById('revenueChart').getContext('2d');
var revenueChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: revenueLabels,
        datasets: [{
            label: 'Revenue',
            data: revenueData,
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return '$' + context.parsed.y.toLocaleString();
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

// Payment Method Pie Chart
var ctx2 = document.getElementById('paymentMethodChart').getContext('2d');
var paymentChart = new Chart(ctx2, {
    type: 'doughnut',
    data: {
        labels: ['Credit Card', 'ACH Transfer', 'Cash/Check', 'Financial Aid'],
        datasets: [{
            data: [45, 30, 15, 10],
            backgroundColor: [
                'rgba(54, 162, 235, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(153, 102, 255, 0.8)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

// Term Comparison Chart
var ctx3 = document.getElementById('termComparisonChart').getContext('2d');
var termChart = new Chart(ctx3, {
    type: 'bar',
    data: {
        labels: ['Spring 2024', 'Fall 2024', 'Spring 2025'],
        datasets: [{
            label: 'Revenue',
            data: [980000, 1150000, 1250000],
            backgroundColor: 'rgba(54, 162, 235, 0.8)'
        }, {
            label: 'Collected',
            data: [850000, 1000000, 1087500],
            backgroundColor: 'rgba(75, 192, 192, 0.8)'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + (value / 1000) + 'k';
                    }
                }
            }
        }
    }
});

// Department Revenue Chart
var ctx4 = document.getElementById('departmentRevenueChart').getContext('2d');
var deptChart = new Chart(ctx4, {
    type: 'horizontalBar',
    data: {
        labels: ['Engineering', 'Business', 'Arts & Sciences', 'Medicine', 'Law'],
        datasets: [{
            label: 'Revenue',
            data: [450000, 380000, 320000, 280000, 220000],
            backgroundColor: [
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(153, 102, 255, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y',
        scales: {
            x: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + (value / 1000) + 'k';
                    }
                }
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

// Student Type Distribution Chart
var ctx5 = document.getElementById('studentTypeChart').getContext('2d');
var studentChart = new Chart(ctx5, {
    type: 'pie',
    data: {
        labels: ['Undergraduate', 'Graduate', 'Doctoral', 'International'],
        datasets: [{
            data: [65, 20, 10, 5],
            backgroundColor: [
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(255, 99, 132, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Handle export date range selection
document.getElementById('date_range').addEventListener('change', function() {
    document.getElementById('customDateRange').style.display = 
        this.value === 'custom' ? 'block' : 'none';
});

// Student search functionality
document.getElementById('student_search').addEventListener('input', function() {
    // Implement AJAX search here
    // For now, just populate with sample data
    const select = document.getElementById('student_id');
    select.innerHTML = '<option value="">Select Student</option>' +
        '<option value="1">John Smith (STU-2025001)</option>' +
        '<option value="2">Sarah Johnson (STU-2025045)</option>';
});

// Update revenue chart function
function updateRevenueChart(period) {
    // Implement chart update logic
    console.log('Updating chart for period:', period);
}
</script>
@endpush

@push('styles')
<style>
.border-left-primary {
    border-left: 4px solid #4e73df;
}
.border-left-success {
    border-left: 4px solid #1cc88a;
}
.border-left-info {
    border-left: 4px solid #36b9cc;
}
.border-left-warning {
    border-left: 4px solid #f6c23e;
}
.border-left-danger {
    border-left: 4px solid #e74a3b;
}
.btn-block {
    width: 100%;
}
.alert-sm {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
}
</style>
@endpush
@endsection