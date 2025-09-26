{{-- Application Progress Bar Component --}}
{{-- Path: resources/views/admissions/partials/application-progress.blade.php --}}

@php
    $sections = [
        'personal' => [
            'title' => 'Personal Information',
            'icon' => 'fas fa-user',
            'completed' => !empty($application->first_name) && !empty($application->last_name) && !empty($application->date_of_birth),
        ],
        'contact' => [
            'title' => 'Contact Information',
            'icon' => 'fas fa-envelope',
            'completed' => !empty($application->email) && !empty($application->phone_primary) && !empty($application->current_address),
        ],
        'educational' => [
            'title' => 'Educational Background',
            'icon' => 'fas fa-graduation-cap',
            'completed' => !empty($application->previous_institution) && !empty($application->previous_gpa),
        ],
        'test_scores' => [
            'title' => 'Test Scores',
            'icon' => 'fas fa-clipboard-list',
            'completed' => !empty($application->test_scores) && count($application->test_scores) > 0,
        ],
        'essays' => [
            'title' => 'Essays & Statements',
            'icon' => 'fas fa-pen-fancy',
            'completed' => !empty($application->personal_statement) || !empty($application->statement_of_purpose),
        ],
        'activities' => [
            'title' => 'Activities & Experience',
            'icon' => 'fas fa-trophy',
            'completed' => !empty($application->extracurricular_activities) || !empty($application->work_experience),
        ],
        'references' => [
            'title' => 'References',
            'icon' => 'fas fa-users',
            'completed' => !empty($application->references) && count($application->references) >= 2,
        ],
        'documents' => [
            'title' => 'Supporting Documents',
            'icon' => 'fas fa-file-upload',
            'completed' => $application->documents()->where('is_verified', true)->count() >= $requiredDocumentCount ?? 3,
        ],
    ];
    
    $completedSections = collect($sections)->filter(function($section) {
        return $section['completed'];
    })->count();
    
    $totalSections = count($sections);
    $progressPercentage = $totalSections > 0 ? round(($completedSections / $totalSections) * 100) : 0;
    
    // Determine current section
    $currentSectionIndex = array_search($currentSection ?? 'personal', array_keys($sections));
@endphp

<div class="application-progress-container mb-4">
    {{-- Overall Progress Bar --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-line text-primary me-2"></i>
                    Application Progress
                </h5>
                <div class="text-end">
                    <span class="badge {{ $progressPercentage >= 100 ? 'bg-success' : ($progressPercentage >= 50 ? 'bg-warning' : 'bg-secondary') }}">
                        {{ $progressPercentage }}% Complete
                    </span>
                    <small class="text-muted d-block">{{ $completedSections }} of {{ $totalSections }} sections</small>
                </div>
            </div>
            
            {{-- Progress Bar --}}
            <div class="progress mb-4" style="height: 25px;">
                <div class="progress-bar {{ $progressPercentage >= 100 ? 'bg-success' : 'bg-primary' }} progress-bar-striped progress-bar-animated" 
                     role="progressbar" 
                     style="width: {{ $progressPercentage }}%;" 
                     aria-valuenow="{{ $progressPercentage }}" 
                     aria-valuemin="0" 
                     aria-valuemax="100">
                    {{ $progressPercentage }}%
                </div>
            </div>
            
            {{-- Step Indicators --}}
            <div class="application-steps d-none d-md-block">
                <div class="step-indicators d-flex justify-content-between position-relative">
                    {{-- Progress Line --}}
                    <div class="progress-line position-absolute" style="top: 20px; left: 0; right: 0; height: 2px; background-color: #e9ecef; z-index: 1;">
                        <div class="progress-line-filled" style="width: {{ $progressPercentage }}%; height: 100%; background-color: #007bff;"></div>
                    </div>
                    
                    @foreach($sections as $key => $section)
                        @php
                            $stepIndex = array_search($key, array_keys($sections));
                            $isActive = $key === ($currentSection ?? 'personal');
                            $isPast = $stepIndex < $currentSectionIndex;
                            $isCompleted = $section['completed'];
                        @endphp
                        
                        <div class="step-item text-center position-relative" style="z-index: 2; flex: 1;">
                            <a href="{{ route('admissions.application.section', ['uuid' => $application->application_uuid, 'section' => $key]) }}" 
                               class="text-decoration-none">
                                <div class="step-icon rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center
                                    {{ $isActive ? 'bg-primary text-white border-primary' : '' }}
                                    {{ $isCompleted && !$isActive ? 'bg-success text-white' : '' }}
                                    {{ !$isCompleted && !$isActive ? 'bg-white border' : '' }}"
                                    style="width: 40px; height: 40px; border-width: 2px; border-style: solid;
                                           {{ !$isCompleted && !$isActive ? 'border-color: #dee2e6;' : '' }}">
                                    @if($isCompleted && !$isActive)
                                        <i class="fas fa-check"></i>
                                    @else
                                        <i class="{{ $section['icon'] }} {{ !$isActive && !$isCompleted ? 'text-muted' : '' }}"></i>
                                    @endif
                                </div>
                                <div class="step-title">
                                    <small class="{{ $isActive ? 'text-primary fw-bold' : ($isCompleted ? 'text-success' : 'text-muted') }}">
                                        {{ $section['title'] }}
                                    </small>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
            
            {{-- Mobile Step List --}}
            <div class="application-steps-mobile d-md-none mt-3">
                <div class="list-group">
                    @foreach($sections as $key => $section)
                        @php
                            $isActive = $key === ($currentSection ?? 'personal');
                            $isCompleted = $section['completed'];
                        @endphp
                        
                        <a href="{{ route('admissions.application.section', ['uuid' => $application->application_uuid, 'section' => $key]) }}" 
                           class="list-group-item list-group-item-action d-flex align-items-center
                                  {{ $isActive ? 'active' : '' }}">
                            <div class="me-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center
                                    {{ $isCompleted ? 'bg-success text-white' : 'bg-light text-muted' }}"
                                    style="width: 30px; height: 30px;">
                                    @if($isCompleted)
                                        <i class="fas fa-check"></i>
                                    @else
                                        <i class="{{ $section['icon'] }}"></i>
                                    @endif
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-medium">{{ $section['title'] }}</div>
                                <small class="{{ $isCompleted ? 'text-success' : 'text-muted' }}">
                                    {{ $isCompleted ? 'Completed' : 'Incomplete' }}
                                </small>
                            </div>
                            @if($isActive)
                                <i class="fas fa-chevron-right"></i>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
            
            {{-- Action Buttons --}}
            @if($showActions ?? true)
                <div class="mt-4 d-flex justify-content-between">
                    <button type="button" 
                            class="btn btn-outline-secondary"
                            onclick="saveProgress()">
                        <i class="fas fa-save me-2"></i>Save Progress
                    </button>
                    
                    @if($progressPercentage >= 100)
                        <button type="button" 
                                class="btn btn-success"
                                onclick="window.location.href='{{ route('admissions.application.review', $application->application_uuid) }}'">
                            <i class="fas fa-eye me-2"></i>Review Application
                        </button>
                    @else
                        <button type="button" 
                                class="btn btn-primary"
                                onclick="continueApplication()">
                            <i class="fas fa-arrow-right me-2"></i>Continue Application
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
    .application-progress-container {
        margin-bottom: 2rem;
    }
    
    .step-indicators {
        padding: 0 20px;
    }
    
    .step-item {
        transition: all 0.3s ease;
    }
    
    .step-item:hover .step-icon {
        transform: scale(1.1);
        box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.1);
    }
    
    .step-icon {
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .progress-line {
        transition: width 0.5s ease;
    }
    
    .application-steps-mobile .list-group-item.active {
        background-color: #f8f9fa;
        color: #007bff;
        border-color: #007bff;
    }
    
    @media (max-width: 768px) {
        .application-progress-container .card-body {
            padding: 1rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    function saveProgress() {
        // AJAX call to save current progress
        $.ajax({
            url: '{{ route("admissions.application.save", $application->application_uuid) }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                toastr.success('Progress saved successfully!');
            },
            error: function() {
                toastr.error('Failed to save progress. Please try again.');
            }
        });
    }
    
    function continueApplication() {
        // Find next incomplete section
        const incompleteSections = @json(collect($sections)->filter(function($s) { return !$s['completed']; })->keys());
        if (incompleteSections.length > 0) {
            window.location.href = '{{ route("admissions.application.section", ["uuid" => $application->application_uuid, "section" => ""]) }}' + incompleteSections[0];
        }
    }
</script>
@endpush