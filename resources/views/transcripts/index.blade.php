@extends('layouts.app')

@section('title', 'Transcript Services')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-file-alt me-2"></i>Transcript Services
            </h1>
            <nav aria-label="breadcrumb" class="mt-2">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Transcripts</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                View Transcript
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Online</div>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('transcripts.view', $student->id ?? null) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Unofficial Transcript
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Free</div>
                        </div>
                        <div class="col-auto">
                            <form action="{{ route('transcripts.generate-pdf', $student->id ?? null) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="type" value="unofficial">
                                <button type="submit" class="btn btn-info btn-sm">
                                    <i class="fas fa-download"></i> Download
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Official Transcript
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">$10</div>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('transcripts.request') }}" class="btn btn-success btn-sm">
                                <i class="fas fa-file-signature"></i> Request
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending Requests
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $recentRequests->where('status', 'pending')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Information Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-user-graduate me-2"></i>Student Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="fw-bold" width="150">Name:</td>
                                    <td>{{ Auth::user()->name }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Student ID:</td>
                                    <td>{{ $student->student_id ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Program:</td>
                                    <td>{{ $student->program->name ?? $student->major ?? 'Undeclared' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="fw-bold" width="150">Enrollment Status:</td>
                                    <td>
                                        <span class="badge bg-{{ $student->enrollment_status == 'active' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($student->enrollment_status ?? 'Unknown') }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Academic Standing:</td>
                                    <td>{{ $student->academic_standing ?? 'Good Standing' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Current GPA:</td>
                                    <td>{{ number_format($student->cumulative_gpa ?? 0, 2) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transcript Requests -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="fas fa-history me-2"></i>Recent Transcript Requests</h6>
                </div>
                <div class="card-body">
                    @if($recentRequests->isEmpty())
                        <p class="text-muted text-center my-4">No transcript requests found.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Request #</th>
                                        <th>Type</th>
                                        <th>Delivery Method</th>
                                        <th>Recipient</th>
                                        <th>Status</th>
                                        <th>Requested Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentRequests as $request)
                                    <tr>
                                        <td>{{ $request->request_number }}</td>
                                        <td>
                                            <span class="badge bg-{{ $request->type == 'official' ? 'primary' : 'secondary' }}">
                                                {{ ucfirst($request->type) }}
                                            </span>
                                        </td>
                                        <td>{{ ucfirst($request->delivery_method) }}</td>
                                        <td>{{ $request->recipient_name }}</td>
                                        <td>
                                            @switch($request->status)
                                                @case('pending')
                                                    <span class="badge bg-warning">Pending</span>
                                                    @break
                                                @case('processing')
                                                    <span class="badge bg-info">Processing</span>
                                                    @break
                                                @case('completed')
                                                    <span class="badge bg-success">Completed</span>
                                                    @break
                                                @case('cancelled')
                                                    <span class="badge bg-danger">Cancelled</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ $request->status }}</span>
                                            @endswitch
                                        </td>
                                        <td>{{ $request->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('transcripts.request.status', $request) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            @if($request->status == 'pending' && $request->payment_status == 'pending')
                                                <a href="{{ route('transcripts.payment', $request) }}" 
                                                   class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-credit-card"></i> Pay
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Information Cards -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Transcript Types</h6>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold">Unofficial Transcript</h6>
                    <ul class="small">
                        <li>Free of charge</li>
                        <li>Instant download</li>
                        <li>For personal use only</li>
                        <li>Contains watermark</li>
                    </ul>
                    
                    <h6 class="fw-bold mt-3">Official Transcript</h6>
                    <ul class="small">
                        <li>$10 per copy</li>
                        <li>3-5 business days processing</li>
                        <li>Sealed and signed</li>
                        <li>Verification code included</li>
                        <li>Accepted by institutions</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-shipping-fast me-2"></i>Delivery Options</h6>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold">Electronic Delivery</h6>
                    <ul class="small">
                        <li>Sent via secure email</li>
                        <li>Fastest option</li>
                        <li>No additional charge</li>
                    </ul>
                    
                    <h6 class="fw-bold mt-3">Mail Delivery</h6>
                    <ul class="small">
                        <li>Sealed envelope</li>
                        <li>$10 shipping fee</li>
                        <li>5-7 business days</li>
                    </ul>
                    
                    <h6 class="fw-bold mt-3">Pickup</h6>
                    <ul class="small">
                        <li>Available at Registrar's Office</li>
                        <li>No additional charge</li>
                        <li>Photo ID required</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection