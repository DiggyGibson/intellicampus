{{-- File: C:\IntelliCampus\resources\views\academic-plans\index.blade.php --}}
{{-- URL: /academic-plans --}}
@extends('layouts.app')

@section('title', 'Academic Plans')

@section('styles')
<style>
    .plan-card {
        border-radius: 12px;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .plan-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .plan-card.active {
        border: 2px solid #667eea;
        background: linear-gradient(135deg, #667eea10, #764ba210);
    }
    
    .plan-status {
        position: absolute;
        top: 10px;
        right: 10px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2>Academic Plans</h2>
                    <p class="text-muted mb-0">Manage and track your academic journey</p>
                </div>
                <div>
                    <a href="{{ route('academic-plans.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create New Plan
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-3">
            <a href="{{ route('academic-plans.planner') }}" class="text-decoration-none">
                <div class="card text-center plan-card">
                    <div class="card-body">
                        <i class="fas fa-calendar-alt fa-3x text-primary mb-3"></i>
                        <h5>4-Year Planner</h5>
                        <p class="text-muted mb-0">Plan your entire degree</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('academic-plans.course-sequence') }}" class="text-decoration-none">
                <div class="card text-center plan-card">
                    <div class="card-body">
                        <i class="fas fa-project-diagram fa-3x text-info mb-3"></i>
                        <h5>Course Sequence</h5>
                        <p class="text-muted mb-0">View prerequisites map</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('degree-audit.what-if') }}" class="text-decoration-none">
                <div class="card text-center plan-card">
                    <div class="card-body">
                        <i class="fas fa-exchange-alt fa-3x text-warning mb-3"></i>
                        <h5>What-If Analysis</h5>
                        <p class="text-muted mb-0">Explore alternatives</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('registration.schedule') }}" class="text-decoration-none">
                <div class="card text-center plan-card">
                    <div class="card-body">
                        <i class="fas fa-clock fa-3x text-success mb-3"></i>
                        <h5>Current Schedule</h5>
                        <p class="text-muted mb-0">View this term</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Saved Plans -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Your Saved Plans</h5>
                </div>
                <div class="card-body">
                    @forelse($plans ?? [] as $plan)
                    <div class="plan-card card mb-3 {{ $plan->is_active ? 'active' : '' }}">
                        <div class="card-body">
                            <span class="plan-status badge bg-{{ $plan->is_active ? 'success' : 'secondary' }}">
                                {{ $plan->is_active ? 'Active' : 'Draft' }}
                            </span>
                            <h6>{{ $plan->name }}</h6>
                            <p class="text-muted mb-2">{{ $plan->description }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    Created: {{ $plan->created_at->format('M d, Y') }} |
                                    {{ $plan->total_credits ?? 0 }} credits planned
                                </small>
                                <div>
                                    <a href="{{ route('academic-plans.show', $plan->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="{{ route('academic-plans.edit', $plan->id) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5">
                        <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                        <p class="text-muted">No saved plans yet</p>
                        <a href="{{ route('academic-plans.planner') }}" class="btn btn-primary">
                            Create Your First Plan
                        </a>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection