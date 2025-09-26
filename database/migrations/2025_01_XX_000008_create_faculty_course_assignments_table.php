<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration 8: 2025_01_XX_000008_create_faculty_course_assignments_table.php
return new class extends Migration
{
    public function up()
    {
        Schema::create('faculty_course_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->enum('assignment_type', [
                'coordinator',      // Course coordinator
                'primary_instructor', // Primary instructor
                'co_instructor',    // Co-instructor
                'teaching_assistant', // TA
                'grader',          // Grader only
                'guest_lecturer'    // Guest lecturer
            ]);
            
            $table->boolean('can_edit_content')->default(false);
            $table->boolean('can_manage_grades')->default(false);
            $table->boolean('can_view_all_sections')->default(false);
            
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->unique(['faculty_id', 'course_id', 'assignment_type']);
            $table->index(['course_id', 'is_active']);
            $table->index(['faculty_id', 'is_active']);
            $table->index('assignment_type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('faculty_course_assignments');
    }
}
;
