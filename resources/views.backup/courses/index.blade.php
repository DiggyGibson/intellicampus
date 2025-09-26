@extends('layouts.app')

@section('title', 'Course Management')

@section('breadcrumb')
    <span>Course Management</span>
@endsection

@section('page-actions')
    <a href="{{ route('courses.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New Course
    </a>
@endsection

@section('content')
<div class="container-fluid px-4">
    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Courses</h6>
                            <h3 class="mb-0">{{ number_format(\App\Models\Course::count()) }}</h3>
                        </div>
                        <div class="stat-icon bg-primary bg-gradient">
                            <i class="fas fa-book"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Active Courses</h6>
                            <h3 class="mb-0 text-success">{{ number_format(\App\Models\Course::where('is_active', true)->count()) }}</h3>
                        </div>
                        <div class="stat-icon bg-success bg-gradient">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Open Sections</h6>
                            <h3 class="mb-0 text-info">{{ number_format(\App\Models\CourseSection::where('status', 'open')->count()) }}</h3>
                        </div>
                        <div class="stat-icon bg-info bg-gradient">
                            <i class="fas fa-door-open"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Lab Courses</h6>
                            <h3 class="mb-0 text-warning">{{ number_format(\App\Models\Course::where('has_lab', true)->count()) }}</h3>
                        </div>
                        <div class="stat-icon bg-warning bg-gradient">
                            <i class="fas fa-flask"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Search & Filter Courses</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('courses.index') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Search</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   class="form-control" placeholder="Search by code, title, or description...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Department</label>
                        <select name="department" class="form-select">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>
                                    {{ $dept }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Level</label>
                        <select name="level" class="form-select">
                            <option value="">All Levels</option>
                            @foreach($levels as $level)
                                <option value="{{ $level }}" {{ request('level') == $level ? 'selected' : '' }}>
                                    Level {{ $level }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="row g-3 mt-1">
                    <div class="col-md-3">
                        <label class="form-label">Course Type</label>
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            <option value="core" {{ request('type') == 'core' ? 'selected' : '' }}>Core</option>
                            <option value="elective" {{ request('type') == 'elective' ? 'selected' : '' }}>Elective</option>
                            <option value="general_education" {{ request('type') == 'general_education' ? 'selected' : '' }}>General Education</option>
                            <option value="major" {{ request('type') == 'major' ? 'selected' : '' }}>Major</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Credits</label>
                        <select name="credits" class="form-select">
                            <option value="">All Credits</option>
                            @for($i = 1; $i <= 6; $i++)
                                <option value="{{ $i }}" {{ request('credits') == $i ? 'selected' : '' }}>
                                    {{ $i }} {{ $i == 1 ? 'Credit' : 'Credits' }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary" data-bs-toggle="tooltip" title="Apply Filters">
                                <i class="fas fa-search"></i>
                            </button>
                            <a href="{{ route('courses.index') }}" class="btn btn-secondary" data-bs-toggle="tooltip" title="Clear Filters">
                                <i class="fas fa-undo"></i>
                            </a>
                            @if($courses->count() > 0)
                                <button type="button" class="btn btn-info" data-bs-toggle="tooltip" title="Export Courses">
                                    <i class="fas fa-download"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Courses Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">Courses ({{ $courses->total() }} total)</h5>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Course Code</th>
                            <th>Title</th>
                            <th>Department</th>
                            <th class="text-center">Level</th>
                            <th class="text-center">Credits</th>
                            <th class="text-center">Type</th>
                            <th class="text-center">Lab</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($courses as $course)
                            <tr>
                                <td>
                                    <span class="fw-semibold">{{ $course->code }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('courses.show', $course) }}" 
                                       class="text-decoration-none">
                                        <div class="fw-semibold text-primary">{{ $course->title }}</div>
                                        @if($course->description)
                                            <small class="text-muted">{{ Str::limit($course->description, 50) }}</small>
                                        @endif
                                    </a>
                                </td>
                                <td>{{ $course->department }}</td>
                                <td class="text-center">
                                    <span class="badge bg-secondary">Level {{ $course->level }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info text-dark">{{ $course->credits }}</span>
                                </td>
                                <td class="text-center">
                                    @php
                                        $typeColors = [
                                            'core' => 'warning',
                                            'elective' => 'purple',
                                            'general_education' => 'info',
                                            'major' => 'success',
                                            'required' => 'danger'
                                        ];
                                        $color = $typeColors[$course->type] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }}">
                                        {{ $course->type == 'general_education' ? 'Gen Ed' : ucfirst($course->type) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($course->has_lab)
                                        <span class="badge bg-success">
                                            <i class="fas fa-flask me-1"></i>Yes
                                        </span>
                                    @else
                                        <span class="badge bg-light text-dark">No</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($course->is_active)
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i>Active
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="fas fa-times-circle me-1"></i>Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('courses.show', $course) }}" 
                                           class="btn btn-outline-info" 
                                           data-bs-toggle="tooltip" 
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('courses.edit', $course) }}" 
                                           class="btn btn-outline-success" 
                                           data-bs-toggle="tooltip" 
                                           title="Edit Course">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('courses.sections', $course) }}" 
                                           class="btn btn-outline-primary" 
                                           data-bs-toggle="tooltip" 
                                           title="Manage Sections">
                                            <i class="fas fa-list"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-book fa-3x mb-3"></i>
                                        <p>No courses found matching your criteria.</p>
                                        <a href="{{ route('courses.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus me-2"></i>Create Your First Course
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($courses->hasPages())
            <div class="card-footer bg-white">
                {{ $courses->withQueryString()->links('custom.pagination') }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .stat-card {
        border: none;
        border-radius: 10px;
        transition: transform 0.2s;
    }
    
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
    }
    
    .bg-purple {
        background-color: #6f42c1 !important;
    }
    
    .badge {
        padding: 0.35em 0.65em;
        font-weight: 500;
    }
</style>
@endpush

@push('scripts')
<script>
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
</script>
@endpush