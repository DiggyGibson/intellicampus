{{-- resources/views/admissions/public/programs.blade.php --}}
@extends('layouts.app')

@section('title', 'Academic Programs - IntelliCampus')

@section('content')
<div class="container-fluid py-4">
    {{-- Hero Section --}}
    <div class="bg-gradient-info text-white rounded-lg p-5 mb-4">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 font-weight-bold mb-3">Academic Programs</h1>
                <p class="lead mb-4">Discover your path to success with our degree programs</p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="{{ route('admissions.portal.start') }}" class="btn btn-light btn-lg">
                        <i class="fas fa-paper-plane me-2"></i>Apply Now
                    </a>
                    <a href="{{ route('admissions.requirements') }}" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-clipboard-check me-2"></i>View Requirements
                    </a>
                </div>
            </div>
            <div class="col-lg-4 text-center">
                <i class="fas fa-graduation-cap" style="font-size: 150px; opacity: 0.3;"></i>
            </div>
        </div>
    </div>

    {{-- Error Display --}}
    @if(isset($error))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>{{ $error }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Program Filter --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admissions.programs') }}">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <select class="form-select" name="level" onchange="this.form.submit()">
                            <option value="">All Levels</option>
                            <option value="bachelor" {{ request('level') == 'bachelor' ? 'selected' : '' }}>Bachelor's</option>
                            <option value="master" {{ request('level') == 'master' ? 'selected' : '' }}>Master's</option>
                            <option value="doctorate" {{ request('level') == 'doctorate' ? 'selected' : '' }}>Doctorate</option>
                            <option value="certificate" {{ request('level') == 'certificate' ? 'selected' : '' }}>Certificate</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        @if(isset($departments) && $departments->count() > 0)
                        <select class="form-select" name="department" onchange="this.form.submit()">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>
                                    {{ $dept }}
                                </option>
                            @endforeach
                        </select>
                        @else
                        <select class="form-select" disabled>
                            <option>No Departments Available</option>
                        </select>
                        @endif
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Search programs by name, code, or description..." 
                                   value="{{ request('search') }}">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i> Search
                            </button>
                            @if(request('search') || request('level') || request('department'))
                            <a href="{{ route('admissions.programs') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Real Statistics from Database --}}
    @php
        // Get additional real statistics
        $activeApplicants = \App\Models\User::where('user_type', 'applicant')->count();
        $totalApplications = \App\Models\AdmissionApplication::count();
        $submittedApplications = \App\Models\AdmissionApplication::where('status', 'submitted')->count();
        $draftApplications = \App\Models\AdmissionApplication::where('status', 'draft')->count();
        $totalDepartments = \App\Models\AcademicProgram::where('is_active', true)
            ->whereNotNull('department')
            ->distinct('department')
            ->count('department');
    @endphp
    
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card text-center border-0 shadow-sm h-100">
                <div class="card-body">
                    <h2 class="text-primary mb-0">{{ $stats['total_programs'] ?? 0 }}</h2>
                    <p class="text-muted mb-0">Active Programs</p>
                    <small class="text-muted">Accepting applications</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card text-center border-0 shadow-sm h-100">
                <div class="card-body">
                    <h2 class="text-success mb-0">{{ $stats['undergraduate'] ?? 0 }}</h2>
                    <p class="text-muted mb-0">Undergraduate</p>
                    <small class="text-muted">Bachelor's degrees</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card text-center border-0 shadow-sm h-100">
                <div class="card-body">
                    <h2 class="text-info mb-0">{{ $totalDepartments }}</h2>
                    <p class="text-muted mb-0">Departments</p>
                    <small class="text-muted">Academic units</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card text-center border-0 shadow-sm h-100">
                <div class="card-body">
                    <h2 class="text-warning mb-0">{{ $totalApplications }}</h2>
                    <p class="text-muted mb-0">Applications</p>
                    <small class="text-muted">{{ $submittedApplications }} submitted</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card text-center border-0 shadow-sm h-100">
                <div class="card-body">
                    <h2 class="text-danger mb-0">{{ $draftApplications }}</h2>
                    <p class="text-muted mb-0">In Progress</p>
                    <small class="text-muted">Draft applications</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card text-center border-0 shadow-sm h-100">
                <div class="card-body">
                    <h2 class="text-secondary mb-0">{{ $activeApplicants }}</h2>
                    <p class="text-muted mb-0">Applicants</p>
                    <small class="text-muted">Registered users</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Programs from Database --}}
    @if(isset($programs) && $programs->count() > 0)
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Available Programs</h2>
        <span class="badge bg-primary">Showing {{ $programs->count() }} of {{ $programs->total() }} programs</span>
    </div>
    
    <div class="row mb-5">
        @foreach($programs as $program)
        @php
            // Get raw department string to avoid accessor returning object
            $deptName = $program->getAttributes()['department'] ?? 'General Studies';
            $facultyName = $program->getAttributes()['faculty'] ?? null;
        @endphp
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 shadow-sm program-card">
                <div class="card-header bg-gradient-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ $program->code }}</h5>
                        @if($program->accepts_applications)
                        <span class="badge bg-success">Open</span>
                        @else
                        <span class="badge bg-danger">Closed</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <h6 class="card-title fw-bold">{{ $program->name }}</h6>
                    
                    {{-- Program Badges --}}
                    <div class="mb-2">
                        @if($program->level)
                        <span class="badge bg-info">{{ ucfirst($program->level) }}</span>
                        @endif
                        @if($program->program_type)
                        <span class="badge bg-secondary">{{ ucfirst($program->program_type) }}</span>
                        @endif
                        @if($program->duration_years)
                        <span class="badge bg-success">{{ $program->duration_years }} Years</span>
                        @endif
                        @if($program->delivery_mode)
                        <span class="badge bg-warning text-dark">{{ ucfirst($program->delivery_mode ?? 'on-campus') }}</span>
                        @endif
                    </div>
                    
                    {{-- Description --}}
                    <p class="card-text small">
                        {{ Str::limit($program->description ?: 'Quality education program preparing students for successful careers and advanced studies.', 150) }}
                    </p>
                    
                    {{-- Program Details --}}
                    <ul class="list-unstyled small mb-3">
                        <li class="mb-1">
                            <i class="fas fa-book text-primary me-2"></i>
                            <strong>Credits:</strong> {{ $program->total_credits ?? 'TBD' }}
                        </li>
                        <li class="mb-1">
                            <i class="fas fa-graduation-cap text-success me-2"></i>
                            <strong>Min GPA:</strong> {{ number_format($program->min_gpa ?? 2.0, 1) }}
                        </li>
                        <li class="mb-1">
                            <i class="fas fa-building text-info me-2"></i>
                            <strong>Department:</strong> {{ $deptName }}
                        </li>
                        @if($facultyName)
                        <li class="mb-1">
                            <i class="fas fa-university text-warning me-2"></i>
                            <strong>Faculty:</strong> {{ $facultyName }}
                        </li>
                        @endif
                        @if($program->application_fee)
                        <li class="mb-1">
                            <i class="fas fa-dollar-sign text-danger me-2"></i>
                            <strong>Application Fee:</strong> ${{ number_format($program->application_fee, 2) }}
                        </li>
                        @endif
                        @if($program->enrollment_capacity)
                        <li class="mb-1">
                            <i class="fas fa-users text-secondary me-2"></i>
                            <strong>Capacity:</strong> {{ $program->enrollment_capacity }} students
                        </li>
                        @endif
                    </ul>
                    
                    {{-- Action Buttons --}}
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" 
                                data-bs-target="#programModal{{ $program->id }}">
                            <i class="fas fa-info-circle me-1"></i> View Details
                        </button>
                        @if($program->accepts_applications)
                        <a href="{{ route('admissions.portal.start') }}?program={{ $program->id }}" 
                           class="btn btn-primary btn-sm">
                            <i class="fas fa-paper-plane me-1"></i> Apply Now
                        </a>
                        @else
                        <button class="btn btn-secondary btn-sm" disabled>
                            <i class="fas fa-lock me-1"></i> Applications Closed
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div class="d-flex justify-content-center">
        {{ $programs->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>

    @else
    {{-- No Programs Message --}}
    <div class="alert alert-info text-center">
        <i class="fas fa-info-circle fa-3x mb-3"></i>
        <h4>No Programs Found</h4>
        <p>No programs match your current filters. Try adjusting your search criteria.</p>
        <a href="{{ route('admissions.programs') }}" class="btn btn-primary">
            <i class="fas fa-refresh me-2"></i>View All Programs
        </a>
    </div>
    @endif

    {{-- Programs Grouped by Department with Enhanced Information --}}
    @php
        // Get raw department values without triggering the accessor
        $programsByDept = DB::table('academic_programs')
            ->where('is_active', true)
            ->whereNotNull('department')
            ->orderBy('department')
            ->orderBy('name')
            ->get()
            ->groupBy('department');
        
        // Get department details from departments table if available
        $departmentDetails = [];
        if (Schema::hasTable('departments')) {
            $departmentDetails = \App\Models\Department::whereIn('name', $programsByDept->keys())
                ->orWhereIn('code', $programsByDept->keys())
                ->get()
                ->keyBy('name');
        }
    @endphp

    @if($programsByDept->count() > 0)
    <h2 class="mb-4 mt-5">
        <i class="fas fa-sitemap me-2"></i>Programs by Department
    </h2>
    
    @foreach($programsByDept as $deptName => $deptPrograms)
    @php
        $deptInfo = isset($departmentDetails[$deptName]) ? $departmentDetails[$deptName] : null;
        if (!$deptInfo && !empty($departmentDetails)) {
            $deptInfo = $departmentDetails->firstWhere('code', $deptName);
        }
    @endphp
    
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-dark text-white">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h4 class="mb-0">
                        <i class="fas fa-building me-2"></i>{{ $deptName }}
                    </h4>
                    @if($deptInfo && $deptInfo->description)
                    <small class="text-white-50 d-block mt-1">{{ $deptInfo->description }}</small>
                    @endif
                </div>
                <div class="col-lg-4 text-lg-end">
                    @if($deptInfo)
                        @if($deptInfo->head_of_department)
                        <small class="d-block">
                            <i class="fas fa-user-tie me-1"></i> Head: {{ $deptInfo->head_of_department }}
                        </small>
                        @endif
                        @if($deptInfo->email)
                        <small class="d-block">
                            <i class="fas fa-envelope me-1"></i> 
                            <a href="mailto:{{ $deptInfo->email }}" class="text-white-50">{{ $deptInfo->email }}</a>
                        </small>
                        @endif
                        @if($deptInfo->phone)
                        <small class="d-block">
                            <i class="fas fa-phone me-1"></i> {{ $deptInfo->phone }}
                        </small>
                        @endif
                    @endif
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-8">
                    <h6 class="text-primary mb-3">
                        <i class="fas fa-list me-1"></i>Available Programs ({{ $deptPrograms->count() }})
                    </h6>
                    <div class="row">
                        @foreach($deptPrograms->chunk(ceil($deptPrograms->count() / 2)) as $chunk)
                        <div class="col-md-6">
                            @foreach($chunk as $prog)
                            <div class="mb-3 p-2 border-start border-3 border-primary">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-graduation-cap text-primary me-2 mt-1"></i>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold">{{ $prog->code }} - {{ $prog->name }}</div>
                                        <div class="small text-muted mt-1">
                                            @if($prog->program_type)
                                            <span class="badge bg-light text-dark me-1">{{ ucfirst($prog->program_type) }}</span>
                                            @endif
                                            @if($prog->duration_years)
                                            <span class="badge bg-light text-dark me-1">{{ $prog->duration_years }} years</span>
                                            @endif
                                            @if($prog->total_credits)
                                            <span class="badge bg-light text-dark me-1">{{ $prog->total_credits }} credits</span>
                                            @endif
                                            @if(!$prog->accepts_applications)
                                            <span class="badge bg-warning text-dark">Applications Closed</span>
                                            @else
                                            <span class="badge bg-success">Accepting Applications</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="col-lg-4">
                    <h6 class="text-secondary mb-3">
                        <i class="fas fa-info-circle me-1"></i>Department Information
                    </h6>
                    <div class="bg-light p-3 rounded">
                        <ul class="list-unstyled small mb-0">
                            @if($deptInfo)
                                @if($deptInfo->building || $deptInfo->office)
                                <li class="mb-2">
                                    <i class="fas fa-map-marker-alt text-muted me-2"></i>
                                    <strong>Location:</strong> 
                                    {{ $deptInfo->building ?: 'Main Campus' }}
                                    @if($deptInfo->office), Room {{ $deptInfo->office }}@endif
                                </li>
                                @endif
                                @if($deptInfo->website)
                                <li class="mb-2">
                                    <i class="fas fa-globe text-muted me-2"></i>
                                    <strong>Website:</strong>
                                    <a href="{{ $deptInfo->website }}" target="_blank" class="text-decoration-none">
                                        Visit Department Site
                                    </a>
                                </li>
                                @endif
                                @if($deptInfo->faculty_count || $deptInfo->student_count)
                                <li class="mb-2">
                                    <i class="fas fa-users text-muted me-2"></i>
                                    <strong>Size:</strong>
                                    @if($deptInfo->faculty_count)Faculty: {{ $deptInfo->faculty_count }}@endif
                                    @if($deptInfo->faculty_count && $deptInfo->student_count) | @endif
                                    @if($deptInfo->student_count)Students: {{ $deptInfo->student_count }}@endif
                                </li>
                                @endif
                                @if($deptInfo->established_date)
                                <li class="mb-2">
                                    <i class="fas fa-calendar text-muted me-2"></i>
                                    <strong>Established:</strong> {{ \Carbon\Carbon::parse($deptInfo->established_date)->format('Y') }}
                                </li>
                                @endif
                            @else
                                <li class="text-muted">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Contact information will be available soon.
                                </li>
                            @endif
                        </ul>
                        
                        @if($deptPrograms->where('accepts_applications', true)->count() > 0)
                        <div class="mt-3 d-grid">
                            <a href="{{ route('admissions.portal.start') }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-paper-plane me-1"></i> Apply to {{ $deptName }}
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
    @endif

    {{-- Call to Action --}}
    <div class="bg-light rounded-lg p-5 text-center mt-5">
        <h2 class="mb-4">Ready to Start Your Journey?</h2>
        <p class="lead mb-4">
            We currently have <strong>{{ $stats['total_programs'] ?? 0 }}</strong> active programs 
            across <strong>{{ $totalDepartments }}</strong> departments accepting applications.
        </p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="{{ route('admissions.portal.start') }}" class="btn btn-primary btn-lg">
                <i class="fas fa-rocket me-2"></i>Start Your Application
            </a>
            <a href="{{ route('admissions.contact') }}" class="btn btn-outline-primary btn-lg">
                <i class="fas fa-comments me-2"></i>Contact Admissions
            </a>
            <a href="{{ route('admissions.requirements') }}" class="btn btn-outline-secondary btn-lg">
                <i class="fas fa-list-check me-2"></i>View Requirements
            </a>
        </div>
    </div>
</div>

{{-- Program Detail Modals --}}
@if(isset($programs))
    @foreach($programs as $program)
    @php
        // Get raw values to avoid accessor issues
        $modalDeptName = $program->getAttributes()['department'] ?? 'General Studies';
        $modalFacultyName = $program->getAttributes()['faculty'] ?? null;
    @endphp
    <div class="modal fade" id="programModal{{ $program->id }}" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-graduation-cap me-2"></i>{{ $program->name }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold text-primary mb-3">Program Details</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Code:</th>
                                    <td>{{ $program->code }}</td>
                                </tr>
                                <tr>
                                    <th>Level:</th>
                                    <td>{{ ucfirst($program->level ?? 'N/A') }}</td>
                                </tr>
                                <tr>
                                    <th>Type:</th>
                                    <td>{{ ucfirst($program->program_type ?? 'N/A') }}</td>
                                </tr>
                                <tr>
                                    <th>Duration:</th>
                                    <td>{{ $program->duration_years ?? 'N/A' }} years</td>
                                </tr>
                                <tr>
                                    <th>Total Credits:</th>
                                    <td>{{ $program->total_credits ?? 'N/A' }}</td>
                                </tr>
                                @if($program->core_credits || $program->major_credits || $program->elective_credits)
                                <tr>
                                    <th>Credit Distribution:</th>
                                    <td>
                                        @if($program->core_credits)Core: {{ $program->core_credits }}@endif
                                        @if($program->major_credits) | Major: {{ $program->major_credits }}@endif
                                        @if($program->elective_credits) | Elective: {{ $program->elective_credits }}@endif
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold text-primary mb-3">Requirements & Fees</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Min GPA:</th>
                                    <td>{{ number_format($program->min_gpa ?? 2.0, 1) }}</td>
                                </tr>
                                @if($program->graduation_gpa)
                                <tr>
                                    <th>Graduation GPA:</th>
                                    <td>{{ number_format($program->graduation_gpa, 1) }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Application Fee:</th>
                                    <td>${{ number_format($program->application_fee ?? 50, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Department:</th>
                                    <td>{{ $modalDeptName }}</td>
                                </tr>
                                @if($modalFacultyName)
                                <tr>
                                    <th>Faculty:</th>
                                    <td>{{ $modalFacultyName }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Delivery Mode:</th>
                                    <td>{{ ucfirst($program->delivery_mode ?? 'on-campus') }}</td>
                                </tr>
                                @if($program->enrollment_capacity)
                                <tr>
                                    <th>Max Enrollment:</th>
                                    <td>{{ $program->enrollment_capacity }} students</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                    
                    @if($program->description)
                    <div class="mt-3">
                        <h6 class="fw-bold text-secondary">Description</h6>
                        <p class="small">{{ $program->description }}</p>
                    </div>
                    @endif
                    
                    @if($program->admission_requirements)
                    <div class="mt-3">
                        <h6 class="fw-bold text-secondary">Admission Requirements</h6>
                        <p class="small">
                            {{ is_array($program->admission_requirements) ? implode(', ', $program->admission_requirements) : $program->admission_requirements }}
                        </p>
                    </div>
                    @endif
                    
                    @if($program->learning_outcomes)
                    <div class="mt-3">
                        <h6 class="fw-bold text-secondary">Learning Outcomes</h6>
                        @if(is_array($program->learning_outcomes) || is_object($program->learning_outcomes))
                        <ul class="small">
                            @foreach((array)$program->learning_outcomes as $outcome)
                            <li>{{ $outcome }}</li>
                            @endforeach
                        </ul>
                        @else
                        <p class="small">{{ $program->learning_outcomes }}</p>
                        @endif
                    </div>
                    @endif
                    
                    @if($program->career_prospects)
                    <div class="mt-3">
                        <h6 class="fw-bold text-secondary">Career Prospects</h6>
                        @if(is_array($program->career_prospects) || is_object($program->career_prospects))
                        <ul class="small">
                            @foreach((array)$program->career_prospects as $career)
                            <li>{{ $career }}</li>
                            @endforeach
                        </ul>
                        @else
                        <p class="small">{{ $program->career_prospects }}</p>
                        @endif
                    </div>
                    @endif
                    
                    @if($program->accreditation_status)
                    <div class="mt-3">
                        <h6 class="fw-bold text-secondary">Accreditation</h6>
                        <p class="small">
                            Status: {{ $program->accreditation_status }}
                            @if($program->accreditation_date)
                            <br>Date: {{ \Carbon\Carbon::parse($program->accreditation_date)->format('F Y') }}
                            @endif
                            @if($program->next_review_date)
                            <br>Next Review: {{ \Carbon\Carbon::parse($program->next_review_date)->format('F Y') }}
                            @endif
                        </p>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    @if($program->accepts_applications)
                    <a href="{{ route('admissions.portal.start') }}?program={{ $program->id }}" 
                       class="btn btn-primary">
                        <i class="fas fa-paper-plane me-1"></i> Apply Now
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach
@endif

@push('styles')
<style>
    .program-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: none;
    }
    
    .program-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 1rem 3rem rgba(0,0,0,.175)!important;
    }
    
    .bg-gradient-info {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .border-start.border-3 {
        border-left-width: 3px !important;
    }
    
    .modal-dialog-centered {
        display: flex;
        align-items: center;
        min-height: calc(100% - 1rem);
    }
</style>
@endpush

@endsection