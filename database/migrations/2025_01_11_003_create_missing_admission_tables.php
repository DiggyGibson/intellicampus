<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for missing critical admissions components.
     */
    public function up(): void
    {
        // First, let's check which tables already exist to avoid conflicts
        
        // 1. Admission Interviews Table (Referenced in AdmissionApplication model)
        if (!Schema::hasTable('admission_interviews')) {
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
                $table->string('meeting_password')->nullable();
                
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
                $table->json('evaluation_criteria')->nullable();
                
                $table->timestamp('confirmed_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->text('cancellation_reason')->nullable();
                
                $table->timestamps();
                
                // Indexes
                $table->index(['application_id', 'status']);
                $table->index(['interviewer_id', 'scheduled_at']);
                $table->index('scheduled_at');
            });
        }

        // 2. Admission Waitlists Table (Referenced in AdmissionApplication model)
        if (!Schema::hasTable('admission_waitlists')) {
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
                $table->string('removal_reason')->nullable();
                
                $table->timestamps();
                
                // Indexes
                $table->unique('application_id');
                $table->index(['term_id', 'program_id', 'status']);
                $table->index(['term_id', 'program_id', 'rank']);
                $table->index('status');
            });
        }

        // 3. Admission Settings Table (For configuration per term/program)
        if (!Schema::hasTable('admission_settings')) {
            Schema::create('admission_settings', function (Blueprint $table) {
                $table->id();
                
                $table->foreignId('term_id')->constrained('academic_terms');
                $table->foreignId('program_id')->nullable()->constrained('academic_programs');
                
                $table->date('application_open_date');
                $table->date('application_close_date');
                $table->date('early_decision_deadline')->nullable();
                $table->date('regular_decision_deadline')->nullable();
                $table->date('decision_release_date')->nullable();
                $table->date('enrollment_deadline')->nullable();
                
                $table->decimal('application_fee', 8, 2);
                $table->decimal('enrollment_deposit', 8, 2);
                $table->decimal('international_application_fee', 8, 2)->nullable();
                
                $table->integer('max_applications')->nullable();
                $table->integer('target_enrollment')->nullable();
                $table->integer('waitlist_size')->nullable();
                
                $table->json('required_documents')->nullable();
                $table->json('admission_criteria')->nullable();
                $table->json('auto_decision_rules')->nullable();
                
                $table->boolean('rolling_admissions')->default(false);
                $table->boolean('is_active')->default(true);
                
                $table->timestamps();
                
                // Indexes
                $table->unique(['term_id', 'program_id']);
                $table->index('is_active');
            });
        }

        // 4. Application Status History (Audit trail for status changes)
        if (!Schema::hasTable('application_status_history')) {
            Schema::create('application_status_history', function (Blueprint $table) {
                $table->id();
                $table->foreignId('application_id')->constrained('admission_applications')->onDelete('cascade');
                
                $table->string('old_status')->nullable();
                $table->string('new_status');
                $table->text('reason')->nullable();
                $table->text('notes')->nullable();
                
                $table->foreignId('changed_by')->nullable()->constrained('users');
                $table->string('changed_by_role')->nullable();
                
                $table->json('metadata')->nullable(); // Additional context
                
                $table->timestamp('changed_at');
                $table->timestamps();
                
                // Indexes
                $table->index(['application_id', 'changed_at']);
                $table->index('new_status');
            });
        }

        // 5. Recommendation Letters Management
        if (!Schema::hasTable('recommendation_letters')) {
            Schema::create('recommendation_letters', function (Blueprint $table) {
                $table->id();
                $table->foreignId('application_id')->constrained('admission_applications')->onDelete('cascade');
                
                $table->string('recommender_name');
                $table->string('recommender_email');
                $table->string('recommender_phone')->nullable();
                $table->string('recommender_title')->nullable();
                $table->string('recommender_institution')->nullable();
                $table->string('relationship_to_applicant');
                $table->integer('years_known')->nullable();
                
                $table->string('request_token')->unique();
                $table->timestamp('request_sent_at')->nullable();
                $table->timestamp('reminder_sent_at')->nullable();
                $table->integer('reminder_count')->default(0);
                
                $table->enum('status', [
                    'pending',
                    'invited',
                    'in_progress',
                    'submitted',
                    'declined',
                    'expired'
                ])->default('pending');
                
                $table->text('letter_content')->nullable();
                $table->string('letter_file_path')->nullable();
                
                $table->json('ratings')->nullable(); // Structured ratings
                $table->timestamp('submitted_at')->nullable();
                $table->timestamp('declined_at')->nullable();
                $table->text('decline_reason')->nullable();
                
                $table->boolean('waived_right_to_view')->default(true);
                
                $table->timestamps();
                
                // Indexes
                $table->index('application_id');
                $table->index('request_token');
                $table->index('status');
                $table->index('recommender_email');
            });
        }

        // 6. Application Notes (Internal notes by staff)
        if (!Schema::hasTable('application_notes')) {
            Schema::create('application_notes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('application_id')->constrained('admission_applications')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users');
                
                $table->text('note');
                $table->enum('visibility', [
                    'private',      // Only creator can see
                    'reviewers',    // All reviewers can see
                    'staff',        // All admissions staff
                    'public'        // Can be shared with applicant
                ])->default('reviewers');
                
                $table->enum('type', [
                    'general',
                    'academic',
                    'financial',
                    'document',
                    'interview',
                    'decision',
                    'follow_up'
                ])->default('general');
                
                $table->boolean('is_important')->default(false);
                $table->boolean('requires_action')->default(false);
                $table->date('action_due_date')->nullable();
                $table->boolean('action_completed')->default(false);
                
                $table->timestamps();
                
                // Indexes
                $table->index(['application_id', 'created_at']);
                $table->index(['user_id', 'created_at']);
                $table->index('requires_action');
            });
        }

        // 7. Application Templates (Email/Letter templates)
        if (!Schema::hasTable('application_templates')) {
            Schema::create('application_templates', function (Blueprint $table) {
                $table->id();
                
                $table->string('name');
                $table->string('code')->unique(); // e.g., 'admission_offer', 'rejection_letter'
                $table->enum('type', [
                    'email',
                    'letter',
                    'sms',
                    'notification'
                ]);
                
                $table->string('subject')->nullable();
                $table->text('content');
                $table->json('variables')->nullable(); // Available merge fields
                
                $table->enum('trigger_event', [
                    'application_started',
                    'application_submitted',
                    'documents_received',
                    'review_completed',
                    'interview_scheduled',
                    'decision_made',
                    'enrollment_confirmed',
                    'custom'
                ])->nullable();
                
                $table->boolean('is_active')->default(true);
                $table->boolean('is_default')->default(false);
                
                $table->timestamps();
                
                // Indexes
                $table->index('code');
                $table->index('type');
                $table->index('trigger_event');
            });
        }

        // 8. Countries Table - Check if it doesn't already exist
        if (!Schema::hasTable('countries')) {
            Schema::create('countries', function (Blueprint $table) {
                $table->id();
                $table->string('code', 2)->unique(); // ISO 3166-1 alpha-2
                $table->string('code3', 3)->unique(); // ISO 3166-1 alpha-3
                $table->string('name');
                $table->string('native_name')->nullable();
                $table->string('phone_code', 10)->nullable();
                $table->string('capital')->nullable();
                $table->string('currency', 3)->nullable();
                $table->string('region')->nullable();
                $table->string('subregion')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                
                $table->timestamps();
                
                // Indexes
                $table->index('name');
                $table->index('is_active');
            });
        }

        // 9. States Table - Check if it doesn't already exist
        if (!Schema::hasTable('states')) {
            Schema::create('states', function (Blueprint $table) {
                $table->id();
                $table->foreignId('country_id')->constrained('countries');
                $table->string('code', 10)->nullable();
                $table->string('name');
                $table->string('type')->nullable(); // state, province, region, etc.
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                
                $table->timestamps();
                
                // Indexes
                $table->index(['country_id', 'name']);
                $table->index('name');
            });
        }

        // 10. Cities Table - Check if it doesn't already exist
        if (!Schema::hasTable('cities')) {
            Schema::create('cities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('state_id')->constrained('states');
                $table->foreignId('country_id')->constrained('countries');
                $table->string('name');
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
                $table->integer('population')->nullable();
                $table->boolean('is_capital')->default(false);
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                
                $table->timestamps();
                
                // Indexes
                $table->index(['state_id', 'name']);
                $table->index(['country_id', 'name']);
                $table->index('name');
            });
        }

        // 11. Application Statistics Table (For analytics and reporting)
        if (!Schema::hasTable('application_statistics')) {
            Schema::create('application_statistics', function (Blueprint $table) {
                $table->id();
                
                $table->foreignId('term_id')->constrained('academic_terms');
                $table->foreignId('program_id')->nullable()->constrained('academic_programs');
                
                $table->date('statistics_date');
                $table->string('period_type', 20); // daily, weekly, monthly
                
                // Application counts
                $table->integer('total_applications')->default(0);
                $table->integer('completed_applications')->default(0);
                $table->integer('incomplete_applications')->default(0);
                $table->integer('withdrawn_applications')->default(0);
                
                // Decision counts
                $table->integer('admitted')->default(0);
                $table->integer('denied')->default(0);
                $table->integer('waitlisted')->default(0);
                $table->integer('deferred')->default(0);
                
                // Enrollment counts
                $table->integer('deposits_received')->default(0);
                $table->integer('enrolled')->default(0);
                $table->integer('declined_offers')->default(0);
                
                // Demographics
                $table->json('demographics')->nullable();
                
                // Academic metrics
                $table->decimal('average_gpa', 3, 2)->nullable();
                $table->integer('average_test_score')->nullable();
                
                // Conversion rates
                $table->decimal('application_completion_rate', 5, 2)->nullable();
                $table->decimal('admission_rate', 5, 2)->nullable();
                $table->decimal('yield_rate', 5, 2)->nullable();
                
                $table->timestamps();
                
                // Indexes
                $table->unique(['term_id', 'program_id', 'statistics_date', 'period_type'], 'unique_statistics');
                $table->index(['term_id', 'program_id']);
                $table->index('statistics_date');
            });
        }

        // 12. Program Prerequisites Table
        if (!Schema::hasTable('program_prerequisites')) {
            Schema::create('program_prerequisites', function (Blueprint $table) {
                $table->id();
                
                $table->foreignId('program_id')->constrained('academic_programs');
                $table->string('prerequisite_type'); // course, degree, experience, certification
                $table->string('prerequisite_code')->nullable();
                $table->string('prerequisite_name');
                $table->text('description')->nullable();
                
                $table->boolean('is_required')->default(true);
                $table->json('alternative_prerequisites')->nullable(); // JSON array of alternatives
                
                $table->decimal('minimum_grade', 3, 2)->nullable();
                $table->integer('minimum_years')->nullable(); // For experience
                
                $table->boolean('can_be_waived')->default(false);
                $table->text('waiver_conditions')->nullable();
                
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                
                $table->timestamps();
                
                // Indexes
                $table->index('program_id');
                $table->index('prerequisite_type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop tables in reverse order to handle foreign key constraints
        Schema::dropIfExists('program_prerequisites');
        Schema::dropIfExists('application_statistics');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('states');
        Schema::dropIfExists('countries');
        Schema::dropIfExists('application_templates');
        Schema::dropIfExists('application_notes');
        Schema::dropIfExists('recommendation_letters');
        Schema::dropIfExists('application_status_history');
        Schema::dropIfExists('admission_settings');
        Schema::dropIfExists('admission_waitlists');
        Schema::dropIfExists('admission_interviews');
    }
};