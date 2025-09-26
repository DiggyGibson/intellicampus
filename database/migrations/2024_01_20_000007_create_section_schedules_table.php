<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration 7: 2024_01_20_000007_create_section_schedules_table.php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('section_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('course_sections')->onDelete('cascade');
            $table->enum('schedule_type', ['lecture', 'lab', 'tutorial', 'exam', 'other']);
            $table->string('days_of_week'); // "M", "T", "W", "Th", "F", "S", "Su"
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room')->nullable();
            $table->string('building')->nullable();
            $table->foreignId('instructor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->timestamps();
            
            $table->index('section_id');
            $table->index(['days_of_week', 'start_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('section_schedules');
    }
};