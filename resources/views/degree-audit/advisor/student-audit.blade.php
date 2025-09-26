{{-- resources/views/degree-audit/advisor/student-audit.blade.php --}}
@extends('layouts.app')

@section('title', 'Student Audit - ' . $student->user->name)

@section('styles')
<style>
    .override-section {
        background: #fef3c7;
        border: 2px dashed #f59e0b;
        border-radius: 8px;
        padding: 15px;
    }
    
    .advising-note {
        background: #f3f4f6;
        border-left: 4px solid #6b7280;
        padding: 12px;
        margin-bottom: 10px;
    }
    
    .action-buttons {
        position: sticky;
        top: 20px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <!-- Student Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3>{{ $student->user->name }}</h3>
                            <p class="mb-1">
                                <strong>Student ID:</strong> {{ $student->student_id }} | 
                                <strong>Email:</strong> {{ $student->user->email }}
                            </p>
                            <p class="mb-0">
                                <strong>Program:</strong> {{ $student->program->name ?? 'Undeclared' }} | 
                                <strong>Year:</strong> {{ $student->academic_year_name }}
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <button class="btn btn-light" onclick="window.history.back()">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Audit Content -->
        <div class="col-lg-9">
            <!-- Include the same content as student dashboard but with advisor controls -->
            @include('degree-audit.partials.audit-summary', ['auditReport' => $auditReport])
            
            <!-- Override Section (Advisor Only) -->
            <div class="card mb-4">
                <div class="card-header bg-warning">
                    <h5 class="mb-0">Requirement Overrides & Substitutions</h5>
                </div>
                <div class="card-body">
                    <div class="override-section mb-3">
                        <h6>Add Override/Substitution</h6>
                        <form id="overrideForm">
                            <div class="row">
                                <div class="col-md-4">
                                    <select class="form-select" name="requirement_id">
                                        <option value="">Select Requirement</option>
                                        @foreach($requirements ?? [] as $req)
                                            <option value="{{ $req->id }}">{{ $req->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select class="form-select" name="action_type">
                                        <option value="override">Override</option>
                                        <option value="substitute">Substitute Course</option>
                                        <option value="waive">Waive Requirement</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" name="reason" 
                                           placeholder="Reason for override">
                                </div>
                                <div class="col-12 mt-2">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-exclamation-triangle"></i> Apply Override
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Existing Overrides -->
                    <h6>Active Overrides</h6>
                    @forelse($overrides ?? [] as $override)
                    <div class="alert alert-warning">
                        <strong>{{ $override->requirement->name }}:</strong> {{ $override->type }}
                        <br>
                        <small>Reason: {{ $override->reason }} | By: {{ $override->approver->name }} | 
                               Date: {{ $override->created_at->format('M d, Y') }}</small>
                        <button class="btn btn-sm btn-danger float-end" 
                                onclick="removeOverride({{ $override->id }})">Remove</button>
                    </div>
                    @empty
                    <p class="text-muted">No active overrides</p>
                    @endforelse
                </div>
            </div>

            <!-- Advising Notes -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Advising Notes</h5>
                </div>
                <div class="card-body">
                    <form class="mb-3" onsubmit="addAdvisingNote(event)">
                        <div class="input-group">
                            <textarea class="form-control" rows="2" placeholder="Add a note..." 
                                      id="newNote"></textarea>
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-plus"></i> Add Note
                            </button>
                        </div>
                    </form>
                    
                    <div id="advisingNotes">
                        @foreach($advisingNotes ?? [] as $note)
                        <div class="advising-note">
                            <strong>{{ $note->advisor->name }}</strong>
                            <small class="text-muted float-end">{{ $note->created_at->format('M d, Y g:i A') }}</small>
                            <p class="mb-0 mt-2">{{ $note->content }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Actions -->
        <div class="col-lg-3">
            <div class="action-buttons">
                <!-- Quick Actions -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <button class="btn btn-primary w-100 mb-2" onclick="runNewAudit()">
                            <i class="fas fa-sync"></i> Run Fresh Audit
                        </button>
                        <button class="btn btn-success w-100 mb-2" onclick="clearForGraduation()">
                            <i class="fas fa-graduation-cap"></i> Clear for Graduation
                        </button>
                        <button class="btn btn-info w-100 mb-2" onclick="sendEmail()">
                            <i class="fas fa-envelope"></i> Email Student
                        </button>
                        <button class="btn btn-warning w-100 mb-2" onclick="scheduleAppointment()">
                            <i class="fas fa-calendar"></i> Schedule Meeting
                        </button>
                        <button class="btn btn-secondary w-100" onclick="printAudit()">
                            <i class="fas fa-print"></i> Print Report
                        </button>
                    </div>
                </div>

                <!-- Student Info Summary -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">Student Information</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong>GPA:</strong> {{ number_format($student->cumulative_gpa ?? 0, 2) }}</p>
                        <p class="mb-2"><strong>Credits:</strong> {{ $student->credits_completed ?? 0 }}</p>
                        <p class="mb-2"><strong>Standing:</strong> 
                            <span class="badge bg-{{ $student->academic_standing === 'good' ? 'success' : 'warning' }}">
                                {{ ucfirst($student->academic_standing ?? 'Unknown') }}
                            </span>
                        </p>
                        <p class="mb-2"><strong>Advisor:</strong> {{ $student->advisor->name ?? 'Not Assigned' }}</p>
                        <p class="mb-0"><strong>Last Advised:</strong> 
                            {{ $student->last_advised_date ? $student->last_advised_date->format('M d, Y') : 'Never' }}
                        </p>
                    </div>
                </div>

                <!-- Registration Holds -->
                @if($student->holds && count($student->holds) > 0)
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h6 class="mb-0">Registration Holds</h6>
                    </div>
                    <div class="card-body">
                        @foreach($student->holds as $hold)
                        <div class="alert alert-danger mb-2">
                            <strong>{{ $hold->type }}:</strong> {{ $hold->reason }}
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function addAdvisingNote(event) {
    event.preventDefault();
    const note = document.getElementById('newNote').value;
    
    if (!note) return;
    
    fetch(`/advisor/student/{{ $student->id }}/note`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ content: note })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function runNewAudit() {
    if (confirm('Run a fresh audit for this student?')) {
        fetch(`/degree-audit/run`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ 
                student_id: {{ $student->id }},
                force_refresh: true 
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
}

function clearForGraduation() {
    if (confirm('Clear this student for graduation?')) {
        fetch(`/graduation/clear`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ student_id: {{ $student->id }} })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
        });
    }
}

function removeOverride(overrideId) {
    if (confirm('Remove this override?')) {
        // Implementation to remove override
        console.log('Removing override:', overrideId);
    }
}

function sendEmail() {
    window.location.href = `mailto:{{ $student->user->email }}?subject=Degree Audit Review`;
}

function scheduleAppointment() {
    // Open scheduling modal or redirect to scheduling system
    alert('Opening scheduling system...');
}

function printAudit() {
    window.print();
}

// Handle override form submission
document.getElementById('overrideForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch(`/advisor/student/{{ $student->id }}/override`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Override applied successfully');
            location.reload();
        }
    });
});
</script>
@endsection