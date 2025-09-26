{{-- Review Summary Component --}}
{{-- Path: resources/views/admissions/partials/review-summary.blade.php --}}

@php
    // Calculate average ratings
    $averageRatings = [
        'academic' => $reviews->avg('academic_rating'),
        'extracurricular' => $reviews->avg('extracurricular_rating'),
        'essay' => $reviews->avg('essay_rating'),
        'recommendation' => $reviews->avg('recommendation_rating'),
        'interview' => $reviews->avg('interview_rating'),
        'overall' => $reviews->avg('overall_rating'),
    ];
    
    // Recommendation counts
    $recommendations = $reviews->groupBy('recommendation')->map->count();
    
    // Get review stages
    $reviewStages = [
        'initial_review' => 'Initial Review',
        'academic_review' => 'Academic Review',
        'department_review' => 'Department Review',
        'committee_review' => 'Committee Review',
        'final_review' => 'Final Review',
    ];
    
    // Review status colors
    $recommendationColors = [
        'strongly_recommend' => 'success',
        'recommend' => 'primary',
        'recommend_with_reservations' => 'warning',
        'do_not_recommend' => 'danger',
        'defer_decision' => 'secondary',
    ];
@endphp

<div class="review-summary-container">
    {{-- Overview Card --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-clipboard-check text-primary me-2"></i>
                    Application Review Summary
                </h5>
                <div>
                    <span class="badge bg-primary">{{ $reviews->count() }} Review(s)</span>
                    @if($reviews->where('status', 'completed')->count() === $reviews->count())
                        <span class="badge bg-success ms-2">All Reviews Complete</span>
                    @else
                        <span class="badge bg-warning ms-2">{{ $reviews->where('status', 'pending')->count() }} Pending</span>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="card-body">
            {{-- Applicant Info Summary --}}
            <div class="row mb-4">
                <div class="col-md-8">
                    <h6 class="text-muted mb-3">Applicant Information</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Name:</strong> 
                                {{ $application->first_name }} {{ $application->middle_name }} {{ $application->last_name }}
                            </p>
                            <p class="mb-2">
                                <strong>Application #:</strong> 
                                <code>{{ $application->application_number }}</code>
                            </p>
                            <p class="mb-2">
                                <strong>Program:</strong> 
                                {{ $application->program->name ?? 'N/A' }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>GPA:</strong> 
                                {{ $application->previous_gpa ?? 'N/A' }} / {{ $application->gpa_scale ?? '4.0' }}
                            </p>
                            <p class="mb-2">
                                <strong>Test Scores:</strong>
                                @if($application->test_scores)
                                    @foreach($application->test_scores as $test => $score)
                                        <span class="badge bg-info me-1">{{ $test }}: {{ $score['total'] ?? $score }}</span>
                                    @endforeach
                                @else
                                    N/A
                                @endif
                            </p>
                            <p class="mb-2">
                                <strong>Application Type:</strong> 
                                <span class="badge bg-secondary">{{ ucfirst($application->application_type) }}</span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <h6 class="text-muted mb-3">Overall Rating</h6>
                    <div class="text-center">
                        <div class="h1 mb-0">
                            @if($averageRatings['overall'])
                                <span class="text-{{ $averageRatings['overall'] >= 4 ? 'success' : ($averageRatings['overall'] >= 3 ? 'warning' : 'danger') }}">
                                    {{ number_format($averageRatings['overall'], 1) }}
                                </span>
                                <small class="text-muted">/5.0</small>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </div>
                        <div class="rating-stars mt-2">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star {{ $i <= round($averageRatings['overall']) ? 'text-warning' : 'text-muted' }}"></i>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Rating Categories --}}
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="text-muted mb-3">Rating Breakdown</h6>
                    <div class="rating-categories">
                        @foreach(['academic', 'extracurricular', 'essay', 'recommendation', 'interview'] as $category)
                            @if($averageRatings[$category])
                                <div class="rating-category mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="text-capitalize">{{ str_replace('_', ' ', $category) }}</span>
                                        <span class="badge bg-primary">{{ number_format($averageRatings[$category], 1) }}/5</span>
                                    </div>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar bg-{{ $averageRatings[$category] >= 4 ? 'success' : ($averageRatings[$category] >= 3 ? 'warning' : 'danger') }}" 
                                             role="progressbar" 
                                             style="width: {{ ($averageRatings[$category] / 5) * 100 }}%"
                                             aria-valuenow="{{ $averageRatings[$category] }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="5">
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
            
            {{-- Recommendations Summary --}}
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="text-muted mb-3">Reviewer Recommendations</h6>
                    <div class="recommendations-chart">
                        <div class="row">
                            @foreach($recommendationColors as $rec => $color)
                                @if($recommendations->has($rec))
                                    <div class="col-md-4 col-6 mb-3">
                                        <div class="text-center">
                                            <div class="h3 mb-0 text-{{ $color }}">{{ $recommendations[$rec] }}</div>
                                            <small class="text-muted">{{ ucwords(str_replace('_', ' ', $rec)) }}</small>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Individual Reviews --}}
            <div class="individual-reviews">
                <h6 class="text-muted mb-3">Individual Reviews</h6>
                <div class="accordion" id="reviewsAccordion">
                    @foreach($reviews as $index => $review)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading{{ $index }}">
                                <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" 
                                        type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#collapse{{ $index }}" 
                                        aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" 
                                        aria-controls="collapse{{ $index }}">
                                    <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                        <div>
                                            <strong>{{ $review->reviewer->name ?? 'Anonymous' }}</strong>
                                            <span class="badge bg-{{ $review->status === 'completed' ? 'success' : 'warning' }} ms-2">
                                                {{ ucfirst($review->status) }}
                                            </span>
                                            <small class="text-muted ms-2">
                                                {{ $reviewStages[$review->review_stage] ?? $review->review_stage }}
                                            </small>
                                        </div>
                                        <div>
                                            <span class="badge bg-{{ $recommendationColors[$review->recommendation] ?? 'secondary' }}">
                                                {{ ucwords(str_replace('_', ' ', $review->recommendation ?? 'Pending')) }}
                                            </span>
                                            <span class="badge bg-primary ms-2">
                                                Overall: {{ $review->overall_rating ?? 'N/A' }}/5
                                            </span>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse{{ $index }}" 
                                 class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" 
                                 aria-labelledby="heading{{ $index }}" 
                                 data-bs-parent="#reviewsAccordion">
                                <div class="accordion-body">
                                    {{-- Review Details --}}
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <h6 class="text-muted">Ratings</h6>
                                            <table class="table table-sm">
                                                <tr>
                                                    <td>Academic:</td>
                                                    <td>
                                                        @if($review->academic_rating)
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <i class="fas fa-star {{ $i <= $review->academic_rating ? 'text-warning' : 'text-muted' }} small"></i>
                                                            @endfor
                                                            ({{ $review->academic_rating }}/5)
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Extracurricular:</td>
                                                    <td>
                                                        @if($review->extracurricular_rating)
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <i class="fas fa-star {{ $i <= $review->extracurricular_rating ? 'text-warning' : 'text-muted' }} small"></i>
                                                            @endfor
                                                            ({{ $review->extracurricular_rating }}/5)
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Essay:</td>
                                                    <td>
                                                        @if($review->essay_rating)
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <i class="fas fa-star {{ $i <= $review->essay_rating ? 'text-warning' : 'text-muted' }} small"></i>
                                                            @endfor
                                                            ({{ $review->essay_rating }}/5)
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-muted">Review Information</h6>
                                            <p class="mb-1">
                                                <small class="text-muted">Reviewed on:</small> 
                                                {{ $review->completed_at ? $review->completed_at->format('M d, Y h:i A') : 'In Progress' }}
                                            </p>
                                            <p class="mb-1">
                                                <small class="text-muted">Time Spent:</small> 
                                                {{ $review->review_duration_minutes ?? 'N/A' }} minutes
                                            </p>
                                            <p class="mb-1">
                                                <small class="text-muted">Stage:</small> 
                                                {{ $reviewStages[$review->review_stage] ?? $review->review_stage }}
                                            </p>
                                        </div>
                                    </div>
                                    
                                    {{-- Comments --}}
                                    @if($review->strengths || $review->weaknesses || $review->additional_comments)
                                        <div class="review-comments">
                                            @if($review->strengths)
                                                <div class="mb-3">
                                                    <h6 class="text-success">
                                                        <i class="fas fa-plus-circle me-1"></i> Strengths
                                                    </h6>
                                                    <p class="ms-3">{{ $review->strengths }}</p>
                                                </div>
                                            @endif
                                            
                                            @if($review->weaknesses)
                                                <div class="mb-3">
                                                    <h6 class="text-danger">
                                                        <i class="fas fa-minus-circle me-1"></i> Areas of Concern
                                                    </h6>
                                                    <p class="ms-3">{{ $review->weaknesses }}</p>
                                                </div>
                                            @endif
                                            
                                            @if($review->additional_comments)
                                                <div class="mb-3">
                                                    <h6 class="text-info">
                                                        <i class="fas fa-comment me-1"></i> Additional Comments
                                                    </h6>
                                                    <p class="ms-3">{{ $review->additional_comments }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            {{-- Decision Summary (if available) --}}
            @if($application->decision)
                <div class="decision-summary mt-4 p-3 border rounded bg-light">
                    <h6 class="text-muted mb-3">Admission Decision</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <p class="mb-2">
                                <strong>Decision:</strong>
                                <span class="badge bg-{{ $application->decision === 'admit' ? 'success' : ($application->decision === 'deny' ? 'danger' : 'warning') }} fs-6">
                                    {{ ucfirst($application->decision) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-2">
                                <strong>Decision Date:</strong>
                                {{ $application->decision_date?->format('M d, Y') ?? 'N/A' }}
                            </p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-2">
                                <strong>Decided By:</strong>
                                {{ $application->decisionMaker->name ?? 'System' }}
                            </p>
                        </div>
                    </div>
                    @if($application->decision_reason)
                        <div class="mt-2">
                            <strong>Reason:</strong>
                            <p class="mb-0">{{ $application->decision_reason }}</p>
                        </div>
                    @endif
                    @if($application->admission_conditions && $application->decision === 'conditional_admit')
                        <div class="mt-2">
                            <strong>Conditions:</strong>
                            <p class="mb-0">{{ $application->admission_conditions }}</p>
                        </div>
                    @endif
                </div>
            @endif
            
            {{-- Action Buttons (for authorized users) --}}
            @if($showActions ?? false)
                <div class="mt-4 d-flex justify-content-between">
                    <div>
                        @if(!$application->decision)
                            <button type="button" 
                                    class="btn btn-primary"
                                    onclick="window.location.href='{{ route('admin.admissions.decision', $application->id) }}'">
                                <i class="fas fa-gavel me-2"></i>Make Decision
                            </button>
                        @endif
                        
                        <button type="button" 
                                class="btn btn-outline-secondary"
                                onclick="window.location.href='{{ route('admin.admissions.add-review', $application->id) }}'">
                            <i class="fas fa-plus me-2"></i>Add Review
                        </button>
                    </div>
                    
                    <div>
                        <button type="button" 
                                class="btn btn-outline-primary"
                                onclick="printReviewSummary()">
                            <i class="fas fa-print me-2"></i>Print Summary
                        </button>
                        
                        <button type="button" 
                                class="btn btn-outline-success"
                                onclick="exportReviewSummary()">
                            <i class="fas fa-file-excel me-2"></i>Export
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
    .rating-stars i {
        font-size: 1.2rem;
    }
    
    .rating-category {
        padding: 0.5rem 0;
    }
    
    .review-comments p {
        font-size: 0.95rem;
        line-height: 1.6;
    }
    
    .accordion-button:not(.collapsed) {
        background-color: #f8f9fa;
    }
    
    .decision-summary {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }
</style>
@endpush

@push('scripts')
<script>
    function printReviewSummary() {
        window.print();
    }
    
    function exportReviewSummary() {
        window.location.href = '{{ route("admin.admissions.export-review", $application->id) }}';
    }
</script>
@endpush