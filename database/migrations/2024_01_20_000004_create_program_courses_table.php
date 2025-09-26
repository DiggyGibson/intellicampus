<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration 4: 2024_01_20_000004_create_program_courses_table.php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('academic_programs')->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->enum('requirement_type', ['core', 'major', 'minor', 'elective', 'general']);
            $table->integer('year_level')->nullable(); // 1, 2, 3, 4
            $table->integer('semester')->nullable(); // 1, 2
            $table->boolean('is_mandatory')->default(true);
            $table->string('alternative_group')->nullable(); // For courses that can be substituted
            $table->timestamps();
            
            $table->unique(['program_id', 'course_id']);
            $table->index('program_id');
            $table->index('course_id');
            $table->index('requirement_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_courses');
    }
};
