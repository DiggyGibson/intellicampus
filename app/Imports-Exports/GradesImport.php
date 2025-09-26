<?php

namespace App\ImportsExports;

use App\Models\Grade;
use App\Models\Enrollment;
use App\Models\GradeComponent;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GradesImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading, SkipsOnError, SkipsOnFailure
{
    use Importable, SkipsErrors, SkipsFailures;

    protected $sectionId;
    protected $componentId;
    protected $gradedBy;
    protected $component;
    protected $results;
    protected $successCount = 0;
    protected $errorCount = 0;
    protected $errors = [];
    protected $successful = [];

    public function __construct($sectionId, $componentId, $gradedBy)
    {
        $this->sectionId = $sectionId;
        $this->componentId = $componentId;
        $this->gradedBy = $gradedBy;
        $this->component = GradeComponent::find($componentId);
        $this->results = [
            'successful' => [],
            'errors' => [],
            'success_count' => 0,
            'error_count' => 0
        ];
    }

    /**
     * Process each row from the Excel file
     */
    public function model(array $row)
    {
        // Skip empty rows
        if (empty($row['enrollment_id']) || empty($row['points_earned'])) {
            return null;
        }

        // Validate enrollment exists and belongs to this section
        $enrollment = Enrollment::where('id', $row['enrollment_id'])
            ->where('section_id', $this->sectionId)
            ->first();

        if (!$enrollment) {
            $this->errorCount++;
            $this->errors[] = "Invalid enrollment ID: {$row['enrollment_id']}";
            return null;
        }

        // Validate points earned
        $pointsEarned = floatval($row['points_earned']);
        if ($pointsEarned < 0 || $pointsEarned > $this->component->max_points) {
            $this->errorCount++;
            $this->errors[] = "Invalid points for enrollment {$row['enrollment_id']}: Points must be between 0 and {$this->component->max_points}";
            return null;
        }

        try {
            DB::beginTransaction();

            // Calculate percentage
            $percentage = $this->component->max_points > 0 
                ? ($pointsEarned / $this->component->max_points) * 100 
                : 0;

            // Calculate letter grade
            $letterGrade = $this->calculateLetterGrade($percentage);

            // Find or create grade record
            $grade = Grade::updateOrCreate(
                [
                    'enrollment_id' => $row['enrollment_id'],
                    'component_id' => $this->componentId
                ],
                [
                    'points_earned' => $pointsEarned,
                    'max_points' => $this->component->max_points,
                    'percentage' => $percentage,
                    'letter_grade' => $letterGrade,
                    'comments' => $row['comments'] ?? null,
                    'graded_by' => $this->gradedBy,
                    'submitted_at' => now(),
                    'grade_status' => 'draft',
                    'is_final' => false
                ]
            );

            DB::commit();

            $this->successCount++;
            $this->successful[] = $row['enrollment_id'];
            $this->results['successful'][] = $row['enrollment_id'];
            $this->results['success_count']++;

            return $grade;

        } catch (\Exception $e) {
            DB::rollback();
            $this->errorCount++;
            $this->errors[] = "Error processing enrollment {$row['enrollment_id']}: " . $e->getMessage();
            $this->results['errors'][] = "Error processing enrollment {$row['enrollment_id']}: " . $e->getMessage();
            $this->results['error_count']++;
            Log::error('Grade import error', [
                'enrollment_id' => $row['enrollment_id'],
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Define validation rules
     */
    public function rules(): array
    {
        return [
            'enrollment_id' => [
                'required',
                'numeric',
                'exists:enrollments,id'
            ],
            'points_earned' => [
                'required',
                'numeric',
                'min:0',
                'max:' . $this->component->max_points
            ],
            'comments' => [
                'nullable',
                'string',
                'max:500'
            ]
        ];
    }

    /**
     * Define custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            'enrollment_id.required' => 'Enrollment ID is required',
            'enrollment_id.exists' => 'Invalid enrollment ID: :input',
            'points_earned.required' => 'Points earned is required',
            'points_earned.numeric' => 'Points must be a number',
            'points_earned.min' => 'Points cannot be negative',
            'points_earned.max' => 'Points cannot exceed maximum points for this component',
            'comments.max' => 'Comments cannot exceed 500 characters'
        ];
    }

    /**
     * Handle validation failures
     */
    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->errorCount++;
            $errorMsg = "Row {$failure->row()}: " . implode(', ', $failure->errors());
            $this->errors[] = $errorMsg;
            $this->results['errors'][] = $errorMsg;
            $this->results['error_count']++;
            
            Log::warning('Grade import validation failure', [
                'row' => $failure->row(),
                'attribute' => $failure->attribute(),
                'errors' => $failure->errors(),
                'values' => $failure->values()
            ]);
        }
    }

    /**
     * Handle general errors
     */
    public function onError(\Throwable $e)
    {
        $this->errorCount++;
        $errorMsg = 'Import error: ' . $e->getMessage();
        $this->errors[] = $errorMsg;
        $this->results['errors'][] = $errorMsg;
        $this->results['error_count']++;
        
        Log::error('Grade import error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }

    /**
     * Define batch size for inserts
     */
    public function batchSize(): int
    {
        return 100;
    }

    /**
     * Define chunk size for reading
     */
    public function chunkSize(): int
    {
        return 100;
    }

    /**
     * Skip rows that start from row 5 (after header info)
     */
    public function headingRow(): int
    {
        return 5; // Assuming we have 4 rows of header info + 1 row of column headers
    }

    /**
     * Calculate letter grade from percentage
     */
    protected function calculateLetterGrade($percentage)
    {
        if ($percentage >= 93) return 'A';
        if ($percentage >= 90) return 'A-';
        if ($percentage >= 87) return 'B+';
        if ($percentage >= 83) return 'B';
        if ($percentage >= 80) return 'B-';
        if ($percentage >= 77) return 'C+';
        if ($percentage >= 73) return 'C';
        if ($percentage >= 70) return 'C-';
        if ($percentage >= 67) return 'D+';
        if ($percentage >= 63) return 'D';
        return 'F';
    }

    /**
     * Get import results
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * Get success count
     */
    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    /**
     * Get error count
     */
    public function getErrorCount(): int
    {
        return $this->errorCount;
    }

    /**
     * Get errors array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get successful enrollment IDs
     */
    public function getSuccessful(): array
    {
        return $this->successful;
    }
}