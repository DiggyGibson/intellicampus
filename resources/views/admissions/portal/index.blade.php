{{-- resources/views/admissions/portal/index.blade.php --}}
@extends('layouts.portal')

@section('title', 'Admissions Portal - Apply to IntelliCampus')

@section('content')
<div class="container-fluid py-4">
    {{-- Hero Section --}}
    <div class="row mb-5">
        <div class="col-12">
            <div class="card bg-primary text-white shadow-lg">
                <div class="card-body p-5">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <h1 class="display-4 font-weight-bold mb-3">Welcome to IntelliCampus Admissions</h1>
                            <p class="lead mb-4">Start your journey towards academic excellence. Apply for admission to our world-class programs.</p>
                            
                            @if($currentTerm)
                                <div class="mb-4">
                                    <span class="badge bg-warning text-dark px-3 py-2 me-3">
                                        <i class="fas fa-calendar-alt me-1"></i> 
                                        Applications Open for {{ $currentTerm->name }}
                                    </span>
                                    <span class="badge bg-light text-dark px-3 py-2">
                                        <i class="fas fa-clock me-1"></i> 
                                        Deadline: {{ $currentTerm->admission_deadline->format('F d, Y') }}
                                    </span>
                                </div>
                            @endif
                            
                            <div class="d-flex gap-3 flex-wrap">
                                {{-- Start New Application --}}
                                <a href="{{ route('admissions.portal.start') }}" class="btn btn-light btn-lg">
                                    <i class="fas fa-plus-circle me-2"></i> Start New Application
                                </a>
                                
                                {{-- Continue Application (with UUID) --}}
                                <button type="button" class="btn btn-warning btn-lg" data-bs-toggle="modal" data-bs-target="#continueModal">
                                    <i class="fas fa-file-alt me-2"></i> Continue Application
                                </button>
                                
                                {{-- Check Status --}}
                                <a href="{{ route('admissions.portal.status') }}" class="btn btn-outline-light btn-lg">
                                    <i class="fas fa-search me-2"></i> Check Status
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-4 text-center d-none d-lg-block">
                            <i class="fas fa-graduation-cap" style="font-size: 150px; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Applications (from session) --}}
    @if(session('recent_applications'))
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <h6 class="alert-heading"><i class="fas fa-history me-2"></i>Your Recent Applications</h6>
                <div class="mt-2">
                    @foreach(session('recent_applications') as $app)
                        <a href="{{ route('admissions.portal.continue', $app['uuid']) }}" class="btn btn-sm btn-primary me-2 mb-2">
                            <i class="fas fa-arrow-right me-1"></i> Continue: {{ $app['number'] }}
                        </a>
                    @endforeach
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    </div>
    @endif

    {{-- Application UUID Info Box --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <i class="fas fa-info-circle fa-2x"></i>
                    </div>
                    <div class="col">
                        <strong>Important Information:</strong> When you start an application, you'll receive a unique UUID (application identifier). 
                        Save this UUID to continue your application later. No account creation required!
                        <a href="#" data-bs-toggle="modal" data-bs-target="#lostUUIDModal" class="text-decoration-underline">Lost your UUID?</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Access Section --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="fas fa-fast-forward me-2"></i>Quick Access</h5>
                    <form action="{{ route('admissions.portal.find') }}" method="POST" class="row g-3">
                        @csrf
                        <div class="col-md-5">
                            <input type="email" class="form-control" name="email" placeholder="Enter your email address" required>
                        </div>
                        <div class="col-md-5">
                            <input type="text" class="form-control" name="reference" placeholder="Enter UUID or Application Number" required>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Find Application</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Stats --}}
    @if($stats)
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Applications
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_applications']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Programs Available
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['programs_available'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-book fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Days Until Deadline
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['deadline'] ? $stats['deadline']->diffInDays(now()) : 'N/A' }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hourglass-half fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Spots Available
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['spots_available'] ?? 'Open' }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Application Process Steps --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-white">
                    <h5 class="mb-0 text-primary">
                        <i class="fas fa-list-ol me-2"></i> Application Process
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-2 mb-3">
                            <div class="process-step">
                                <div class="step-icon bg-primary text-white rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; line-height: 60px;">
                                    <i class="fas fa-rocket fa-lg"></i>
                                </div>
                                <h6>1. Start Application</h6>
                                <small class="text-muted">Begin with basic info</small>
                            </div>
                        </div>
                        
                        <div class="col-md-2 mb-3">
                            <div class="process-step">
                                <div class="step-icon bg-info text-white rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; line-height: 60px;">
                                    <i class="fas fa-edit fa-lg"></i>
                                </div>
                                <h6>2. Fill Application</h6>
                                <small class="text-muted">Complete all sections</small>
                            </div>
                        </div>
                        
                        <div class="col-md-2 mb-3">
                            <div class="process-step">
                                <div class="step-icon bg-success text-white rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; line-height: 60px;">
                                    <i class="fas fa-upload fa-lg"></i>
                                </div>
                                <h6>3. Upload Documents</h6>
                                <small class="text-muted">Transcripts & certificates</small>
                            </div>
                        </div>
                        
                        <div class="col-md-2 mb-3">
                            <div class="process-step">
                                <div class="step-icon bg-warning text-white rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; line-height: 60px;">
                                    <i class="fas fa-dollar-sign fa-lg"></i>
                                </div>
                                <h6>4. Pay Fee</h6>
                                <small class="text-muted">Application fee payment</small>
                            </div>
                        </div>
                        
                        <div class="col-md-2 mb-3">
                            <div class="process-step">
                                <div class="step-icon bg-danger text-white rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; line-height: 60px;">
                                    <i class="fas fa-paper-plane fa-lg"></i>
                                </div>
                                <h6>5. Submit</h6>
                                <small class="text-muted">Final submission</small>
                            </div>
                        </div>
                        
                        <div class="col-md-2 mb-3">
                            <div class="process-step">
                                <div class="step-icon bg-secondary text-white rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; line-height: 60px;">
                                    <i class="fas fa-check fa-lg"></i>
                                </div>
                                <h6>6. Track Status</h6>
                                <small class="text-muted">Monitor progress</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Available Programs --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-primary">
                        <i class="fas fa-graduation-cap me-2"></i> Available Programs
                    </h5>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-primary active" data-filter="all">All</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-filter="undergraduate">Undergraduate</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-filter="graduate">Graduate</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row" id="programs-list">
                        @foreach($programs as $program)
                        <div class="col-md-4 mb-3 program-item" data-level="{{ strtolower($program->degree_type ?? 'undergraduate') }}">
                            <div class="card h-100 border-left-primary">
                                <div class="card-body">
                                    <h6 class="font-weight-bold text-primary">{{ $program->name }}</h6>
                                    <p class="text-muted small mb-2">{{ $program->code ?? 'PRG' . $program->id }}</p>
                                    <p class="mb-2">{{ Str::limit($program->description ?? 'Excellence in education and research', 100) }}</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-{{ ($program->degree_type ?? 'undergraduate') == 'undergraduate' ? 'info' : 'success' }}">
                                            {{ ucfirst($program->degree_type ?? 'undergraduate') }}
                                        </span>
                                        <span class="text-muted small">
                                            <i class="fas fa-clock"></i> {{ $program->duration_years ?? 4 }} years
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Links & Resources --}}
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0 text-primary">
                        <i class="fas fa-link me-2"></i> Quick Links
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('admissions.requirements') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-list-check me-2 text-primary"></i> Admission Requirements
                        </a>
                        <a href="{{ route('admissions.calendar') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-calendar me-2 text-info"></i> Important Dates
                        </a>
                        <a href="{{ route('admissions.faq') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-question-circle me-2 text-warning"></i> Frequently Asked Questions
                        </a>
                        <a href="{{ route('admissions.contact') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-phone me-2 text-success"></i> Contact Admissions Office
                        </a>
                        <a href="{{ route('exams.information') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-file-alt me-2 text-danger"></i> Entrance Exam Information
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0 text-primary">
                        <i class="fas fa-download me-2"></i> Downloads
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('admissions.download', 'prospectus') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-book me-2 text-primary"></i> University Prospectus
                            <span class="badge bg-secondary float-end">PDF</span>
                        </a>
                        <a href="{{ route('admissions.download', 'application-guide') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-file-pdf me-2 text-danger"></i> Application Guide
                            <span class="badge bg-secondary float-end">PDF</span>
                        </a>
                        <a href="{{ route('admissions.download', 'fee-structure') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-dollar-sign me-2 text-success"></i> Fee Structure
                            <span class="badge bg-secondary float-end">PDF</span>
                        </a>
                        <a href="{{ route('admissions.download', 'sample-papers') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-file-alt me-2 text-info"></i> Sample Test Papers
                            <span class="badge bg-secondary float-end">PDF</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Contact Section --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body text-center py-4">
                    <h5 class="mb-3">Need Help?</h5>
                    <p class="mb-3">Our admissions team is here to assist you with your application.</p>
                    <div class="d-flex justify-content-center gap-4 flex-wrap">
                        <div>
                            <i class="fas fa-phone text-primary"></i> 
                            <strong>Call:</strong> +231 77 000 0000
                        </div>
                        <div>
                            <i class="fas fa-envelope text-primary"></i> 
                            <strong>Email:</strong> admissions@intellicampus.edu
                        </div>
                        <div>
                            <i class="fas fa-clock text-primary"></i> 
                            <strong>Hours:</strong> Mon-Fri, 8AM-5PM
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Continue Application Modal --}}
<div class="modal fade" id="continueModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-alt me-2"></i>Continue Your Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Enter your application details to continue where you left off:</p>
                <form action="{{ route('admissions.portal.find') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Email Address *</label>
                        <input type="email" class="form-control" name="email" required>
                        <small class="text-muted">The email you used when starting your application</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Application UUID or Number *</label>
                        <input type="text" class="form-control" name="reference" required>
                        <small class="text-muted">Example: APP202501001 or abc123-def456-ghi789</small>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-arrow-right me-2"></i>Continue Application
                    </button>
                </form>
            </div>
            <div class="modal-footer">
                <small class="text-muted">
                    Lost your UUID? Use the "Lost UUID" link or contact admissions.
                </small>
            </div>
        </div>
    </div>
</div>

{{-- Lost UUID Modal --}}
<div class="modal fade" id="lostUUIDModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-search me-2"></i>Find Your Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Lost your UUID? We can help you find your application.</p>
                <form action="{{ route('admissions.portal.recover') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Email Address *</label>
                        <input type="email" class="form-control" name="email" required>
                        <small class="text-muted">We'll send all application UUIDs associated with this email</small>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-envelope me-2"></i>Send My Application Details
                    </button>
                </form>
                <hr class="my-3">
                <p class="text-center text-muted">Or contact us directly:</p>
                <p class="text-center">
                    <i class="fas fa-phone text-primary"></i> +231 77 000 0000<br>
                    <i class="fas fa-envelope text-primary"></i> admissions@intellicampus.edu
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .process-step {
        position: relative;
        padding: 0 15px;
    }
    
    .process-step::after {
        content: '';
        position: absolute;
        top: 30px;
        right: -50%;
        width: 100%;
        height: 2px;
        background: #dee2e6;
        z-index: -1;
    }
    
    .process-step:last-child::after {
        display: none;
    }
    
    .step-icon {
        position: relative;
        z-index: 1;
        background: #fff;
    }
    
    .program-item {
        transition: all 0.3s ease;
    }
    
    .program-item.hidden {
        display: none;
    }
    
    .border-left-primary {
        border-left: 4px solid #007bff !important;
    }
    
    .border-left-success {
        border-left: 4px solid #28a745 !important;
    }
    
    .border-left-info {
        border-left: 4px solid #17a2b8 !important;
    }
    
    .border-left-warning {
        border-left: 4px solid #ffc107 !important;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Program filtering
    const filterButtons = document.querySelectorAll('[data-filter]');
    const programItems = document.querySelectorAll('.program-item');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Update active button
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Filter programs
            const filter = this.dataset.filter;
            programItems.forEach(item => {
                if (filter === 'all' || item.dataset.level === filter) {
                    item.classList.remove('hidden');
                } else {
                    item.classList.add('hidden');
                }
            });
        });
    });
    
    // Display any success/error messages
    @if(session('success'))
        toastr.success('{{ session("success") }}');
    @endif
    
    @if(session('error'))
        toastr.error('{{ session("error") }}');
    @endif
    
    @if(session('info'))
        toastr.info('{{ session("info") }}');
    @endif
    
    // Store recently accessed applications in localStorage
    @if(session('application_uuid') && session('application_number'))
    (function() {
        let recentApps = JSON.parse(localStorage.getItem('recent_applications') || '[]');
        const newApp = {
            uuid: '{{ session("application_uuid") }}',
            number: '{{ session("application_number") }}',
            date: new Date().toISOString()
        };
        
        // Remove duplicate if exists
        recentApps = recentApps.filter(app => app.uuid !== newApp.uuid);
        
        // Add to beginning and limit to 5 recent
        recentApps.unshift(newApp);
        recentApps = recentApps.slice(0, 5);
        
        localStorage.setItem('recent_applications', JSON.stringify(recentApps));
    })();
    @endif
    
    // Load recent applications from localStorage
    const recentApps = JSON.parse(localStorage.getItem('recent_applications') || '[]');
    if (recentApps.length > 0 && !document.querySelector('.alert-info')) {
        const recentSection = document.createElement('div');
        recentSection.className = 'row mb-4';
        recentSection.innerHTML = `
            <div class="col-12">
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <h6 class="alert-heading"><i class="fas fa-history me-2"></i>Recent Applications (This Browser)</h6>
                    <div class="mt-2">
                        ${recentApps.map(app => `
                            <a href="/admissions/portal/continue/${app.uuid}" class="btn btn-sm btn-primary me-2 mb-2">
                                <i class="fas fa-arrow-right me-1"></i> ${app.number}
                            </a>
                        `).join('')}
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        `;
        
        // Insert after hero section
        const heroSection = document.querySelector('.row.mb-5');
        if (heroSection && heroSection.nextSibling) {
            heroSection.parentNode.insertBefore(recentSection, heroSection.nextSibling);
        }
    }
});
</script>
@endpush