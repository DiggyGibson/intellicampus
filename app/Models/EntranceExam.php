<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EntranceExam extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'entrance_exams';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'exam_code',
        'exam_name',
        'description',
        'exam_type',
        'delivery_mode',
        'term_id',
        'applicable_programs',
        'applicable_application_types',
        'total_marks',
        'passing_marks',
        'duration_minutes',
        'exam_start_time',
        'exam_end_time',
        'total_questions',
        'sections',
        'general_instructions',
        'exam_rules',
        'allowed_materials',
        'negative_marking',
        'negative_mark_value',
        'registration_start_date',
        'registration_end_date',
        'exam_date',
        'exam_window_start',
        'exam_window_end',
        'result_publish_date',
        'show_detailed_results',
        'allow_result_review',
        'review_period_days',
        'status',
        'is_active'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'applicable_programs' => 'array',
        'applicable_application_types' => 'array',
        'sections' => 'array',
        'allowed_materials' => 'array',
        'negative_marking' => 'boolean',
        'negative_mark_value' => 'decimal:2',
        'show_detailed_results' => 'boolean',
        'allow_result_review' => 'boolean',
        'is_active' => 'boolean',
        'exam_start_time' => 'datetime:H:i',
        'exam_end_time' => 'datetime:H:i',
        'registration_start_date' => 'date',
        'registration_end_date' => 'date',
        'exam_date' => 'date',
        'exam_window_start' => 'date',
        'exam_window_end' => 'date',
        'result_publish_date' => 'date'
    ];

    /**
     * Default exam configurations by type.
     */
    protected static $defaultConfigurations = [
        'entrance' => [
            'duration_minutes' => 180,
            'total_questions' => 100,
            'total_marks' => 100,
            'passing_marks' => 40,
            'negative_marking' => true,
            'negative_mark_value' => 0.25
        ],
        'placement' => [
            'duration_minutes' => 120,
            'total_questions' => 60,
            'total_marks' => 60,
            'passing_marks' => 30,
            'negative_marking' => false
        ],
        'scholarship' => [
            'duration_minutes' => 150,
            'total_questions' => 75,
            'total_marks' => 100,
            'passing_marks' => 60,
            'negative_marking' => true,
            'negative_mark_value' => 0.33
        ]
    ];

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Generate exam code if not provided
        static::creating(function ($exam) {
            if (!$exam->exam_code) {
                $exam->exam_code = self::generateExamCode($exam->exam_type);
            }
            
            // Set default status
            if (!$exam->status) {
                $exam->status = 'draft';
            }
        });

        // Update status based on dates
        static::saving(function ($exam) {
            $exam->updateStatusBasedOnDates();
        });
    }

    /**
     * Relationships
     */

    /**
     * Get the term for this exam.
     */
    public function term()
    {
        return $this->belongsTo(AcademicTerm::class, 'term_id');
    }

    /**
     * Get the programs applicable for this exam.
     */
    public function programs()
    {
        if ($this->applicable_programs) {
            return AcademicProgram::whereIn('id', $this->applicable_programs)->get();
        }
        return collect();
    }

    /**
     * Get registrations for this exam.
     */
    public function registrations()
    {
        return $this->hasMany(EntranceExamRegistration::class, 'exam_id');
    }

    /**
     * Get sessions for this exam.
     */
    public function sessions()
    {
        return $this->hasMany(ExamSession::class, 'exam_id');
    }

    /**
     * Get questions for this exam.
     */
    public function questions()
    {
        return $this->hasMany(ExamQuestion::class, 'exam_id');
    }

    /**
     * Get question papers for this exam.
     */
    public function questionPapers()
    {
        return $this->hasMany(ExamQuestionPaper::class, 'exam_id');
    }

    /**
     * Get results for this exam.
     */
    public function results()
    {
        return $this->hasMany(EntranceExamResult::class, 'exam_id');
    }

    /**
     * Get answer keys for this exam.
     */
    public function answerKeys()
    {
        return $this->hasMany(ExamAnswerKey::class, 'exam_id');
    }

    /**
     * Scopes
     */

    /**
     * Scope for active exams.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for published exams.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope for exams open for registration.
     */
    public function scopeOpenForRegistration($query)
    {
        return $query->where('status', 'registration_open')
            ->where('registration_start_date', '<=', now())
            ->where('registration_end_date', '>=', now());
    }

    /**
     * Scope for upcoming exams.
     */
    public function scopeUpcoming($query)
    {
        return $query->where(function ($q) {
            $q->where('exam_date', '>', now())
              ->orWhere('exam_window_start', '>', now());
        });
    }

    /**
     * Scope for exams by type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('exam_type', $type);
    }

    /**
     * Scope for exams by delivery mode.
     */
    public function scopeByDeliveryMode($query, $mode)
    {
        return $query->where('delivery_mode', $mode);
    }

    /**
     * Helper Methods
     */

    /**
     * Generate unique exam code.
     */
    public static function generateExamCode($type = 'entrance'): string
    {
        $prefix = match($type) {
            'entrance' => 'ENT',
            'placement' => 'PLC',
            'diagnostic' => 'DGN',
            'scholarship' => 'SCH',
            'transfer_credit' => 'TRC',
            'exemption' => 'EXM',
            default => 'EXM'
        };
        
        $year = date('Y');
        
        $lastExam = self::where('exam_code', 'like', "{$prefix}-{$year}-%")
            ->orderBy('exam_code', 'desc')
            ->first();
        
        if ($lastExam) {
            $lastNumber = intval(substr($lastExam->exam_code, -3));
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }
        
        return "{$prefix}-{$year}-{$newNumber}";
    }

    /**
     * Update status based on dates.
     */
    public function updateStatusBasedOnDates(): void
    {
        $now = now();
        
        // Skip if manually set to certain statuses
        if (in_array($this->status, ['draft', 'archived', 'cancelled'])) {
            return;
        }
        
        // Check registration period
        if ($this->registration_start_date && $this->registration_end_date) {
            if ($now >= $this->registration_start_date && $now <= $this->registration_end_date) {
                if ($this->status === 'published') {
                    $this->status = 'registration_open';
                }
            } elseif ($now > $this->registration_end_date && $this->status === 'registration_open') {
                $this->status = 'registration_closed';
            }
        }
        
        // Check exam period
        if ($this->exam_date) {
            if ($now->toDateString() === $this->exam_date->toDateString()) {
                $this->status = 'in_progress';
            } elseif ($now > $this->exam_date && $this->status === 'in_progress') {
                $this->status = 'completed';
            }
        } elseif ($this->exam_window_start && $this->exam_window_end) {
            if ($now >= $this->exam_window_start && $now <= $this->exam_window_end) {
                if (!in_array($this->status, ['in_progress', 'completed'])) {
                    $this->status = 'in_progress';
                }
            } elseif ($now > $this->exam_window_end) {
                $this->status = 'completed';
            }
        }
        
        // Check result publication
        if ($this->result_publish_date && $now >= $this->result_publish_date) {
            if ($this->status === 'results_pending') {
                $this->status = 'results_published';
            }
        }
    }

    /**
     * Publish the exam.
     */
    public function publish(): bool
    {
        if ($this->status !== 'draft') {
            return false;
        }
        
        $this->status = 'published';
        return $this->save();
    }

    /**
     * Open registration.
     */
    public function openRegistration(): bool
    {
        if ($this->status !== 'published') {
            return false;
        }
        
        $this->status = 'registration_open';
        return $this->save();
    }

    /**
     * Close registration.
     */
    public function closeRegistration(): bool
    {
        if ($this->status !== 'registration_open') {
            return false;
        }
        
        $this->status = 'registration_closed';
        return $this->save();
    }

    /**
     * Check if registration is open.
     */
    public function isRegistrationOpen(): bool
    {
        return $this->status === 'registration_open' &&
               $this->registration_start_date <= now() &&
               $this->registration_end_date >= now();
    }

    /**
     * Check if exam is in progress.
     */
    public function isInProgress(): bool
    {
        if ($this->exam_date) {
            return $this->exam_date->isToday() && 
                   now()->between(
                       $this->exam_date->copy()->setTimeFromTimeString($this->exam_start_time),
                       $this->exam_date->copy()->setTimeFromTimeString($this->exam_end_time)
                   );
        }
        
        if ($this->exam_window_start && $this->exam_window_end) {
            return now()->between($this->exam_window_start, $this->exam_window_end);
        }
        
        return false;
    }

    /**
     * Get registration statistics.
     */
    public function getRegistrationStats(): array
    {
        $registrations = $this->registrations;
        
        return [
            'total_registrations' => $registrations->count(),
            'confirmed_registrations' => $registrations->where('registration_status', 'confirmed')->count(),
            'pending_registrations' => $registrations->where('registration_status', 'pending')->count(),
            'cancelled_registrations' => $registrations->where('registration_status', 'cancelled')->count(),
            'fee_paid' => $registrations->where('fee_paid', true)->count(),
            'accommodations_requested' => $registrations->where('requires_accommodation', true)->count()
        ];
    }

    /**
     * Get exam statistics.
     */
    public function getExamStats(): array
    {
        $results = $this->results;
        
        if ($results->isEmpty()) {
            return [
                'total_appeared' => 0,
                'passed' => 0,
                'failed' => 0,
                'pass_percentage' => 0,
                'average_score' => 0,
                'highest_score' => 0,
                'lowest_score' => 0
            ];
        }
        
        return [
            'total_appeared' => $results->count(),
            'passed' => $results->where('result_status', 'pass')->count(),
            'failed' => $results->where('result_status', 'fail')->count(),
            'pass_percentage' => round(($results->where('result_status', 'pass')->count() / $results->count()) * 100, 2),
            'average_score' => round($results->avg('final_score'), 2),
            'highest_score' => $results->max('final_score'),
            'lowest_score' => $results->min('final_score')
        ];
    }

    /**
     * Calculate total duration including all sections.
     */
    public function getTotalDuration(): int
    {
        if ($this->sections) {
            $totalDuration = 0;
            foreach ($this->sections as $section) {
                $totalDuration += $section['duration'] ?? 0;
            }
            return $totalDuration ?: $this->duration_minutes;
        }
        
        return $this->duration_minutes;
    }

    /**
     * Get exam type label.
     */
    public function getTypeLabel(): string
    {
        return match($this->exam_type) {
            'entrance' => 'Entrance Examination',
            'placement' => 'Placement Test',
            'diagnostic' => 'Diagnostic Assessment',
            'scholarship' => 'Scholarship Examination',
            'transfer_credit' => 'Transfer Credit Assessment',
            'exemption' => 'Exemption Test',
            default => ucwords(str_replace('_', ' ', $this->exam_type))
        };
    }

    /**
     * Get delivery mode label.
     */
    public function getDeliveryModeLabel(): string
    {
        return match($this->delivery_mode) {
            'paper_based' => 'Paper-Based',
            'computer_based' => 'Computer-Based Test (CBT)',
            'online_proctored' => 'Online Proctored',
            'online_unproctored' => 'Online Unproctored',
            'hybrid' => 'Hybrid Mode',
            'take_home' => 'Take-Home Exam',
            default => ucwords(str_replace('_', ' ', $this->delivery_mode))
        };
    }

    /**
     * Get status color for UI.
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'published' => 'blue',
            'registration_open' => 'green',
            'registration_closed' => 'yellow',
            'in_progress' => 'purple',
            'completed' => 'indigo',
            'results_pending' => 'orange',
            'results_published' => 'teal',
            'archived' => 'gray',
            default => 'gray'
        };
    }

    /**
     * Generate exam summary.
     */
    public function generateSummary(): array
    {
        return [
            'exam_code' => $this->exam_code,
            'exam_name' => $this->exam_name,
            'type' => $this->exam_type,
            'delivery_mode' => $this->delivery_mode,
            'status' => $this->status,
            'structure' => [
                'total_questions' => $this->total_questions,
                'total_marks' => $this->total_marks,
                'passing_marks' => $this->passing_marks,
                'duration_minutes' => $this->duration_minutes,
                'negative_marking' => $this->negative_marking,
                'negative_mark_value' => $this->negative_mark_value
            ],
            'dates' => [
                'registration_start' => $this->registration_start_date?->format('Y-m-d'),
                'registration_end' => $this->registration_end_date?->format('Y-m-d'),
                'exam_date' => $this->exam_date?->format('Y-m-d'),
                'result_date' => $this->result_publish_date?->format('Y-m-d')
            ],
            'statistics' => array_merge(
                $this->getRegistrationStats(),
                $this->getExamStats()
            )
        ];
    }
}