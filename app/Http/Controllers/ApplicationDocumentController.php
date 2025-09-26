<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\DocumentVerificationService;
use App\Services\ApplicationService;
use App\Models\AdmissionApplication;
use App\Models\ApplicationDocument;
use App\Models\ApplicationChecklistItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Exception;

class ApplicationDocumentController extends Controller
{
    protected $documentService;
    protected $applicationService;

    /**
     * Maximum file size in bytes (10MB)
     */
    private const MAX_FILE_SIZE = 10485760;

    /**
     * Allowed file extensions
     */
    private const ALLOWED_EXTENSIONS = [
        'pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'
    ];

    /**
     * Document types and their requirements
     */
    private const DOCUMENT_TYPES = [
        'transcript' => [
            'name' => 'Official Transcript',
            'formats' => ['pdf'],
            'max_size' => 10485760,
            'required' => true,
        ],
        'high_school_transcript' => [
            'name' => 'High School Transcript',
            'formats' => ['pdf'],
            'max_size' => 10485760,
            'required' => true,
        ],
        'diploma' => [
            'name' => 'Diploma/Certificate',
            'formats' => ['pdf', 'jpg', 'jpeg', 'png'],
            'max_size' => 10485760,
            'required' => false,
        ],
        'test_scores' => [
            'name' => 'Test Score Report',
            'formats' => ['pdf'],
            'max_size' => 5242880,
            'required' => false,
        ],
        'recommendation_letter' => [
            'name' => 'Letter of Recommendation',
            'formats' => ['pdf', 'doc', 'docx'],
            'max_size' => 5242880,
            'required' => false,
        ],
        'resume' => [
            'name' => 'Resume/CV',
            'formats' => ['pdf', 'doc', 'docx'],
            'max_size' => 5242880,
            'required' => false,
        ],
        'portfolio' => [
            'name' => 'Portfolio',
            'formats' => ['pdf', 'jpg', 'jpeg', 'png'],
            'max_size' => 52428800, // 50MB for portfolios
            'required' => false,
        ],
        'passport' => [
            'name' => 'Passport Copy',
            'formats' => ['pdf', 'jpg', 'jpeg', 'png'],
            'max_size' => 5242880,
            'required' => false,
        ],
        'financial_statement' => [
            'name' => 'Financial Statement',
            'formats' => ['pdf'],
            'max_size' => 10485760,
            'required' => false,
        ],
        'english_proficiency' => [
            'name' => 'English Proficiency Certificate',
            'formats' => ['pdf'],
            'max_size' => 5242880,
            'required' => false,
        ],
    ];

    /**
     * Create a new controller instance.
     */
    public function __construct(
        DocumentVerificationService $documentService,
        ApplicationService $applicationService
    ) {
        $this->documentService = $documentService;
        $this->applicationService = $applicationService;
    }

    /**
     * Upload a document for an application.
     */
    public function upload(Request $request, $uuid = null)
    {
        try {
            // Get UUID from route parameter or request
            $applicationUuid = $uuid ?? $request->application_uuid;
            
            if (!$applicationUuid) {
                throw new Exception('Application UUID is required');
            }

            // Validate request - using correct document types from DB constraint
            $request->validate([
                'document_type' => 'required|string|in:transcript,high_school_transcript,university_transcript,diploma,degree_certificate,test_scores,recommendation_letter,personal_statement,essay,resume,portfolio,financial_statement,bank_statement,sponsor_letter,passport,national_id,birth_certificate,medical_certificate,english_proficiency,other',
                'file' => 'required|file|max:10240', // 10MB max
            ]);

            // Get application
            $application = AdmissionApplication::where('application_uuid', $applicationUuid)
                ->firstOrFail();

            $file = $request->file('file');
            
            // Store the file
            $directory = "applications/{$application->id}/documents";
            $path = $file->store($directory, 'public');
            
            // Get file information using correct methods
            $extension = $file->extension(); // Correct method
            $mimeType = $file->getMimeType();
            $fileSize = $file->getSize();
            $originalName = $file->getClientOriginalName();
            
            // Generate file hash for duplicate detection
            $fileHash = md5_file($file->getRealPath());
            
            // Check for duplicate uploads
            $existingDoc = ApplicationDocument::where('application_id', $application->id)
                ->where('document_type', $request->document_type)
                ->where('file_hash', $fileHash)
                ->first();
                
            if ($existingDoc) {
                return response()->json([
                    'success' => false,
                    'message' => 'This document type has already been uploaded with the same file'
                ], 400);
            }
            
            // Delete old document of same type if exists
            ApplicationDocument::where('application_id', $application->id)
                ->where('document_type', $request->document_type)
                ->delete();
            
            // Create new document record
            $document = ApplicationDocument::create([
                'application_id' => $application->id,
                'document_type' => $request->document_type,
                'document_name' => $originalName,
                'original_filename' => $originalName,
                'file_path' => $path,
                'file_type' => $extension,
                'file_size' => $fileSize,
                'mime_type' => $mimeType,
                'file_hash' => $fileHash,
                'status' => 'uploaded',
                'uploaded_at' => now(),
            ]);

            // Update the JSON column in admission_applications
            $documents = $application->documents ?? [];
            $documents[$request->document_type] = [
                'id' => $document->id,
                'name' => $originalName,
                'path' => $path,
                'size' => $fileSize,
                'uploaded_at' => now()->toIso8601String(),
                'status' => 'uploaded'
            ];
            
            $application->documents = $documents;
            $application->last_updated_at = now();
            $application->save();

            Log::info('Document uploaded successfully', [
                'application_id' => $application->id,
                'document_id' => $document->id,
                'document_type' => $request->document_type,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully',
                'document' => [
                    'id' => $document->id,
                    'type' => $document->document_type,
                    'name' => $document->document_name,
                    'status' => $document->status,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Document upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a document.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        try {
            $document = ApplicationDocument::findOrFail($id);
            
            // Get application to verify ownership
            $application = $document->application;
            
            // Verify ownership
            if (!$this->verifyApplicationOwnership($application)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            // Check if document can be deleted
            if ($document->status === 'verified') {
                return response()->json([
                    'success' => false,
                    'message' => 'Verified documents cannot be deleted',
                ], 403);
            }

            // Check if application is still editable
            if (!in_array($application->status, ['draft', 'documents_pending'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documents cannot be modified after application submission',
                ], 403);
            }

            // Store document info before deletion
            $documentType = $document->document_type;
            $fileName = $document->document_name;

            // Delete physical file
            if (Storage::exists($document->file_path)) {
                Storage::delete($document->file_path);
            }

            // Delete database record
            $document->delete();

            // Update checklist
            $this->updateDocumentChecklist($application, $documentType, false);

            Log::info('Document deleted', [
                'application_id' => $application->id,
                'document_type' => $documentType,
                'file_name' => $fileName,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully',
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found',
            ], 404);
        } catch (Exception $e) {
            Log::error('Document deletion failed', [
                'document_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete document',
            ], 500);
        }
    }

    /**
     * Download a document.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function download($id)
    {
        try {
            $document = ApplicationDocument::findOrFail($id);
            $application = $document->application;

            // Verify ownership or admin access
            if (!$this->verifyApplicationOwnership($application) && !$this->isAdminUser()) {
                abort(403, 'Unauthorized');
            }

            // Check if file exists
            if (!Storage::exists($document->file_path)) {
                abort(404, 'File not found');
            }

            // Get file content
            $fileContent = Storage::get($document->file_path);
            $mimeType = Storage::mimeType($document->file_path);
            
            // Prepare download response
            return Response::make($fileContent, 200, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'attachment; filename="' . $document->original_filename . '"',
                'Content-Length' => strlen($fileContent),
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Document not found');
        } catch (Exception $e) {
            Log::error('Document download failed', [
                'document_id' => $id,
                'error' => $e->getMessage(),
            ]);
            abort(500, 'Failed to download document');
        }
    }

    /**
     * Preview a document.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function preview($id)
    {
        try {
            $document = ApplicationDocument::findOrFail($id);
            $application = $document->application;

            // Verify ownership or admin access
            if (!$this->verifyApplicationOwnership($application) && !$this->isAdminUser()) {
                abort(403, 'Unauthorized');
            }

            // Check if file exists
            if (!Storage::exists($document->file_path)) {
                abort(404, 'File not found');
            }

            // Only allow preview for certain file types
            $previewableTypes = ['pdf', 'jpg', 'jpeg', 'png'];
            $extension = pathinfo($document->file_path, PATHINFO_EXTENSION);
            
            if (!in_array(strtolower($extension), $previewableTypes)) {
                // Redirect to download for non-previewable files
                return redirect()->route('admissions.documents.download', $id);
            }

            // Get file content
            $fileContent = Storage::get($document->file_path);
            $mimeType = Storage::mimeType($document->file_path);

            // Return file for inline viewing
            return Response::make($fileContent, 200, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . $document->original_filename . '"',
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Document not found');
        } catch (Exception $e) {
            Log::error('Document preview failed', [
                'document_id' => $id,
                'error' => $e->getMessage(),
            ]);
            abort(500, 'Failed to preview document');
        }
    }

    /**
     * Get list of uploaded documents for an application.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUploadedList(Request $request)
    {
        try {
            $request->validate([
                'application_uuid' => 'required|string',
            ]);

            $application = $this->getApplication($request->application_uuid);

            // Get all documents with verification status
            $documents = ApplicationDocument::where('application_id', $application->id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($doc) {
                    return [
                        'id' => $doc->id,
                        'type' => $doc->document_type,
                        'type_name' => self::DOCUMENT_TYPES[$doc->document_type]['name'] ?? $doc->document_type,
                        'name' => $doc->document_name,
                        'original_name' => $doc->original_filename,
                        'size' => $this->formatBytes($doc->file_size),
                        'status' => $doc->status,
                        'is_verified' => $doc->is_verified,
                        'verified_by' => $doc->verifiedBy ? $doc->verifiedBy->name : null,
                        'verified_at' => $doc->verified_at ? $doc->verified_at->format('Y-m-d H:i:s') : null,
                        'rejection_reason' => $doc->rejection_reason,
                        'uploaded_at' => $doc->created_at->format('Y-m-d H:i:s'),
                        'can_delete' => !$doc->is_verified && in_array($application->status, ['draft', 'documents_pending']),
                        'preview_url' => route('admissions.documents.preview', $doc->id),
                        'download_url' => route('admissions.documents.download', $doc->id),
                    ];
                });

            // Get document requirements checklist
            $checklist = $this->getDocumentChecklist($application);

            return response()->json([
                'success' => true,
                'documents' => $documents,
                'checklist' => $checklist,
                'stats' => [
                    'total_uploaded' => $documents->count(),
                    'verified' => $documents->where('is_verified', true)->count(),
                    'pending' => $documents->where('status', 'pending_verification')->count(),
                    'rejected' => $documents->where('status', 'rejected')->count(),
                ],
            ]);

        } catch (Exception $e) {
            Log::error('Failed to get document list', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve documents',
            ], 500);
        }
    }

    /**
     * Bulk upload documents.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkUpload(Request $request)
    {
        try {
            $request->validate([
                'application_uuid' => 'required|string',
                'documents' => 'required|array|min:1|max:10',
                'documents.*' => 'required|file|max:' . (self::MAX_FILE_SIZE / 1024),
            ]);

            $application = $this->getApplication($request->application_uuid);
            
            $uploaded = [];
            $failed = [];

            foreach ($request->file('documents') as $index => $file) {
                try {
                    // Determine document type from file name or request
                    $documentType = $this->determineDocumentType($file->getClientOriginalName());
                    
                    if (!$documentType) {
                        $failed[] = [
                            'file' => $file->getClientOriginalName(),
                            'error' => 'Unable to determine document type',
                        ];
                        continue;
                    }

                    // Upload document
                    $document = $this->documentService->uploadDocument(
                        $application->id,
                        $file,
                        $documentType
                    );

                    $uploaded[] = [
                        'id' => $document->id,
                        'name' => $document->document_name,
                        'type' => $document->document_type,
                    ];

                } catch (Exception $e) {
                    $failed[] = [
                        'file' => $file->getClientOriginalName(),
                        'error' => $e->getMessage(),
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => count($uploaded) . ' document(s) uploaded successfully',
                'uploaded' => $uploaded,
                'failed' => $failed,
            ]);

        } catch (Exception $e) {
            Log::error('Bulk upload failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Bulk upload failed',
            ], 500);
        }
    }

    /**
     * Request document from applicant.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function requestDocument(Request $request)
    {
        try {
            $request->validate([
                'application_id' => 'required|integer|exists:admission_applications,id',
                'document_type' => 'required|string',
                'message' => 'nullable|string|max:500',
                'deadline' => 'nullable|date|after:today',
            ]);

            // Check admin permissions
            if (!$this->isAdminUser()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            $application = AdmissionApplication::findOrFail($request->application_id);

            // Create document request record
            $checklistItem = ApplicationChecklistItem::create([
                'application_id' => $application->id,
                'item_type' => 'document',
                'item_name' => self::DOCUMENT_TYPES[$request->document_type]['name'] ?? $request->document_type,
                'is_required' => true,
                'is_completed' => false,
                'notes' => $request->message,
            ]);

            // Update application status
            if ($application->status === 'submitted') {
                $application->status = 'documents_pending';
                $application->save();
            }

            // Send notification to applicant
            $this->documentService->requestAdditionalDocuments(
                $application->id,
                [$request->document_type],
                $request->message,
                $request->deadline
            );

            return response()->json([
                'success' => true,
                'message' => 'Document request sent to applicant',
                'checklist_item_id' => $checklistItem->id,
            ]);

        } catch (Exception $e) {
            Log::error('Failed to request document', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to request document',
            ], 500);
        }
    }

    /**
     * Helper Methods
     */

    /**
     * Get application by UUID with ownership check.
     */
    private function getApplication($uuid)
    {
        $application = AdmissionApplication::where('application_uuid', $uuid)->firstOrFail();

        // Verify ownership
        if (!$this->verifyApplicationOwnership($application)) {
            abort(403, 'Unauthorized access to application');
        }

        return $application;
    }

    /**
     * Verify application ownership.
     */
    private function verifyApplicationOwnership($application): bool
    {
        if (Auth::check()) {
            return $application->user_id === Auth::id() || 
                   $application->email === Auth::user()->email;
        }

        // Check session for non-authenticated users
        return Session::get('current_application_uuid') === $application->application_uuid;
    }

    /**
     * Check if current user is admin.
     */
    private function isAdminUser(): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $adminRoles = ['admin', 'admissions_officer', 'registrar', 'document_verifier'];
        return Auth::user()->hasAnyRole($adminRoles);
    }

    /**
     * Format bytes to human readable size.
     */
    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Update document checklist.
     */
    private function updateDocumentChecklist($application, $documentType, $isCompleted)
    {
        $checklistItem = ApplicationChecklistItem::firstOrCreate(
            [
                'application_id' => $application->id,
                'item_type' => 'document',
                'item_name' => self::DOCUMENT_TYPES[$documentType]['name'] ?? $documentType,
            ],
            [
                'is_required' => self::DOCUMENT_TYPES[$documentType]['required'] ?? false,
            ]
        );

        $checklistItem->is_completed = $isCompleted;
        $checklistItem->completed_at = $isCompleted ? now() : null;
        $checklistItem->save();
    }

    /**
     * Get document checklist for application.
     */
    private function getDocumentChecklist($application): array
    {
        $checklist = [];
        
        // Get required documents based on application type
        $requiredTypes = $this->getRequiredDocumentTypes($application);
        
        foreach ($requiredTypes as $type => $config) {
            $uploaded = ApplicationDocument::where('application_id', $application->id)
                ->where('document_type', $type)
                ->whereIn('status', ['uploaded', 'verified', 'pending_verification'])
                ->first();

            $checklist[] = [
                'type' => $type,
                'name' => $config['name'],
                'required' => $config['required'],
                'uploaded' => $uploaded !== null,
                'status' => $uploaded ? $uploaded->status : 'pending',
                'document_id' => $uploaded ? $uploaded->id : null,
            ];
        }

        return $checklist;
    }

    /**
     * Get required document types based on application.
     */
    private function getRequiredDocumentTypes($application): array
    {
        $types = [];

        // Common documents
        $types['transcript'] = self::DOCUMENT_TYPES['transcript'];

        // Application type specific
        switch ($application->application_type) {
            case 'freshman':
                $types['high_school_transcript'] = self::DOCUMENT_TYPES['high_school_transcript'];
                break;
                
            case 'international':
                $types['passport'] = self::DOCUMENT_TYPES['passport'];
                $types['english_proficiency'] = self::DOCUMENT_TYPES['english_proficiency'];
                $types['financial_statement'] = self::DOCUMENT_TYPES['financial_statement'];
                break;
                
            case 'graduate':
                $types['resume'] = self::DOCUMENT_TYPES['resume'];
                if ($application->program && $application->program->requires_portfolio) {
                    $types['portfolio'] = self::DOCUMENT_TYPES['portfolio'];
                }
                break;
        }

        return $types;
    }

    /**
     * Determine document type from filename.
     */
    private function determineDocumentType($filename): ?string
    {
        $filename = strtolower($filename);
        
        $patterns = [
            'transcript' => ['transcript', 'academic_record', 'grade_report'],
            'diploma' => ['diploma', 'certificate', 'degree'],
            'test_scores' => ['sat', 'act', 'gre', 'toefl', 'ielts', 'test_score'],
            'resume' => ['resume', 'cv', 'curriculum_vitae'],
            'passport' => ['passport'],
            'financial_statement' => ['financial', 'bank_statement', 'sponsor'],
        ];

        foreach ($patterns as $type => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($filename, $keyword)) {
                    return $type;
                }
            }
        }

        return null;
    }
}