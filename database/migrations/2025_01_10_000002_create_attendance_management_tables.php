<?php
// File: database/migrations/2025_01_10_000002_create_attendance_management_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Attendance Sessions Table
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('course_sections');
            $table->date('session_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('session_type', 50)->default('regular'); // regular, makeup, exam, lab
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_cancelled')->default(false);
            $table->string('cancellation_reason')->nullable();
            $table->boolean('attendance_taken')->default(false);
            $table->foreignId('marked_by')->nullable()->constrained('users');
            $table->timestamp('marked_at')->nullable();
            $table->timestamps();
            
            $table->unique(['section_id', 'session_date', 'start_time']);
            $table->index(['section_id', 'session_date']);
            $table->index('attendance_taken');
        });

        // Attendance Records Table
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('attendance_sessions')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students');
            $table->enum('status', ['present', 'absent', 'late', 'excused', 'sick', 'left_early']);
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->integer('minutes_late')->default(0);
            $table->text('remarks')->nullable();
            $table->string('excuse_document')->nullable();
            $table->boolean('excuse_verified')->default(false);
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            
            $table->unique(['session_id', 'student_id']);
            $table->index(['student_id', 'status']);
            $table->index('session_id');
        });

        // Attendance Policies Table
        Schema::create('attendance_policies', function (Blueprint $table) {
            $table->id();
            $table->string('policy_name');
            $table->text('description')->nullable();
            $table->integer('max_absences')->nullable();
            $table->integer('max_late_arrivals')->nullable();
            $table->integer('late_threshold_minutes')->default(15);
            $table->decimal('attendance_weight', 5, 2)->default(0); // Weight in final grade
            $table->boolean('auto_fail_on_excess_absence')->default(false);
            $table->integer('auto_fail_threshold')->nullable();
            $table->json('grade_penalties')->nullable(); // JSON array of penalties
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Section Attendance Policies (Override)
        Schema::create('section_attendance_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('course_sections');
            $table->foreignId('policy_id')->nullable()->constrained('attendance_policies');
            $table->json('custom_rules')->nullable(); // Section-specific overrides
            $table->boolean('track_attendance')->default(true);
            $table->boolean('display_to_students')->default(true);
            $table->timestamps();
            
            $table->unique('section_id');
        });

        // Attendance Excuses Table
        Schema::create('attendance_excuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('excuse_type', 50); // medical, family_emergency, university_activity, etc.
            $table->text('reason');
            $table->string('supporting_document')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('submitted_to')->nullable()->constrained('users');
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->boolean('apply_to_all_courses')->default(false);
            $table->json('applicable_sections')->nullable(); // If not all courses
            $table->timestamps();
            
            $table->index(['student_id', 'status']);
            $table->index(['start_date', 'end_date']);
        });

        // Attendance Statistics Table (for caching/reporting)
        Schema::create('attendance_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('section_id')->constrained('course_sections');
            $table->foreignId('term_id')->constrained('academic_terms');
            $table->integer('total_sessions')->default(0);
            $table->integer('sessions_present')->default(0);
            $table->integer('sessions_absent')->default(0);
            $table->integer('sessions_late')->default(0);
            $table->integer('sessions_excused')->default(0);
            $table->decimal('attendance_percentage', 5, 2)->default(0);
            $table->decimal('attendance_grade', 5, 2)->nullable();
            $table->date('last_calculated')->nullable();
            $table->timestamps();
            
            $table->unique(['student_id', 'section_id']);
            $table->index(['section_id', 'attendance_percentage']);
        });

        // Attendance Alerts Table
        Schema::create('attendance_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('section_id')->constrained('course_sections');
            $table->string('alert_type', 50); // excessive_absence, at_risk, pattern_detected
            $table->text('message');
            $table->integer('absence_count');
            $table->decimal('attendance_percentage', 5, 2);
            $table->boolean('sent_to_student')->default(false);
            $table->boolean('sent_to_advisor')->default(false);
            $table->boolean('sent_to_instructor')->default(false);
            $table->timestamp('student_notified_at')->nullable();
            $table->timestamp('advisor_notified_at')->nullable();
            $table->timestamp('instructor_notified_at')->nullable();
            $table->boolean('acknowledged')->default(false);
            $table->foreignId('acknowledged_by')->nullable()->constrained('users');
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();
            
            $table->index(['student_id', 'alert_type']);
            $table->index('acknowledged');
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_alerts');
        Schema::dropIfExists('attendance_statistics');
        Schema::dropIfExists('attendance_excuses');
        Schema::dropIfExists('section_attendance_policies');
        Schema::dropIfExists('attendance_policies');
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('attendance_sessions');
    }
};