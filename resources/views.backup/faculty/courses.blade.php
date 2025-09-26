@extends('layouts.app')

@section('title', 'My Courses')

@section('breadcrumb')
    <a href="{{ route('faculty.dashboard') }}">Faculty Dashboard</a>
    <i class="fas fa-chevron-right"></i>
    <span>My Courses</span>
@endsection

@section('page-actions')
    <a href="{{ route('faculty.dashboard') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
    </a>
@endsection

@section('content')
<div class="container-fluid px-4">
    <!-- Header Section -->
    <div class="mb-4">
        <h2 class="fw-bold text-dark mb-1">My Courses</h2>
        <p class="text-muted">Manage your teaching assignments and course sections</p>
    </div>

    <!-- Current Term Info -->
    @if(isset($currentTerm))
    <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
        <i class="fas fa-info-circle me-2"></i>
        <div>
            Current Term: <strong>{{ $currentTerm->name ?? 'Not Set' }}</strong>
            ({{ $currentTerm->start_date ?? '' }} - {{ $currentTerm->end_date ?? '' }})
        </div>
    </div>
    @endif

    <!-- Courses by Term -->
    @if($sectionsByTerm->isNotEmpty())
        @foreach($sectionsByTerm as $termId => $sections)
            @php
                $term = $sections->first()->term ?? null;
                $isCurrent = $term && isset($currentTerm) && $term->id == $currentTerm->id;
            @endphp
            
            <div class="card shadow-sm mb-4">
                <!-- Term Header -->
                <div class="card-header bg-gradient-primary text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">
                                {{ $term->name ?? 'No Term' }}
                                @if($isCurrent)
                                    <span class="badge bg-success ms-2">CURRENT</span>
                                @endif
                            </h4>
                            @if($term)
                                <small class="opacity-75">
                                    {{ \Carbon\Carbon::parse($term->start_date)->format('M d, Y') }} - 
                                    {{ \Carbon\Carbon::parse($term->end_date)->format('M d, Y') }}
                                </small>
                            @endif
                        </div>
                        <div>
                            <span class="fs-2 fw-bold">{{ $sections->count() }}</span>
                            <span class="small ms-1">{{ Str::plural('Section', $sections->count()) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Sections Grid -->
                <div class="card-body">
                    <div class="row">
                        @foreach($sections as $section)
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="card h-100 course-card">
                                    <!-- Course Header -->
                                    <div class="card-header bg-light">
                                        <h6 class="fw-bold mb-1">{{ $section->course->course_code }}</h6>
                                        <small class="text-muted">{{ $section->course->title }}</small>
                                    </div>

                                    <!-- Section Details -->
                                    <div class="card-body">
                                        <dl class="row small mb-0">
                                            <dt class="col-5 text-muted">Section:</dt>
                                            <dd class="col-7 fw-bold">{{ $section->section_number }}</dd>

                                            <dt class="col-5 text-muted">CRN:</dt>
                                            <dd class="col-7">{{ $section->crn ?? 'TBA' }}</dd>

                                            <dt class="col-5 text-muted">Schedule:</dt>
                                            <dd class="col-7">
                                                {{ $section->days_of_week ?? 'TBA' }}
                                                @if($section->start_time)
                                                    <br>{{ \Carbon\Carbon::parse($section->start_time)->format('g:i A') }}
                                                @endif
                                            </dd>

                                            <dt class="col-5 text-muted">Room:</dt>
                                            <dd class="col-7">{{ $section->room ?? 'Online' }}</dd>

                                            <dt class="col-5 text-muted">Mode:</dt>
                                            <dd class="col-7 text-capitalize">{{ $section->delivery_mode ?? 'traditional' }}</dd>

                                            <dt class="col-5 text-muted">Enrollment:</dt>
                                            <dd class="col-7">
                                                <span class="badge bg-info">
                                                    {{ $section->current_enrollment ?? 0 }} / {{ $section->enrollment_capacity ?? 30 }}
                                                </span>
                                            </dd>

                                            <dt class="col-5 text-muted">Status:</dt>
                                            <dd class="col-7">
                                                @php
                                                    $statusClass = match($section->status) {
                                                        'open' => 'bg-success',
                                                        'closed' => 'bg-danger',
                                                        'cancelled' => 'bg-secondary',
                                                        default => 'bg-primary'
                                                    };
                                                @endphp
                                                <span class="badge {{ $statusClass }}">
                                                    {{ ucfirst($section->status ?? 'open') }}
                                                </span>
                                            </dd>
                                        </dl>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="card-footer bg-light">
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <a href="{{ route('faculty.section.details', $section->id) }}" 
                                                   class="btn btn-sm btn-primary w-100">
                                                    <i class="fas fa-eye me-1"></i> View
                                                </a>
                                            </div>
                                            <div class="col-6">
                                                <a href="{{ route('faculty.roster', $section->id) }}" 
                                                   class="btn btn-sm btn-success w-100">
                                                    <i class="fas fa-users me-1"></i> Roster
                                                </a>
                                            </div>
                                            <div class="col-6">
                                                <a href="{{ route('faculty.attendance', $section->id) }}" 
                                                   class="btn btn-sm btn-warning w-100">
                                                    <i class="fas fa-clipboard-check me-1"></i> Attendance
                                                </a>
                                            </div>
                                            <div class="col-6">
                                                <a href="{{ route('faculty.gradebook', $section->id) }}" 
                                                   class="btn btn-sm btn-purple w-100">
                                                    <i class="fas fa-chart-line me-1"></i> Grades
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <!-- No Courses Message -->
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No Courses Assigned</h4>
                <p class="text-muted">You don't have any courses assigned yet.</p>
                <a href="{{ route('faculty.dashboard') }}" class="btn btn-primary mt-3">
                    <i class="fas fa-arrow-left me-1"></i> Return to Dashboard
                </a>
            </div>
        </div>
    @endif
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
}
.btn-purple {
    background-color: #6f42c1;
    border-color: #6f42c1;
    color: white;
}
.btn-purple:hover {
    background-color: #5a32a3;
    border-color: #5a32a3;
    color: white;
}
.course-card {
    transition: transform 0.2s, box-shadow 0.2s;
}
.course-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
</style>
@endsection