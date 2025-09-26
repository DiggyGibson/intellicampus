{{-- resources/views/degree-audit/advisor/batch-reports.blade.php --}}
@extends('layouts.app')

@section('title', 'Batch Degree Audit Reports')

@section('styles')
<style>
    .report-options {
        background: #f8fafc;
        border-radius: 12px;
        padding: 20px;
    }
    
    .student-select-item {
        padding: 8px;
        border-bottom: 1px solid #e5e7eb;
        cursor: pointer;
    }
    
    .student-select-item:hover {
        background: #f3f4f6;
    }
    
    .student-select-item.selected {
        background: #dbeafe;
        border-left: 3px solid #3b82f6;
    }
    
    .generation-status {
        display: none;
    }
    
    .generation-status.active {
        display: block;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2>Generate Batch Degree Audit Reports</h2>
            <p class="text-muted">Generate multiple student audit reports at once</p>
        </div>
    </div>

    <div class="row">
        <!-- Report Configuration -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Report Options</h5>
                </div>
                <div class="card-body">
                    <div class="report-options">
                        <div class="mb-3">
                            <label class="form-label">Report Type</label>
                            <select class="form-select" id="reportType">
                                <option value="summary">Summary Report</option>
                                <option value="detailed">Detailed Report</option>
                                <option value="progress">Progress Report</option>
                                <option value="graduation">Graduation Eligibility</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Format</label>
                            <select class="form-select" id="reportFormat">
                                <option value="pdf">PDF</option>
                                <option value="excel">Excel</option>
                                <option value="csv">CSV</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Include Sections</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="includeGPA" checked>
                                <label class="form-check-label" for="includeGPA">GPA Information</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="includeProgress" checked>
                                <label class="form-check-label" for="includeProgress">Progress Details</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="includeRecommendations" checked>
                                <label class="form-check-label" for="includeRecommendations">Recommendations</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="includeCourseHistory">
                                <label class="form-check-label" for="includeCourseHistory">Course History</label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Grouping</label>
                            <select class="form-select" id="groupBy">
                                <option value="none">No Grouping</option>
                                <option value="major">By Major</option>
                                <option value="year">By Academic Year</option>
                                <option value="status">By Status</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student Selection -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Select Students</h5>
                    <div class="mt-2">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick="selectAll()">Select All</button>
                            <button class="btn btn-outline-secondary" onclick="selectNone()">Clear</button>
                            <button class="btn btn-outline-info" onclick="selectAtRisk()">At Risk Only</button>
                        </div>
                    </div>
                </div>
                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                    <input type="text" class="form-control mb-3" placeholder="Search students..." 
                           id="studentSearchBatch" onkeyup="filterStudentList()">
                    
                    <div id="studentList">
                        @foreach($students ?? [] as $student)
                        <div class="student-select-item" 
                             data-student-id="{{ $student->id }}"
                             data-status="{{ $student->audit_status }}"
                             onclick="toggleStudent(this)">
                            <div class="form-check">
                                <input class="form-check-input student-checkbox" type="checkbox" 
                                       value="{{ $student->id }}" id="student{{ $student->id }}">
                                <label class="form-check-label" for="student{{ $student->id }}">
                                    <strong>{{ $student->user->name }}</strong>
                                    <small class="d-block text-muted">
                                        {{ $student->student_id }} | {{ $student->program->name ?? 'Undeclared' }}
                                    </small>
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="card-footer">
                    <small class="text-muted">
                        <span id="selectedCount">0</span> students selected
                    </small>
                </div>
            </div>
        </div>

        <!-- Generation Status -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Generation Status</h5>
                </div>
                <div class="card-body">
                    <!-- Initial State -->
                    <div id="preGeneration">
                        <div class="text-center py-5">
                            <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
                            <p class="text-muted">Configure options and select students to generate reports</p>
                            <button class="btn btn-success btn-lg" onclick="generateReports()" 
                                    id="generateBtn" disabled>
                                <i class="fas fa-play"></i> Generate Reports
                            </button>
                        </div>
                    </div>

                    <!-- Generation Progress -->
                    <div id="generationProgress" class="generation-status">
                        <h6>Generating Reports...</h6>
                        <div class="progress mb-3">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 id="progressBar" style="width: 0%"></div>
                        </div>
                        <p class="text-muted">
                            Processing <span id="currentStudent">0</span> of <span id="totalStudents">0</span> students
                        </p>
                        <div id="generationLog" class="border rounded p-2" 
                             style="max-height: 200px; overflow-y: auto; font-size: 0.875rem;">
                        </div>
                    </div>

                    <!-- Completion Status -->
                    <div id="generationComplete" class="generation-status">
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> Reports generated successfully!
                        </div>
                        <p><strong>Summary:</strong></p>
                        <ul id="completionSummary"></ul>
                        <button class="btn btn-primary" onclick="downloadReports()">
                            <i class="fas fa-download"></i> Download All Reports
                        </button>
                        <button class="btn btn-secondary" onclick="resetGenerator()">
                            <i class="fas fa-redo"></i> Generate New Batch
                        </button>
                    </div>
                </div>
            </div>

            <!-- Recent Batches -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Recent Batches</h5>
                </div>
                <div class="card-body">
                    @forelse($recentBatches ?? [] as $batch)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <small class="text-muted">{{ $batch->created_at->format('M d, Y g:i A') }}</small>
                            <div>{{ $batch->student_count }} students</div>
                        </div>
                        <button class="btn btn-sm btn-outline-primary" 
                                onclick="downloadBatch('{{ $batch->id }}')">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                    @empty
                    <p class="text-muted">No recent batches</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let selectedStudents = new Set();

function toggleStudent(element) {
    const checkbox = element.querySelector('.student-checkbox');
    const studentId = element.dataset.studentId;
    
    checkbox.checked = !checkbox.checked;
    element.classList.toggle('selected');
    
    if (checkbox.checked) {
        selectedStudents.add(studentId);
    } else {
        selectedStudents.delete(studentId);
    }
    
    updateSelectedCount();
}

function updateSelectedCount() {
    document.getElementById('selectedCount').textContent = selectedStudents.size;
    document.getElementById('generateBtn').disabled = selectedStudents.size === 0;
}

function selectAll() {
    document.querySelectorAll('.student-select-item').forEach(item => {
        item.classList.add('selected');
        item.querySelector('.student-checkbox').checked = true;
        selectedStudents.add(item.dataset.studentId);
    });
    updateSelectedCount();
}

function selectNone() {
    document.querySelectorAll('.student-select-item').forEach(item => {
        item.classList.remove('selected');
        item.querySelector('.student-checkbox').checked = false;
    });
    selectedStudents.clear();
    updateSelectedCount();
}

function selectAtRisk() {
    selectNone();
    document.querySelectorAll('.student-select-item[data-status="at-risk"]').forEach(item => {
        item.classList.add('selected');
        item.querySelector('.student-checkbox').checked = true;
        selectedStudents.add(item.dataset.studentId);
    });
    updateSelectedCount();
}

function filterStudentList() {
    const search = document.getElementById('studentSearchBatch').value.toLowerCase();
    
    document.querySelectorAll('.student-select-item').forEach(item => {
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(search) ? 'block' : 'none';
    });
}

function generateReports() {
    if (selectedStudents.size === 0) return;
    
    // Hide pre-generation, show progress
    document.getElementById('preGeneration').style.display = 'none';
    document.getElementById('generationProgress').classList.add('active');
    
    const totalStudents = selectedStudents.size;
    document.getElementById('totalStudents').textContent = totalStudents;
    
    // Simulate generation progress
    let processed = 0;
    const interval = setInterval(() => {
        processed++;
        const progress = (processed / totalStudents) * 100;
        
        document.getElementById('progressBar').style.width = progress + '%';
        document.getElementById('currentStudent').textContent = processed;
        
        // Add log entry
        const log = document.getElementById('generationLog');
        log.innerHTML += `<div>✓ Generated report for Student ${processed}</div>`;
        log.scrollTop = log.scrollHeight;
        
        if (processed >= totalStudents) {
            clearInterval(interval);
            showCompletion();
        }
    }, 500);
}

function showCompletion() {
    document.getElementById('generationProgress').classList.remove('active');
    document.getElementById('generationComplete').classList.add('active');
    
    // Show summary
    const summary = document.getElementById('completionSummary');
    summary.innerHTML = `
        <li>${selectedStudents.size} reports generated</li>
        <li>Format: ${document.getElementById('reportFormat').value.toUpperCase()}</li>
        <li>Type: ${document.getElementById('reportType').value}</li>
    `;
}

function downloadReports() {
    // Implementation for downloading reports
    alert('Downloading batch reports...');
}

function resetGenerator() {
    document.getElementById('generationComplete').classList.remove('active');
    document.getElementById('preGeneration').style.display = 'block';
    document.getElementById('progressBar').style.width = '0%';
    document.getElementById('generationLog').innerHTML = '';
    selectNone();
}

function downloadBatch(batchId) {
    // Download previous batch
    window.location.href = `/advisor/batch-reports/download/${batchId}`;
}
</script>
@endsection