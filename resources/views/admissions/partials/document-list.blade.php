{{-- Document List Component --}}
{{-- Path: resources/views/admissions/partials/document-list.blade.php --}}

@php
    $documentTypes = [
        'transcript' => ['icon' => 'fas fa-file-alt', 'color' => 'primary', 'label' => 'Transcript'],
        'high_school_transcript' => ['icon' => 'fas fa-school', 'color' => 'info', 'label' => 'High School Transcript'],
        'university_transcript' => ['icon' => 'fas fa-university', 'color' => 'info', 'label' => 'University Transcript'],
        'diploma' => ['icon' => 'fas fa-certificate', 'color' => 'success', 'label' => 'Diploma'],
        'degree_certificate' => ['icon' => 'fas fa-graduation-cap', 'color' => 'success', 'label' => 'Degree Certificate'],
        'test_scores' => ['icon' => 'fas fa-clipboard-check', 'color' => 'warning', 'label' => 'Test Scores'],
        'recommendation_letter' => ['icon' => 'fas fa-envelope-open-text', 'color' => 'secondary', 'label' => 'Recommendation Letter'],
        'personal_statement' => ['icon' => 'fas fa-pen-fancy', 'color' => 'purple', 'label' => 'Personal Statement'],
        'essay' => ['icon' => 'fas fa-file-word', 'color' => 'purple', 'label' => 'Essay'],
        'resume' => ['icon' => 'fas fa-id-card', 'color' => 'dark', 'label' => 'Resume/CV'],
        'portfolio' => ['icon' => 'fas fa-briefcase', 'color' => 'indigo', 'label' => 'Portfolio'],
        'financial_statement' => ['icon' => 'fas fa-dollar-sign', 'color' => 'success', 'label' => 'Financial Statement'],
        'bank_statement' => ['icon' => 'fas fa-piggy-bank', 'color' => 'success', 'label' => 'Bank Statement'],
        'sponsor_letter' => ['icon' => 'fas fa-handshake', 'color' => 'primary', 'label' => 'Sponsor Letter'],
        'passport' => ['icon' => 'fas fa-passport', 'color' => 'danger', 'label' => 'Passport'],
        'national_id' => ['icon' => 'fas fa-id-card', 'color' => 'danger', 'label' => 'National ID'],
        'birth_certificate' => ['icon' => 'fas fa-baby', 'color' => 'info', 'label' => 'Birth Certificate'],
        'medical_certificate' => ['icon' => 'fas fa-heartbeat', 'color' => 'danger', 'label' => 'Medical Certificate'],
        'english_proficiency' => ['icon' => 'fas fa-language', 'color' => 'primary', 'label' => 'English Proficiency'],
        'other' => ['icon' => 'fas fa-file', 'color' => 'secondary', 'label' => 'Other Document'],
    ];
    
    $statusClasses = [
        'uploaded' => 'badge-secondary',
        'pending_verification' => 'badge-warning',
        'verified' => 'badge-success',
        'rejected' => 'badge-danger',
        'expired' => 'badge-dark',
    ];
    
    $statusLabels = [
        'uploaded' => 'Uploaded',
        'pending_verification' => 'Under Review',
        'verified' => 'Verified',
        'rejected' => 'Rejected',
        'expired' => 'Expired',
    ];
@endphp

<div class="document-list-container">
    {{-- Header --}}
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-folder-open text-primary me-2"></i>
                    Application Documents
                </h5>
                @if($showUploadButton ?? true)
                    <button type="button" 
                            class="btn btn-primary btn-sm"
                            data-bs-toggle="modal" 
                            data-bs-target="#uploadDocumentModal">
                        <i class="fas fa-cloud-upload-alt me-2"></i>Upload Document
                    </button>
                @endif
            </div>
        </div>
        
        <div class="card-body">
            {{-- Statistics --}}
            @if($showStats ?? true)
                <div class="row mb-4">
                    <div class="col-md-3 col-6">
                        <div class="text-center">
                            <div class="h3 mb-0 text-primary">{{ $documents->count() }}</div>
                            <small class="text-muted">Total Documents</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="text-center">
                            <div class="h3 mb-0 text-success">{{ $documents->where('is_verified', true)->count() }}</div>
                            <small class="text-muted">Verified</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="text-center">
                            <div class="h3 mb-0 text-warning">{{ $documents->where('status', 'pending_verification')->count() }}</div>
                            <small class="text-muted">Pending Review</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="text-center">
                            <div class="h3 mb-0 text-danger">{{ $documents->where('status', 'rejected')->count() }}</div>
                            <small class="text-muted">Rejected</small>
                        </div>
                    </div>
                </div>
            @endif
            
            {{-- Document List --}}
            @if($documents->isEmpty())
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    No documents have been uploaded yet. Please upload the required documents to proceed with your application.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="40">Type</th>
                                <th>Document Name</th>
                                <th>Upload Date</th>
                                <th>Size</th>
                                <th>Status</th>
                                <th>Verified By</th>
                                <th width="120" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $document)
                                @php
                                    $typeInfo = $documentTypes[$document->document_type] ?? $documentTypes['other'];
                                @endphp
                                <tr>
                                    <td class="text-center">
                                        <i class="{{ $typeInfo['icon'] }} text-{{ $typeInfo['color'] }}" 
                                           data-bs-toggle="tooltip" 
                                           title="{{ $typeInfo['label'] }}"></i>
                                    </td>
                                    <td>
                                        <div class="fw-medium">{{ $document->document_name }}</div>
                                        <small class="text-muted">{{ $document->original_filename }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $document->created_at->format('M d, Y') }}</small>
                                        <br>
                                        <small class="text-muted">{{ $document->created_at->format('h:i A') }}</small>
                                    </td>
                                    <td>
                                        <small>{{ formatFileSize($document->file_size) }}</small>
                                    </td>
                                    <td>
                                        <span class="badge {{ $statusClasses[$document->status] ?? 'badge-secondary' }}">
                                            {{ $statusLabels[$document->status] ?? 'Unknown' }}
                                        </span>
                                        @if($document->status === 'rejected' && $document->rejection_reason)
                                            <i class="fas fa-exclamation-circle text-danger ms-1" 
                                               data-bs-toggle="tooltip" 
                                               title="{{ $document->rejection_reason }}"></i>
                                        @endif
                                    </td>
                                    <td>
                                        @if($document->verified_by)
                                            <small>{{ $document->verifier->name ?? 'System' }}</small>
                                            <br>
                                            <small class="text-muted">{{ $document->verified_at?->format('M d, Y') }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            {{-- View/Download Button --}}
                                            <button type="button" 
                                                    class="btn btn-outline-primary"
                                                    onclick="viewDocument({{ $document->id }})"
                                                    data-bs-toggle="tooltip" 
                                                    title="View Document">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            
                                            {{-- Download Button --}}
                                            <a href="{{ route('admissions.document.download', $document->id) }}" 
                                               class="btn btn-outline-success"
                                               data-bs-toggle="tooltip" 
                                               title="Download">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            
                                            {{-- Delete Button (only if not verified) --}}
                                            @if(!$document->is_verified && ($canDelete ?? false))
                                                <button type="button" 
                                                        class="btn btn-outline-danger"
                                                        onclick="deleteDocument({{ $document->id }})"
                                                        data-bs-toggle="tooltip" 
                                                        title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                            
                                            {{-- Admin Actions --}}
                                            @if($isAdmin ?? false)
                                                @if(!$document->is_verified)
                                                    <button type="button" 
                                                            class="btn btn-outline-success"
                                                            onclick="verifyDocument({{ $document->id }})"
                                                            data-bs-toggle="tooltip" 
                                                            title="Verify">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    
                                                    <button type="button" 
                                                            class="btn btn-outline-warning"
                                                            onclick="rejectDocument({{ $document->id }})"
                                                            data-bs-toggle="tooltip" 
                                                            title="Reject">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                {{-- Pagination --}}
                @if(method_exists($documents, 'links'))
                    <div class="d-flex justify-content-center mt-3">
                        {{ $documents->links() }}
                    </div>
                @endif
            @endif
            
            {{-- Required Documents Checklist --}}
            @if($showChecklist ?? false)
                <div class="mt-4">
                    <h6 class="mb-3">Required Documents Checklist</h6>
                    <div class="list-group">
                        @foreach($requiredDocuments ?? [] as $type => $label)
                            @php
                                $uploaded = $documents->where('document_type', $type)->first();
                                $typeInfo = $documentTypes[$type] ?? $documentTypes['other'];
                            @endphp
                            <div class="list-group-item">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        @if($uploaded && $uploaded->is_verified)
                                            <i class="fas fa-check-circle text-success fa-lg"></i>
                                        @elseif($uploaded)
                                            <i class="fas fa-clock text-warning fa-lg"></i>
                                        @else
                                            <i class="far fa-circle text-muted fa-lg"></i>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-medium">
                                            <i class="{{ $typeInfo['icon'] }} text-{{ $typeInfo['color'] }} me-2"></i>
                                            {{ $label }}
                                        </div>
                                        @if($uploaded)
                                            <small class="text-muted">
                                                Uploaded: {{ $uploaded->created_at->format('M d, Y') }}
                                                @if($uploaded->is_verified)
                                                    <span class="text-success ms-2">✓ Verified</span>
                                                @endif
                                            </small>
                                        @else
                                            <small class="text-danger">Not yet uploaded</small>
                                        @endif
                                    </div>
                                    @if(!$uploaded)
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-primary"
                                                onclick="uploadSpecificDocument('{{ $type }}')">
                                            <i class="fas fa-upload"></i> Upload
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Helper function to format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }
    
    function viewDocument(documentId) {
        window.open('{{ url("admissions/document") }}/' + documentId + '/view', '_blank');
    }
    
    function deleteDocument(documentId) {
        if (confirm('Are you sure you want to delete this document?')) {
            $.ajax({
                url: '{{ url("admissions/document") }}/' + documentId,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    toastr.success('Document deleted successfully');
                    location.reload();
                },
                error: function() {
                    toastr.error('Failed to delete document');
                }
            });
        }
    }
    
    @if($isAdmin ?? false)
    function verifyDocument(documentId) {
        // Admin verification logic
        $.ajax({
            url: '{{ url("admin/admissions/document") }}/' + documentId + '/verify',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                toastr.success('Document verified successfully');
                location.reload();
            }
        });
    }
    
    function rejectDocument(documentId) {
        const reason = prompt('Please provide a reason for rejection:');
        if (reason) {
            $.ajax({
                url: '{{ url("admin/admissions/document") }}/' + documentId + '/reject',
                method: 'POST',
                data: { reason: reason },
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    toastr.warning('Document rejected');
                    location.reload();
                }
            });
        }
    }
    @endif
    
    function uploadSpecificDocument(type) {
        $('#documentTypeSelect').val(type);
        $('#uploadDocumentModal').modal('show');
    }
    
    // Initialize tooltips
    $(document).ready(function() {
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>
@endpush

@php
    function formatFileSize($bytes) {
        if ($bytes == 0) return '0 Bytes';
        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));
        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }
@endphp