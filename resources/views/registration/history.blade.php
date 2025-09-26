@extends('layouts.app')

@section('title', 'Registration History')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i class="fas fa-chevron-right"></i>
    <a href="{{ route('registration.catalog') }}">Course Catalog</a>
    <i class="fas fa-chevron-right"></i>
    <span>Registration History</span>
@endsection

@section('page-actions')
    <a href="{{ route('registration.catalog') }}" class="btn btn-primary me-2">
        <i class="fas fa-plus me-1"></i> Browse Catalog
    </a>
    <a href="{{ route('registration.schedule') }}" class="btn btn-success">
        <i class="fas fa-calendar-alt me-1"></i> My Schedule
    </a>
@endsection

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="mb-4">
        <h2 class="fw-bold text-dark mb-1">Registration History</h2>
        <p class="text-muted">View all your registration activities</p>
    </div>

    <!-- History Content -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-gradient-primary text-white py-3">
            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Activity Timeline</h5>
        </div>
        <div class="card-body">
            @if($history->isEmpty())
                <!-- No History -->
                <div class="text-center py-5">
                    <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Registration History</h5>
                    <p class="text-muted">You haven't performed any registration activities yet</p>
                    <a href="{{ route('registration.catalog') }}" class="btn btn-primary mt-3">
                        <i class="fas fa-book me-1"></i> Start Registration
                    </a>
                </div>
            @else
                <!-- Filters -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <select onchange="filterHistory(this.value, 'action')" class="form-select">
                            <option value="">All Actions</option>
                            <option value="added_to_cart">Added to Cart</option>
                            <option value="removed_from_cart">Removed from Cart</option>
                            <option value="enrolled">Enrolled</option>
                            <option value="dropped">Dropped</option>
                            <option value="waitlisted">Waitlisted</option>
                            <option value="withdrawn">Withdrawn</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <select onchange="filterHistory(this.value, 'term')" class="form-select">
                            <option value="">All Terms</option>
                            @php
                                $terms = $history->pluck('term_name')->unique()->filter();
                            @endphp
                            @foreach($terms as $term)
                                <option value="{{ $term }}">{{ $term }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- History Timeline -->
                <div class="timeline">
                    @foreach($history as $index => $entry)
                        <div class="timeline-item">
                            <div class="timeline-marker 
                                {{ in_array($entry->action, ['enrolled', 'added_to_cart']) ? 'bg-success' : '' }}
                                {{ in_array($entry->action, ['dropped', 'removed_from_cart', 'withdrawn']) ? 'bg-danger' : '' }}
                                {{ in_array($entry->action, ['waitlisted', 'offered']) ? 'bg-warning' : '' }}
                                {{ !in_array($entry->action, ['enrolled', 'added_to_cart', 'dropped', 'removed_from_cart', 'withdrawn', 'waitlisted', 'offered']) ? 'bg-secondary' : '' }}">
                                @switch($entry->action)
                                    @case('enrolled')
                                    @case('added_to_cart')
                                        <i class="fas fa-plus text-white"></i>
                                        @break
                                    @case('dropped')
                                    @case('removed_from_cart')
                                    @case('withdrawn')
                                        <i class="fas fa-minus text-white"></i>
                                        @break
                                    @case('waitlisted')
                                        <i class="fas fa-clock text-white"></i>
                                        @break
                                    @default
                                        <i class="fas fa-info text-white"></i>
                                @endswitch
                            </div>
                            <div class="timeline-content">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">
                                            <span class="badge 
                                                {{ in_array($entry->action, ['enrolled', 'added_to_cart']) ? 'bg-success' : '' }}
                                                {{ in_array($entry->action, ['dropped', 'removed_from_cart', 'withdrawn']) ? 'bg-danger' : '' }}
                                                {{ in_array($entry->action, ['waitlisted', 'offered']) ? 'bg-warning' : '' }}
                                                {{ !in_array($entry->action, ['enrolled', 'added_to_cart', 'dropped', 'removed_from_cart', 'withdrawn', 'waitlisted', 'offered']) ? 'bg-secondary' : '' }}">
                                                {{ ucfirst(str_replace('_', ' ', $entry->action)) }}
                                            </span>
                                        </h6>
                                        <p class="mb-1">
                                            <strong>{{ $entry->course_code }}</strong> - {{ $entry->course_title }}
                                            @if($entry->section_number)
                                                (Section {{ $entry->section_number }})
                                            @endif
                                        </p>
                                        @if($entry->details)
                                            <p class="text-muted small mb-0">{{ $entry->details }}</p>
                                        @endif
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($entry->created_at)->format('M d, Y') }}
                                            <br>
                                            {{ \Carbon\Carbon::parse($entry->created_at)->format('g:i A') }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $history->withQueryString()->links('custom.pagination') }}
                </div>
            @endif
        </div>
    </div>

    <!-- Statistics Card -->
    @if(!$history->isEmpty())
        <div class="card shadow-sm">
            <div class="card-header bg-gradient-info text-white py-3">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Registration Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="stat-box">
                            <div class="fs-3 fw-bold text-success">
                                {{ $history->where('action', 'enrolled')->count() }}
                            </div>
                            <small class="text-muted">Courses Enrolled</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-box">
                            <div class="fs-3 fw-bold text-danger">
                                {{ $history->where('action', 'dropped')->count() }}
                            </div>
                            <small class="text-muted">Courses Dropped</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-box">
                            <div class="fs-3 fw-bold text-warning">
                                {{ $history->where('action', 'waitlisted')->count() }}
                            </div>
                            <small class="text-muted">Waitlisted</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-box">
                            <div class="fs-3 fw-bold text-primary">
                                {{ $history->count() }}
                            </div>
                            <small class="text-muted">Total Activities</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
}
.bg-gradient-info {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
}
.stat-box {
    padding: 1.5rem;
    border-radius: 0.5rem;
    background: #f8f9fa;
    transition: transform 0.2s;
}
.stat-box:hover {
    transform: translateY(-2px);
}
.timeline {
    position: relative;
    padding-left: 40px;
}
.timeline:before {
    content: '';
    position: absolute;
    left: 15px;
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
    left: -25px;
    top: 0;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 3px solid #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.timeline-content {
    background: #fff;
    padding: 15px;
    border-radius: 0.5rem;
    border: 1px solid #dee2e6;
}
</style>

<script>
function filterHistory(value, type) {
    const url = new URL(window.location);
    if (value) {
        url.searchParams.set(type, value);
    } else {
        url.searchParams.delete(type);
    }
    window.location = url.toString();
}
</script>
@endsection