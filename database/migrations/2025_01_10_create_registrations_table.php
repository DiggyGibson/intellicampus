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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            
            // Core relationships
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('section_id')->constrained('course_sections')->onDelete('cascade');
            $table->foreignId('term_id')->constrained('academic_terms');
            
            // Registration process fields (matching your existing model)
            $table->timestamp('registration_date')->nullable();
            $table->enum('status', [
                'pending',
                'enrolled', 
                'waitlisted',
                'dropped',
                'withdrawn',
                'completed',
                'failed',
                'incomplete',
                'audit'
            ])->default('pending');
            
            // Registration type
            $table->string('registration_type')->default('regular'); // regular, audit, repeat, etc.
            
            // Academic fields (for when it becomes an enrollment)
            $table->string('grade', 5)->nullable();
            $table->decimal('grade_points', 4, 2)->nullable();
            $table->decimal('credits_attempted', 3, 1)->nullable();
            $table->decimal('credits_earned', 3, 1)->nullable();
            $table->string('midterm_grade', 5)->nullable();
            $table->string('final_grade', 5)->nullable();
            $table->timestamp('grade_submission_date')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users');
            
            // Status change tracking
            $table->timestamp('dropped_date')->nullable();
            $table->timestamp('withdrawn_date')->nullable();
            $table->string('completion_status', 50)->nullable();
            
            // Notes
            $table->text('notes')->nullable();
            
            // Soft deletes (your model expects this)
            $table->softDeletes();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['student_id', 'term_id', 'status']);
            $table->index(['section_id', 'status']);
            $table->index(['term_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};