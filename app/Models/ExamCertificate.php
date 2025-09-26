<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;

class ExamCertificate extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'exam_certificates';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'result_id',
        'registration_id',
        'certificate_number',
        'certificate_type',
        'file_path',
        'verification_code',
        'qr_code_path',
        'issued_at',
        'issued_by',
        'is_downloaded',
        'first_downloaded_at',
        'download_count',
        'valid_until',
        'is_revoked',
        'revoked_at',
        'revoked_by',
        'revoke_reason',
        'metadata'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'issued_at' => 'datetime',
        'first_downloaded_at' => 'datetime',
        'download_count' => 'integer',
        'is_downloaded' => 'boolean',
        'valid_until' => 'date',
        'is_revoked' => 'boolean',
        'revoked_at' => 'datetime',
        'metadata' => 'array'
    ];

    /**
     * Certificate types.
     */
    const TYPES = [
        'merit' => 'Merit Certificate',
        'participation' => 'Participation Certificate',
        'qualification' => 'Qualification Certificate',
        'excellence' => 'Certificate of Excellence',
        'completion' => 'Completion Certificate',
        'rank' => 'Rank Certificate',
        'appreciation' => 'Appreciation Certificate'
    ];

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Generate certificate number and verification code
        static::creating(function ($certificate) {
            if (!$certificate->certificate_number) {
                $certificate->certificate_number = self::generateCertificateNumber();
            }
            
            if (!$certificate->verification_code) {
                $certificate->verification_code = self::generateVerificationCode();
            }
            
            if (!$certificate->issued_at) {
                $certificate->issued_at = now();
            }
            
            if (!$certificate->issued_by) {
                $certificate->issued_by = auth()->id();
            }

            // Set validity period (default 5 years)
            if (!$certificate->valid_until) {
                $certificate->valid_until = now()->addYears(5);
            }
        });

        // Generate certificate files after creation
        static::created(function ($certificate) {
            $certificate->generateCertificate();
        });
    }

    /**
     * Relationships
     */

    /**
     * Get the exam result.
     */
    public function result()
    {
        return $this->belongsTo(EntranceExamResult::class, 'result_id');
    }

    /**
     * Get the registration.
     */
    public function registration()
    {
        return $this->belongsTo(EntranceExamRegistration::class, 'registration_id');
    }

    /**
     * Get the issuer.
     */
    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    /**
     * Get the revoker.
     */
    public function revoker()
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    /**
     * Scopes
     */

    /**
     * Scope for valid certificates.
     */
    public function scopeValid($query)
    {
        return $query->where('is_revoked', false)
            ->where('valid_until', '>=', now());
    }

    /**
     * Scope for revoked certificates.
     */
    public function scopeRevoked($query)
    {
        return $query->where('is_revoked', true);
    }

    /**
     * Scope for downloaded certificates.
     */
    public function scopeDownloaded($query)
    {
        return $query->where('is_downloaded', true);
    }

    /**
     * Scope for certificates by type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('certificate_type', $type);
    }

    /**
     * Helper Methods
     */

    /**
     * Generate unique certificate number.
     */
    public static function generateCertificateNumber(): string
    {
        $prefix = 'CERT';
        $year = date('Y');
        
        $lastCertificate = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastCertificate) {
            $lastNumber = intval(substr($lastCertificate->certificate_number, -6));
            $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '000001';
        }
        
        return "{$prefix}-{$year}-{$newNumber}";
    }

    /**
     * Generate unique verification code.
     */
    public static function generateVerificationCode(): string
    {
        do {
            $code = strtoupper(Str::random(12));
        } while (self::where('verification_code', $code)->exists());
        
        return $code;
    }

    /**
     * Generate the certificate PDF and QR code.
     */
    public function generateCertificate(): bool
    {
        try {
            // Generate QR code
            $this->generateQRCode();
            
            // Generate PDF certificate
            $this->generatePDF();
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Certificate generation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate QR code for verification.
     */
    protected function generateQRCode(): void
    {
        $verificationUrl = route('certificates.verify', $this->verification_code);
        
        $qrCode = QrCode::format('png')
            ->size(200)
            ->margin(10)
            ->generate($verificationUrl);
        
        $filename = "certificates/qr/{$this->certificate_number}.png";
        
        // Save QR code
        \Storage::disk('public')->put($filename, $qrCode);
        
        $this->qr_code_path = $filename;
        $this->saveQuietly();
    }

    /**
     * Generate PDF certificate.
     */
    protected function generatePDF(): void
    {
        $data = $this->getCertificateData();
        
        $pdf = Pdf::loadView('certificates.' . $this->certificate_type, $data)
            ->setPaper('a4', 'landscape')
            ->setOption('margin-top', 0)
            ->setOption('margin-bottom', 0)
            ->setOption('margin-left', 0)
            ->setOption('margin-right', 0);
        
        $filename = "certificates/pdf/{$this->certificate_number}.pdf";
        
        // Save PDF
        \Storage::disk('public')->put($filename, $pdf->output());
        
        $this->file_path = $filename;
        $this->saveQuietly();
    }

    /**
     * Get data for certificate generation.
     */
    protected function getCertificateData(): array
    {
        $result = $this->result;
        $registration = $this->registration;
        $exam = $result->exam;
        
        // Get candidate name from registration or application
        $candidateName = 'Unknown';
        if ($registration->application) {
            $app = $registration->application;
            $candidateName = $app->first_name . ' ' . $app->middle_name . ' ' . $app->last_name;
        } elseif ($registration->candidate_name) {
            $candidateName = $registration->candidate_name;
        }

        return [
            'certificate_number' => $this->certificate_number,
            'certificate_type' => $this->certificate_type,
            'candidate_name' => trim($candidateName),
            'exam_name' => $exam->exam_name,
            'exam_date' => $exam->exam_date?->format('F d, Y'),
            'score' => $result->final_score,
            'total_marks' => $exam->total_marks,
            'percentage' => $result->percentage,
            'rank' => $result->overall_rank,
            'grade' => $result->getGrade(),
            'performance' => $result->getPerformanceLevel(),
            'issued_date' => $this->issued_at->format('F d, Y'),
            'verification_code' => $this->verification_code,
            'qr_code_path' => $this->qr_code_path,
            'institution_name' => config('app.institution_name', 'IntelliCampus University'),
            'institution_logo' => config('app.institution_logo'),
            'signatures' => $this->getSignatures(),
            'watermark' => $this->getWatermark()
        ];
    }

    /**
     * Get signatures for certificate.
     */
    protected function getSignatures(): array
    {
        return [
            'registrar' => [
                'name' => 'Dr. John Smith',
                'title' => 'Registrar',
                'signature' => 'signatures/registrar.png'
            ],
            'director' => [
                'name' => 'Prof. Jane Doe',
                'title' => 'Director of Examinations',
                'signature' => 'signatures/director.png'
            ]
        ];
    }

    /**
     * Get watermark for certificate.
     */
    protected function getWatermark(): ?string
    {
        if ($this->is_revoked) {
            return 'REVOKED';
        }
        
        if ($this->valid_until < now()) {
            return 'EXPIRED';
        }
        
        return null;
    }

    /**
     * Download the certificate.
     */
    public function download(): ?string
    {
        if (!$this->file_path || !\Storage::disk('public')->exists($this->file_path)) {
            // Regenerate if missing
            $this->generateCertificate();
        }

        // Update download tracking
        if (!$this->is_downloaded) {
            $this->is_downloaded = true;
            $this->first_downloaded_at = now();
        }
        
        $this->download_count++;
        $this->save();

        return \Storage::disk('public')->path($this->file_path);
    }

    /**
     * Verify certificate authenticity.
     */
    public static function verify($verificationCode): ?array
    {
        $certificate = self::where('verification_code', $verificationCode)->first();
        
        if (!$certificate) {
            return [
                'valid' => false,
                'message' => 'Certificate not found'
            ];
        }

        if ($certificate->is_revoked) {
            return [
                'valid' => false,
                'message' => 'Certificate has been revoked',
                'revoked_at' => $certificate->revoked_at->format('Y-m-d'),
                'reason' => $certificate->revoke_reason
            ];
        }

        if ($certificate->valid_until < now()) {
            return [
                'valid' => false,
                'message' => 'Certificate has expired',
                'expired_on' => $certificate->valid_until->format('Y-m-d')
            ];
        }

        return [
            'valid' => true,
            'certificate_number' => $certificate->certificate_number,
            'type' => $certificate->certificate_type,
            'issued_to' => $certificate->getCandidateName(),
            'issued_on' => $certificate->issued_at->format('Y-m-d'),
            'valid_until' => $certificate->valid_until->format('Y-m-d'),
            'exam_details' => $certificate->getExamDetails()
        ];
    }

    /**
     * Revoke the certificate.
     */
    public function revoke($reason, $revokedBy = null): bool
    {
        $this->is_revoked = true;
        $this->revoked_at = now();
        $this->revoked_by = $revokedBy ?? auth()->id();
        $this->revoke_reason = $reason;
        
        // Regenerate with REVOKED watermark
        $this->generateCertificate();
        
        return $this->save();
    }

    /**
     * Restore revoked certificate.
     */
    public function restore(): bool
    {
        $this->is_revoked = false;
        $this->revoked_at = null;
        $this->revoked_by = null;
        $this->revoke_reason = null;
        
        // Regenerate without watermark
        $this->generateCertificate();
        
        return $this->save();
    }

    /**
     * Extend validity period.
     */
    public function extendValidity($years = 1): bool
    {
        $this->valid_until = $this->valid_until->addYears($years);
        return $this->save();
    }

    /**
     * Get candidate name.
     */
    public function getCandidateName(): string
    {
        if ($this->registration->application) {
            $app = $this->registration->application;
            return trim($app->first_name . ' ' . $app->middle_name . ' ' . $app->last_name);
        }
        
        return $this->registration->candidate_name ?? 'Unknown';
    }

    /**
     * Get exam details for verification.
     */
    public function getExamDetails(): array
    {
        $result = $this->result;
        $exam = $result->exam;
        
        return [
            'exam_name' => $exam->exam_name,
            'exam_date' => $exam->exam_date?->format('Y-m-d'),
            'score' => $result->final_score . '/' . $exam->total_marks,
            'percentage' => number_format($result->percentage, 2) . '%',
            'rank' => $result->overall_rank,
            'status' => $result->result_status
        ];
    }

    /**
     * Check if certificate can be issued.
     */
    public static function canIssue(EntranceExamResult $result): bool
    {
        // Check if result is published
        if (!$result->is_published) {
            return false;
        }

        // Check if certificate already exists
        if (self::where('result_id', $result->id)->exists()) {
            return false;
        }

        // Check minimum criteria based on certificate type
        // This can be customized based on requirements
        
        return true;
    }

    /**
     * Determine certificate type based on performance.
     */
    public static function determineCertificateType(EntranceExamResult $result): string
    {
        // Excellence for top 5%
        if ($result->percentile >= 95) {
            return 'excellence';
        }
        
        // Merit for top 20%
        if ($result->percentile >= 80) {
            return 'merit';
        }
        
        // Qualification for passing candidates
        if ($result->is_qualified) {
            return 'qualification';
        }
        
        // Participation for all others
        return 'participation';
    }

    /**
     * Generate bulk certificates for an exam.
     */
    public static function generateBulkCertificates($examId, $criteria = []): int
    {
        $query = EntranceExamResult::where('exam_id', $examId)
            ->where('is_published', true);

        // Apply criteria
        if (isset($criteria['min_percentage'])) {
            $query->where('percentage', '>=', $criteria['min_percentage']);
        }
        
        if (isset($criteria['result_status'])) {
            $query->where('result_status', $criteria['result_status']);
        }

        $results = $query->get();
        $generated = 0;

        foreach ($results as $result) {
            if (self::canIssue($result)) {
                $certificate = self::create([
                    'result_id' => $result->id,
                    'registration_id' => $result->registration_id,
                    'certificate_type' => self::determineCertificateType($result)
                ]);
                
                if ($certificate) {
                    $generated++;
                }
            }
        }

        return $generated;
    }

    /**
     * Get certificate statistics.
     */
    public static function getStatistics($examId = null): array
    {
        $query = self::query();
        
        if ($examId) {
            $query->whereHas('result', function ($q) use ($examId) {
                $q->where('exam_id', $examId);
            });
        }

        return [
            'total_issued' => $query->count(),
            'downloaded' => $query->where('is_downloaded', true)->count(),
            'valid' => $query->valid()->count(),
            'revoked' => $query->revoked()->count(),
            'by_type' => $query->groupBy('certificate_type')
                ->selectRaw('certificate_type, count(*) as count')
                ->pluck('count', 'certificate_type'),
            'total_downloads' => $query->sum('download_count')
        ];
    }
}