<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ApplicationDocument extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'application_documents';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'application_id',
        'document_type',
        'document_name',
        'original_filename',
        'file_path',
        'file_type',
        'file_size',
        'file_hash',
        'status',
        'is_verified',
        'verified_by',
        'verified_at',
        'verification_notes',
        'rejection_reason',
        'recommender_name',
        'recommender_email',
        'recommender_title',
        'recommender_institution'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'file_size' => 'integer'
    ];

    /**
     * Document types that require verification.
     */
    protected static $verifiableTypes = [
        'transcript',
        'high_school_transcript',
        'university_transcript',
        'diploma',
        'degree_certificate',
        'test_scores',
        'financial_statement',
        'bank_statement'
    ];

    /**
     * Maximum file sizes in bytes (by document type).
     */
    protected static $maxFileSizes = [
        'transcript' => 10485760, // 10MB
        'portfolio' => 52428800,  // 50MB
        'default' => 5242880      // 5MB
    ];

    /**
     * Allowed MIME types by document type.
     */
    protected static $allowedMimeTypes = [
        'pdf' => 'application/pdf',
        'image' => ['image/jpeg', 'image/png', 'image/gif'],
        'document' => [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ]
    ];

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Generate file hash on creation
        static::creating(function ($document) {
            if ($document->file_path && !$document->file_hash) {
                $document->file_hash = $document->generateFileHash();
            }
        });

        // Clean up file on deletion
        static::deleted(function ($document) {
            if ($document->file_path && Storage::exists($document->file_path)) {
                Storage::delete($document->file_path);
            }
        });
    }

    /**
     * Relationships
     */

    /**
     * Get the application that owns the document.
     */
    public function application()
    {
        return $this->belongsTo(AdmissionApplication::class, 'application_id');
    }

    /**
     * Get the user who verified the document.
     */
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Scopes
     */

    /**
     * Scope for verified documents.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope for pending verification.
     */
    public function scopePendingVerification($query)
    {
        return $query->where('status', 'pending_verification');
    }

    /**
     * Scope for rejected documents.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope for documents by type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('document_type', $type);
    }

    /**
     * Helper Methods
     */

    /**
     * Check if document requires verification.
     */
    public function requiresVerification(): bool
    {
        return in_array($this->document_type, self::$verifiableTypes);
    }

    /**
     * Mark document as verified.
     */
    public function markAsVerified($userId = null, $notes = null): bool
    {
        $this->status = 'verified';
        $this->is_verified = true;
        $this->verified_by = $userId ?? auth()->id();
        $this->verified_at = now();
        $this->verification_notes = $notes;
        $this->rejection_reason = null;
        
        return $this->save();
    }

    /**
     * Reject document.
     */
    public function reject($reason, $userId = null): bool
    {
        $this->status = 'rejected';
        $this->is_verified = false;
        $this->verified_by = $userId ?? auth()->id();
        $this->verified_at = now();
        $this->rejection_reason = $reason;
        
        return $this->save();
    }

    /**
     * Generate file hash for integrity checking.
     */
    public function generateFileHash(): ?string
    {
        if (!$this->file_path || !Storage::exists($this->file_path)) {
            return null;
        }
        
        return hash_file('sha256', Storage::path($this->file_path));
    }

    /**
     * Verify file integrity.
     */
    public function verifyIntegrity(): bool
    {
        if (!$this->file_hash) {
            return true; // No hash to verify against
        }
        
        $currentHash = $this->generateFileHash();
        return $currentHash === $this->file_hash;
    }

    /**
     * Get the maximum allowed file size for this document type.
     */
    public function getMaxFileSize(): int
    {
        return self::$maxFileSizes[$this->document_type] ?? self::$maxFileSizes['default'];
    }

    /**
     * Check if file size is valid.
     */
    public function isFileSizeValid(): bool
    {
        return $this->file_size <= $this->getMaxFileSize();
    }

    /**
     * Get document URL for download.
     */
    public function getDownloadUrl(): ?string
    {
        if (!$this->file_path) {
            return null;
        }
        
        return Storage::url($this->file_path);
    }

    /**
     * Get document thumbnail URL (for images).
     */
    public function getThumbnailUrl(): ?string
    {
        if (!$this->isImage()) {
            return null;
        }
        
        // Generate thumbnail path
        $thumbnailPath = str_replace(
            ['documents/', '.'],
            ['thumbnails/', '_thumb.'],
            $this->file_path
        );
        
        if (Storage::exists($thumbnailPath)) {
            return Storage::url($thumbnailPath);
        }
        
        return $this->getDownloadUrl();
    }

    /**
     * Check if document is an image.
     */
    public function isImage(): bool
    {
        return in_array($this->file_type, self::$allowedMimeTypes['image'] ?? []);
    }

    /**
     * Get human-readable file size.
     */
    public function getHumanFileSize(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get status badge color for UI.
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'uploaded' => 'blue',
            'pending_verification' => 'yellow',
            'verified' => 'green',
            'rejected' => 'red',
            'expired' => 'gray',
            default => 'gray'
        };
    }

    /**
     * Get document type label.
     */
    public function getTypeLabel(): string
    {
        return match($this->document_type) {
            'transcript' => 'Academic Transcript',
            'high_school_transcript' => 'High School Transcript',
            'university_transcript' => 'University Transcript',
            'diploma' => 'Diploma Certificate',
            'degree_certificate' => 'Degree Certificate',
            'test_scores' => 'Test Score Report',
            'recommendation_letter' => 'Letter of Recommendation',
            'personal_statement' => 'Personal Statement',
            'essay' => 'Essay',
            'resume' => 'Resume/CV',
            'portfolio' => 'Portfolio',
            'financial_statement' => 'Financial Statement',
            'bank_statement' => 'Bank Statement',
            'sponsor_letter' => 'Sponsorship Letter',
            'passport' => 'Passport Copy',
            'national_id' => 'National ID',
            'birth_certificate' => 'Birth Certificate',
            'medical_certificate' => 'Medical Certificate',
            'english_proficiency' => 'English Proficiency Certificate',
            default => ucwords(str_replace('_', ' ', $this->document_type))
        };
    }

    /**
     * Check if document is expired (e.g., test scores older than 2 years).
     */
    public function isExpired(): bool
    {
        if ($this->document_type === 'test_scores') {
            return $this->created_at->diffInYears(now()) > 2;
        }
        
        if ($this->document_type === 'medical_certificate') {
            return $this->created_at->diffInMonths(now()) > 6;
        }
        
        return false;
    }

    /**
     * Generate a secure download link with expiration.
     */
    public function generateSecureDownloadLink($expirationMinutes = 60): ?string
    {
        if (!$this->file_path) {
            return null;
        }
        
        return Storage::temporaryUrl(
            $this->file_path,
            now()->addMinutes($expirationMinutes)
        );
    }
}