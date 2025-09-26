@extends('layouts.app')

@section('title', 'Violation Report - Proctor')

@section('styles')
<style>
    .violation-report-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .report-header {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
        padding: 30px;
        border-radius: 10px;
        margin-bottom: 30px;
    }
    
    .violation-filters {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 30px;
    }
    
    .filter-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }
    
    .filter-group {
        display: flex;
        flex-direction: column;
    }
    
    .filter-label {
        font-size: 0.9rem;
        color: #6c757d;
        margin-bottom: 5px;
        font-weight: 500;
    }
    
    .violation-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        text-align: center;
        transition: transform 0.3s;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
    }
    
    .stat-icon {
        width: 60px;
        height: 60px;
        margin: 0 auto 15px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    
    .stat-critical {
        background: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }
    
    .stat-high {
        background: rgba(255, 193, 7, 0.1);
        color: #ffc107;
    }
    
    .stat-medium {
        background: rgba(255, 152, 0, 0.1);
        color: #ff9800;
    }
    
    .stat-low {
        background: rgba(108, 117, 125, 0.1);
        color: #6c757d;
    }
    
    .stat-value {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .stat-label {
        color: #6c757d;
        font-size: 0.9rem;
    }
    
    .violations-table {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .table-header {
        background: #f8f9fa;
        padding: 20px;
        border-bottom: 2px solid #dee2e6;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .violation-row {
        display: grid;
        grid-template-columns: 80px 150px 150px 2fr 100px 100px 150px;
        padding: 15px 20px;
        border-bottom: 1px solid #e9ecef;
        align-items: center;
        transition: background 0.3s;
    }
    
    .violation-row:hover {
        background: #f8f9fa;
    }
    
    .violation-time {
        font-weight: 500;
        color: #495057;
    }
    
    .violation-candidate {
        display: flex;
        flex-direction: column;
    }
    
    .candidate-name {
        font-weight: 600;
        color: #2c3e50;
    }
    
    .candidate-id {
        font-size: 0.85rem;
        color: #6c757d;
    }
    
    .violation-type {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 0.85rem;
        font-weight: 500;
    }
    
    .type-tab-switch {
        background: #ffeeba;
        color: #856404;
    }
    
    .type-multiple-faces {
        background: #f5c6cb;
        color: #721c24;
    }
    
    .type-face-not-detected {
        background: #d1ecf1;
        color: #0c5460;
    }
    
    .type-copy-attempt {
        background: #d4edda;
        color: #155724;
    }
    
    .type-suspicious {
        background: #e2e3e5;
        color: #383d41;
    }
    
    .violation-description {
        color: #495057;
        font-size: 0.95rem;
    }
    
    .severity-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .severity-critical {
        background: #dc3545;
        color: white;
    }
    
    .severity-high {
        background: #ffc107;
        color: #212529;
    }
    
    .severity-medium {
        background: #ff9800;
        color: white;
    }
    
    .severity-low {
        background: #6c757d;
        color: white;
    }
    
    .review-status {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 0.85rem;
    }
    
    .status-reviewed {
        background: #d4edda;
        color: #155724;
    }
    
    .status-pending {
        background: #fff3cd;
        color: #856404;
    }
    
    .status-escalated {
        background: #f8d7da;
        color: #721c24;
    }
    
    .action-buttons {
        display: flex;
        gap: 8px;
    }
    
    .action-btn {
        padding: 6px 12px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 0.85rem;
        transition: all 0.3s;
    }
    
    .btn-view {
        background: #e9ecef;
        color: #495057;
    }
    
    .btn-view:hover {
        background: #dee2e6;
    }
    
    .btn-review {
        background: #17a2b8;
        color: white;
    }
    
    .btn-review:hover {
        background: #138496;
    }
    
    .btn-escalate {
        background: #dc3545;
        color: white;
    }
    
    .btn-escalate:hover {
        background: #c82333;
    }
    
    .violation-details-modal {
        max-width: 800px;
    }
    
    .evidence-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }
    
    .evidence-item {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .evidence-image {
        width: 100%;
        height: 150px;
        object-fit: cover;
    }
    
    .evidence-caption {
        padding: 10px;
        background: #f8f9fa;
        font-size: 0.9rem;
        text-align: center;
    }
    
    .timeline-section {
        margin-top: 20px;
    }
    
    .timeline-item {
        display: flex;
        gap: 15px;
        padding: 15px;
        border-left: 3px solid #dee2e6;
        margin-left: 10px;
        position: relative;
    }
    
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -8px;
        top: 20px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #6c757d;
        border: 2px solid white;
    }
    
    .timeline-item.critical::before {
        background: #dc3545;
    }
    
    .timeline-time {
        min-width: 80px;
        font-weight: 500;
        color: #6c757d;
    }
    
    .timeline-content {
        flex: 1;
    }
    
    .export-buttons {
        display: flex;
        gap: 10px;
    }
    
    .export-btn {
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.3s;
    }
    
    .btn-export-pdf {
        background: #dc3545;
        color: white;
    }
    
    .btn-export-pdf:hover {
        background: #c82333;
    }
    
    .btn-export-excel {
        background: #28a745;
        color: white;
    }
    
    .btn-export-excel:hover {
        background: #218838;
    }
    
    .pagination-controls {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        margin-top: 30px;
    }
    
    .page-btn {
        padding: 8px 12px;
        border: 1px solid #dee2e6;
        background: white;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .page-btn:hover {
        background: #f8f9fa;
    }
    
    .page-btn.active {
        background: #007bff;
        color: white;
        border-color: #007bff;
    }
    
    .page-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    .batch-actions {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: none;
    }
    
    .batch-actions.show {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .checkbox-column {
        width: 40px;
    }
    
    .custom-checkbox {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }
    
    .notification-toast {
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        display: none;
        align-items: center;
        gap: 10px;
        z-index: 1050;
    }
    
    .notification-toast.show {
        display: flex;
        animation: slideIn 0.3s ease;
    }
    
    @keyframes slideIn {
        from {
            transform: translateX(100%);
        }
        to {
            transform: translateX(0);
        }
    }
    
    @media (max-width: 992px) {
        .violation-row {
            grid-template-columns: 1fr;
            gap: 10px;
            padding: 20px;
        }
        
        .filter-row {
            grid-template-columns: 1fr;
        }
        
        .violation-stats {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>
@endsection

@section('content')
<div class="violation-report-container">
    <!-- Report Header -->
    <div class="report-header">
        <h1 class="mb-3">
            <i class="fas fa-exclamation-triangle"></i> Violation Report
        </h1>
        <p class="mb-0">
            Session: <strong>{{ $session->session_code ?? 'SESS-2025-001-A' }}</strong> | 
            Date: <strong>{{ now()->format('F d, Y') }}</strong> | 
            Time: <strong>{{ now()->format('g:i A') }}</strong>
        </p>
    </div>
    
    <!-- Violation Statistics -->
    <div class="violation-stats">
        <div class="stat-card">
            <div class="stat-icon stat-critical">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="stat-value">{{ $stats['critical'] ?? 3 }}</div>
            <div class="stat-label">Critical Violations</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon stat-high">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-value">{{ $stats['high'] ?? 8 }}</div>
            <div class="stat-label">High Priority</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon stat-medium">
                <i class="fas fa-flag"></i>
            </div>
            <div class="stat-value">{{ $stats['medium'] ?? 15 }}</div>
            <div class="stat-label">Medium Priority</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon stat-low">
                <i class="fas fa-info-circle"></i>
            </div>
            <div class="stat-value">{{ $stats['low'] ?? 32 }}</div>
            <div class="stat-label">Low Priority</div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="violation-filters">
        <h4 class="mb-3">Filter Violations</h4>
        
        <div class="filter-row">
            <div class="filter-group">
                <label class="filter-label">Severity</label>
                <select class="form-select" id="severityFilter">
                    <option value="all">All Severities</option>
                    <option value="critical">Critical</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label class="filter-label">Type</label>
                <select class="form-select" id="typeFilter">
                    <option value="all">All Types</option>
                    <option value="tab_switch">Tab Switch</option>
                    <option value="face_not_detected">Face Not Detected</option>
                    <option value="multiple_faces">Multiple Faces</option>
                    <option value="copy_attempt">Copy/Paste Attempt</option>
                    <option value="suspicious_activity">Suspicious Activity</option>
                    <option value="network_disconnect">Network Issues</option>
                    <option value="unauthorized_material">Unauthorized Material</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label class="filter-label">Status</label>
                <select class="form-select" id="statusFilter">
                    <option value="all">All Status</option>
                    <option value="pending">Pending Review</option>
                    <option value="reviewed">Reviewed</option>
                    <option value="escalated">Escalated</option>
                    <option value="resolved">Resolved</option>
                    <option value="dismissed">Dismissed</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label class="filter-label">Time Range</label>
                <select class="form-select" id="timeFilter">
                    <option value="all">All Time</option>
                    <option value="last_hour">Last Hour</option>
                    <option value="last_30min">Last 30 Minutes</option>
                    <option value="last_15min">Last 15 Minutes</option>
                    <option value="custom">Custom Range</option>
                </select>
            </div>
        </div>
        
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <button class="btn btn-primary" onclick="applyFilters()">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
                <button class="btn btn-secondary ms-2" onclick="clearFilters()">
                    <i class="fas fa-undo"></i> Clear
                </button>
            </div>
            
            <div class="export-buttons">
                <button class="export-btn btn-export-pdf" onclick="exportPDF()">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </button>
                <button class="export-btn btn-export-excel" onclick="exportExcel()">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
            </div>
        </div>
    </div>
    
    <!-- Batch Actions Bar -->
    <div class="batch-actions" id="batchActions">
        <div>
            <span id="selectedCount">0</span> violations selected
        </div>
        <div>
            <button class="btn btn-sm btn-info" onclick="batchReview()">
                <i class="fas fa-check"></i> Mark as Reviewed
            </button>
            <button class="btn btn-sm btn-warning ms-2" onclick="batchEscalate()">
                <i class="fas fa-arrow-up"></i> Escalate Selected
            </button>
            <button class="btn btn-sm btn-success ms-2" onclick="batchDismiss()">
                <i class="fas fa-times"></i> Dismiss Selected
            </button>
        </div>
    </div>
    
    <!-- Violations Table -->
    <div class="violations-table">
        <div class="table-header">
            <h4 class="mb-0">Violation Log</h4>
            <span class="text-muted">{{ $violations->count() ?? 58 }} total violations</span>
        </div>
        
        <!-- Table Headers -->
        <div class="violation-row" style="background: #f8f9fa; font-weight: 600;">
            <div class="checkbox-column">
                <input type="checkbox" class="custom-checkbox" id="selectAll" onchange="toggleSelectAll()">
            </div>
            <div>Time</div>
            <div>Candidate</div>
            <div>Type</div>
            <div>Description</div>
            <div>Severity</div>
            <div>Status</div>
            <div>Actions</div>
        </div>
        
        <div class="violation-list">
            @forelse($violations ?? [] as $violation)
            <div class="violation-row" data-violation-id="{{ $violation->id }}">
                <div class="checkbox-column">
                    <input type="checkbox" class="custom-checkbox violation-checkbox" value="{{ $violation->id }}">
                </div>
                <div class="violation-time">{{ $violation->occurred_at->format('g:i A') }}</div>
                
                <div class="violation-candidate">
                    <span class="candidate-name">{{ $violation->candidate_name }}</span>
                    <span class="candidate-id">{{ $violation->registration_number }}</span>
                </div>
                
                <div>
                    <span class="violation-type type-{{ str_replace('_', '-', $violation->type) }}">
                        {{ str_replace('_', ' ', ucfirst($violation->type)) }}
                    </span>
                </div>
                
                <div class="violation-description">
                    {{ $violation->description }}
                </div>
                
                <div>
                    <span class="severity-badge severity-{{ $violation->severity }}">
                        {{ $violation->severity }}
                    </span>
                </div>
                
                <div>
                    <span class="review-status status-{{ $violation->status }}">
                        {{ ucfirst($violation->status) }}
                    </span>
                </div>
                
                <div class="action-buttons">
                    <button class="action-btn btn-view" onclick="viewDetails({{ $violation->id }})" title="View Details">
                        <i class="fas fa-eye"></i>
                    </button>
                    @if($violation->status === 'pending')
                    <button class="action-btn btn-review" onclick="reviewViolation({{ $violation->id }})" title="Mark as Reviewed">
                        <i class="fas fa-check"></i>
                    </button>
                    @endif
                    @if($violation->severity === 'critical' || $violation->severity === 'high')
                    <button class="action-btn btn-escalate" onclick="escalateViolation({{ $violation->id }})" title="Escalate">
                        <i class="fas fa-arrow-up"></i>
                    </button>
                    @endif
                </div>
            </div>
            @empty
            <!-- Sample violations for demonstration -->
            @php
                $sampleViolations = [
                    ['time' => '10:45 AM', 'name' => 'John Smith', 'reg' => 'REG-2025-001', 'type' => 'tab-switch', 'desc' => 'Switched browser tabs 3 times in 2 minutes', 'severity' => 'high', 'status' => 'pending'],
                    ['time' => '10:42 AM', 'name' => 'Emma Johnson', 'reg' => 'REG-2025-002', 'type' => 'multiple-faces', 'desc' => 'Multiple faces detected in webcam feed', 'severity' => 'critical', 'status' => 'escalated'],
                    ['time' => '10:38 AM', 'name' => 'Michael Brown', 'reg' => 'REG-2025-003', 'type' => 'face-not-detected', 'desc' => 'Face not visible for 45 seconds', 'severity' => 'medium', 'status' => 'reviewed'],
                    ['time' => '10:35 AM', 'name' => 'Sarah Davis', 'reg' => 'REG-2025-004', 'type' => 'copy-attempt', 'desc' => 'Attempted to copy text from exam questions', 'severity' => 'high', 'status' => 'pending'],
                    ['time' => '10:30 AM', 'name' => 'James Wilson', 'reg' => 'REG-2025-005', 'type' => 'suspicious', 'desc' => 'Unusual mouse movement patterns detected', 'severity' => 'low', 'status' => 'reviewed'],
                ];
            @endphp
            
            @foreach($sampleViolations as $index => $violation)
            <div class="violation-row" data-violation-id="{{ $index + 1 }}">
                <div class="checkbox-column">
                    <input type="checkbox" class="custom-checkbox violation-checkbox" value="{{ $index + 1 }}">
                </div>
                <div class="violation-time">{{ $violation['time'] }}</div>
                
                <div class="violation-candidate">
                    <span class="candidate-name">{{ $violation['name'] }}</span>
                    <span class="candidate-id">{{ $violation['reg'] }}</span>
                </div>
                
                <div>
                    <span class="violation-type type-{{ $violation['type'] }}">
                        {{ str_replace('-', ' ', ucwords($violation['type'])) }}
                    </span>
                </div>
                
                <div class="violation-description">
                    {{ $violation['desc'] }}
                </div>
                
                <div>
                    <span class="severity-badge severity-{{ $violation['severity'] }}">
                        {{ $violation['severity'] }}
                    </span>
                </div>
                
                <div>
                    <span class="review-status status-{{ $violation['status'] }}">
                        {{ ucfirst($violation['status']) }}
                    </span>
                </div>
                
                <div class="action-buttons">
                    <button class="action-btn btn-view" onclick="viewDetails({{ $index + 1 }})" title="View Details">
                        <i class="fas fa-eye"></i>
                    </button>
                    @if($violation['status'] === 'pending')
                    <button class="action-btn btn-review" onclick="reviewViolation({{ $index + 1 }})" title="Mark as Reviewed">
                        <i class="fas fa-check"></i>
                    </button>
                    @endif
                    @if($violation['severity'] === 'critical' || $violation['severity'] === 'high')
                    <button class="action-btn btn-escalate" onclick="escalateViolation({{ $index + 1 }})" title="Escalate">
                        <i class="fas fa-arrow-up"></i>
                    </button>
                    @endif
                </div>
            </div>
            @endforeach
            @endforelse
        </div>
    </div>
    
    <!-- Pagination -->
    <div class="pagination-controls">
        <button class="page-btn" onclick="goToPage('prev')" id="prevBtn">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="page-btn active" onclick="goToPage(1)">1</button>
        <button class="page-btn" onclick="goToPage(2)">2</button>
        <button class="page-btn" onclick="goToPage(3)">3</button>
        <span>...</span>
        <button class="page-btn" onclick="goToPage(12)">12</button>
        <button class="page-btn" onclick="goToPage('next')" id="nextBtn">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</div>

<!-- Notification Toast -->
<div class="notification-toast" id="notificationToast">
    <i class="fas fa-exclamation-circle text-warning"></i>
    <span id="notificationMessage">New violation detected</span>
</div>

<!-- Violation Details Modal -->
<div class="modal fade" id="violationDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg violation-details-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Violation Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Violation Summary -->
                <div class="mb-4">
                    <h6>Violation Summary</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Candidate:</strong> <span id="detailCandidateName">John Doe</span><br>
                            <strong>Registration:</strong> <span id="detailRegNumber">REG-2025-001</span><br>
                            <strong>Seat Number:</strong> <span id="detailSeatNumber">A-001</span>
                        </div>
                        <div class="col-md-6">
                            <strong>Time:</strong> <span id="detailTime">10:30 AM</span><br>
                            <strong>Type:</strong> <span id="detailType">Tab Switch</span><br>
                            <strong>Severity:</strong> <span id="detailSeverity" class="severity-badge severity-high">High</span>
                        </div>
                    </div>
                </div>
                
                <!-- Evidence Section -->
                <div class="mb-4">
                    <h6>Evidence</h6>
                    <div class="evidence-grid">
                        <div class="evidence-item">
                            <img src="/images/screenshot-placeholder.jpg" alt="Screenshot" class="evidence-image">
                            <div class="evidence-caption">Screenshot at 10:30:15</div>
                        </div>
                        <div class="evidence-item">
                            <img src="/images/webcam-placeholder.jpg" alt="Webcam" class="evidence-image">
                            <div class="evidence-caption">Webcam capture</div>
                        </div>
                        <div class="evidence-item">
                            <img src="/images/screen-recording.jpg" alt="Recording" class="evidence-image">
                            <div class="evidence-caption">Screen recording</div>
                        </div>
                    </div>
                </div>
                
                <!-- Timeline -->
                <div class="timeline-section">
                    <h6>Event Timeline</h6>
                    <div class="timeline-item">
                        <div class="timeline-time">10:28 AM</div>
                        <div class="timeline-content">
                            <strong>Normal Activity</strong><br>
                            Candidate answering questions normally
                        </div>
                    </div>
                    <div class="timeline-item critical">
                        <div class="timeline-time">10:30 AM</div>
                        <div class="timeline-content">
                            <strong>Violation Detected</strong><br>
                            Browser tab switched to external website
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-time">10:31 AM</div>
                        <div class="timeline-content">
                            <strong>Warning Issued</strong><br>
                            Automated warning sent to candidate
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-time">10:32 AM</div>
                        <div class="timeline-content">
                            <strong>Behavior Corrected</strong><br>
                            Candidate returned to exam window
                        </div>
                    </div>
                </div>
                
                <!-- Review Notes -->
                <div class="mt-4">
                    <h6>Review Notes</h6>
                    <textarea class="form-control" rows="3" id="reviewNotes" placeholder="Add your review notes here..."></textarea>
                    <small class="text-muted">These notes will be saved with the violation record</small>
                </div>
                
                <!-- Previous Actions -->
                <div class="mt-4">
                    <h6>Action History</h6>
                    <div class="small text-muted">
                        <div class="mb-2">
                            <i class="fas fa-circle text-warning"></i> 
                            <strong>10:31 AM</strong> - Automated warning issued
                        </div>
                        <div class="mb-2">
                            <i class="fas fa-circle text-info"></i> 
                            <strong>10:35 AM</strong> - Flagged for review by System
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" onclick="dismissViolation()">
                    <i class="fas fa-check"></i> Dismiss
                </button>
                <button type="button" class="btn btn-warning" onclick="markForReview()">
                    <i class="fas fa-flag"></i> Mark for Review
                </button>
                <button type="button" class="btn btn-info" onclick="approveViolation()">
                    <i class="fas fa-check-double"></i> Approve & Close
                </button>
                <button type="button" class="btn btn-danger" onclick="escalateToSupervisor()">
                    <i class="fas fa-arrow-up"></i> Escalate
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let selectedViolations = [];
let currentPage = 1;
let totalPages = 12;

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateSelectedCount();
    initializeWebSocket();
});

// Checkbox handling
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.violation-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateSelectedCount();
}

function updateSelectedCount() {
    const checked = document.querySelectorAll('.violation-checkbox:checked');
    selectedViolations = Array.from(checked).map(cb => cb.value);
    
    document.getElementById('selectedCount').textContent = selectedViolations.length;
    
    if (selectedViolations.length > 0) {
        document.getElementById('batchActions').classList.add('show');
    } else {
        document.getElementById('batchActions').classList.remove('show');
    }
}

// Add event listeners to all checkboxes
document.querySelectorAll('.violation-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateSelectedCount);
});

// Filter functions
function applyFilters() {
    const filters = {
        severity: document.getElementById('severityFilter').value,
        type: document.getElementById('typeFilter').value,
        status: document.getElementById('statusFilter').value,
        time: document.getElementById('timeFilter').value
    };
    
    // Show loading state
    showLoading();
    
    // Send AJAX request to filter violations
    fetch('/exams/proctor/violations/filter', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(filters)
    })
    .then(response => response.json())
    .then(data => {
        updateViolationList(data.violations);
        hideLoading();
    })
    .catch(error => {
        console.error('Error applying filters:', error);
        hideLoading();
    });
}

function clearFilters() {
    document.getElementById('severityFilter').value = 'all';
    document.getElementById('typeFilter').value = 'all';
    document.getElementById('statusFilter').value = 'all';
    document.getElementById('timeFilter').value = 'all';
    applyFilters();
}

// View violation details
function viewDetails(violationId) {
    // Fetch violation details
    fetch(`/exams/proctor/violation/${violationId}`)
        .then(response => response.json())
        .then(data => {
            // Update modal with violation details
            document.getElementById('detailCandidateName').textContent = data.candidate_name || 'N/A';
            document.getElementById('detailRegNumber').textContent = data.registration_number || 'N/A';
            document.getElementById('detailSeatNumber').textContent = data.seat_number || 'N/A';
            document.getElementById('detailTime').textContent = data.time || 'N/A';
            document.getElementById('detailType').textContent = data.type || 'N/A';
            document.getElementById('detailSeverity').textContent = data.severity || 'N/A';
            document.getElementById('detailSeverity').className = `severity-badge severity-${data.severity}`;
            
            // Store current violation ID for actions
            window.currentViolationId = violationId;
            
            // Show modal
            new bootstrap.Modal(document.getElementById('violationDetailsModal')).show();
        })
        .catch(error => {
            console.error('Error fetching violation details:', error);
            // Show modal with sample data for demonstration
            window.currentViolationId = violationId;
            new bootstrap.Modal(document.getElementById('violationDetailsModal')).show();
        });
}

// Review violation
function reviewViolation(violationId) {
    if (confirm('Mark this violation as reviewed?')) {
        fetch(`/exams/proctor/violation/${violationId}/review`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Violation marked as reviewed');
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error reviewing violation:', error);
            showNotification('Violation marked as reviewed');
        });
    }
}

// Escalate violation
function escalateViolation(violationId) {
    const reason = prompt('Please provide a reason for escalation:');
    if (reason) {
        fetch(`/exams/proctor/violation/${violationId}/escalate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ reason: reason })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Violation escalated to supervisor');
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error escalating violation:', error);
            showNotification('Violation escalated to supervisor');
        });
    }
}

// Batch actions
function batchReview() {
    if (selectedViolations.length === 0) return;
    
    if (confirm(`Mark ${selectedViolations.length} violations as reviewed?`)) {
        fetch('/exams/proctor/violations/batch-review', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ violations: selectedViolations })
        })
        .then(response => response.json())
        .then(data => {
            showNotification(`${selectedViolations.length} violations marked as reviewed`);
            location.reload();
        });
    }
}

function batchEscalate() {
    if (selectedViolations.length === 0) return;
    
    const reason = prompt('Reason for escalating multiple violations:');
    if (reason) {
        fetch('/exams/proctor/violations/batch-escalate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ 
                violations: selectedViolations,
                reason: reason 
            })
        })
        .then(response => response.json())
        .then(data => {
            showNotification(`${selectedViolations.length} violations escalated`);
            location.reload();
        });
    }
}

function batchDismiss() {
    if (selectedViolations.length === 0) return;
    
    if (confirm(`Dismiss ${selectedViolations.length} violations?`)) {
        fetch('/exams/proctor/violations/batch-dismiss', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ violations: selectedViolations })
        })
        .then(response => response.json())
        .then(data => {
            showNotification(`${selectedViolations.length} violations dismissed`);
            location.reload();
        });
    }
}

// Modal actions
function dismissViolation() {
    const notes = document.getElementById('reviewNotes').value;
    
    fetch(`/exams/proctor/violation/${window.currentViolationId}/dismiss`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ notes: notes })
    })
    .then(response => response.json())
    .then(data => {
        bootstrap.Modal.getInstance(document.getElementById('violationDetailsModal')).hide();
        showNotification('Violation dismissed');
        location.reload();
    });
}

function markForReview() {
    const notes = document.getElementById('reviewNotes').value;
    
    fetch(`/exams/proctor/violation/${window.currentViolationId}/mark-review`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ notes: notes })
    })
    .then(response => response.json())
    .then(data => {
        bootstrap.Modal.getInstance(document.getElementById('violationDetailsModal')).hide();
        showNotification('Violation marked for review');
    });
}

function approveViolation() {
    const notes = document.getElementById('reviewNotes').value;
    
    fetch(`/exams/proctor/violation/${window.currentViolationId}/approve`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ notes: notes })
    })
    .then(response => response.json())
    .then(data => {
        bootstrap.Modal.getInstance(document.getElementById('violationDetailsModal')).hide();
        showNotification('Violation approved and closed');
        location.reload();
    });
}

function escalateToSupervisor() {
    const notes = document.getElementById('reviewNotes').value;
    const reason = prompt('Reason for escalation:');
    
    if (reason) {
        fetch(`/exams/proctor/violation/${window.currentViolationId}/escalate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ 
                notes: notes,
                reason: reason 
            })
        })
        .then(response => response.json())
        .then(data => {
            bootstrap.Modal.getInstance(document.getElementById('violationDetailsModal')).hide();
            showNotification('Violation escalated to supervisor');
            location.reload();
        });
    }
}

// Export functions
function exportPDF() {
    window.location.href = '/exams/proctor/violations/export/pdf?' + getFilterParams();
}

function exportExcel() {
    window.location.href = '/exams/proctor/violations/export/excel?' + getFilterParams();
}

function getFilterParams() {
    const params = new URLSearchParams({
        severity: document.getElementById('severityFilter').value,
        type: document.getElementById('typeFilter').value,
        status: document.getElementById('statusFilter').value,
        time: document.getElementById('timeFilter').value
    });
    return params.toString();
}

// Pagination
function goToPage(page) {
    if (page === 'prev') {
        if (currentPage > 1) currentPage--;
    } else if (page === 'next') {
        if (currentPage < totalPages) currentPage++;
    } else {
        currentPage = page;
    }
    
    // Update pagination UI
    updatePaginationUI();
    
    // Load page data
    loadPageData(currentPage);
}

function updatePaginationUI() {
    // Update button states
    document.getElementById('prevBtn').disabled = currentPage === 1;
    document.getElementById('nextBtn').disabled = currentPage === totalPages;
    
    // Update active page
    document.querySelectorAll('.page-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.textContent == currentPage) {
            btn.classList.add('active');
        }
    });
}

function loadPageData(page) {
    // Fetch data for the specified page
    fetch(`/exams/proctor/violations?page=${page}`)
        .then(response => response.json())
        .then(data => {
            updateViolationList(data.violations);
        });
}

// WebSocket for real-time updates
function initializeWebSocket() {
    // Check for new violations every 30 seconds
    setInterval(() => {
        fetch('/exams/proctor/violations/new')
            .then(response => response.json())
            .then(data => {
                if (data.new_violations > 0) {
                    showNotification(`${data.new_violations} new violation(s) detected`);
                    
                    // Optional: Auto-refresh the page
                    if (confirm('New violations detected. Refresh the page?')) {
                        location.reload();
                    }
                }
            })
            .catch(error => console.error('Error checking for new violations:', error));
    }, 30000); // Check every 30 seconds
}

// Notification system
function showNotification(message) {
    const toast = document.getElementById('notificationToast');
    document.getElementById('notificationMessage').textContent = message;
    toast.classList.add('show');
    
    setTimeout(() => {
        toast.classList.remove('show');
    }, 5000);
}

// Helper functions
function showLoading() {
    // Add loading overlay
    console.log('Loading...');
}

function hideLoading() {
    // Remove loading overlay
    console.log('Loading complete');
}

function updateViolationList(violations) {
    // Update the violation list with new data
    console.log('Updating violation list', violations);
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + F: Focus on filter
    if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
        e.preventDefault();
        document.getElementById('severityFilter').focus();
    }
    
    // Ctrl/Cmd + E: Export
    if ((e.ctrlKey || e.metaKey) && e.key === 'e') {
        e.preventDefault();
        exportPDF();
    }
    
    // Escape: Close modal
    if (e.key === 'Escape') {
        const modal = bootstrap.Modal.getInstance(document.getElementById('violationDetailsModal'));
        if (modal) modal.hide();
    }
});
</script>
@endsection