{{-- ============================================================ --}}
{{-- 1. resources/views/registrar/dashboard.blade.php --}}
{{-- ============================================================ --}}
@extends('layouts.app')

@section('title', 'Registrar Dashboard')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">Registrar Dashboard</h1>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                        Total Students
                    </div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['total_students'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                        Active Enrollments
                    </div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['active_enrollments'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                        Pending Transcripts
                    </div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['pending_transcripts'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                        Pending Grades
                    </div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['pending_grades'] ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Requests -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Recent Transcript Requests</h5>
        </div>
        <div class="card-body">
            @if(isset($recentRequests) && $recentRequests->count() > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentRequests as $request)
                            <tr>
                                <td>{{ $request->student->name ?? 'N/A' }}</td>
                                <td>{{ $request->type ?? 'Official' }}</td>
                                <td>
                                    <span class="badge bg-warning">{{ $request->status ?? 'Pending' }}</span>
                                </td>
                                <td>{{ $request->created_at->format('Y-m-d') }}</td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-primary">View</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted">No recent transcript requests.</p>
            @endif
        </div>
    </div>
</div>
@endsection