@extends('layouts.app')

@section('title', 'Grade Change Request')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('grades.index') }}">Grade Management</a></li>
                    <li class="breadcrumb-item active">Grade Change Request</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0">Grade Change Request</h1>
            <p class="text-muted">Submit a formal request to change a student's grade</p>
        </div>
    </div>

    <!-- Student Information Card -->
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-user-graduate me-2"></i>Student Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Student ID:</th>
                            <td>{{ $enrollment->student->student_id }}</td>
                        </tr>
                        <tr>
                            <th>Name:</th>
                            <td>
                                <strong>{{ $enrollment->student->first_name }} {{ $enrollment->student->last_name }}</strong>
                            </td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td>{{ $enrollment->student->email }}</td>
                        </tr>
                        <tr>
                            <th>Program:</th>
                            <td>{{ $enrollment->student->program_name }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Course:</th>
                            <td>{{ $enrollment->section->course->code }} - {{ $enrollment->section->course->title }}</td>
                        </tr>
                        <tr>
                            <th>Section:</th>
                            <td>{{ $enrollment->section->section_code }}</td>
                        </tr>
                        <tr>
                            <th>Term:</th>
                            <td>{{ $enrollment->section->term->name }}</td>
                        </tr>
                        <tr>
                            <th>Current Grade:</th>
                            <td>
                                <span class="badge bg-{{ $currentGrade->letter_grade == 'F' ? 'danger' : 'primary' }} fs-6">
                                    {{ $currentGrade->letter_grade ?? 'Not Graded' }}
                                </span>
                                @if($currentGrade && $currentGrade->percentage)
                                    ({{ number_format($currentGrade->percentage, 2) }}%)
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Request Form -->
    <form action="{{ route('grades.change.submit', $enrollment->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Grade Change Details</h5>
            </div>
            <div class="card-body">
                <!-- Current and New Grade -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="current_grade" class="form-label">Current Grade <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('current_grade') is-invalid @enderror" 
                               id="current_grade" name="current_grade" 
                               value="{{ old('current_grade', $currentGrade->letter_grade ?? '') }}" 
                               readonly>
                        @error('current_grade')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="new_grade" class="form-label">New Grade <span class="text-danger">*</span></label>
                        <select class="form-select @error('new_grade') is-invalid @enderror" 
                                id="new_grade" name="new_grade" required>
                            <option value="">Select New Grade</option>
                            <option value="A" {{ old('new_grade') == 'A' ? 'selected' : '' }}>A (93-100%)</option>
                            <option value="A-" {{ old('new_grade') == 'A-' ? 'selected' : '' }}>A- (90-92%)</option>
                            <option value="B+" {{ old('new_grade') == 'B+' ? 'selected' : '' }}>B+ (87-89%)</option>
                            <option value="B" {{ old('new_grade') == 'B' ? 'selected' : '' }}>B (83-86%)</option>
                            <option value="B-" {{ old('new_grade') == 'B-' ? 'selected' : '' }}>B- (80-82%)</option>
                            <option value="C+" {{ old('new_grade') == 'C+' ? 'selected' : '' }}>C+ (77-79%)</option>
                            <option value="C" {{ old('new_grade') == 'C' ? 'selected' : '' }}>C (73-76%)</option>
                            <option value="C-" {{ old('new_grade') == 'C-' ? 'selected' : '' }}>C- (70-72%)</option>
                            <option value="D+" {{ old('new_grade') == 'D+' ? 'selected' : '' }}>D+ (67-69%)</option>
                            <option value="D" {{ old('new_grade') == 'D' ? 'selected' : '' }}>D (63-66%)</option>
                            <option value="F" {{ old('new_grade') == 'F' ? 'selected' : '' }}>F (Below 60%)</option>
                            <option value="I" {{ old('new_grade') == 'I' ? 'selected' : '' }}>I (Incomplete)</option>
                            <option value="W" {{ old('new_grade') == 'W' ? 'selected' : '' }}>W (Withdrawn)</option>
                        </select>
                        @error('new_grade')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Reason for Change -->
                <div class="mb-4">
                    <label for="reason" class="form-label">
                        Reason for Grade Change <span class="text-danger">*</span>
                        <small class="text-muted">(Minimum 50 characters)</small>
                    </label>
                    <textarea class="form-control @error('reason') is-invalid @enderror" 
                              id="reason" name="reason" rows="5" 
                              minlength="50" maxlength="1000" required>{{ old('reason') }}</textarea>
                    @error('reason')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">
                        <span id="charCount">0</span>/1000 characters
                    </small>
                </div>

                <!-- Reason Category -->
                <div class="mb-4">
                    <label class="form-label">Reason Category <span class="text-danger">*</span></label>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="reason_category" 
                                       id="calculation_error" value="calculation_error" required>
                                <label class="form-check-label" for="calculation_error">
                                    Calculation Error
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="reason_category" 
                                       id="missing_work" value="missing_work">
                                <label class="form-check-label" for="missing_work">
                                    Missing Work Submitted
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="reason_category" 
                                       id="grading_error" value="grading_error">
                                <label class="form-check-label" for="grading_error">
                                    Grading Error
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="reason_category" 
                                       id="medical_emergency" value="medical_emergency">
                                <label class="form-check-label" for="medical_emergency">
                                    Medical Emergency
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="reason_category" 
                                       id="technical_issue" value="technical_issue">
                                <label class="form-check-label" for="technical_issue">
                                    Technical Issue
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="reason_category" 
                                       id="other" value="other">
                                <label class="form-check-label" for="other">
                                    Other (Explain in detail)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Supporting Documents -->
                <div class="mb-4">
                    <label for="supporting_documents" class="form-label">
                        Supporting Documents 
                        <small class="text-muted">(Optional - PDF, DOC, DOCX, max 5MB)</small>
                    </label>
                    <input type="file" class="form-control @error('supporting_documents') is-invalid @enderror" 
                           id="supporting_documents" name="supporting_documents" 
                           accept=".pdf,.doc,.docx">
                    @error('supporting_documents')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Important Notes -->
                <div class="alert alert-warning">
                    <h6 class="alert-heading">Important Notes:</h6>
                    <ul class="mb-0">
                        <li>Grade change requests require department head approval</li>
                        <li>Processing time is typically 3-5 business days</li>
                        <li>You will be notified via email once a decision is made</li>
                        <li>False or misleading information may result in disciplinary action</li>
                    </ul>
                </div>

                <!-- Certification -->
                <div class="form-check mb-4">
                    <input class="form-check-input @error('certify') is-invalid @enderror" 
                           type="checkbox" id="certify" name="certify" value="1" required>
                    <label class="form-check-label" for="certify">
                        <strong>I certify that the information provided is accurate and complete to the best of my knowledge</strong>
                    </label>
                    @error('certify')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Form Actions -->
                <div class="row">
                    <div class="col-md-6">
                        <a href="{{ route('grades.index') }}" class="btn btn-secondary w-100">
                            <i class="fas fa-arrow-left me-2"></i>Cancel
                        </a>
                    </div>
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary w-100" id="submitBtn">
                            <i class="fas fa-paper-plane me-2"></i>Submit Request
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
// Character counter
document.getElementById('reason').addEventListener('input', function() {
    const length = this.value.length;
    document.getElementById('charCount').textContent = length;
    
    if (length < 50) {
        this.classList.add('is-invalid');
    } else {
        this.classList.remove('is-invalid');
    }
});

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const newGrade = document.getElementById('new_grade').value;
    const currentGrade = document.getElementById('current_grade').value;
    
    if (newGrade === currentGrade) {
        e.preventDefault();
        alert('New grade must be different from current grade');
        return false;
    }
    
    if (!confirm('Are you sure you want to submit this grade change request?')) {
        e.preventDefault();
        return false;
    }
});

// Initialize character count
document.getElementById('charCount').textContent = document.getElementById('reason').value.length;
</script>
@endpush
@endsection