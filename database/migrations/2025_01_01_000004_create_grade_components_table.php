<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGradeComponentsTable extends Migration
{
    public function up()
    {
        Schema::create('grade_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('course_sections')->onDelete('cascade');
            $table->string('name', 100);
            $table->decimal('weight', 5, 2);
            $table->decimal('max_points', 8, 2)->default(100);
            $table->enum('type', ['exam', 'assignment', 'quiz', 'project', 'participation', 'other']);
            $table->date('due_date')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
            
            $table->index('section_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('grade_components');
    }
}