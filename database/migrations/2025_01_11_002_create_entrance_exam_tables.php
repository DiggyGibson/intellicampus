<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Entrance Examination Management.
     * Supports paper-based, computer-based (CBT), and online examinations.
     */
    public function up(): void
    {
        // 1. Entrance Exam Definitions
        Schema::create('entrance_exams', function (Blueprint $table) {
            $table->id();
            
            // Exam Identification
            $table->string('exam_code', 20)->unique(); // e.g., "ENT-2025-001"
            $table->string('exam_name', 200); // e.g., "Computer Science Entrance Exam 2025"
            $table->text('description')->nullable();
            
            // Exam Configuration
            $table->enum('exam_type', [
                'entrance',
                'placement',
                'diagnostic',
                'scholarship',
                'transfer_credit',
                'exemption'
            ])->default('entrance');
            
            $table->enum('delivery_mode', [
                'paper_based',
                'computer_based',
                'online_proctored',
                'online_unproctored',
                'hybrid',
                'take_home'
            ]);
            
            // Associated Programs/Terms
            $table->foreignId('term_id')->constrained('academic_terms');
            $table->json('applicable_programs')->nullable(); // Array of program IDs
            $table->json('applicable_application_types')->nullable(); // freshman, transfer, etc.
            
            // Exam Structure
            $table->integer('total_marks');
            $table->integer('passing_marks');
            $table->integer('duration_minutes');
            $table->time('exam_start_time')->nullable();
            $table->time('exam_end_time')->nullable();
            
            // Question Configuration
            $table->integer('total_questions');
            $table->json('sections')->nullable(); // Exam sections with weightage
            /* Example:
            [
                {
                    "name": "Mathematics",
                    "questions": 30,
                    "marks": 30,
                    "duration": 30,
                    "is_mandatory": true
                },
                {
                    "name": "English",
                    "questions": 25,
                    "marks": 25,
                    "duration": 25,
                    "is_mandatory": true
                },
                {
                    "name": "General Knowledge",
                    "questions": 20,
                    "marks": 20,
                    "duration": 20,
                    "is_mandatory": false
                }
            ]
            */
            
            // Instructions and Rules
            $table->text('general_instructions')->nullable();
            $table->text('exam_rules')->nullable();
            $table->json('allowed_materials')->nullable(); // Calculator, dictionary, etc.
            $table->boolean('negative_marking')->default(false);
            $table->decimal('negative_mark_value', 3, 2)->nullable(); // e.g., 0.25
            
            // Scheduling Windows
            $table->date('registration_start_date');
            $table->date('registration_end_date');
            $table->date('exam_date')->nullable(); // For fixed date exams
            $table->date('exam_window_start')->nullable(); // For flexible scheduling
            $table->date('exam_window_end')->nullable();
            
            // Result Configuration
            $table->date('result_publish_date')->nullable();
            $table->boolean('show_detailed_results')->default(false);
            $table->boolean('allow_result_review')->default(false);
            $table->integer('review_period_days')->nullable();
            
            // Status
            $table->enum('status', [
                'draft',
                'published',
                'registration_open',
                'registration_closed',
                'in_progress',
                'completed',
                'results_pending',
                'results_published',
                'archived'
            ])->default('draft');
            
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            // Indexes
            $table->index('exam_code');
            $table->index('term_id');
            $table->index('status');
            $table->index(['exam_date', 'status']);
        });

        // 2. Exam Registrations
        Schema::create('entrance_exam_registrations', function (Blueprint $table) {
            $table->id();
            
            // Registration Details
            $table->string('registration_number', 30)->unique(); // e.g., "REG-2025-000001"
            $table->foreignId('exam_id')->constrained('entrance_exams');
            $table->foreignId('application_id')->nullable()->constrained('admission_applications');
            $table->foreignId('student_id')->nullable()->constrained('students'); // For current students
            
            // Candidate Information (if not linked to application)
            $table->string('candidate_name', 200)->nullable();
            $table->string('candidate_email')->nullable();
            $table->string('candidate_phone', 20)->nullable();
            $table->date('date_of_birth')->nullable();
            
            // Registration Status
            $table->enum('registration_status', [
                'pending',
                'confirmed',
                'cancelled',
                'expired'
            ])->default('pending');
            
            // Payment Information
            $table->boolean('fee_paid')->default(false);
            $table->decimal('fee_amount', 8, 2)->nullable();
            $table->string('payment_reference', 100)->nullable();
            $table->timestamp('payment_date')->nullable();
            
            // Special Accommodations
            $table->boolean('requires_accommodation')->default(false);
            $table->json('accommodation_details')->nullable();
            /* Example:
            {
                "extra_time": 30,
                "large_print": true,
                "reader_required": false,
                "scribe_required": false,
                "wheelchair_access": true,
                "special_seating": "front_row",
                "other_requirements": "Needs breaks every hour"
            }
            */
            
            // Hall Ticket/Admit Card
            $table->string('hall_ticket_number', 30)->nullable()->unique();
            $table->timestamp('hall_ticket_generated_at')->nullable();
            $table->boolean('hall_ticket_downloaded')->default(false);
            
            $table->timestamps();
            
            // Indexes
            $table->index('registration_number');
            $table->index(['exam_id', 'registration_status']);
            $table->index('application_id');
            $table->index('hall_ticket_number');
        });

        // 3. Exam Centers/Venues
        Schema::create('exam_centers', function (Blueprint $table) {
            $table->id();
            
            $table->string('center_code', 20)->unique();
            $table->string('center_name', 200);
            $table->enum('center_type', [
                'internal', // University campus
                'external', // External venue
                'online',   // For online exams
                'home'      // For take-home exams
            ]);
            
            // Location Details
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Capacity
            $table->integer('total_capacity');
            $table->integer('computer_seats')->nullable(); // For CBT
            $table->integer('paper_seats')->nullable(); // For paper-based
            
            // Facilities
            $table->json('facilities')->nullable();
            /* Example:
            {
                "computers": 100,
                "parking": true,
                "cafeteria": true,
                "medical_room": true,
                "backup_power": true,
                "internet_bandwidth": "100mbps",
                "cctv": true,
                "biometric": true
            }
            */
            
            // Contact Information
            $table->string('contact_person', 200)->nullable();
            $table->string('contact_phone', 20)->nullable();
            $table->string('contact_email')->nullable();
            
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            // Indexes
            $table->index('center_code');
            $table->index('center_type');
            $table->index('city');
        });

        // 4. Exam Sessions (Specific exam instances)
        Schema::create('exam_sessions', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('exam_id')->constrained('entrance_exams');
            $table->foreignId('center_id')->constrained('exam_centers');
            
            $table->string('session_code', 30)->unique(); // e.g., "SESS-2025-001-A"
            $table->date('session_date');
            $table->time('start_time');
            $table->time('end_time');
            
            $table->enum('session_type', [
                'morning',
                'afternoon',
                'evening',
                'full_day'
            ]);
            
            $table->integer('capacity');
            $table->integer('registered_count')->default(0);
            
            // Proctoring Configuration
            $table->enum('proctoring_type', [
                'in_person',
                'remote_live',
                'remote_recorded',
                'ai_proctored',
                'honor_code'
            ]);
            
            $table->json('proctor_assignments')->nullable(); // Array of user IDs
            $table->integer('candidates_per_proctor')->default(30);
            
            // Session Status
            $table->enum('status', [
                'scheduled',
                'registration_open',
                'registration_closed',
                'in_progress',
                'completed',
                'cancelled',
                'postponed'
            ])->default('scheduled');
            
            $table->text('special_instructions')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('session_code');
            $table->index(['exam_id', 'session_date']);
            $table->index(['center_id', 'session_date']);
            $table->index('status');
        });

        // 5. Seat Allocations
        Schema::create('exam_seat_allocations', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('registration_id')->constrained('entrance_exam_registrations');
            $table->foreignId('session_id')->constrained('exam_sessions');
            $table->foreignId('center_id')->constrained('exam_centers');
            
            $table->string('seat_number', 20)->nullable();
            $table->string('room_number', 20)->nullable();
            $table->string('floor', 10)->nullable();
            $table->string('building', 50)->nullable();
            
            // For CBT
            $table->string('computer_number', 20)->nullable();
            $table->string('login_id', 50)->nullable();
            $table->string('password', 255)->nullable(); // Encrypted
            
            // Attendance
            $table->boolean('attendance_marked')->default(false);
            $table->timestamp('check_in_time')->nullable();
            $table->timestamp('check_out_time')->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users');
            
            $table->timestamps();
            
            // Indexes
            $table->unique(['session_id', 'seat_number']);
            $table->index('registration_id');
            $table->index(['session_id', 'attendance_marked']);
        });

        // 6. Question Bank
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            
            $table->string('question_code', 30)->unique();
            $table->foreignId('exam_id')->nullable()->constrained('entrance_exams');
            
            // Question Details
            $table->text('question_text');
            $table->enum('question_type', [
                'multiple_choice',
                'multiple_answer',
                'true_false',
                'fill_blanks',
                'short_answer',
                'essay',
                'numerical',
                'matching',
                'ordering'
            ]);
            
            // Category and Difficulty
            $table->string('subject', 100)->nullable();
            $table->string('topic', 100)->nullable();
            $table->string('subtopic', 100)->nullable();
            $table->enum('difficulty_level', ['easy', 'medium', 'hard', 'expert']);
            $table->integer('marks');
            $table->integer('negative_marks')->default(0);
            $table->integer('time_limit_seconds')->nullable(); // Per question time limit
            
            // Answer Options (for MCQ)
            $table->json('options')->nullable();
            /* Example:
            {
                "a": "Option A text",
                "b": "Option B text",
                "c": "Option C text",
                "d": "Option D text"
            }
            */
            
            $table->json('correct_answer'); // Can be array for multiple answers
            $table->text('answer_explanation')->nullable();
            
            // Media Attachments
            $table->string('question_image')->nullable();
            $table->string('question_audio')->nullable();
            $table->string('question_video')->nullable();
            
            // Usage Tracking
            $table->integer('times_used')->default(0);
            $table->decimal('average_score', 5, 2)->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            // Indexes
            $table->index('question_code');
            $table->index(['exam_id', 'subject']);
            $table->index('difficulty_level');
            $table->index(['subject', 'topic']);
        });

        // 7. Question Papers (Generated exam papers)
        Schema::create('exam_question_papers', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('exam_id')->constrained('entrance_exams');
            $table->foreignId('session_id')->nullable()->constrained('exam_sessions');
            
            $table->string('paper_code', 30)->unique();
            $table->string('paper_set', 10)->nullable(); // A, B, C, D for different sets
            
            // Paper Generation Method
            $table->enum('generation_method', [
                'manual',
                'random',
                'template',
                'adaptive'
            ]);
            
            // Questions in Paper
            $table->json('questions_order'); // Array of question IDs in order
            $table->integer('total_questions');
            $table->integer('total_marks');
            
            // Security
            $table->string('paper_hash', 64)->nullable(); // For integrity verification
            $table->boolean('is_locked')->default(false);
            $table->timestamp('locked_at')->nullable();
            
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('paper_code');
            $table->index(['exam_id', 'paper_set']);
            $table->index('session_id');
        });

        // 8. Candidate Responses
        Schema::create('exam_responses', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('registration_id')->constrained('entrance_exam_registrations');
            $table->foreignId('session_id')->constrained('exam_sessions');
            $table->foreignId('paper_id')->constrained('exam_question_papers');
            
            // Response Tracking
            $table->timestamp('exam_started_at')->nullable();
            $table->timestamp('exam_submitted_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            
            // Response Status
            $table->enum('status', [
                'not_started',
                'in_progress',
                'submitted',
                'auto_submitted', // When time runs out
                'terminated',     // Due to violation
                'system_error'
            ])->default('not_started');
            
            // Time Tracking
            $table->integer('time_spent_seconds')->default(0);
            $table->integer('remaining_time_seconds')->nullable();
            
            // Browser/System Info (for online exams)
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('browser_fingerprint')->nullable();
            
            // Violation Tracking
            $table->integer('tab_switches')->default(0);
            $table->integer('copy_attempts')->default(0);
            $table->integer('paste_attempts')->default(0);
            $table->json('violations')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->unique(['registration_id', 'session_id']);
            $table->index(['session_id', 'status']);
        });

        // 9. Question-wise Responses
        Schema::create('exam_response_details', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('response_id')->constrained('exam_responses');
            $table->foreignId('question_id')->constrained('exam_questions');
            
            $table->integer('question_number');
            $table->json('answer')->nullable(); // Student's answer
            
            // Response Metadata
            $table->enum('status', [
                'not_visited',
                'not_answered',
                'answered',
                'marked_review',
                'answered_marked_review'
            ])->default('not_visited');
            
            $table->integer('time_spent_seconds')->default(0);
            $table->timestamp('first_viewed_at')->nullable();
            $table->timestamp('last_updated_at')->nullable();
            $table->integer('visit_count')->default(0);
            
            // Scoring (filled after evaluation)
            $table->decimal('marks_obtained', 5, 2)->nullable();
            $table->boolean('is_correct')->nullable();
            $table->text('evaluator_comments')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['response_id', 'question_number']);
            $table->index(['response_id', 'status']);
        });

        // 10. Exam Results
        Schema::create('entrance_exam_results', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('registration_id')->constrained('entrance_exam_registrations');
            $table->foreignId('exam_id')->constrained('entrance_exams');
            $table->foreignId('response_id')->constrained('exam_responses');
            
            // Scores
            $table->integer('total_questions_attempted');
            $table->integer('correct_answers');
            $table->integer('wrong_answers');
            $table->integer('unanswered');
            
            $table->decimal('marks_obtained', 7, 2);
            $table->decimal('negative_marks', 5, 2)->default(0);
            $table->decimal('final_score', 7, 2);
            $table->decimal('percentage', 5, 2);
            
            // Section-wise Scores
            $table->json('section_scores')->nullable();
            /* Example:
            {
                "mathematics": {
                    "attempted": 28,
                    "correct": 25,
                    "marks": 25,
                    "percentage": 83.33
                },
                "english": {
                    "attempted": 20,
                    "correct": 18,
                    "marks": 18,
                    "percentage": 72
                }
            }
            */
            
            // Ranking
            $table->integer('overall_rank')->nullable();
            $table->integer('category_rank')->nullable(); // Within their category
            $table->integer('center_rank')->nullable();   // Within their center
            $table->decimal('percentile', 5, 2)->nullable();
            
            // Result Status
            $table->enum('result_status', [
                'pass',
                'fail',
                'absent',
                'disqualified',
                'withheld',
                'under_review'
            ]);
            
            $table->boolean('is_qualified')->default(false);
            $table->text('remarks')->nullable();
            
            // Evaluation
            $table->foreignId('evaluated_by')->nullable()->constrained('users');
            $table->timestamp('evaluated_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();
            
            // Result Publication
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->boolean('candidate_notified')->default(false);
            
            $table->timestamps();
            
            // Indexes
            $table->unique('registration_id');
            $table->index(['exam_id', 'result_status']);
            $table->index(['exam_id', 'overall_rank']);
            $table->index(['exam_id', 'percentile']);
        });

        // 11. Answer Key Management
        Schema::create('exam_answer_keys', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('exam_id')->constrained('entrance_exams');
            $table->foreignId('paper_id')->constrained('exam_question_papers');
            
            $table->enum('key_type', [
                'provisional',
                'final'
            ]);
            
            $table->json('answers'); // Question ID => Correct Answer mapping
            
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['exam_id', 'key_type']);
            $table->index('paper_id');
        });

        // 12. Answer Key Challenges
        Schema::create('answer_key_challenges', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('registration_id')->constrained('entrance_exam_registrations');
            $table->foreignId('question_id')->constrained('exam_questions');
            $table->foreignId('answer_key_id')->constrained('exam_answer_keys');
            
            $table->text('challenge_reason');
            $table->json('supporting_documents')->nullable();
            
            $table->enum('status', [
                'pending',
                'under_review',
                'accepted',
                'rejected'
            ])->default('pending');
            
            $table->text('review_comments')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['registration_id', 'status']);
            $table->index(['answer_key_id', 'status']);
        });

        // 13. Proctoring Logs (for online exams)
        Schema::create('exam_proctoring_logs', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('response_id')->constrained('exam_responses');
            $table->foreignId('registration_id')->constrained('entrance_exam_registrations');
            
            $table->enum('event_type', [
                'face_not_detected',
                'multiple_faces',
                'face_mismatch',
                'tab_switch',
                'window_blur',
                'copy_attempt',
                'paste_attempt',
                'right_click',
                'print_attempt',
                'screenshot_attempt',
                'fullscreen_exit',
                'network_disconnect',
                'unusual_activity'
            ]);
            
            $table->string('severity', 20); // low, medium, high, critical
            $table->text('description')->nullable();
            $table->string('screenshot_path')->nullable();
            $table->json('metadata')->nullable();
            
            $table->timestamp('occurred_at');
            $table->boolean('is_reviewed')->default(false);
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->text('review_notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['response_id', 'event_type']);
            $table->index(['registration_id', 'severity']);
            $table->index('is_reviewed');
        });

        // 14. Exam Certificates
        Schema::create('exam_certificates', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('result_id')->constrained('entrance_exam_results');
            $table->foreignId('registration_id')->constrained('entrance_exam_registrations');
            
            $table->string('certificate_number', 50)->unique();
            $table->string('certificate_type', 50); // merit, participation, qualification
            
            $table->string('file_path', 500)->nullable();
            $table->string('verification_code', 50)->unique();
            $table->string('qr_code_path', 500)->nullable();
            
            $table->timestamp('issued_at');
            $table->foreignId('issued_by')->constrained('users');
            
            $table->boolean('is_downloaded')->default(false);
            $table->timestamp('first_downloaded_at')->nullable();
            $table->integer('download_count')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index('certificate_number');
            $table->index('verification_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_certificates');
        Schema::dropIfExists('exam_proctoring_logs');
        Schema::dropIfExists('answer_key_challenges');
        Schema::dropIfExists('exam_answer_keys');
        Schema::dropIfExists('entrance_exam_results');
        Schema::dropIfExists('exam_response_details');
        Schema::dropIfExists('exam_responses');
        Schema::dropIfExists('exam_question_papers');
        Schema::dropIfExists('exam_questions');
        Schema::dropIfExists('exam_seat_allocations');
        Schema::dropIfExists('exam_sessions');
        Schema::dropIfExists('exam_centers');
        Schema::dropIfExists('entrance_exam_registrations');
        Schema::dropIfExists('entrance_exams');
    }
};