@extends('layouts.app')

@section('title', 'GPA Calculator')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">GPA Calculator</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('student.grades') }}">Grades</a></li>
                    <li class="breadcrumb-item active">GPA Calculator</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <!-- Calculator Section -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Calculate Your GPA</h5>
                </div>
                <div class="card-body">
                    <!-- Current Courses -->
                    <div class="mb-4">
                        <h6 class="font-weight-bold">Current Semester Courses</h6>
                        <p class="text-muted small">Enter your expected grades to calculate projected GPA</p>
                        
                        <div id="current-courses">
                            @if(isset($currentEnrollments) && $currentEnrollments->count() > 0)
                                @foreach($currentEnrollments as $enrollment)
                                <div class="course-row mb-3 p-3 border rounded">
                                    <div class="row align-items-center">
                                        <div class="col-md-5">
                                            <strong>{{ $enrollment->courseSection->course->course_code }}</strong><br>
                                            <small class="text-muted">{{ $enrollment->courseSection->course->title }}</small>
                                        </div>
                                        <div class="col-md-2">
                                            <span class="badge badge-info">{{ $enrollment->courseSection->course->credits }} Credits</span>
                                        </div>
                                        <div class="col-md-3">
                                            <select class="form-control grade-select" 
                                                    data-credits="{{ $enrollment->courseSection->course->credits }}"
                                                    data-course="{{ $enrollment->courseSection->course->course_code }}">
                                                <option value="">Select Grade</option>
                                                <option value="4.00">A (4.00)</option>
                                                <option value="3.67">A- (3.67)</option>
                                                <option value="3.33">B+ (3.33)</option>
                                                <option value="3.00">B (3.00)</option>
                                                <option value="2.67">B- (2.67)</option>
                                                <option value="2.33">C+ (2.33)</option>
                                                <option value="2.00">C (2.00)</option>
                                                <option value="1.67">C- (1.67)</option>
                                                <option value="1.33">D+ (1.33)</option>
                                                <option value="1.00">D (1.00)</option>
                                                <option value="0.00">F (0.00)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2 text-right">
                                            <span class="quality-points">0.00</span> pts
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> No current enrollments found.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Add Custom Course -->
                    <div class="mb-4">
                        <h6 class="font-weight-bold">Add Custom Course</h6>
                        <p class="text-muted small">Add additional courses for what-if scenarios</p>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" class="form-control" id="custom-course-name" placeholder="Course Name">
                            </div>
                            <div class="col-md-2">
                                <input type="number" class="form-control" id="custom-credits" placeholder="Credits" min="0" max="6" step="0.5">
                            </div>
                            <div class="col-md-3">
                                <select class="form-control" id="custom-grade">
                                    <option value="">Select Grade</option>
                                    <option value="4.00">A (4.00)</option>
                                    <option value="3.67">A- (3.67)</option>
                                    <option value="3.33">B+ (3.33)</option>
                                    <option value="3.00">B (3.00)</option>
                                    <option value="2.67">B- (2.67)</option>
                                    <option value="2.33">C+ (2.33)</option>
                                    <option value="2.00">C (2.00)</option>
                                    <option value="1.67">C- (1.67)</option>
                                    <option value="1.33">D+ (1.33)</option>
                                    <option value="1.00">D (1.00)</option>
                                    <option value="0.00">F (0.00)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-primary btn-block" id="add-custom-course">
                                    <i class="fas fa-plus"></i> Add Course
                                </button>
                            </div>
                        </div>
                        
                        <div id="custom-courses" class="mt-3"></div>
                    </div>

                    <!-- Calculate Button -->
                    <div class="text-center">
                        <button class="btn btn-success btn-lg" id="calculate-gpa">
                            <i class="fas fa-calculator"></i> Calculate GPA
                        </button>
                        <button class="btn btn-secondary btn-lg ml-2" id="reset-calculator">
                            <i class="fas fa-redo"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <div class="col-lg-4">
            <!-- Projected GPA -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Projected Results</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <h2 class="display-4 mb-0" id="projected-gpa">0.00</h2>
                        <p class="text-muted">Semester GPA</p>
                    </div>
                    
                    <div class="row text-center">
                        <div class="col-6">
                            <h5 id="total-credits">0</h5>
                            <small class="text-muted">Total Credits</small>
                        </div>
                        <div class="col-6">
                            <h5 id="total-points">0.00</h5>
                            <small class="text-muted">Quality Points</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Impact on Cumulative GPA -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Impact on Cumulative GPA</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="font-weight-bold">Current Cumulative GPA</label>
                        <p class="h4 text-primary">{{ number_format($cumulativeGPA ?? 0, 2) }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="font-weight-bold">Projected New Cumulative GPA</label>
                        <p class="h4 text-success" id="new-cumulative-gpa">{{ number_format($cumulativeGPA ?? 0, 2) }}</p>
                    </div>
                    
                    <div class="change-indicator" id="gpa-change" style="display: none;">
                        <div class="alert alert-info">
                            <i class="fas fa-chart-line"></i>
                            <span id="change-text"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grade Scale Reference -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Grade Scale Reference</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Grade</th>
                                <th>Points</th>
                                <th>Range</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>A</td><td>4.00</td><td>93-100</td></tr>
                            <tr><td>A-</td><td>3.67</td><td>90-92</td></tr>
                            <tr><td>B+</td><td>3.33</td><td>87-89</td></tr>
                            <tr><td>B</td><td>3.00</td><td>83-86</td></tr>
                            <tr><td>B-</td><td>2.67</td><td>80-82</td></tr>
                            <tr><td>C+</td><td>2.33</td><td>77-79</td></tr>
                            <tr><td>C</td><td>2.00</td><td>73-76</td></tr>
                            <tr><td>C-</td><td>1.67</td><td>70-72</td></tr>
                            <tr><td>D+</td><td>1.33</td><td>67-69</td></tr>
                            <tr><td>D</td><td>1.00</td><td>60-66</td></tr>
                            <tr><td>F</td><td>0.00</td><td>0-59</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tips Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-lightbulb"></i> GPA Calculator Tips</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h6 class="font-weight-bold">How to Use</h6>
                            <ul class="small">
                                <li>Select expected grades for current courses</li>
                                <li>Add custom courses for what-if scenarios</li>
                                <li>Click Calculate to see projected GPA</li>
                                <li>View impact on cumulative GPA</li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <h6 class="font-weight-bold">Understanding GPA</h6>
                            <ul class="small">
                                <li>GPA = Total Quality Points ÷ Total Credits</li>
                                <li>Quality Points = Grade Points × Credits</li>
                                <li>Cumulative GPA includes all semesters</li>
                                <li>Major GPA includes only major courses</li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <h6 class="font-weight-bold">Academic Standing</h6>
                            <ul class="small">
                                <li>Dean's List: 3.50+ GPA</li>
                                <li>Good Standing: 2.00+ GPA</li>
                                <li>Academic Probation: Below 2.00</li>
                                <li>Graduation: 2.00+ cumulative GPA</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Store current cumulative stats
    let currentCumulativeGPA = {{ $cumulativeGPA ?? 0 }};
    let currentTotalCredits = {{ $totalCredits ?? 0 }};
    let currentTotalPoints = currentCumulativeGPA * currentTotalCredits;

    // Calculate quality points when grade is selected
    $('.grade-select').on('change', function() {
        const credits = parseFloat($(this).data('credits'));
        const gradePoints = parseFloat($(this).val()) || 0;
        const qualityPoints = (credits * gradePoints).toFixed(2);
        $(this).closest('.course-row').find('.quality-points').text(qualityPoints);
    });

    // Add custom course
    $('#add-custom-course').click(function() {
        const courseName = $('#custom-course-name').val();
        const credits = $('#custom-credits').val();
        const grade = $('#custom-grade').val();
        
        if (!courseName || !credits || !grade) {
            alert('Please fill in all fields');
            return;
        }
        
        const qualityPoints = (parseFloat(credits) * parseFloat(grade)).toFixed(2);
        
        const customCourseHtml = `
            <div class="custom-course-row mb-3 p-3 border rounded bg-light">
                <div class="row align-items-center">
                    <div class="col-md-5">
                        <strong>${courseName}</strong><br>
                        <small class="text-muted">Custom Course</small>
                    </div>
                    <div class="col-md-2">
                        <span class="badge badge-warning">${credits} Credits</span>
                    </div>
                    <div class="col-md-3">
                        <span class="selected-grade" data-credits="${credits}" data-grade="${grade}">
                            Grade: ${$('#custom-grade option:selected').text()}
                        </span>
                    </div>
                    <div class="col-md-2 text-right">
                        <span class="quality-points">${qualityPoints}</span> pts
                        <button class="btn btn-sm btn-danger remove-custom ml-2">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        $('#custom-courses').append(customCourseHtml);
        
        // Clear inputs
        $('#custom-course-name').val('');
        $('#custom-credits').val('');
        $('#custom-grade').val('');
    });

    // Remove custom course
    $(document).on('click', '.remove-custom', function() {
        $(this).closest('.custom-course-row').remove();
    });

    // Calculate GPA
    $('#calculate-gpa').click(function() {
        let totalCredits = 0;
        let totalQualityPoints = 0;
        
        // Calculate from current courses
        $('.grade-select').each(function() {
            if ($(this).val()) {
                const credits = parseFloat($(this).data('credits'));
                const gradePoints = parseFloat($(this).val());
                totalCredits += credits;
                totalQualityPoints += credits * gradePoints;
            }
        });
        
        // Calculate from custom courses
        $('.selected-grade').each(function() {
            const credits = parseFloat($(this).data('credits'));
            const gradePoints = parseFloat($(this).data('grade'));
            totalCredits += credits;
            totalQualityPoints += credits * gradePoints;
        });
        
        // Calculate semester GPA
        const semesterGPA = totalCredits > 0 ? (totalQualityPoints / totalCredits) : 0;
        
        // Calculate new cumulative GPA
        const newTotalCredits = currentTotalCredits + totalCredits;
        const newTotalPoints = currentTotalPoints + totalQualityPoints;
        const newCumulativeGPA = newTotalCredits > 0 ? (newTotalPoints / newTotalCredits) : 0;
        
        // Update display
        $('#projected-gpa').text(semesterGPA.toFixed(2));
        $('#total-credits').text(totalCredits);
        $('#total-points').text(totalQualityPoints.toFixed(2));
        $('#new-cumulative-gpa').text(newCumulativeGPA.toFixed(2));
        
        // Show change indicator
        if (totalCredits > 0) {
            const change = newCumulativeGPA - currentCumulativeGPA;
            const changeText = change >= 0 
                ? `GPA will increase by ${Math.abs(change).toFixed(2)} points`
                : `GPA will decrease by ${Math.abs(change).toFixed(2)} points`;
            
            $('#change-text').text(changeText);
            $('#gpa-change').show();
            
            // Color code the new GPA
            if (change > 0) {
                $('#new-cumulative-gpa').removeClass('text-danger').addClass('text-success');
            } else if (change < 0) {
                $('#new-cumulative-gpa').removeClass('text-success').addClass('text-danger');
            }
        }
    });

    // Reset calculator
    $('#reset-calculator').click(function() {
        $('.grade-select').val('');
        $('.quality-points').text('0.00');
        $('#custom-courses').empty();
        $('#projected-gpa').text('0.00');
        $('#total-credits').text('0');
        $('#total-points').text('0.00');
        $('#new-cumulative-gpa').text(currentCumulativeGPA.toFixed(2));
        $('#gpa-change').hide();
        $('#new-cumulative-gpa').removeClass('text-success text-danger').addClass('text-primary');
    });
</script>
@endpush
@endsection