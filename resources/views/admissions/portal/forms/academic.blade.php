{{-- resources/views/admissions/portal/forms/academic.blade.php --}}
<div class="academic-section">
    <h4 class="mb-4"><i class="fas fa-graduation-cap me-2"></i>Academic Information</h4>
    
    @if($application->application_type === 'freshman')
        {{-- High School Section for Freshmen --}}
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">High School Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="high_school_name" class="form-label">High School Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="high_school_name" name="high_school_name" 
                               value="{{ old('high_school_name', $application->high_school_name) }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="high_school_country" class="form-label">Country</label>
                        <input type="text" class="form-control" id="high_school_country" name="high_school_country" 
                               value="{{ old('high_school_country', $application->high_school_country ?? 'Liberia') }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="high_school_graduation_date" class="form-label">Graduation Date <span class="text-danger">*</span></label>
                        @php
                            $graduationDate = $application->high_school_graduation_date;
                            if ($graduationDate && !($graduationDate instanceof \Carbon\Carbon)) {
                                $graduationDate = \Carbon\Carbon::parse($graduationDate);
                            }
                        @endphp
                        <input type="date" class="form-control" id="high_school_graduation_date" name="high_school_graduation_date" 
                               value="{{ old('high_school_graduation_date', $graduationDate ? $graduationDate->format('Y-m-d') : '') }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="high_school_diploma_type" class="form-label">Diploma Type</label>
                        <select class="form-select" id="high_school_diploma_type" name="high_school_diploma_type">
                            <option value="">Select...</option>
                            <option value="regular" {{ $application->high_school_diploma_type == 'regular' ? 'selected' : '' }}>Regular Diploma</option>
                            <option value="honors" {{ $application->high_school_diploma_type == 'honors' ? 'selected' : '' }}>Honors Diploma</option>
                            <option value="wassce" {{ $application->high_school_diploma_type == 'wassce' ? 'selected' : '' }}>WASSCE Certificate</option>
                            <option value="ib" {{ $application->high_school_diploma_type == 'ib' ? 'selected' : '' }}>IB Diploma</option>
                            <option value="ged" {{ $application->high_school_diploma_type == 'ged' ? 'selected' : '' }}>GED</option>
                            <option value="other" {{ $application->high_school_diploma_type == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="class_rank" class="form-label">Class Rank (if available)</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="class_rank" name="class_rank" 
                                   value="{{ old('class_rank', $application->class_rank) }}" min="1" placeholder="Rank">
                            <span class="input-group-text">of</span>
                            <input type="number" class="form-control" id="class_size" name="class_size" 
                                   value="{{ old('class_size', $application->class_size) }}" min="1" placeholder="Total">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    
    @if($application->application_type === 'transfer' || $application->application_type === 'graduate')
        {{-- Previous College Information --}}
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Previous College/University Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="previous_institution" class="form-label">Institution Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="previous_institution" name="previous_institution" 
                               value="{{ old('previous_institution', $application->previous_institution) }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="previous_institution_country" class="form-label">Country</label>
                        <input type="text" class="form-control" id="previous_institution_country" name="previous_institution_country" 
                               value="{{ old('previous_institution_country', $application->previous_institution_country ?? 'Liberia') }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="previous_degree" class="form-label">Degree Earned/Pursuing</label>
                        <input type="text" class="form-control" id="previous_degree" name="previous_degree" 
                               value="{{ old('previous_degree', $application->previous_degree) }}"
                               placeholder="e.g., Bachelor of Science">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="previous_major" class="form-label">Major/Field of Study</label>
                        <input type="text" class="form-control" id="previous_major" name="previous_major" 
                               value="{{ old('previous_major', $application->previous_major) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="previous_institution_graduation_date" class="form-label">Graduation Date</label>
                        @php
                            $prevGradDate = $application->previous_institution_graduation_date;
                            if ($prevGradDate && !($prevGradDate instanceof \Carbon\Carbon)) {
                                $prevGradDate = \Carbon\Carbon::parse($prevGradDate);
                            }
                        @endphp
                        <input type="date" class="form-control" id="previous_institution_graduation_date" 
                               name="previous_institution_graduation_date" 
                               value="{{ old('previous_institution_graduation_date', $prevGradDate ? $prevGradDate->format('Y-m-d') : '') }}">
                    </div>
                </div>
            </div>
        </div>
    @else
        {{-- For Freshman and other types that don't have college, use high school as previous institution --}}
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Previous Education</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="previous_institution" class="form-label">Last School Attended <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="previous_institution" name="previous_institution" 
                               value="{{ old('previous_institution', $application->previous_institution ?? $application->high_school_name) }}" 
                               placeholder="Enter your high school or last educational institution"
                               required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="previous_institution_country" class="form-label">Country</label>
                        <input type="text" class="form-control" id="previous_institution_country" name="previous_institution_country" 
                               value="{{ old('previous_institution_country', $application->previous_institution_country ?? 'Liberia') }}">
                    </div>
                </div>
            </div>
        </div>
    @endif
    
    {{-- Common Academic Performance Section --}}
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Academic Performance</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="previous_gpa" class="form-label">GPA <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="previous_gpa" name="previous_gpa" 
                           value="{{ old('previous_gpa', $application->previous_gpa) }}" 
                           step="0.01" min="0" max="5" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="gpa_scale" class="form-label">GPA Scale <span class="text-danger">*</span></label>
                    <select class="form-select" id="gpa_scale" name="gpa_scale" required>
                        <option value="">Select Scale</option>
                        <option value="4.0" {{ $application->gpa_scale == '4.0' ? 'selected' : '' }}>4.0 Scale</option>
                        <option value="5.0" {{ $application->gpa_scale == '5.0' ? 'selected' : '' }}>5.0 Scale</option>
                        <option value="100" {{ $application->gpa_scale == '100' ? 'selected' : '' }}>100 Point Scale</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="grading_system" class="form-label">Grading System Notes</label>
                    <input type="text" class="form-control" id="grading_system" name="grading_system" 
                           value="{{ old('grading_system', $application->grading_system) }}"
                           placeholder="Any additional information about your grading system">
                </div>
            </div>
            
            @if($application->application_type === 'graduate')
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="thesis_title" class="form-label">Thesis/Research Title (if applicable)</label>
                        <input type="text" class="form-control" id="thesis_title" name="thesis_title" 
                               value="{{ old('thesis_title', $application->thesis_title) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="thesis_advisor" class="form-label">Thesis Advisor/Supervisor</label>
                        <input type="text" class="form-control" id="thesis_advisor" name="thesis_advisor" 
                               value="{{ old('thesis_advisor', $application->thesis_advisor) }}">
                    </div>
                </div>
            @endif
        </div>
    </div>
    
    @if($application->application_type === 'readmission')
        {{-- Previous Enrollment at This Institution --}}
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Previous Enrollment Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="previous_student_id" class="form-label">Previous Student ID</label>
                        <input type="text" class="form-control" id="previous_student_id" name="previous_student_id" 
                               value="{{ old('previous_student_id', $application->previous_student_id) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="last_enrollment_term" class="form-label">Last Term Enrolled</label>
                        <input type="text" class="form-control" id="last_enrollment_term" name="last_enrollment_term" 
                               value="{{ old('last_enrollment_term', $application->last_enrollment_term) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="reason_for_leaving" class="form-label">Reason for Leaving</label>
                        <select class="form-select" id="reason_for_leaving" name="reason_for_leaving">
                            <option value="">Select...</option>
                            <option value="academic" {{ $application->reason_for_leaving == 'academic' ? 'selected' : '' }}>Academic</option>
                            <option value="financial" {{ $application->reason_for_leaving == 'financial' ? 'selected' : '' }}>Financial</option>
                            <option value="personal" {{ $application->reason_for_leaving == 'personal' ? 'selected' : '' }}>Personal</option>
                            <option value="medical" {{ $application->reason_for_leaving == 'medical' ? 'selected' : '' }}>Medical</option>
                            <option value="other" {{ $application->reason_for_leaving == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>