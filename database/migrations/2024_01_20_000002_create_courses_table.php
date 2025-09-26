<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('title');
            $table->text('description');
            $table->integer('credits')->default(3);
            $table->integer('lecture_hours')->default(3);
            $table->integer('lab_hours')->default(0);
            $table->integer('tutorial_hours')->default(0);
            $table->integer('level')->default(100);
            $table->string('department');
            $table->enum('type', ['required', 'elective', 'core', 'major', 'minor', 'general']);
            $table->enum('grading_method', ['letter', 'pass_fail', 'numeric']);
            $table->text('learning_outcomes')->nullable();
            $table->text('topics_covered')->nullable();
            $table->text('assessment_methods')->nullable();
            $table->text('textbooks')->nullable();
            $table->decimal('course_fee', 10, 2)->default(0);
            $table->decimal('lab_fee', 10, 2)->default(0);
            $table->boolean('has_lab')->default(false);
            $table->boolean('has_tutorial')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('min_enrollment')->default(5);
            $table->integer('max_enrollment')->default(30);
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('code');
            $table->index('department');
            $table->index('level');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};