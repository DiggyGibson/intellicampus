<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEnrollmentsTable extends Migration
{
    public function up()
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('section_id')->constrained('course_sections')->onDelete('cascade');
            $table->foreignId('term_id')->constrained('academic_terms');
            $table->enum('enrollment_status', ['enrolled', 'dropped', 'withdrawn', 'completed', 'failed', 'incomplete']);
            $table->date('enrollment_date');
            $table->date('drop_date')->nullable();
            $table->string('grade', 10)->nullable();
            $table->decimal('grade_points', 3, 2)->nullable();
            $table->enum('attendance_mode', ['in-person', 'online', 'hybrid'])->default('in-person');
            $table->timestamps();
            
            $table->unique(['student_id', 'section_id']);
            $table->index(['section_id', 'enrollment_status']);
            $table->index(['student_id', 'term_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('enrollments');
    }
}