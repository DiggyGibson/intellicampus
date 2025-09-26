<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for the Admissions & Enrollment Management module.
     * This creates all necessary tables for handling the complete admission process.
     */
    public function up(): void
    {
        // 1. Main Admission Applications Table
        Schema::create('admission_applications', function (Blueprint $table) {
            $table->id();
            
            // Application Identifier
            $table->string('application_number', 20)->unique(); // Format: APP-2025-000001
            $table->uuid('application_uuid')->unique(); // For secure public access
            
            // Applicant Personal Information
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100);
            $table->string('preferred_name', 100)->nullable();
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female', 'other', 'prefer_not_to_say'])->nullable();
            $table->string('nationality', 100);
            $table->string('country_of_birth', 100)->nullable();
            $table->string('passport_number', 50)->nullable();
            $table->string('national_id', 50)->nullable();
            
            // Contact Information
            $table->string('email')->unique();
            $table->string('phone_primary', 20);
            $table->string('phone_secondary', 20)->nullable();
            $table->text('current_address');
            $table->text('permanent_address');
            $table->string('city', 100);
            $table->string('state_province', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 100);
            
            // Emergency Contact
            $table->string('emergency_contact_name', 200)->nullable();
            $table->string('emergency_contact_relationship', 100)->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            $table->string('emergency_contact_email')->nullable();
            
            // Parent/Guardian Information (for undergraduate)
            $table->string('parent_guardian_name', 200)->nullable();
            $table->string('parent_guardian_occupation', 100)->nullable();
            $table->string('parent_guardian_phone', 20)->nullable();
            $table->string('parent_guardian_email')->nullable();
            $table->decimal('parent_guardian_income', 10, 2)->nullable();
            
            // Application Details
            $table->enum('application_type', [
                'freshman',
                'transfer', 
                'graduate',
                'international',
                'readmission',
                'non_degree',
                'exchange'
            ]);
            $table->foreignId('term_id')->constrained('academic_terms');
            $table->foreignId('program_id')->constrained('academic_programs');
            $table->foreignId('alternate_program_id')->nullable()->constrained('academic_programs');
            $table->string('intended_major', 100)->nullable();
            $table->string('intended_minor', 100)->nullable();
            $table->enum('entry_type', ['fall', 'spring', 'summer'])->nullable();
            $table->year('entry_year');
            
            // Educational Background
            $table->string('previous_institution', 255)->nullable();
            $table->string('previous_institution_country', 100)->nullable();
            $table->date('previous_institution_graduation_date')->nullable();
            $table->string('previous_degree', 100)->nullable();
            $table->string('previous_major', 100)->nullable();
            $table->decimal('previous_gpa', 3, 2)->nullable();
            $table->string('gpa_scale', 10)->nullable(); // e.g., "4.0", "5.0", "100"
            $table->integer('class_rank')->nullable();
            $table->integer('class_size')->nullable();
            
            // High School Information (for freshman)
            $table->string('high_school_name', 255)->nullable();
            $table->string('high_school_country', 100)->nullable();
            $table->date('high_school_graduation_date')->nullable();
            $table->string('high_school_diploma_type', 100)->nullable();
            
            // Test Scores (stored as JSON for flexibility)
            $table->json('test_scores')->nullable();
            /* Example structure:
            {
                "SAT": {
                    "total": 1400,
                    "math": 700,
                    "verbal": 700,
                    "test_date": "2024-05-15"
                },
                "ACT": {
                    "composite": 32,
                    "english": 33,
                    "math": 31,
                    "reading": 32,
                    "science": 32,
                    "test_date": "2024-06-01"
                },
                "TOEFL": {
                    "total": 100,
                    "reading": 25,
                    "listening": 25,
                    "speaking": 25,
                    "writing": 25,
                    "test_date": "2024-07-01"
                },
                "IELTS": {
                    "overall": 7.5,
                    "test_date": "2024-07-15"
                },
                "GRE": {
                    "verbal": 160,
                    "quantitative": 165,
                    "analytical": 4.5,
                    "test_date": "2024-08-01"
                }
            }
            */
            
            // Essays and Statements
            $table->text('personal_statement')->nullable();
            $table->text('statement_of_purpose')->nullable();
            $table->text('additional_essay_1')->nullable();
            $table->text('additional_essay_2')->nullable();
            $table->text('research_interests')->nullable();
            
            // Extracurricular Activities (JSON)
            $table->json('extracurricular_activities')->nullable();
            $table->json('awards_honors')->nullable();
            $table->json('work_experience')->nullable();
            $table->json('volunteer_experience')->nullable();
            
            // References/Recommendations
            $table->json('references')->nullable();
            /* Example:
            [
                {
                    "name": "Dr. John Smith",
                    "title": "Professor",
                    "institution": "Previous University",
                    "email": "jsmith@university.edu",
                    "phone": "123-456-7890",
                    "relationship": "Academic Advisor",
                    "letter_received": true,
                    "letter_received_date": "2024-09-01"
                }
            ]
            */
            
            // Application Status Tracking
            $table->enum('status', [
                'draft',
                'submitted',
                'under_review',
                'documents_pending',
                'committee_review',
                'interview_scheduled',
                'decision_pending',
                'admitted',
                'conditional_admit',
                'waitlisted',
                'denied',
                'deferred',
                'withdrawn',
                'expired'
            ])->default('draft');
            
            // Decision Information
            $table->enum('decision', [
                'admit',
                'conditional_admit',
                'waitlist',
                'deny',
                'defer'
            ])->nullable();
            $table->date('decision_date')->nullable();
            $table->foreignId('decision_by')->nullable()->constrained('users');
            $table->text('decision_reason')->nullable();
            $table->text('admission_conditions')->nullable(); // For conditional admits
            
            // Enrollment Confirmation
            $table->boolean('enrollment_confirmed')->default(false);
            $table->date('enrollment_confirmation_date')->nullable();
            $table->boolean('enrollment_deposit_paid')->default(false);
            $table->decimal('enrollment_deposit_amount', 8, 2)->nullable();
            $table->date('enrollment_deposit_date')->nullable();
            $table->string('enrollment_deposit_receipt', 50)->nullable();
            $table->date('enrollment_deadline')->nullable();
            $table->boolean('enrollment_declined')->default(false);
            $table->date('enrollment_declined_date')->nullable();
            $table->text('enrollment_declined_reason')->nullable();
            
            // Fee Information
            $table->boolean('application_fee_paid')->default(false);
            $table->decimal('application_fee_amount', 8, 2)->nullable();
            $table->date('application_fee_date')->nullable();
            $table->string('application_fee_receipt', 50)->nullable();
            $table->boolean('fee_waiver_requested')->default(false);
            $table->boolean('fee_waiver_approved')->default(false);
            $table->string('fee_waiver_reason')->nullable();
            
            // Important Dates
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completed_at')->nullable(); // All documents received
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamp('expires_at')->nullable(); // Application expiry
            
            // User Account Link (if they create an account)
            $table->foreignId('user_id')->nullable()->constrained('users');
            
            // Audit Fields
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('activity_log')->nullable(); // Track all status changes
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index('application_number');
            $table->index('email');
            $table->index('status');
            $table->index('decision');
            $table->index('term_id');
            $table->index('program_id');
            $table->index(['status', 'term_id']);
            $table->index(['decision', 'term_id']);
            $table->index('submitted_at');
        });

        // 2. Application Documents Table
        Schema::create('application_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('admission_applications')->onDelete('cascade');
            
            $table->enum('document_type', [
                'transcript',
                'high_school_transcript',
                'university_transcript',
                'diploma',
                'degree_certificate',
                'test_scores',
                'recommendation_letter',
                'personal_statement',
                'essay',
                'resume',
                'portfolio',
                'financial_statement',
                'bank_statement',
                'sponsor_letter',
                'passport',
                'national_id',
                'birth_certificate',
                'medical_certificate',
                'english_proficiency',
                'other'
            ]);
            
            $table->string('document_name', 255);
            $table->string('original_filename', 255);
            $table->string('file_path', 500);
            $table->string('file_type', 50); // mime type
            $table->integer('file_size'); // in bytes
            $table->string('file_hash', 64)->nullable(); // For integrity check
            
            // Document Status
            $table->enum('status', [
                'uploaded',
                'pending_verification',
                'verified',
                'rejected',
                'expired'
            ])->default('uploaded');
            
            // Verification Details
            $table->boolean('is_verified')->default(false);
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // For recommendation letters
            $table->string('recommender_name', 200)->nullable();
            $table->string('recommender_email', 255)->nullable();
            $table->string('recommender_title', 100)->nullable();
            $table->string('recommender_institution', 255)->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['application_id', 'document_type']);
            $table->index('status');
        });

        // 3. Application Reviews Table
        Schema::create('application_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('admission_applications')->onDelete('cascade');
            $table->foreignId('reviewer_id')->constrained('users');
            
            // Review Stage
            $table->enum('review_stage', [
                'initial_review',
                'academic_review',
                'department_review',
                'committee_review',
                'final_review'
            ]);
            
            // Ratings (1-5 scale)
            $table->integer('academic_rating')->nullable()->check('academic_rating >= 1 AND academic_rating <= 5');
            $table->integer('extracurricular_rating')->nullable()->check('extracurricular_rating >= 1 AND extracurricular_rating <= 5');
            $table->integer('essay_rating')->nullable()->check('essay_rating >= 1 AND essay_rating <= 5');
            $table->integer('recommendation_rating')->nullable()->check('recommendation_rating >= 1 AND recommendation_rating <= 5');
            $table->integer('interview_rating')->nullable()->check('interview_rating >= 1 AND interview_rating <= 5');
            $table->integer('overall_rating')->nullable()->check('overall_rating >= 1 AND overall_rating <= 5');
            
            // Detailed Evaluation
            $table->text('academic_comments')->nullable();
            $table->text('extracurricular_comments')->nullable();
            $table->text('essay_comments')->nullable();
            $table->text('strengths')->nullable();
            $table->text('weaknesses')->nullable();
            $table->text('additional_comments')->nullable();
            
            // Recommendation
            $table->enum('recommendation', [
                'strongly_recommend',
                'recommend',
                'recommend_with_reservations',
                'do_not_recommend',
                'defer_decision'
            ])->nullable();
            
            // Review Status
            $table->enum('status', [
                'pending',
                'in_progress',
                'completed',
                'skipped'
            ])->default('pending');
            
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('review_duration_minutes')->nullable(); // Track time spent
            
            $table->timestamps();
            
            // Indexes
            $table->index(['application_id', 'review_stage']);
            $table->index(['reviewer_id', 'status']);
            $table->unique(['application_id', 'reviewer_id', 'review_stage']);
        });

        // 4. Application Checklist Items Table
        Schema::create('application_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('admission_applications')->onDelete('cascade');
            
            $table->string('item_name', 200);
            $table->enum('item_type', [
                'document',
                'form',
                'fee',
                'test_score',
                'recommendation',
                'interview',
                'other'
            ]);
            
            $table->boolean('is_required')->default(true);
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['application_id', 'is_completed']);
        });

        // 5. Application Communications Table
        Schema::create('application_communications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('admission_applications')->onDelete('cascade');
            
            $table->enum('communication_type', [
                'email',
                'sms',
                'letter',
                'portal_message',
                'phone_call'
            ]);
            
            $table->enum('direction', ['outbound', 'inbound']);
            
            $table->string('subject', 255)->nullable();
            $table->text('message');
            $table->string('recipient_email')->nullable();
            $table->string('recipient_phone')->nullable();
            $table->string('sender_name')->nullable();
            $table->foreignId('sender_id')->nullable()->constrained('users');
            
            $table->enum('status', [
                'pending',
                'sent',
                'delivered',
                'failed',
                'bounced',
                'opened',
                'clicked'
            ])->default('pending');
            
            $table->string('template_used', 100)->nullable();
            $table->json('template_variables')->nullable();
            
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['application_id', 'communication_type']);
            $table->index('status');
        });

        // 6. Enrollment Confirmations Table
        Schema::create('enrollment_confirmations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('admission_applications')->onDelete('cascade');
            
            // Enrollment Decision
            $table->enum('decision', [
                'accept',
                'decline',
                'defer',
                'pending'
            ])->default('pending');
            
            $table->timestamp('decision_date')->nullable();
            $table->text('decision_reason')->nullable();
            
            // Enrollment Requirements
            $table->boolean('deposit_paid')->default(false);
            $table->decimal('deposit_amount', 8, 2)->nullable();
            $table->timestamp('deposit_paid_date')->nullable();
            $table->string('deposit_transaction_id', 100)->nullable();
            
            $table->boolean('health_form_submitted')->default(false);
            $table->boolean('immunization_submitted')->default(false);
            $table->boolean('housing_applied')->default(false);
            $table->boolean('orientation_registered')->default(false);
            $table->boolean('id_card_processed')->default(false);
            
            // Important Dates
            $table->date('enrollment_deadline');
            $table->date('orientation_date')->nullable();
            $table->date('move_in_date')->nullable();
            $table->date('classes_start_date')->nullable();
            
            // Student Account Creation
            $table->boolean('student_account_created')->default(false);
            $table->string('student_id', 20)->nullable();
            $table->foreignId('student_record_id')->nullable()->constrained('students');
            
            $table->timestamps();
            
            // Indexes
            $table->unique('application_id');
            $table->index('decision');
        });

        // 7. Application Fees Table
        Schema::create('application_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('admission_applications')->onDelete('cascade');
            
            $table->enum('fee_type', [
                'application_fee',
                'enrollment_deposit',
                'housing_deposit',
                'orientation_fee',
                'document_evaluation_fee'
            ]);
            
            $table->decimal('amount', 8, 2);
            $table->string('currency', 3)->default('USD');
            
            $table->enum('status', [
                'pending',
                'paid',
                'waived',
                'refunded',
                'cancelled'
            ])->default('pending');
            
            $table->enum('payment_method', [
                'credit_card',
                'debit_card',
                'bank_transfer',
                'mobile_money',
                'cash',
                'check',
                'waiver'
            ])->nullable();
            
            $table->string('transaction_id', 100)->nullable();
            $table->string('receipt_number', 50)->nullable();
            
            $table->timestamp('due_date')->nullable();
            $table->timestamp('paid_date')->nullable();
            $table->timestamp('refunded_date')->nullable();
            
            $table->text('notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['application_id', 'fee_type']);
            $table->index('status');
        });

        // 8. Application Settings Table (for configuration)
        Schema::create('admission_settings', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('term_id')->constrained('academic_terms');
            $table->foreignId('program_id')->nullable()->constrained('academic_programs');
            
            $table->date('application_open_date');
            $table->date('application_close_date');
            $table->date('decision_release_date')->nullable();
            $table->date('enrollment_deadline')->nullable();
            
            $table->decimal('application_fee', 8, 2);
            $table->decimal('enrollment_deposit', 8, 2);
            
            $table->integer('max_applications')->nullable(); // Cap on total applications
            $table->integer('target_enrollment')->nullable();
            
            $table->json('required_documents')->nullable();
            $table->json('admission_criteria')->nullable();
            
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            // Indexes
            $table->unique(['term_id', 'program_id']);
        });

        // 9. Interview Schedules Table
        Schema::create('admission_interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('admission_applications')->onDelete('cascade');
            $table->foreignId('interviewer_id')->constrained('users');
            
            $table->dateTime('scheduled_at');
            $table->integer('duration_minutes')->default(30);
            
            $table->enum('interview_type', [
                'in_person',
                'phone',
                'video',
                'group'
            ]);
            
            $table->string('location')->nullable();
            $table->string('meeting_link')->nullable();
            $table->string('meeting_id')->nullable();
            
            $table->enum('status', [
                'scheduled',
                'confirmed',
                'completed',
                'cancelled',
                'no_show',
                'rescheduled'
            ])->default('scheduled');
            
            $table->text('notes')->nullable();
            $table->integer('interview_score')->nullable();
            $table->text('feedback')->nullable();
            
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['application_id', 'status']);
            $table->index(['interviewer_id', 'scheduled_at']);
        });

        // 10. Waitlist Management Table
        Schema::create('admission_waitlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('admission_applications')->onDelete('cascade');
            $table->foreignId('term_id')->constrained('academic_terms');
            $table->foreignId('program_id')->constrained('academic_programs');
            
            $table->integer('rank')->nullable();
            $table->integer('original_rank')->nullable();
            
            $table->enum('status', [
                'active',
                'offered',
                'accepted',
                'declined',
                'expired',
                'removed'
            ])->default('active');
            
            $table->date('offer_date')->nullable();
            $table->date('offer_expiry_date')->nullable();
            $table->date('response_date')->nullable();
            
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['term_id', 'program_id', 'status']);
            $table->index(['application_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admission_waitlists');
        Schema::dropIfExists('admission_interviews');
        Schema::dropIfExists('admission_settings');
        Schema::dropIfExists('application_fees');
        Schema::dropIfExists('enrollment_confirmations');
        Schema::dropIfExists('application_communications');
        Schema::dropIfExists('application_checklist_items');
        Schema::dropIfExists('application_reviews');
        Schema::dropIfExists('application_documents');
        Schema::dropIfExists('admission_applications');
    }
};