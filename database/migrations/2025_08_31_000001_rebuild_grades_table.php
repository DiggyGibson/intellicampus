<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class RebuildAllGradeTables extends Migration
{
    /**
     * Run the migrations - Complete rebuild of all grade-related tables
     */
    public function up()
    {
        // Disable foreign key checks for cleanup
        DB::statement('SET session_replication_role = replica;');
        
        // Drop all existing grade-related tables
        Schema::dropIfExists('grade_change_requests');
        Schema::dropIfExists('grade_audit_log');
        Schema::dropIfExists('grade_submissions');
        Schema::dropIfExists('final_grades');
        Schema::dropIfExists('grades');
        Schema::dropIfExists('grade_components');
        Schema::dropIfExists('grade_deadlines');
        Schema::dropIfExists('grade_scales');
        
        // Re-enable foreign key checks
        DB::statement('SET session_replication_role = DEFAULT;');
        
        // 1. Grade Scales Table
        Schema::create('grade_scales', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->json('scale_values'); // Stores grade letters and their point values
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index('is_active');
        });
        
        // 2. Grade Components Table (for assignments, exams, etc.)
        Schema::create('grade_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('course_sections')->onDelete('cascade');
            $table->string('name', 100);
            $table->enum('type', [
                'exam', 
                'assignment', 
                'quiz', 
                'project', 
                'participation',
                'lab',
                'presentation',
                'homework',
                'paper',
                'discussion',
                'attendance',
                'midterm',
                'final',
                'other'
            ]);
            $table->decimal('weight', 5, 2); // Percentage weight (0-100)
            $table->decimal('max_points', 8, 2)->default(100);
            $table->date('due_date')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_extra_credit')->default(false);
            $table->timestamps();
            
            $table->index('section_id');
            $table->index('type');
            $table->index('due_date');
        });
        
        // 3. Main Grades Table
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('enrollments')->onDelete('cascade');
            $table->foreignId('component_id')->constrained('grade_components')->onDelete('cascade');
            $table->decimal('points_earned', 8, 2);
            $table->decimal('max_points', 8, 2)->default(100);
            $table->decimal('percentage', 5, 2)->nullable();
            $table->string('letter_grade', 3)->nullable();
            $table->text('feedback')->nullable();
            $table->text('comments')->nullable(); // Private instructor comments
            $table->foreignId('graded_by')->constrained('users');
            $table->timestamp('submitted_at')->nullable();
            $table->boolean('is_final')->default(false);
            $table->enum('grade_status', ['draft', 'submitted', 'approved', 'posted', 'returned'])->default('draft');
            $table->timestamps();
            
            $table->unique(['enrollment_id', 'component_id']);
            $table->index(['enrollment_id', 'grade_status']);
            $table->index('component_id');
            $table->index('submitted_at');
        });
        
        // 4. Grade Deadlines Table - WITH ALL NEEDED TYPES
        Schema::create('grade_deadlines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('term_id')->constrained('academic_terms')->onDelete('cascade');
            $table->enum('deadline_type', [
                'midterm',
                'final',
                'incomplete',
                'grade_change'  // This is now included!
            ]);
            $table->date('deadline_date');
            $table->time('deadline_time')->default('23:59:59');
            $table->text('description')->nullable();
            $table->boolean('send_reminders')->default(true);
            $table->integer('reminder_days_before')->default(3);
            $table->boolean('is_enforced')->default(false); // Whether system blocks submissions after deadline
            $table->timestamps();
            
            $table->unique(['term_id', 'deadline_type']);
            $table->index('deadline_date');
        });
        
        // 5. Final Grades Table (for official term grades)
        Schema::create('final_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('enrollments')->onDelete('cascade');
            $table->foreignId('term_id')->constrained('academic_terms');
            $table->string('letter_grade', 3);
            $table->decimal('grade_points', 3, 2)->nullable();
            $table->decimal('quality_points', 6, 2)->nullable();
            $table->integer('credits_earned')->default(0);
            $table->enum('grade_status', ['draft', 'pending', 'pending_approval', 'posted', 'official'])->default('draft');
            $table->foreignId('submitted_by')->nullable()->constrained('users');
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->unique(['enrollment_id', 'term_id']);
            $table->index(['term_id', 'grade_status']);
        });
        
        // 6. Grade Submissions Log
        Schema::create('grade_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('course_sections');
            $table->foreignId('term_id')->constrained('academic_terms');
            $table->foreignId('submitted_by')->constrained('users');
            $table->timestamp('submitted_at');
            $table->integer('total_grades');
            $table->enum('status', ['pending_approval', 'approved', 'rejected', 'returned'])->default('pending_approval');
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_comments')->nullable();
            $table->timestamps();
            
            $table->index(['section_id', 'term_id']);
            $table->index('status');
        });
        
        // 7. Grade Change Requests
        Schema::create('grade_change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('enrollments');
            $table->foreignId('requested_by')->constrained('users');
            $table->string('current_grade', 3);
            $table->string('requested_grade', 3);
            $table->text('reason');
            $table->string('supporting_document')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'under_review'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamp('requested_at');
            $table->timestamps();
            
            $table->index(['enrollment_id', 'status']);
            $table->index('requested_at');
        });
        
        // 8. Grade Audit Log (for tracking all changes)
        Schema::create('grade_audit_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('enrollments');
            $table->foreignId('component_id')->nullable()->constrained('grade_components');
            $table->string('field_changed', 50);
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->foreignId('changed_by')->constrained('users');
            $table->timestamp('changed_at');
            $table->string('reason')->nullable();
            $table->timestamps();
            
            $table->index(['enrollment_id', 'changed_at']);
            $table->index('changed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::statement('SET session_replication_role = replica;');
        
        Schema::dropIfExists('grade_audit_log');
        Schema::dropIfExists('grade_change_requests');
        Schema::dropIfExists('grade_submissions');
        Schema::dropIfExists('final_grades');
        Schema::dropIfExists('grades');
        Schema::dropIfExists('grade_components');
        Schema::dropIfExists('grade_deadlines');
        Schema::dropIfExists('grade_scales');
        
        DB::statement('SET session_replication_role = DEFAULT;');
    }
}