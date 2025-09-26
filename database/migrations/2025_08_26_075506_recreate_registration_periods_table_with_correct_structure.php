<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, show current structure for debugging
        if (Schema::hasTable('registration_periods')) {
            echo "\n=== Current registration_periods structure ===\n";
            $columns = Schema::getColumnListing('registration_periods');
            echo "Existing columns: " . implode(', ', $columns) . "\n";
            
            // Check if there's any data we need to preserve
            $count = DB::table('registration_periods')->count();
            echo "Existing records: {$count}\n";
            
            if ($count > 0) {
                echo "Warning: Dropping table with {$count} existing records.\n";
            }
            
            // Drop the incorrectly structured table
            Schema::dropIfExists('registration_periods');
            echo "Dropped old registration_periods table.\n\n";
        }
        
        // Create the table with the CORRECT structure expected by the seeder
        echo "Creating registration_periods table with correct structure...\n";
        
        Schema::create('registration_periods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('term_id');
            $table->string('period_type', 50); // THIS is the column that was missing: 'priority', 'regular', 'late'
            $table->date('start_date');
            $table->date('end_date');
            $table->json('student_levels')->nullable(); // JSON array: ['senior', 'junior', etc.]
            $table->json('priority_groups')->nullable(); // JSON array: ['athlete', 'honors', etc.]
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['term_id', 'start_date', 'end_date']);
            $table->index('period_type');
            $table->index('is_active');
        });
        
        echo "Table created successfully with columns:\n";
        $newColumns = Schema::getColumnListing('registration_periods');
        echo implode(', ', $newColumns) . "\n";
        
        // Add foreign key if academic_terms exists
        if (Schema::hasTable('academic_terms')) {
            Schema::table('registration_periods', function (Blueprint $table) {
                $table->foreign('term_id')->references('id')->on('academic_terms')->onDelete('cascade');
            });
            echo "Added foreign key constraint to academic_terms.\n";
        }
        
        // Also ensure other related tables have correct structure
        $this->ensureRelatedTablesCorrectStructure();
        
        echo "\n✅ registration_periods table recreated with correct structure!\n";
    }
    
    /**
     * Ensure other related tables have the correct columns
     */
    private function ensureRelatedTablesCorrectStructure(): void
    {
        // Ensure students table has the new columns
        if (Schema::hasTable('students')) {
            $existingColumns = Schema::getColumnListing('students');
            $columnsToAdd = [
                'is_athlete' => ['type' => 'boolean', 'default' => false],
                'is_honors' => ['type' => 'boolean', 'default' => false],
                'has_disability_accommodation' => ['type' => 'boolean', 'default' => false],
                'expected_graduation_date' => ['type' => 'date', 'nullable' => true],
                'cumulative_gpa' => ['type' => 'decimal', 'precision' => 3, 'scale' => 2, 'nullable' => true],
                'academic_standing' => ['type' => 'string', 'length' => 50, 'nullable' => true]
            ];
            
            Schema::table('students', function (Blueprint $table) use ($existingColumns, $columnsToAdd) {
                foreach ($columnsToAdd as $column => $config) {
                    if (!in_array($column, $existingColumns)) {
                        switch ($config['type']) {
                            case 'boolean':
                                $table->boolean($column)->default($config['default']);
                                break;
                            case 'date':
                                $table->date($column)->nullable();
                                break;
                            case 'decimal':
                                $table->decimal($column, $config['precision'], $config['scale'])->nullable();
                                break;
                            case 'string':
                                $table->string($column, $config['length'])->nullable();
                                break;
                        }
                        echo "Added {$column} to students table.\n";
                    }
                }
            });
        }
        
        // Ensure course_prerequisites has required columns
        if (Schema::hasTable('course_prerequisites')) {
            $existingColumns = Schema::getColumnListing('course_prerequisites');
            
            Schema::table('course_prerequisites', function (Blueprint $table) use ($existingColumns) {
                if (!in_array('minimum_grade', $existingColumns)) {
                    $table->string('minimum_grade', 3)->nullable()->after('requirement_type');
                    echo "Added minimum_grade to course_prerequisites table.\n";
                }
                if (!in_array('additional_requirements', $existingColumns)) {
                    $table->json('additional_requirements')->nullable()->after('minimum_grade');
                    echo "Added additional_requirements to course_prerequisites table.\n";
                }
            });
        }
        
        // Ensure registration_holds has required columns
        if (Schema::hasTable('registration_holds')) {
            $existingColumns = Schema::getColumnListing('registration_holds');
            
            Schema::table('registration_holds', function (Blueprint $table) use ($existingColumns) {
                if (!in_array('placed_by_department', $existingColumns)) {
                    $table->string('placed_by_department', 100)->nullable()->after('reason');
                    echo "Added placed_by_department to registration_holds table.\n";
                }
                if (!in_array('cleared_at', $existingColumns)) {
                    $table->timestamp('cleared_at')->nullable()->after('is_active');
                    echo "Added cleared_at to registration_holds table.\n";
                }
                if (!in_array('resolution_instructions', $existingColumns)) {
                    $table->text('resolution_instructions')->nullable()->after('reason');
                    echo "Added resolution_instructions to registration_holds table.\n";
                }
            });
        }
        
        // Ensure academic_terms has required columns
        if (Schema::hasTable('academic_terms')) {
            $existingColumns = Schema::getColumnListing('academic_terms');
            
            Schema::table('academic_terms', function (Blueprint $table) use ($existingColumns) {
                if (!in_array('drop_deadline', $existingColumns)) {
                    $table->date('drop_deadline')->nullable()->after('end_date');
                    echo "Added drop_deadline to academic_terms table.\n";
                }
                if (!in_array('withdrawal_deadline', $existingColumns)) {
                    $table->date('withdrawal_deadline')->nullable()->after('drop_deadline');
                    echo "Added withdrawal_deadline to academic_terms table.\n";
                }
            });
        }
        
        // Ensure enrollments has required columns
        if (Schema::hasTable('enrollments')) {
            $existingColumns = Schema::getColumnListing('enrollments');
            
            Schema::table('enrollments', function (Blueprint $table) use ($existingColumns) {
                if (!in_array('dropped_at', $existingColumns)) {
                    $table->timestamp('dropped_at')->nullable();
                    echo "Added dropped_at to enrollments table.\n";
                }
                if (!in_array('final_grade', $existingColumns)) {
                    $table->string('final_grade', 3)->nullable();
                    echo "Added final_grade to enrollments table.\n";
                }
            });
        }
        
        // Ensure registration_logs has required columns
        if (Schema::hasTable('registration_logs')) {
            $existingColumns = Schema::getColumnListing('registration_logs');
            
            Schema::table('registration_logs', function (Blueprint $table) use ($existingColumns) {
                if (!in_array('ip_address', $existingColumns)) {
                    $table->string('ip_address', 45)->nullable();
                    echo "Added ip_address to registration_logs table.\n";
                }
                if (!in_array('user_agent', $existingColumns)) {
                    $table->text('user_agent')->nullable();
                    echo "Added user_agent to registration_logs table.\n";
                }
            });
        }
        
        // Create registration_overrides if it doesn't exist
        if (!Schema::hasTable('registration_overrides')) {
            Schema::create('registration_overrides', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('term_id');
                $table->string('override_type', 50);
                $table->unsignedBigInteger('course_id')->nullable();
                $table->boolean('is_approved')->default(false);
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->text('reason')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                
                $table->index(['student_id', 'term_id']);
                $table->index(['override_type', 'is_approved']);
            });
            echo "Created registration_overrides table.\n";
        }
        
        // Create prerequisite_overrides if it doesn't exist
        if (!Schema::hasTable('prerequisite_overrides')) {
            Schema::create('prerequisite_overrides', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('course_id');
                $table->unsignedBigInteger('term_id');
                $table->boolean('is_approved')->default(false);
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->text('reason');
                $table->timestamps();
                
                $table->index(['student_id', 'course_id', 'term_id']);
            });
            echo "Created prerequisite_overrides table.\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the new structure
        Schema::dropIfExists('registration_periods');
        
        // Recreate the old structure (if needed for rollback)
        Schema::create('registration_periods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('term_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('student_level')->nullable();
            $table->integer('min_credits')->nullable();
            $table->integer('max_credits')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
};