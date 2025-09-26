<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdditionalGradeTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Grade scales table
        Schema::create('grade_scales', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->json('scale_values'); // JSON of grade letters and points
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Final grades table (separate from component grades)
        Schema::create('final_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained()->onDelete('cascade');
            $table->foreignId('term_id')->constrained('academic_terms');
            $table->string('letter_grade', 3);
            $table->decimal('grade_points', 3, 2);
            $table->decimal('quality_points', 6, 2);
            $table->integer('credits_earned');
            $table->enum('grade_status', ['draft', 'pending', 'pending_approval', 'posted']);
            $table->foreignId('submitted_by')->constrained('users');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamps();
            
            $table->unique(['enrollment_id', 'term_id']);
            $table->index(['term_id', 'grade_status']);
        });

        // Grade submissions log
        Schema::create('grade_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('course_sections');
            $table->foreignId('term_id')->constrained('academic_terms');
            $table->foreignId('submitted_by')->constrained('users');
            $table->timestamp('submitted_at');
            $table->integer('total_grades');
            $table->enum('status', ['pending_approval', 'approved', 'rejected']);
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_comments')->nullable();
            $table->timestamps();
            
            $table->index(['section_id', 'term_id']);
        });

        // Grade change requests
        Schema::create('grade_change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained();
            $table->foreignId('requested_by')->constrained('users');
            $table->string('current_grade', 3);
            $table->string('requested_grade', 3);
            $table->text('reason');
            $table->string('supporting_document')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected']);
            $table->timestamp('requested_at');
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();
            
            $table->index(['enrollment_id', 'status']);
        });

        // Grade audit log
        Schema::create('grade_audit_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained();
            $table->foreignId('component_id')->nullable()->constrained('grade_components');
            $table->string('field_name');
            $table->string('old_value')->nullable();
            $table->string('new_value');
            $table->foreignId('changed_by')->constrained('users');
            $table->timestamp('changed_at');
            $table->string('reason')->nullable();
            $table->timestamps();
            
            $table->index(['enrollment_id', 'changed_at']);
        });

        // Grade deadlines
        Schema::create('grade_deadlines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('term_id')->constrained('academic_terms');
            $table->enum('deadline_type', ['midterm', 'final', 'incomplete']);
            $table->date('deadline_date');
            $table->time('deadline_time')->default('23:59:59');
            $table->text('description')->nullable();
            $table->boolean('send_reminders')->default(true);
            $table->integer('reminder_days_before')->default(3);
            $table->timestamps();
            
            $table->unique(['term_id', 'deadline_type']);
        });

        // Academic standing changes
        Schema::create('academic_standing_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained();
            $table->string('previous_standing', 50)->nullable();
            $table->string('new_standing', 50);
            $table->decimal('gpa', 3, 2);
            $table->foreignId('term_id')->nullable()->constrained('academic_terms');
            $table->timestamp('changed_at');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['student_id', 'changed_at']);
        });

        // Dean's list
        Schema::create('deans_list', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained();
            $table->foreignId('term_id')->constrained('academic_terms');
            $table->decimal('gpa', 3, 2);
            $table->integer('credits');
            $table->timestamps();
            
            $table->unique(['student_id', 'term_id']);
            $table->index('term_id');
        });

        // Student honors
        Schema::create('student_honors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained();
            $table->string('honor_type', 100);
            $table->text('description');
            $table->date('awarded_date');
            $table->string('awarded_by')->nullable();
            $table->timestamps();
            
            $table->index(['student_id', 'awarded_date']);
        });

        // Transfer credits
        Schema::create('transfer_credits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained();
            $table->string('institution');
            $table->string('course_code', 20);
            $table->string('course_title');
            $table->integer('credits');
            $table->string('grade', 3)->nullable();
            $table->foreignId('equivalent_course_id')->nullable()->constrained('courses');
            $table->enum('status', ['pending', 'approved', 'rejected']);
            $table->foreignId('evaluated_by')->nullable()->constrained('users');
            $table->timestamp('evaluated_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['student_id', 'status']);
        });

        // Transcript requests
        Schema::create('transcript_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained();
            $table->foreignId('requested_by')->constrained('users');
            $table->enum('type', ['official', 'unofficial']);
            $table->enum('delivery_method', ['electronic', 'mail', 'pickup']);
            $table->string('recipient_name')->nullable();
            $table->string('recipient_email')->nullable();
            $table->text('mailing_address')->nullable();
            $table->text('purpose');
            $table->integer('copies')->default(1);
            $table->boolean('rush_order')->default(false);
            $table->decimal('fee', 10, 2)->default(0);
            $table->enum('status', ['pending_payment', 'pending', 'processing', 'completed', 'cancelled']);
            $table->timestamp('requested_at');
            $table->foreignId('processed_by')->nullable()->constrained('users');
            $table->timestamp('processed_at')->nullable();
            $table->string('verification_code', 20)->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();
            
            $table->index(['student_id', 'status']);
            $table->index('requested_at');
        });

        // Transcript payments
        Schema::create('transcript_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('transcript_requests');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['card', 'bank_transfer', 'mobile_money'])->nullable();
            $table->string('reference_number')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded']);
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            $table->index('request_id');
        });

        // Transcript verifications
        Schema::create('transcript_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->foreignId('student_id')->constrained();
            $table->enum('type', ['official', 'unofficial']);
            $table->string('file_path')->nullable();
            $table->integer('verification_count')->default(0);
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamps();
            
            $table->index('code');
        });

        // Transcript logs
        Schema::create('transcript_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained();
            $table->enum('type', ['view', 'unofficial', 'official']);
            $table->foreignId('requested_by')->constrained('users');
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            $table->index(['student_id', 'created_at']);
        });

        // Teaching assistants (for grade access)
        Schema::create('teaching_assistants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('course_sections');
            $table->foreignId('user_id')->constrained();
            $table->enum('role', ['grader', 'assistant', 'lab_instructor']);
            $table->boolean('can_enter_grades')->default(false);
            $table->boolean('can_view_grades')->default(true);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamps();
            
            $table->unique(['section_id', 'user_id']);
            $table->index('user_id');
        });

        // Student holds
        Schema::create('student_holds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained();
            $table->enum('hold_type', ['academic', 'financial', 'disciplinary', 'library', 'administrative']);
            $table->string('reason');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('placed_date');
            $table->foreignId('placed_by')->constrained('users');
            $table->date('resolved_date')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users');
            $table->timestamps();
            
            $table->index(['student_id', 'is_active']);
        });

        // Update students table to add GPA fields if they don't exist
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'cumulative_gpa')) {
                $table->decimal('cumulative_gpa', 3, 2)->nullable()->after('enrollment_status');
            }
            if (!Schema::hasColumn('students', 'semester_gpa')) {
                $table->decimal('semester_gpa', 3, 2)->nullable()->after('cumulative_gpa');
            }
            if (!Schema::hasColumn('students', 'major_gpa')) {
                $table->decimal('major_gpa', 3, 2)->nullable()->after('semester_gpa');
            }
            if (!Schema::hasColumn('students', 'total_credits_earned')) {
                $table->integer('total_credits_earned')->default(0)->after('major_gpa');
            }
            if (!Schema::hasColumn('students', 'total_credits_attempted')) {
                $table->integer('total_credits_attempted')->default(0)->after('total_credits_earned');
            }
            if (!Schema::hasColumn('students', 'academic_standing')) {
                $table->string('academic_standing', 50)->nullable()->after('total_credits_attempted');
            }
        });

        // Update grades table to add missing fields
        Schema::table('grades', function (Blueprint $table) {
            if (!Schema::hasColumn('grades', 'enrollment_id')) {
                $table->foreignId('enrollment_id')->after('id')->constrained();
            }
            if (!Schema::hasColumn('grades', 'points_earned')) {
                $table->decimal('points_earned', 8, 2)->nullable()->after('component_id');
            }
            if (!Schema::hasColumn('grades', 'percentage')) {
                $table->decimal('percentage', 5, 2)->nullable()->after('max_score');
            }
            if (!Schema::hasColumn('grades', 'letter_grade')) {
                $table->string('letter_grade', 3)->nullable()->after('percentage');
            }
            if (!Schema::hasColumn('grades', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('graded_by');
            }
            if (!Schema::hasColumn('grades', 'is_final')) {
                $table->boolean('is_final')->default(false)->after('submitted_at');
            }
            if (!Schema::hasColumn('grades', 'grade_status')) {
                $table->enum('grade_status', ['draft', 'pending', 'posted'])->default('draft')->after('is_final');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove added columns from existing tables
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'cumulative_gpa',
                'semester_gpa',
                'major_gpa',
                'total_credits_earned',
                'total_credits_attempted',
                'academic_standing'
            ]);
        });

        Schema::table('grades', function (Blueprint $table) {
            $table->dropColumn([
                'enrollment_id',
                'points_earned',
                'percentage',
                'letter_grade',
                'submitted_at',
                'is_final',
                'grade_status'
            ]);
        });

        // Drop tables
        Schema::dropIfExists('student_holds');
        Schema::dropIfExists('teaching_assistants');
        Schema::dropIfExists('transcript_logs');
        Schema::dropIfExists('transcript_verifications');
        Schema::dropIfExists('transcript_payments');
        Schema::dropIfExists('transcript_requests');
        Schema::dropIfExists('transfer_credits');
        Schema::dropIfExists('student_honors');
        Schema::dropIfExists('deans_list');
        Schema::dropIfExists('academic_standing_changes');
        Schema::dropIfExists('grade_deadlines');
        Schema::dropIfExists('grade_audit_log');
        Schema::dropIfExists('grade_change_requests');
        Schema::dropIfExists('grade_submissions');
        Schema::dropIfExists('final_grades');
        Schema::dropIfExists('grade_scales');
    }
}