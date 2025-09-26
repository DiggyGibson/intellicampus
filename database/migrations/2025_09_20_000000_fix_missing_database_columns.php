<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration fixes missing columns in various tables that are expected
     * by the controllers but were not created in initial migrations.
     */
    public function up(): void
    {
        // ============================================================
        // FIX ANNOUNCEMENTS TABLE
        // ============================================================
        Schema::table('announcements', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('announcements', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('send_email');
            }
            
            if (!Schema::hasColumn('announcements', 'target_audience')) {
                $table->string('target_audience', 50)->default('all')->after('is_active');
            }
            
            if (!Schema::hasColumn('announcements', 'target_id')) {
                $table->unsignedBigInteger('target_id')->nullable()->after('target_audience');
            }
            
            if (!Schema::hasColumn('announcements', 'visibility')) {
                $table->string('visibility', 50)->default('public')->after('target_id');
            }
            
            // Rename columns if they exist with wrong names
            if (Schema::hasColumn('announcements', 'publish_at') && !Schema::hasColumn('announcements', 'publish_date')) {
                $table->renameColumn('publish_at', 'publish_date');
            }
            
            if (Schema::hasColumn('announcements', 'expires_at') && !Schema::hasColumn('announcements', 'expiry_date')) {
                $table->renameColumn('expires_at', 'expiry_date');
            }
            
            // Add indexes for better performance
            $table->index(['is_active', 'publish_date', 'expiry_date'], 'idx_announcements_active_dates');
            $table->index(['target_audience', 'target_id'], 'idx_announcements_target');
        });
        
        // ============================================================
        // FIX STUDENTS TABLE
        // ============================================================
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index('user_id');
            }
            
            if (!Schema::hasColumn('students', 'enrollment_status')) {
                $table->string('enrollment_status', 50)->default('active')->after('student_id');
            }
            
            if (!Schema::hasColumn('students', 'academic_level')) {
                $table->string('academic_level', 50)->nullable()->after('enrollment_status');
            }
            
            if (!Schema::hasColumn('students', 'department')) {
                $table->string('department', 100)->nullable();
            }
        });
        
        // ============================================================
        // FIX COURSE_SECTIONS TABLE
        // ============================================================
        Schema::table('course_sections', function (Blueprint $table) {
            if (!Schema::hasColumn('course_sections', 'status')) {
                $table->string('status', 50)->default('active')->after('section_number');
            }
            
            if (!Schema::hasColumn('course_sections', 'primary_instructor_id')) {
                $table->unsignedBigInteger('primary_instructor_id')->nullable();
                $table->foreign('primary_instructor_id')->references('id')->on('users');
            }
        });
        
        // ============================================================
        // FIX ACADEMIC_TERMS TABLE
        // ============================================================
        Schema::table('academic_terms', function (Blueprint $table) {
            if (!Schema::hasColumn('academic_terms', 'is_current')) {
                $table->boolean('is_current')->default(false)->after('end_date');
            }
            
            if (!Schema::hasColumn('academic_terms', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_current');
            }
        });
        
        // Ensure only one term is current
        DB::statement('UPDATE academic_terms SET is_current = false');
        DB::statement('UPDATE academic_terms SET is_current = true WHERE id = (SELECT id FROM (SELECT id FROM academic_terms WHERE is_active = true ORDER BY start_date DESC LIMIT 1) as temp)');
        
        // ============================================================
        // FIX USERS TABLE
        // ============================================================
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'user_type')) {
                $table->string('user_type', 50)->default('student')->after('email');
            }
            
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
            
            if (!Schema::hasColumn('users', 'department_id')) {
                $table->unsignedBigInteger('department_id')->nullable();
            }
        });
        
        // ============================================================
        // FIX GRADES TABLE
        // ============================================================
        if (Schema::hasTable('grades')) {
            Schema::table('grades', function (Blueprint $table) {
                if (!Schema::hasColumn('grades', 'is_final')) {
                    $table->boolean('is_final')->default(false);
                }
                
                if (!Schema::hasColumn('grades', 'submission_date')) {
                    $table->timestamp('submission_date')->nullable();
                }
            });
        }
        
        // ============================================================
        // FIX ENROLLMENTS/REGISTRATIONS TABLE
        // ============================================================
        $enrollmentTable = Schema::hasTable('enrollments') ? 'enrollments' : 'registrations';
        
        if (Schema::hasTable($enrollmentTable)) {
            Schema::table($enrollmentTable, function (Blueprint $table) use ($enrollmentTable) {
                if (!Schema::hasColumn($enrollmentTable, 'status')) {
                    $table->string('status', 50)->default('enrolled');
                }
            });
        }
        
        // ============================================================
        // FIX TRANSCRIPT_REQUESTS TABLE
        // ============================================================
        if (Schema::hasTable('transcript_requests')) {
            Schema::table('transcript_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('transcript_requests', 'status')) {
                    $table->string('status', 50)->default('pending');
                }
                
                if (!Schema::hasColumn('transcript_requests', 'student_id')) {
                    $table->unsignedBigInteger('student_id');
                    $table->foreign('student_id')->references('id')->on('students');
                }
            });
        }
        
        // ============================================================
        // CREATE MISSING TABLES IF THEY DON'T EXIST
        // ============================================================
        
        // Create academic_programs table if missing
        if (!Schema::hasTable('academic_programs')) {
            Schema::create('academic_programs', function (Blueprint $table) {
                $table->id();
                $table->string('code', 20)->unique();
                $table->string('name');
                $table->string('degree_type', 50);
                $table->integer('credit_hours_required');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
        
        // Create financial_transactions table if missing
        if (!Schema::hasTable('financial_transactions')) {
            Schema::create('financial_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->string('type', 50);
                $table->decimal('amount', 10, 2);
                $table->string('status', 50)->default('pending');
                $table->string('reference_number')->nullable();
                $table->text('description')->nullable();
                $table->timestamp('transaction_date');
                $table->timestamps();
                
                $table->foreign('student_id')->references('id')->on('students');
                $table->index(['student_id', 'status']);
            });
        }
        
        // Create attendance_records table if missing
        if (!Schema::hasTable('attendance_records')) {
            Schema::create('attendance_records', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('section_id');
                $table->date('attendance_date');
                $table->enum('status', ['present', 'absent', 'late', 'excused']);
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('marked_by')->nullable();
                $table->timestamps();
                
                $table->foreign('student_id')->references('id')->on('students');
                $table->foreign('section_id')->references('id')->on('course_sections');
                $table->foreign('marked_by')->references('id')->on('users');
                $table->unique(['student_id', 'section_id', 'attendance_date']);
            });
        }
        
        // Create student_status_changes table if missing
        if (!Schema::hasTable('student_status_changes')) {
            Schema::create('student_status_changes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->string('old_status', 50);
                $table->string('new_status', 50);
                $table->string('reason')->nullable();
                $table->unsignedBigInteger('changed_by');
                $table->timestamp('changed_at');
                $table->timestamps();
                
                $table->foreign('student_id')->references('id')->on('students');
                $table->foreign('changed_by')->references('id')->on('users');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove indexes
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropIndex('idx_announcements_active_dates');
            $table->dropIndex('idx_announcements_target');
        });
        
        // Note: We don't reverse column additions/renames in down() 
        // as that could cause data loss in production
    }
};