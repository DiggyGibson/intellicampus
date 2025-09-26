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
        Schema::table('users', function (Blueprint $table) {
            // User Type and Status
            $table->string('username')->unique()->nullable()->after('email');
            $table->enum('user_type', ['student', 'faculty', 'staff', 'admin', 'parent'])->default('student')->after('password');
            $table->enum('status', ['active', 'inactive', 'suspended', 'pending'])->default('pending')->after('user_type');
            
            // Personal Information
            $table->string('title')->nullable()->after('status');
            $table->string('first_name')->nullable()->after('title');
            $table->string('middle_name')->nullable()->after('first_name');
            $table->string('last_name')->nullable()->after('middle_name');
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable()->after('last_name');
            $table->date('date_of_birth')->nullable()->after('gender');
            $table->string('nationality')->nullable()->after('date_of_birth');
            $table->string('national_id')->nullable()->after('nationality');
            $table->string('passport_number')->nullable()->after('national_id');
            
            // Contact Information
            $table->string('phone')->nullable()->after('passport_number');
            $table->string('alternate_phone')->nullable()->after('phone');
            $table->text('address')->nullable()->after('alternate_phone');
            $table->string('city')->nullable()->after('address');
            $table->string('state')->nullable()->after('city');
            $table->string('country')->nullable()->after('state');
            $table->string('postal_code')->nullable()->after('country');
            
            // Employment Information (for faculty/staff)
            $table->string('employee_id')->nullable()->after('postal_code');
            $table->string('department')->nullable()->after('employee_id');
            $table->string('designation')->nullable()->after('department');
            $table->string('office_location')->nullable()->after('designation');
            $table->string('office_phone')->nullable()->after('office_location');
            $table->date('date_of_joining')->nullable()->after('office_phone');
            $table->enum('employment_status', ['full-time', 'part-time', 'contract', 'visiting'])->nullable()->after('date_of_joining');
            $table->date('contract_end_date')->nullable()->after('employment_status');
            
            // Academic Information (for faculty)
            $table->string('highest_qualification')->nullable()->after('contract_end_date');
            $table->string('specialization')->nullable()->after('highest_qualification');
            $table->jsonb('research_interests')->nullable()->after('specialization');
            $table->jsonb('publications')->nullable()->after('research_interests');
            
            // Emergency Contact
            $table->string('emergency_contact_name')->nullable()->after('publications');
            $table->string('emergency_contact_relationship')->nullable()->after('emergency_contact_name');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_relationship');
            $table->string('emergency_contact_email')->nullable()->after('emergency_contact_phone');
            
            // Profile and Settings
            $table->string('profile_photo')->nullable()->after('emergency_contact_email');
            $table->text('bio')->nullable()->after('profile_photo');
            $table->jsonb('preferences')->nullable()->after('bio');
            $table->string('timezone')->default('UTC')->after('preferences');
            $table->string('language')->default('en')->after('timezone');
            $table->jsonb('notification_preferences')->nullable()->after('language');
            
            // Security Fields
            $table->timestamp('last_login_at')->nullable()->after('notification_preferences');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
            $table->timestamp('last_activity_at')->nullable()->after('last_login_ip');
            $table->timestamp('password_changed_at')->nullable()->after('last_activity_at');
            $table->boolean('must_change_password')->default(false)->after('password_changed_at');
            $table->integer('login_attempts')->default(0)->after('must_change_password');
            $table->timestamp('locked_until')->nullable()->after('login_attempts');
            $table->text('two_factor_secret')->nullable()->after('locked_until');
            $table->boolean('two_factor_enabled')->default(false)->after('two_factor_secret');
            $table->jsonb('security_questions')->nullable()->after('two_factor_enabled');
            
            // System Fields
            $table->unsignedBigInteger('created_by')->nullable()->after('security_questions');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            $table->jsonb('metadata')->nullable()->after('updated_by');
            $table->text('notes')->nullable()->after('metadata');
            
            // Soft Deletes
            $table->softDeletes()->after('notes');
            
            // Indexes for performance
            $table->index('username');
            $table->index('user_type');
            $table->index('status');
            $table->index('employee_id');
            $table->index('department');
            $table->index(['user_type', 'status']);
            
            // Foreign keys
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            
            // Drop indexes
            $table->dropIndex(['username']);
            $table->dropIndex(['user_type']);
            $table->dropIndex(['status']);
            $table->dropIndex(['employee_id']);
            $table->dropIndex(['department']);
            $table->dropIndex(['user_type', 'status']);
            
            // Drop all added columns
            $table->dropColumn([
                'username', 'user_type', 'status',
                'title', 'first_name', 'middle_name', 'last_name', 'gender',
                'date_of_birth', 'nationality', 'national_id', 'passport_number',
                'phone', 'alternate_phone', 'address', 'city', 'state', 'country', 'postal_code',
                'employee_id', 'department', 'designation', 'office_location', 'office_phone',
                'date_of_joining', 'employment_status', 'contract_end_date',
                'highest_qualification', 'specialization', 'research_interests', 'publications',
                'emergency_contact_name', 'emergency_contact_relationship',
                'emergency_contact_phone', 'emergency_contact_email',
                'profile_photo', 'bio', 'preferences', 'timezone', 'language', 'notification_preferences',
                'last_login_at', 'last_login_ip', 'last_activity_at', 'password_changed_at',
                'must_change_password', 'login_attempts', 'locked_until',
                'two_factor_secret', 'two_factor_enabled', 'security_questions',
                'created_by', 'updated_by', 'metadata', 'notes',
                'deleted_at'
            ]);
        });
    }
};