<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // 1. First, fix the users table constraint to allow 'applicant'
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_user_type_check");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_user_type_check 
                      CHECK (user_type IN ('student', 'faculty', 'admin', 'staff', 'applicant', 'guardian', 'alumni'))");

        // 2. Create program_types table (missing)
        if (!Schema::hasTable('program_types')) {
            Schema::create('program_types', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code', 20)->unique();
                $table->integer('level')->default(1);
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->index('code');
                $table->index('is_active');
            });
        }

        // 3. Create degrees table (for degree types)
        if (!Schema::hasTable('degrees')) {
            Schema::create('degrees', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // Bachelor of Science, Master of Arts, etc.
                $table->string('abbreviation', 20); // BS, MS, PhD, etc.
                $table->string('level'); // undergraduate, graduate, doctoral
                $table->integer('order')->default(1); // For sorting
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->index('abbreviation');
                $table->index('level');
            });
        }

        // 4. Create or update programs table (proper structure)
        if (!Schema::hasTable('programs')) {
            Schema::create('programs', function (Blueprint $table) {
                $table->id();
                $table->string('code', 20)->unique();
                $table->string('name');
                $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');
                $table->foreignId('program_type_id')->nullable()->constrained()->onDelete('set null');
                $table->foreignId('degree_id')->nullable()->constrained()->onDelete('set null');
                $table->integer('duration_years')->default(4);
                $table->integer('credits_required')->default(120);
                $table->decimal('min_gpa', 3, 2)->default(2.0);
                $table->text('description')->nullable();
                $table->json('requirements')->nullable(); // Admission requirements
                $table->json('learning_outcomes')->nullable();
                $table->json('career_prospects')->nullable();
                $table->string('delivery_mode')->default('on-campus'); // on-campus, online, hybrid
                $table->boolean('is_active')->default(true);
                $table->boolean('admission_open')->default(true);
                $table->integer('max_enrollment')->nullable();
                $table->integer('current_enrollment')->default(0);
                $table->decimal('application_fee', 8, 2)->default(50.00);
                $table->string('accreditation_status')->nullable();
                $table->date('accreditation_date')->nullable();
                $table->date('next_review_date')->nullable();
                $table->timestamps();
                $table->softDeletes();
                
                $table->index(['code', 'is_active']);
                $table->index('department_id');
                $table->index('program_type_id');
                $table->index('admission_open');
            });
        } else {
            // Add missing columns if table exists
            Schema::table('programs', function (Blueprint $table) {
                if (!Schema::hasColumn('programs', 'department_id')) {
                    $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');
                }
                if (!Schema::hasColumn('programs', 'program_type_id')) {
                    $table->foreignId('program_type_id')->nullable()->constrained()->onDelete('set null');
                }
                if (!Schema::hasColumn('programs', 'degree_id')) {
                    $table->foreignId('degree_id')->nullable()->constrained()->onDelete('set null');
                }
                if (!Schema::hasColumn('programs', 'credits_required')) {
                    $table->integer('credits_required')->default(120);
                }
                if (!Schema::hasColumn('programs', 'admission_open')) {
                    $table->boolean('admission_open')->default(true);
                }
                if (!Schema::hasColumn('programs', 'max_enrollment')) {
                    $table->integer('max_enrollment')->nullable();
                }
                if (!Schema::hasColumn('programs', 'current_enrollment')) {
                    $table->integer('current_enrollment')->default(0);
                }
                if (!Schema::hasColumn('programs', 'requirements')) {
                    $table->json('requirements')->nullable();
                }
                if (!Schema::hasColumn('programs', 'learning_outcomes')) {
                    $table->json('learning_outcomes')->nullable();
                }
                if (!Schema::hasColumn('programs', 'career_prospects')) {
                    $table->json('career_prospects')->nullable();
                }
            });
        }

        // 5. Create applicants table (separate from students!)
        if (!Schema::hasTable('applicants')) {
            Schema::create('applicants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
                $table->string('applicant_id')->unique(); // APP-2025-00001
                $table->string('first_name');
                $table->string('middle_name')->nullable();
                $table->string('last_name');
                $table->date('date_of_birth')->nullable();
                $table->enum('gender', ['male', 'female', 'other', 'prefer_not_to_say'])->nullable();
                $table->string('phone')->nullable();
                $table->string('mobile')->nullable();
                $table->text('address')->nullable();
                $table->string('city')->nullable();
                $table->string('state')->nullable();
                $table->string('country')->nullable();
                $table->string('postal_code')->nullable();
                $table->string('citizenship')->nullable();
                $table->string('passport_number')->nullable();
                $table->string('national_id')->nullable();
                $table->json('emergency_contact')->nullable();
                $table->json('preferences')->nullable();
                $table->enum('status', ['active', 'inactive', 'blocked'])->default('active');
                $table->timestamps();
                
                $table->index('applicant_id');
                $table->index(['last_name', 'first_name']);
                $table->index('user_id');
            });
        }

        // 6. Update admission_applications table to reference applicants
        if (Schema::hasTable('admission_applications')) {
            Schema::table('admission_applications', function (Blueprint $table) {
                // Add applicant_id if it doesn't exist
                if (!Schema::hasColumn('admission_applications', 'applicant_id')) {
                    $table->foreignId('applicant_id')->nullable()->constrained()->onDelete('cascade');
                }
                
                // Ensure program_id exists and is properly constrained
                if (!Schema::hasColumn('admission_applications', 'program_id')) {
                    $table->foreignId('program_id')->constrained()->onDelete('cascade');
                }
            });
        }

        // 7. Create student_conversions table (track applicant to student conversion)
        if (!Schema::hasTable('student_conversions')) {
            Schema::create('student_conversions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('applicant_id')->constrained()->onDelete('cascade');
                $table->foreignId('student_id')->constrained()->onDelete('cascade');
                $table->foreignId('application_id')->constrained('admission_applications')->onDelete('cascade');
                $table->string('student_number'); // Official student ID
                $table->date('conversion_date');
                $table->string('converted_by')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                
                $table->index(['applicant_id', 'student_id']);
                $table->unique('application_id');
            });
        }

        // 8. Add check constraint for academic_programs delivery_mode if missing
        if (Schema::hasTable('academic_programs')) {
            DB::statement("ALTER TABLE academic_programs DROP CONSTRAINT IF EXISTS academic_programs_delivery_mode_check");
            DB::statement("ALTER TABLE academic_programs ADD CONSTRAINT academic_programs_delivery_mode_check 
                          CHECK (delivery_mode IN ('on-campus', 'online', 'hybrid', 'flexible'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('student_conversions');
        Schema::dropIfExists('applicants');
        Schema::dropIfExists('programs');
        Schema::dropIfExists('degrees');
        Schema::dropIfExists('program_types');
        
        // Restore original user_type constraint
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_user_type_check");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_user_type_check 
                      CHECK (user_type IN ('student', 'faculty', 'admin', 'staff'))");
    }
};