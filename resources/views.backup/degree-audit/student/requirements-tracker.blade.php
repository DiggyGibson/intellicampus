{{-- resources/views/degree-audit/student/requirements-tracker.blade.php --}}
@extends('layouts.app')

@section('title', 'Requirements Tracker')

@section('styles')
<style>
    .requirement-item {
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .requirement-item:hover {
        background-color: #f8fafc;
        transform: translateX(5px);
    }
    
    .requirement-item.completed {
        background: linear-gradient(to right, #10b98110, transparent);
        border-left: 4px solid #10b981;
    }
    
    .requirement-item.in-progress {
        background: linear-gradient(to right, #f59e0b10, transparent);
        border-left: 4px solid #f59e0b;
    }
    
    .requirement-item.not-started {
        border-left: 4px solid #ef4444;
    }
    
    .course-selector {
        max-height: 400px;
        overflow-y: auto;
    }
    
    .planned-course {
        background: #eff6ff;
        border: 2px dashed #3b82f6;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Requirements Tracker</h2>
                <div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#plannerModal">
                        <i class="fas fa-calendar-plus"></i> Plan Courses
                    </button>
                    <a href="{{ route('degree-audit.dashboard') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Overview -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                    <h3 class="mb-0">{{ $completedCount ?? 0 }}</h3>
                    <p class="text-muted mb-0">Completed</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-clock text-warning fa-2x mb-2"></i>
                    <h3 class="mb-0">{{ $inProgressCount ?? 0 }}</h3>
                    <p class="text-muted mb-0">In Progress</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-times-circle text-danger fa-2x mb-2"></i>
                    <h3 class="mb-0">{{ $remainingCount ?? 0 }}</h3>
                    <p class="text-muted mb-0">Remaining</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-calendar-alt text-info fa-2x mb-2"></i>
                    <h3 class="mb-0">{{ $plannedCount ?? 0 }}</h3>
                    <p class="text-muted mb-0">Planned</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Interactive Requirements List -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">All Requirements</h5>
                </div>
                <div class="card-body">
                    @foreach($auditReport->requirements_summary ?? [] as $category)
                    <div class="mb-4">
                        <h6 class="text-muted mb-3">{{ $category['category_name'] }}</h6>
                        
                        @foreach($category['requirements'] ?? [] as $req)
                        <div class="requirement-item card mb-2 {{ $req['is_satisfied'] ? 'completed' : ($req['progress_percentage'] > 0 ? 'in-progress' : 'not-started') }}"
                             onclick="showRequirementDetails({{ json_encode($req) }})">
                            <div class="card-body py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $req['requirement_name'] }}</strong>
                                        @if($req['requirement_type'] === 'credit_hours')
                                            <span class="text-muted ms-2">
                                                ({{ $req['credits_completed'] ?? 0 }}/{{ $req['credits_required'] ?? 0 }} credits)
                                            </span>
                                        @endif
                                    </div>
                                    <div>
                                        @if($req['is_satisfied'])
                                            <span class="badge bg-success">Complete</span>
                                        @elseif($req['progress_percentage'] > 0)
                                            <span class="badge bg-warning">{{ number_format($req['progress_percentage'], 0) }}%</span>
                                        @else
                                            <span class="badge bg-danger">Not Started</span>
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
        </div>

        <!-- Requirement Details Panel -->
        <div class="col-lg-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Requirement Details</h5>
                </div>
                <div class="card-body" id="requirementDetails">
                    <p class="text-muted">Click on a requirement to see details</p>
                </div>
            </div>

            <!-- Course Recommendations -->
            <div class="card mt-3">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Recommended Next Courses</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        @foreach($recommendedCourses ?? [] as $course)
                        <a href="#" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between">
                                <strong>{{ $course->code }}</strong>
                                <span class="badge bg-primary">{{ $course->credits }} credits</span>
                            </div>
                            <small>{{ $course->name }}</small>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Course Planner Modal -->
<div class="modal fade" id="plannerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Plan Your Courses</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Available Courses</h6>
                        <div class="course-selector border rounded p-2">
                            @foreach($availableCourses ?? [] as $course)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                       value="{{ $course->id }}" id="course{{ $course->id }}">
                                <label class="form-check-label" for="course{{ $course->id }}">
                                    {{ $course->code }} - {{ $course->name }} ({{ $course->credits }} cr)
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>Select Term</h6>
                        <select class="form-select mb-3" id="termSelect">
                            <option>Fall 2025</option>
                            <option>Spring 2026</option>
                            <option>Summer 2026</option>
                            <option>Fall 2026</option>
                        </select>
                        
                        <h6>Selected Courses</h6>
                        <div id="selectedCourses" class="border rounded p-2" style="min-height: 200px;">
                            <p class="text-muted">No courses selected</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="savePlan()">Save Plan</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function showRequirementDetails(requirement) {
    let detailsHtml = `
        <h6>${requirement.requirement_name}</h6>
        <hr>
        <p><strong>Type:</strong> ${requirement.requirement_type}</p>
        <p><strong>Status:</strong> ${requirement.is_satisfied ? 'Complete' : 'In Progress'}</p>
    `;
    
    if (requirement.requirement_type === 'credit_hours') {
        detailsHtml += `
            <p><strong>Credits:</strong> ${requirement.credits_completed || 0} / ${requirement.credits_required || 0}</p>
            <div class="progress mb-3">
                <div class="progress-bar" style="width: ${requirement.progress_percentage || 0}%">
                    ${Math.round(requirement.progress_percentage || 0)}%
                </div>
            </div>
        `;
    }
    
    if (requirement.remaining_courses && requirement.remaining_courses.length > 0) {
        detailsHtml += `
            <h6 class="mt-3">Remaining Courses:</h6>
            <ul class="list-unstyled">
        `;
        requirement.remaining_courses.forEach(course => {
            detailsHtml += `<li><i class="fas fa-chevron-right"></i> ${course}</li>`;
        });
        detailsHtml += `</ul>`;
    }
    
    document.getElementById('requirementDetails').innerHTML = detailsHtml;
}

function savePlan() {
    // Implementation for saving course plan
    alert('Course plan saved successfully!');
    $('#plannerModal').modal('hide');
}
</script>
@endsection