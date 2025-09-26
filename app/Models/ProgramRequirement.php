<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramRequirement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'program_id',
        'requirement_id',
        'catalog_year',
        'program_parameters',
        'credits_required',
        'courses_required',
        'applies_to',
        'concentration_code',
        'is_active'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'program_parameters' => 'array',
        'credits_required' => 'decimal:1',
        'courses_required' => 'integer',
        'is_active' => 'boolean'
    ];

    /**
     * Get the program
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(AcademicProgram::class, 'program_id');
    }

    /**
     * Get the degree requirement
     */
    public function requirement(): BelongsTo
    {
        return $this->belongsTo(DegreeRequirement::class, 'requirement_id');
    }

    /**
     * Get student progress for this program requirement
     */
    public function studentProgress(): HasMany
    {
        return $this->hasMany(StudentDegreeProgress::class, 'program_requirement_id');
    }

    /**
     * Get the effective parameters for this requirement
     * Merges base requirement parameters with program-specific overrides
     * 
     * FIXED: Properly handle JSON string parameters
     */
    public function getEffectiveParameters(): array
    {
        // Get base parameters from the requirement
        $baseParams = [];
        if ($this->requirement) {
            $params = $this->requirement->parameters;
            
            // Handle if parameters is a JSON string
            if (is_string($params)) {
                $baseParams = json_decode($params, true) ?? [];
            } elseif (is_array($params)) {
                $baseParams = $params;
            }
        }
        
        // Get program-specific overrides
        $programParams = [];
        if ($this->program_parameters) {
            // Handle if program_parameters is a JSON string
            if (is_string($this->program_parameters)) {
                $programParams = json_decode($this->program_parameters, true) ?? [];
            } elseif (is_array($this->program_parameters)) {
                $programParams = $this->program_parameters;
            }
        }
        
        // Merge parameters (program overrides base)
        return array_merge($baseParams, $programParams);
    }

    /**
     * Check if this requirement applies to a student
     */
    public function appliesToStudent(Student $student): bool
    {
        switch ($this->applies_to) {
            case 'all':
                return true;
                
            case 'major_only':
                return $student->program_id === $this->program_id;
                
            case 'minor_only':
                // Check if student has this program as minor
                return false; // Implement minor check logic
                
            case 'concentration':
                // Check if student has this concentration
                return $this->concentration_code === $student->concentration_code;
                
            default:
                return true;
        }
    }

    /**
     * Get the credits required for this requirement
     */
    public function getCreditsRequired(): ?float
    {
        // Use program-specific override if set
        if ($this->credits_required !== null) {
            return floatval($this->credits_required);
        }
        
        // Otherwise get from requirement parameters
        $params = $this->getEffectiveParameters();
        return $params['min_credits'] ?? null;
    }

    /**
     * Get the courses required for this requirement
     */
    public function getCoursesRequired(): ?int
    {
        // Use program-specific override if set
        if ($this->courses_required !== null) {
            return intval($this->courses_required);
        }
        
        // Otherwise get from requirement parameters
        $params = $this->getEffectiveParameters();
        return $params['min_courses'] ?? null;
    }

    /**
     * Scope for active requirements
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by catalog year
     */
    public function scopeForCatalogYear($query, string $catalogYear)
    {
        return $query->where('catalog_year', $catalogYear);
    }

    /**
     * Scope by program
     */
    public function scopeForProgram($query, int $programId)
    {
        return $query->where('program_id', $programId);
    }
}