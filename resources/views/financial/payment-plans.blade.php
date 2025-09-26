@extends('layouts.app')

@section('title', 'Payment Plans')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-calendar-alt me-2"></i>Payment Plans
            </h1>
            <nav aria-label="breadcrumb" class="mt-2">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('financial.student-dashboard') }}">Financial Dashboard</a></li>
                    <li class="breadcrumb-item active">Payment Plans</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Request Payment Plan Button -->
    <div class="row mb-4">
        <div class="col-12">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#requestPaymentPlanModal">
                <i class="fas fa-plus-circle me-2"></i>Request New Payment Plan
            </button>
        </div>
    </div>

    <!-- Active Payment Plans -->
    @if($plans->where('status', 'active')->count() > 0)
    <div class="card shadow mb-4">
        <div class="card-header bg-success text-white">
            <h6 class="mb-0"><i class="fas fa-check-circle me-2"></i>Active Payment Plans</h6>
        </div>
        <div class="card-body">
            @foreach($plans->where('status', 'active') as $plan)
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5>{{ $plan->plan_name ?? 'Payment Plan' }}</h5>
                            <p class="text-muted mb-2">
                                <i class="fas fa-calendar me-2"></i>Duration: {{ $plan->start_date }} to {{ $plan->end_date }}
                            </p>
                            <div class="progress mb-2" style="height: 20px;">
                                @php
                                    $progress = ($plan->amount_paid ?? 0) / ($plan->total_amount ?? 1) * 100;
                                @endphp
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: {{ $progress }}%"
                                     aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                                    {{ number_format($progress, 0) }}%
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <h6>Total: ${{ number_format($plan->total_amount ?? 0, 2) }}</h6>
                            <p class="text-success">Paid: ${{ number_format($plan->amount_paid ?? 0, 2) }}</p>
                            <p class="text-danger">Remaining: ${{ number_format(($plan->total_amount ?? 0) - ($plan->amount_paid ?? 0), 2) }}</p>
                            <a href="{{ route('financial.payment-plan.view', $plan->id) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-eye me-1"></i>View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Pending Payment Plans -->
    @if($plans->where('status', 'pending')->count() > 0)
    <div class="card shadow mb-4">
        <div class="card-header bg-warning text-dark">
            <h6 class="mb-0"><i class="fas fa-clock me-2"></i>Pending Approval</h6>
        </div>
        <div class="card-body">
            @foreach($plans->where('status', 'pending') as $plan)
            <div class="alert alert-warning">
                <h6>{{ $plan->plan_name ?? 'Payment Plan' }}</h6>
                <p class="mb-1">Amount: ${{ number_format($plan->total_amount ?? 0, 2) }}</p>
                <p class="mb-1">Installments: {{ $plan->number_of_installments ?? 0 }}</p>
                <p class="mb-0"><small>Submitted: {{ $plan->created_at->format('M d, Y') }}</small></p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Completed Payment Plans -->
    @if($plans->where('status', 'completed')->count() > 0)
    <div class="card shadow">
        <div class="card-header">
            <h6 class="mb-0"><i class="fas fa-history me-2"></i>Completed Payment Plans</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Plan Name</th>
                            <th>Amount</th>
                            <th>Duration</th>
                            <th>Completed Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($plans->where('status', 'completed') as $plan)
                        <tr>
                            <td>{{ $plan->plan_name ?? 'Payment Plan' }}</td>
                            <td>${{ number_format($plan->total_amount ?? 0, 2) }}</td>
                            <td>{{ $plan->start_date }} - {{ $plan->end_date }}</td>
                            <td>{{ $plan->completed_at ?? $plan->updated_at->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('financial.payment-plan.view', $plan->id) }}" class="btn btn-sm btn-outline-info">
                                    View
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    @if($plans->count() == 0)
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>You don't have any payment plans. Click the button above to request one.
    </div>
    @endif
</div>

<!-- Request Payment Plan Modal -->
<div class="modal fade" id="requestPaymentPlanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('financial.request-payment-plan') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Request Payment Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="term_id" class="form-label">Academic Term</label>
                        <select class="form-select" name="term_id" required>
                            <option value="1">Spring 2025</option>
                            <option value="2">Fall 2025</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="number_of_installments" class="form-label">Number of Installments</label>
                        <select class="form-select" name="number_of_installments" required>
                            <option value="2">2 Installments</option>
                            <option value="3">3 Installments</option>
                            <option value="4">4 Installments</option>
                            <option value="6">6 Installments</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" name="start_date" 
                               min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection