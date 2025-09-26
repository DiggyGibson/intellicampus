@extends('layouts.app')

@section('title', 'Registration Holds')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i class="fas fa-chevron-right"></i>
    <a href="{{ route('registration.catalog') }}">Course Catalog</a>
    <i class="fas fa-chevron-right"></i>
    <span>Registration Holds</span>
@endsection

@section('page-actions')
    <a href="{{ route('registration.catalog') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Catalog
    </a>
@endsection

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="mb-4">
        <h2 class="fw-bold text-dark mb-1">Registration Holds</h2>
        <p class="text-muted">View and resolve any holds on your account</p>
    </div>

    <!-- Holds Content -->
    <div class="card shadow-sm mb-4">
        @if($holds->isEmpty())
            <!-- No Holds -->
            <div class="card-body text-center py-5">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success mb-4" 
                     style="width: 80px; height: 80px;">
                    <i class="fas fa-check-circle fa-3x text-white"></i>
                </div>
                <h4 class="text-success">No Registration Holds</h4>
                <p class="text-muted">You have no holds preventing registration</p>
                <a href="{{ route('registration.catalog') }}" class="btn btn-primary mt-3">
                    <i class="fas fa-book me-1"></i> Continue to Course Catalog
                </a>
            </div>
        @else
            <!-- Active Holds Alert -->
            <div class="card-header bg-danger text-white">
                <div class="d-flex align-items-center">
                    <i class="fas fa-ban fa-2x me-3"></i>
                    <div>
                        <h5 class="mb-0">You have {{ $holds->count() }} active hold(s)</h5>
                        <small>Registration is blocked until all holds are resolved</small>
                    </div>
                </div>
            </div>

            <!-- Holds Table -->
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Hold Type</th>
                            <th>Reason</th>
                            <th>Description</th>
                            <th>Placed Date</th>
                            <th>Placed By</th>
                            <th>Action Required</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($holds as $hold)
                            <tr>
                                <td>
                                    @php
                                        $typeClass = match($hold->hold_type) {
                                            'financial' => 'bg-warning',
                                            'academic' => 'bg-danger',
                                            'administrative' => 'bg-purple',
                                            'health' => 'bg-info',
                                            default => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $typeClass }}">
                                        {{ ucfirst($hold->hold_type) }}
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ $hold->reason }}</strong>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $hold->description }}</small>
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($hold->placed_date)->format('M d, Y') }}
                                </td>
                                <td>
                                    {{ $hold->placed_by_name ?? 'System' }}
                                </td>
                                <td>
                                    @switch($hold->hold_type)
                                        @case('financial')
                                            <a href="#financial-aid" class="btn btn-sm btn-outline-warning">
                                                <i class="fas fa-dollar-sign me-1"></i> Visit Financial Aid
                                            </a>
                                            @break
                                        @case('academic')
                                            <a href="#advisor" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-user-tie me-1"></i> Contact Advisor
                                            </a>
                                            @break
                                        @case('administrative')
                                            <a href="#registrar" class="btn btn-sm btn-outline-purple">
                                                <i class="fas fa-building me-1"></i> Visit Registrar
                                            </a>
                                            @break
                                        @case('health')
                                            <a href="#health" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-heartbeat me-1"></i> Health Center
                                            </a>
                                            @break
                                        @default
                                            <span class="text-muted">Contact Admin</span>
                                    @endswitch
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Contact Information -->
    @if(!$holds->isEmpty())
        <div class="card shadow-sm">
            <div class="card-header bg-gradient-info text-white py-3">
                <h5 class="mb-0"><i class="fas fa-phone-alt me-2"></i>Contact Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-4" id="financial-aid">
                        <div class="contact-card h-100">
                            <div class="contact-icon bg-warning">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <h5 class="mt-3 mb-2">Financial Aid Office</h5>
                            <ul class="list-unstyled small">
                                <li><i class="fas fa-phone me-2 text-muted"></i> (555) 123-4567</li>
                                <li><i class="fas fa-envelope me-2 text-muted"></i> financialaid@intellicampus.edu</li>
                                <li><i class="fas fa-clock me-2 text-muted"></i> Mon-Fri 8:00 AM - 5:00 PM</li>
                                <li><i class="fas fa-map-marker-alt me-2 text-muted"></i> Admin Building, Room 102</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4" id="advisor">
                        <div class="contact-card h-100">
                            <div class="contact-icon bg-danger">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <h5 class="mt-3 mb-2">Academic Advising</h5>
                            <ul class="list-unstyled small">
                                <li><i class="fas fa-phone me-2 text-muted"></i> (555) 123-4568</li>
                                <li><i class="fas fa-envelope me-2 text-muted"></i> advising@intellicampus.edu</li>
                                <li><i class="fas fa-clock me-2 text-muted"></i> Mon-Fri 9:00 AM - 4:00 PM</li>
                                <li><i class="fas fa-map-marker-alt me-2 text-muted"></i> Student Services, Room 205</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4" id="registrar">
                        <div class="contact-card h-100">
                            <div class="contact-icon bg-purple">
                                <i class="fas fa-building"></i>
                            </div>
                            <h5 class="mt-3 mb-2">Registrar's Office</h5>
                            <ul class="list-unstyled small">
                                <li><i class="fas fa-phone me-2 text-muted"></i> (555) 123-4569</li>
                                <li><i class="fas fa-envelope me-2 text-muted"></i> registrar@intellicampus.edu</li>
                                <li><i class="fas fa-clock me-2 text-muted"></i> Mon-Fri 8:30 AM - 4:30 PM</li>
                                <li><i class="fas fa-map-marker-alt me-2 text-muted"></i> Admin Building, Room 101</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4" id="health">
                        <div class="contact-card h-100">
                            <div class="contact-icon bg-info">
                                <i class="fas fa-heartbeat"></i>
                            </div>
                            <h5 class="mt-3 mb-2">Health Center</h5>
                            <ul class="list-unstyled small">
                                <li><i class="fas fa-phone me-2 text-muted"></i> (555) 123-4570</li>
                                <li><i class="fas fa-envelope me-2 text-muted"></i> healthcenter@intellicampus.edu</li>
                                <li><i class="fas fa-clock me-2 text-muted"></i> Mon-Fri 8:00 AM - 6:00 PM</li>
                                <li><i class="fas fa-map-marker-alt me-2 text-muted"></i> Health Services Building</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resolution Steps -->
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-gradient-success text-white py-3">
                <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>How to Resolve Holds</h5>
            </div>
            <div class="card-body">
                <div class="timeline-horizontal">
                    <div class="timeline-step">
                        <div class="step-number">1</div>
                        <h6>Identify Hold Type</h6>
                        <small class="text-muted">Review the hold details above</small>
                    </div>
                    <div class="timeline-step">
                        <div class="step-number">2</div>
                        <h6>Contact Department</h6>
                        <small class="text-muted">Use contact info provided</small>
                    </div>
                    <div class="timeline-step">
                        <div class="step-number">3</div>
                        <h6>Complete Requirements</h6>
                        <small class="text-muted">Submit documents or payments</small>
                    </div>
                    <div class="timeline-step">
                        <div class="step-number">4</div>
                        <h6>Wait for Clearance</h6>
                        <small class="text-muted">Usually 24-48 hours</small>
                    </div>
                    <div class="timeline-step">
                        <div class="step-number">5</div>
                        <h6>Register for Classes</h6>
                        <small class="text-muted">Continue with registration</small>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
.bg-gradient-info {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
}
.bg-gradient-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}
.bg-purple {
    background-color: #6f42c1;
}
.btn-outline-purple {
    color: #6f42c1;
    border-color: #6f42c1;
}
.btn-outline-purple:hover {
    background-color: #6f42c1;
    border-color: #6f42c1;
    color: white;
}
.contact-card {
    padding: 1.5rem;
    border-radius: 0.5rem;
    background: #f8f9fa;
    transition: transform 0.2s;
}
.contact-card:hover {
    transform: translateY(-2px);
}
.contact-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}
.timeline-horizontal {
    display: flex;
    justify-content: space-between;
    position: relative;
    padding: 0 20px;
}
.timeline-horizontal::before {
    content: '';
    position: absolute;
    top: 30px;
    left: 50px;
    right: 50px;
    height: 2px;
    background: #dee2e6;
}
.timeline-step {
    text-align: center;
    position: relative;
    flex: 1;
}
.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #10b981;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
    font-weight: bold;
    position: relative;
    z-index: 1;
}
</style>
@endsection