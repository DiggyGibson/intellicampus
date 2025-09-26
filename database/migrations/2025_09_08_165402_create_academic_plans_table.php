<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('academic_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students');
            $table->string('plan_name');
            $table->text('description')->nullable();
            
            // Plan details
            $table->enum('plan_type', [
                'four_year',      // Traditional 4-year plan
                'custom',         // Custom duration
                'accelerated',    // Fast-track plan
                'part_time',      // Part-time student plan
                'transfer'        // Transfer student plan
            ])->default('four_year');
            
            $table->foreignId('primary_program_id')->constrained('academic_programs');
            $table->foreignId('minor_program_id')->nullable()->constrained('academic_programs');
            $table->string('catalog_year', 10);
            
            // Timeline
            $table->date('start_date');
            $table->date('expected_graduation_date');
            $table->integer('total_terms')->default(8);
            
            // Status
            $table->enum('status', [
                'draft',
                'active',
                'completed',
                'archived'
            ])->default('draft');
            
            $table->boolean('is_current')->default(false);
            
            // Validation results
            $table->boolean('is_valid')->default(false);
            $table->json('validation_errors')->nullable();
            $table->timestamp('last_validated_at')->nullable();
            
            // Approval workflow
            $table->boolean('advisor_approved')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('advisor_notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['student_id', 'status']);
            $table->index(['student_id', 'is_current']);
        });

        // Plan Terms - Terms within an academic plan
        Schema::create('plan_terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('academic_plans')->onDelete('cascade');
            $table->foreignId('term_id')->nullable()->constrained('academic_terms');
            
            // Term details
            $table->integer('sequence_number'); // 1, 2, 3... (order in plan)
            $table->string('term_name'); // "Fall 2024", "Spring 2025"
            $table->enum('term_type', [
                'fall',
                'spring',
                'summer',
                'winter',
                'intersession'
            ]);
            $table->integer('year');
            
            // Credit planning
            $table->decimal('planned_credits', 5, 1)->default(0);
            $table->decimal('min_credits', 5, 1)->default(12);
            $table->decimal('max_credits', 5, 1)->default(18);
            
            // Status
            $table->enum('status', [
                'planned',      // Future term
                'current',      // Current term
                'completed',    // Past term
                'skipped'       // Term not taken
            ])->default('planned');
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['plan_id', 'sequence_number']);
            $table->index(['plan_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_terms');
        Schema::dropIfExists('academic_plans');
    }
};