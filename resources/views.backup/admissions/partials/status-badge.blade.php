{{-- Status Badge Component --}}
{{-- Path: resources/views/admissions/partials/status-badge.blade.php --}}

@php
    // Define status configurations
    $statusConfigs = [
        // Application Statuses
        'draft' => [
            'color' => 'secondary',
            'icon' => 'fas fa-pencil-alt',
            'label' => 'Draft',
            'description' => 'Application in progress'
        ],
        'submitted' => [
            'color' => 'info',
            'icon' => 'fas fa-paper-plane',
            'label' => 'Submitted',
            'description' => 'Application submitted for review'
        ],
        'under_review' => [
            'color' => 'primary',
            'icon' => 'fas fa-search',
            'label' => 'Under Review',
            'description' => 'Application is being reviewed'
        ],
        'documents_pending' => [
            'color' => 'warning',
            'icon' => 'fas fa-file-medical',
            'label' => 'Documents Pending',
            'description' => 'Additional documents required'
        ],
        'committee_review' => [
            'color' => 'purple',
            'icon' => 'fas fa-users',
            'label' => 'Committee Review',
            'description' => 'Under committee evaluation'
        ],
        'interview_scheduled' => [
            'color' => 'indigo',
            'icon' => 'fas fa-calendar-check',
            'label' => 'Interview Scheduled',
            'description' => 'Interview has been scheduled'
        ],
        'decision_pending' => [
            'color' => 'pink',
            'icon' => 'fas fa-hourglass-half',
            'label' => 'Decision Pending',
            'description' => 'Awaiting final decision'
        ],
        'admitted' => [
            'color' => 'success',
            'icon' => 'fas fa-check-circle',
            'label' => 'Admitted',
            'description' => 'Congratulations! You have been admitted'
        ],
        'conditional_admit' => [
            'color' => 'lime',
            'icon' => 'fas fa-exclamation-circle',
            'label' => 'Conditional Admit',
            'description' => 'Admitted with conditions'
        ],
        'waitlisted' => [
            'color' => 'amber',
            'icon' => 'fas fa-clock',
            'label' => 'Waitlisted',
            'description' => 'Placed on waiting list'
        ],
        'denied' => [
            'color' => 'danger',
            'icon' => 'fas fa-times-circle',
            'label' => 'Denied',
            'description' => 'Application not approved'
        ],
        'deferred' => [
            'color' => 'cyan',
            'icon' => 'fas fa-forward',
            'label' => 'Deferred',
            'description' => 'Decision deferred to next term'
        ],
        'withdrawn' => [
            'color' => 'dark',
            'icon' => 'fas fa-ban',
            'label' => 'Withdrawn',
            'description' => 'Application withdrawn'
        ],
        'expired' => [
            'color' => 'gray',
            'icon' => 'fas fa-hourglass-end',
            'label' => 'Expired',
            'description' => 'Application has expired'
        ],
        
        // Document Statuses
        'uploaded' => [
            'color' => 'secondary',
            'icon' => 'fas fa-upload',
            'label' => 'Uploaded',
            'description' => 'Document uploaded successfully'
        ],
        'pending_verification' => [
            'color' => 'warning',
            'icon' => 'fas fa-hourglass-half',
            'label' => 'Pending Verification',
            'description' => 'Document awaiting verification'
        ],
        'verified' => [
            'color' => 'success',
            'icon' => 'fas fa-check-circle',
            'label' => 'Verified',
            'description' => 'Document verified'
        ],
        'rejected' => [
            'color' => 'danger',
            'icon' => 'fas fa-times-circle',
            'label' => 'Rejected',
            'description' => 'Document rejected'
        ],
        
        // Enrollment Statuses
        'enrollment_pending' => [
            'color' => 'warning',
            'icon' => 'fas fa-user-clock',
            'label' => 'Enrollment Pending',
            'description' => 'Awaiting enrollment confirmation'
        ],
        'enrollment_confirmed' => [
            'color' => 'success',
            'icon' => 'fas fa-user-check',
            'label' => 'Enrolled',
            'description' => 'Enrollment confirmed'
        ],
        'enrollment_declined' => [
            'color' => 'danger',
            'icon' => 'fas fa-user-times',
            'label' => 'Declined',
            'description' => 'Enrollment offer declined'
        ],
        
        // Payment Statuses
        'payment_pending' => [
            'color' => 'warning',
            'icon' => 'fas fa-dollar-sign',
            'label' => 'Payment Pending',
            'description' => 'Awaiting payment'
        ],
        'payment_completed' => [
            'color' => 'success',
            'icon' => 'fas fa-check-circle',
            'label' => 'Paid',
            'description' => 'Payment completed'
        ],
        'payment_failed' => [
            'color' => 'danger',
            'icon' => 'fas fa-exclamation-triangle',
            'label' => 'Payment Failed',
            'description' => 'Payment processing failed'
        ],
        
        // Review Statuses
        'review_pending' => [
            'color' => 'secondary',
            'icon' => 'fas fa-clock',
            'label' => 'Pending Review',
            'description' => 'Awaiting reviewer action'
        ],
        'review_in_progress' => [
            'color' => 'primary',
            'icon' => 'fas fa-spinner',
            'label' => 'In Progress',
            'description' => 'Review in progress'
        ],
        'review_completed' => [
            'color' => 'success',
            'icon' => 'fas fa-check',
            'label' => 'Review Complete',
            'description' => 'Review has been completed'
        ],
        
        // Exam Statuses
        'exam_registered' => [
            'color' => 'info',
            'icon' => 'fas fa-clipboard-list',
            'label' => 'Registered',
            'description' => 'Registered for exam'
        ],
        'exam_scheduled' => [
            'color' => 'primary',
            'icon' => 'fas fa-calendar-alt',
            'label' => 'Scheduled',
            'description' => 'Exam scheduled'
        ],
        'exam_completed' => [
            'color' => 'success',
            'icon' => 'fas fa-check-double',
            'label' => 'Completed',
            'description' => 'Exam completed'
        ],
        'exam_passed' => [
            'color' => 'success',
            'icon' => 'fas fa-trophy',
            'label' => 'Passed',
            'description' => 'Exam passed successfully'
        ],
        'exam_failed' => [
            'color' => 'danger',
            'icon' => 'fas fa-times',
            'label' => 'Failed',
            'description' => 'Did not pass the exam'
        ],
    ];
    
    // Get the configuration for the given status
    $config = $statusConfigs[$status] ?? [
        'color' => 'secondary',
        'icon' => 'fas fa-question-circle',
        'label' => ucfirst(str_replace('_', ' ', $status)),
        'description' => 'Status: ' . $status
    ];
    
    // Determine badge size
    $sizeClass = match($size ?? 'normal') {
        'small' => 'badge-sm',
        'large' => 'badge-lg',
        'extra-large' => 'badge-xl',
        default => ''
    };
    
    // Determine display type
    $displayType = $type ?? 'badge'; // badge, pill, card, inline, button
@endphp

@if($displayType === 'badge')
    {{-- Standard Badge Display --}}
    <span class="badge bg-{{ $config['color'] }} {{ $sizeClass }} status-badge"
          data-bs-toggle="{{ $showTooltip ?? true ? 'tooltip' : '' }}"
          data-bs-placement="{{ $tooltipPlacement ?? 'top' }}"
          title="{{ $config['description'] }}">
        @if($showIcon ?? true)
            <i class="{{ $config['icon'] }} me-1"></i>
        @endif
        {{ $customLabel ?? $config['label'] }}
    </span>

@elseif($displayType === 'pill')
    {{-- Pill Style Badge --}}
    <span class="badge rounded-pill bg-{{ $config['color'] }} {{ $sizeClass }} status-pill"
          data-bs-toggle="{{ $showTooltip ?? true ? 'tooltip' : '' }}"
          data-bs-placement="{{ $tooltipPlacement ?? 'top' }}"
          title="{{ $config['description'] }}">
        @if($showIcon ?? true)
            <i class="{{ $config['icon'] }} me-1"></i>
        @endif
        {{ $customLabel ?? $config['label'] }}
    </span>

@elseif($displayType === 'card')
    {{-- Card Style Display --}}
    <div class="status-card border-start border-4 border-{{ $config['color'] }} bg-light p-3 rounded">
        <div class="d-flex align-items-center">
            <div class="status-icon me-3">
                <div class="rounded-circle bg-{{ $config['color'] }} text-white d-flex align-items-center justify-content-center"
                     style="width: 40px; height: 40px;">
                    <i class="{{ $config['icon'] }}"></i>
                </div>
            </div>
            <div class="status-content">
                <h6 class="mb-0 text-{{ $config['color'] }}">{{ $customLabel ?? $config['label'] }}</h6>
                @if($showDescription ?? true)
                    <small class="text-muted">{{ $config['description'] }}</small>
                @endif
                @if(isset($additionalInfo))
                    <div class="mt-1">
                        <small class="text-secondary">{{ $additionalInfo }}</small>
                    </div>
                @endif
            </div>
        </div>
    </div>

@elseif($displayType === 'inline')
    {{-- Inline Text Display --}}
    <span class="status-inline text-{{ $config['color'] }}">
        @if($showIcon ?? true)
            <i class="{{ $config['icon'] }} me-1"></i>
        @endif
        <span class="{{ $bold ?? false ? 'fw-bold' : '' }}">{{ $customLabel ?? $config['label'] }}</span>
        @if($showDescription ?? false)
            <small class="text-muted ms-1">({{ $config['description'] }})</small>
        @endif
    </span>

@elseif($displayType === 'button')
    {{-- Button Style Display --}}
    <button type="button" 
            class="btn btn-{{ $outline ?? false ? 'outline-' : '' }}{{ $config['color'] }} {{ $sizeClass }} status-button"
            @if($onClick ?? false) onclick="{{ $onClick }}" @endif
            @if($disabled ?? false) disabled @endif>
        @if($showIcon ?? true)
            <i class="{{ $config['icon'] }} me-1"></i>
        @endif
        {{ $customLabel ?? $config['label'] }}
    </button>

@elseif($displayType === 'timeline')
    {{-- Timeline Style Display --}}
    <div class="timeline-status d-flex align-items-center">
        <div class="timeline-icon">
            <div class="rounded-circle bg-{{ $config['color'] }} text-white d-flex align-items-center justify-content-center"
                 style="width: 30px; height: 30px;">
                <i class="{{ $config['icon'] }} small"></i>
            </div>
        </div>
        <div class="timeline-content ms-3">
            <span class="badge bg-{{ $config['color'] }}">{{ $customLabel ?? $config['label'] }}</span>
            @if(isset($timestamp))
                <small class="text-muted ms-2">{{ $timestamp }}</small>
            @endif
        </div>
    </div>

@elseif($displayType === 'progress')
    {{-- Progress Indicator Display --}}
    <div class="status-progress">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="text-{{ $config['color'] }}">
                @if($showIcon ?? true)
                    <i class="{{ $config['icon'] }} me-1"></i>
                @endif
                {{ $customLabel ?? $config['label'] }}
            </span>
            @if(isset($percentage))
                <span class="badge bg-{{ $config['color'] }}">{{ $percentage }}%</span>
            @endif
        </div>
        @if(isset($percentage))
            <div class="progress" style="height: 5px;">
                <div class="progress-bar bg-{{ $config['color'] }}" 
                     role="progressbar" 
                     style="width: {{ $percentage }}%"
                     aria-valuenow="{{ $percentage }}" 
                     aria-valuemin="0" 
                     aria-valuemax="100">
                </div>
            </div>
        @endif
    </div>

@elseif($displayType === 'alert')
    {{-- Alert Style Display --}}
    <div class="alert alert-{{ $config['color'] }} d-flex align-items-center status-alert" role="alert">
        <i class="{{ $config['icon'] }} me-2"></i>
        <div>
            <strong>{{ $customLabel ?? $config['label'] }}</strong>
            @if($showDescription ?? true)
                - {{ $config['description'] }}
            @endif
        </div>
        @if($dismissible ?? false)
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        @endif
    </div>

@elseif($displayType === 'dropdown')
    {{-- Dropdown Status Selector --}}
    <div class="dropdown status-dropdown">
        <button class="btn btn-{{ $config['color'] }} dropdown-toggle {{ $sizeClass }}" 
                type="button" 
                id="statusDropdown{{ $uniqueId ?? uniqid() }}" 
                data-bs-toggle="dropdown" 
                aria-expanded="false">
            @if($showIcon ?? true)
                <i class="{{ $config['icon'] }} me-1"></i>
            @endif
            {{ $customLabel ?? $config['label'] }}
        </button>
        @if(isset($options) && is_array($options))
            <ul class="dropdown-menu" aria-labelledby="statusDropdown{{ $uniqueId ?? uniqid() }}">
                @foreach($options as $optionValue => $optionLabel)
                    @php
                        $optionConfig = $statusConfigs[$optionValue] ?? null;
                    @endphp
                    <li>
                        <a class="dropdown-item" href="#" 
                           onclick="changeStatus('{{ $optionValue }}'); return false;">
                            @if($optionConfig)
                                <i class="{{ $optionConfig['icon'] }} text-{{ $optionConfig['color'] }} me-2"></i>
                            @endif
                            {{ $optionLabel }}
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

@else
    {{-- Default Badge Display --}}
    <span class="badge bg-{{ $config['color'] }} {{ $sizeClass }}">
        @if($showIcon ?? true)
            <i class="{{ $config['icon'] }} me-1"></i>
        @endif
        {{ $customLabel ?? $config['label'] }}
    </span>
@endif

@once
    @push('styles')
    <style>
        /* Badge Sizes */
        .badge-sm {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        
        .badge-lg {
            font-size: 1rem;
            padding: 0.5rem 1rem;
        }
        
        .badge-xl {
            font-size: 1.25rem;
            padding: 0.75rem 1.25rem;
        }
        
        /* Status Card */
        .status-card {
            transition: all 0.3s ease;
        }
        
        .status-card:hover {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            transform: translateX(2px);
        }
        
        /* Status Badge Animation */
        .status-badge {
            transition: all 0.2s ease;
            cursor: default;
        }
        
        .status-badge:hover {
            transform: scale(1.05);
        }
        
        /* Timeline Status */
        .timeline-status {
            position: relative;
        }
        
        .timeline-status::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 30px;
            height: calc(100% + 20px);
            width: 2px;
            background-color: #dee2e6;
        }
        
        /* Progress Status */
        .status-progress .progress {
            background-color: rgba(0,0,0,0.05);
        }
        
        /* Alert Status */
        .status-alert {
            border-left-width: 4px;
        }
        
        /* Dropdown Status */
        .status-dropdown .dropdown-item:hover {
            background-color: rgba(0,0,0,0.05);
        }
        
        /* Color utilities for custom colors */
        .bg-purple { background-color: #6f42c1 !important; }
        .text-purple { color: #6f42c1 !important; }
        .border-purple { border-color: #6f42c1 !important; }
        
        .bg-indigo { background-color: #6610f2 !important; }
        .text-indigo { color: #6610f2 !important; }
        .border-indigo { border-color: #6610f2 !important; }
        
        .bg-pink { background-color: #d63384 !important; }
        .text-pink { color: #d63384 !important; }
        .border-pink { border-color: #d63384 !important; }
        
        .bg-lime { background-color: #84cc16 !important; }
        .text-lime { color: #84cc16 !important; }
        .border-lime { border-color: #84cc16 !important; }
        
        .bg-amber { background-color: #f59e0b !important; }
        .text-amber { color: #f59e0b !important; }
        .border-amber { border-color: #f59e0b !important; }
        
        .bg-gray { background-color: #6b7280 !important; }
        .text-gray { color: #6b7280 !important; }
        .border-gray { border-color: #6b7280 !important; }
        
        /* Responsive adjustments */
        @media (max-width: 576px) {
            .status-card .status-content h6 {
                font-size: 0.875rem;
            }
            
            .status-card .status-icon div {
                width: 35px !important;
                height: 35px !important;
            }
        }
    </style>
    @endpush
    
    @push('scripts')
    <script>
        // Initialize tooltips for status badges
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
        
        // Function to change status (for dropdown type)
        function changeStatus(newStatus) {
            // This function should be implemented based on your specific needs
            console.log('Status changed to:', newStatus);
            // You can emit an event or make an AJAX call here
        }
    </script>
    @endpush
@endonce