<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration 5: 2024_01_20_000005_create_academic_terms_table.php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_terms', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique(); // e.g., "2024-FALL"
            $table->string('name'); // e.g., "Fall 2024"
            $table->enum('type', ['fall', 'spring', 'summer', 'winter', 'trimester1', 'trimester2', 'trimester3']);
            $table->integer('academic_year'); // 2024
            $table->date('start_date');
            $table->date('end_date');
            $table->date('registration_start');
            $table->date('registration_end');
            $table->date('add_drop_deadline');
            $table->date('withdrawal_deadline');
            $table->date('grades_due_date');
            $table->boolean('is_current')->default(false);
            $table->boolean('is_active')->default(true);
            $table->jsonb('important_dates')->nullable(); // Additional dates as JSON
            $table->timestamps();
            
            $table->index('code');
            $table->index('academic_year');
            $table->index('is_current');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_terms');
    }
};