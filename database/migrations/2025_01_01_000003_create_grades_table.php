<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGradesTable extends Migration
{
    public function up()
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('course_sections')->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->integer('component_id'); // References grade component (midterm, final, etc.)
            $table->decimal('score', 5, 2);
            $table->decimal('max_score', 5, 2)->default(100);
            $table->decimal('weight', 5, 2)->nullable();
            $table->text('comments')->nullable();
            $table->foreignId('graded_by')->constrained('users');
            $table->timestamps();
            
            $table->unique(['section_id', 'student_id', 'component_id']);
            $table->index(['section_id', 'component_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('grades');
    }
}