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
        Schema::create('degree_audit_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('program_id')->constrained('academic_programs');
            $table->foreignId('term_id')->constrained('academic_terms');
            
            // Report metadata
            $table->string('report_type', 50); // 'official', 'unofficial', 'what_if'
            $table->string('catalog_year', 10);
            
            // Overall progress
            $table->decimal('total_credits_required', 5, 1);
            $table->decimal('total_credits_completed', 5, 1);
            $table->decimal('total_credits_in_progress', 5, 1);
            $table->decimal('total_credits_remaining', 5, 1);
            $table->decimal('overall_completion_percentage', 5, 2);
            
            // GPA information
            $table->decimal('cumulative_gpa', 3, 2)->nullable();
            $table->decimal('major_gpa', 3, 2)->nullable();
            $table->decimal('minor_gpa', 3, 2)->nullable();
            
            // Graduation eligibility
            $table->boolean('graduation_eligible')->default(false);
            $table->integer('terms_to_completion')->nullable();
            $table->date('expected_graduation_date')->nullable();
            
            // Report data (JSON for flexibility)
            $table->json('requirements_summary')->nullable();
            $table->json('completed_requirements')->nullable();
            $table->json('in_progress_requirements')->nullable();
            $table->json('remaining_requirements')->nullable();
            $table->json('recommendations')->nullable();
            
            // Generation info
            $table->foreignId('generated_by')->constrained('users');
            $table->timestamp('generated_at');
            $table->boolean('is_official')->default(false);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['student_id', 'generated_at']);
            $table->index(['student_id', 'report_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('degree_audit_reports');
    }
};