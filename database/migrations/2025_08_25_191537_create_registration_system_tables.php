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
        // Shopping Cart table - temporary storage for course selection
        if (!Schema::hasTable('registration_carts')) {
            Schema::create('registration_carts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('section_id');
                $table->unsignedBigInteger('term_id');
                $table->enum('status', ['pending', 'registered', 'dropped'])->default('pending');
                $table->text('notes')->nullable();
                $table->timestamps();
                
                $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
                $table->foreign('section_id')->references('id')->on('course_sections')->onDelete('cascade');
                $table->foreign('term_id')->references('id')->on('academic_terms')->onDelete('cascade');
                
                // Prevent duplicate entries
                $table->unique(['student_id', 'section_id']);
                
                $table->index(['student_id', 'status']);
            });
        }

        // Registration Periods - control when students can register
        if (!Schema::hasTable('registration_periods')) {
            Schema::create('registration_periods', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('term_id');
                $table->string('name'); // e.g., "Early Registration", "Open Registration"
                $table->text('description')->nullable();
                $table->dateTime('start_date');
                $table->dateTime('end_date');
                $table->enum('student_level', ['all', 'freshman', 'sophomore', 'junior', 'senior', 'graduate'])->default('all');
                $table->integer('min_credits')->nullable();
                $table->integer('max_credits')->default(18);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->foreign('term_id')->references('id')->on('academic_terms')->onDelete('cascade');
                $table->index(['term_id', 'is_active']);
            });
        }

        // Waitlist table
        if (!Schema::hasTable('waitlists')) {
            Schema::create('waitlists', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('section_id');
                $table->unsignedBigInteger('student_id');
                $table->integer('position');
                $table->dateTime('added_date');
                $table->dateTime('expiry_date')->nullable();
                $table->enum('status', ['waiting', 'offered', 'enrolled', 'expired', 'cancelled'])->default('waiting');
                $table->dateTime('offer_date')->nullable();
                $table->dateTime('response_date')->nullable();
                $table->timestamps();
                
                $table->foreign('section_id')->references('id')->on('course_sections')->onDelete('cascade');
                $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
                
                $table->unique(['section_id', 'student_id']);
                $table->index(['section_id', 'status', 'position']);
            });
        }

        // Registration Holds - prevent registration
        if (!Schema::hasTable('registration_holds')) {
            Schema::create('registration_holds', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->string('hold_type'); // e.g., "financial", "academic", "disciplinary", "immunization"
                $table->string('reason');
                $table->text('description')->nullable();
                $table->date('placed_date');
                $table->date('resolved_date')->nullable();
                $table->unsignedBigInteger('placed_by');
                $table->unsignedBigInteger('resolved_by')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
                $table->foreign('placed_by')->references('id')->on('users');
                $table->foreign('resolved_by')->references('id')->on('users');
                
                $table->index(['student_id', 'is_active']);
            });
        }

        // Course Prerequisites - enforce requirements
        if (!Schema::hasTable('course_prerequisites')) {
            Schema::create('course_prerequisites', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('course_id');
                $table->unsignedBigInteger('prerequisite_course_id');
                $table->decimal('minimum_grade', 3, 2)->nullable(); // e.g., 2.0 for C
                $table->enum('requirement_type', ['required', 'corequisite', 'recommended'])->default('required');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
                $table->foreign('prerequisite_course_id')->references('id')->on('courses')->onDelete('cascade');
                
                $table->unique(['course_id', 'prerequisite_course_id']);
            });
        }

        // Add additional columns to enrollments if they don't exist
        if (Schema::hasTable('enrollments')) {
            Schema::table('enrollments', function (Blueprint $table) {
                if (!Schema::hasColumn('enrollments', 'registration_date')) {
                    $table->dateTime('registration_date')->nullable()->after('enrollment_status');
                }
                if (!Schema::hasColumn('enrollments', 'drop_date')) {
                    $table->dateTime('drop_date')->nullable()->after('registration_date');
                }
                if (!Schema::hasColumn('enrollments', 'grade_option')) {
                    $table->enum('grade_option', ['graded', 'pass_fail', 'audit'])->default('graded')->after('enrollment_status');
                }
                if (!Schema::hasColumn('enrollments', 'credits_attempted')) {
                    $table->decimal('credits_attempted', 3, 1)->nullable()->after('grade_option');
                }
                if (!Schema::hasColumn('enrollments', 'credits_earned')) {
                    $table->decimal('credits_earned', 3, 1)->nullable()->after('credits_attempted');
                }
            });
        }

        // Registration Activity Log
        if (!Schema::hasTable('registration_logs')) {
            Schema::create('registration_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('section_id');
                $table->string('action'); // e.g., "added_to_cart", "enrolled", "dropped", "waitlisted"
                $table->text('details')->nullable();
                $table->string('ip_address')->nullable();
                $table->string('user_agent')->nullable();
                $table->timestamps();
                
                $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
                $table->foreign('section_id')->references('id')->on('course_sections')->onDelete('cascade');
                
                $table->index(['student_id', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop tables in reverse order to avoid foreign key constraints
        Schema::dropIfExists('registration_logs');
        
        // Remove columns from enrollments if they exist
        if (Schema::hasTable('enrollments')) {
            Schema::table('enrollments', function (Blueprint $table) {
                if (Schema::hasColumn('enrollments', 'registration_date')) {
                    $table->dropColumn('registration_date');
                }
                if (Schema::hasColumn('enrollments', 'drop_date')) {
                    $table->dropColumn('drop_date');
                }
                if (Schema::hasColumn('enrollments', 'grade_option')) {
                    $table->dropColumn('grade_option');
                }
                if (Schema::hasColumn('enrollments', 'credits_attempted')) {
                    $table->dropColumn('credits_attempted');
                }
                if (Schema::hasColumn('enrollments', 'credits_earned')) {
                    $table->dropColumn('credits_earned');
                }
            });
        }
        
        Schema::dropIfExists('course_prerequisites');
        Schema::dropIfExists('registration_holds');
        Schema::dropIfExists('waitlists');
        Schema::dropIfExists('registration_periods');
        Schema::dropIfExists('registration_carts');
    }
};