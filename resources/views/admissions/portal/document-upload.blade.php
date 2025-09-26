{{-- resources/views/admissions/portal/document-upload.blade.php --}}
@extends('layouts.portal')

@section('title', 'Upload Documents - Application #' . $application->application_number)

@section('styles')
<style>
    .upload-zone {
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 30px;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
        background: #fafafa;
    }
    
    .upload-zone:hover {
        border-color: #007bff;
        background: #f0f8ff;
    }
    
    .upload-zone.dragover {
        border-color: #28a745;
        background: #e8f5e9;
    }
    
    .document-item {
        border-left: 4px solid transparent;
        transition: all 0.3s ease;
    }
    
    .document-item.verified {
        border-left-color: #28a745;
    }
    
    .document-item.pending {
        border-left-color: #ffc107;
    }
    
    .document-item.rejected {
        border-left-color: #dc3545;
    }
    
    .file-preview {
        max-width: 100px;
        max-height: 100px;
        object-fit: cover;
    }
    
    .progress-ring {
        width: 40px;
        height: 40px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="h3 font-weight-bold text-gray-800">
                        <i class="fas fa-cloud-upload-alt me-2"></i>Document Upload
                    </h2>
                    <p class="text-muted mb-0">
                        Application #{{ $application->application_number }} | 
                        Step 4 of 6
                    </p>
                </div>
                <div>
                    <a href="{{ route('admissions.form.show', $application->id) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Form
                    </a>
                    <a href="{{ route('admissions.portal.review', $application->id) }}" class="btn btn-primary">
                        Continue <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Progress Bar --}}
    @include('admissions.partials.application-progress', ['application' => $application])

    {{-- Alert Messages --}}
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

    <div class="row">
        {{-- Upload Section --}}
        <div class="col-lg-8">
            {{-- Required Documents --}}
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-file-alt me-2"></i>Required Documents
                    </h5>
                </div>
                <div class="card-body">
                    {{-- Upload Zone --}}
                    <div class="upload-zone" id="upload-zone">
                        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                        <h5>Drag & Drop Files Here</h5>
                        <p class="text-muted">or click to browse</p>
                        <button type="button" class="btn btn-primary" onclick="document.getElementById('file-input').click()">
                            <i class="fas fa-folder-open me-2"></i>Select Files
                        </button>
                        <input type="file" id="file-input" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" style="display: none;">
                        <p class="text-muted mt-3 small">
                            Accepted formats: PDF, JPG, PNG, DOC, DOCX | Max size: 10MB per file
                        </p>
                    </div>

                    {{-- Document Checklist --}}
                    <div class="mt-4">
                        <h6 class="mb-3">Documents Checklist:</h6>
                        <div class="row">
                            @foreach($requiredDocuments as $docType => $docInfo)
                            <div class="col-md-6 mb-3">
                                <div class="card document-item {{ $docInfo['status'] }}">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1">{{ $docInfo['name'] }}</h6>
                                                <small class="text-muted">{{ $docInfo['description'] }}</small>
                                            </div>
                                            <div>
                                                @if($docInfo['status'] == 'verified')
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check"></i> Verified
                                                    </span>
                                                @elseif($docInfo['status'] == 'uploaded')
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-clock"></i> Pending
                                                    </span>
                                                @elseif($docInfo['status'] == 'rejected')
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-times"></i> Rejected
                                                    </span>
                                                @else
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-primary upload-doc-btn"
                                                            data-doc-type="{{ $docType }}">
                                                        <i class="fas fa-upload"></i> Upload
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Uploaded Documents List --}}
                    @if($uploadedDocuments->count() > 0)
                    <div class="mt-4">
                        <h6 class="mb-3">Uploaded Documents:</h6>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Document</th>
                                        <th>Type</th>
                                        <th>Size</th>
                                        <th>Uploaded</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($uploadedDocuments as $document)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if(in_array($document->file_type, ['image/jpeg', 'image/png']))
                                                    <img src="{{ Storage::url($document->file_path) }}" 
                                                         class="file-preview me-2" 
                                                         alt="Preview">
                                                @else
                                                    <i class="fas fa-file-pdf fa-2x text-danger me-2"></i>
                                                @endif
                                                <div>
                                                    <div class="fw-bold">{{ $document->document_name }}</div>
                                                    <small class="text-muted">{{ $document->original_filename }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ ucwords(str_replace('_', ' ', $document->document_type)) }}</td>
                                        <td>{{ number_format($document->file_size / 1024, 2) }} KB</td>
                                        <td>{{ $document->created_at->format('M d, Y') }}</td>
                                        <td>
                                            @switch($document->status)
                                                @case('verified')
                                                    <span class="badge bg-success">Verified</span>
                                                    @break
                                                @case('pending_verification')
                                                    <span class="badge bg-warning">Pending</span>
                                                    @break
                                                @case('rejected')
                                                    <span class="badge bg-danger">Rejected</span>
                                                    @if($document->rejection_reason)
                                                        <i class="fas fa-info-circle ms-1" 
                                                           data-bs-toggle="tooltip" 
                                                           title="{{ $document->rejection_reason }}"></i>
                                                    @endif
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">Uploaded</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('admissions.document.preview', $document->id) }}" 
                                                   class="btn btn-outline-info" 
                                                   target="_blank"
                                                   title="Preview">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admissions.document.download', $document->id) }}" 
                                                   class="btn btn-outline-primary"
                                                   title="Download">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                @if($document->status != 'verified')
                                                <button type="button" 
                                                        class="btn btn-outline-danger delete-doc-btn"
                                                        data-doc-id="{{ $document->id }}"
                                                        title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Optional Documents --}}
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-paperclip me-2"></i>Optional Documents
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">You may upload additional documents to support your application:</p>
                    <ul class="text-muted">
                        <li>Additional recommendation letters</li>
                        <li>Certificates of achievement</li>
                        <li>Portfolio samples (for creative programs)</li>
                        <li>Research papers or publications</li>
                        <li>Proof of extracurricular activities</li>
                    </ul>
                    <button type="button" class="btn btn-outline-primary" id="upload-optional">
                        <i class="fas fa-plus me-2"></i>Add Optional Document
                    </button>
                </div>
            </div>
        </div>

        {{-- Right Sidebar --}}
        <div class="col-lg-4">
            {{-- Instructions --}}
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Instructions
                    </h6>
                </div>
                <div class="card-body">
                    <ol class="small">
                        <li class="mb-2">Ensure all documents are clear and legible</li>
                        <li class="mb-2">Official transcripts must be sealed or verified</li>
                        <li class="mb-2">Test scores should be from official sources</li>
                        <li class="mb-2">Each file must not exceed 10MB</li>
                        <li class="mb-2">Use PDF format when possible</li>
                        <li class="mb-2">Name files descriptively</li>
                    </ol>
                </div>
            </div>

            {{-- Upload Progress --}}
            <div class="card shadow mb-4" id="upload-progress-card" style="display: none;">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-spinner fa-spin me-2"></i>Upload Progress
                    </h6>
                </div>
                <div class="card-body">
                    <div id="upload-progress-list"></div>
                </div>
            </div>

            {{-- Help Section --}}
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-question-circle me-2"></i>Need Help?
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small mb-2">Having trouble uploading documents?</p>
                    <ul class="small text-muted">
                        <li>Check file format and size</li>
                        <li>Try a different browser</li>
                        <li>Clear browser cache</li>
                    </ul>
                    <hr>
                    <p class="small mb-2">Contact Admissions Office:</p>
                    <p class="small mb-1">
                        <i class="fas fa-phone me-1"></i> +231 77 000 0000
                    </p>
                    <p class="small mb-0">
                        <i class="fas fa-envelope me-1"></i> admissions@intellicampus.edu
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this document?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirm-delete">Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
    
    // File upload handling
    const uploadZone = document.getElementById('upload-zone');
    const fileInput = document.getElementById('file-input');
    
    // Drag and drop
    uploadZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadZone.classList.add('dragover');
    });
    
    uploadZone.addEventListener('dragleave', () => {
        uploadZone.classList.remove('dragover');
    });
    
    uploadZone.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadZone.classList.remove('dragover');
        handleFiles(e.dataTransfer.files);
    });
    
    // File input change
    fileInput.addEventListener('change', (e) => {
        handleFiles(e.target.files);
    });
    
    // Upload specific document type
    $('.upload-doc-btn').click(function() {
        const docType = $(this).data('doc-type');
        $('#file-input').attr('data-doc-type', docType);
        $('#file-input').click();
    });
    
    // Handle file upload
    function handleFiles(files) {
        const formData = new FormData();
        const progressCard = $('#upload-progress-card');
        const progressList = $('#upload-progress-list');
        
        // Show progress card
        progressCard.show();
        progressList.empty();
        
        Array.from(files).forEach((file, index) => {
            // Validate file
            if (!validateFile(file)) {
                return;
            }
            
            formData.append('documents[]', file);
            
            // Add progress item
            const progressItem = $(`
                <div class="upload-item mb-3" id="upload-${index}">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small">${file.name}</span>
                        <span class="badge bg-primary">0%</span>
                    </div>
                    <div class="progress mt-1">
                        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>
            `);
            progressList.append(progressItem);
        });
        
        // Add application ID and CSRF token
        formData.append('application_id', '{{ $application->id }}');
        formData.append('_token', '{{ csrf_token() }}');
        
        // Upload files
        $.ajax({
            url: '{{ route("admissions.document.upload") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                const xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        const percentComplete = Math.round((e.loaded / e.total) * 100);
                        $('.upload-item .progress-bar').css('width', percentComplete + '%');
                        $('.upload-item .badge').text(percentComplete + '%');
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                // Show success message
                toastr.success('Documents uploaded successfully!');
                
                // Reload page after delay
                setTimeout(() => {
                    location.reload();
                }, 1500);
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.message || 'Upload failed';
                toastr.error(error);
                progressCard.hide();
            }
        });
    }
    
    // Validate file
    function validateFile(file) {
        const maxSize = 10 * 1024 * 1024; // 10MB
        const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 
                            'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        
        if (file.size > maxSize) {
            toastr.error(`File ${file.name} exceeds 10MB limit`);
            return false;
        }
        
        if (!allowedTypes.includes(file.type)) {
            toastr.error(`File ${file.name} has invalid format`);
            return false;
        }
        
        return true;
    }
    
    // Delete document
    let deleteDocId = null;
    
    $('.delete-doc-btn').click(function() {
        deleteDocId = $(this).data('doc-id');
        $('#deleteModal').modal('show');
    });
    
    $('#confirm-delete').click(function() {
        if (deleteDocId) {
            $.ajax({
                url: `/admissions/document/${deleteDocId}`,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    toastr.success('Document deleted successfully');
                    $('#deleteModal').modal('hide');
                    location.reload();
                },
                error: function(xhr) {
                    toastr.error('Failed to delete document');
                }
            });
        }
    });
    
    // Upload optional document
    $('#upload-optional').click(function() {
        $('#file-input').attr('data-doc-type', 'optional');
        $('#file-input').click();
    });
});
</script>
@endsection