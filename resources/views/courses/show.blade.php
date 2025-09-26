@extends('layouts.app')

@section('title', 'Course Details - ' . $course->code)

@section('breadcrumb')
    <a href="{{ route('courses.index') }}">Course Management</a>
    <i class="fas fa-chevron-right"></i>
    <span>{{ $course->code }}</span>
@endsection

@section('page-actions')
    <a href="{{ route('courses.edit', $course) }}" class="btn btn-primary">
        <i class="fas fa-edit"></i> Edit Course
    </a>
    <a href="{{ route('courses.sections', $course) }}" class="btn btn-success">
        <i class="fas fa-list"></i> Manage Sections
    </a>
    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
        <i class="fas fa-trash"></i> Delete
    </button>
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

    <div class="row">
        <!-- Main Content Column -->
        <div class="col-lg-8">
            <!-- Course Information Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary bg-gradient text-white">
                    <h5 class="mb-0"><i class="fas fa-book me-2"></i>Course Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Course Code</label>
                            <p class="fw-bold fs-5">{{ $course->code }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Department</label>
                            <p class="fw-semibold">{{ $course->department }}</p>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small">Course Title</label>
                            <p class="fw-bold fs-5">{{ $course->title }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted small">Level</label>
                            <p><span class="badge bg-secondary">Level {{ $course->level }}</span></p>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted small">Credits</label>
                            <p><span class="badge bg-info text-dark">{{ $course->credits }} Credits</span></p>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted small">Course Type</label>
                            <p>
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
                                    {{ ucwords(str_replace('_', ' ', $course->type)) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted small">Status</label>
                            <p>
                                @if($course->is_active)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i>Active
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times-circle me-1"></i>Inactive
                                    </span>
                                @endif
                            </p>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small">Description</label>
                            <p class="text-justify">{{ $course->description ?: 'No description available.' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Grading Method</label>
                            <p>{{ ucwords(str_replace('_', ' ', $course->grading_method)) }}</p>
                        </div>
                        @if($course->course_fee && $course->course_fee > 0)
                        <div class="col-md-6">
                            <label class="text-muted small">Additional Fee</label>
                            <p class="fw-bold text-danger">${{ number_format($course->course_fee, 2) }}</p>
                        </div>
                        @endif
                    </div>

                    <!-- Course Features -->
                    <div class="mt-4">
                        <label class="text-muted small mb-2">Course Features</label>
                        <div class="d-flex flex-wrap gap-2">
                            @if($course->has_lab)
                                <span class="badge bg-primary">
                                    <i class="fas fa-flask me-1"></i>Has Lab Component
                                </span>
                            @endif
                            @if($course->has_tutorial)
                                <span class="badge bg-info">
                                    <i class="fas fa-chalkboard-teacher me-1"></i>Has Tutorial
                                </span>
                            @endif
                            @if($course->contact_hours)
                                <span class="badge bg-secondary">
                                    <i class="fas fa-clock me-1"></i>{{ $course->contact_hours }} Contact Hours/Week
                                </span>
                            @endif
                            @if($course->lab_hours)
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-hourglass-half me-1"></i>{{ $course->lab_hours }} Lab Hours/Week
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Academic Details Card -->
            @if($course->prerequisites || $course->corequisites || $course->learning_outcomes || $course->assessment_methods || $course->textbooks)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success bg-gradient text-white">
                    <h5 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Academic Details</h5>
                </div>
                <div class="card-body">
                    @if($course->prerequisites)
                    <div class="mb-3">
                        <h6 class="text-primary">Prerequisites</h6>
                        <p class="ms-3">{{ $course->prerequisites }}</p>
                    </div>
                    @endif
                    
                    @if($course->corequisites)
                    <div class="mb-3">
                        <h6 class="text-primary">Corequisites</h6>
                        <p class="ms-3">{{ $course->corequisites }}</p>
                    </div>
                    @endif
                    
                    @if($course->learning_outcomes)
                    <div class="mb-3">
                        <h6 class="text-primary">Learning Outcomes</h6>
                        <div class="ms-3">
                            {!! nl2br(e($course->learning_outcomes)) !!}
                        </div>
                    </div>
                    @endif
                    
                    @if($course->assessment_methods)
                    <div class="mb-3">
                        <h6 class="text-primary">Assessment Methods</h6>
                        <p class="ms-3">{{ $course->assessment_methods }}</p>
                    </div>
                    @endif
                    
                    @if($course->textbooks)
                    <div>
                        <h6 class="text-primary">Required Textbooks</h6>
                        <p class="ms-3">{{ $course->textbooks }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Current Sections Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info bg-gradient text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Course Sections</h5>
                    <a href="{{ route('courses.sections', $course) }}" class="btn btn-sm btn-light">
                        Manage All <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($sections->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>CRN</th>
                                        <th>Section</th>
                                        <th>Term</th>
                                        <th>Instructor</th>
                                        <th>Mode</th>
                                        <th class="text-center">Enrollment</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sections as $section)
                                    <tr>
                                        <td>{{ $section->crn }}</td>
                                        <td class="fw-semibold">{{ $section->section_number }}</td>
                                        <td>{{ $section->term->name ?? 'N/A' }}</td>
                                        <td>{{ $section->instructor->name ?? 'TBA' }}</td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ ucwords(str_replace('_', ' ', $section->delivery_mode)) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $section->isFull() ? 'bg-danger' : 'bg-success' }}">
                                                {{ $section->current_enrollment }}/{{ $section->enrollment_capacity }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if($section->status == 'open')
                                                <span class="badge bg-success">Open</span>
                                            @elseif($section->status == 'closed')
                                                <span class="badge bg-danger">Closed</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($section->status) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($sections->hasPages())
                            <div class="card-footer bg-white">
                                {{ $sections->links('custom.pagination') }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No sections created yet.</p>
                            <a href="{{ route('courses.sections', $course) }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Create First Section
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar Column -->
        <div class="col-lg-4">
            <!-- Statistics Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-warning bg-gradient">
                    <h5 class="mb-0 text-dark"><i class="fas fa-chart-bar me-2"></i>Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 text-center">
                        <div class="col-6">
                            <div class="stat-item">
                                <h3 class="text-primary mb-0">{{ $stats['total_sections'] }}</h3>
                                <small class="text-muted">Total Sections</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-item">
                                <h3 class="text-success mb-0">{{ $stats['active_sections'] }}</h3>
                                <small class="text-muted">Active Sections</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-item">
                                <h3 class="text-info mb-0">{{ $stats['total_enrolled'] }}</h3>
                                <small class="text-muted">Total Enrolled</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-item">
                                <h3 class="text-warning mb-0">{{ number_format($stats['average_enrollment'], 1) }}</h3>
                                <small class="text-muted">Avg. Enrollment</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-secondary bg-gradient text-white">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('courses.sections', $course) }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Create New Section
                        </a>
                        <a href="{{ route('courses.edit', $course) }}" class="btn btn-info">
                            <i class="fas fa-edit me-2"></i>Edit Course Details
                        </a>
                        <button type="button" class="btn btn-success" onclick="alert('Clone feature coming soon!')">
                            <i class="fas fa-copy me-2"></i>Clone Course
                        </button>
                        <button type="button" class="btn btn-warning" onclick="alert('Export feature coming soon!')">
                            <i class="fas fa-file-export me-2"></i>Export Syllabus
                        </button>
                    </div>
                </div>
            </div>

            <!-- System Information Card -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>System Information</h6>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5 text-muted">Course ID:</dt>
                        <dd class="col-7">#{{ $course->id }}</dd>
                        
                        <dt class="col-5 text-muted">Created:</dt>
                        <dd class="col-7">{{ $course->created_at->format('M d, Y') }}</dd>
                        
                        <dt class="col-5 text-muted">Updated:</dt>
                        <dd class="col-7">{{ $course->updated_at->format('M d, Y') }}</dd>
                        
                        @if($course->created_by)
                        <dt class="col-5 text-muted">Created By:</dt>
                        <dd class="col-7">{{ $course->creator->name ?? 'System' }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this course?</p>
                <div class="alert alert-warning">
                    <strong>Course:</strong> {{ $course->code }} - {{ $course->title }}<br>
                    <strong>Department:</strong> {{ $course->department }}<br>
                    <strong>Sections:</strong> {{ $stats['total_sections'] }} section(s)
                </div>
                <p class="text-danger">
                    <i class="fas fa-exclamation-circle"></i> 
                    This action cannot be undone. All course data and sections will be permanently deleted.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('courses.destroy', $course) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>Delete Course
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .stat-item {
        padding: 15px;
        border-radius: 8px;
        background: #f8f9fa;
        transition: all 0.3s;
    }
    
    .stat-item:hover {
        background: #e9ecef;
        transform: translateY(-2px);
    }
    
    .bg-purple {
        background-color: #6f42c1 !important;
    }
    
    label.text-muted {
        font-size: 0.875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
</style>
@endpush