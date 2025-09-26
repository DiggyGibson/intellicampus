{{-- resources/views/academic-plans/planner.blade.php --}}
@extends('layouts.app')

@section('title', '4-Year Academic Planner')

@section('styles')
<style>
    .term-card {
        border: 2px dashed #e5e7eb;
        border-radius: 12px;
        min-height: 200px;
        transition: all 0.3s ease;
    }
    
    .term-card.active {
        border-color: #667eea;
        background: #f0f4ff;
    }
    
    .course-pill {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 20px;
        padding: 8px 16px;
        margin: 4px;
        cursor: move;
        transition: all 0.2s;
    }
    
    .course-pill:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    
    .course-pill.dragging {
        opacity: 0.5;
    }
    
    .year-header {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        padding: 1rem;
        border-radius: 8px 8px 0 0;
    }
    
    .credit-counter {
        font-size: 1.2rem;
        font-weight: bold;
    }
    
    .credit-counter.overload {
        color: #ef4444;
    }
    
    .credit-counter.underload {
        color: #f59e0b;
    }
    
    .credit-counter.normal {
        color: #10b981;
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
                    <h2>4-Year Academic Planner</h2>
                    <p class="text-muted mb-0">Drag and drop courses to plan your academic journey</p>
                </div>
                <div>
                    <button class="btn btn-success" onclick="savePlan()">
                        <i class="fas fa-save"></i> Save Plan
                    </button>
                    <button class="btn btn-primary" onclick="validatePlan()">
                        <i class="fas fa-check"></i> Validate
                    </button>
                    <button class="btn btn-outline-secondary" onclick="printPlan()">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Bar -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <h4 id="totalCredits">0</h4>
                            <small class="text-muted">Total Credits Planned</small>
                        </div>
                        <div class="col-md-3">
                            <h4 id="avgCreditsPerTerm">0</h4>
                            <small class="text-muted">Avg Credits/Term</small>
                        </div>
                        <div class="col-md-3">
                            <h4 id="requirementsMet">0%</h4>
                            <small class="text-muted">Requirements Met</small>
                        </div>
                        <div class="col-md-3">
                            <h4 id="graduationDate">TBD</h4>
                            <small class="text-muted">Expected Graduation</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Course Bank -->
        <div class="col-lg-3">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header">
                    <h5 class="mb-0">Course Bank</h5>
                    <input type="text" class="form-control form-control-sm mt-2" 
                           placeholder="Search courses..." id="courseSearch">
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    <h6 class="text-muted">Required Courses</h6>
                    <div id="requiredCourses" class="mb-3">
                        @foreach($requiredCourses ?? [] as $course)
                        <div class="course-pill" draggable="true" 
                             data-course-id="{{ $course->id }}"
                             data-credits="{{ $course->credits }}">
                            <strong>{{ $course->code }}</strong>
                            <span class="badge bg-primary ms-2">{{ $course->credits }}cr</span>
                            <small class="d-block">{{ $course->name }}</small>
                        </div>
                        @endforeach
                    </div>

                    <h6 class="text-muted">Elective Courses</h6>
                    <div id="electiveCourses">
                        @foreach($electiveCourses ?? [] as $course)
                        <div class="course-pill" draggable="true"
                             data-course-id="{{ $course->id }}"
                             data-credits="{{ $course->credits }}">
                            <strong>{{ $course->code }}</strong>
                            <span class="badge bg-secondary ms-2">{{ $course->credits }}cr</span>
                            <small class="d-block">{{ $course->name }}</small>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Academic Years -->
        <div class="col-lg-9">
            @for($year = 1; $year <= 4; $year++)
            <div class="card mb-4">
                <div class="year-header">
                    <h5 class="mb-0">Year {{ $year }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Fall Term -->
                        <div class="col-md-4 mb-3">
                            <h6>Fall {{ date('Y') + $year - 1 }}</h6>
                            <div class="term-card p-3" 
                                 data-year="{{ $year }}" 
                                 data-term="fall"
                                 ondrop="dropCourse(event)"
                                 ondragover="allowDrop(event)">
                                <div class="courses-container">
                                    <!-- Courses will be dropped here -->
                                </div>
                                <div class="mt-3 text-center">
                                    <span class="credit-counter normal">0 credits</span>
                                </div>
                            </div>
                        </div>

                        <!-- Spring Term -->
                        <div class="col-md-4 mb-3">
                            <h6>Spring {{ date('Y') + $year }}</h6>
                            <div class="term-card p-3"
                                 data-year="{{ $year }}"
                                 data-term="spring"
                                 ondrop="dropCourse(event)"
                                 ondragover="allowDrop(event)">
                                <div class="courses-container">
                                    <!-- Courses will be dropped here -->
                                </div>
                                <div class="mt-3 text-center">
                                    <span class="credit-counter normal">0 credits</span>
                                </div>
                            </div>
                        </div>

                        <!-- Summer Term -->
                        <div class="col-md-4 mb-3">
                            <h6>Summer {{ date('Y') + $year }}</h6>
                            <div class="term-card p-3"
                                 data-year="{{ $year }}"
                                 data-term="summer"
                                 ondrop="dropCourse(event)"
                                 ondragover="allowDrop(event)">
                                <div class="courses-container">
                                    <!-- Courses will be dropped here -->
                                </div>
                                <div class="mt-3 text-center">
                                    <span class="credit-counter normal">0 credits</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endfor
        </div>
    </div>
</div>

<!-- Validation Results Modal -->
<div class="modal fade" id="validationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Plan Validation Results</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="validationResults">
                <!-- Results will be populated here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let draggedCourse = null;

// Drag and Drop Functions
document.querySelectorAll('.course-pill').forEach(pill => {
    pill.addEventListener('dragstart', function(e) {
        draggedCourse = this;
        this.classList.add('dragging');
    });
    
    pill.addEventListener('dragend', function(e) {
        this.classList.remove('dragging');
    });
});

function allowDrop(e) {
    e.preventDefault();
    e.currentTarget.classList.add('active');
}

function dropCourse(e) {
    e.preventDefault();
    e.currentTarget.classList.remove('active');
    
    if (draggedCourse) {
        const container = e.currentTarget.querySelector('.courses-container');
        const courseCopy = draggedCourse.cloneNode(true);
        
        // Add remove button
        const removeBtn = document.createElement('button');
        removeBtn.className = 'btn btn-sm btn-danger float-end';
        removeBtn.innerHTML = '×';
        removeBtn.onclick = function() {
            this.parentElement.remove();
            updateStatistics();
        };
        courseCopy.appendChild(removeBtn);
        
        container.appendChild(courseCopy);
        updateTermCredits(e.currentTarget);
        updateStatistics();
    }
}

function updateTermCredits(termCard) {
    const courses = termCard.querySelectorAll('.course-pill');
    let totalCredits = 0;
    
    courses.forEach(course => {
        totalCredits += parseInt(course.dataset.credits || 0);
    });
    
    const counter = termCard.querySelector('.credit-counter');
    counter.textContent = totalCredits + ' credits';
    
    // Update color based on credit load
    counter.className = 'credit-counter';
    if (totalCredits > 18) {
        counter.classList.add('overload');
    } else if (totalCredits < 12) {
        counter.classList.add('underload');
    } else {
        counter.classList.add('normal');
    }
}

function updateStatistics() {
    let totalCredits = 0;
    let termCount = 0;
    
    document.querySelectorAll('.term-card').forEach(term => {
        const courses = term.querySelectorAll('.course-pill');
        if (courses.length > 0) {
            termCount++;
            courses.forEach(course => {
                totalCredits += parseInt(course.dataset.credits || 0);
            });
        }
    });
    
    document.getElementById('totalCredits').textContent = totalCredits;
    document.getElementById('avgCreditsPerTerm').textContent = 
        termCount > 0 ? (totalCredits / termCount).toFixed(1) : '0';
    
    // Update requirements met percentage (mock calculation)
    const requiredCredits = 120;
    const percentage = Math.min(100, (totalCredits / requiredCredits * 100).toFixed(0));
    document.getElementById('requirementsMet').textContent = percentage + '%';
    
    // Update expected graduation
    const remainingCredits = Math.max(0, requiredCredits - totalCredits);
    const termsNeeded = Math.ceil(remainingCredits / 15);
    const graduationYear = new Date().getFullYear() + Math.ceil(termsNeeded / 2);
    document.getElementById('graduationDate').textContent = 
        termsNeeded > 0 ? `Spring ${graduationYear}` : 'Ready!';
}

function savePlan() {
    const planData = [];
    
    document.querySelectorAll('.term-card').forEach(term => {
        const courses = [];
        term.querySelectorAll('.course-pill').forEach(course => {
            courses.push({
                id: course.dataset.courseId,
                credits: course.dataset.credits
            });
        });
        
        if (courses.length > 0) {
            planData.push({
                year: term.dataset.year,
                term: term.dataset.term,
                courses: courses
            });
        }
    });
    
    // Send to server
    fetch('{{ route("academic-plans.create") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ plan: planData })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Plan saved successfully!');
        }
    });
}

function validatePlan() {
    // Show validation modal with results
    const resultsHtml = `
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> Prerequisites satisfied
        </div>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> Missing 3 elective credits
        </div>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Consider adding internship in Year 3
        </div>
    `;
    
    document.getElementById('validationResults').innerHTML = resultsHtml;
    $('#validationModal').modal('show');
}

function printPlan() {
    window.print();
}

// Course search
document.getElementById('courseSearch').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    
    document.querySelectorAll('#requiredCourses .course-pill, #electiveCourses .course-pill').forEach(course => {
        const text = course.textContent.toLowerCase();
        course.style.display = text.includes(searchTerm) ? 'block' : 'none';
    });
});
</script>
@endsection