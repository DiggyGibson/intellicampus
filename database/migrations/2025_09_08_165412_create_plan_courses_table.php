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
        Schema::create('plan_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_term_id')->constrained('plan_terms')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses');
            $table->foreignId('section_id')->nullable()->constrained('course_sections');
            
            // Course details
            $table->decimal('credits', 5, 1);
            $table->enum('status', [
                'planned',       // Planned to take
                'registered',    // Registered for course
                'in_progress',   // Currently taking
                'completed',     // Successfully completed
                'dropped',       // Dropped the course
                'failed',        // Failed the course
                'withdrawn'      // Withdrawn from course
            ])->default('planned');
            
            // Requirements this course satisfies
            $table->json('satisfies_requirements')->nullable(); // Array of requirement IDs
            
            // Validation
            $table->boolean('prerequisites_met')->default(false);
            $table->boolean('corequisites_met')->default(false);
            $table->json('validation_warnings')->nullable();
            
            // Alternative courses (if primary choice not available)
            $table->json('alternative_courses')->nullable();
            
            // Priority for registration
            $table->integer('priority')->default(1);
            $table->boolean('is_required')->default(true);
            $table->boolean('is_backup')->default(false);
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['plan_term_id', 'status']);
            $table->index(['course_id', 'status']);
        });

        // What-If Scenarios - For testing different academic paths
        Schema::create('what_if_scenarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students');
            $table->string('scenario_name');
            $table->text('description')->nullable();
            
            // Scenario parameters
            $table->enum('scenario_type', [
                'change_major',
                'add_minor',
                'add_double_major',
                'change_catalog',
                'transfer_credits',
                'course_substitution'
            ]);
            
            // Program changes
            $table->foreignId('new_program_id')->nullable()->constrained('academic_programs');
            $table->foreignId('add_minor_id')->nullable()->constrained('academic_programs');
            $table->foreignId('add_second_major_id')->nullable()->constrained('academic_programs');
            $table->string('new_catalog_year', 10)->nullable();
            
            // Transfer credits scenario
            $table->json('transfer_courses')->nullable();
            $table->decimal('transfer_credits', 5, 1)->nullable();
            
            // Results of analysis
            $table->json('analysis_results')->nullable();
            
            // Comparison with current
            $table->decimal('current_credits_required', 5, 1)->nullable();
            $table->decimal('scenario_credits_required', 5, 1)->nullable();
            $table->decimal('credit_difference', 5, 1)->nullable();
            
            $table->integer('current_terms_remaining')->nullable();
            $table->integer('scenario_terms_remaining')->nullable();
            
            // Feasibility
            $table->boolean('is_feasible')->default(true);
            $table->json('feasibility_issues')->nullable();
            
            // Save/apply status
            $table->boolean('is_saved')->default(false);
            $table->boolean('is_applied')->default(false);
            $table->timestamp('applied_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['student_id', 'scenario_type']);
            $table->index(['student_id', 'is_saved']);
        });

        // Graduation Applications - Track graduation eligibility
        Schema::create('graduation_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('program_id')->constrained('academic_programs');
            $table->foreignId('term_id')->constrained('academic_terms');
            
            // Application details
            $table->date('expected_graduation_date');
            $table->string('degree_type'); // BS, BA, MS, etc.
            $table->string('diploma_name')->nullable();
            
            // Audit results at time of application
            $table->boolean('requirements_met')->default(false);
            $table->decimal('final_gpa', 3, 2)->nullable();
            $table->integer('total_credits_earned');
            
            // Missing requirements
            $table->json('pending_requirements')->nullable();
            $table->boolean('has_holds')->default(false);
            $table->json('holds_list')->nullable();
            
            // Honors and Latin honors
            $table->string('honors')->nullable(); // cum laude, magna cum laude, summa cum laude
            $table->json('special_recognitions')->nullable();
            
            // Application status
            $table->enum('status', [
                'draft',
                'submitted',
                'under_review',
                'approved',
                'conditional',
                'denied',
                'graduated'
            ])->default('draft');
            
            // Review process
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            
            // Final clearance
            $table->boolean('academic_clearance')->default(false);
            $table->boolean('financial_clearance')->default(false);
            $table->boolean('library_clearance')->default(false);
            
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('graduation_date')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['student_id', 'status']);
            $table->index(['term_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('graduation_applications');
        Schema::dropIfExists('what_if_scenarios');
        Schema::dropIfExists('plan_courses');
    }
};