{{-- Form Navigation Component --}}
{{-- Path: resources/views/admissions/partials/form-navigation.blade.php --}}

@php
    // Define the form sections and their order
    $sections = [
        'personal' => ['title' => 'Personal Information', 'icon' => 'fas fa-user'],
        'contact' => ['title' => 'Contact Information', 'icon' => 'fas fa-envelope'],
        'educational' => ['title' => 'Educational Background', 'icon' => 'fas fa-graduation-cap'],
        'test_scores' => ['title' => 'Test Scores', 'icon' => 'fas fa-clipboard-list'],
        'essays' => ['title' => 'Essays & Statements', 'icon' => 'fas fa-pen-fancy'],
        'activities' => ['title' => 'Activities & Experience', 'icon' => 'fas fa-trophy'],
        'references' => ['title' => 'References', 'icon' => 'fas fa-users'],
        'documents' => ['title' => 'Supporting Documents', 'icon' => 'fas fa-file-upload'],
        'review' => ['title' => 'Review & Submit', 'icon' => 'fas fa-check-circle'],
    ];
    
    // Get current section index
    $sectionKeys = array_keys($sections);
    $currentIndex = array_search($currentSection, $sectionKeys);
    $totalSections = count($sections);
    
    // Determine previous and next sections
    $previousSection = $currentIndex > 0 ? $sectionKeys[$currentIndex - 1] : null;
    $nextSection = $currentIndex < ($totalSections - 1) ? $sectionKeys[$currentIndex + 1] : null;
    
    // Check if we're on first or last section
    $isFirstSection = $currentIndex === 0;
    $isLastSection = $currentIndex === ($totalSections - 1);
    
    // Navigation style (can be 'standard', 'wizard', 'minimal', 'floating')
    $navStyle = $style ?? 'standard';
    
    // Show progress indicator
    $showProgress = $showProgress ?? true;
    
    // Allow navigation to any section
    $allowJumping = $allowJumping ?? true;
    
    // Show save draft button
    $showSaveDraft = $showSaveDraft ?? true;
    
    // Progress percentage
    $progressPercentage = round((($currentIndex + 1) / $totalSections) * 100);
@endphp

@if($navStyle === 'wizard')
    {{-- Wizard Style Navigation --}}
    <div class="form-navigation-wizard">
        {{-- Progress Steps --}}
        <div class="wizard-steps mb-4">
            <div class="steps-container">
                @foreach($sections as $key => $section)
                    @php
                        $stepIndex = array_search($key, $sectionKeys);
                        $isActive = $key === $currentSection;
                        $isPast = $stepIndex < $currentIndex;
                        $isFuture = $stepIndex > $currentIndex;
                    @endphp
                    
                    <div class="step-item {{ $isActive ? 'active' : '' }} {{ $isPast ? 'completed' : '' }} {{ $isFuture ? 'disabled' : '' }}">
                        <div class="step-number">
                            @if($isPast)
                                <i class="fas fa-check"></i>
                            @else
                                {{ $stepIndex + 1 }}
                            @endif
                        </div>
                        <div class="step-title d-none d-md-block">{{ $section['title'] }}</div>
                        @if($stepIndex < $totalSections - 1)
                            <div class="step-line"></div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        
        {{-- Navigation Buttons --}}
        <div class="wizard-navigation d-flex justify-content-between align-items-center">
            <div>
                @if(!$isFirstSection)
                    <button type="button" 
                            class="btn btn-outline-secondary"
                            onclick="navigateToSection('{{ $previousSection }}')">
                        <i class="fas fa-chevron-left me-2"></i>Previous
                    </button>
                @else
                    <button type="button" 
                            class="btn btn-outline-secondary"
                            onclick="window.location.href='{{ route('admissions.portal.index') }}'">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                @endif
            </div>
            
            <div class="text-center">
                @if($showSaveDraft)
                    <button type="button" 
                            class="btn btn-outline-primary"
                            onclick="saveFormData(true)">
                        <i class="fas fa-save me-2"></i>Save Draft
                    </button>
                @endif
            </div>
            
            <div>
                @if(!$isLastSection)
                    <button type="button" 
                            class="btn btn-primary"
                            onclick="validateAndNavigate('{{ $nextSection }}')">
                        Next<i class="fas fa-chevron-right ms-2"></i>
                    </button>
                @else
                    <button type="button" 
                            class="btn btn-success"
                            onclick="submitApplication()">
                        <i class="fas fa-paper-plane me-2"></i>Submit Application
                    </button>
                @endif
            </div>
        </div>
    </div>

@elseif($navStyle === 'minimal')
    {{-- Minimal Style Navigation --}}
    <div class="form-navigation-minimal">
        <div class="row align-items-center">
            <div class="col-4">
                @if(!$isFirstSection)
                    <button type="button" 
                            class="btn btn-link text-decoration-none"
                            onclick="navigateToSection('{{ $previousSection }}')">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </button>
                @endif
            </div>
            
            <div class="col-4 text-center">
                <small class="text-muted">
                    Step {{ $currentIndex + 1 }} of {{ $totalSections }}
                </small>
            </div>
            
            <div class="col-4 text-end">
                @if(!$isLastSection)
                    <button type="button" 
                            class="btn btn-link text-decoration-none"
                            onclick="validateAndNavigate('{{ $nextSection }}')">
                        Continue <i class="fas fa-arrow-right ms-1"></i>
                    </button>
                @else
                    <button type="button" 
                            class="btn btn-success btn-sm"
                            onclick="submitApplication()">
                        Submit
                    </button>
                @endif
            </div>
        </div>
    </div>

@elseif($navStyle === 'floating')
    {{-- Floating Action Buttons --}}
    <div class="form-navigation-floating">
        <div class="floating-nav-container">
            {{-- Previous Button --}}
            @if(!$isFirstSection)
                <button type="button" 
                        class="btn btn-floating btn-secondary"
                        onclick="navigateToSection('{{ $previousSection }}')"
                        data-bs-toggle="tooltip"
                        title="Previous Section">
                    <i class="fas fa-chevron-left"></i>
                </button>
            @endif
            
            {{-- Quick Jump Menu --}}
            @if($allowJumping)
                <div class="dropdown">
                    <button class="btn btn-floating btn-primary dropdown-toggle" 
                            type="button" 
                            id="quickJumpMenu" 
                            data-bs-toggle="dropdown" 
                            aria-expanded="false"
                            title="Jump to Section">
                        <i class="fas fa-th"></i>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="quickJumpMenu">
                        @foreach($sections as $key => $section)
                            <li>
                                <a class="dropdown-item {{ $key === $currentSection ? 'active' : '' }}" 
                                   href="#"
                                   onclick="navigateToSection('{{ $key }}'); return false;">
                                    <i class="{{ $section['icon'] }} me-2"></i>
                                    {{ $section['title'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            {{-- Save Draft Button --}}
            @if($showSaveDraft)
                <button type="button" 
                        class="btn btn-floating btn-info"
                        onclick="saveFormData(true)"
                        data-bs-toggle="tooltip"
                        title="Save Draft">
                    <i class="fas fa-save"></i>
                </button>
            @endif
            
            {{-- Next/Submit Button --}}
            @if(!$isLastSection)
                <button type="button" 
                        class="btn btn-floating btn-primary"
                        onclick="validateAndNavigate('{{ $nextSection }}')"
                        data-bs-toggle="tooltip"
                        title="Next Section">
                    <i class="fas fa-chevron-right"></i>
                </button>
            @else
                <button type="button" 
                        class="btn btn-floating btn-success"
                        onclick="submitApplication()"
                        data-bs-toggle="tooltip"
                        title="Submit Application">
                    <i class="fas fa-check"></i>
                </button>
            @endif
        </div>
    </div>

@else
    {{-- Standard Navigation (Default) --}}
    <div class="form-navigation-standard">
        {{-- Progress Bar --}}
        @if($showProgress)
            <div class="navigation-progress mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-muted">
                        Section {{ $currentIndex + 1 }} of {{ $totalSections }}: {{ $sections[$currentSection]['title'] }}
                    </small>
                    <small class="text-muted">{{ $progressPercentage }}% Complete</small>
                </div>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" 
                         style="width: {{ $progressPercentage }}%"
                         aria-valuenow="{{ $progressPercentage }}" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                    </div>
                </div>
            </div>
        @endif
        
        {{-- Navigation Buttons --}}
        <div class="navigation-buttons">
            <div class="row">
                <div class="col-md-4 col-6 text-start">
                    @if(!$isFirstSection)
                        <button type="button" 
                                class="btn btn-outline-secondary w-100"
                                onclick="navigateToSection('{{ $previousSection }}')">
                            <i class="fas fa-arrow-left me-2"></i>
                            <span class="d-none d-sm-inline">Previous</span>
                            <span class="d-sm-none">Back</span>
                        </button>
                    @else
                        <button type="button" 
                                class="btn btn-outline-danger w-100"
                                onclick="confirmExit()">
                            <i class="fas fa-times me-2"></i>
                            <span class="d-none d-sm-inline">Cancel</span>
                            <span class="d-sm-none">Exit</span>
                        </button>
                    @endif
                </div>
                
                <div class="col-md-4 d-none d-md-block text-center">
                    @if($showSaveDraft)
                        <button type="button" 
                                class="btn btn-outline-primary w-100"
                                onclick="saveFormData(true)">
                            <i class="fas fa-save me-2"></i>Save & Continue Later
                        </button>
                    @endif
                </div>
                
                <div class="col-md-4 col-6 text-end">
                    @if(!$isLastSection)
                        <button type="button" 
                                class="btn btn-primary w-100"
                                onclick="validateAndNavigate('{{ $nextSection }}')">
                            <span class="d-none d-sm-inline">Save & Continue</span>
                            <span class="d-sm-none">Next</span>
                            <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    @else
                        <button type="button" 
                                class="btn btn-success w-100"
                                onclick="reviewAndSubmit()">
                            <i class="fas fa-check-circle me-2"></i>
                            <span class="d-none d-sm-inline">Review & Submit</span>
                            <span class="d-sm-none">Submit</span>
                        </button>
                    @endif
                </div>
            </div>
            
            {{-- Mobile Save Button --}}
            @if($showSaveDraft)
                <div class="row mt-2 d-md-none">
                    <div class="col-12">
                        <button type="button" 
                                class="btn btn-outline-primary w-100"
                                onclick="saveFormData(true)">
                            <i class="fas fa-save me-2"></i>Save Draft
                        </button>
                    </div>
                </div>
            @endif
        </div>
        
        {{-- Quick Navigation (Desktop Only) --}}
        @if($allowJumping)
            <div class="quick-navigation mt-3 d-none d-lg-block">
                <div class="text-center">
                    <small class="text-muted">Quick Navigation:</small>
                    <div class="btn-group btn-group-sm mt-1" role="group">
                        @foreach($sections as $key => $section)
                            @php
                                $stepIndex = array_search($key, $sectionKeys);
                                $isCompleted = isset($completedSections) && in_array($key, $completedSections);
                            @endphp
                            <button type="button" 
                                    class="btn {{ $key === $currentSection ? 'btn-primary' : ($isCompleted ? 'btn-success' : 'btn-outline-secondary') }}"
                                    onclick="navigateToSection('{{ $key }}')"
                                    data-bs-toggle="tooltip"
                                    title="{{ $section['title'] }}">
                                @if($isCompleted)
                                    <i class="fas fa-check"></i>
                                @else
                                    {{ $stepIndex + 1 }}
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
@endif

@push('styles')
<style>
    /* Standard Navigation */
    .form-navigation-standard {
        padding: 1.5rem 0;
        border-top: 1px solid #dee2e6;
        margin-top: 2rem;
    }
    
    .navigation-buttons .btn {
        min-height: 45px;
    }
    
    /* Wizard Navigation */
    .form-navigation-wizard {
        padding: 2rem 0;
    }
    
    .wizard-steps {
        margin-bottom: 2rem;
    }
    
    .steps-container {
        display: flex;
        justify-content: space-between;
        position: relative;
    }
    
    .step-item {
        flex: 1;
        text-align: center;
        position: relative;
    }
    
    .step-number {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #e9ecef;
        color: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        font-weight: bold;
        position: relative;
        z-index: 2;
        transition: all 0.3s ease;
    }
    
    .step-item.active .step-number {
        background-color: #007bff;
        color: white;
        box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.1);
    }
    
    .step-item.completed .step-number {
        background-color: #28a745;
        color: white;
    }
    
    .step-title {
        margin-top: 0.5rem;
        font-size: 0.875rem;
        color: #6c757d;
    }
    
    .step-item.active .step-title {
        color: #007bff;
        font-weight: 500;
    }
    
    .step-item.completed .step-title {
        color: #28a745;
    }
    
    .step-line {
        position: absolute;
        top: 20px;
        left: 50%;
        width: 100%;
        height: 2px;
        background-color: #e9ecef;
        z-index: 1;
    }
    
    .step-item.completed .step-line {
        background-color: #28a745;
    }
    
    /* Minimal Navigation */
    .form-navigation-minimal {
        padding: 1rem 0;
        border-top: 1px solid #dee2e6;
    }
    
    /* Floating Navigation */
    .form-navigation-floating {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1000;
    }
    
    .floating-nav-container {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .btn-floating {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }
    
    .btn-floating:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }
    
    /* Quick Navigation */
    .quick-navigation {
        padding-top: 1rem;
        border-top: 1px dashed #dee2e6;
    }
    
    /* Mobile Responsive */
    @media (max-width: 768px) {
        .wizard-steps {
            overflow-x: auto;
            padding-bottom: 1rem;
        }
        
        .steps-container {
            min-width: 600px;
        }
        
        .form-navigation-floating {
            bottom: 10px;
            right: 10px;
        }
        
        .btn-floating {
            width: 48px;
            height: 48px;
        }
    }
    
    @media (max-width: 576px) {
        .navigation-buttons .btn {
            font-size: 0.875rem;
            padding: 0.5rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Navigation functions
    function navigateToSection(section) {
        // Save current section data before navigating
        saveFormData(false, function() {
            window.location.href = '{{ route("admissions.application.section", ["uuid" => $application->application_uuid ?? "", "section" => ""]) }}' + section;
        });
    }
    
    function validateAndNavigate(nextSection) {
        // Validate current section
        if (validateCurrentSection()) {
            // Save and navigate
            saveFormData(false, function() {
                window.location.href = '{{ route("admissions.application.section", ["uuid" => $application->application_uuid ?? "", "section" => ""]) }}' + nextSection;
            });
        } else {
            toastr.warning('Please complete all required fields before proceeding.');
        }
    }
    
    function saveFormData(showNotification = true, callback = null) {
        const formData = new FormData(document.getElementById('applicationForm'));
        
        $.ajax({
            url: '{{ route("admissions.application.save", $application->application_uuid ?? "") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                if (showNotification) {
                    toastr.success('Your progress has been saved.');
                }
                if (callback) callback();
            },
            error: function(xhr) {
                toastr.error('Failed to save your progress. Please try again.');
                console.error('Save error:', xhr);
            }
        });
    }
    
    function validateCurrentSection() {
        // Basic validation - check required fields
        const requiredFields = document.querySelectorAll('#applicationForm [required]:not([disabled])');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value || field.value.trim() === '') {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        return isValid;
    }
    
    function confirmExit() {
        if (confirm('Are you sure you want to exit? Your progress will be saved.')) {
            saveFormData(false, function() {
                window.location.href = '{{ route("admissions.portal.index") }}';
            });
        }
    }
    
    function reviewAndSubmit() {
        // Navigate to review page
        saveFormData(false, function() {
            window.location.href = '{{ route("admissions.application.review", $application->application_uuid ?? "") }}';
        });
    }
    
    function submitApplication() {
        if (confirm('Are you sure you want to submit your application? Once submitted, you cannot make changes.')) {
            const formData = new FormData(document.getElementById('applicationForm'));
            
            $.ajax({
                url: '{{ route("admissions.application.submit", $application->application_uuid ?? "") }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    toastr.success('Your application has been submitted successfully!');
                    setTimeout(function() {
                        window.location.href = '{{ route("admissions.application.success", $application->application_uuid ?? "") }}';
                    }, 1500);
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON?.errors;
                    if (errors) {
                        let errorMessage = 'Please fix the following errors:\n';
                        Object.values(errors).forEach(error => {
                            errorMessage += '- ' + error[0] + '\n';
                        });
                        alert(errorMessage);
                    } else {
                        toastr.error('Failed to submit application. Please try again.');
                    }
                }
            });
        }
    }
    
    // Auto-save functionality
    let autoSaveTimer;
    function initAutoSave() {
        // Clear existing timer
        if (autoSaveTimer) clearInterval(autoSaveTimer);
        
        // Auto-save every 2 minutes
        autoSaveTimer = setInterval(function() {
            saveFormData(false);
        }, 120000);
    }
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Initialize auto-save
        initAutoSave();
        
        // Add event listener for form changes
        const form = document.getElementById('applicationForm');
        if (form) {
            form.addEventListener('change', function() {
                // Mark form as dirty
                form.dataset.dirty = 'true';
            });
        }
        
        // Warn user before leaving if form is dirty
        window.addEventListener('beforeunload', function(e) {
            const form = document.getElementById('applicationForm');
            if (form && form.dataset.dirty === 'true') {
                e.preventDefault();
                e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
            }
        });
    });
</script>
@endpush