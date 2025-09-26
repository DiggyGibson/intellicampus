{{-- resources/views/admissions/portal/index.blade.php --}}
@extends('layouts.app')

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
                            
                            <div class="d-flex gap-3">
                                @auth
                                    <a href="{{ route('admissions.portal.continue') }}" class="btn btn-light btn-lg">
                                        <i class="fas fa-file-alt me-2"></i> Continue Application
                                    </a>
                                @else
                                    <a href="{{ route('admissions.portal.start') }}" class="btn btn-light btn-lg">
                                        <i class="fas fa-plus-circle me-2"></i> Start New Application
                                    </a>
                                @endauth
                                
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
                                    <i class="fas fa-user-plus fa-lg"></i>
                                </div>
                                <h6>1. Create Account</h6>
                                <small class="text-muted">Register and verify email</small>
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
                        <div class="col-md-4 mb-3 program-item" data-level="{{ strtolower($program->level) }}">
                            <div class="card h-100 border-left-primary">
                                <div class="card-body">
                                    <h6 class="font-weight-bold text-primary">{{ $program->name }}</h6>
                                    <p class="text-muted small mb-2">{{ $program->code }}</p>
                                    <p class="mb-2">{{ Str::limit($program->description, 100) }}</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-{{ $program->level == 'Undergraduate' ? 'info' : 'success' }}">
                                            {{ $program->level }}
                                        </span>
                                        <span class="text-muted small">
                                            <i class="fas fa-clock"></i> {{ $program->duration }} years
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
                    <div class="d-flex justify-content-center gap-4">
                        <div>
                            <i class="fas fa-phone text-primary"></i> 
                            <strong>Call:</strong> +1 234 567 8900
                        </div>
                        <div>
                            <i class="fas fa-envelope text-primary"></i> 
                            <strong>Email:</strong> admissions@intellicampus.edu
                        </div>
                        <div>
                            <i class="fas fa-clock text-primary"></i> 
                            <strong>Hours:</strong> Mon-Fri, 9AM-5PM
                        </div>
                    </div>
                </div>
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
    
    // Countdown timer for deadline
    @if(isset($stats['deadline']))
    const deadline = new Date('{{ $stats["deadline"]->toIso8601String() }}').getTime();
    
    const timer = setInterval(function() {
        const now = new Date().getTime();
        const distance = deadline - now;
        
        if (distance < 0) {
            clearInterval(timer);
            document.getElementById('countdown').innerHTML = "EXPIRED";
        } else {
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            document.getElementById('days-remaining').innerHTML = days;
        }
    }, 1000);
    @endif
});
</script>
@endpush