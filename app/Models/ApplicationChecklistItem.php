<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationChecklistItem extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'application_checklist_items';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'application_id',
        'item_name',
        'item_type',
        'is_required',
        'is_completed',
        'completed_at',
        'notes',
        'sort_order'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_required' => 'boolean',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
        'sort_order' => 'integer'
    ];

    /**
     * Default checklist items for different application types.
     */
    protected static $defaultItems = [
        'freshman' => [
            ['name' => 'High School Transcript', 'type' => 'document', 'required' => true],
            ['name' => 'SAT/ACT Scores', 'type' => 'test_score', 'required' => true],
            ['name' => 'Personal Statement', 'type' => 'document', 'required' => true],
            ['name' => 'Two Letters of Recommendation', 'type' => 'recommendation', 'required' => true],
            ['name' => 'Application Fee', 'type' => 'fee', 'required' => true],
            ['name' => 'Resume/CV', 'type' => 'document', 'required' => false],
            ['name' => 'Portfolio', 'type' => 'document', 'required' => false]
        ],
        'transfer' => [
            ['name' => 'College Transcript', 'type' => 'document', 'required' => true],
            ['name' => 'High School Transcript', 'type' => 'document', 'required' => true],
            ['name' => 'Personal Statement', 'type' => 'document', 'required' => true],
            ['name' => 'One Letter of Recommendation', 'type' => 'recommendation', 'required' => true],
            ['name' => 'Application Fee', 'type' => 'fee', 'required' => true],
            ['name' => 'Course Descriptions', 'type' => 'document', 'required' => false]
        ],
        'graduate' => [
            ['name' => 'Bachelor\'s Transcript', 'type' => 'document', 'required' => true],
            ['name' => 'GRE/GMAT Scores', 'type' => 'test_score', 'required' => true],
            ['name' => 'Statement of Purpose', 'type' => 'document', 'required' => true],
            ['name' => 'Three Letters of Recommendation', 'type' => 'recommendation', 'required' => true],
            ['name' => 'Resume/CV', 'type' => 'document', 'required' => true],
            ['name' => 'Application Fee', 'type' => 'fee', 'required' => true],
            ['name' => 'Writing Sample', 'type' => 'document', 'required' => false],
            ['name' => 'Research Proposal', 'type' => 'document', 'required' => false]
        ],
        'international' => [
            ['name' => 'Academic Transcripts', 'type' => 'document', 'required' => true],
            ['name' => 'TOEFL/IELTS Scores', 'type' => 'test_score', 'required' => true],
            ['name' => 'Passport Copy', 'type' => 'document', 'required' => true],
            ['name' => 'Financial Statement', 'type' => 'document', 'required' => true],
            ['name' => 'Personal Statement', 'type' => 'document', 'required' => true],
            ['name' => 'Letters of Recommendation', 'type' => 'recommendation', 'required' => true],
            ['name' => 'Application Fee', 'type' => 'fee', 'required' => true],
            ['name' => 'Credential Evaluation', 'type' => 'document', 'required' => false]
        ]
    ];

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Set completed_at when marking as completed
        static::updating(function ($item) {
            if ($item->isDirty('is_completed')) {
                if ($item->is_completed && !$item->completed_at) {
                    $item->completed_at = now();
                } elseif (!$item->is_completed) {
                    $item->completed_at = null;
                }
            }
        });
    }

    /**
     * Relationships
     */

    /**
     * Get the application that owns the checklist item.
     */
    public function application()
    {
        return $this->belongsTo(AdmissionApplication::class, 'application_id');
    }

    /**
     * Scopes
     */

    /**
     * Scope for required items.
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    /**
     * Scope for optional items.
     */
    public function scopeOptional($query)
    {
        return $query->where('is_required', false);
    }

    /**
     * Scope for completed items.
     */
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    /**
     * Scope for pending items.
     */
    public function scopePending($query)
    {
        return $query->where('is_completed', false);
    }

    /**
     * Scope for items by type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('item_type', $type);
    }

    /**
     * Scope for ordered items.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('is_required', 'desc');
    }

    /**
     * Helper Methods
     */

    /**
     * Mark item as completed.
     */
    public function markAsCompleted($notes = null): bool
    {
        $this->is_completed = true;
        $this->completed_at = now();
        
        if ($notes) {
            $this->notes = $notes;
        }
        
        return $this->save();
    }

    /**
     * Mark item as pending.
     */
    public function markAsPending($notes = null): bool
    {
        $this->is_completed = false;
        $this->completed_at = null;
        
        if ($notes) {
            $this->notes = $notes;
        }
        
        return $this->save();
    }

    /**
     * Toggle completion status.
     */
    public function toggleCompletion(): bool
    {
        $this->is_completed = !$this->is_completed;
        
        if ($this->is_completed) {
            $this->completed_at = now();
        } else {
            $this->completed_at = null;
        }
        
        return $this->save();
    }

    /**
     * Get item type label.
     */
    public function getTypeLabel(): string
    {
        return match($this->item_type) {
            'document' => 'Document',
            'form' => 'Form',
            'fee' => 'Fee Payment',
            'test_score' => 'Test Score',
            'recommendation' => 'Recommendation Letter',
            'interview' => 'Interview',
            'other' => 'Other',
            default => ucfirst($this->item_type)
        };
    }

    /**
     * Get item type icon (for UI).
     */
    public function getTypeIcon(): string
    {
        return match($this->item_type) {
            'document' => 'document-text',
            'form' => 'clipboard-list',
            'fee' => 'credit-card',
            'test_score' => 'academic-cap',
            'recommendation' => 'mail',
            'interview' => 'video-camera',
            'other' => 'question-mark-circle',
            default => 'document'
        };
    }

    /**
     * Get status color for UI.
     */
    public function getStatusColor(): string
    {
        if ($this->is_completed) {
            return 'green';
        }
        
        return $this->is_required ? 'red' : 'yellow';
    }

    /**
     * Check if item is overdue.
     */
    public function isOverdue(): bool
    {
        if ($this->is_completed) {
            return false;
        }
        
        // Check if application has a deadline
        $application = $this->application;
        if ($application && $application->expires_at) {
            $daysUntilDeadline = now()->diffInDays($application->expires_at, false);
            return $daysUntilDeadline < 7; // Consider overdue if less than 7 days
        }
        
        return false;
    }

    /**
     * Create default checklist for application.
     */
    public static function createDefaultChecklist($applicationId, $applicationType): void
    {
        $items = self::$defaultItems[$applicationType] ?? self::$defaultItems['freshman'];
        
        foreach ($items as $index => $item) {
            self::create([
                'application_id' => $applicationId,
                'item_name' => $item['name'],
                'item_type' => $item['type'],
                'is_required' => $item['required'],
                'is_completed' => false,
                'sort_order' => ($index + 1) * 10
            ]);
        }
    }

    /**
     * Update checklist based on documents.
     */
    public function updateFromDocuments(): bool
    {
        // Map document types to checklist items
        $documentMap = [
            'transcript' => ['High School Transcript', 'College Transcript', 'Academic Transcripts'],
            'test_scores' => ['SAT/ACT Scores', 'GRE/GMAT Scores', 'TOEFL/IELTS Scores'],
            'personal_statement' => ['Personal Statement', 'Statement of Purpose'],
            'recommendation_letter' => ['Letters of Recommendation', 'One Letter of Recommendation', 'Two Letters of Recommendation', 'Three Letters of Recommendation'],
            'resume' => ['Resume/CV'],
            'portfolio' => ['Portfolio'],
            'financial_statement' => ['Financial Statement'],
            'passport' => ['Passport Copy']
        ];
        
        $updated = false;
        
        foreach ($documentMap as $docType => $itemNames) {
            if (in_array($this->item_name, $itemNames)) {
                $hasDocument = $this->application->documents()
                    ->where('document_type', $docType)
                    ->where('status', 'verified')
                    ->exists();
                
                if ($hasDocument && !$this->is_completed) {
                    $this->markAsCompleted('Auto-completed based on verified document');
                    $updated = true;
                }
            }
        }
        
        // Check for application fee
        if ($this->item_name === 'Application Fee' && !$this->is_completed) {
            if ($this->application->application_fee_paid) {
                $this->markAsCompleted('Application fee paid');
                $updated = true;
            }
        }
        
        return $updated;
    }

    /**
     * Get completion statistics for an application.
     */
    public static function getCompletionStats($applicationId): array
    {
        $items = self::where('application_id', $applicationId)->get();
        
        $stats = [
            'total' => $items->count(),
            'required' => $items->where('is_required', true)->count(),
            'optional' => $items->where('is_required', false)->count(),
            'completed' => $items->where('is_completed', true)->count(),
            'pending' => $items->where('is_completed', false)->count(),
            'required_completed' => $items->where('is_required', true)->where('is_completed', true)->count(),
            'required_pending' => $items->where('is_required', true)->where('is_completed', false)->count(),
        ];
        
        $stats['completion_percentage'] = $stats['total'] > 0 
            ? round(($stats['completed'] / $stats['total']) * 100) 
            : 0;
            
        $stats['required_completion_percentage'] = $stats['required'] > 0 
            ? round(($stats['required_completed'] / $stats['required']) * 100) 
            : 0;
        
        return $stats;
    }
}