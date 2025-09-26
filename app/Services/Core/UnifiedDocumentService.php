<?php

namespace App\Services\Core;

use App\Models\Document;
use App\Models\DocumentRelationship;
use App\Models\DocumentVersion;
use App\Models\DocumentAccessLog;
use App\Models\DocumentProcessingQueue;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Exception;

/**
 * UnifiedDocumentService
 * 
 * Central service for all document operations across IntelliCampus.
 * Handles upload, storage, deduplication, verification, and access control.
 * 
 * @author IntelliCampus Development Team
 * @version 1.0.1 - Fixed path handling issue
 */
class UnifiedDocumentService
{
    /**
     * Storage configuration
     */
    private const STORAGE_DISK = 'local';
    private const STORAGE_PATH_PATTERN = 'documents/{year}/{month}/{day}/{hash}';
    private const THUMBNAIL_PATH = 'thumbnails/{year}/{month}/{hash}_thumb.jpg';
    
    /**
     * File constraints
     */
    private const MAX_FILE_SIZE = 10485760; // 10MB in bytes
    private const ALLOWED_MIME_TYPES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/gif',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/plain',
        'text/csv'
    ];

    /**
     * Store a document with deduplication and processing
     * 
     * @param UploadedFile $file The uploaded file
     * @param string $context Context: 'admission', 'student', 'course', 'transcript', 'faculty', 'finance'
     * @param int $ownerId ID of the owning entity
     * @param array $options Additional options and metadata
     * @return Document
     * @throws Exception
     */
    public function store(UploadedFile $file, string $context, int $ownerId, array $options = []): Document
    {
        // Validate file
        $this->validateFile($file);
        
        DB::beginTransaction();
        
        try {
            // Calculate file hash for deduplication
            $hash = $this->calculateFileHash($file);
            
            // Check if file already exists (deduplication)
            $existingDocument = $this->findByHash($hash);
            
            if ($existingDocument && $this->canReuseDocument($existingDocument, $context)) {
                // File already exists - create relationship instead of duplicating
                Log::info("Document deduplicated", ['hash' => $hash, 'document_id' => $existingDocument->id]);
                
                // Create relationship to existing document
                $this->createRelationship($existingDocument, $context, $ownerId, $options);
                
                DB::commit();
                return $existingDocument;
            }
            
            // Generate the storage path
            $path = $this->generateStoragePath($file, $hash);
            
            // Store the physical file
            $this->storePhysicalFile($file, $path);
            
            // Create new document record with path
            $document = $this->createNewDocument($file, $hash, $path, $context, $options);
            
            // Create ownership relationship
            $this->createRelationship($document, $context, $ownerId, $options);
            
            // Queue for processing (virus scan, OCR, thumbnails, etc.)
            $this->queueForProcessing($document, $options);
            
            // Log the upload
            $this->logAccess($document, 'upload', [
                'context' => $context,
                'owner_id' => $ownerId
            ]);
            
            DB::commit();
            
            Log::info("Document stored successfully", [
                'document_id' => $document->id,
                'hash' => $hash,
                'context' => $context,
                'path' => $path
            ]);
            
            return $document;
            
        } catch (Exception $e) {
            DB::rollback();
            
            // Clean up any stored files if transaction failed
            if (isset($path)) {
                Storage::disk(self::STORAGE_DISK)->delete($path);
            }
            
            Log::error("Document storage failed", [
                'error' => $e->getMessage(),
                'context' => $context,
                'file' => $file->getClientOriginalName()
            ]);
            
            throw new Exception("Failed to store document: " . $e->getMessage());
        }
    }

    /**
     * Retrieve a document with access control
     * 
     * @param int $documentId
     * @param string $purpose Optional purpose for access log
     * @return Document
     * @throws Exception
     */
    public function retrieve(int $documentId, string $purpose = null): Document
    {
        $document = Document::findOrFail($documentId);
        
        // Check access permissions
        if (!$this->canAccessDocument($document)) {
            throw new Exception("Access denied to document");
        }
        
        // Log access
        $this->logAccess($document, 'view', ['purpose' => $purpose]);
        
        // Increment view count
        $document->increment('view_count');
        $document->update(['last_accessed_at' => now()]);
        
        return $document;
    }

    /**
     * Download a document
     * 
     * @param int $documentId
     * @return array Contains 'path', 'name', and 'mime_type'
     * @throws Exception
     */
    public function download(int $documentId): array
    {
        $document = Document::findOrFail($documentId);
        
        // Check access permissions
        if (!$this->canAccessDocument($document)) {
            throw new Exception("Access denied to document");
        }
        
        // Check if file exists
        if (!Storage::disk(self::STORAGE_DISK)->exists($document->path)) {
            throw new Exception("Document file not found");
        }
        
        // Log download
        $this->logAccess($document, 'download');
        
        // Increment download count
        $document->increment('download_count');
        $document->update(['last_accessed_at' => now()]);
        
        return [
            'path' => Storage::disk(self::STORAGE_DISK)->path($document->path),
            'name' => $document->original_name,
            'mime_type' => $document->mime_type
        ];
    }

    /**
     * Verify a document
     * 
     * @param int $documentId
     * @param bool $approved
     * @param string $notes
     * @return Document
     */
    public function verify(int $documentId, bool $approved, string $notes = null): Document
    {
        $document = Document::findOrFail($documentId);
        
        DB::beginTransaction();
        
        try {
            if ($approved) {
                $document->status = 'verified';
                $document->verification_status = 'verified';
                $document->verified_by = Auth::id();
                $document->verified_at = now();
                $document->verification_notes = $notes;
            } else {
                $document->status = 'rejected';
                $document->verification_status = 'rejected';
                $document->rejection_reason = $notes;
            }
            
            $document->save();
            
            // Log the verification
            $this->logAccess($document, $approved ? 'verify' : 'reject', [
                'notes' => $notes
            ]);
            
            // Update related entities if needed
            $this->updateRelatedEntities($document, $approved);
            
            DB::commit();
            
            return $document;
            
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Update document metadata
     * 
     * @param int $documentId
     * @param array $metadata
     * @return Document
     */
    public function updateMetadata(int $documentId, array $metadata): Document
    {
        $document = Document::findOrFail($documentId);
        
        // Check edit permissions
        if (!$this->canEditDocument($document)) {
            throw new Exception("Cannot edit this document");
        }
        
        // Merge with existing metadata
        $existingMetadata = $document->metadata ?? [];
        $document->metadata = array_merge($existingMetadata, $metadata);
        $document->updated_by = Auth::id();
        $document->save();
        
        // Log the update
        $this->logAccess($document, 'edit', ['metadata_updated' => array_keys($metadata)]);
        
        return $document;
    }

    /**
     * Create a new version of a document
     * 
     * @param int $documentId
     * @param UploadedFile $newFile
     * @param string $changeSummary
     * @return Document
     */
    public function createVersion(int $documentId, UploadedFile $newFile, string $changeSummary): Document
    {
        $document = Document::findOrFail($documentId);
        
        // Check edit permissions
        if (!$this->canEditDocument($document)) {
            throw new Exception("Cannot edit this document");
        }
        
        DB::beginTransaction();
        
        try {
            // Get current version number
            $currentVersion = DocumentVersion::where('document_id', $documentId)
                ->max('version_number') ?? 0;
            
            // Store old version in versions table
            DocumentVersion::create([
                'document_id' => $documentId,
                'version_number' => $currentVersion + 1,
                'hash' => $document->hash,
                'path' => $document->path,
                'size' => $document->size,
                'change_type' => 'replace',
                'change_summary' => $changeSummary,
                'metadata' => $document->metadata,
                'created_by' => Auth::id()
            ]);
            
            // Update document with new file
            $newHash = $this->calculateFileHash($newFile);
            $newPath = $this->generateStoragePath($newFile, $newHash);
            $this->storePhysicalFile($newFile, $newPath);
            
            $document->hash = $newHash;
            $document->path = $newPath;
            $document->size = $newFile->getSize();
            $document->mime_type = $newFile->getMimeType();
            $document->updated_by = Auth::id();
            $document->save();
            
            // Queue for reprocessing
            $this->queueForProcessing($document);
            
            DB::commit();
            
            return $document;
            
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Search documents
     * 
     * @param array $criteria
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function search(array $criteria)
    {
        $query = Document::query();
        
        // Apply search criteria
        if (isset($criteria['context'])) {
            $query->where('context', $criteria['context']);
        }
        
        if (isset($criteria['category'])) {
            $query->where('category', $criteria['category']);
        }
        
        if (isset($criteria['status'])) {
            $query->where('status', $criteria['status']);
        }
        
        if (isset($criteria['search'])) {
            $query->where(function($q) use ($criteria) {
                $q->where('original_name', 'LIKE', '%' . $criteria['search'] . '%')
                  ->orWhere('display_name', 'LIKE', '%' . $criteria['search'] . '%')
                  ->orWhereJsonContains('tags', $criteria['search']);
            });
        }
        
        if (isset($criteria['uploaded_by'])) {
            $query->where('uploaded_by', $criteria['uploaded_by']);
        }
        
        if (isset($criteria['date_from'])) {
            $query->where('created_at', '>=', $criteria['date_from']);
        }
        
        if (isset($criteria['date_to'])) {
            $query->where('created_at', '<=', $criteria['date_to']);
        }
        
        // Apply access control
        $query = $this->applyAccessControl($query);
        
        return $query->orderBy('created_at', 'desc')->paginate($criteria['per_page'] ?? 15);
    }

    /**
     * Get documents for a specific entity
     * 
     * @param string $ownerType
     * @param int $ownerId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDocumentsFor(string $ownerType, int $ownerId)
    {
        return Document::whereHas('relationships', function($query) use ($ownerType, $ownerId) {
            $query->where('owner_type', $ownerType)
                  ->where('owner_id', $ownerId);
        })->with('relationships')->get();
    }

    /**
     * Delete a document (soft delete)
     * 
     * @param int $documentId
     * @return bool
     */
    public function delete(int $documentId): bool
    {
        $document = Document::findOrFail($documentId);
        
        // Check delete permissions
        if (!$this->canDeleteDocument($document)) {
            throw new Exception("Cannot delete this document");
        }
        
        // Log the deletion
        $this->logAccess($document, 'delete');
        
        // Soft delete
        return $document->delete();
    }

    // ==================== Private Helper Methods ====================

    /**
     * Validate uploaded file
     */
    private function validateFile(UploadedFile $file): void
    {
        // Check file size
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new Exception("File size exceeds maximum allowed size of " . (self::MAX_FILE_SIZE / 1048576) . "MB");
        }
        
        // Check mime type
        if (!in_array($file->getMimeType(), self::ALLOWED_MIME_TYPES)) {
            throw new Exception("File type not allowed. Allowed types: PDF, Images (JPEG, PNG, GIF), Word, Excel, Text, CSV");
        }
        
        // Additional validation can be added here (virus scan, etc.)
    }

    /**
     * Calculate SHA-256 hash of file
     */
    private function calculateFileHash(UploadedFile $file): string
    {
        return hash_file('sha256', $file->getRealPath());
    }

    /**
     * Find document by hash
     */
    private function findByHash(string $hash): ?Document
    {
        return Document::where('hash', $hash)->first();
    }

    /**
     * Check if existing document can be reused in new context
     */
    private function canReuseDocument(Document $document, string $context): bool
    {
        // Don't reuse rejected or deleted documents
        if (in_array($document->status, ['rejected', 'deleted', 'expired'])) {
            return false;
        }
        
        // Don't reuse private documents in different contexts
        if ($document->access_level === 'private' && $document->context !== $context) {
            return false;
        }
        
        // Check if document is sensitive
        if ($document->is_sensitive) {
            return false;
        }
        
        return true;
    }

    /**
     * Generate storage path for file
     */
    private function generateStoragePath(UploadedFile $file, string $hash): string
    {
        $date = now();
        $path = str_replace(
            ['{year}', '{month}', '{day}', '{hash}'],
            [$date->year, $date->format('m'), $date->format('d'), $hash],
            self::STORAGE_PATH_PATTERN
        );
        
        // Add file extension
        $extension = $file->getClientOriginalExtension();
        if (empty($extension)) {
            $extension = 'bin'; // Default extension if none provided
        }
        $path .= '.' . $extension;
        
        return $path;
    }

    /**
     * Store physical file on disk
     */
    private function storePhysicalFile(UploadedFile $file, string $path): void
    {
        // Ensure directory exists
        $directory = dirname($path);
        Storage::disk(self::STORAGE_DISK)->makeDirectory($directory);
        
        // Store the file
        Storage::disk(self::STORAGE_DISK)->put($path, file_get_contents($file->getRealPath()));
    }

    /**
     * Create new document record
     */
    private function createNewDocument(UploadedFile $file, string $hash, string $path, string $context, array $options): Document
    {
        return Document::create([
            'uuid' => Str::uuid(),
            'hash' => $hash,
            'original_name' => $file->getClientOriginalName(),
            'display_name' => $options['display_name'] ?? $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'path' => $path,  // Path is now properly set
            'disk' => self::STORAGE_DISK,
            'context' => $context,
            'category' => $options['category'] ?? null,
            'subcategory' => $options['subcategory'] ?? null,
            'tags' => $options['tags'] ?? [],
            'metadata' => $options['metadata'] ?? [],
            'status' => $options['requires_verification'] ?? false ? 'pending_verification' : 'active',
            'verification_status' => $options['requires_verification'] ?? false ? 'pending' : 'not_required',
            'access_level' => $options['access_level'] ?? 'private',
            'requires_authentication' => $options['requires_authentication'] ?? true,
            'is_sensitive' => $options['is_sensitive'] ?? false,
            'retention_until' => $options['retention_until'] ?? null,
            'expires_at' => $options['expires_at'] ?? null,
            'uploaded_by' => Auth::id() ?? 1,  // Default to system user if not authenticated
            'uploaded_ip' => request()->ip()
        ]);
    }

    /**
     * Create document relationship
     */
    private function createRelationship(Document $document, string $ownerType, int $ownerId, array $options): DocumentRelationship
    {
        return DocumentRelationship::create([
            'document_id' => $document->id,
            'owner_type' => $ownerType,
            'owner_id' => $ownerId,
            'relationship_type' => $options['relationship_type'] ?? 'owner',
            'purpose' => $options['purpose'] ?? null,
            'access_level' => $options['access_level'] ?? 'read',
            'is_primary' => $options['is_primary'] ?? false,
            'is_required' => $options['is_required'] ?? false,
            'sort_order' => $options['sort_order'] ?? 0
        ]);
    }

    /**
     * Queue document for processing
     */
    private function queueForProcessing(Document $document, array $options = []): void
    {
        $processes = [];
        
        // Always scan for viruses
        $processes[] = ['type' => 'virus_scan', 'priority' => 1];
        
        // Generate thumbnail for images
        if (Str::startsWith($document->mime_type, 'image/')) {
            $processes[] = ['type' => 'thumbnail', 'priority' => 2];
        }
        
        // OCR for PDFs and images if requested
        if ($options['enable_ocr'] ?? false) {
            if ($document->mime_type === 'application/pdf' || Str::startsWith($document->mime_type, 'image/')) {
                $processes[] = ['type' => 'ocr', 'priority' => 3];
            }
        }
        
        // Extract metadata
        $processes[] = ['type' => 'extract_metadata', 'priority' => 4];
        
        // Create queue entries
        foreach ($processes as $process) {
            DocumentProcessingQueue::create([
                'document_id' => $document->id,
                'process_type' => $process['type'],
                'status' => 'pending',
                'options' => $options
            ]);
        }
    }

    /**
     * Log document access
     */
    private function logAccess(Document $document, string $accessType, array $context = []): void
    {
        DocumentAccessLog::create([
            'document_id' => $document->id,
            'accessed_by' => Auth::id() ?? 1,  // Default to system user
            'access_type' => $accessType,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'context' => $context,
            'accessed_at' => now()
        ]);
    }

    /**
     * Check if user can access document
     */
    private function canAccessDocument(Document $document): bool
    {
        // For now, allow access if user is authenticated
        // You can implement more complex logic based on your requirements
        return true;  // Temporarily allow all access for testing
    }

    /**
     * Check if user can edit document
     */
    private function canEditDocument(Document $document): bool
    {
        // Check if user is owner or has admin rights
        $userId = Auth::id() ?? 1;
        return $userId === $document->uploaded_by || $userId === 1;  // Allow system user
    }

    /**
     * Check if user can delete document
     */
    private function canDeleteDocument(Document $document): bool
    {
        // Check if user is owner or has admin rights
        $userId = Auth::id() ?? 1;
        return $userId === $document->uploaded_by || $userId === 1;  // Allow system user
    }

    /**
     * Apply access control to query
     */
    private function applyAccessControl($query)
    {
        // For now, show all documents
        // You can implement more complex access control based on your requirements
        return $query;
    }

    /**
     * Update related entities after verification
     */
    private function updateRelatedEntities(Document $document, bool $approved): void
    {
        // Update related application, student record, etc. based on verification
        // This will be implemented based on specific business logic
    }
}