<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            
            // System Fields
            $table->string('student_id', 10)->unique(); // Format: YYXXXXXX
            $table->unsignedBigInteger('user_id')->nullable();
            
            // Personal Information
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100);
            $table->string('preferred_name', 100)->nullable();
            $table->string('email', 255)->unique();
            $table->string('secondary_email', 255)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('home_phone', 20)->nullable();
            $table->string('work_phone', 20)->nullable();
            $table->date('date_of_birth');
            $table->string('place_of_birth', 100)->nullable();
            $table->enum('gender', ['male', 'female', 'other']);
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable();
            $table->string('religion', 50)->nullable();
            $table->string('ethnicity', 50)->nullable();
            $table->string('nationality', 100)->nullable();
            $table->string('national_id_number', 50)->nullable();
            
            // Addresses
            $table->string('address', 500)->nullable();
            $table->string('permanent_address', 500)->nullable();
            
            // Academic Information
            $table->string('program_name', 200);
            $table->integer('program_id')->nullable();
            $table->string('department', 100)->nullable();
            $table->string('major', 100)->nullable();
            $table->string('minor', 100)->nullable();
            $table->enum('academic_level', ['freshman', 'sophomore', 'junior', 'senior', 'graduate']);
            $table->enum('enrollment_status', ['active', 'inactive', 'suspended', 'graduated', 'withdrawn'])->default('active');
            $table->enum('academic_standing', ['good', 'probation', 'suspension', 'dismissal'])->default('good');
            $table->enum('admission_status', ['prospective', 'admitted', 'enrolled', 'rejected'])->default('enrolled');
            $table->date('admission_date');
            $table->integer('expected_graduation_year')->nullable();
            $table->date('graduation_date')->nullable();
            $table->string('degree_awarded', 200)->nullable();
            $table->decimal('current_gpa', 3, 2)->default(0.00);
            $table->decimal('cumulative_gpa', 3, 2)->default(0.00);
            $table->integer('credits_earned')->default(0);
            $table->integer('credits_completed')->default(0);
            $table->integer('credits_required')->default(120);
            
            // Previous Education
            $table->string('high_school_name', 200)->nullable();
            $table->year('high_school_graduation_year')->nullable();
            $table->decimal('high_school_gpa', 3, 2)->nullable();
            $table->string('previous_university', 200)->nullable();
            $table->text('transfer_credits_info')->nullable();
            $table->text('previous_education')->nullable();
            
            // Advisory
            $table->string('advisor_name', 200)->nullable();
            
            // Guardian Information
            $table->string('guardian_name', 200)->nullable();
            $table->string('guardian_phone', 20)->nullable();
            $table->string('guardian_email', 255)->nullable();
            
            // Emergency Contact
            $table->string('emergency_contact_name', 200)->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            
            // Next of Kin
            $table->string('next_of_kin_name', 200)->nullable();
            $table->string('next_of_kin_relationship', 50)->nullable();
            $table->string('next_of_kin_phone', 20)->nullable();
            
            // Medical Information
            $table->string('blood_group', 10)->nullable();
            $table->text('medical_conditions')->nullable();
            $table->string('insurance_provider', 100)->nullable();
            $table->string('insurance_policy_number', 50)->nullable();
            $table->date('insurance_expiry')->nullable();
            
            // Documents
            $table->string('profile_photo')->nullable();
            $table->boolean('has_profile_photo')->default(false);
            $table->boolean('has_national_id_copy')->default(false);
            $table->boolean('has_high_school_certificate')->default(false);
            $table->boolean('has_high_school_transcript')->default(false);
            $table->boolean('has_immunization_records')->default(false);
            
            // International Students
            $table->boolean('is_international')->default(false);
            $table->string('passport_number', 50)->nullable();
            $table->string('visa_status', 20)->nullable();
            $table->date('visa_expiry')->nullable();
            
            // Enrollment Lifecycle
            $table->date('application_date')->nullable();
            $table->date('admission_decision_date')->nullable();
            $table->date('enrollment_confirmation_date')->nullable();
            $table->date('last_enrollment_date')->nullable();
            $table->date('leave_start_date')->nullable();
            $table->date('leave_end_date')->nullable();
            $table->text('leave_reason')->nullable();
            $table->date('withdrawal_date')->nullable();
            $table->text('withdrawal_reason')->nullable();
            $table->date('readmission_date')->nullable();
            $table->boolean('is_alumni')->default(false);
            
            // Audit Fields
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->json('change_history')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            
            // Laravel Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for Performance
            $table->index('student_id');
            $table->index('email');
            $table->index('enrollment_status');
            $table->index('academic_standing');
            $table->index('academic_level');
            $table->index('program_name');
            $table->index('department');
            $table->index('user_id');
            $table->index('is_international');
            $table->index('is_alumni');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};