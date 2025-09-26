{{-- File: resources/views/exams/admin/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Exam Management Dashboard')

@section('styles')
<style>
    .exam-card {
        transition: transform 0.2s, box-shadow 0.2s;
        border-left: 4px solid transparent;
    }
    .exam-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .exam-card.active {
        border-left-color: #28a745;
    }
    .exam-card.upcoming {
        border-left-color: #007bff;
    }
    .exam-card.completed {
        border-left-color: #6c757d;
    }
    .stat-card {
        border-radius: 10px;
        padding: 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    .chart-container {
        position: relative;
        height: 300px;
    }
    .timeline-item {
        border-left: 3px solid #dee2e6;
        padding-left: 20px;
        margin-left: 10px;
        position: relative;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -8px;
        top: 0;
        width: 13px;
        height: 13px;
        border-radius: 50%;
        background: #007bff;
        border: 3px solid #fff;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    {{-- Header Section --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-clipboard-list me-2"></i>Exam Management Dashboard
                    </h1>
                    <p class="text-muted mb-0">Manage entrance examinations and assessments</p>
                </div>
                <div class="btn-toolbar">
                    <button type="button" class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#quickActionsModal">
                        <i class="fas fa-bolt me-1"></i> Quick Actions
                    </button>
                    <a href="{{ route('exams.admin.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Create New Exam
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Active Exams</h6>
                            <h2 class="mb-0">{{ $stats['active_exams'] ?? 0 }}</h2>
                            <small class="text-white-50">
                                <i class="fas fa-arrow-up me-1"></i>{{ $stats['active_change'] ?? 0 }}% from last month
                            </small>
                        </div>
                        <div class="text-white-50">
                            <i class="fas fa-clipboard-check fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Total Registrations</h6>
                            <h2 class="mb-0">{{ number_format($stats['total_registrations'] ?? 0) }}</h2>
                            <small class="text-white-50">
                                {{ $stats['pending_registrations'] ?? 0 }} pending
                            </small>
                        </div>
                        <div class="text-white-50">
                            <i class="fas fa-users fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Upcoming Sessions</h6>
                            <h2 class="mb-0">{{ $stats['upcoming_sessions'] ?? 0 }}</h2>
                            <small class="text-white-50">
                                Next: {{ $stats['next_session_date'] ?? 'N/A' }}
                            </small>
                        </div>
                        <div class="text-white-50">
                            <i class="fas fa-calendar-alt fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Results Pending</h6>
                            <h2 class="mb-0">{{ $stats['results_pending'] ?? 0 }}</h2>
                            <small class="text-white-50">
                                {{ $stats['evaluations_in_progress'] ?? 0 }} in evaluation
                            </small>
                        </div>
                        <div class="text-white-50">
                            <i class="fas fa-hourglass-half fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content Area --}}
    <div class="row">
        {{-- Active Exams Section --}}
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i>Active Examinations</h5>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-secondary active" data-filter="all">All</button>
                        <button type="button" class="btn btn-outline-secondary" data-filter="entrance">Entrance</button>
                        <button type="button" class="btn btn-outline-secondary" data-filter="placement">Placement</button>
                        <button type="button" class="btn btn-outline-secondary" data-filter="scholarship">Scholarship</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Exam Code</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Mode</th>
                                    <th>Date/Window</th>
                                    <th>Registrations</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activeExams as $exam)
                                <tr>
                                    <td>
                                        <a href="{{ route('exams.admin.show', $exam->id) }}" class="text-primary font-weight-bold">
                                            {{ $exam->exam_code }}
                                        </a>
                                    </td>
                                    <td>{{ Str::limit($exam->exam_name, 30) }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ ucfirst($exam->exam_type) }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $modeIcons = [
                                                'paper_based' => 'fa-file-alt text-secondary',
                                                'computer_based' => 'fa-desktop text-primary',
                                                'online_proctored' => 'fa-video text-success',
                                                'online_unproctored' => 'fa-globe text-info',
                                                'hybrid' => 'fa-random text-warning'
                                            ];
                                            $icon = $modeIcons[$exam->delivery_mode] ?? 'fa-question';
                                        @endphp
                                        <i class="fas {{ $icon }}" title="{{ ucfirst(str_replace('_', ' ', $exam->delivery_mode)) }}"></i>
                                    </td>
                                    <td>
                                        @if($exam->exam_date)
                                            {{ \Carbon\Carbon::parse($exam->exam_date)->format('M d, Y') }}
                                        @else
                                            {{ \Carbon\Carbon::parse($exam->exam_window_start)->format('M d') }} - 
                                            {{ \Carbon\Carbon::parse($exam->exam_window_end)->format('M d, Y') }}
                                        @endif
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            @php
                                                $percentage = $exam->total_capacity > 0 
                                                    ? ($exam->registrations_count / $exam->total_capacity) * 100 
                                                    : 0;
                                                $progressClass = $percentage >= 90 ? 'bg-danger' : ($percentage >= 70 ? 'bg-warning' : 'bg-success');
                                            @endphp
                                            <div class="progress-bar {{ $progressClass }}" 
                                                 role="progressbar" 
                                                 style="width: {{ $percentage }}%">
                                                {{ $exam->registrations_count }}/{{ $exam->total_capacity }}
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @include('exams.partials.status-badge', ['status' => $exam->status])
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('exams.admin.manage', $exam->id) }}" 
                                               class="btn btn-outline-primary" 
                                               title="Manage">
                                                <i class="fas fa-cog"></i>
                                            </a>
                                            <a href="{{ route('exams.admin.registrations', $exam->id) }}" 
                                               class="btn btn-outline-info" 
                                               title="Registrations">
                                                <i class="fas fa-users"></i>
                                            </a>
                                            <a href="{{ route('exams.admin.sessions', $exam->id) }}" 
                                               class="btn btn-outline-secondary" 
                                               title="Sessions">
                                                <i class="fas fa-calendar"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>No active examinations found</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($activeExams->hasPages())
                    <div class="d-flex justify-content-end">
                        {{ $activeExams->links() }}
                    </div>
                    @endif
                </div>
            </div>

            {{-- Recent Activities --}}
            <div class="card shadow">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Activities</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach($recentActivities as $activity)
                        <div class="timeline-item mb-3">
                            <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                            <p class="mb-1">
                                <strong>{{ $activity->user->name ?? 'System' }}</strong>
                                {{ $activity->description }}
                            </p>
                            @if($activity->metadata)
                            <div class="badge bg-light text-dark">
                                {{ $activity->metadata['exam_code'] ?? '' }}
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Sidebar --}}
        <div class="col-lg-4">
            {{-- Upcoming Sessions --}}
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Upcoming Sessions</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($upcomingSessions as $session)
                        <a href="{{ route('exams.admin.session.show', $session->id) }}" 
                           class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">{{ $session->exam->exam_code }}</h6>
                                <small>{{ \Carbon\Carbon::parse($session->session_date)->format('M d') }}</small>
                            </div>
                            <p class="mb-1 small">
                                {{ $session->center->center_name }}<br>
                                {{ $session->start_time }} - {{ $session->end_time }}
                            </p>
                            <small class="text-muted">
                                <i class="fas fa-users me-1"></i>{{ $session->registered_count }}/{{ $session->capacity }} registered
                            </small>
                        </a>
                        @empty
                        <div class="list-group-item text-center py-4">
                            <i class="fas fa-calendar-times fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">No upcoming sessions</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Quick Stats Chart --}}
            <div class="card shadow mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Registration Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="registrationChart"></canvas>
                </div>
            </div>

            {{-- Pending Tasks --}}
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Pending Tasks</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @if($pendingTasks['question_papers'] > 0)
                        <a href="{{ route('exams.admin.question-papers.pending') }}" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-file-alt me-2"></i>Question Papers to Generate</span>
                                <span class="badge bg-danger rounded-pill">{{ $pendingTasks['question_papers'] }}</span>
                            </div>
                        </a>
                        @endif
                        
                        @if($pendingTasks['hall_tickets'] > 0)
                        <a href="{{ route('exams.admin.hall-tickets.pending') }}" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-ticket-alt me-2"></i>Hall Tickets to Generate</span>
                                <span class="badge bg-warning rounded-pill">{{ $pendingTasks['hall_tickets'] }}</span>
                            </div>
                        </a>
                        @endif
                        
                        @if($pendingTasks['results'] > 0)
                        <a href="{{ route('exams.admin.results.pending') }}" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-poll me-2"></i>Results to Process</span>
                                <span class="badge bg-info rounded-pill">{{ $pendingTasks['results'] }}</span>
                            </div>
                        </a>
                        @endif
                        
                        @if($pendingTasks['evaluations'] > 0)
                        <a href="{{ route('exams.admin.evaluations.pending') }}" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-check-double me-2"></i>Evaluations Pending</span>
                                <span class="badge bg-primary rounded-pill">{{ $pendingTasks['evaluations'] }}</span>
                            </div>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Quick Actions Modal --}}
<div class="modal fade" id="quickActionsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('exams.admin.create') }}" class="btn btn-outline-primary text-start">
                        <i class="fas fa-plus-circle me-2"></i>Create New Exam
                    </a>
                    <a href="{{ route('exams.admin.sessions.create') }}" class="btn btn-outline-info text-start">
                        <i class="fas fa-calendar-plus me-2"></i>Schedule Session
                    </a>
                    <a href="{{ route('exams.admin.centers.index') }}" class="btn btn-outline-secondary text-start">
                        <i class="fas fa-building me-2"></i>Manage Exam Centers
                    </a>
                    <a href="{{ route('exams.admin.questions.bank') }}" class="btn btn-outline-success text-start">
                        <i class="fas fa-database me-2"></i>Question Bank
                    </a>
                    <a href="{{ route('exams.admin.reports.index') }}" class="btn btn-outline-warning text-start">
                        <i class="fas fa-chart-bar me-2"></i>Generate Reports
                    </a>
                    <button type="button" class="btn btn-outline-danger text-start" onclick="bulkGenerateHallTickets()">
                        <i class="fas fa-id-card me-2"></i>Bulk Generate Hall Tickets
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Registration Distribution Chart
    const ctx = document.getElementById('registrationChart').getContext('2d');
    const registrationChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($registrationStats['labels'] ?? []) !!},
            datasets: [{
                data: {!! json_encode($registrationStats['data'] ?? []) !!},
                backgroundColor: [
                    '#007bff',
                    '#28a745',
                    '#ffc107',
                    '#dc3545',
                    '#6c757d'
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

    // Filter exams by type
    document.querySelectorAll('[data-filter]').forEach(button => {
        button.addEventListener('click', function() {
            const filter = this.dataset.filter;
            // Implementation for filtering
            document.querySelectorAll('.btn-group-sm button').forEach(btn => {
                btn.classList.remove('active');
            });
            this.classList.add('active');
            // Add AJAX call to filter table
        });
    });

    // Bulk generate hall tickets
    function bulkGenerateHallTickets() {
        if(confirm('Generate hall tickets for all pending registrations?')) {
            // Implementation for bulk generation
            window.location.href = '{{ route("exams.admin.hall-tickets.bulk-generate") }}';
        }
    }
</script>
@endsection