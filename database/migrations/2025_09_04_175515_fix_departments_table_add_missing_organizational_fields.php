<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('departments', function (Blueprint $table) {
            $columns = Schema::getColumnListing('departments');
            
            // Add type field if missing
            if (!in_array('type', $columns)) {
                $table->string('type')->default('academic')->after('description');
            }
            
            // Add organizational fields if missing
            if (!in_array('college_id', $columns)) {
                $table->foreignId('college_id')->nullable()->after('id');
            }
            if (!in_array('school_id', $columns)) {
                $table->foreignId('school_id')->nullable()->after('college_id');
            }
            if (!in_array('parent_department_id', $columns)) {
                $table->foreignId('parent_department_id')->nullable()->after('school_id');
            }
            
            // Add leadership fields if missing
            if (!in_array('head_id', $columns)) {
                $table->foreignId('head_id')->nullable()->after('description');
            }
            if (!in_array('deputy_head_id', $columns)) {
                $table->foreignId('deputy_head_id')->nullable()->after('head_id');
            }
            if (!in_array('secretary_id', $columns)) {
                $table->foreignId('secretary_id')->nullable()->after('deputy_head_id');
            }
            
            // Add additional fields if missing
            if (!in_array('fax', $columns)) {
                $table->string('fax')->nullable()->after('phone');
            }
            if (!in_array('website', $columns)) {
                $table->string('website')->nullable()->after('fax');
            }
            if (!in_array('building', $columns)) {
                $table->string('building')->nullable()->after('website');
            }
            if (!in_array('office', $columns)) {
                $table->string('office')->nullable()->after('building');
            }
            if (!in_array('faculty_count', $columns)) {
                $table->integer('faculty_count')->default(0);
            }
            if (!in_array('student_count', $columns)) {
                $table->integer('student_count')->default(0);
            }
            if (!in_array('course_count', $columns)) {
                $table->integer('course_count')->default(0);
            }
            if (!in_array('program_count', $columns)) {
                $table->integer('program_count')->default(0);
            }
            if (!in_array('annual_budget', $columns)) {
                $table->decimal('annual_budget', 15, 2)->nullable();
            }
            if (!in_array('budget_code', $columns)) {
                $table->string('budget_code')->nullable();
            }
            if (!in_array('accepts_students', $columns)) {
                $table->boolean('accepts_students')->default(true);
            }
            if (!in_array('offers_courses', $columns)) {
                $table->boolean('offers_courses')->default(true);
            }
            if (!in_array('established_date', $columns)) {
                $table->date('established_date')->nullable();
            }
            if (!in_array('settings', $columns)) {
                $table->json('settings')->nullable();
            }
            if (!in_array('metadata', $columns)) {
                $table->json('metadata')->nullable();
            }
            if (!in_array('deleted_at', $columns)) {
                $table->softDeletes();
            }
        });
        
        // Add foreign key constraints separately
        Schema::table('departments', function (Blueprint $table) {
            $table->foreign('college_id')->references('id')->on('colleges')->cascadeOnDelete();
            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('parent_department_id')->references('id')->on('departments')->cascadeOnDelete();
            $table->foreign('head_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('deputy_head_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('secretary_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('departments', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['college_id']);
            $table->dropForeign(['school_id']);
            $table->dropForeign(['parent_department_id']);
            $table->dropForeign(['head_id']);
            $table->dropForeign(['deputy_head_id']);
            $table->dropForeign(['secretary_id']);
            
            // Drop columns
            $table->dropColumn([
                'type', 'college_id', 'school_id', 'parent_department_id',
                'head_id', 'deputy_head_id', 'secretary_id',
                'fax', 'website', 'building', 'office',
                'faculty_count', 'student_count', 'course_count', 'program_count',
                'annual_budget', 'budget_code', 'accepts_students', 'offers_courses',
                'established_date', 'settings', 'metadata', 'deleted_at'
            ]);
        });
    }
};