<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration 6: 2024_01_20_000006_create_course_sections_table.php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_sections', function (Blueprint $table) {
            $table->id();
            $table->string('crn', 10)->unique(); // Course Reference Number
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('term_id')->constrained('academic_terms')->onDelete('cascade');
            $table->string('section_number', 10); // e.g., "01", "02", "A", "B"
            $table->foreignId('instructor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('delivery_mode', [
                'traditional',      // In-person only
                'online_sync',      // Online synchronous
                'online_async',     // Online asynchronous  
                'hybrid',           // Mix of in-person and online
                'hyflex'           // Student choice of in-person or online
            ]);
            $table->integer('enrollment_capacity');
            $table->integer('current_enrollment')->default(0);
            $table->integer('waitlist_capacity')->default(0);
            $table->integer('current_waitlist')->default(0);
            $table->enum('status', ['planned', 'open', 'closed', 'cancelled'])->default('planned');
            
            // Schedule fields
            $table->string('days_of_week')->nullable(); // "MWF", "TTh", etc.
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('room')->nullable();
            $table->string('building')->nullable();
            $table->string('campus')->nullable();
            
            // Online fields
            $table->string('online_meeting_url')->nullable();
            $table->string('online_meeting_password')->nullable();
            $table->boolean('auto_record')->default(false);
            
            // Additional fields
            $table->text('section_notes')->nullable();
            $table->text('instructor_notes')->nullable();
            $table->decimal('additional_fee', 10, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['course_id', 'term_id']);
            $table->index('crn');
            $table->index('instructor_id');
            $table->index('status');
            $table->index('delivery_mode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_sections');
    }
};
