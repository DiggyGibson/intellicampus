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
        Schema::create('degree_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('requirement_categories');
            $table->string('code', 50)->unique(); // e.g., 'GE_MATH', 'MAJOR_CS_CORE'
            $table->string('name');
            $table->text('description')->nullable();
            
            // Requirement rules
            $table->enum('requirement_type', [
                'credit_hours',      // Requires X credit hours
                'course_count',      // Requires X number of courses
                'specific_courses',  // Requires specific course(s)
                'course_list',       // Choose from list of courses
                'gpa',              // Minimum GPA requirement
                'residency',        // Residency requirement
                'milestone',        // Thesis, comprehensive exam, etc.
                'other'             // Other special requirements
            ]);
            
            // Requirement parameters (stored as JSON for flexibility)
            $table->json('parameters')->nullable();
            /* Example parameters:
            {
                "min_credits": 15,
                "min_courses": 5,
                "required_courses": ["CS101", "CS102"],
                "choose_from": ["MATH201", "MATH202", "MATH203"],
                "min_to_choose": 2,
                "min_gpa": 2.0,
                "min_grade": "C",
                "allow_pass_fail": false
            }
            */
            
            // Display and ordering
            $table->integer('display_order')->default(0);
            $table->boolean('is_required')->default(true);
            $table->boolean('is_active')->default(true);
            
            // Effective dates for catalog years
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['category_id', 'is_active']);
            $table->index('requirement_type');
        });

        // Course-Requirement Mappings - Which courses satisfy which requirements
        Schema::create('course_requirement_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses');
            $table->foreignId('requirement_id')->constrained('degree_requirements');
            
            // How this course applies to requirement
            $table->enum('fulfillment_type', [
                'full',      // Fully satisfies requirement
                'partial',   // Partially satisfies
                'elective',  // Counts as elective
                'choice'     // One of several choices
            ])->default('full');
            
            $table->decimal('credit_value', 5, 1)->nullable(); // Override course credits
            $table->string('min_grade', 5)->nullable(); // Minimum grade required
            
            // Effective dates
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->index(['course_id', 'requirement_id']);
            $table->index(['requirement_id', 'is_active']);
        });

        // Requirement Substitutions - Track approved substitutions
        Schema::create('requirement_substitutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('requirement_id')->constrained('degree_requirements');
            $table->foreignId('original_course_id')->nullable()->constrained('courses');
            $table->foreignId('substitute_course_id')->constrained('courses');
            
            // Approval details
            $table->text('reason');
            $table->enum('status', [
                'pending',
                'approved',
                'denied',
                'revoked'
            ])->default('pending');
            
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['student_id', 'status']);
            $table->index(['requirement_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requirement_substitutions');
        Schema::dropIfExists('course_requirement_mappings');
        Schema::dropIfExists('degree_requirements');
    }
};