<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration 3: 2025_01_XX_000003_create_departments_table.php
class CreateDepartmentsTable extends Migration
{
    public function up()
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type')->default('academic'); // academic, administrative, support
            
            // Hierarchy - department can belong to either college or school
            $table->foreignId('college_id')->nullable()->constrained('colleges')->cascadeOnDelete();
            $table->foreignId('school_id')->nullable()->constrained('schools')->cascadeOnDelete();
            $table->foreignId('parent_department_id')->nullable()->constrained('departments')->cascadeOnDelete();
            
            // Leadership
            $table->foreignId('head_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deputy_head_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('secretary_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Contact Information
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('fax')->nullable();
            $table->string('website')->nullable();
            $table->string('building')->nullable();
            $table->string('office')->nullable();
            
            // Academic Information
            $table->integer('faculty_count')->default(0);
            $table->integer('student_count')->default(0);
            $table->integer('course_count')->default(0);
            $table->integer('program_count')->default(0);
            
            // Budget Information
            $table->decimal('annual_budget', 15, 2)->nullable();
            $table->string('budget_code')->nullable();
            
            // Status and Settings
            $table->boolean('is_active')->default(true);
            $table->boolean('accepts_students')->default(true);
            $table->boolean('offers_courses')->default(true);
            $table->date('established_date')->nullable();
            $table->json('settings')->nullable(); // Department-specific settings
            $table->json('metadata')->nullable(); // Additional metadata
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index('code');
            $table->index('college_id');
            $table->index('school_id');
            $table->index('parent_department_id');
            $table->index('head_id');
            $table->index('is_active');
            $table->index(['college_id', 'is_active']);
            $table->index(['school_id', 'is_active']);
            
            // Ensure department belongs to either college or school, not both
            $table->index(['college_id', 'school_id']);
        });
        
        // Add check constraint
        DB::statement('ALTER TABLE departments ADD CONSTRAINT check_parent_organization CHECK (
            (college_id IS NOT NULL AND school_id IS NULL) OR 
            (college_id IS NULL AND school_id IS NOT NULL) OR
            (college_id IS NULL AND school_id IS NULL AND parent_department_id IS NOT NULL)
        )');
    }

    public function down()
    {
        Schema::dropIfExists('departments');
    }
}