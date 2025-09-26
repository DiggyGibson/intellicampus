{{-- resources/views/admissions/portal/forms/consolidated-application.blade.php --}}
@extends('layouts.portal')

@section('title', 'Complete Your ' . ucfirst($application->application_type) . ' Application')

@section('styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    /* Application Header */
    .application-header {
        background: linear-gradient(135deg, 
            {{ $application->application_type == 'graduate' ? '#4a5568' : '#667eea' }} 0%, 
            {{ $application->application_type == 'international' ? '#f56565' : '#764ba2' }} 100%);
        color: white;
        padding: 30px;
        border-radius: 10px;
        margin-bottom: 30px;
    }
    
    /* Navigation Sidebar */
    .nav-pills .nav-link {
        color: #6c757d;
        padding: 12px 16px;
        margin-bottom: 5px;
        border-radius: 8px;
        transition: all 0.3s;
    }
    
    .nav-pills .nav-link:hover {
        background: #f8f9fa;
        color: #495057;
    }
    
    .nav-pills .nav-link.active {
        background: #007bff;
        color: white;
    }
    
    .nav-pills .nav-link.completed {
        border-left: 4px solid #28a745;
        padding-left: 12px;
    }
    
    .section-status {
        min-width: 20px;
        display: inline-block;
        text-align: center;
    }
    
    /* Form Sections */
    .form-section {
        display: none !important;
    }
    
    .form-section.active {
        display: block !important;
        animation: fadeIn 0.3s;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Progress Indicators */
    .progress-card {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .progress-stat {
        text-align: center;
        padding: 10px;
    }
    
    .progress-stat .number {
        font-size: 2rem;
        font-weight: bold;
        color: #007bff;
    }
    
    .progress-stat .label {
        font-size: 0.875rem;
        color: #6c757d;
        margin-top: 5px;
    }
    
    /* Toast Notifications */
    .toast-container {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 9999;
    }
    
    .custom-toast {
        min-width: 300px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        padding: 16px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        animation: slideIn 0.3s;
    }
    
    .custom-toast.success {
        border-left: 4px solid #28a745;
    }
    
    .custom-toast.error {
        border-left: 4px solid #dc3545;
    }
    
    .custom-toast.info {
        border-left: 4px solid #17a2b8;
    }
    
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    /* Quick Actions */
    .quick-actions {
        position: fixed;
        bottom: 30px;
        left: 50%;
        transform: translateX(-50%);
        background: white;
        border-radius: 50px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        padding: 10px 20px;
        display: flex;
        gap: 10px;
        z-index: 100;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .nav-sidebar {
            position: fixed;
            top: 0;
            left: -280px;
            width: 280px;
            height: 100vh;
            background: white;
            z-index: 1050;
            transition: left 0.3s;
            overflow-y: auto;
        }
        
        .nav-sidebar.show {
            left: 0;
        }
        
        .mobile-nav-toggle {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1051;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 15px;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    {{-- Application Header --}}
    <div class="application-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-2">
                    {{ ucfirst($application->application_type) }} Application
                    @if($application->program)
                        <span class="badge bg-white text-primary ms-2">{{ $application->program->name }}</span>
                    @endif
                </h2>
                <p class="mb-0 opacity-90">
                    Welcome back, {{ $application->first_name }} {{ $application->last_name }}
                </p>
                <p class="mt-2 mb-0">
                    <i class="fas fa-id-badge me-2"></i>{{ $application->application_number }} | 
                    <i class="fas fa-calendar me-2"></i>{{ $application->term->name ?? 'Term' }}
                    @if($application->application_type == 'international')
                        | <i class="fas fa-globe me-2"></i>{{ $application->nationality }}
                    @endif
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="mt-3 mt-md-0">
                    <div class="progress mb-2" style="height: 30px; border-radius: 15px;">
                        <div class="progress-bar bg-success" id="main-progress" role="progressbar" 
                             style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                            <span class="fw-bold">0%</span>
                        </div>
                    </div>
                    <small class="text-white-50">Overall Progress</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Progress Stats --}}
    <div class="progress-card">
        <div class="row">
            <div class="col-md-3 col-6">
                <div class="progress-stat">
                    <div class="number" id="completed-count">0</div>
                    <div class="label">Sections Completed</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="progress-stat">
                    <div class="number" id="total-count">{{ count($requirements['sections']) }}</div>
                    <div class="label">Total Sections</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="progress-stat">
                    <div class="number" id="time-spent">0m</div>
                    <div class="label">Time Spent</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="progress-stat">
                    <div class="number text-warning" id="days-remaining">
                        {{ $application->expires_at ? now()->diffInDays($application->expires_at) : '90' }}
                    </div>
                    <div class="label">Days Remaining</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Mobile Nav Toggle --}}
    <button class="mobile-nav-toggle d-md-none" onclick="toggleMobileNav()">
        <i class="fas fa-bars"></i> Menu
    </button>

    <div class="row">
        {{-- Left Sidebar Navigation --}}
        <div class="col-md-3">
            <div class="card shadow-sm position-sticky nav-sidebar" style="top: 20px;">
                <div class="card-body">
                    <h6 class="card-title mb-3 d-flex justify-content-between align-items-center">
                        <span>Navigation</span>
                        <button class="btn btn-sm btn-outline-primary" onclick="expandAllSections()">
                            <i class="fas fa-expand"></i>
                        </button>
                    </h6>
                    
                    <div class="nav flex-column nav-pills">
                        @foreach($requirements['sections'] as $index => $section)
                            <a class="nav-link section-nav-item {{ $loop->first ? 'active' : '' }}" 
                               href="javascript:void(0)"
                               data-section="{{ $section }}"
                               onclick="navigateToSection('{{ $section }}')">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>
                                        <span class="me-2">{{ $index + 1 }}.</span>
                                        @switch($section)
                                            @case('academic')
                                                <i class="fas fa-graduation-cap me-2"></i> Academic
                                                @break
                                            @case('test-scores')
                                                <i class="fas fa-clipboard-check me-2"></i> Test Scores
                                                @break
                                            @case('essays')
                                                <i class="fas fa-pen-fancy me-2"></i> Essays
                                                @break
                                            @case('documents')
                                                <i class="fas fa-folder-open me-2"></i> Documents
                                                @break
                                            @case('recommendations')
                                                <i class="fas fa-user-friends me-2"></i> References
                                                @break
                                            @case('activities')
                                                <i class="fas fa-trophy me-2"></i> Activities
                                                @break
                                            @case('college-courses')
                                                <i class="fas fa-book me-2"></i> Courses
                                                @break
                                            @case('research')
                                                <i class="fas fa-flask me-2"></i> Research
                                                @break
                                            @case('english-proficiency')
                                                <i class="fas fa-language me-2"></i> English
                                                @break
                                            @case('financial')
                                                <i class="fas fa-dollar-sign me-2"></i> Financial
                                                @break
                                            @default
                                                <i class="fas fa-file me-2"></i> {{ ucwords(str_replace('-', ' ', $section)) }}
                                        @endswitch
                                    </span>
                                    <span class="section-status" id="status-{{ $section }}">
                                        <i class="fas fa-circle text-muted" style="font-size: 8px;"></i>
                                    </span>
                                </div>
                            </a>
                        @endforeach
                        
                        <hr class="my-2">
                        
                        <a class="nav-link section-nav-item" 
                           href="javascript:void(0)"
                           data-section="review"
                           onclick="navigateToSection('review')">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>
                                    <i class="fas fa-check-circle me-2"></i> Review & Submit
                                </span>
                                <span class="section-status" id="status-review">
                                    <i class="fas fa-lock text-muted"></i>
                                </span>
                            </div>
                        </a>
                    </div>
                    
                    {{-- Quick Actions --}}
                    <div class="mt-4 pt-3 border-top">
                        <div class="d-grid gap-2">
                            <button class="btn btn-sm btn-outline-primary" onclick="saveAllSections()">
                                <i class="fas fa-save me-1"></i> Save All
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" onclick="previewApplication()">
                                <i class="fas fa-eye me-1"></i> Preview
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content Area --}}
        <div class="col-md-9">
            <div class="card shadow">
                <div class="card-body">
                    <form id="application-form">
                        @csrf
                        <input type="hidden" id="application-uuid" value="{{ $application->application_uuid }}">
                        <input type="hidden" id="application-type" value="{{ $application->application_type }}">
                        
                        {{-- Dynamic Form Sections --}}
                        @foreach($requirements['sections'] as $section)
                            <div class="form-section {{ $loop->first ? 'active' : '' }}" id="{{ $section }}">
                                <div class="section-header mb-4">
                                    <h3 class="section-title">
                                        {{ ucwords(str_replace('-', ' ', $section)) }}
                                    </h3>
                                    <div class="section-progress">
                                        <div class="progress" style="height: 4px;">
                                            <div class="progress-bar section-progress-bar" 
                                                 id="progress-{{ $section }}" 
                                                 style="width: 0%"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                @switch($section)
                                    @case('academic')
                                        @include('admissions.portal.forms.academic')
                                        @break
                                    @case('test-scores')
                                        @include('admissions.portal.forms.test-scores')
                                        @break
                                    @case('essays')
                                        @include('admissions.portal.forms.essays')
                                        @break
                                    @case('activities')
                                        @include('admissions.portal.forms.activities')
                                        @break
                                    @case('documents')
                                        @include('admissions.portal.forms.documents')
                                        @break
                                    @case('recommendations')
                                        @include('admissions.portal.forms.recommendations')
                                        @break
                                    @default
                                        <div class="alert alert-warning">
                                            <i class="fas fa-info-circle me-2"></i>
                                            This section is specific to your program. Please contact admissions if you need assistance.
                                        </div>
                                @endswitch
                            </div>
                        @endforeach
                        
                        {{-- Review & Submit Section --}}
                        <div class="form-section" id="review">
                            <div class="section-header mb-4">
                                <h3 class="section-title">Review Your Application</h3>
                            </div>
                            
                            <div class="alert alert-warning mb-4">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Please review all sections carefully. After submission, you cannot make changes.
                            </div>
                            
                            {{-- Section Review Cards --}}
                            <div class="row" id="review-cards">
                                @foreach($requirements['sections'] as $section)
                                    <div class="col-md-6 mb-3">
                                        <div class="card border-0 shadow-sm review-card" id="review-card-{{ $section }}">
                                            <div class="card-body">
                                                <h6 class="card-title">
                                                    <span class="status-icon" id="review-icon-{{ $section }}">
                                                        <i class="fas fa-spinner fa-spin text-muted"></i>
                                                    </span>
                                                    {{ ucwords(str_replace('-', ' ', $section)) }}
                                                </h6>
                                                <p class="card-text small text-muted" id="review-summary-{{ $section }}">
                                                    Checking...
                                                </p>
                                                <a href="javascript:void(0)" 
                                                   onclick="navigateToSection('{{ $section }}')" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit me-1"></i> Edit
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            {{-- Agreements --}}
                            <div class="card mt-4">
                                <div class="card-body">
                                    <h5 class="card-title">Certification & Agreement</h5>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="certify" required>
                                        <label class="form-check-label" for="certify">
                                            I certify that all information provided in this application is accurate, complete, 
                                            and honestly presented. I understand that any false information may result in the 
                                            denial or revocation of admission.
                                        </label>
                                    </div>
                                    
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="agree" required>
                                        <label class="form-check-label" for="agree">
                                            I agree to the terms and conditions of admission, including all academic policies 
                                            and requirements of the program.
                                        </label>
                                    </div>
                                    
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="ferpa">
                                        <label class="form-check-label" for="ferpa">
                                            I understand and agree to the FERPA (Family Educational Rights and Privacy Act) 
                                            policies regarding the privacy of my educational records.
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    
                    {{-- Navigation Buttons --}}
                    <div class="mt-4 d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" id="prev-btn" 
                                onclick="previousSection()" style="display:none;">
                            <i class="fas fa-arrow-left me-2"></i> Previous
                        </button>
                        
                        <div class="ms-auto d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary" onclick="saveCurrentSection()">
                                <i class="fas fa-save me-2"></i> Save Progress
                            </button>
                            
                            <button type="button" class="btn btn-primary" id="next-btn" onclick="nextSection()">
                                Next <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                            
                            <button type="button" class="btn btn-success d-none" id="submit-btn" 
                                    onclick="submitApplication()">
                                <i class="fas fa-paper-plane me-2"></i> Submit Application
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Quick Actions Bar (Desktop) --}}
<div class="quick-actions d-none d-md-flex">
    <button class="btn btn-sm btn-light" onclick="saveCurrentSection()">
        <i class="fas fa-save"></i> Save
    </button>
    <button class="btn btn-sm btn-light" onclick="showHelp()">
        <i class="fas fa-question-circle"></i> Help
    </button>
    <button class="btn btn-sm btn-light" onclick="showProgress()">
        <i class="fas fa-chart-pie"></i> Progress
    </button>
</div>

{{-- Toast Container --}}
<div class="toast-container" id="toast-container"></div>
@endsection

@section('scripts')
<script>
// Configuration
const requirements = @json($requirements);
const sections = requirements.sections || [];
let currentSectionIndex = 0;
let completedSections = [];
let sectionProgress = {};
let autoSaveInterval = null;
let timeSpent = 0;
let timeInterval = null;

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    initializeApplication();
    startTimeTracking();
    setupKeyboardShortcuts();
    setupAutoSave();
    loadSavedProgress();
    checkInitialProgress();
});

function initializeApplication() {
    updateNavigation();
    attachInputListeners();
}

// Navigation Functions
function navigateToSection(sectionId) {
    // Save current section before switching
    const currentSection = document.querySelector('.form-section.active');
    if (currentSection && currentSection.id !== 'review' && currentSection.id !== sectionId) {
        saveSection(currentSection.id, false);
    }
    
    showSection(sectionId);
    
    // Close mobile nav if open
    if (window.innerWidth < 768) {
        document.querySelector('.nav-sidebar').classList.remove('show');
    }
}

function showSection(sectionId) {
    // Hide ALL sections first
    const allSections = document.querySelectorAll('.form-section');
    allSections.forEach(section => {
        section.classList.remove('active');
        section.style.display = 'none'; // Force hide
    });
    
    // Show only the selected section
    const targetSection = document.getElementById(sectionId);
    if (targetSection) {
        targetSection.classList.add('active');
        targetSection.style.display = 'block'; // Force show
    }
    
    // Update navigation active states
    document.querySelectorAll('.section-nav-item').forEach(item => {
        item.classList.remove('active');
        if (item.dataset.section === sectionId) {
            item.classList.add('active');
        }
    });
    
    // Update current section index
    if (sectionId === 'review') {
        currentSectionIndex = sections.length;
        updateReviewSection();
    } else {
        currentSectionIndex = sections.indexOf(sectionId);
    }
    
    updateNavigation();
    
    // Scroll to top of the page
    window.scrollTo(0, 0);
}

function nextSection() {
    if (currentSectionIndex < sections.length - 1) {
        saveCurrentSection();
        showSection(sections[currentSectionIndex + 1]);
    } else if (currentSectionIndex === sections.length - 1) {
        saveCurrentSection();
        showSection('review');
    }
}

function previousSection() {
    if (currentSectionIndex > 0) {
        showSection(sections[currentSectionIndex - 1]);
    }
}

// Save Functions
function saveCurrentSection() {
    const currentSection = document.querySelector('.form-section.active');
    if (currentSection && currentSection.id !== 'review') {
        saveSection(currentSection.id, true);
    }
}

function saveSection(sectionId, showNotification = true) {
    const section = document.getElementById(sectionId);
    if (!section) return;
    
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    formData.append('section', sectionId);
    
    // Collect all inputs from this specific section only
    section.querySelectorAll('input, select, textarea').forEach(input => {
        if (input.name && !input.name.startsWith('_')) { // Skip CSRF tokens
            if (input.type === 'file') {
                if (input.files.length > 0) {
                    formData.append(input.name, input.files[0]);
                }
            } else if (input.type === 'checkbox') {
                formData.append(input.name, input.checked ? '1' : '0');
            } else if (input.type === 'radio') {
                if (input.checked) {
                    formData.append(input.name, input.value);
                }
            } else {
                formData.append(input.name, input.value || '');
            }
        }
    });
    
    const uuid = document.getElementById('application-uuid').value;
    
    // Save via AJAX with correct URL
    fetch(`/application/${uuid}/save-section`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            markSectionComplete(sectionId);
            if (showNotification) {
                showToast('Section saved successfully', 'success');
            }
            if (data.completion) {
                updateProgress(data.completion);
            }
        } else {
            throw new Error(data.message || 'Save failed');
        }
    })
    .catch(error => {
        console.error('Save error:', error);
        if (showNotification) {
            showToast('Error saving section: ' + error.message, 'error');
        }
    });
}

function saveAllSections() {
    let sectionsToSave = sections.filter(s => !completedSections.includes(s));
    let savedCount = 0;
    
    sectionsToSave.forEach((sectionId, index) => {
        setTimeout(() => {
            saveSection(sectionId, false);
            savedCount++;
            if (savedCount === sectionsToSave.length) {
                showToast('All sections saved successfully', 'success');
            }
        }, index * 500);
    });
}

// Progress Functions
function markSectionComplete(sectionId) {
    if (!completedSections.includes(sectionId)) {
        completedSections.push(sectionId);
    }
    updateSectionStatus(sectionId, 'complete');
    updateOverallProgress();
}

function updateSectionStatus(sectionId, status) {
    const statusIcon = document.getElementById(`status-${sectionId}`);
    if (statusIcon) {
        switch(status) {
            case 'complete':
                statusIcon.innerHTML = '<i class="fas fa-check-circle text-success"></i>';
                break;
            case 'in-progress':
                statusIcon.innerHTML = '<i class="fas fa-edit text-warning"></i>';
                break;
            case 'error':
                statusIcon.innerHTML = '<i class="fas fa-exclamation-circle text-danger"></i>';
                break;
            default:
                statusIcon.innerHTML = '<i class="fas fa-circle text-muted" style="font-size: 8px;"></i>';
        }
    }
}

function updateOverallProgress() {
    const percentage = sections.length > 0 ? Math.round((completedSections.length / sections.length) * 100) : 0;
    
    // Update main progress bar
    const progressBar = document.getElementById('main-progress');
    if (progressBar) {
        progressBar.style.width = percentage + '%';
        progressBar.querySelector('span').textContent = percentage + '%';
    }
    
    // Update stats
    document.getElementById('completed-count').textContent = completedSections.length;
    
    // Enable submit button if all complete
    if (percentage === 100) {
        document.getElementById('status-review').innerHTML = '<i class="fas fa-unlock text-success"></i>';
    }
}

function updateProgress(completion) {
    if (completion) {
        // Update individual section statuses if provided
        if (completion.sections) {
            Object.keys(completion.sections).forEach(section => {
                if (completion.sections[section]) {
                    markSectionComplete(section);
                }
            });
        }
        updateOverallProgress();
    }
}

// Check Initial Progress
function checkInitialProgress() {
    // Make AJAX call to get completion status
    const uuid = document.getElementById('application-uuid').value;
    
    fetch(`/application/${uuid}/review-summary`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (response.ok) {
            return response.json();
        }
        // Don't throw error for 404/500, just log it
        console.log('Could not load initial progress - endpoint may not exist');
        return null;
    })
    .then(data => {
        if (data && data.completion) {
            updateProgress(data.completion);
        }
    })
    .catch(error => {
        // Silently fail - not critical
        console.log('Could not load initial progress:', error);
    });
}

// Review Section
function updateReviewSection() {
    sections.forEach(section => {
        const card = document.getElementById(`review-card-${section}`);
        const icon = document.getElementById(`review-icon-${section}`);
        const summary = document.getElementById(`review-summary-${section}`);
        
        if (completedSections.includes(section)) {
            icon.innerHTML = '<i class="fas fa-check-circle text-success me-2"></i>';
            summary.textContent = 'Section completed';
            if (card) card.classList.add('border-success');
        } else {
            icon.innerHTML = '<i class="fas fa-exclamation-circle text-warning me-2"></i>';
            summary.textContent = 'Section incomplete';
            if (card) card.classList.add('border-warning');
        }
    });
}

// Submit Application
function submitApplication() {
    if (!document.getElementById('certify').checked || !document.getElementById('agree').checked) {
        showToast('Please certify and agree to the terms', 'error');
        return;
    }
    
    const submitBtn = document.getElementById('submit-btn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Submitting...';
    
    const uuid = document.getElementById('application-uuid').value;
    
    fetch(`/application/${uuid}/submit`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            certify: document.getElementById('certify').checked,
            agree: document.getElementById('agree').checked,
            ferpa: document.getElementById('ferpa').checked
        })
    })
    .then(response => {
        return response.json().then(data => {
            if (!response.ok && !data.already_submitted) {
                throw data;
            }
            return data;
        });
    })
    .then(data => {
        if (data.already_submitted) {
            // Application was already submitted
            showToast(`Your application was already submitted on ${data.submitted_at}. Redirecting to confirmation page...`, 'info');
            setTimeout(() => {
                window.location.href = data.redirect || `/application/${uuid}/confirmation`;
            }, 2000);
        } else if (data.success) {
            // Fresh submission
            showToast('Application submitted successfully! 🎉', 'success');
            
            // Show success modal
            showSuccessModal();
            
            // Redirect after a delay
            setTimeout(() => {
                window.location.href = data.redirect || `/application/${uuid}/confirmation`;
            }, 3000);
        } else {
            throw data;
        }
    })
    .catch(error => {
        console.error('Submit error:', error);
        
        // Handle incomplete sections error
        if (error.incomplete_sections && error.incomplete_sections.length > 0) {
            let errorMessage = 'Please complete the following sections:\n';
            errorMessage += error.incomplete_sections.map(s => '• ' + ucwords(s.replace('-', ' '))).join('\n');
            
            if (error.details) {
                showDetailedError('Incomplete Application', errorMessage, formatErrorDetails(error.details));
            } else {
                showToast(errorMessage, 'error');
            }
        } else {
            showToast(error.message || 'Submission failed. Please try again.', 'error');
        }
        
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i> Submit Application';
    });
}

// Add a success modal function
function showSuccessModal() {
    const modal = document.createElement('div');
    modal.innerHTML = `
        <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                        </div>
                        <h4 class="mb-3">Application Submitted Successfully!</h4>
                        <p class="mb-3">Congratulations! Your application has been submitted for review.</p>
                        <p class="text-muted">You will receive a confirmation email shortly.</p>
                        <div class="progress mt-3" style="height: 4px;">
                            <div class="progress-bar bg-success progress-bar-animated" style="width: 100%"></div>
                        </div>
                        <small class="text-muted">Redirecting to confirmation page...</small>
                    </div>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

// Format error details for display
function formatErrorDetails(details) {
    let html = '<div class="mt-3"><strong>Missing Information:</strong><ul class="mt-2">';
    for (let section in details) {
        html += `<li><strong>${ucwords(section.replace('-', ' '))}:</strong>`;
        html += '<ul>';
        details[section].forEach(item => {
            html += `<li>${item}</li>`;
        });
        html += '</ul></li>';
    }
    html += '</ul></div>';
    return html;
}

// Helper function to capitalize words
function ucwords(str) {
    return str.replace(/\b[a-z]/g, function(letter) {
        return letter.toUpperCase();
    });
}

// Function to show detailed error modal
function showDetailedError(title, message, details) {
    // Create modal if it doesn't exist
    let modal = document.getElementById('errorModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.innerHTML = `
            <div class="modal fade" id="errorModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="errorModalTitle"></h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-danger" id="errorModalMessage"></div>
                            <div id="errorModalDetails"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" onclick="reviewIncompleteSections()">Review Sections</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }
    
    document.getElementById('errorModalTitle').textContent = title;
    document.getElementById('errorModalMessage').textContent = message;
    document.getElementById('errorModalDetails').innerHTML = details || '';
    
    // Show modal
    const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
    errorModal.show();
}

// Function to review incomplete sections
function reviewIncompleteSections() {
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('errorModal'));
    if (modal) modal.hide();
    
    // Go to first incomplete section
    for (let section of sections) {
        if (!completedSections.includes(section)) {
            navigateToSection(section);
            showToast(`Please complete the ${ucwords(section.replace('-', ' '))} section`, 'warning');
            break;
        }
    }
}

// UI Functions
function updateNavigation() {
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const submitBtn = document.getElementById('submit-btn');
    
    if (prevBtn) {
        prevBtn.style.display = currentSectionIndex === 0 ? 'none' : 'inline-block';
    }
    
    const activeSection = document.querySelector('.form-section.active');
    if (activeSection && activeSection.id === 'review') {
        nextBtn.classList.add('d-none');
        submitBtn.classList.remove('d-none');
    } else {
        nextBtn.classList.remove('d-none');
        submitBtn.classList.add('d-none');
        
        if (currentSectionIndex === sections.length - 1) {
            nextBtn.innerHTML = 'Review Application <i class="fas fa-arrow-right ms-2"></i>';
        } else {
            nextBtn.innerHTML = 'Next <i class="fas fa-arrow-right ms-2"></i>';
        }
    }
}

function showToast(message, type = 'info') {
    const toastContainer = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `custom-toast ${type}`;
    
    const icon = type === 'success' ? 'check-circle' : 
                  type === 'error' ? 'exclamation-circle' : 'info-circle';
    
    toast.innerHTML = `
        <i class="fas fa-${icon} me-2"></i>
        <span>${message}</span>
    `;
    
    toastContainer.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideIn 0.3s reverse';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Helper Functions
function attachInputListeners() {
    document.querySelectorAll('.form-section input, .form-section textarea, .form-section select').forEach(input => {
        input.addEventListener('change', function() {
            const section = this.closest('.form-section');
            if (section && !completedSections.includes(section.id)) {
                updateSectionStatus(section.id, 'in-progress');
            }
        });
        
        // Track section progress
        input.addEventListener('input', function() {
            const section = this.closest('.form-section');
            if (section) {
                updateSectionProgress(section.id);
            }
        });
    });
}

function updateSectionProgress(sectionId) {
    const section = document.getElementById(sectionId);
    if (!section) return;
    
    const inputs = section.querySelectorAll('input[required], textarea[required], select[required]');
    const filled = Array.from(inputs).filter(input => {
        if (input.type === 'checkbox' || input.type === 'radio') {
            return input.checked;
        }
        return input.value.trim() !== '';
    });
    
    const percentage = inputs.length > 0 ? Math.round((filled.length / inputs.length) * 100) : 0;
    const progressBar = document.getElementById(`progress-${sectionId}`);
    if (progressBar) {
        progressBar.style.width = percentage + '%';
    }
    
    sectionProgress[sectionId] = percentage;
}

function setupAutoSave() {
    autoSaveInterval = setInterval(() => {
        const currentSection = document.querySelector('.form-section.active');
        if (currentSection && currentSection.id !== 'review') {
            saveSection(currentSection.id, false);
        }
    }, 30000); // Auto-save every 30 seconds
}

function setupKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey || e.metaKey) {
            switch(e.key) {
                case 's':
                    e.preventDefault();
                    saveCurrentSection();
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                    nextSection();
                    break;
                case 'ArrowLeft':
                    e.preventDefault();
                    previousSection();
                    break;
            }
        }
    });
}

function startTimeTracking() {
    timeInterval = setInterval(() => {
        timeSpent++;
        const minutes = Math.floor(timeSpent / 60);
        const hours = Math.floor(minutes / 60);
        
        let display = '';
        if (hours > 0) {
            display = `${hours}h ${minutes % 60}m`;
        } else {
            display = `${minutes}m`;
        }
        
        document.getElementById('time-spent').textContent = display;
    }, 60000); // Update every minute
}

function loadSavedProgress() {
    // Load from localStorage or make AJAX call to get saved progress
    const saved = localStorage.getItem('application_progress_' + document.getElementById('application-uuid').value);
    if (saved) {
        const progress = JSON.parse(saved);
        completedSections = progress.completedSections || [];
        timeSpent = progress.timeSpent || 0;
        updateOverallProgress();
    }
}

function saveProgressLocally() {
    const progress = {
        completedSections: completedSections,
        timeSpent: timeSpent,
        lastSaved: new Date().toISOString()
    };
    localStorage.setItem('application_progress_' + document.getElementById('application-uuid').value, JSON.stringify(progress));
}

// Mobile Functions
function toggleMobileNav() {
    document.querySelector('.nav-sidebar').classList.toggle('show');
}

// Additional Helper Functions
function expandAllSections() {
    // For preview mode - shows all sections at once
    document.querySelectorAll('.form-section').forEach(section => {
        section.style.display = 'block';
    });
    showToast('All sections expanded for review', 'info');
}

function previewApplication() {
    window.open(`/application/${document.getElementById('application-uuid').value}/preview`, '_blank');
}

function showHelp() {
    // Implement help modal or guide
    showToast('Help guide coming soon', 'info');
}

function showProgress() {
    // Show detailed progress modal
    showToast(`Progress: ${completedSections.length}/${sections.length} sections complete`, 'info');
}

// Cleanup on page unload
window.addEventListener('beforeunload', function(e) {
    saveProgressLocally();
    
    const currentSection = document.querySelector('.form-section.active');
    if (currentSection && currentSection.id !== 'review') {
        saveSection(currentSection.id, false);
    }
});
</script>
@endsection