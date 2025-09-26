@extends('layouts.app')

@section('title', 'Financial Aid')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-hands-helping me-2"></i>Financial Aid
            </h1>
            <nav aria-label="breadcrumb" class="mt-2">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('financial.student-dashboard') }}">Financial Dashboard</a></li>
                    <li class="breadcrumb-item active">Financial Aid</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Apply for Aid Button -->
    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('financial.financial-aid.apply') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>Apply for Financial Aid
            </a>
        </div>
    </div>

    <!-- Current Aid Awards -->
    @if($aids->where('status', 'awarded')->count() > 0)
    <div class="card shadow mb-4">
        <div class="card-header bg-success text-white">
            <h6 class="mb-0"><i class="fas fa-award me-2"></i>Current Aid Awards</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Aid Type</th>
                            <th>Academic Term</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Disbursement Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($aids->where('status', 'awarded') as $aid)
                        <tr>
                            <td>
                                <strong>{{ $aid->aid_type ?? 'Financial Aid' }}</strong><br>
                                <small class="text-muted">{{ $aid->description ?? '' }}</small>
                            </td>
                            <td>{{ $aid->term->name ?? 'N/A' }}</td>
                            <td class="text-success">
                                <strong>${{ number_format($aid->amount ?? 0, 2) }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-success">Awarded</span>
                            </td>
                            <td>{{ $aid->disbursement_date ? $aid->disbursement_date->format('M d, Y') : 'Pending' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Pending Applications -->
    @if($aids->where('status', 'pending')->count() > 0)
    <div class="card shadow mb-4">
        <div class="card-header bg-warning text-dark">
            <h6 class="mb-0"><i class="fas fa-clock me-2"></i>Pending Applications</h6>
        </div>
        <div class="card-body">
            @foreach($aids->where('status', 'pending') as $aid)
            <div class="alert alert-warning">
                <h6>{{ $aid->aid_type ?? 'Financial Aid Application' }}</h6>
                <p class="mb-1">Amount Requested: ${{ number_format($aid->amount_requested ?? 0, 2) }}</p>
                <p class="mb-1">Term: {{ $aid->term->name ?? 'N/A' }}</p>
                <p class="mb-0"><small>Applied: {{ $aid->created_at->format('M d, Y') }}</small></p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Aid History -->
    <div class="card shadow">
        <div class="card-header">
            <h6 class="mb-0"><i class="fas fa-history me-2"></i>Aid History</h6>
        </div>
        <div class="card-body">
            @if($aids->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Aid Type</th>
                                <th>Term</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($aids as $aid)
                            <tr>
                                <td>{{ $aid->aid_type ?? 'Financial Aid' }}</td>
                                <td>{{ $aid->term->name ?? 'N/A' }}</td>
                                <td>${{ number_format($aid->amount ?? 0, 2) }}</td>
                                <td>
                                    @if($aid->status == 'awarded')
                                        <span class="badge bg-success">Awarded</span>
                                    @elseif($aid->status == 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($aid->status == 'rejected')
                                        <span class="badge bg-danger">Not Approved</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($aid->status) }}</span>
                                    @endif
                                </td>
                                <td>{{ $aid->created_at->format('M d, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>No financial aid history found. 
                    <a href="{{ route('financial.financial-aid.apply') }}">Apply for financial aid</a> to get started.
                </div>
            @endif
        </div>
    </div>

    <!-- Information Cards -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-info-circle me-2"></i>Eligibility Requirements</h5>
                    <ul>
                        <li>Must be enrolled at least half-time (6+ credits)</li>
                        <li>Maintain satisfactory academic progress</li>
                        <li>Complete financial aid application each academic year</li>
                        <li>Submit all required documentation</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-phone me-2"></i>Financial Aid Office</h5>
                    <p><strong>Phone:</strong> (555) 123-4567</p>
                    <p><strong>Email:</strong> financialaid@university.edu</p>
                    <p><strong>Office Hours:</strong> Mon-Fri 8:30 AM - 5:00 PM</p>
                    <p><strong>Location:</strong> Student Services Building, Room 201</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection