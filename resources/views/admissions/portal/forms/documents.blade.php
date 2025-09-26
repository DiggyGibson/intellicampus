{{-- resources/views/admissions/portal/forms/documents.blade.php --}}
<div class="documents-section">
    <h4 class="mb-4"><i class="fas fa-folder-open me-2"></i>Required Documents</h4>
    
    <div class="alert alert-warning mb-4">
        <i class="fas fa-exclamation-triangle me-2"></i>
        All documents must be in PDF, JPG, or PNG format. Maximum file size: 10MB per document.
    </div>
    
    <div class="row">
        @foreach($requirements['required_documents'] ?? [] as $docType)
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            {{ ucwords(str_replace('_', ' ', $docType)) }}
                            <span class="text-danger">*</span>
                        </h6>
                    </div>
                    <div class="card-body">
                        @php
                            $existingDoc = $application->documents()
                                ->wherePivot('purpose', $docType)
                                ->first();
                        @endphp
                        
                        @if($existingDoc)
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>Uploaded
                                <br>
                                <small>{{ $existingDoc->original_filename }}</small>
                                <br>
                                <button type="button" class="btn btn-sm btn-danger mt-2" 
                                        onclick="removeDocument('{{ $docType }}')">
                                    Remove
                                </button>
                            </div>
                        @else
                            <input type="file" 
                                   class="form-control" 
                                   id="doc_{{ $docType }}"
                                   name="doc_{{ $docType }}"
                                   accept=".pdf,.jpg,.jpeg,.png"
                                   required>
                            <small class="text-muted">
                                @switch($docType)
                                    @case('transcript')
                                        Official or unofficial transcript
                                        @break
                                    @case('recommendation_letter')
                                        Letter from teacher or counselor
                                        @break
                                    @case('passport')
                                        Photo page of passport
                                        @break
                                    @case('financial_statement')
                                        Bank statement or sponsor letter
                                        @break
                                @endswitch
                            </small>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    
    <div class="card mt-4">
        <div class="card-header bg-light">
            <h6 class="mb-0">Additional Documents (Optional)</h6>
        </div>
        <div class="card-body">
            <p class="text-muted">Upload any additional supporting documents</p>
            <input type="file" class="form-control" name="additional_documents[]" multiple accept=".pdf,.jpg,.jpeg,.png">
        </div>
    </div>
</div>