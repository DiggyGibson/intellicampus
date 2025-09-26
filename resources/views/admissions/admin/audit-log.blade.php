{{-- File: resources/views/admissions/admin/audit-log.blade.php --}}
@extends('layouts.app')

@section('title', 'Application History - ' . $application->application_number)

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">Application History</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.admissions.dashboard') }}">Admissions</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.admissions.applications.index') }}">Applications</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.admissions.applications.show', $application->id) }}">{{ $application->application_number }}</a></li>
                            <li class="breadcrumb-item active">History</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('admin.admissions.applications.show', $application->id) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Application
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Application Info Bar --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body py-3">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <strong>Application:</strong> {{ $application->application_number }}
                        </div>
                        <div class="col-md-3">
                            <strong>Applicant:</strong> {{ $application->first_name }} {{ $application->last_name }}
                        </div>
                        <div class="col-md-3">
                            <strong>Status:</strong> 
                            <span class="badge bg-{{ $application->status == 'admitted' ? 'success' : 'info' }}">
                                {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                            </span>
                        </div>
                        <div class="col-md-3">
                            <strong>Submitted:</strong> {{ $application->created_at->format('M d, Y') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Timeline --}}
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Activity Timeline</h5>
                </div>
                <div class="card-body">
                    @if($timeline->count() > 0)
                        <div class="timeline-container">
                            @foreach($timeline as $event)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-{{ $event['color'] }}">
                                    <i class="{{ $event['icon'] }} text-white"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1">{{ $event['title'] }}</h6>
                                                    <p class="mb-2">{{ $event['description'] }}</p>
                                                    @if(isset($event['notes']) && $event['notes'])
                                                        <p class="text-muted small mb-1">
                                                            <em>Notes: {{ $event['notes'] }}</em>
                                                        </p>
                                                    @endif
                                                    <small class="text-muted">
                                                        <i class="fas fa-user"></i> {{ $event['user'] }}
                                                        <i class="fas fa-clock ms-2"></i> 
                                                        {{ \Carbon\Carbon::parse($event['date'])->format('M d, Y H:i') }}
                                                        ({{ \Carbon\Carbon::parse($event['date'])->diffForHumans() }})
                                                    </small>
                                                </div>
                                                <span class="badge bg-{{ $event['color'] }}">
                                                    {{ str_replace('_', ' ', ucfirst($event['type'])) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No activity history available for this application.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline-container {
    position: relative;
    padding-left: 50px;
}

.timeline-container::before {
    content: '';
    position: absolute;
    left: 20px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    top: 0;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #6c757d;
    z-index: 1;
}

.timeline-content {
    padding-left: 20px;
}
</style>
@endsection