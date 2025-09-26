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
        Schema::create('student_degree_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('program_requirement_id')->constrained('program_requirements');
            $table->foreignId('requirement_id')->constrained('degree_requirements');
            
            // Progress tracking
            $table->decimal('credits_completed', 5, 1)->default(0);
            $table->decimal('credits_in_progress', 5, 1)->default(0);
            $table->decimal('credits_remaining', 5, 1)->default(0);
            
            $table->integer('courses_completed')->default(0);
            $table->integer('courses_in_progress')->default(0);
            $table->integer('courses_remaining')->default(0);
            
            // Completion status
            $table->enum('status', [
                'not_started',
                'in_progress',
                'completed',
                'waived',
                'substituted',
                'transferred'
            ])->default('not_started');
            
            $table->decimal('completion_percentage', 5, 2)->default(0);
            $table->boolean('is_satisfied')->default(false);
            
            // GPA tracking for GPA requirements
            $table->decimal('requirement_gpa', 3, 2)->nullable();
            $table->boolean('gpa_met')->default(false);
            
            // Notes and overrides
            $table->text('notes')->nullable();
            $table->boolean('manually_cleared')->default(false);
            $table->foreignId('cleared_by')->nullable()->constrained('users');
            $table->timestamp('cleared_at')->nullable();
            
            // Last calculation
            $table->timestamp('last_calculated_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->unique(['student_id', 'program_requirement_id'], 'unique_student_requirement');
            $table->index(['student_id', 'status']);
            $table->index(['student_id', 'is_satisfied']);
        });

        // Student Course Applications - How student's courses apply to requirements
        Schema::create('student_course_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('enrollment_id')->constrained('enrollments');
            $table->foreignId('course_id')->constrained('courses');
            $table->foreignId('requirement_id')->constrained('degree_requirements');
            $table->foreignId('program_requirement_id')->nullable()->constrained('program_requirements');
            
            // Application details
            $table->decimal('credits_applied', 5, 1);
            $table->string('grade', 5)->nullable();
            $table->enum('status', [
                'planned',      // Course is planned
                'in_progress',  // Currently taking
                'completed',    // Completed successfully
                'failed',       // Did not meet requirement
                'withdrawn',    // Withdrawn from course
                'repeated'      // Course was repeated
            ]);
            
            // Override capability
            $table->boolean('is_override')->default(false);
            $table->text('override_reason')->nullable();
            $table->foreignId('override_by')->nullable()->constrained('users');
            $table->timestamp('override_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['student_id', 'requirement_id']);
            $table->index(['student_id', 'status']);
            $table->unique(['student_id', 'enrollment_id', 'requirement_id'], 'unique_course_application');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_course_applications');
        Schema::dropIfExists('student_degree_progress');
    }
};