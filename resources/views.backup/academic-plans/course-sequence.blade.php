{{-- resources/views/academic-plans/course-sequence.blade.php --}}
@extends('layouts.app')

@section('title', 'Course Sequence Visualization')

@section('styles')
<style>
    .sequence-container {
        position: relative;
        padding: 20px;
        background: #f8fafc;
        border-radius: 12px;
        overflow-x: auto;
    }
    
    .course-node {
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        padding: 12px;
        margin: 10px;
        min-width: 180px;
        position: relative;
        transition: all 0.3s ease;
    }
    
    .course-node:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .course-node.completed {
        background: #d1fae5;
        border-color: #10b981;
    }
    
    .course-node.in-progress {
        background: #fed7aa;
        border-color: #f59e0b;
    }
    
    .course-node.available {
        background: #dbeafe;
        border-color: #3b82f6;
    }
    
    .course-node.locked {
        background: #f3f4f6;
        border-color: #9ca3af;
        opacity: 0.6;
    }
    
    .prerequisite-line {
        position: absolute;
        background: #6b7280;
        height: 2px;
        transform-origin: left center;
    }
    
    .semester-column {
        min-height: 400px;
        border-right: 2px dashed #e5e7eb;
    }
    
    .semester-column:last-child {
        border-right: none;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2>Course Sequence & Prerequisites</h2>
                    <p class="text-muted mb-0">Visual map of your course progression</p>
                </div>
                <div>
                    <button class="btn btn-outline-primary" onclick="toggleView()">
                        <i class="fas fa-exchange-alt"></i> Toggle View
                    </button>
                    <button class="btn btn-outline-secondary" onclick="printSequence()">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-around align-items-center">
                        <div class="text-center">
                            <div class="course-node completed d-inline-block px-3 py-1 mb-2">
                                <small>Completed</small>
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="course-node in-progress d-inline-block px-3 py-1 mb-2">
                                <small>In Progress</small>
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="course-node available d-inline-block px-3 py-1 mb-2">
                                <small>Available</small>
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="course-node locked d-inline-block px-3 py-1 mb-2">
                                <small>Prerequisites Not Met</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sequence Visualization -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#byYear">By Year</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#byCategory">By Category</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#byPrerequisite">Prerequisite Tree</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <!-- By Year View -->
                        <div class="tab-pane fade show active" id="byYear">
                            <div class="sequence-container">
                                <div class="row">
                                    @for($year = 1; $year <= 4; $year++)
                                    <div class="col-md-3 semester-column">
                                        <h5 class="text-center mb-3">Year {{ $year }}</h5>
                                        
                                        @foreach($coursesByYear[$year] ?? [] as $course)
                                        <div class="course-node {{ $course->status }}" 
                                             onclick="showCourseDetails({{ json_encode($course) }})">
                                            <strong>{{ $course->code }}</strong>
                                            <small class="d-block">{{ $course->name }}</small>
                                            <span class="badge bg-secondary">{{ $course->credits }} cr</span>
                                            
                                            @if($course->has_prerequisites)
                                            <i class="fas fa-link text-muted float-end" 
                                               title="Has prerequisites"></i>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                    @endfor
                                </div>
                            </div>
                        </div>

                        <!-- By Category View -->
                        <div class="tab-pane fade" id="byCategory">
                            <div class="row">
                                @foreach($coursesByCategory ?? [] as $category => $courses)
                                <div class="col-lg-4 mb-4">
                                    <h5>{{ $category }}</h5>
                                    <div class="border rounded p-3">
                                        @foreach($courses as $course)
                                        <div class="course-node {{ $course->status }} mb-2">
                                            <strong>{{ $course->code }}</strong>
                                            <small class="d-block">{{ $course->name }}</small>
                                            <div class="d-flex justify-content-between">
                                                <span class="badge bg-secondary">{{ $course->credits }} cr</span>
                                                @if($course->term_offered)
                                                <small class="text-muted">{{ $course->term_offered }}</small>
                                                @endif
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Prerequisite Tree View -->
                        <div class="tab-pane fade" id="byPrerequisite">
                            <div class="sequence-container" style="min-height: 600px;">
                                <svg id="prerequisiteTree" width="100%" height="600">
                                    <!-- D3.js or similar will render the tree here -->
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Course Details Panel -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card" id="courseDetailsPanel" style="display: none;">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Course Details</h5>
                </div>
                <div class="card-body" id="courseDetailsContent">
                    <!-- Details will be populated here -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function showCourseDetails(course) {
    const panel = document.getElementById('courseDetailsPanel');
    const content = document.getElementById('courseDetailsContent');
    
    let detailsHtml = `
        <div class="row">
            <div class="col-md-6">
                <h4>${course.code} - ${course.name}</h4>
                <p><strong>Credits:</strong> ${course.credits}</p>
                <p><strong>Status:</strong> <span class="badge bg-${getStatusColor(course.status)}">${course.status}</span></p>
                <p><strong>Description:</strong> ${course.description || 'No description available'}</p>
            </div>
            <div class="col-md-6">
                <h6>Prerequisites:</h6>
                <ul>
    `;
    
    if (course.prerequisites && course.prerequisites.length > 0) {
        course.prerequisites.forEach(prereq => {
            detailsHtml += `<li>${prereq.code} - ${prereq.name}</li>`;
        });
    } else {
        detailsHtml += `<li>None</li>`;
    }
    
    detailsHtml += `
                </ul>
                <h6>When Offered:</h6>
                <p>${course.terms_offered || 'Every semester'}</p>
                
                <button class="btn btn-primary" onclick="addToPlanner('${course.code}')">
                    Add to Planner
                </button>
            </div>
        </div>
    `;
    
    content.innerHTML = detailsHtml;
    panel.style.display = 'block';
    panel.scrollIntoView({ behavior: 'smooth' });
}

function getStatusColor(status) {
    switch(status) {
        case 'completed': return 'success';
        case 'in-progress': return 'warning';
        case 'available': return 'primary';
        case 'locked': return 'secondary';
        default: return 'light';
    }
}

function addToPlanner(courseCode) {
    alert(`Adding ${courseCode} to your academic planner...`);
    // Implementation to add course to planner
}

function toggleView() {
    // Toggle between different view modes
}

function printSequence() {
    window.print();
}

// Initialize prerequisite tree visualization (simplified)
document.addEventListener('DOMContentLoaded', function() {
    // This would typically use D3.js or similar library for complex visualizations
    drawPrerequisiteTree();
});

function drawPrerequisiteTree() {
    // Simplified tree drawing
    const svg = document.getElementById('prerequisiteTree');
    // Add tree visualization logic here
}
</script>
@endsection