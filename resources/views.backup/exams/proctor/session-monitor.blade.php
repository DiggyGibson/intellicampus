@extends('layouts.app')

@section('title', 'Exam Session Monitor - Proctor Dashboard')

@section('styles')
<style>
    .proctor-dashboard {
        background: #f8f9fa;
        min-height: 100vh;
        padding: 20px;
    }
    
    .session-header {
        background: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .session-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
    }
    
    .session-timer {
        font-size: 2rem;
        font-weight: bold;
        color: #2c3e50;
        text-align: center;
    }
    
    .timer-label {
        font-size: 0.9rem;
        color: #6c757d;
        margin-top: 5px;
    }
    
    .monitoring-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .candidate-card {
        background: white;
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: all 0.3s;
    }
    
    .candidate-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    
    .candidate-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }
    
    .candidate-status {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.85rem;
        font-weight: 500;
    }
    
    .status-active {
        background: #d4edda;
        color: #155724;
    }
    
    .status-idle {
        background: #fff3cd;
        color: #856404;
    }
    
    .status-disconnected {
        background: #f8d7da;
        color: #721c24;
    }
    
    .status-flagged {
        background: #f5c6cb;
        color: #721c24;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.7; }
        100% { opacity: 1; }
    }
    
    .candidate-details {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        margin-top: 10px;
    }
    
    .detail-item {
        font-size: 0.9rem;
    }
    
    .detail-label {
        color: #6c757d;
        margin-right: 5px;
    }
    
    .detail-value {
        font-weight: 500;
        color: #2c3e50;
    }
    
    .violation-indicator {
        display: inline-flex;
        align-items: center;
        background: #dc3545;
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.85rem;
        margin-left: 10px;
    }
    
    .action-buttons {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }
    
    .action-btn {
        flex: 1;
        padding: 8px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.9rem;
        transition: all 0.3s;
    }
    
    .btn-view {
        background: #17a2b8;
        color: white;
    }
    
    .btn-view:hover {
        background: #138496;
    }
    
    .btn-message {
        background: #6c757d;
        color: white;
    }
    
    .btn-message:hover {
        background: #5a6268;
    }
    
    .btn-flag {
        background: #ffc107;
        color: #212529;
    }
    
    .btn-flag:hover {
        background: #e0a800;
    }
    
    .btn-terminate {
        background: #dc3545;
        color: white;
    }
    
    .btn-terminate:hover {
        background: #c82333;
    }
    
    .control-panel {
        background: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .filter-controls {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }
    
    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    
    .filter-label {
        font-size: 0.9rem;
        color: #6c757d;
    }
    
    .summary-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }
    
    .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px;
        border-radius: 8px;
        text-align: center;
    }
    
    .stat-value {
        font-size: 2rem;
        font-weight: bold;
    }
    
    .stat-label {
        font-size: 0.9rem;
        opacity: 0.9;
        margin-top: 5px;
    }
    
    .alert-banner {
        background: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
        padding: 12px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .alert-content {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .webcam-preview {
        width: 100%;
        height: 120px;
        background: #2c3e50;
        border-radius: 4px;
        margin-top: 10px;
        position: relative;
        overflow: hidden;
    }
    
    .webcam-placeholder {
        color: white;
        text-align: center;
        padding-top: 45px;
        font-size: 0.9rem;
    }
    
    .live-indicator {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #28a745;
        color: white;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        animation: blink 2s infinite;
    }
    
    @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
</style>
@endsection

@section('content')
<div class="proctor-dashboard">
    <!-- Session Header -->
    <div class="session-header">
        <div class="session-info">
            <div>
                <h2 class="mb-2">{{ $session->exam->exam_name ?? 'Entrance Exam 2025' }}</h2>
                <p class="text-muted mb-0">
                    Session Code: <strong>{{ $session->session_code ?? 'SESS-2025-001-A' }}</strong> | 
                    Center: <strong>{{ $session->center->center_name ?? 'Main Campus' }}</strong> |
                    Room: <strong>{{ $session->room_number ?? 'Hall A' }}</strong>
                </p>
            </div>
            
            <div class="session-timer" id="sessionTimer">
                <div id="timerDisplay">02:45:30</div>
                <div class="timer-label">Time Remaining</div>
            </div>
        </div>
    </div>
    
    <!-- Alert Banner (if any violations) -->
    <div class="alert-banner" id="alertBanner" style="display: none;">
        <div class="alert-content">
            <i class="fas fa-exclamation-triangle"></i>
            <span id="alertMessage">Multiple violations detected. Immediate attention required.</span>
        </div>
        <button class="btn btn-sm btn-light" onclick="dismissAlert()">Dismiss</button>
    </div>
    
    <!-- Control Panel -->
    <div class="control-panel">
        <h4 class="mb-3">Monitoring Controls</h4>
        
        <div class="filter-controls">
            <div class="filter-group">
                <label class="filter-label">Filter by Status</label>
                <select class="form-select" id="statusFilter" onchange="filterCandidates()">
                    <option value="all">All Candidates</option>
                    <option value="active">Active</option>
                    <option value="idle">Idle</option>
                    <option value="flagged">Flagged</option>
                    <option value="disconnected">Disconnected</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label class="filter-label">Sort By</label>
                <select class="form-select" id="sortBy" onchange="sortCandidates()">
                    <option value="seat">Seat Number</option>
                    <option value="name">Name</option>
                    <option value="violations">Violations</option>
                    <option value="progress">Progress</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label class="filter-label">View Mode</label>
                <select class="form-select" id="viewMode" onchange="changeViewMode()">
                    <option value="grid">Grid View</option>
                    <option value="list">List View</option>
                    <option value="focus">Focus Mode</option>
                </select>
            </div>
            
            <div class="filter-group" style="margin-left: auto;">
                <label class="filter-label">&nbsp;</label>
                <button class="btn btn-primary" onclick="refreshMonitoring()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>
        
        <!-- Summary Statistics -->
        <div class="summary-stats">
            <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="stat-value">{{ $stats['total'] ?? 45 }}</div>
                <div class="stat-label">Total Candidates</div>
            </div>
            
            <div class="stat-card" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                <div class="stat-value">{{ $stats['active'] ?? 42 }}</div>
                <div class="stat-label">Active</div>
            </div>
            
            <div class="stat-card" style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);">
                <div class="stat-value">{{ $stats['flagged'] ?? 2 }}</div>
                <div class="stat-label">Flagged</div>
            </div>
            
            <div class="stat-card" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);">
                <div class="stat-value">{{ $stats['violations'] ?? 5 }}</div>
                <div class="stat-label">Total Violations</div>
            </div>
            
            <div class="stat-card" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);">
                <div class="stat-value">{{ $stats['submitted'] ?? 0 }}</div>
                <div class="stat-label">Submitted</div>
            </div>
        </div>
    </div>
    
    <!-- Monitoring Grid -->
    <div class="monitoring-grid" id="monitoringGrid">
        @forelse($candidates ?? [] as $candidate)
        <div class="candidate-card" data-candidate-id="{{ $candidate->id }}" data-status="{{ $candidate->status }}">
            <div class="candidate-header">
                <div>
                    <strong>{{ $candidate->seat_number ?? 'A-001' }}</strong>
                    @if($candidate->violations_count > 0)
                    <span class="violation-indicator">
                        <i class="fas fa-exclamation-circle"></i> {{ $candidate->violations_count }}
                    </span>
                    @endif
                </div>
                <span class="candidate-status status-{{ $candidate->status ?? 'active' }}">
                    {{ ucfirst($candidate->status ?? 'Active') }}
                </span>
            </div>
            
            <h5 class="mb-2">{{ $candidate->candidate_name ?? 'John Doe' }}</h5>
            
            <div class="candidate-details">
                <div class="detail-item">
                    <span class="detail-label">Reg No:</span>
                    <span class="detail-value">{{ $candidate->registration_number ?? 'REG-2025-001' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Progress:</span>
                    <span class="detail-value">{{ $candidate->progress ?? '65' }}%</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Questions:</span>
                    <span class="detail-value">{{ $candidate->answered ?? '32' }}/{{ $candidate->total_questions ?? '50' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Time Used:</span>
                    <span class="detail-value">{{ $candidate->time_used ?? '01:45:00' }}</span>
                </div>
            </div>
            
            <!-- Webcam Preview (for online exams) -->
            @if($session->proctoring_type === 'remote_live')
            <div class="webcam-preview">
                <div class="live-indicator">LIVE</div>
                <div class="webcam-placeholder">
                    <i class="fas fa-video"></i> Video Feed
                </div>
            </div>
            @endif
            
            <div class="action-buttons">
                <button class="action-btn btn-view" onclick="viewCandidate({{ $candidate->id }})">
                    <i class="fas fa-eye"></i> View
                </button>
                <button class="action-btn btn-message" onclick="sendMessage({{ $candidate->id }})">
                    <i class="fas fa-comment"></i> Message
                </button>
                <button class="action-btn btn-flag" onclick="flagCandidate({{ $candidate->id }})">
                    <i class="fas fa-flag"></i> Flag
                </button>
                @if($candidate->violations_count > 2)
                <button class="action-btn btn-terminate" onclick="confirmTerminate({{ $candidate->id }})">
                    <i class="fas fa-ban"></i> Terminate
                </button>
                @endif
            </div>
        </div>
        @empty
        <!-- Sample candidate cards for demonstration -->
        @for($i = 1; $i <= 6; $i++)
        <div class="candidate-card" data-candidate-id="{{ $i }}" data-status="active">
            <div class="candidate-header">
                <div>
                    <strong>A-{{ str_pad($i, 3, '0', STR_PAD_LEFT) }}</strong>
                    @if($i == 3)
                    <span class="violation-indicator">
                        <i class="fas fa-exclamation-circle"></i> 2
                    </span>
                    @endif
                </div>
                <span class="candidate-status status-{{ $i == 3 ? 'flagged' : ($i == 6 ? 'idle' : 'active') }}">
                    {{ $i == 3 ? 'Flagged' : ($i == 6 ? 'Idle' : 'Active') }}
                </span>
            </div>
            
            <h5 class="mb-2">Candidate {{ $i }}</h5>
            
            <div class="candidate-details">
                <div class="detail-item">
                    <span class="detail-label">Reg No:</span>
                    <span class="detail-value">REG-2025-{{ str_pad($i, 3, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Progress:</span>
                    <span class="detail-value">{{ rand(30, 90) }}%</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Questions:</span>
                    <span class="detail-value">{{ rand(15, 45) }}/50</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Time Used:</span>
                    <span class="detail-value">0{{ rand(1, 2) }}:{{ str_pad(rand(10, 59), 2, '0', STR_PAD_LEFT) }}:00</span>
                </div>
            </div>
            
            <div class="action-buttons">
                <button class="action-btn btn-view" onclick="viewCandidate({{ $i }})">
                    <i class="fas fa-eye"></i> View
                </button>
                <button class="action-btn btn-message" onclick="sendMessage({{ $i }})">
                    <i class="fas fa-comment"></i> Message
                </button>
                <button class="action-btn btn-flag" onclick="flagCandidate({{ $i }})">
                    <i class="fas fa-flag"></i> Flag
                </button>
            </div>
        </div>
        @endfor
        @endforelse
    </div>
</div>

<!-- View Candidate Modal -->
<div class="modal fade" id="viewCandidateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Candidate Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="candidateDetailsContent">
                <!-- Content loaded dynamically -->
            </div>
        </div>
    </div>
</div>

<!-- Message Modal -->
<div class="modal fade" id="messageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Message to Candidate</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Message Type</label>
                    <select class="form-select" id="messageType">
                        <option value="reminder">Time Reminder</option>
                        <option value="warning">Warning</option>
                        <option value="instruction">Instruction</option>
                        <option value="custom">Custom Message</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Message</label>
                    <textarea class="form-control" id="messageContent" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="sendMessageToCandidate()">Send Message</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Session timer
let sessionEndTime = new Date('{{ $session->end_time ?? now()->addHours(3) }}').getTime();

function updateTimer() {
    const now = new Date().getTime();
    const distance = sessionEndTime - now;
    
    if (distance < 0) {
        document.getElementById('timerDisplay').innerHTML = "00:00:00";
        document.getElementById('timerDisplay').style.color = '#dc3545';
        return;
    }
    
    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
    
    const display = 
        String(hours).padStart(2, '0') + ':' +
        String(minutes).padStart(2, '0') + ':' +
        String(seconds).padStart(2, '0');
    
    document.getElementById('timerDisplay').innerHTML = display;
    
    // Change color based on time remaining
    if (distance < 1800000) { // Less than 30 minutes
        document.getElementById('timerDisplay').style.color = '#dc3545';
    } else if (distance < 3600000) { // Less than 1 hour
        document.getElementById('timerDisplay').style.color = '#ffc107';
    }
}

// Update timer every second
setInterval(updateTimer, 1000);
updateTimer();

// Auto-refresh monitoring data
setInterval(refreshMonitoring, 30000); // Refresh every 30 seconds

function refreshMonitoring() {
    // Fetch updated monitoring data via AJAX
    fetch('/exams/proctor/session/{{ $session->id ?? 1 }}/monitor/refresh')
        .then(response => response.json())
        .then(data => {
            updateMonitoringGrid(data);
            updateStatistics(data.stats);
            checkForAlerts(data.alerts);
        })
        .catch(error => console.error('Error refreshing:', error));
}

function updateMonitoringGrid(data) {
    // Update the monitoring grid with new data
    // Implementation would update DOM elements
}

function updateStatistics(stats) {
    // Update summary statistics
    // Implementation would update stat cards
}

function checkForAlerts(alerts) {
    if (alerts && alerts.length > 0) {
        document.getElementById('alertBanner').style.display = 'block';
        document.getElementById('alertMessage').textContent = alerts[0].message;
    }
}

function viewCandidate(candidateId) {
    // Load candidate details
    fetch(`/exams/proctor/candidate/${candidateId}/details`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('candidateDetailsContent').innerHTML = html;
            new bootstrap.Modal(document.getElementById('viewCandidateModal')).show();
        });
}

function sendMessage(candidateId) {
    window.currentCandidateId = candidateId;
    new bootstrap.Modal(document.getElementById('messageModal')).show();
}

function sendMessageToCandidate() {
    const message = {
        candidate_id: window.currentCandidateId,
        type: document.getElementById('messageType').value,
        content: document.getElementById('messageContent').value
    };
    
    // Send message via AJAX
    fetch('/exams/proctor/message', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(message)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Message sent successfully');
            bootstrap.Modal.getInstance(document.getElementById('messageModal')).hide();
        }
    });
}

function flagCandidate(candidateId) {
    if (confirm('Flag this candidate for review?')) {
        // Flag candidate via AJAX
        fetch(`/exams/proctor/candidate/${candidateId}/flag`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                refreshMonitoring();
            }
        });
    }
}

function confirmTerminate(candidateId) {
    if (confirm('Are you sure you want to terminate this candidate\'s exam? This action cannot be undone.')) {
        // Terminate exam via AJAX
        fetch(`/exams/proctor/candidate/${candidateId}/terminate`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                reason: prompt('Please provide a reason for termination:')
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Exam terminated successfully');
                refreshMonitoring();
            }
        });
    }
}

function filterCandidates() {
    const filter = document.getElementById('statusFilter').value;
    const cards = document.querySelectorAll('.candidate-card');
    
    cards.forEach(card => {
        if (filter === 'all' || card.dataset.status === filter) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function dismissAlert() {
    document.getElementById('alertBanner').style.display = 'none';
}

// WebSocket connection for real-time updates (if available)
if (typeof Echo !== 'undefined') {
    Echo.channel('exam-session.{{ $session->id ?? 1 }}')
        .listen('CandidateStatusChanged', (e) => {
            updateCandidateStatus(e.candidateId, e.status);
        })
        .listen('ViolationDetected', (e) => {
            showViolationAlert(e.candidateId, e.violation);
        });
}
</script>
@endsection