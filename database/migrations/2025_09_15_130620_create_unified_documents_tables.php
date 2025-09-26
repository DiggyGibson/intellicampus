<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations to create a unified document management system.
     * This consolidates all document storage across the entire IntelliCampus system.
     */
    public function up(): void
    {
        // 1. Create the master documents table (single source of truth)
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique(); // Public-facing identifier
            
            // File Information (Core)
            $table->string('hash', 64)->unique(); // SHA-256 hash for deduplication
            $table->string('original_name', 255);
            $table->string('display_name', 255)->nullable(); // User-friendly name
            $table->string('mime_type', 100);
            $table->bigInteger('size'); // File size in bytes
            $table->string('path', 500); // Storage path (organized by date/hash)
            $table->string('disk', 50)->default('local'); // Storage disk
            
            // Document Context & Classification
            $table->string('context', 50); // 'admission', 'student', 'course', 'transcript', 'faculty', 'finance'
            $table->string('category', 50)->nullable(); // 'transcript', 'id', 'certificate', 'assignment', etc.
            $table->string('subcategory', 50)->nullable(); // Further classification
            $table->json('tags')->nullable(); // Searchable tags array
            
            // Metadata (Flexible storage for context-specific data)
            $table->json('metadata')->nullable();
            /* Example metadata structure:
            {
                "pages": 5,
                "ocr_text": "extracted text...",
                "thumbnail_path": "thumbnails/abc123.jpg",
                "expires_at": "2025-12-31",
                "academic_year": "2024-2025",
                "term": "Fall 2024",
                "course_code": "CS101",
                "student_id": "24000001",
                "language": "en",
                "is_official": true,
                "institution": "Previous University",
                "issue_date": "2024-01-15"
            }
            */
            
            // Status & Verification
            $table->enum('status', [
                'pending',           // Just uploaded
                'processing',        // Being processed (virus scan, OCR, etc.)
                'active',           // Ready for use
                'pending_verification', // Awaiting verification
                'verified',         // Verified by authorized personnel
                'rejected',         // Failed verification
                'expired',          // Past expiration date
                'archived',         // Soft archived
                'deleted'           // Marked for deletion
            ])->default('pending');
            
            $table->enum('verification_status', [
                'not_required',
                'pending',
                'in_progress',
                'verified',
                'rejected',
                'expired'
            ])->default('not_required');
            
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Security & Access Control
            $table->enum('access_level', [
                'public',           // Anyone can access
                'authenticated',    // Any logged-in user
                'restricted',       // Specific permissions required
                'confidential',     // High-level access only
                'private'          // Owner and admins only
            ])->default('private');
            
            $table->boolean('requires_authentication')->default(true);
            $table->json('access_rules')->nullable(); // Custom access rules
            
            // Processing Flags
            $table->boolean('is_processed')->default(false);
            $table->boolean('has_thumbnail')->default(false);
            $table->boolean('is_searchable')->default(false); // OCR completed
            $table->boolean('virus_scanned')->default(false);
            $table->timestamp('virus_scanned_at')->nullable();
            
            // Retention & Compliance
            $table->date('retention_until')->nullable(); // Document retention policy
            $table->date('expires_at')->nullable(); // Document expiration
            $table->boolean('is_sensitive')->default(false); // Contains PII/sensitive data
            $table->string('compliance_tags')->nullable(); // FERPA, GDPR, etc.
            
            // Usage Tracking
            $table->integer('download_count')->default(0);
            $table->integer('view_count')->default(0);
            $table->timestamp('last_accessed_at')->nullable();
            
            // Audit Fields
            $table->foreignId('uploaded_by')->constrained('users');
            $table->string('uploaded_ip', 45)->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index('hash');
            $table->index('context');
            $table->index('category');
            $table->index('status');
            $table->index('verification_status');
            $table->index(['context', 'category']);
            $table->index('uploaded_by');
            $table->index('expires_at');
            $table->fullText(['original_name', 'display_name']);
            $table->index('created_at');
        });

        // 2. Document Relationships table (links documents to various entities)
        Schema::create('document_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            
            // Polymorphic relationship to any entity
            $table->string('owner_type', 50); // 'student', 'application', 'course', 'faculty', 'transaction'
            $table->unsignedBigInteger('owner_id');
            
            // Relationship details
            $table->string('relationship_type', 50); // 'owner', 'attachment', 'submission', 'reference'
            $table->string('purpose', 100)->nullable(); // 'transcript', 'id_proof', 'assignment', etc.
            $table->integer('sort_order')->default(0);
            
            // Access control for this relationship
            $table->enum('access_level', ['read', 'write', 'delete', 'share'])->default('read');
            $table->boolean('is_primary')->default(false); // Primary document for this purpose
            $table->boolean('is_required')->default(false); // Required document
            $table->boolean('is_verified')->default(false); // Verified for this specific use
            
            // Validity period for this relationship
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['owner_type', 'owner_id']);
            $table->index(['document_id', 'owner_type', 'owner_id']);
            $table->unique(['document_id', 'owner_type', 'owner_id', 'relationship_type'], 'unique_document_relationship');
        });

        // 3. Document Versions table (track document history)
        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            
            $table->integer('version_number');
            $table->string('hash', 64); // Hash of this version
            $table->string('path', 500); // Storage path for this version
            $table->bigInteger('size');
            
            // What changed
            $table->string('change_type', 50); // 'update', 'replace', 'edit', 'correction'
            $table->text('change_summary')->nullable();
            $table->json('changes')->nullable(); // Detailed change log
            
            // Version metadata
            $table->json('metadata')->nullable();
            $table->boolean('is_major_version')->default(false);
            
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('created_at');
            
            // Indexes
            $table->unique(['document_id', 'version_number']);
            $table->index('created_at');
        });

        // 4. Document Access Logs (audit trail)
        Schema::create('document_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            $table->foreignId('accessed_by')->constrained('users');
            
            $table->enum('access_type', [
                'view',
                'download',
                'print',
                'share',
                'edit',
                'verify',
                'reject',
                'delete',
                'restore'
            ]);
            
            $table->string('purpose', 255)->nullable(); // Why accessed
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->json('context')->nullable(); // Additional context
            
            $table->timestamp('accessed_at');
            
            // Indexes
            $table->index(['document_id', 'accessed_at']);
            $table->index(['accessed_by', 'accessed_at']);
            $table->index('access_type');
        });

        // 5. Document Templates (for generating documents)
        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();
            
            $table->string('name', 100);
            $table->string('code', 50)->unique(); // 'transcript_official', 'enrollment_cert'
            $table->string('category', 50);
            $table->text('description')->nullable();
            
            // Template details
            $table->string('file_type', 20); // 'pdf', 'docx', 'html'
            $table->text('template_content')->nullable(); // For HTML templates
            $table->string('template_path', 500)->nullable(); // For file-based templates
            
            // Variables available in template
            $table->json('variables'); // List of variables that can be used
            $table->json('sample_data')->nullable(); // Sample data for preview
            
            // Configuration
            $table->json('settings')->nullable();
            $table->boolean('requires_signature')->default(false);
            $table->boolean('requires_seal')->default(false);
            $table->boolean('is_official')->default(false);
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('category');
            $table->index('is_active');
        });

        // 6. Document Requests (for requesting documents from users)
        Schema::create('document_requests', function (Blueprint $table) {
            $table->id();
            
            // Who is being asked for documents
            $table->string('requestee_type', 50); // 'student', 'applicant', 'faculty'
            $table->unsignedBigInteger('requestee_id');
            
            // What is being requested
            $table->string('document_type', 100);
            $table->string('document_category', 50);
            $table->text('description')->nullable();
            $table->text('requirements')->nullable(); // Specific requirements
            
            // Request details
            $table->string('purpose', 255);
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->date('due_date');
            
            // Status
            $table->enum('status', [
                'pending',
                'notified',
                'in_progress',
                'submitted',
                'approved',
                'rejected',
                'cancelled',
                'expired'
            ])->default('pending');
            
            // Response
            $table->foreignId('document_id')->nullable()->constrained('documents');
            $table->timestamp('submitted_at')->nullable();
            $table->text('response_notes')->nullable();
            
            // Request metadata
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['requestee_type', 'requestee_id']);
            $table->index('status');
            $table->index('due_date');
        });

        // 7. Document Processing Queue (for async processing)
        Schema::create('document_processing_queue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            
            $table->enum('process_type', [
                'virus_scan',
                'ocr',
                'thumbnail',
                'compress',
                'watermark',
                'convert',
                'extract_metadata'
            ]);
            
            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'failed',
                'cancelled'
            ])->default('pending');
            
            $table->integer('attempts')->default(0);
            $table->integer('max_attempts')->default(3);
            $table->json('options')->nullable(); // Processing options
            $table->json('result')->nullable(); // Processing result
            $table->text('error_message')->nullable();
            
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['status', 'created_at']);
            $table->index('document_id');
        });

        // Add document statistics view for reporting
        DB::statement("
            CREATE OR REPLACE VIEW document_statistics AS
            SELECT 
                context,
                category,
                COUNT(*) as total_documents,
                SUM(size) as total_size,
                AVG(size) as avg_size,
                COUNT(DISTINCT uploaded_by) as unique_uploaders,
                COUNT(CASE WHEN status = 'verified' THEN 1 END) as verified_count,
                COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_count,
                COUNT(CASE WHEN status = 'pending_verification' THEN 1 END) as pending_count,
                MAX(created_at) as latest_upload,
                MIN(created_at) as earliest_upload
            FROM documents
            WHERE deleted_at IS NULL
            GROUP BY context, category
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS document_statistics");
        
        Schema::dropIfExists('document_processing_queue');
        Schema::dropIfExists('document_requests');
        Schema::dropIfExists('document_templates');
        Schema::dropIfExists('document_access_logs');
        Schema::dropIfExists('document_versions');
        Schema::dropIfExists('document_relationships');
        Schema::dropIfExists('documents');
    }
};