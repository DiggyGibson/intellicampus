<?php

namespace App\Services;

use App\Models\AdmissionApplication;
use App\Models\ApplicationDocument;
use App\Models\ApplicationChecklistItem;
use App\Models\ApplicationCommunication;
use App\Models\ApplicationNote;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Exception;
use Intervention\Image\Facades\Image;

class DocumentVerificationService
{
    /**
     * Allowed file types and their MIME types
     */
    private const ALLOWED_FILE_TYPES = [
        'pdf' => ['application/pdf'],
        'jpg' => ['image/jpeg', 'image/jpg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'doc' => ['application/msword'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
    ];

    /**
     * Maximum file sizes in bytes (10MB default)
     */
    private const MAX_FILE_SIZES = [
        'transcript' => 10485760, // 10MB
        'essay' => 5242880, // 5MB
        'recommendation_letter' => 5242880, // 5MB
        'portfolio' => 52428800, // 50MB
        'default' => 10485760, // 10MB
    ];

    /**
     * Document type requirements
     */
    private const DOCUMENT_REQUIREMENTS = [
        'transcript' => [
            'formats' => ['pdf'],
            'max_size' => 10485760,
            'requires_verification' => true,
            'verification_method' => 'manual',
        ],
        'high_school_transcript' => [
            'formats' => ['pdf'],
            'max_size' => 10485760,
            'requires_verification' => true,
            'verification_method' => 'manual',
        ],
        'university_transcript' => [
            'formats' => ['pdf'],
            'max_size' => 10485760,
            'requires_verification' => true,
            'verification_method' => 'third_party',
        ],
        'test_scores' => [
            'formats' => ['pdf'],
            'max_size' => 5242880,
            'requires_verification' => true,
            'verification_method' => 'api',
        ],
        'recommendation_letter' => [
            'formats' => ['pdf', 'doc', 'docx'],
            'max_size' => 5242880,
            'requires_verification' => false,
        ],
        'personal_statement' => [
            'formats' => ['pdf', 'doc', 'docx'],
            'max_size' => 5242880,
            'requires_verification' => false,
        ],
        'passport' => [
            'formats' => ['pdf', 'jpg', 'jpeg', 'png'],
            'max_size' => 5242880,
            'requires_verification' => true,
            'verification_method' => 'manual',
        ],
        'portfolio' => [
            'formats' => ['pdf', 'jpg', 'jpeg', 'png'],
            'max_size' => 52428800,
            'requires_verification' => false,
        ],
    ];

    /**
     * Upload document for application
     *
     * @param int $applicationId
     * @param UploadedFile $file
     * @param string $documentType
     * @param array $metadata
     * @return ApplicationDocument
     * @throws Exception
     */
    public function uploadDocument(
        int $applicationId, 
        UploadedFile $file, 
        string $documentType,
        array $metadata = []
    ): ApplicationDocument {
        DB::beginTransaction();

        try {
            // Validate application exists
            $application = AdmissionApplication::findOrFail($applicationId);
            
            // Check if application can receive documents
            if (!$this->canUploadDocuments($application)) {
                throw new Exception("Documents cannot be uploaded for application in status: {$application->status}");
            }

            // Validate file
            $this->validateFile($file, $documentType);

            // Check for duplicate documents
            $existingDocument = $this->checkDuplicateDocument($applicationId, $documentType, $file);
            if ($existingDocument) {
                throw new Exception("This document appears to be a duplicate of an existing upload");
            }

            // Generate secure filename and path
            $filename = $this->generateSecureFilename($file, $documentType);
            $path = "applications/{$applicationId}/documents/{$documentType}";
            
            // Store the file
            $storedPath = Storage::putFileAs($path, $file, $filename);
            
            if (!$storedPath) {
                throw new Exception("Failed to store document");
            }

            // Calculate file hash for integrity
            $fileHash = hash_file('sha256', $file->getRealPath());

            // Create document record
            $document = new ApplicationDocument();
            $document->application_id = $applicationId;
            $document->document_type = $documentType;
            $document->document_name = $this->getDocumentName($documentType, $metadata);
            $document->original_filename = $file->getClientOriginalName();
            $document->file_path = $storedPath;
            $document->file_type = $file->getMimeType();
            $document->file_size = $file->getSize();
            $document->file_hash = $fileHash;
            $document->status = 'uploaded';
            
            // Add metadata if provided
            if (isset($metadata['recommender_name'])) {
                $document->recommender_name = $metadata['recommender_name'];
                $document->recommender_email = $metadata['recommender_email'] ?? null;
                $document->recommender_title = $metadata['recommender_title'] ?? null;
                $document->recommender_institution = $metadata['recommender_institution'] ?? null;
            }
            
            $document->save();

            // Update checklist
            $this->updateDocumentChecklist($applicationId, $documentType, true);

            // Process document based on type
            $this->processDocumentByType($document);

            // Add upload note
            $this->addDocumentNote($application, "Document uploaded: {$document->document_name}");

            // Check if auto-verification is possible
            if ($this->canAutoVerify($documentType)) {
                $this->attemptAutoVerification($document);
            }

            DB::commit();

            Log::info('Document uploaded', [
                'application_id' => $applicationId,
                'document_id' => $document->id,
                'type' => $documentType,
                'size' => $file->getSize(),
            ]);

            return $document;

        } catch (Exception $e) {
            DB::rollBack();
            
            // Clean up uploaded file if it exists
            if (isset($storedPath)) {
                Storage::delete($storedPath);
            }
            
            Log::error('Document upload failed', [
                'application_id' => $applicationId,
                'type' => $documentType,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Verify a document
     *
     * @param int $documentId
     * @param string $status
     * @param string|null $notes
     * @return ApplicationDocument
     * @throws Exception
     */
    public function verifyDocument(int $documentId, string $status, ?string $notes = null): ApplicationDocument
    {
        DB::beginTransaction();

        try {
            $document = ApplicationDocument::with('application')->findOrFail($documentId);
            
            // Validate status
            if (!in_array($status, ['verified', 'rejected'])) {
                throw new Exception("Invalid verification status: {$status}");
            }

            // Check if user can verify documents
            if (!$this->canVerifyDocuments(auth()->user())) {
                throw new Exception("You do not have permission to verify documents");
            }

            // Update document status
            $previousStatus = $document->status;
            $document->status = $status;
            $document->is_verified = ($status === 'verified');
            $document->verified_by = auth()->id();
            $document->verified_at = now();
            $document->verification_notes = $notes;
            
            if ($status === 'rejected') {
                $document->rejection_reason = $notes;
            }
            
            $document->save();

            // Update checklist
            $this->updateDocumentChecklist(
                $document->application_id, 
                $document->document_type, 
                $status === 'verified'
            );

            // Send notification based on status
            if ($status === 'verified') {
                $this->sendVerificationSuccessNotification($document);
            } else {
                $this->sendRejectionNotification($document, $notes);
            }

            // Add verification note
            $this->addDocumentNote(
                $document->application,
                "Document {$status}: {$document->document_name}" . ($notes ? " - {$notes}" : "")
            );

            // Check if all documents are verified
            if ($status === 'verified') {
                $this->checkAllDocumentsVerified($document->application_id);
            }

            DB::commit();

            Log::info('Document verified', [
                'document_id' => $documentId,
                'status' => $status,
                'verified_by' => auth()->id(),
            ]);

            return $document;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Document verification failed', [
                'document_id' => $documentId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Request additional documents from applicant
     *
     * @param int $applicationId
     * @param array $documentTypes
     * @param string $message
     * @param Carbon|null $deadline
     * @return array
     * @throws Exception
     */
    public function requestAdditionalDocuments(
        int $applicationId, 
        array $documentTypes,
        string $message,
        ?Carbon $deadline = null
    ): array {
        DB::beginTransaction();

        try {
            $application = AdmissionApplication::findOrFail($applicationId);
            
            // Update application status if needed
            if ($application->status === 'submitted') {
                $application->status = 'documents_pending';
                $application->save();
            }

            // Create checklist items for requested documents
            foreach ($documentTypes as $type => $label) {
                ApplicationChecklistItem::updateOrCreate(
                    [
                        'application_id' => $applicationId,
                        'item_type' => 'document',
                        'item_name' => $label,
                    ],
                    [
                        'is_required' => true,
                        'is_completed' => false,
                        'notes' => 'Requested on ' . now()->format('Y-m-d'),
                    ]
                );
            }

            // Send notification
            $this->sendDocumentRequestNotification($application, $documentTypes, $message, $deadline);

            // Add note
            $this->addDocumentNote(
                $application,
                "Additional documents requested: " . implode(', ', array_values($documentTypes))
            );

            DB::commit();

            Log::info('Additional documents requested', [
                'application_id' => $applicationId,
                'documents' => $documentTypes,
            ]);

            return [
                'status' => 'success',
                'message' => 'Document request sent successfully',
                'documents_requested' => $documentTypes,
                'deadline' => $deadline?->format('Y-m-d'),
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to request documents', [
                'application_id' => $applicationId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Validate transcript document
     *
     * @param int $documentId
     * @return array
     * @throws Exception
     */
    public function validateTranscript(int $documentId): array
    {
        $document = ApplicationDocument::findOrFail($documentId);
        
        if (!in_array($document->document_type, ['transcript', 'high_school_transcript', 'university_transcript'])) {
            throw new Exception("Document is not a transcript");
        }

        $validationResults = [
            'is_valid' => true,
            'checks' => [],
            'warnings' => [],
            'errors' => [],
        ];

        // Check file integrity
        $currentHash = hash_file('sha256', Storage::path($document->file_path));
        if ($currentHash !== $document->file_hash) {
            $validationResults['is_valid'] = false;
            $validationResults['errors'][] = 'File integrity check failed';
        } else {
            $validationResults['checks'][] = 'File integrity verified';
        }

        // Check if PDF is readable
        try {
            $pdfContent = Storage::get($document->file_path);
            if (strlen($pdfContent) < 100) {
                throw new Exception("PDF appears to be empty");
            }
            $validationResults['checks'][] = 'PDF is readable';
        } catch (Exception $e) {
            $validationResults['is_valid'] = false;
            $validationResults['errors'][] = 'Cannot read PDF content';
        }

        // Extract text for analysis (would use OCR library in production)
        $extractedData = $this->extractTranscriptData($document);
        
        if ($extractedData) {
            // Validate GPA if found
            if (isset($extractedData['gpa'])) {
                $application = $document->application;
                if ($application->previous_gpa) {
                    $gpaDifference = abs($application->previous_gpa - $extractedData['gpa']);
                    if ($gpaDifference > 0.1) {
                        $validationResults['warnings'][] = "GPA mismatch: Application shows {$application->previous_gpa}, transcript shows {$extractedData['gpa']}";
                    }
                }
            }
            
            // Check institution name
            if (isset($extractedData['institution'])) {
                $validationResults['checks'][] = "Institution identified: {$extractedData['institution']}";
            }
            
            // Check graduation date
            if (isset($extractedData['graduation_date'])) {
                $validationResults['checks'][] = "Graduation date: {$extractedData['graduation_date']}";
            }
        }

        return $validationResults;
    }

    /**
     * Authenticate document with third-party service
     *
     * @param int $documentId
     * @return array
     * @throws Exception
     */
    public function authenticateDocument(int $documentId): array
    {
        $document = ApplicationDocument::findOrFail($documentId);
        
        // This would integrate with services like National Student Clearinghouse, 
        // Parchment, or other verification services
        
        $authenticationResult = [
            'authenticated' => false,
            'service' => null,
            'verification_code' => null,
            'verified_at' => null,
            'details' => [],
        ];

        try {
            // Example: Check with external verification service
            if ($document->document_type === 'university_transcript') {
                $authenticationResult = $this->verifyWithClearinghouse($document);
            } elseif ($document->document_type === 'test_scores') {
                $authenticationResult = $this->verifyWithTestingService($document);
            } else {
                // Manual verification required
                $authenticationResult['details'][] = 'Manual verification required for this document type';
            }

            // Update document with authentication results
            if ($authenticationResult['authenticated']) {
                $document->status = 'verified';
                $document->is_verified = true;
                $document->verified_at = now();
                $document->verification_notes = "Authenticated via {$authenticationResult['service']}";
                $document->save();
            }

        } catch (Exception $e) {
            Log::error('Document authentication failed', [
                'document_id' => $documentId,
                'error' => $e->getMessage(),
            ]);
            
            $authenticationResult['error'] = $e->getMessage();
        }

        return $authenticationResult;
    }

    /**
     * Generate document checklist for application
     *
     * @param int $applicationId
     * @return array
     */
    public function generateDocumentChecklist(int $applicationId): array
    {
        $application = AdmissionApplication::with(['documents', 'checklistItems'])->findOrFail($applicationId);
        
        // Get required documents based on application type
        $requiredDocuments = $this->getRequiredDocuments($application->application_type);
        
        $checklist = [];
        
        foreach ($requiredDocuments as $type => $label) {
            $uploadedDocument = $application->documents
                ->where('document_type', $type)
                ->first();
            
            $checklist[] = [
                'type' => $type,
                'label' => $label,
                'required' => true,
                'uploaded' => !is_null($uploadedDocument),
                'upload_date' => $uploadedDocument?->created_at?->format('Y-m-d'),
                'status' => $uploadedDocument?->status ?? 'pending',
                'verified' => $uploadedDocument?->is_verified ?? false,
                'verification_date' => $uploadedDocument?->verified_at?->format('Y-m-d'),
                'file_name' => $uploadedDocument?->original_filename,
                'file_size' => $uploadedDocument ? $this->formatFileSize($uploadedDocument->file_size) : null,
            ];
        }

        // Add optional documents that have been uploaded
        $optionalUploaded = $application->documents
            ->whereNotIn('document_type', array_keys($requiredDocuments));
        
        foreach ($optionalUploaded as $document) {
            $checklist[] = [
                'type' => $document->document_type,
                'label' => $this->getDocumentLabel($document->document_type),
                'required' => false,
                'uploaded' => true,
                'upload_date' => $document->created_at->format('Y-m-d'),
                'status' => $document->status,
                'verified' => $document->is_verified,
                'verification_date' => $document->verified_at?->format('Y-m-d'),
                'file_name' => $document->original_filename,
                'file_size' => $this->formatFileSize($document->file_size),
            ];
        }

        return [
            'application_id' => $applicationId,
            'total_required' => count($requiredDocuments),
            'total_uploaded' => $application->documents->count(),
            'total_verified' => $application->documents->where('is_verified', true)->count(),
            'completion_percentage' => $this->calculateDocumentCompletion($application, $requiredDocuments),
            'checklist' => $checklist,
        ];
    }

    /**
     * Archive documents for completed application
     *
     * @param int $applicationId
     * @return bool
     * @throws Exception
     */
    public function archiveDocuments(int $applicationId): bool
    {
        DB::beginTransaction();

        try {
            $application = AdmissionApplication::with('documents')->findOrFail($applicationId);
            
            // Check if application is in a final state
            if (!in_array($application->status, ['admitted', 'denied', 'withdrawn'])) {
                throw new Exception("Cannot archive documents for active application");
            }

            $archivePath = "archives/applications/{$application->application_number}";
            
            foreach ($application->documents as $document) {
                // Move file to archive
                $newPath = "{$archivePath}/" . basename($document->file_path);
                Storage::move($document->file_path, $newPath);
                
                // Update document path
                $document->file_path = $newPath;
                $document->archived_at = now();
                $document->save();
            }

            // Create archive manifest
            $manifest = [
                'application_id' => $applicationId,
                'application_number' => $application->application_number,
                'archived_at' => now()->toIso8601String(),
                'documents' => $application->documents->map(function ($doc) {
                    return [
                        'id' => $doc->id,
                        'type' => $doc->document_type,
                        'name' => $doc->document_name,
                        'path' => $doc->file_path,
                        'hash' => $doc->file_hash,
                    ];
                })->toArray(),
            ];
            
            Storage::put("{$archivePath}/manifest.json", json_encode($manifest, JSON_PRETTY_PRINT));

            DB::commit();

            Log::info('Documents archived', [
                'application_id' => $applicationId,
                'document_count' => $application->documents->count(),
            ]);

            return true;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Document archival failed', [
                'application_id' => $applicationId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Detect potentially fraudulent documents
     *
     * @param int $documentId
     * @return array
     */
    public function detectFraudulentDocuments(int $documentId): array
    {
        $document = ApplicationDocument::findOrFail($documentId);
        
        $fraudIndicators = [];
        $riskScore = 0;

        // Check 1: File metadata consistency
        $metadataCheck = $this->checkFileMetadata($document);
        if (!$metadataCheck['is_consistent']) {
            $fraudIndicators[] = 'Inconsistent file metadata';
            $riskScore += 20;
        }

        // Check 2: Check for known fraudulent document hashes
        if ($this->isKnownFraudulentHash($document->file_hash)) {
            $fraudIndicators[] = 'Document hash matches known fraudulent document';
            $riskScore += 50;
        }

        // Check 3: Check for image manipulation (for image documents)
        if (in_array($document->file_type, ['image/jpeg', 'image/png'])) {
            $manipulationCheck = $this->checkImageManipulation($document);
            if ($manipulationCheck['is_manipulated']) {
                $fraudIndicators[] = 'Possible image manipulation detected';
                $riskScore += 30;
            }
        }

        // Check 4: Text analysis for PDFs
        if ($document->file_type === 'application/pdf') {
            $textAnalysis = $this->analyzePDFText($document);
            if ($textAnalysis['has_suspicious_patterns']) {
                $fraudIndicators[] = 'Suspicious text patterns detected';
                $riskScore += 25;
            }
        }

        // Check 5: Cross-reference with other applications
        $duplicateCheck = $this->checkCrossApplicationDuplicates($document);
        if ($duplicateCheck['has_duplicates']) {
            $fraudIndicators[] = "Document used in {$duplicateCheck['count']} other applications";
            $riskScore += 40;
        }

        // Determine risk level
        $riskLevel = match(true) {
            $riskScore >= 70 => 'high',
            $riskScore >= 40 => 'medium',
            $riskScore >= 20 => 'low',
            default => 'none',
        };

        return [
            'document_id' => $documentId,
            'risk_score' => $riskScore,
            'risk_level' => $riskLevel,
            'fraud_indicators' => $fraudIndicators,
            'requires_manual_review' => $riskScore >= 40,
            'checked_at' => now()->toIso8601String(),
        ];
    }

    /**
     * OCR processing for documents
     *
     * @param int $documentId
     * @return array
     * @throws Exception
     */
    public function OCRProcessing(int $documentId): array
    {
        $document = ApplicationDocument::findOrFail($documentId);
        
        // Check if document is an image
        if (!in_array($document->file_type, ['image/jpeg', 'image/png', 'application/pdf'])) {
            throw new Exception("Document type not supported for OCR");
        }

        $extractedText = '';
        $extractedData = [];

        try {
            // In production, this would use services like:
            // - Google Cloud Vision API
            // - Amazon Textract
            // - Azure Computer Vision
            // - Tesseract OCR
            
            // For now, simulate OCR processing
            $filePath = Storage::path($document->file_path);
            
            if ($document->file_type === 'application/pdf') {
                // Extract text from PDF
                $extractedText = $this->extractPDFText($filePath);
            } else {
                // For images, would use OCR service
                $extractedText = $this->performImageOCR($filePath);
            }

            // Parse extracted text based on document type
            if ($document->document_type === 'transcript') {
                $extractedData = $this->parseTranscriptText($extractedText);
            } elseif ($document->document_type === 'test_scores') {
                $extractedData = $this->parseTestScoreText($extractedText);
            } else {
                $extractedData = ['raw_text' => $extractedText];
            }

            // Store extracted data
            $document->extracted_text = $extractedText;
            $document->extracted_data = $extractedData;
            $document->ocr_processed_at = now();
            $document->save();

            return [
                'status' => 'success',
                'document_id' => $documentId,
                'text_length' => strlen($extractedText),
                'extracted_data' => $extractedData,
                'processed_at' => now()->toIso8601String(),
            ];

        } catch (Exception $e) {
            Log::error('OCR processing failed', [
                'document_id' => $documentId,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Private helper methods
     */

    /**
     * Check if application can receive document uploads
     */
    private function canUploadDocuments(AdmissionApplication $application): bool
    {
        $allowedStatuses = ['draft', 'documents_pending', 'submitted', 'under_review'];
        return in_array($application->status, $allowedStatuses);
    }

    /**
     * Validate uploaded file
     */
    private function validateFile(UploadedFile $file, string $documentType): void
    {
        $requirements = self::DOCUMENT_REQUIREMENTS[$documentType] ?? [
            'formats' => ['pdf'],
            'max_size' => self::MAX_FILE_SIZES['default'],
        ];

        // Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $requirements['formats'])) {
            throw new ValidationException(null, [
                'file' => "File must be one of the following formats: " . implode(', ', $requirements['formats'])
            ]);
        }

        // Check MIME type
        $mimeType = $file->getMimeType();
        $allowedMimes = [];
        foreach ($requirements['formats'] as $format) {
            $allowedMimes = array_merge($allowedMimes, self::ALLOWED_FILE_TYPES[$format] ?? []);
        }
        
        if (!in_array($mimeType, $allowedMimes)) {
            throw new ValidationException(null, [
                'file' => "Invalid file type. Detected: {$mimeType}"
            ]);
        }

        // Check file size
        if ($file->getSize() > $requirements['max_size']) {
            $maxSizeMB = $requirements['max_size'] / 1048576;
            throw new ValidationException(null, [
                'file' => "File size exceeds maximum allowed size of {$maxSizeMB}MB"
            ]);
        }

        // Scan for malware (would use ClamAV or similar in production)
        $this->scanForMalware($file);
    }

    /**
     * Check for duplicate documents
     */
    private function checkDuplicateDocument(int $applicationId, string $documentType, UploadedFile $file): ?ApplicationDocument
    {
        $fileHash = hash_file('sha256', $file->getRealPath());
        
        return ApplicationDocument::where('application_id', $applicationId)
            ->where('document_type', $documentType)
            ->where('file_hash', $fileHash)
            ->first();
    }

    /**
     * Generate secure filename
     */
    private function generateSecureFilename(UploadedFile $file, string $documentType): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('Ymd_His');
        $random = Str::random(8);
        
        return "{$documentType}_{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Get document display name
     */
    private function getDocumentName(string $documentType, array $metadata): string
    {
        $labels = [
            'transcript' => 'Academic Transcript',
            'high_school_transcript' => 'High School Transcript',
            'university_transcript' => 'University Transcript',
            'test_scores' => 'Test Scores',
            'recommendation_letter' => 'Letter of Recommendation',
            'personal_statement' => 'Personal Statement',
            'statement_of_purpose' => 'Statement of Purpose',
            'passport' => 'Passport/ID',
            'portfolio' => 'Portfolio',
            'resume' => 'Resume/CV',
            'financial_statement' => 'Financial Statement',
        ];

        $name = $labels[$documentType] ?? ucwords(str_replace('_', ' ', $documentType));
        
        if ($documentType === 'recommendation_letter' && isset($metadata['recommender_name'])) {
            $name .= " - {$metadata['recommender_name']}";
        }
        
        return $name;
    }

    /**
     * Update document checklist
     */
    private function updateDocumentChecklist(int $applicationId, string $documentType, bool $isCompleted): void
    {
        $checklistItem = ApplicationChecklistItem::where('application_id', $applicationId)
            ->where('item_type', 'document')
            ->where('item_name', 'like', "%{$documentType}%")
            ->first();
        
        if ($checklistItem) {
            $checklistItem->is_completed = $isCompleted;
            $checklistItem->completed_at = $isCompleted ? now() : null;
            $checklistItem->save();
        }
    }

    /**
     * Process document based on type
     */
    private function processDocumentByType(ApplicationDocument $document): void
    {
        switch ($document->document_type) {
            case 'transcript':
            case 'high_school_transcript':
            case 'university_transcript':
                // Extract GPA and course information
                $this->processTranscript($document);
                break;
                
            case 'test_scores':
                // Extract test scores
                $this->processTestScores($document);
                break;
                
            case 'passport':
            case 'national_id':
                // Extract identification information
                $this->processIdentification($document);
                break;
                
            case 'portfolio':
                // Process portfolio items
                $this->processPortfolio($document);
                break;
        }
    }

    /**
     * Check if document type can be auto-verified
     */
    private function canAutoVerify(string $documentType): bool
    {
        $autoVerifiableTypes = ['test_scores']; // Can verify via API
        return in_array($documentType, $autoVerifiableTypes);
    }

    /**
     * Attempt automatic verification
     */
    private function attemptAutoVerification(ApplicationDocument $document): void
    {
        try {
            if ($document->document_type === 'test_scores') {
                // Attempt to verify with testing service
                $verificationResult = $this->verifyWithTestingService($document);
                
                if ($verificationResult['authenticated']) {
                    $document->status = 'verified';
                    $document->is_verified = true;
                    $document->verified_at = now();
                    $document->verification_notes = 'Auto-verified via testing service';
                    $document->save();
                }
            }
        } catch (Exception $e) {
            Log::warning('Auto-verification failed', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Check if user can verify documents
     */
    private function canVerifyDocuments(User $user): bool
    {
        $allowedRoles = [
            'admissions_officer',
            'registrar',
            'admissions_director',
            'academic_administrator',
        ];
        
        return $user->hasAnyRole($allowedRoles);
    }

    /**
     * Check if all required documents are verified
     */
    private function checkAllDocumentsVerified(int $applicationId): void
    {
        $application = AdmissionApplication::with('documents')->find($applicationId);
        
        if (!$application) {
            return;
        }
        
        $requiredDocuments = $this->getRequiredDocuments($application->application_type);
        $verifiedCount = 0;
        
        foreach (array_keys($requiredDocuments) as $type) {
            $document = $application->documents
                ->where('document_type', $type)
                ->where('is_verified', true)
                ->first();
            
            if ($document) {
                $verifiedCount++;
            }
        }
        
        // If all required documents are verified, update application status
        if ($verifiedCount === count($requiredDocuments)) {
            if ($application->status === 'documents_pending') {
                $application->status = 'under_review';
                $application->save();
                
                $this->sendAllDocumentsVerifiedNotification($application);
            }
        }
    }

    /**
     * Get required documents for application type
     */
    private function getRequiredDocuments(string $applicationType): array
    {
        $documents = [
            'freshman' => [
                'high_school_transcript' => 'High School Transcript',
                'test_scores' => 'SAT/ACT Scores',
                'personal_statement' => 'Personal Statement',
                'recommendation_letter' => 'Letter of Recommendation',
                'passport' => 'Passport/ID',
            ],
            'transfer' => [
                'university_transcript' => 'University Transcript',
                'high_school_transcript' => 'High School Transcript',
                'personal_statement' => 'Personal Statement',
                'recommendation_letter' => 'Letter of Recommendation',
                'passport' => 'Passport/ID',
            ],
            'graduate' => [
                'university_transcript' => 'University Transcript',
                'test_scores' => 'GRE/GMAT Scores',
                'statement_of_purpose' => 'Statement of Purpose',
                'recommendation_letter' => 'Letters of Recommendation',
                'resume' => 'Resume/CV',
                'passport' => 'Passport/ID',
            ],
            'international' => [
                'university_transcript' => 'Academic Transcripts',
                'test_scores' => 'TOEFL/IELTS Scores',
                'financial_statement' => 'Financial Support Statement',
                'passport' => 'Passport',
                'personal_statement' => 'Personal Statement',
                'recommendation_letter' => 'Letters of Recommendation',
            ],
        ];
        
        return $documents[$applicationType] ?? $documents['freshman'];
    }

    // Additional helper methods for document processing, notifications, etc.
    // These would be implemented based on specific requirements...
}