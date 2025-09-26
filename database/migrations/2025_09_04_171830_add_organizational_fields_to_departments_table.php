<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('departments', function (Blueprint $table) {
            // Add organizational hierarchy fields
            $table->foreignId('college_id')->nullable()->after('id')->constrained('colleges')->cascadeOnDelete();
            $table->foreignId('school_id')->nullable()->after('college_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('parent_department_id')->nullable()->after('school_id')->constrained('departments')->cascadeOnDelete();
            
            // Replace head_of_department string with foreign key
            $table->foreignId('head_id')->nullable()->after('description')->constrained('users')->nullOnDelete();
            $table->foreignId('deputy_head_id')->nullable()->after('head_id')->constrained('users')->nullOnDelete();
            $table->foreignId('secretary_id')->nullable()->after('deputy_head_id')->constrained('users')->nullOnDelete();
            
            // Add additional fields
            $table->string('type')->default('academic')->after('description');
            $table->string('fax')->nullable()->after('phone');
            $table->string('website')->nullable()->after('fax');
            $table->string('building')->nullable()->after('website');
            $table->string('office')->nullable()->after('building');
            $table->integer('faculty_count')->default(0);
            $table->integer('student_count')->default(0);
            $table->integer('course_count')->default(0);
            $table->integer('program_count')->default(0);
            $table->decimal('annual_budget', 15, 2)->nullable();
            $table->string('budget_code')->nullable();
            $table->boolean('accepts_students')->default(true);
            $table->boolean('offers_courses')->default(true);
            $table->date('established_date')->nullable();
            $table->json('settings')->nullable();
            $table->json('metadata')->nullable();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['college_id']);
            $table->dropForeign(['school_id']);
            $table->dropForeign(['parent_department_id']);
            $table->dropForeign(['head_id']);
            $table->dropForeign(['deputy_head_id']);
            $table->dropForeign(['secretary_id']);
            
            $table->dropColumn([
                'college_id', 'school_id', 'parent_department_id',
                'head_id', 'deputy_head_id', 'secretary_id',
                'type', 'fax', 'website', 'building', 'office',
                'faculty_count', 'student_count', 'course_count', 'program_count',
                'annual_budget', 'budget_code', 'accepts_students', 'offers_courses',
                'established_date', 'settings', 'metadata', 'deleted_at'
            ]);
        });
    }
};