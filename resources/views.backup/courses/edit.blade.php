@extends('layouts.app')

@section('title', 'Edit Course - ' . $course->code)

@section('breadcrumb')
    <a href="{{ route('courses.index') }}">Course Management</a>
    <i class="fas fa-chevron-right"></i>
    <a href="{{ route('courses.show', $course) }}">{{ $course->code }}</a>
    <i class="fas fa-chevron-right"></i>
    <span>Edit</span>
@endsection

@section('page-actions')
    <a href="{{ route('courses.show', $course) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back to Course
    </a>
@endsection

@section('content')
<div class="container-fluid px-4">
    <!-- Error Alert -->
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h6 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Please fix the following errors:</h6>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Course Header -->
    <div class="card shadow-sm mb-4 bg-light">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="mb-1">Editing: {{ $course->code }} - {{ $course->title }}</h4>
                    <small class="text-muted">Course ID: #{{ $course->id }} | Created: {{ $course->created_at->format('M d, Y') }}</small>
                </div>
                <div class="col-auto">
                    @if($course->is_active)
                        <span class="badge bg-success fs-6"><i class="fas fa-check-circle me-1"></i>Active</span>
                    @else
                        <span class="badge bg-danger fs-6"><i class="fas fa-times-circle me-1"></i>Inactive</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('courses.update', $course) }}" id="editCourseForm">
        @csrf
        @method('PUT')

        <!-- Basic Information Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary bg-gradient text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Basic Information</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <!-- Course Code -->
                    <div class="col-md-4">
                        <label for="code" class="form-label">
                            Course Code <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                            <input type="text" 
                                   name="code" 
                                   id="code" 
                                   value="{{ old('code', $course->code) }}"
                                   class="form-control @error('code') is-invalid @enderror"
                                   placeholder="e.g., CS101"
                                   required>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Department -->
                    <div class="col-md-4">
                        <label for="department" class="form-label">
                            Department <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-building"></i></span>
                            <input type="text" 
                                   name="department" 
                                   id="department" 
                                   value="{{ old('department', $course->department) }}"
                                   class="form-control @error('department') is-invalid @enderror"
                                   placeholder="e.g., Computer Science"
                                   required>
                            @error('department')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Course Level -->
                    <div class="col-md-4">
                        <label for="level" class="form-label">
                            Course Level <span class="text-danger">*</span>
                        </label>
                        <select name="level" 
                                id="level" 
                                class="form-select @error('level') is-invalid @enderror"
                                required>
                            <option value="">Select Level</option>
                            <option value="100" {{ old('level', $course->level) == '100' ? 'selected' : '' }}>100 Level - Freshman</option>
                            <option value="200" {{ old('level', $course->level) == '200' ? 'selected' : '' }}>200 Level - Sophomore</option>
                            <option value="300" {{ old('level', $course->level) == '300' ? 'selected' : '' }}>300 Level - Junior</option>
                            <option value="400" {{ old('level', $course->level) == '400' ? 'selected' : '' }}>400 Level - Senior</option>
                            <option value="500" {{ old('level', $course->level) == '500' ? 'selected' : '' }}>500 Level - Graduate</option>
                            <option value="600" {{ old('level', $course->level) == '600' ? 'selected' : '' }}>600 Level - Graduate</option>
                        </select>
                        @error('level')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Course Title -->
                    <div class="col-12">
                        <label for="title" class="form-label">
                            Course Title <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               name="title" 
                               id="title" 
                               value="{{ old('title', $course->title) }}"
                               class="form-control @error('title') is-invalid @enderror"
                               placeholder="e.g., Introduction to Computer Science"
                               required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Credits -->
                    <div class="col-md-3">
                        <label for="credits" class="form-label">
                            Credits <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-award"></i></span>
                            <input type="number" 
                                   name="credits" 
                                   id="credits" 
                                   value="{{ old('credits', $course->credits) }}"
                                   min="0"
                                   max="12"
                                   class="form-control @error('credits') is-invalid @enderror"
                                   required>
                            @error('credits')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Course Type -->
                    <div class="col-md-3">
                        <label for="type" class="form-label">
                            Course Type <span class="text-danger">*</span>
                        </label>
                        <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                            <option value="">Select Type</option>
                            <option value="required" {{ old('type', $course->type) == 'required' ? 'selected' : '' }}>Required</option>
                            <option value="core" {{ old('type', $course->type) == 'core' ? 'selected' : '' }}>Core</option>
                            <option value="elective" {{ old('type', $course->type) == 'elective' ? 'selected' : '' }}>Elective</option>
                            <option value="general_education" {{ old('type', $course->type) == 'general_education' ? 'selected' : '' }}>General Education</option>
                            <option value="major" {{ old('type', $course->type) == 'major' ? 'selected' : '' }}>Major</option>
                            <option value="minor" {{ old('type', $course->type) == 'minor' ? 'selected' : '' }}>Minor</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Grading Method -->
                    <div class="col-md-3">
                        <label for="grading_method" class="form-label">
                            Grading Method <span class="text-danger">*</span>
                        </label>
                        <select name="grading_method" 
                                id="grading_method" 
                                class="form-select @error('grading_method') is-invalid @enderror"
                                required>
                            <option value="letter" {{ old('grading_method', $course->grading_method) == 'letter' ? 'selected' : '' }}>Letter Grade (A-F)</option>
                            <option value="pass_fail" {{ old('grading_method', $course->grading_method) == 'pass_fail' ? 'selected' : '' }}>Pass/Fail</option>
                            <option value="numeric" {{ old('grading_method', $course->grading_method) == 'numeric' ? 'selected' : '' }}>Numeric (0-100)</option>
                        </select>
                        @error('grading_method')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Course Fee -->
                    <div class="col-md-3">
                        <label for="course_fee" class="form-label">Additional Fee ($)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" 
                                   name="course_fee" 
                                   id="course_fee" 
                                   value="{{ old('course_fee', $course->course_fee) }}"
                                   min="0"
                                   step="0.01"
                                   class="form-control">
                        </div>
                    </div>

                    <!-- Contact Hours -->
                    <div class="col-md-3">
                        <label for="contact_hours" class="form-label">Contact Hours/Week</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-clock"></i></span>
                            <input type="number" 
                                   name="contact_hours" 
                                   id="contact_hours" 
                                   value="{{ old('contact_hours', $course->contact_hours) }}"
                                   min="0"
                                   max="20"
                                   class="form-control">
                        </div>
                    </div>

                    <!-- Lab Hours -->
                    <div class="col-md-3">
                        <label for="lab_hours" class="form-label">Lab Hours/Week</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-flask"></i></span>
                            <input type="number" 
                                   name="lab_hours" 
                                   id="lab_hours" 
                                   value="{{ old('lab_hours', $course->lab_hours) }}"
                                   min="0"
                                   max="10"
                                   class="form-control">
                        </div>
                    </div>

                    <!-- Course Description -->
                    <div class="col-12">
                        <label for="description" class="form-label">
                            Course Description <span class="text-danger">*</span>
                        </label>
                        <textarea name="description" 
                                  id="description" 
                                  rows="4"
                                  class="form-control @error('description') is-invalid @enderror"
                                  placeholder="Provide a detailed description of the course content, objectives, and scope..."
                                  required>{{ old('description', $course->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">This description will appear in the course catalog.</small>
                    </div>

                    <!-- Course Features -->
                    <div class="col-12">
                        <label class="form-label">Course Features</label>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" 
                                           name="has_lab" 
                                           id="has_lab" 
                                           value="1"
                                           {{ old('has_lab', $course->has_lab) ? 'checked' : '' }}
                                           class="form-check-input">
                                    <label for="has_lab" class="form-check-label">
                                        <i class="fas fa-flask text-primary me-1"></i>
                                        Has Lab Component
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" 
                                           name="has_tutorial" 
                                           id="has_tutorial" 
                                           value="1"
                                           {{ old('has_tutorial', $course->has_tutorial) ? 'checked' : '' }}
                                           class="form-check-input">
                                    <label for="has_tutorial" class="form-check-label">
                                        <i class="fas fa-chalkboard-teacher text-info me-1"></i>
                                        Has Tutorial Sessions
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" 
                                           name="is_active" 
                                           id="is_active" 
                                           value="1"
                                           {{ old('is_active', $course->is_active) ? 'checked' : '' }}
                                           class="form-check-input">
                                    <label for="is_active" class="form-check-label">
                                        <i class="fas fa-check-circle text-success me-1"></i>
                                        Active Course
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Academic Details Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success bg-gradient text-white">
                <h5 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Academic Details</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <!-- Prerequisites -->
                    <div class="col-md-6">
                        <label for="prerequisites" class="form-label">
                            <i class="fas fa-lock text-warning me-1"></i>Prerequisites
                        </label>
                        <textarea name="prerequisites" 
                                  id="prerequisites" 
                                  rows="3"
                                  class="form-control"
                                  placeholder="List any prerequisite courses or requirements...">{{ old('prerequisites', $course->prerequisites) }}</textarea>
                        <small class="text-muted">Enter prerequisite course codes separated by commas</small>
                    </div>

                    <!-- Corequisites -->
                    <div class="col-md-6">
                        <label for="corequisites" class="form-label">
                            <i class="fas fa-link text-info me-1"></i>Corequisites
                        </label>
                        <textarea name="corequisites" 
                                  id="corequisites" 
                                  rows="3"
                                  class="form-control"
                                  placeholder="List any corequisite courses...">{{ old('corequisites', $course->corequisites) }}</textarea>
                        <small class="text-muted">Courses that must be taken concurrently</small>
                    </div>

                    <!-- Learning Outcomes -->
                    <div class="col-12">
                        <label for="learning_outcomes" class="form-label">
                            <i class="fas fa-bullseye text-primary me-1"></i>Learning Outcomes
                        </label>
                        <textarea name="learning_outcomes" 
                                  id="learning_outcomes" 
                                  rows="4"
                                  class="form-control"
                                  placeholder="Upon successful completion of this course, students will be able to:&#10;1. &#10;2. &#10;3. ">{{ old('learning_outcomes', $course->learning_outcomes) }}</textarea>
                        <small class="text-muted">Define what students will achieve upon completion</small>
                    </div>

                    <!-- Assessment Methods -->
                    <div class="col-md-6">
                        <label for="assessment_methods" class="form-label">
                            <i class="fas fa-tasks text-danger me-1"></i>Assessment Methods
                        </label>
                        <textarea name="assessment_methods" 
                                  id="assessment_methods" 
                                  rows="3"
                                  class="form-control"
                                  placeholder="e.g., Midterm Exam (30%), Final Exam (40%), Projects (20%), Participation (10%)">{{ old('assessment_methods', $course->assessment_methods) }}</textarea>
                    </div>

                    <!-- Textbooks -->
                    <div class="col-md-6">
                        <label for="textbooks" class="form-label">
                            <i class="fas fa-book text-secondary me-1"></i>Required Textbooks
                        </label>
                        <textarea name="textbooks" 
                                  id="textbooks" 
                                  rows="3"
                                  class="form-control"
                                  placeholder="List required textbooks with ISBN if available...">{{ old('textbooks', $course->textbooks) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Course Statistics (if editing existing course) -->
        @if($course->sections->count() > 0)
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-info bg-gradient text-white">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Course Impact</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded">
                            <h3 class="text-primary mb-0">{{ $course->sections->count() }}</h3>
                            <small class="text-muted">Total Sections</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded">
                            <h3 class="text-success mb-0">{{ $course->sections->where('status', 'open')->count() }}</h3>
                            <small class="text-muted">Active Sections</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded">
                            <h3 class="text-info mb-0">{{ $course->sections->sum('current_enrollment') }}</h3>
                            <small class="text-muted">Students Enrolled</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded">
                            <h3 class="text-warning mb-0">{{ $course->sections->unique('instructor_id')->count() }}</h3>
                            <small class="text-muted">Instructors</small>
                        </div>
                    </div>
                </div>
                <div class="alert alert-warning mt-3 mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Note:</strong> Changes to this course will affect all {{ $course->sections->count() }} associated sections.
                </div>
            </div>
        </div>
        @endif

        <!-- Form Actions -->
        <div class="d-flex justify-content-between mb-4">
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                <i class="fas fa-trash me-2"></i>Delete Course
            </button>
            <div>
                <a href="{{ route('courses.show', $course) }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Update Course
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirm Course Deletion
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this course?</p>
                <div class="alert alert-warning">
                    <strong>Course:</strong> {{ $course->code }} - {{ $course->title }}<br>
                    <strong>Department:</strong> {{ $course->department }}<br>
                    <strong>Credits:</strong> {{ $course->credits }}
                    @if($course->sections->count() > 0)
                        <br><strong>Warning:</strong> This course has {{ $course->sections->count() }} section(s) that will also be deleted!
                    @endif
                </div>
                <p class="text-danger">
                    <i class="fas fa-exclamation-circle"></i> 
                    This action cannot be undone. All course data, sections, and enrollments will be permanently deleted.
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
    .card-header {
        font-weight: 600;
    }
    
    .form-label {
        font-weight: 500;
        color: #495057;
        margin-bottom: 0.5rem;
    }
    
    .text-danger {
        font-weight: bold;
    }
</style>
@endpush

@push('scripts')
<script>
    // Track changes to warn user before leaving
    let formChanged = false;
    const form = document.getElementById('editCourseForm');
    
    form.addEventListener('change', function() {
        formChanged = true;
    });
    
    window.addEventListener('beforeunload', function(e) {
        if (formChanged) {
            e.preventDefault();
            e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
        }
    });
    
    form.addEventListener('submit', function() {
        formChanged = false;
    });
</script>
@endpush