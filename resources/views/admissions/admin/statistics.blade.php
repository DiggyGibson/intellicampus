{{-- 
    File: resources/views/admissions/admin/statistics.blade.php
    Purpose: Display comprehensive admission statistics
--}}
@extends('layouts.app')

@section('title', 'Admission Statistics')

@section('content')
<div class="container-fluid">
    {{-- Overview Stats --}}
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card">
                <div class="stat-icon bg-primary">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $stats['total_applications'] ?? 0 }}</h3>
                    <p>Total Applications</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card">
                <div class="stat-icon bg-warning">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $stats['pending_review'] ?? 0 }}</h3>
                    <p>Pending Review</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card">
                <div class="stat-icon bg-success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $stats['admitted'] ?? 0 }}</h3>
                    <p>Admitted</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card">
                <div class="stat-icon bg-info">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $stats['conversion_rate'] ?? 0 }}%</h3>
                    <p>Acceptance Rate</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Application Status Chart --}}
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Application Status Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" height="300"></canvas>
                </div>
            </div>
        </div>

        {{-- Programs Distribution --}}
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Applications by Program</h5>
                </div>
                <div class="card-body">
                    <canvas id="programChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Trends Over Time --}}
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Application Trends (Last 30 Days)</h5>
                </div>
                <div class="card-body">
                    <canvas id="trendsChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Detailed Statistics Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Detailed Statistics by Program</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Program</th>
                                    <th>Applications</th>
                                    <th>Under Review</th>
                                    <th>Admitted</th>
                                    <th>Denied</th>
                                    <th>Waitlisted</th>
                                    <th>Acceptance Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stats['by_program'] ?? [] as $program)
                                <tr>
                                    <td>{{ $program->name ?? 'Unknown' }}</td>
                                    <td>{{ $program->total ?? 0 }}</td>
                                    <td>{{ $program->under_review ?? 0 }}</td>
                                    <td>{{ $program->admitted ?? 0 }}</td>
                                    <td>{{ $program->denied ?? 0 }}</td>
                                    <td>{{ $program->waitlisted ?? 0 }}</td>
                                    <td>
                                        @if($program->total > 0)
                                            {{ round(($program->admitted / $program->total) * 100, 1) }}%
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No data available</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Status Distribution Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Submitted', 'Under Review', 'Admitted', 'Denied', 'Waitlisted'],
        datasets: [{
            data: [
                {{ $stats['submitted'] ?? 0 }},
                {{ $stats['under_review'] ?? 0 }},
                {{ $stats['admitted'] ?? 0 }},
                {{ $stats['denied'] ?? 0 }},
                {{ $stats['waitlisted'] ?? 0 }}
            ],
            backgroundColor: ['#ffc107', '#17a2b8', '#28a745', '#dc3545', '#6c757d']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Programs Chart
const programCtx = document.getElementById('programChart').getContext('2d');
new Chart(programCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode(collect($stats['by_program'] ?? [])->pluck('name')) !!},
        datasets: [{
            label: 'Applications',
            data: {!! json_encode(collect($stats['by_program'] ?? [])->pluck('total')) !!},
            backgroundColor: '#3498db'
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

// Trends Chart (placeholder data - replace with actual)
const trendsCtx = document.getElementById('trendsChart').getContext('2d');
new Chart(trendsCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode(array_map(function($i) { return date('M d', strtotime("-$i days")); }, range(30, 0))) !!},
        datasets: [{
            label: 'Applications',
            data: {!! json_encode(array_map(function() { return rand(5, 25); }, range(0, 30))) !!},
            borderColor: '#3498db',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});
</script>
@endpush