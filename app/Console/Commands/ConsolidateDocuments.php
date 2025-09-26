<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Document;
use App\Models\DocumentRelationship;
use App\Models\ApplicationDocument;
use App\Models\ContentItem;
use App\Services\Core\UnifiedDocumentService;

/**
 * ConsolidateDocuments Command
 * 
 * Migrates existing documents from various tables to the unified document system.
 * This command safely consolidates all document storage with rollback capability.
 * 
 * Usage: php artisan documents:consolidate [--dry-run] [--module=admission]
 */
class ConsolidateDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'documents:consolidate 
                            {--dry-run : Run without making changes}
                            {--module= : Specific module to migrate (admission, content, transcript)}
                            {--batch-size=100 : Number of records to process at once}
                            {--skip-backup : Skip backup creation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consolidate all document storage into unified document system';

    /**
     * Statistics tracking
     */
    private $stats = [
        'total_processed' => 0,
        'migrated' => 0,
        'deduplicated' => 0,
        'errors' => 0,
        'skipped' => 0
    ];

    /**
     * Document service instance
     */
    private $documentService;

    /**
     * Create a new command instance.
     */
    public function __construct(UnifiedDocumentService $documentService)
    {
        parent::__construct();
        $this->documentService = $documentService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('========================================');
        $this->info('IntelliCampus Document Consolidation');
        $this->info('========================================');
        
        if ($this->option('dry-run')) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        // Step 1: Create backup if not skipped
        if (!$this->option('skip-backup')) {
            $this->createBackup();
        }

        // Step 2: Check current state
        $this->checkCurrentState();

        // Step 3: Create storage directories
        $this->createStorageDirectories();

        // Step 4: Migrate documents based on module
        $module = $this->option('module');
        
        if ($module) {
            $this->migrateModule($module);
        } else {
            // Migrate all modules
            $this->migrateAllModules();
        }

        // Step 5: Display results
        $this->displayResults();

        // Step 6: Verify migration
        if (!$this->option('dry-run')) {
            $this->verifyMigration();
        }

        return Command::SUCCESS;
    }

    /**
     * Create backup of existing document tables
     */
    private function createBackup(): void
    {
        $this->info('Creating backup of existing document tables...');
        
        $tables = [
            'application_documents',
            'content_items',
            'transcript_requests'
        ];

        foreach ($tables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                $backupTable = $table . '_backup_' . date('Ymd_His');
                
                if (!$this->option('dry-run')) {
                    DB::statement("CREATE TABLE {$backupTable} AS SELECT * FROM {$table}");
                    $this->info("✅ Backed up {$table} to {$backupTable}");
                } else {
                    $this->info("[DRY RUN] Would backup {$table} to {$backupTable}");
                }
            }
        }
    }

    /**
     * Check current state of document storage
     */
    private function checkCurrentState(): void
    {
        $this->info("\n📊 Current Document State:");
        
        // Check application_documents
        if (DB::getSchemaBuilder()->hasTable('application_documents')) {
            $appDocs = DB::table('application_documents')->count();
            $this->info("  Application Documents: {$appDocs}");
            
            // Check for null hashes
            $nullHashes = DB::table('application_documents')->whereNull('file_hash')->count();
            if ($nullHashes > 0) {
                $this->warn("  ⚠️  {$nullHashes} documents without hash (will calculate during migration)");
            }
        }

        // Check content_items
        if (DB::getSchemaBuilder()->hasTable('content_items')) {
            $contentItems = DB::table('content_items')->count();
            $this->info("  Content Items: {$contentItems}");
        }

        // Check transcript_requests
        if (DB::getSchemaBuilder()->hasTable('transcript_requests')) {
            $transcripts = DB::table('transcript_requests')->count();
            $this->info("  Transcript Requests: {$transcripts}");
        }

        // Check unified documents table
        if (DB::getSchemaBuilder()->hasTable('documents')) {
            $unifiedDocs = DB::table('documents')->count();
            $this->info("  Unified Documents (existing): {$unifiedDocs}");
        }
    }

    /**
     * Create necessary storage directories
     */
    private function createStorageDirectories(): void
    {
        $this->info("\n📁 Creating storage directories...");
        
        $directories = [
            'documents/' . date('Y') . '/' . date('m') . '/' . date('d'),
            'thumbnails/' . date('Y') . '/' . date('m'),
            'temp/processing'
        ];

        foreach ($directories as $dir) {
            $path = storage_path('app/' . $dir);
            
            if (!file_exists($path)) {
                if (!$this->option('dry-run')) {
                    mkdir($path, 0755, true);
                    $this->info("  ✅ Created: {$dir}");
                } else {
                    $this->info("  [DRY RUN] Would create: {$dir}");
                }
            } else {
                $this->info("  ✓ Already exists: {$dir}");
            }
        }
    }

    /**
     * Migrate all modules
     */
    private function migrateAllModules(): void
    {
        $this->info("\n🔄 Migrating all modules...\n");
        
        $this->migrateAdmissionDocuments();
        $this->migrateContentItems();
        $this->migrateTranscriptDocuments();
    }

    /**
     * Migrate specific module
     */
    private function migrateModule(string $module): void
    {
        switch ($module) {
            case 'admission':
                $this->migrateAdmissionDocuments();
                break;
            case 'content':
                $this->migrateContentItems();
                break;
            case 'transcript':
                $this->migrateTranscriptDocuments();
                break;
            default:
                $this->error("Unknown module: {$module}");
        }
    }

    /**
     * Migrate admission application documents
     */
    private function migrateAdmissionDocuments(): void
    {
        $this->info("📋 Migrating Admission Documents...");
        
        if (!DB::getSchemaBuilder()->hasTable('application_documents')) {
            $this->warn("  Table 'application_documents' not found. Skipping.");
            return;
        }

        $batchSize = (int) $this->option('batch-size');
        $progress = $this->output->createProgressBar(
            DB::table('application_documents')->count()
        );

        DB::table('application_documents')
            ->orderBy('id')
            ->chunk($batchSize, function ($documents) use ($progress) {
                foreach ($documents as $doc) {
                    $this->migrateApplicationDocument($doc);
                    $progress->advance();
                }
            });

        $progress->finish();
        $this->line('');
    }

    /**
     * Migrate individual application document
     */
    private function migrateApplicationDocument($doc): void
    {
        try {
            $this->stats['total_processed']++;

            // Check if already migrated (by checking for existing hash if available)
            if ($doc->file_hash) {
                $existing = Document::where('hash', $doc->file_hash)->first();
                if ($existing) {
                    // Document already exists, just create relationship
                    if (!$this->option('dry-run')) {
                        $this->createRelationshipForExisting($existing, 'application', $doc->application_id, $doc);
                    }
                    $this->stats['deduplicated']++;
                    return;
                }
            }

            // Calculate hash if not present
            $hash = $doc->file_hash;
            if (!$hash && Storage::exists($doc->file_path)) {
                $hash = hash_file('sha256', Storage::path($doc->file_path));
            }

            // Check again with calculated hash
            if ($hash) {
                $existing = Document::where('hash', $hash)->first();
                if ($existing) {
                    if (!$this->option('dry-run')) {
                        $this->createRelationshipForExisting($existing, 'application', $doc->application_id, $doc);
                    }
                    $this->stats['deduplicated']++;
                    return;
                }
            }

            // Create new unified document
            if (!$this->option('dry-run')) {
                $document = Document::create([
                    'uuid' => \Str::uuid(),
                    'hash' => $hash ?: 'legacy_' . md5($doc->id . '_application_documents'),
                    'original_name' => $doc->original_filename ?? $doc->document_name,
                    'display_name' => $doc->document_name,
                    'mime_type' => $doc->file_type,
                    'size' => $doc->file_size ?? 0,
                    'path' => $doc->file_path,
                    'disk' => 'local',
                    'context' => 'admission',
                    'category' => $this->mapDocumentType($doc->document_type),
                    'metadata' => [
                        'migrated_from' => 'application_documents',
                        'original_id' => $doc->id,
                        'document_type' => $doc->document_type,
                        'recommender_name' => $doc->recommender_name,
                        'recommender_email' => $doc->recommender_email,
                        'recommender_title' => $doc->recommender_title,
                        'recommender_institution' => $doc->recommender_institution,
                    ],
                    'status' => $this->mapStatus($doc->status),
                    'verification_status' => $doc->is_verified ? 'verified' : 
                        ($doc->status === 'rejected' ? 'rejected' : 'pending'),
                    'verified_by' => $doc->verified_by,
                    'verified_at' => $doc->verified_at,
                    'verification_notes' => $doc->verification_notes,
                    'rejection_reason' => $doc->rejection_reason,
                    'access_level' => 'restricted',
                    'requires_authentication' => true,
                    'uploaded_by' => 1, // System user for migration
                    'created_at' => $doc->created_at,
                    'updated_at' => $doc->updated_at
                ]);

                // Create relationship
                DocumentRelationship::create([
                    'document_id' => $document->id,
                    'owner_type' => 'application',
                    'owner_id' => $doc->application_id,
                    'relationship_type' => 'attachment',
                    'purpose' => $doc->document_type,
                    'is_verified' => $doc->is_verified ?? false,
                    'created_at' => $doc->created_at
                ]);
            }

            $this->stats['migrated']++;

        } catch (\Exception $e) {
            $this->stats['errors']++;
            Log::error("Failed to migrate application document ID {$doc->id}: " . $e->getMessage());
            $this->error("  ❌ Error migrating document ID {$doc->id}: " . $e->getMessage());
        }
    }

    /**
     * Migrate content items (LMS documents)
     */
    private function migrateContentItems(): void
    {
        $this->info("\n📚 Migrating Content Items...");
        
        if (!DB::getSchemaBuilder()->hasTable('content_items')) {
            $this->warn("  Table 'content_items' not found. Skipping.");
            return;
        }

        $count = DB::table('content_items')->count();
        if ($count === 0) {
            $this->info("  No content items to migrate.");
            return;
        }

        $batchSize = (int) $this->option('batch-size');
        $progress = $this->output->createProgressBar($count);

        DB::table('content_items')
            ->orderBy('id')
            ->chunk($batchSize, function ($items) use ($progress) {
                foreach ($items as $item) {
                    $this->migrateContentItem($item);
                    $progress->advance();
                }
            });

        $progress->finish();
        $this->line('');
    }

    /**
     * Migrate individual content item
     */
    private function migrateContentItem($item): void
    {
        try {
            $this->stats['total_processed']++;

            // Only migrate if it has a file path
            if (empty($item->file_path)) {
                $this->stats['skipped']++;
                return;
            }

            if (!$this->option('dry-run')) {
                $document = Document::create([
                    'uuid' => \Str::uuid(),
                    'hash' => 'legacy_content_' . md5($item->id . '_content_items'),
                    'original_name' => $item->title,
                    'display_name' => $item->title,
                    'mime_type' => $item->content_type ?? 'application/octet-stream',
                    'size' => $item->file_size ?? 0,
                    'path' => $item->file_path,
                    'disk' => 'local',
                    'context' => 'course',
                    'category' => 'course_material',
                    'subcategory' => $item->type ?? null,
                    'metadata' => [
                        'migrated_from' => 'content_items',
                        'original_id' => $item->id,
                        'description' => $item->description ?? null,
                        'course_site_id' => $item->course_site_id ?? null,
                        'module_id' => $item->module_id ?? null
                    ],
                    'status' => $item->is_published ? 'active' : 'pending',
                    'verification_status' => 'not_required',
                    'access_level' => 'authenticated',
                    'requires_authentication' => true,
                    'uploaded_by' => $item->created_by ?? 1,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at
                ]);

                // Create relationship to course
                if ($item->course_site_id) {
                    DocumentRelationship::create([
                        'document_id' => $document->id,
                        'owner_type' => 'course',
                        'owner_id' => $item->course_site_id,
                        'relationship_type' => 'content',
                        'purpose' => 'course_material',
                        'sort_order' => $item->sort_order ?? 0,
                        'created_at' => $item->created_at
                    ]);
                }
            }

            $this->stats['migrated']++;

        } catch (\Exception $e) {
            $this->stats['errors']++;
            Log::error("Failed to migrate content item ID {$item->id}: " . $e->getMessage());
        }
    }

    /**
     * Migrate transcript documents
     */
    private function migrateTranscriptDocuments(): void
    {
        $this->info("\n📜 Migrating Transcript Documents...");
        
        if (!DB::getSchemaBuilder()->hasTable('transcript_requests')) {
            $this->warn("  Table 'transcript_requests' not found. Skipping.");
            return;
        }

        // Add migration logic for transcript documents here
        // Similar pattern to admission documents
        $this->info("  Transcript migration not yet implemented.");
    }

    /**
     * Create relationship for existing document
     */
    private function createRelationshipForExisting($document, $ownerType, $ownerId, $originalRecord): void
    {
        // Check if relationship already exists
        $existing = DocumentRelationship::where('document_id', $document->id)
            ->where('owner_type', $ownerType)
            ->where('owner_id', $ownerId)
            ->first();

        if (!$existing) {
            DocumentRelationship::create([
                'document_id' => $document->id,
                'owner_type' => $ownerType,
                'owner_id' => $ownerId,
                'relationship_type' => 'attachment',
                'purpose' => $originalRecord->document_type ?? 'general',
                'is_verified' => $originalRecord->is_verified ?? false,
                'created_at' => $originalRecord->created_at
            ]);
        }
    }

    /**
     * Map old document type to new category
     */
    private function mapDocumentType($type): string
    {
        $mapping = [
            'transcript' => 'academic_record',
            'high_school_transcript' => 'academic_record',
            'university_transcript' => 'academic_record',
            'diploma' => 'certificate',
            'degree_certificate' => 'certificate',
            'test_scores' => 'test_result',
            'recommendation_letter' => 'recommendation',
            'personal_statement' => 'essay',
            'essay' => 'essay',
            'resume' => 'resume',
            'portfolio' => 'portfolio',
            'financial_statement' => 'financial',
            'bank_statement' => 'financial',
            'sponsor_letter' => 'financial',
            'passport' => 'identity',
            'national_id' => 'identity',
            'birth_certificate' => 'identity',
            'medical_certificate' => 'medical',
            'english_proficiency' => 'test_result',
            'other' => 'other'
        ];

        return $mapping[$type] ?? 'other';
    }

    /**
     * Map old status to new status
     */
    private function mapStatus($status): string
    {
        $mapping = [
            'uploaded' => 'pending',
            'pending_verification' => 'pending_verification',
            'verified' => 'verified',
            'rejected' => 'rejected',
            'expired' => 'expired'
        ];

        return $mapping[$status] ?? 'pending';
    }

    /**
     * Display migration results
     */
    private function displayResults(): void
    {
        $this->info("\n📈 Migration Results:");
        $this->info("====================================");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Processed', $this->stats['total_processed']],
                ['Successfully Migrated', $this->stats['migrated']],
                ['Deduplicated', $this->stats['deduplicated']],
                ['Skipped', $this->stats['skipped']],
                ['Errors', $this->stats['errors']],
            ]
        );

        if ($this->stats['deduplicated'] > 0) {
            $savedSpace = $this->stats['deduplicated'] * 500000; // Assume 500KB average per file
            $this->info("\n💾 Estimated space saved from deduplication: " . $this->formatBytes($savedSpace));
        }
    }

    /**
     * Verify migration was successful
     */
    private function verifyMigration(): void
    {
        $this->info("\n🔍 Verifying migration...");
        
        // Count documents in unified table
        $unifiedCount = Document::count();
        $this->info("  Total documents in unified table: {$unifiedCount}");
        
        // Check for orphaned relationships
        $orphaned = DocumentRelationship::whereDoesntHave('document')->count();
        if ($orphaned > 0) {
            $this->warn("  ⚠️  Found {$orphaned} orphaned relationships");
        } else {
            $this->info("  ✅ No orphaned relationships found");
        }
        
        // Check for documents without relationships
        $unlinked = Document::whereDoesntHave('relationships')->count();
        if ($unlinked > 0) {
            $this->warn("  ⚠️  Found {$unlinked} documents without relationships");
        }
        
        // Verify file integrity
        $this->info("  Checking file integrity...");
        $missing = Document::take(10)->get()->filter(function ($doc) {
            return !Storage::exists($doc->path);
        })->count();
        
        if ($missing > 0) {
            $this->warn("  ⚠️  Found {$missing} documents with missing files (checked first 10)");
        } else {
            $this->info("  ✅ File integrity check passed (sampled)");
        }
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes($bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}