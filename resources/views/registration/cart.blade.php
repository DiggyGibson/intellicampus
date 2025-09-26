@extends('layouts.app')

@section('title', 'Registration Cart')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i class="fas fa-chevron-right"></i>
    <a href="{{ route('registration.catalog') }}">Course Catalog</a>
    <i class="fas fa-chevron-right"></i>
    <span>Registration Cart</span>
@endsection

@section('page-actions')
    <a href="{{ route('registration.catalog') }}" class="btn btn-secondary me-2">
        <i class="fas fa-arrow-left me-1"></i> Continue Shopping
    </a>
    <a href="{{ route('registration.schedule') }}" class="btn btn-success me-2">
        <i class="fas fa-calendar-alt me-1"></i> My Schedule
    </a>
    <div class="btn-group" role="group">
        <button type="button" class="btn btn-outline-info dropdown-toggle" data-bs-toggle="dropdown">
            <i class="fas fa-ellipsis-v me-1"></i> More
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('registration.history') }}">
                <i class="fas fa-history me-2"></i>Registration History
            </a></li>
            <li><a class="dropdown-item" href="{{ route('registration.holds') }}">
                <i class="fas fa-ban me-2"></i>View Holds
            </a></li>
            <li><a class="dropdown-item" href="{{ route('registration.waitlist') }}">
                <i class="fas fa-clock me-2"></i>Waitlist Status
            </a></li>
        </ul>
    </div>
@endsection

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="mb-4">
        <h2 class="fw-bold text-dark mb-1">Registration Cart - {{ $currentTerm->name ?? 'Current Term' }}</h2>
        <p class="text-muted">Review and submit your course registration</p>
    </div>
    
    <!-- Registration Period Status -->
    @if(isset($registrationPeriod) && !$registrationPeriod['is_open'])
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ $registrationPeriod['message'] }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    <!-- Registration Holds Alert -->
    @if(count($holds) > 0 || (is_object($holds) && $holds->count() > 0))
        <div class="alert alert-danger" role="alert">
            <h5 class="alert-heading"><i class="fas fa-ban me-2"></i>Registration Holds</h5>
            <p>You have the following holds that must be cleared before registration:</p>
            <hr>
            <ul class="mb-0">
                @foreach($holds as $hold)
                    <li>
                        <strong>{{ ucfirst(str_replace('_', ' ', $hold->hold_type)) }}:</strong> 
                        {{ $hold->reason }}
                        @if($hold->placed_by_department)
                            <span class="text-muted">(Contact: {{ $hold->placed_by_department }})</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Prerequisites Issues Alert -->
    @if(count($prerequisiteIssues) > 0)
        <div class="alert alert-danger" role="alert">
            <h5 class="alert-heading"><i class="fas fa-exclamation-circle me-2"></i>Prerequisite Requirements Not Met</h5>
            <ul class="mb-0">
                @foreach($prerequisiteIssues as $issue)
                    <li>
                        <strong>{{ $issue['course'] }}:</strong> {{ $issue['message'] }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Time Conflicts Alert -->
    @if(count($conflicts) > 0)
        <div class="alert alert-danger" role="alert">
            <h5 class="alert-heading"><i class="fas fa-clock me-2"></i>Schedule Conflicts Detected</h5>
            <ul class="mb-0">
                @foreach($conflicts as $conflict)
                    <li>
                        <strong>{{ $conflict['new_section']['code'] }}</strong> conflicts with 
                        <strong>{{ $conflict['existing_section']['code'] }}</strong> on 
                        {{ $conflict['conflict_days'] }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Credit Validation Alert -->
    @if(isset($creditValidation) && !$creditValidation['valid'])
        <div class="alert alert-danger" role="alert">
            <h5 class="alert-heading"><i class="fas fa-calculator me-2"></i>Credit Limit Issues</h5>
            <ul class="mb-0">
                @foreach($creditValidation['issues'] as $issue)
                    <li>
                        {{ $issue['message'] }}
                        @if(isset($issue['suggestion']))
                            <br><small class="text-muted">→ {{ $issue['suggestion'] }}</small>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Credit Warnings -->
    @if(isset($creditValidation['warnings']) && count($creditValidation['warnings']) > 0)
        <div class="alert alert-warning" role="alert">
            <h5 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Registration Warnings</h5>
            <ul class="mb-0">
                @foreach($creditValidation['warnings'] as $warning)
                    <li>
                        @if(isset($warning['course']))
                            <strong>{{ $warning['course'] }}:</strong>
                        @endif
                        {{ $warning['message'] }}
                        @if(isset($warning['note']))
                            <br><small class="fst-italic">{{ $warning['note'] }}</small>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Credit Summary -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-3">
                    <div class="stat-box">
                        <small class="text-muted">Current Credits</small>
                        <div class="fs-3 fw-bold">{{ $creditValidation['current_credits'] ?? 0 }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box">
                        <small class="text-muted">Cart Credits</small>
                        <div class="fs-3 fw-bold text-primary">{{ $creditValidation['new_credits'] ?? 0 }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box">
                        <small class="text-muted">Total Credits</small>
                        <div class="fs-3 fw-bold {{ ($creditValidation['total_credits'] ?? 0) > 18 ? 'text-danger' : 'text-success' }}">
                            {{ $creditValidation['total_credits'] ?? 0 }}
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box">
                        <small class="text-muted">Credit Limit</small>
                        <div class="fs-3 fw-bold">
                            {{ $creditValidation['min_credits'] ?? 12 }} - {{ $creditValidation['max_credits'] ?? 18 }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Shopping Cart Items -->
    <div class="card shadow-sm">
        <div class="card-header bg-gradient-primary text-white py-3">
            <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Courses in Cart</h5>
        </div>
        
        @if($sections->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Course</th>
                            <th>Section</th>
                            <th>Credits</th>
                            <th>Schedule</th>
                            <th>Instructor</th>
                            <th>Seats</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sections as $section)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $section->code }}</div>
                                    <small class="text-muted">{{ $section->title }}</small>
                                </td>
                                <td>{{ $section->section_number }}</td>
                                <td>{{ $section->credits }}</td>
                                <td>
                                    @if($section->days_of_week && $section->start_time)
                                        <div>{{ $section->days_of_week }}</div>
                                        <small>
                                            {{ \Carbon\Carbon::parse($section->start_time)->format('g:i A') }} -
                                            {{ \Carbon\Carbon::parse($section->end_time)->format('g:i A') }}
                                        </small>
                                    @else
                                        <span class="text-muted">Online/Async</span>
                                    @endif
                                </td>
                                <td>{{ $section->instructor_name ?? 'TBA' }}</td>
                                <td>
                                    @if($section->available_seats > 0)
                                        <span class="badge bg-success">
                                            {{ $section->available_seats }} available
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            Full (Waitlist)
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <form method="POST" action="{{ route('registration.cart.remove') }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="section_id" value="{{ $section->id }}">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i> Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Registration Actions -->
            <div class="card-footer bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted">Total courses:</span> 
                        <span class="fw-bold">{{ $sections->count() }}</span>
                        <span class="mx-2">|</span>
                        <span class="text-muted">Total credits:</span> 
                        <span class="fw-bold">{{ $creditValidation['new_credits'] ?? 0 }}</span>
                    </div>
                    
                    <div>
                        @if(isset($canRegister) && $canRegister)
                            <form method="POST" action="{{ route('registration.submit') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-check-circle me-1"></i> Submit Registration
                                </button>
                            </form>
                        @else
                            <button disabled class="btn btn-secondary">
                                <i class="fas fa-lock me-1"></i> Cannot Register (Fix Issues Above)
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="card-body text-center py-5">
                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Your cart is empty</h5>
                <p class="text-muted">Start by browsing available courses.</p>
                <a href="{{ route('registration.catalog') }}" class="btn btn-primary mt-3">
                    <i class="fas fa-book me-1"></i> Browse Courses
                </a>
            </div>
        @endif
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
}
.stat-box {
    padding: 1rem;
    border-radius: 0.5rem;
    background: #f8f9fa;
    transition: transform 0.2s;
}
.stat-box:hover {
    transform: translateY(-2px);
}
</style>
@endsection