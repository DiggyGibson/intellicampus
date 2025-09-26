<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration 3: 2024_01_20_000003_create_course_prerequisites_table.php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_prerequisites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('prerequisite_id')->constrained('courses')->onDelete('cascade');
            $table->enum('type', ['prerequisite', 'corequisite', 'recommended']);
            $table->decimal('min_grade', 3, 2)->nullable(); // Minimum grade required
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['course_id', 'prerequisite_id', 'type']);
            $table->index('course_id');
            $table->index('prerequisite_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_prerequisites');
    }
};