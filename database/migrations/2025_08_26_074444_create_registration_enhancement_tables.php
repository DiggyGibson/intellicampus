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
        // Create registration_periods table
        if (!Schema::hasTable('registration_periods')) {
            Schema::create('registration_periods', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('term_id');
                $table->string('period_type', 50); // early, regular, late, priority
                $table->date('start_date');
                $table->date('end_date');
                $table->json('student_levels')->nullable(); // ['senior', 'junior', etc.]
                $table->json('priority_groups')->nullable(); // ['athlete', 'honors', etc.]
                $table->boolean('is_active')->default(true);
                $table->text('description')->nullable();
                $table->timestamps();
                
                $table->index(['term_id', 'start_date', 'end_date']);
                $table->index('period_type');
                
                // Add foreign key if academic_terms table exists
                if (Schema::hasTable('academic_terms')) {
                    $table->foreign('term_id')->references('id')->on('academic_terms')->onDelete('cascade');
                }
            });
        }

        // Create registration_overrides table
        if (!Schema::hasTable('registration_overrides')) {
            Schema::create('registration_overrides', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('term_id');
                $table->string('override_type', 50); // credit_overload, prerequisite, time_conflict
                $table->unsignedBigInteger('course_id')->nullable();
                $table->boolean('is_approved')->default(false);
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->text('reason')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                
                $table->index(['student_id', 'term_id']);
                $table->index(['override_type', 'is_approved']);
                
                // Add foreign keys if tables exist
                if (Schema::hasTable('students')) {
                    $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
                }
                if (Schema::hasTable('academic_terms')) {
                    $table->foreign('term_id')->references('id')->on('academic_terms')->onDelete('cascade');
                }
                if (Schema::hasTable('courses')) {
                    $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
                }
                if (Schema::hasTable('users')) {
                    $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
                }
            });
        }

        // Create prerequisite_overrides table
        if (!Schema::hasTable('prerequisite_overrides')) {
            Schema::create('prerequisite_overrides', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('course_id');
                $table->unsignedBigInteger('term_id');
                $table->boolean('is_approved')->default(false);
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->text('reason');
                $table->timestamps();
                
                $table->index(['student_id', 'course_id', 'term_id']);
                
                // Add foreign keys if tables exist
                if (Schema::hasTable('students')) {
                    $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
                }
                if (Schema::hasTable('courses')) {
                    $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
                }
                if (Schema::hasTable('academic_terms')) {
                    $table->foreign('term_id')->references('id')->on('academic_terms')->onDelete('cascade');
                }
                if (Schema::hasTable('users')) {
                    $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
                }
            });
        }

        // Add columns to students table if they don't exist
        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) {
                if (!Schema::hasColumn('students', 'is_athlete')) {
                    $table->boolean('is_athlete')->default(false)->after('enrollment_status');
                }
                if (!Schema::hasColumn('students', 'is_honors')) {
                    $table->boolean('is_honors')->default(false)->after('is_athlete');
                }
                if (!Schema::hasColumn('students', 'has_disability_accommodation')) {
                    $table->boolean('has_disability_accommodation')->default(false)->after('is_honors');
                }
                if (!Schema::hasColumn('students', 'expected_graduation_date')) {
                    $table->date('expected_graduation_date')->nullable()->after('has_disability_accommodation');
                }
                if (!Schema::hasColumn('students', 'cumulative_gpa')) {
                    $table->decimal('cumulative_gpa', 3, 2)->nullable()->after('expected_graduation_date');
                }
                if (!Schema::hasColumn('students', 'academic_standing')) {
                    $table->string('academic_standing', 50)->nullable()->after('cumulative_gpa'); // good, probation, suspension
                }
            });
        }

        // Add columns to academic_programs table if it exists
        if (Schema::hasTable('academic_programs')) {
            Schema::table('academic_programs', function (Blueprint $table) {
                if (!Schema::hasColumn('academic_programs', 'min_credits_per_term')) {
                    $table->integer('min_credits_per_term')->nullable();
                }
                if (!Schema::hasColumn('academic_programs', 'max_credits_per_term')) {
                    $table->integer('max_credits_per_term')->nullable();
                }
            });
        }

        // Add columns to course_prerequisites table if it exists
        if (Schema::hasTable('course_prerequisites')) {
            Schema::table('course_prerequisites', function (Blueprint $table) {
                if (!Schema::hasColumn('course_prerequisites', 'minimum_grade')) {
                    $table->string('minimum_grade', 3)->nullable();
                }
                if (!Schema::hasColumn('course_prerequisites', 'additional_requirements')) {
                    $table->json('additional_requirements')->nullable();
                }
            });
        }

        // Add columns to academic_terms table if it exists
        if (Schema::hasTable('academic_terms')) {
            Schema::table('academic_terms', function (Blueprint $table) {
                if (!Schema::hasColumn('academic_terms', 'drop_deadline')) {
                    $table->date('drop_deadline')->nullable();
                }
                if (!Schema::hasColumn('academic_terms', 'withdrawal_deadline')) {
                    $table->date('withdrawal_deadline')->nullable();
                }
            });
        }

        // Add columns to registration_holds table if it exists
        if (Schema::hasTable('registration_holds')) {
            Schema::table('registration_holds', function (Blueprint $table) {
                if (!Schema::hasColumn('registration_holds', 'placed_by_department')) {
                    $table->string('placed_by_department', 100)->nullable();
                }
                if (!Schema::hasColumn('registration_holds', 'cleared_at')) {
                    $table->timestamp('cleared_at')->nullable();
                }
                if (!Schema::hasColumn('registration_holds', 'resolution_instructions')) {
                    $table->text('resolution_instructions')->nullable();
                }
            });
        }

        // Add columns to registration_logs table if it exists
        if (Schema::hasTable('registration_logs')) {
            Schema::table('registration_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('registration_logs', 'ip_address')) {
                    $table->string('ip_address', 45)->nullable();
                }
                if (!Schema::hasColumn('registration_logs', 'user_agent')) {
                    $table->text('user_agent')->nullable();
                }
            });
        }

        // Add columns to enrollments table if it exists
        if (Schema::hasTable('enrollments')) {
            Schema::table('enrollments', function (Blueprint $table) {
                if (!Schema::hasColumn('enrollments', 'dropped_at')) {
                    $table->timestamp('dropped_at')->nullable();
                }
                if (!Schema::hasColumn('enrollments', 'final_grade')) {
                    $table->string('final_grade', 3)->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop new tables
        Schema::dropIfExists('prerequisite_overrides');
        Schema::dropIfExists('registration_overrides');
        Schema::dropIfExists('registration_periods');
        
        // Remove added columns from students table
        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropColumn([
                    'is_athlete', 
                    'is_honors', 
                    'has_disability_accommodation',
                    'expected_graduation_date', 
                    'cumulative_gpa', 
                    'academic_standing'
                ]);
            });
        }
        
        // Remove added columns from other tables
        if (Schema::hasTable('academic_programs')) {
            Schema::table('academic_programs', function (Blueprint $table) {
                $table->dropColumn(['min_credits_per_term', 'max_credits_per_term']);
            });
        }
        
        if (Schema::hasTable('course_prerequisites')) {
            Schema::table('course_prerequisites', function (Blueprint $table) {
                $table->dropColumn(['minimum_grade', 'additional_requirements']);
            });
        }
        
        if (Schema::hasTable('academic_terms')) {
            Schema::table('academic_terms', function (Blueprint $table) {
                $table->dropColumn(['drop_deadline', 'withdrawal_deadline']);
            });
        }
        
        if (Schema::hasTable('registration_holds')) {
            Schema::table('registration_holds', function (Blueprint $table) {
                $table->dropColumn(['placed_by_department', 'cleared_at', 'resolution_instructions']);
            });
        }
        
        if (Schema::hasTable('registration_logs')) {
            Schema::table('registration_logs', function (Blueprint $table) {
                $table->dropColumn(['ip_address', 'user_agent']);
            });
        }
        
        if (Schema::hasTable('enrollments')) {
            Schema::table('enrollments', function (Blueprint $table) {
                $table->dropColumn(['dropped_at', 'final_grade']);
            });
        }
    }
};