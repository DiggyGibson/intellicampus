{{-- File: resources/views/admissions/admin/review-form.blade.php --}}
@extends('layouts.app')

@section('title', 'Review Application - ' . $application->application_number)

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-1">Review Application</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admissions.admin.dashboard') }}">Admissions</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admissions.admin.applications.index') }}">Applications</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admissions.admin.applications.show', $application->id) }}">{{ $application->application_number }}</a></li>
                    <li class="breadcrumb-item active">Review</li>
                </ol>
            </nav>
        </div>
    </div>

    <form action="{{ route('admissions.admin.reviews.store', $application->id) }}" method="POST" id="reviewForm">
        @csrf
        
        <div class="row">
            {{-- Left Column - Application Summary --}}
            <div class="col-lg-4">
                {{-- Applicant Information Card --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-user"></i> Applicant Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="avatar-lg mx-auto mb-2">
                                <span class="avatar-title rounded-circle bg-primary text-white" style="width: 80px; height: 80px; font-size: 2rem;">
                                    {{ substr($application->first_name, 0, 1) }}{{ substr($application->last_name, 0, 1) }}
                                </span>
                            </div>
                            <h5 class="mb-1">{{ $application->first_name }} {{ $application->last_name }}</h5>
                            <p class="text-muted mb-0">{{ $application->application_number }}</p>
                        </div>
                        
                        <dl class="row small">
                            <dt class="col-6">Application Type:</dt>
                            <dd class="col-6">{{ ucfirst(str_replace('_', ' ', $application->application_type)) }}</dd>
                            
                            <dt class="col-6">Program:</dt>
                            <dd class="col-6">{{ $application->program->code ?? 'N/A' }}</dd>
                            
                            <dt class="col-6">Term:</dt>
                            <dd class="col-6">{{ $application->term->name ?? 'N/A' }}</dd>
                            
                            <dt class="col-6">Previous GPA:</dt>
                            <dd class="col-6">{{ $application->previous_gpa ?? 'N/A' }}</dd>
                            
                            <dt class="col-6">Submitted:</dt>
                            <dd class="col-6">{{ $application->submitted_at ? $application->submitted_at->format('M d, Y') : 'Not submitted' }}</dd>
                        </dl>
                    </div>
                </div>

                {{-- Quick Links Card --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-link"></i> Quick Links</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('admissions.admin.applications.show', $application->id) }}" 
                               class="btn btn-outline-primary btn-sm" target="_blank">
                                <i class="fas fa-external-link-alt"></i> View Full Application
                            </a>
                            <a href="{{ route('admissions.admin.documents.list', $application->id) }}" 
                               class="btn btn-outline-secondary btn-sm" target="_blank">
                                <i class="fas fa-file-alt"></i> View Documents
                            </a>
                            <a href="{{ route('admissions.admin.reviews.compare', $application->id) }}" 
                               class="btn btn-outline-info btn-sm" target="_blank">
                                <i class="fas fa-balance-scale"></i> Compare Reviews
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Previous Reviews Summary --}}
                @if($previousReviews->count() > 0)
                <div class="card shadow-sm">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0"><i class="fas fa-history"></i> Previous Reviews</h5>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-2">{{ $previousReviews->count() }} review(s) submitted</p>
                        <div class="mb-2">
                            <strong>Average Rating:</strong>
                            <span class="badge bg-primary">{{ number_format($previousReviews->avg('overall_rating'), 1) }}/5.0</span>
                        </div>
                        <div>
                            <strong>Recommendations:</strong>
                            <ul class="small mb-0">
                                @foreach($previousReviews->groupBy('recommendation') as $recommendation => $reviews)
                                <li>{{ ucfirst(str_replace('_', ' ', $recommendation)) }}: {{ $reviews->count() }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            {{-- Right Column - Review Form --}}
            <div class="col-lg-8">
                {{-- Rating Criteria Card --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-star"></i> Rating Criteria</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            {{-- Academic Rating --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Academic Performance</label>
                                <div class="rating-input" data-field="academic_rating">
                                    @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star rating-star" data-rating="{{ $i }}"></i>
                                    @endfor
                                    <input type="hidden" name="academic_rating" id="academic_rating" value="{{ old('academic_rating') }}">
                                </div>
                                <small class="text-muted">GPA, test scores, academic achievements</small>
                                @error('academic_rating')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Extracurricular Rating --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Extracurricular Activities</label>
                                <div class="rating-input" data-field="extracurricular_rating">
                                    @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star rating-star" data-rating="{{ $i }}"></i>
                                    @endfor
                                    <input type="hidden" name="extracurricular_rating" id="extracurricular_rating" value="{{ old('extracurricular_rating') }}">
                                </div>
                                <small class="text-muted">Leadership, volunteer work, activities</small>
                                @error('extracurricular_rating')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Essay Rating --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Essays & Personal Statement</label>
                                <div class="rating-input" data-field="essay_rating">
                                    @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star rating-star" data-rating="{{ $i }}"></i>
                                    @endfor
                                    <input type="hidden" name="essay_rating" id="essay_rating" value="{{ old('essay_rating') }}">
                                </div>
                                <small class="text-muted">Writing quality, clarity, compelling narrative</small>
                                @error('essay_rating')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Recommendation Rating --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Recommendation Letters</label>
                                <div class="rating-input" data-field="recommendation_rating">
                                    @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star rating-star" data-rating="{{ $i }}"></i>
                                    @endfor
                                    <input type="hidden" name="recommendation_rating" id="recommendation_rating" value="{{ old('recommendation_rating') }}">
                                </div>
                                <small class="text-muted">Strength of recommendations</small>
                                @error('recommendation_rating')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Interview Rating (if applicable) --}}
                            @if($application->interviews->count() > 0)
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Interview Performance</label>
                                <div class="rating-input" data-field="interview_rating">
                                    @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star rating-star" data-rating="{{ $i }}"></i>
                                    @endfor
                                    <input type="hidden" name="interview_rating" id="interview_rating" value="{{ old('interview_rating') }}">
                                </div>
                                <small class="text-muted">Communication, enthusiasm, fit</small>
                                @error('interview_rating')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            @endif

                            {{-- Overall Rating --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><strong>Overall Rating</strong></label>
                                <div class="rating-input" data-field="overall_rating">
                                    @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star rating-star" data-rating="{{ $i }}"></i>
                                    @endfor
                                    <input type="hidden" name="overall_rating" id="overall_rating" value="{{ old('overall_rating') }}" required>
                                </div>
                                <small class="text-muted">Overall assessment of the candidate</small>
                                @error('overall_rating')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Detailed Comments Card --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-comment"></i> Detailed Evaluation</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            {{-- Academic Comments --}}
                            <div class="col-md-6 mb-3">
                                <label for="academic_comments" class="form-label">Academic Comments</label>
                                <textarea class="form-control" id="academic_comments" name="academic_comments" rows="3"
                                          placeholder="Comments on academic qualifications...">{{ old('academic_comments') }}</textarea>
                                @error('academic_comments')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Extracurricular Comments --}}
                            <div class="col-md-6 mb-3">
                                <label for="extracurricular_comments" class="form-label">Extracurricular Comments</label>
                                <textarea class="form-control" id="extracurricular_comments" name="extracurricular_comments" rows="3"
                                          placeholder="Comments on activities and leadership...">{{ old('extracurricular_comments') }}</textarea>
                                @error('extracurricular_comments')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Essay Comments --}}
                            <div class="col-md-6 mb-3">
                                <label for="essay_comments" class="form-label">Essay Comments</label>
                                <textarea class="form-control" id="essay_comments" name="essay_comments" rows="3"
                                          placeholder="Comments on essays and writing...">{{ old('essay_comments') }}</textarea>
                                @error('essay_comments')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Strengths --}}
                            <div class="col-md-6 mb-3">
                                <label for="strengths" class="form-label">Key Strengths</label>
                                <textarea class="form-control" id="strengths" name="strengths" rows="3"
                                          placeholder="Identify the applicant's main strengths...">{{ old('strengths') }}</textarea>
                                @error('strengths')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Weaknesses --}}
                            <div class="col-md-6 mb-3">
                                <label for="weaknesses" class="form-label">Areas of Concern</label>
                                <textarea class="form-control" id="weaknesses" name="weaknesses" rows="3"
                                          placeholder="Note any concerns or weaknesses...">{{ old('weaknesses') }}</textarea>
                                @error('weaknesses')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Additional Comments --}}
                            <div class="col-md-6 mb-3">
                                <label for="additional_comments" class="form-label">Additional Comments</label>
                                <textarea class="form-control" id="additional_comments" name="additional_comments" rows="3"
                                          placeholder="Any other relevant observations...">{{ old('additional_comments') }}</textarea>
                                @error('additional_comments')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Recommendation Card --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-thumbs-up"></i> Recommendation</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Your Recommendation <span class="text-danger">*</span></label>
                                <div class="recommendation-options">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="recommendation" 
                                               id="strongly_recommend" value="strongly_recommend" 
                                               {{ old('recommendation') == 'strongly_recommend' ? 'checked' : '' }} required>
                                        <label class="form-check-label" for="strongly_recommend">
                                            <strong class="text-success">Strongly Recommend</strong>
                                            <small class="d-block text-muted">Exceptional candidate, top tier</small>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="recommendation" 
                                               id="recommend" value="recommend"
                                               {{ old('recommendation') == 'recommend' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="recommend">
                                            <strong class="text-primary">Recommend</strong>
                                            <small class="d-block text-muted">Strong candidate, meets requirements</small>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="recommendation" 
                                               id="recommend_with_reservations" value="recommend_with_reservations"
                                               {{ old('recommendation') == 'recommend_with_reservations' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="recommend_with_reservations">
                                            <strong class="text-warning">Recommend with Reservations</strong>
                                            <small class="d-block text-muted">Adequate but has some concerns</small>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="recommendation" 
                                               id="do_not_recommend" value="do_not_recommend"
                                               {{ old('recommendation') == 'do_not_recommend' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="do_not_recommend">
                                            <strong class="text-danger">Do Not Recommend</strong>
                                            <small class="d-block text-muted">Does not meet standards</small>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="recommendation" 
                                               id="defer_decision" value="defer_decision"
                                               {{ old('recommendation') == 'defer_decision' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="defer_decision">
                                            <strong class="text-secondary">Defer Decision</strong>
                                            <small class="d-block text-muted">Need more information or committee review</small>
                                        </label>
                                    </div>
                                </div>
                                @error('recommendation')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Review Stage --}}
                        <div class="row">
                            <div class="col-md-6">
                                <label for="review_stage" class="form-label">Review Stage</label>
                                <select class="form-select" id="review_stage" name="review_stage" required>
                                    <option value="">Select Stage</option>
                                    <option value="initial_review" {{ old('review_stage') == 'initial_review' ? 'selected' : '' }}>Initial Review</option>
                                    <option value="academic_review" {{ old('review_stage') == 'academic_review' ? 'selected' : '' }}>Academic Review</option>
                                    <option value="department_review" {{ old('review_stage') == 'department_review' ? 'selected' : '' }}>Department Review</option>
                                    <option value="committee_review" {{ old('review_stage') == 'committee_review' ? 'selected' : '' }}>Committee Review</option>
                                    <option value="final_review" {{ old('review_stage') == 'final_review' ? 'selected' : '' }}>Final Review</option>
                                </select>
                                @error('review_stage')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Mark as Complete --}}
                            <div class="col-md-6">
                                <label class="form-label d-block">&nbsp;</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="mark_complete" name="mark_complete" value="1">
                                    <label class="form-check-label" for="mark_complete">
                                        Mark this review as complete
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary" name="action" value="save">
                                    <i class="fas fa-save"></i> Save Review
                                </button>
                                <button type="submit" class="btn btn-success" name="action" value="save_and_next">
                                    <i class="fas fa-arrow-right"></i> Save & Review Next
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="saveDraft()">
                                    <i class="fas fa-file-alt"></i> Save as Draft
                                </button>
                                <a href="{{ route('admissions.admin.applications.show', $application->id) }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .rating-input {
        display: inline-block;
        font-size: 1.5rem;
    }
    .rating-star {
        color: #ddd;
        cursor: pointer;
        transition: color 0.2s;
    }
    .rating-star:hover,
    .rating-star.active {
        color: #ffc107;
    }
    .recommendation-options .form-check {
        padding: 10px;
        margin-bottom: 10px;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        transition: background-color 0.2s;
    }
    .recommendation-options .form-check:hover {
        background-color: #f8f9fa;
    }
    .recommendation-options .form-check-input:checked + .form-check-label {
        font-weight: bold;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Star rating functionality
    $('.rating-input').each(function() {
        const container = $(this);
        const field = container.data('field');
        const input = $(`#${field}`);
        const stars = container.find('.rating-star');
        
        // Set initial value
        if (input.val()) {
            stars.each(function(index) {
                if (index < input.val()) {
                    $(this).addClass('active');
                }
            });
        }
        
        stars.on('click', function() {
            const rating = $(this).data('rating');
            input.val(rating);
            
            stars.removeClass('active');
            stars.each(function(index) {
                if (index < rating) {
                    $(this).addClass('active');
                }
            });
        });
        
        stars.on('mouseenter', function() {
            const rating = $(this).data('rating');
            stars.removeClass('active');
            stars.each(function(index) {
                if (index < rating) {
                    $(this).addClass('active');
                }
            });
        });
        
        container.on('mouseleave', function() {
            stars.removeClass('active');
            if (input.val()) {
                stars.each(function(index) {
                    if (index < input.val()) {
                        $(this).addClass('active');
                    }
                });
            }
        });
    });
    
    // Auto-save draft every 30 seconds
    setInterval(function() {
        saveDraft(true); // true = silent save
    }, 30000);
});

function saveDraft(silent = false) {
    const formData = $('#reviewForm').serialize() + '&action=draft';
    
    $.post('{{ route("admissions.admin.reviews.draft", $application->id) }}', formData)
        .done(function(response) {
            if (!silent) {
                toastr.success('Draft saved successfully');
            }
        })
        .fail(function() {
            if (!silent) {
                toastr.error('Failed to save draft');
            }
        });
}

// Warn user about unsaved changes
let formChanged = false;
$('#reviewForm').on('change', 'input, select, textarea', function() {
    formChanged = true;
});

$(window).on('beforeunload', function() {
    if (formChanged) {
        return 'You have unsaved changes. Are you sure you want to leave?';
    }
});

$('#reviewForm').on('submit', function() {
    formChanged = false;
});
</script>
@endpush