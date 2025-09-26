@extends('layouts.app')

@section('title', 'Candidate Verification - Proctor')

@section('styles')
<style>
    .verification-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .verification-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 10px;
        margin-bottom: 30px;
    }
    
    .verification-grid {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 30px;
        margin-bottom: 30px;
    }
    
    @media (max-width: 992px) {
        .verification-grid {
            grid-template-columns: 1fr;
        }
    }
    
    .verification-panel {
        background: white;
        border-radius: 10px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .candidate-photo-section {
        text-align: center;
    }
    
    .candidate-photo {
        width: 200px;
        height: 200px;
        border-radius: 10px;
        object-fit: cover;
        margin: 0 auto 20px;
        border: 3px solid #e9ecef;
    }
    
    .photo-placeholder {
        width: 200px;
        height: 200px;
        background: #f8f9fa;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        border: 2px dashed #dee2e6;
    }
    
    .verification-status {
        display: inline-block;
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 500;
        margin-top: 10px;
    }
    
    .status-verified {
        background: #d4edda;
        color: #155724;
    }
    
    .status-pending {
        background: #fff3cd;
        color: #856404;
    }
    
    .status-failed {
        background: #f8d7da;
        color: #721c24;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-top: 20px;
    }
    
    .info-item {
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 4px solid #667eea;
    }
    
    .info-label {
        font-size: 0.85rem;
        color: #6c757d;
        margin-bottom: 5px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .info-value {
        font-size: 1.1rem;
        font-weight: 600;
        color: #2c3e50;
    }
    
    .document-list {
        margin-top: 20px;
    }
    
    .document-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px;
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 10px;
        transition: all 0.3s;
    }
    
    .document-item:hover {
        background: #e9ecef;
    }
    
    .document-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .document-icon {
        width: 40px;
        height: 40px;
        background: #667eea;
        color: white;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .document-status {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.85rem;
    }
    
    .status-valid {
        background: #d4edda;
        color: #155724;
    }
    
    .status-invalid {
        background: #f8d7da;
        color: #721c24;
    }
    
    .status-checking {
        background: #fff3cd;
        color: #856404;
    }
    
    .verification-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-top: 30px;
    }
    
    .action-btn {
        padding: 12px 20px;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
        text-align: center;
    }
    
    .btn-verify {
        background: #28a745;
        color: white;
    }
    
    .btn-verify:hover {
        background: #218838;
        transform: translateY(-2px);
    }
    
    .btn-reject {
        background: #dc3545;
        color: white;
    }
    
    .btn-reject:hover {
        background: #c82333;
        transform: translateY(-2px);
    }
    
    .btn-capture {
        background: #17a2b8;
        color: white;
    }
    
    .btn-capture:hover {
        background: #138496;
        transform: translateY(-2px);
    }
    
    .btn-secondary {
        background: #6c757d;
        color: white;
    }
    
    .btn-secondary:hover {
        background: #5a6268;
        transform: translateY(-2px);
    }
    
    .webcam-section {
        background: #2c3e50;
        border-radius: 10px;
        padding: 20px;
        margin-top: 20px;
        position: relative;
    }
    
    .webcam-view {
        width: 100%;
        height: 300px;
        background: #1a1a1a;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        position: relative;
    }
    
    .webcam-controls {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-top: 15px;
    }
    
    .webcam-btn {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 10px 20px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .webcam-btn:hover {
        background: rgba(255, 255, 255, 0.3);
    }
    
    .comparison-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-top: 20px;
    }
    
    .comparison-item {
        text-align: center;
    }
    
    .comparison-image {
        width: 100%;
        max-width: 200px;
        height: 200px;
        object-fit: cover;
        border-radius: 10px;
        margin: 0 auto 10px;
        border: 2px solid #dee2e6;
    }
    
    .match-score {
        font-size: 2rem;
        font-weight: bold;
        margin: 20px 0;
    }
    
    .score-high {
        color: #28a745;
    }
    
    .score-medium {
        color: #ffc107;
    }
    
    .score-low {
        color: #dc3545;
    }
    
    .notes-section {
        margin-top: 20px;
    }
    
    .notes-textarea {
        width: 100%;
        min-height: 100px;
        padding: 12px;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        font-size: 0.95rem;
        resize: vertical;
    }
    
    .timestamp {
        font-size: 0.85rem;
        color: #6c757d;
        margin-top: 10px;
    }
    
    .alert-box {
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .alert-warning {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
    }
    
    .alert-danger {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
</style>
@endsection

@section('content')
<div class="verification-container">
    <!-- Header -->
    <div class="verification-header">
        <h1 class="mb-3">Candidate Verification</h1>
        <p class="mb-0">
            Session: <strong>{{ $session->session_code ?? 'SESS-2025-001-A' }}</strong> | 
            Center: <strong>{{ $session->center->center_name ?? 'Main Campus' }}</strong> | 
            Date: <strong>{{ now()->format('F d, Y') }}</strong>
        </p>
    </div>
    
    <!-- Alert if any issues -->
    @if(isset($alert))
    <div class="alert-box alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        <span>{{ $alert ?? 'Some candidates require additional verification. Please review carefully.' }}</span>
    </div>
    @endif
    
    <!-- Main Verification Grid -->
    <div class="verification-grid">
        <!-- Left Panel - Candidate Photo & Basic Info -->
        <div class="verification-panel">
            <h3 class="mb-4">Candidate Information</h3>
            
            <div class="candidate-photo-section">
                @if(isset($candidate->photo))
                <img src="{{ $candidate->photo }}" alt="Candidate Photo" class="candidate-photo">
                @else
                <div class="photo-placeholder">
                    <i class="fas fa-user fa-4x text-muted"></i>
                </div>
                @endif
                
                <h4>{{ $candidate->name ?? 'John Doe' }}</h4>
                <p class="text-muted">{{ $candidate->registration_number ?? 'REG-2025-00001' }}</p>
                
                <div class="verification-status status-{{ $candidate->verification_status ?? 'pending' }}">
                    <i class="fas fa-{{ $candidate->verification_status == 'verified' ? 'check-circle' : 'clock' }}"></i>
                    {{ ucfirst($candidate->verification_status ?? 'Pending Verification') }}
                </div>
            </div>
            
            <div class="info-grid mt-4">
                <div class="info-item">
                    <div class="info-label">Date of Birth</div>
                    <div class="info-value">{{ $candidate->dob ?? 'Jan 15, 2005' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Exam Type</div>
                    <div class="info-value">{{ $candidate->exam_type ?? 'Entrance' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Seat Number</div>
                    <div class="info-value">{{ $candidate->seat_number ?? 'A-001' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Hall Ticket</div>
                    <div class="info-value">{{ $candidate->hall_ticket ?? 'HT-2025-001' }}</div>
                </div>
            </div>
        </div>
        
        <!-- Right Panel - Verification Details -->
        <div class="verification-panel">
            <h3 class="mb-4">Verification Process</h3>
            
            <!-- Document Verification -->
            <h5 class="mb-3">
                <i class="fas fa-file-alt"></i> Document Verification
            </h5>
            
            <div class="document-list">
                <div class="document-item">
                    <div class="document-info">
                        <div class="document-icon">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <div>
                            <strong>Photo ID</strong>
                            <div class="text-muted small">National ID / Passport</div>
                        </div>
                    </div>
                    <span class="document-status status-valid">
                        <i class="fas fa-check"></i> Valid
                    </span>
                </div>
                
                <div class="document-item">
                    <div class="document-info">
                        <div class="document-icon">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                        <div>
                            <strong>Hall Ticket</strong>
                            <div class="text-muted small">Admission Card</div>
                        </div>
                    </div>
                    <span class="document-status status-valid">
                        <i class="fas fa-check"></i> Valid
                    </span>
                </div>
                
                <div class="document-item">
                    <div class="document-info">
                        <div class="document-icon">
                            <i class="fas fa-camera"></i>
                        </div>
                        <div>
                            <strong>Photo Match</strong>
                            <div class="text-muted small">Biometric Verification</div>
                        </div>
                    </div>
                    <span class="document-status status-checking">
                        <i class="fas fa-spinner fa-spin"></i> Checking
                    </span>
                </div>
            </div>
            
            <!-- Live Photo Capture Section -->
            <div class="webcam-section">
                <h5 class="text-white mb-3">
                    <i class="fas fa-camera"></i> Live Photo Capture
                </h5>
                
                <div class="webcam-view">
                    <div id="webcamPlaceholder">
                        <i class="fas fa-video fa-3x"></i>
                        <p class="mt-3">Click "Start Camera" to begin verification</p>
                    </div>
                    <video id="webcam" style="width: 100%; height: 100%; display: none;" autoplay></video>
                </div>
                
                <div class="webcam-controls">
                    <button class="webcam-btn" onclick="startCamera()">
                        <i class="fas fa-play"></i> Start Camera
                    </button>
                    <button class="webcam-btn" onclick="capturePhoto()">
                        <i class="fas fa-camera"></i> Capture Photo
                    </button>
                    <button class="webcam-btn" onclick="stopCamera()">
                        <i class="fas fa-stop"></i> Stop Camera
                    </button>
                </div>
            </div>
            
            <!-- Photo Comparison -->
            <div class="comparison-grid">
                <div class="comparison-item">
                    <h6>ID Photo</h6>
                    <img src="/images/id-photo-placeholder.jpg" alt="ID Photo" class="comparison-image">
                </div>
                <div class="comparison-item">
                    <h6>Live Capture</h6>
                    <img src="/images/live-photo-placeholder.jpg" alt="Live Photo" class="comparison-image" id="capturedPhoto">
                </div>
            </div>
            
            <div class="text-center">
                <div class="match-score score-high">
                    <i class="fas fa-check-circle"></i> 92% Match
                </div>
                <p class="text-muted">Facial recognition confidence score</p>
            </div>
            
            <!-- Verification Notes -->
            <div class="notes-section">
                <h5 class="mb-3">
                    <i class="fas fa-sticky-note"></i> Verification Notes
                </h5>
                <textarea class="notes-textarea" placeholder="Add any notes or observations about the verification process..."></textarea>
                <div class="timestamp">
                    Last updated: {{ now()->format('g:i A') }}
                </div>
            </div>
        </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="verification-panel">
        <h4 class="mb-3">Verification Actions</h4>
        
        <div class="verification-actions">
            <button class="action-btn btn-verify" onclick="verifyCandidate()">
                <i class="fas fa-check-circle"></i> Verify & Allow Entry
            </button>
            
            <button class="action-btn btn-reject" onclick="rejectCandidate()">
                <i class="fas fa-times-circle"></i> Reject Entry
            </button>
            
            <button class="action-btn btn-capture" onclick="requestAdditionalDocs()">
                <i class="fas fa-file-upload"></i> Request Additional Documents
            </button>
            
            <button class="action-btn btn-secondary" onclick="markForReview()">
                <i class="fas fa-flag"></i> Mark for Review
            </button>
            
            <button class="action-btn btn-secondary" onclick="callSupervisor()">
                <i class="fas fa-phone"></i> Call Supervisor
            </button>
            
            <button class="action-btn btn-secondary" onclick="nextCandidate()">
                <i class="fas fa-arrow-right"></i> Next Candidate
            </button>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Candidate Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    This action will prevent the candidate from taking the exam.
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Reason for Rejection</label>
                    <select class="form-select" id="rejectionReason">
                        <option value="">Select a reason...</option>
                        <option value="invalid_id">Invalid ID Document</option>
                        <option value="photo_mismatch">Photo Does Not Match</option>
                        <option value="missing_documents">Missing Required Documents</option>
                        <option value="late_arrival">Arrived After Cut-off Time</option>
                        <option value="suspicious_behavior">Suspicious Behavior</option>
                        <option value="other">Other (Specify)</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Additional Notes</label>
                    <textarea class="form-control" id="rejectionNotes" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmRejection()">Confirm Rejection</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let stream = null;
let candidateId = {{ $candidate->id ?? 1 }};

function startCamera() {
    navigator.mediaDevices.getUserMedia({ video: true })
        .then(function(mediaStream) {
            stream = mediaStream;
            const video = document.getElementById('webcam');
            video.srcObject = stream;
            video.style.display = 'block';
            document.getElementById('webcamPlaceholder').style.display = 'none';
        })
        .catch(function(err) {
            console.error('Error accessing camera:', err);
            alert('Unable to access camera. Please check permissions.');
        });
}

function stopCamera() {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        const video = document.getElementById('webcam');
        video.style.display = 'none';
        document.getElementById('webcamPlaceholder').style.display = 'flex';
    }
}

function capturePhoto() {
    const video = document.getElementById('webcam');
    const canvas = document.createElement('canvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0);
    
    // Convert to base64 and display
    const dataURL = canvas.toDataURL('image/jpeg');
    document.getElementById('capturedPhoto').src = dataURL;
    
    // Send to server for verification
    verifyPhoto(dataURL);
}

function verifyPhoto(photoData) {
    fetch('/exams/proctor/verify-photo', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            candidate_id: candidateId,
            photo: photoData
        })
    })
    .then(response => response.json())
    .then(data => {
        updateMatchScore(data.match_percentage);
    });
}

function updateMatchScore(percentage) {
    const scoreElement = document.querySelector('.match-score');
    scoreElement.innerHTML = `<i class="fas fa-check-circle"></i> ${percentage}% Match`;
    
    // Update color based on percentage
    scoreElement.className = 'match-score';
    if (percentage >= 80) {
        scoreElement.classList.add('score-high');
    } else if (percentage >= 60) {
        scoreElement.classList.add('score-medium');
    } else {
        scoreElement.classList.add('score-low');
    }
}

function verifyCandidate() {
    if (confirm('Verify this candidate and allow exam entry?')) {
        fetch(`/exams/proctor/candidate/${candidateId}/verify`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                verification_notes: document.querySelector('.notes-textarea').value
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Candidate verified successfully!');
                nextCandidate();
            }
        });
    }
}

function rejectCandidate() {
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function confirmRejection() {
    const reason = document.getElementById('rejectionReason').value;
    const notes = document.getElementById('rejectionNotes').value;
    
    if (!reason) {
        alert('Please select a reason for rejection.');
        return;
    }
    
    fetch(`/exams/proctor/candidate/${candidateId}/reject`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            reason: reason,
            notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Candidate entry rejected.');
            bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
            nextCandidate();
        }
    });
}

function requestAdditionalDocs() {
    const docType = prompt('What additional document is required?');
    if (docType) {
        fetch(`/exams/proctor/candidate/${candidateId}/request-docs`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                document_type: docType
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Document request sent to candidate.');
            }
        });
    }
}

function markForReview() {
    if (confirm('Mark this candidate for supervisor review?')) {
        fetch(`/exams/proctor/candidate/${candidateId}/mark-review`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Candidate marked for review.');
            }
        });
    }
}

function callSupervisor() {
    if (confirm('Request supervisor assistance for this verification?')) {
        fetch('/exams/proctor/call-supervisor', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                candidate_id: candidateId,
                location: '{{ $session->room_number ?? "Hall A" }}'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Supervisor has been notified and will arrive shortly.');
            }
        });
    }
}

function nextCandidate() {
    // Move to next candidate in queue
    window.location.href = '/exams/proctor/verification/next';
}

// Auto-save notes
let notesTimeout;
document.querySelector('.notes-textarea').addEventListener('input', function() {
    clearTimeout(notesTimeout);
    notesTimeout = setTimeout(() => {
        saveNotes(this.value);
    }, 1000);
});

function saveNotes(notes) {
    fetch(`/exams/proctor/candidate/${candidateId}/notes`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            notes: notes
        })
    });
}

// Cleanup on page leave
window.addEventListener('beforeunload', function() {
    stopCamera();
});
</script>
@endsection