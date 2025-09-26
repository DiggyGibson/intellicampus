@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-chart-line me-2"></i>Revenue Report
            </h1>
            <nav aria-label="breadcrumb" class="mt-2">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('financial.admin-dashboard') }}">Financial Dashboard</a></li>
                    <li class="breadcrumb-item active">Revenue Report</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('financial.reports.revenue') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" 
                           value="{{ $startDate->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" 
                           value="{{ $endDate->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-2"></i>Apply Filter
                        </button>
                        <a href="{{ route('financial.reports.revenue') }}" class="btn btn-secondary">
                            <i class="fas fa-redo me-2"></i>Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Revenue
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format($summary['total_revenue'] ?? 0, 2) }}
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
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Transactions
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($summary['total_transactions'] ?? 0) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-receipt fa-2x text-gray-300"></i>
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
                                Daily Average
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format($summary['daily_average'] ?? 0, 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
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
                                Period Days
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $startDate->diffInDays($endDate) + 1 }} days
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <!-- Revenue Trend Chart -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>Daily Revenue Trend</h6>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Payment Methods Pie Chart -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Payment Methods</h6>
                </div>
                <div class="card-body">
                    <canvas id="paymentMethodChart" height="200"></canvas>
                    <div class="mt-3">
                        @php
                            $byMethod = $summary['by_method'] ?? collect();
                            if ($byMethod instanceof \Illuminate\Support\Collection) {
                                $byMethod = $byMethod->toArray();
                            }
                        @endphp
                        @foreach($byMethod as $method => $amount)
                        <div class="small">
                            <span class="badge bg-primary me-2"></span>
                            {{ ucfirst($method) }}: ${{ number_format($amount, 2) }}
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Transactions Table -->
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fas fa-list me-2"></i>Revenue Details</h6>
            <button class="btn btn-sm btn-success" onclick="exportToExcel()">
                <i class="fas fa-file-excel me-2"></i>Export to Excel
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="revenueTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Transaction ID</th>
                            <th>Student</th>
                            <th>Method</th>
                            <th>Reference</th>
                            <th class="text-end">Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($revenue as $payment)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($payment->date ?? $payment->payment_date)->format('M d, Y') }}</td>
                            <td>#{{ str_pad($payment->id ?? rand(1000, 9999), 6, '0', STR_PAD_LEFT) }}</td>
                            <td>
                                @if(isset($payment->student))
                                    {{ $payment->student->user->name ?? 'N/A' }}
                                @else
                                    Student Name
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ ucfirst($payment->payment_method) }}
                                </span>
                            </td>
                            <td>{{ $payment->reference_number ?? '-' }}</td>
                            <td class="text-end font-weight-bold">
                                ${{ number_format($payment->total ?? $payment->amount ?? 0, 2) }}
                            </td>
                            <td>
                                <span class="badge bg-success">Completed</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                No revenue data found for the selected period
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($revenue && count($revenue) > 0)
                    <tfoot>
                        <tr class="font-weight-bold">
                            <td colspan="5" class="text-end">Total:</td>
                            <td class="text-end">${{ number_format($summary['total_revenue'] ?? 0, 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Prepare data for charts - FIX for the Collection issue
@php
    $chartRevenue = $revenue ?? collect();
    $chartLabels = [];
    $chartData = [];
    
    if ($chartRevenue instanceof \Illuminate\Support\Collection) {
        $grouped = $chartRevenue->groupBy('date');
        foreach ($grouped as $date => $items) {
            $chartLabels[] = \Carbon\Carbon::parse($date)->format('M d');
            $chartData[] = $items->sum('total') ?: $items->sum('amount');
        }
    }
    
    // Fix payment methods data
    $methodData = [];
    $methodLabels = [];
    if (isset($summary['by_method'])) {
        $byMethod = $summary['by_method'];
        if ($byMethod instanceof \Illuminate\Support\Collection) {
            $byMethod = $byMethod->toArray();
        }
        $methodLabels = array_keys($byMethod);
        $methodData = array_values($byMethod);
    }
@endphp

// Revenue Line Chart
var ctx = document.getElementById('revenueChart').getContext('2d');
var revenueChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! json_encode($chartLabels) !!},
        datasets: [{
            label: 'Daily Revenue',
            data: {!! json_encode($chartData) !!},
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
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
const methodData = {!! json_encode($methodData) !!};
const methodLabels = {!! json_encode($methodLabels) !!};

var ctx2 = document.getElementById('paymentMethodChart').getContext('2d');
var methodChart = new Chart(ctx2, {
    type: 'doughnut',
    data: {
        labels: methodLabels,
        datasets: [{
            data: methodData,
            backgroundColor: [
                'rgba(54, 162, 235, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(153, 102, 255, 0.8)',
                'rgba(255, 99, 132, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

function exportToExcel() {
    window.location.href = '{{ route("financial.reports.export") }}?type=revenue&start_date={{ $startDate->format("Y-m-d") }}&end_date={{ $endDate->format("Y-m-d") }}';
}
</script>
@endpush

@push('styles')
<style>
.border-left-primary { border-left: 4px solid #4e73df; }
.border-left-success { border-left: 4px solid #1cc88a; }
.border-left-info { border-left: 4px solid #36b9cc; }
.border-left-warning { border-left: 4px solid #f6c23e; }
</style>
@endpush
@endsection