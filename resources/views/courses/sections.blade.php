@extends('layouts.app')

@section('title', 'Manage Sections - ' . $course->code)

@section('breadcrumb')
    <a href="{{ route('courses.index') }}">Course Management</a>
    <i class="fas fa-chevron-right"></i>
    <a href="{{ route('courses.show', $course) }}">{{ $course->code }}</a>
    <i class="fas fa-chevron-right"></i>
    <span>Manage Sections</span>
@endsection

@section('page-actions')
    <a href="{{ route('courses.show', $course) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back to Course
    </a>
@endsection

@section('content')
<div class="container-fluid px-4">
    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Course Header -->
    <div class="card shadow-sm mb-4 bg-light">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="mb-1">{{ $course->code }} - {{ $course->title }}</h4>
                    <span class="badge bg-primary">{{ $course->department }}</span>
                    <span class="badge bg-info">Level {{ $course->level }}</span>
                    <span class="badge bg-success">{{ $course->credits }} Credits</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Create New Section Form -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary bg-gradient text-white">
                    <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Create New Section</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('courses.sections.create', $course) }}" id="createSectionForm">
                        @csrf
                        
                        <!-- Term -->
                        <div class="mb-3">
                            <label for="term_id" class="form-label">
                                Academic Term <span class="text-danger">*</span>
                            </label>
                            <select name="term_id" id="term_id" class="form-select @error('term_id') is-invalid @enderror" required>
                                <option value="">Select Term</option>
                                @foreach($terms as $term)
                                    <option value="{{ $term->id }}" {{ $term->is_current ? 'selected' : '' }}>
                                        {{ $term->name }}
                                        @if($term->is_current)
                                            <span class="badge bg-success ms-1">Current</span>
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('term_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Section Number -->
                        <div class="mb-3">
                            <label for="section_number" class="form-label">
                                Section Number <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="section_number" id="section_number" 
                                   class="form-control @error('section_number') is-invalid @enderror" 
                                   placeholder="e.g., 01, 02, A, B"
                                   required>
                            @error('section_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Instructor -->
                        <div class="mb-3">
                            <label for="instructor_id" class="form-label">Instructor</label>
                            <select name="instructor_id" id="instructor_id" class="form-select">
                                <option value="">TBA</option>
                                @foreach($instructors as $instructor)
                                    <option value="{{ $instructor->id }}">
                                        {{ $instructor->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Delivery Mode -->
                        <div class="mb-3">
                            <label for="delivery_mode" class="form-label">
                                Delivery Mode <span class="text-danger">*</span>
                            </label>
                            <select name="delivery_mode" id="delivery_mode" class="form-select @error('delivery_mode') is-invalid @enderror" required>
                                <option value="traditional">Traditional (In-person)</option>
                                <option value="online_sync">Online Synchronous</option>
                                <option value="online_async">Online Asynchronous</option>
                                <option value="hybrid">Hybrid</option>
                                <option value="hyflex">HyFlex</option>
                            </select>
                            @error('delivery_mode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Enrollment Capacity -->
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label for="enrollment_capacity" class="form-label">
                                    Capacity <span class="text-danger">*</span>
                                </label>
                                <input type="number" name="enrollment_capacity" id="enrollment_capacity" 
                                       class="form-control @error('enrollment_capacity') is-invalid @enderror" 
                                       min="1" max="500" value="30" required>
                                @error('enrollment_capacity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6">
                                <label for="waitlist_capacity" class="form-label">Waitlist</label>
                                <input type="number" name="waitlist_capacity" id="waitlist_capacity" 
                                       class="form-control" 
                                       min="0" max="50" value="5">
                            </div>
                        </div>

                        <!-- Schedule Accordion -->
                        <div class="accordion mb-3" id="scheduleAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#scheduleCollapse">
                                        <i class="fas fa-calendar-alt me-2"></i>Schedule (Optional)
                                    </button>
                                </h2>
                                <div id="scheduleCollapse" class="accordion-collapse collapse" data-bs-parent="#scheduleAccordion">
                                    <div class="accordion-body">
                                        <!-- Days of Week -->
                                        <div class="mb-3">
                                            <label for="days_of_week" class="form-label">Days</label>
                                            <select name="days_of_week" id="days_of_week" class="form-select">
                                                <option value="">Select Days</option>
                                                <option value="MWF">Mon, Wed, Fri</option>
                                                <option value="TTh">Tue, Thu</option>
                                                <option value="MW">Mon, Wed</option>
                                                <option value="TR">Tue, Thu</option>
                                                <option value="M">Monday</option>
                                                <option value="T">Tuesday</option>
                                                <option value="W">Wednesday</option>
                                                <option value="R">Thursday</option>
                                                <option value="F">Friday</option>
                                            </select>
                                        </div>
                                        
                                        <!-- Time -->
                                        <div class="row g-2 mb-3">
                                            <div class="col-6">
                                                <label for="start_time" class="form-label">Start</label>
                                                <input type="time" name="start_time" id="start_time" class="form-control">
                                            </div>
                                            <div class="col-6">
                                                <label for="end_time" class="form-label">End</label>
                                                <input type="time" name="end_time" id="end_time" class="form-control">
                                            </div>
                                        </div>
                                        
                                        <!-- Location -->
                                        <div class="mb-3">
                                            <label for="building" class="form-label">Building</label>
                                            <input type="text" name="building" id="building" 
                                                   class="form-control" placeholder="e.g., Science Building">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="room" class="form-label">Room</label>
                                            <input type="text" name="room" id="room" 
                                                   class="form-control" placeholder="e.g., Room 101">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Online Meeting Accordion (conditional) -->
                        <div class="accordion mb-3" id="onlineAccordion" style="display: none;">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#onlineCollapse">
                                        <i class="fas fa-video me-2"></i>Online Meeting Info
                                    </button>
                                </h2>
                                <div id="onlineCollapse" class="accordion-collapse collapse" data-bs-parent="#onlineAccordion">
                                    <div class="accordion-body">
                                        <div class="mb-3">
                                            <label for="online_meeting_url" class="form-label">Meeting URL</label>
                                            <input type="url" name="online_meeting_url" id="online_meeting_url" 
                                                   class="form-control" placeholder="https://zoom.us/j/...">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="online_meeting_password" class="form-label">Password</label>
                                            <input type="text" name="online_meeting_password" id="online_meeting_password" 
                                                   class="form-control">
                                        </div>
                                        
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="auto_record" value="1" id="autoRecord">
                                            <label class="form-check-label" for="autoRecord">
                                                Auto-record sessions
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Additional Fee -->
                        <div class="mb-3">
                            <label for="additional_fee" class="form-label">Additional Fee ($)</label>
                            <input type="number" name="additional_fee" id="additional_fee" 
                                   class="form-control" min="0" step="0.01" value="0">
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save me-2"></i>Create Section
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Existing Sections List -->
        <div class="col-lg-8">
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3 col-6 mb-3">
                    <div class="card bg-primary bg-gradient text-white">
                        <div class="card-body text-center">
                            <h3 class="mb-0">{{ $sections->count() }}</h3>
                            <small>Total Sections</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <div class="card bg-success bg-gradient text-white">
                        <div class="card-body text-center">
                            <h3 class="mb-0">{{ $sections->where('status', 'open')->count() }}</h3>
                            <small>Open Sections</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <div class="card bg-info bg-gradient text-white">
                        <div class="card-body text-center">
                            <h3 class="mb-0">{{ $sections->sum('current_enrollment') }}</h3>
                            <small>Total Enrolled</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <div class="card bg-warning bg-gradient text-dark">
                        <div class="card-body text-center">
                            <h3 class="mb-0">{{ $sections->sum('enrollment_capacity') - $sections->sum('current_enrollment') }}</h3>
                            <small>Available Seats</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sections Table -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Existing Sections</h5>
                </div>
                <div class="card-body p-0">
                    @if($sections->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>CRN</th>
                                        <th>Section</th>
                                        <th>Term</th>
                                        <th>Instructor</th>
                                        <th>Mode</th>
                                        <th>Schedule</th>
                                        <th class="text-center">Enrollment</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sections as $section)
                                    <tr>
                                        <td>{{ $section->crn }}</td>
                                        <td class="fw-semibold">{{ $section->section_number }}</td>
                                        <td>{{ $section->term->name ?? 'N/A' }}</td>
                                        <td>{{ $section->instructor->name ?? 'TBA' }}</td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                @php
                                                    $modeLabels = [
                                                        'traditional' => 'In-Person',
                                                        'online_sync' => 'Online Sync',
                                                        'online_async' => 'Online Async',
                                                        'hybrid' => 'Hybrid',
                                                        'hyflex' => 'HyFlex'
                                                    ];
                                                @endphp
                                                {{ $modeLabels[$section->delivery_mode] ?? ucwords(str_replace('_', ' ', $section->delivery_mode)) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($section->days_of_week)
                                                <strong>{{ $section->days_of_week }}</strong>
                                                @if($section->start_time)
                                                    <br><small>{{ date('g:i A', strtotime($section->start_time)) }} - {{ date('g:i A', strtotime($section->end_time)) }}</small>
                                                @endif
                                                @if($section->room)
                                                    <br><small class="text-muted">{{ $section->building }} {{ $section->room }}</small>
                                                @endif
                                            @else
                                                <span class="text-muted">TBA</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($section->isFull())
                                                <span class="badge bg-danger">
                                                    {{ $section->current_enrollment }}/{{ $section->enrollment_capacity }} FULL
                                                </span>
                                            @else
                                                <span class="badge bg-success">
                                                    {{ $section->current_enrollment }}/{{ $section->enrollment_capacity }}
                                                </span>
                                            @endif
                                            @if($section->waitlist_current > 0)
                                                <br><small class="text-muted">WL: {{ $section->waitlist_current }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <form method="POST" action="{{ route('courses.sections.update-status', [$course, $section]) }}" class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <select name="status" onchange="this.form.submit()" 
                                                        class="form-select form-select-sm {{ $section->status == 'open' ? 'border-success' : ($section->status == 'closed' ? 'border-danger' : '') }}">
                                                    <option value="planned" {{ $section->status == 'planned' ? 'selected' : '' }}>Planned</option>
                                                    <option value="open" {{ $section->status == 'open' ? 'selected' : '' }}>Open</option>
                                                    <option value="closed" {{ $section->status == 'closed' ? 'selected' : '' }}>Closed</option>
                                                    <option value="cancelled" {{ $section->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-primary" 
                                                        data-bs-toggle="tooltip" title="Edit Section"
                                                        onclick="alert('Edit feature coming soon!')">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-info" 
                                                        data-bs-toggle="tooltip" title="View Roster"
                                                        onclick="alert('Roster feature coming soon!')">
                                                    <i class="fas fa-users"></i>
                                                </button>
                                                <form method="POST" action="{{ route('courses.sections.delete', [$course, $section]) }}" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm" 
                                                            data-bs-toggle="tooltip" title="Delete Section"
                                                            onclick="return confirm('Are you sure you want to delete this section?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($sections->hasPages())
                            <div class="card-footer bg-white">
                                {{ $sections->links('custom.pagination') }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No sections created yet for this course.</p>
                            <p class="text-muted">Use the form on the left to create your first section.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .accordion-button:not(.collapsed) {
        background-color: #f0f6ff;
        color: #2563eb;
    }
    
    .accordion-button:focus {
        box-shadow: none;
        border-color: #dee2e6;
    }
    
    .form-select-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    .border-success {
        border-color: #10b981 !important;
    }
    
    .border-danger {
        border-color: #ef4444 !important;
    }
</style>
@endpush

@push('scripts')
<script>
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Show/hide online fields based on delivery mode
    document.getElementById('delivery_mode').addEventListener('change', function() {
        const onlineAccordion = document.getElementById('onlineAccordion');
        const mode = this.value;
        
        if (mode === 'online_sync' || mode === 'online_async' || mode === 'hybrid' || mode === 'hyflex') {
            onlineAccordion.style.display = 'block';
        } else {
            onlineAccordion.style.display = 'none';
        }
    });
</script>
@endpush