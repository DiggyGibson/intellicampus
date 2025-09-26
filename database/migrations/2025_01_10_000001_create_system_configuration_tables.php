<?php
// File: database/migrations/2025_01_10_000001_create_system_configuration_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // System Settings Table
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('category', 100);
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->string('type', 50);
            $table->text('description')->nullable();
            $table->json('options')->nullable();
            $table->boolean('is_public')->default(false);
            $table->boolean('is_editable')->default(true);
            $table->timestamps();
            
            $table->index('category');
            $table->index(['category', 'key']);
        });

        // Academic Calendar Configuration
        Schema::create('academic_calendars', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('academic_year');
            $table->date('year_start');
            $table->date('year_end');
            $table->boolean('is_active')->default(false);
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index('academic_year');
            $table->index('is_active');
        });

        // Academic Calendar Events
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_calendar_id')->constrained()->onDelete('cascade');
            $table->foreignId('term_id')->nullable()->constrained('academic_terms');
            $table->string('event_type', 50);
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->boolean('all_day')->default(true);
            $table->boolean('is_holiday')->default(false);
            $table->boolean('affects_classes')->default(false);
            $table->string('visibility', 50)->default('public');
            $table->string('priority', 20)->default('normal');
            $table->json('applicable_to')->nullable();
            $table->timestamps();
            
            $table->index(['academic_calendar_id', 'start_date']);
            $table->index('event_type');
            $table->index('visibility');
        });

        // Institution Configuration
        Schema::create('institution_config', function (Blueprint $table) {
            $table->id();
            $table->string('institution_name');
            $table->string('institution_code', 50)->unique();
            $table->string('institution_type', 50);
            $table->text('address');
            $table->string('city', 100);
            $table->string('state', 100);
            $table->string('country', 100);
            $table->string('postal_code', 20);
            $table->string('phone', 50);
            $table->string('email');
            $table->string('website')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('timezone', 50)->default('UTC');
            $table->string('currency_code', 10)->default('USD');
            $table->string('currency_symbol', 10)->default('$');
            $table->string('date_format', 20)->default('Y-m-d');
            $table->string('time_format', 20)->default('H:i');
            $table->json('social_media')->nullable();
            $table->json('accreditations')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Academic Periods Configuration
        Schema::create('academic_period_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('code', 20)->unique();
            $table->integer('periods_per_year');
            $table->integer('weeks_per_period');
            $table->integer('instruction_weeks');
            $table->integer('exam_weeks');
            $table->boolean('has_breaks')->default(true);
            $table->json('break_configuration')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Credit Hour Configuration
        Schema::create('credit_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('credit_system', 50);
            $table->integer('min_credits_full_time');
            $table->integer('max_credits_regular');
            $table->integer('max_credits_overload');
            $table->integer('min_credits_graduation');
            $table->decimal('hours_per_credit', 4, 2);
            $table->json('credit_rules')->nullable();
            $table->timestamps();
        });

        // Grading System Configuration
        Schema::create('grading_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('grading_system', 50);
            $table->decimal('max_gpa', 3, 2);
            $table->decimal('passing_gpa', 3, 2);
            $table->decimal('probation_gpa', 3, 2);
            $table->decimal('honors_gpa', 3, 2);
            $table->decimal('high_honors_gpa', 3, 2);
            $table->json('grade_scale')->nullable();
            $table->json('gpa_calculation_rules')->nullable();
            $table->boolean('use_plus_minus')->default(true);
            $table->boolean('include_failed_in_gpa')->default(true);
            $table->timestamps();
        });

        // Attendance Configuration
        Schema::create('attendance_configurations', function (Blueprint $table) {
            $table->id();
            $table->boolean('track_attendance')->default(true);
            $table->integer('max_absences_allowed')->nullable();
            $table->decimal('attendance_weight_in_grade', 5, 2)->default(0);
            $table->string('attendance_calculation_method', 50)->default('percentage'); 
            $table->json('attendance_rules')->nullable();
            $table->boolean('notify_on_absence')->default(true);
            $table->integer('absence_notification_threshold')->default(3);
            $table->timestamps();
        });

        // Registration Configuration
        Schema::create('registration_configurations', function (Blueprint $table) {
            $table->id();
            $table->boolean('allow_online_registration')->default(true);
            $table->integer('registration_priority_days')->default(7);
            $table->boolean('enforce_prerequisites')->default(true);
            $table->boolean('allow_time_conflicts')->default(false);
            $table->boolean('allow_waitlist')->default(true);
            $table->integer('max_waitlist_size')->default(10);
            $table->integer('drop_deadline_weeks')->default(2);
            $table->integer('withdraw_deadline_weeks')->default(10);
            $table->decimal('late_registration_fee', 10, 2)->default(50);
            $table->json('registration_rules')->nullable();
            $table->timestamps();
        });

        // Email Templates
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('category', 50);
            $table->string('subject');
            $table->text('body');
            $table->json('variables')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('category');
            $table->index('is_active');
        });

        // System Modules Configuration
        Schema::create('system_modules', function (Blueprint $table) {
            $table->id();
            $table->string('module_name', 100);
            $table->string('module_code', 50)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->json('configuration')->nullable();
            $table->json('permissions')->nullable();
            $table->integer('display_order')->default(0);
            $table->timestamps();
            
            $table->index('is_enabled');
            $table->index('display_order');
        });

        // Insert default data
        $this->insertDefaultData();
    }

    private function insertDefaultData()
    {
        // Default System Settings - Fixed insert statement
        $settings = [
            ['category' => 'general', 'key' => 'maintenance_mode', 'value' => 'false', 'type' => 'boolean', 'description' => 'Enable maintenance mode', 'created_at' => now(), 'updated_at' => now()],
            ['category' => 'general', 'key' => 'allow_registration', 'value' => 'true', 'type' => 'boolean', 'description' => 'Allow new user registration', 'created_at' => now(), 'updated_at' => now()],
            ['category' => 'general', 'key' => 'default_language', 'value' => 'en', 'type' => 'text', 'description' => 'Default system language', 'created_at' => now(), 'updated_at' => now()],
            ['category' => 'academic', 'key' => 'current_term_id', 'value' => '1', 'type' => 'number', 'description' => 'Current academic term', 'created_at' => now(), 'updated_at' => now()],
            ['category' => 'academic', 'key' => 'allow_course_shopping', 'value' => 'true', 'type' => 'boolean', 'description' => 'Allow course shopping period', 'created_at' => now(), 'updated_at' => now()],
            ['category' => 'academic', 'key' => 'shopping_period_days', 'value' => '7', 'type' => 'number', 'description' => 'Course shopping period in days', 'created_at' => now(), 'updated_at' => now()],
            ['category' => 'financial', 'key' => 'payment_gateway', 'value' => 'stripe', 'type' => 'text', 'description' => 'Active payment gateway', 'created_at' => now(), 'updated_at' => now()],
            ['category' => 'financial', 'key' => 'allow_partial_payment', 'value' => 'true', 'type' => 'boolean', 'description' => 'Allow partial payments', 'created_at' => now(), 'updated_at' => now()],
            ['category' => 'financial', 'key' => 'late_payment_fee', 'value' => '25', 'type' => 'number', 'description' => 'Late payment fee amount', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('system_settings')->insert($settings);

        // Default Institution Config
        DB::table('institution_config')->insert([
            'institution_name' => 'IntelliCampus University',
            'institution_code' => 'ICU',
            'institution_type' => 'university',
            'address' => '123 Education Boulevard',
            'city' => 'Knowledge City',
            'state' => 'Learning State',
            'country' => 'United States',
            'postal_code' => '12345',
            'phone' => '+1-555-123-4567',
            'email' => 'info@intellicampus.edu',
            'website' => 'https://www.intellicampus.edu',
            'timezone' => 'America/New_York',
            'currency_code' => 'USD',
            'currency_symbol' => '$',
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Default Academic Period Types
        $periodTypes = [
            [
                'name' => 'Semester',
                'code' => 'SEMESTER',
                'periods_per_year' => 2,
                'weeks_per_period' => 16,
                'instruction_weeks' => 15,
                'exam_weeks' => 1,
                'has_breaks' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Quarter',
                'code' => 'QUARTER',
                'periods_per_year' => 4,
                'weeks_per_period' => 11,
                'instruction_weeks' => 10,
                'exam_weeks' => 1,
                'has_breaks' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('academic_period_types')->insert($periodTypes);

        // Default Credit Configuration
        DB::table('credit_configurations')->insert([
            'credit_system' => 'semester_hours',
            'min_credits_full_time' => 12,
            'max_credits_regular' => 18,
            'max_credits_overload' => 21,
            'min_credits_graduation' => 120,
            'hours_per_credit' => 1.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Default Grading Configuration
        DB::table('grading_configurations')->insert([
            'grading_system' => 'letter',
            'max_gpa' => 4.00,
            'passing_gpa' => 2.00,
            'probation_gpa' => 2.00,
            'honors_gpa' => 3.50,
            'high_honors_gpa' => 3.80,
            'use_plus_minus' => true,
            'include_failed_in_gpa' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Default Attendance Configuration
        DB::table('attendance_configurations')->insert([
            'track_attendance' => true,
            'max_absences_allowed' => 3,
            'attendance_weight_in_grade' => 10.00,
            'attendance_calculation_method' => 'percentage',
            'notify_on_absence' => true,
            'absence_notification_threshold' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Default Registration Configuration
        DB::table('registration_configurations')->insert([
            'allow_online_registration' => true,
            'registration_priority_days' => 7,
            'enforce_prerequisites' => true,
            'allow_time_conflicts' => false,
            'allow_waitlist' => true,
            'max_waitlist_size' => 10,
            'drop_deadline_weeks' => 2,
            'withdraw_deadline_weeks' => 10,
            'late_registration_fee' => 50.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Default System Modules
        $modules = [
            ['module_name' => 'Student Management', 'module_code' => 'STUDENT_MGMT', 'display_order' => 1, 'is_enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['module_name' => 'Course Management', 'module_code' => 'COURSE_MGMT', 'display_order' => 2, 'is_enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['module_name' => 'Registration', 'module_code' => 'REGISTRATION', 'display_order' => 3, 'is_enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['module_name' => 'Grading', 'module_code' => 'GRADING', 'display_order' => 4, 'is_enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['module_name' => 'Financial', 'module_code' => 'FINANCIAL', 'display_order' => 5, 'is_enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['module_name' => 'LMS', 'module_code' => 'LMS', 'display_order' => 6, 'is_enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['module_name' => 'Scheduling', 'module_code' => 'SCHEDULING', 'display_order' => 7, 'is_enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['module_name' => 'Examination', 'module_code' => 'EXAMINATION', 'display_order' => 8, 'is_enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['module_name' => 'Attendance', 'module_code' => 'ATTENDANCE', 'display_order' => 9, 'is_enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['module_name' => 'Admissions', 'module_code' => 'ADMISSIONS', 'display_order' => 10, 'is_enabled' => true, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('system_modules')->insert($modules);
    }

    public function down()
    {
        Schema::dropIfExists('system_modules');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('registration_configurations');
        Schema::dropIfExists('attendance_configurations');
        Schema::dropIfExists('grading_configurations');
        Schema::dropIfExists('credit_configurations');
        Schema::dropIfExists('academic_period_types');
        Schema::dropIfExists('institution_config');
        Schema::dropIfExists('calendar_events');
        Schema::dropIfExists('academic_calendars');
        Schema::dropIfExists('system_settings');
    }
};