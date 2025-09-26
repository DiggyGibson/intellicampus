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
        // Main override request table
        Schema::create('registration_override_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('term_id');
            $table->string('request_type', 50); // credit_overload, prerequisite, capacity, time_conflict, late_registration
            $table->string('status', 20)->default('pending'); // pending, approved, denied, expired, cancelled
            
            // Request details
            $table->integer('requested_credits')->nullable(); // For credit overload
            $table->integer('current_credits')->nullable();
            $table->unsignedBigInteger('section_id')->nullable(); // For specific course overrides
            $table->unsignedBigInteger('course_id')->nullable();
            
            // Justification
            $table->text('student_justification');
            $table->json('supporting_documents')->nullable(); // Array of document URLs/IDs
            
            // Approval details
            $table->unsignedBigInteger('approver_id')->nullable();
            $table->string('approver_role', 50)->nullable(); // advisor, department_head, registrar
            $table->timestamp('approval_date')->nullable();
            $table->text('approver_notes')->nullable();
            $table->text('conditions')->nullable(); // Any conditions attached to approval
            
            // Override code (if approved)
            $table->string('override_code', 20)->nullable()->unique(); // System-generated permission number
            $table->boolean('override_used')->default(false);
            $table->timestamp('override_expires_at')->nullable();
            
            // Priority handling
            $table->integer('priority_level')->default(5); // 1-10 scale, 10 being highest
            $table->boolean('is_graduating_senior')->default(false);
            
            // Timestamps
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('term_id')->references('id')->on('academic_terms')->onDelete('cascade');
            $table->foreign('section_id')->references('id')->on('course_sections')->onDelete('set null');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('set null');
            $table->foreign('approver_id')->references('id')->on('users')->onDelete('set null');
            
            // Indexes for performance
            $table->index(['student_id', 'term_id']);
            $table->index(['status', 'request_type']);
            $table->index('override_code');
            $table->index(['created_at', 'status']); // For pending request queries
        });

        // Credit overload permissions (simplified table for quick checks)
        Schema::create('credit_overload_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('term_id');
            $table->integer('max_credits'); // Approved maximum (e.g., 21)
            $table->unsignedBigInteger('approved_by');
            $table->timestamp('approved_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->text('reason')->nullable();
            $table->text('conditions')->nullable(); // "Maintain 3.0 GPA", "One-time only", etc.
            $table->date('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('term_id')->references('id')->on('academic_terms')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('restrict');
            
            // Ensure only one active permission per student per term
            $table->unique(['student_id', 'term_id', 'is_active']);
        });

        // Prerequisite waivers
        Schema::create('prerequisite_waivers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('waived_prerequisite_id')->nullable();
            $table->unsignedBigInteger('term_id');
            
            $table->string('reason', 100); // equivalent_course, professional_experience, placement_test, department_override
            $table->text('justification')->nullable();
            $table->json('supporting_evidence')->nullable();
            
            $table->unsignedBigInteger('approved_by');
            $table->timestamp('approved_at')->nullable();
            $table->date('expires_at')->nullable(); // Waiver might be term-specific
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('waived_prerequisite_id')->references('id')->on('courses')->onDelete('set null');
            $table->foreign('term_id')->references('id')->on('academic_terms')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('restrict');
            
            // Unique constraint to prevent duplicate waivers
            $table->unique(['student_id', 'course_id', 'waived_prerequisite_id', 'term_id'], 'unique_waiver');
        });

        // Special registration flags (for various scenarios)
        Schema::create('special_registration_flags', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('term_id');
            $table->string('flag_type', 50); // late_registration, concurrent_enrollment, audit_allowed, time_conflict_allowed
            $table->json('flag_value')->nullable();
            $table->unsignedBigInteger('authorized_by');
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('term_id')->references('id')->on('academic_terms')->onDelete('cascade');
            $table->foreign('authorized_by')->references('id')->on('users')->onDelete('restrict');
            
            // Indexes
            $table->index(['student_id', 'term_id', 'flag_type']);
            $table->index(['flag_type', 'is_active']);
        });

        // Update registration_cart table to track overrides
        if (Schema::hasTable('registration_cart')) {
            Schema::table('registration_cart', function (Blueprint $table) {
                if (!Schema::hasColumn('registration_cart', 'override_codes')) {
                    $table->json('override_codes')->nullable()->after('has_prerequisite_issues');
                }
                if (!Schema::hasColumn('registration_cart', 'has_overrides')) {
                    $table->boolean('has_overrides')->default(false)->after('override_codes');
                }
            });
        }

        // Update registrations table to track which registrations used overrides
        if (Schema::hasTable('registrations')) {
            Schema::table('registrations', function (Blueprint $table) {
                if (!Schema::hasColumn('registrations', 'registered_with_override')) {
                    $table->boolean('registered_with_override')->default(false)->after('registration_status');
                }
                if (!Schema::hasColumn('registrations', 'override_type')) {
                    $table->string('override_type', 50)->nullable()->after('registered_with_override');
                }
                if (!Schema::hasColumn('registrations', 'override_request_id')) {
                    $table->unsignedBigInteger('override_request_id')->nullable()->after('override_type');
                    $table->foreign('override_request_id')->references('id')->on('registration_override_requests')->onDelete('set null');
                }
            });
        }

        // Create override approval routing rules table
        Schema::create('override_approval_routes', function (Blueprint $table) {
            $table->id();
            $table->string('request_type', 50); // credit_overload, prerequisite, etc.
            $table->string('approver_role', 50); // advisor, department_head, registrar
            $table->integer('min_priority')->default(1);
            $table->integer('max_priority')->default(10);
            $table->json('auto_approve_conditions')->nullable(); // Conditions for auto-approval
            $table->integer('escalation_days')->nullable(); // Days before escalation
            $table->string('escalation_role', 50)->nullable(); // Role to escalate to
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['request_type', 'is_active']);
        });

        // Insert default routing rules
        DB::table('override_approval_routes')->insert([
            [
                'request_type' => 'credit_overload',
                'approver_role' => 'advisor',
                'min_priority' => 1,
                'max_priority' => 10,
                'auto_approve_conditions' => json_encode(['min_gpa' => 3.5, 'max_credits' => 21]),
                'escalation_days' => 3,
                'escalation_role' => 'registrar',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'request_type' => 'prerequisite',
                'approver_role' => 'department_head',
                'min_priority' => 1,
                'max_priority' => 10,
                'auto_approve_conditions' => null,
                'escalation_days' => 5,
                'escalation_role' => 'registrar',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'request_type' => 'capacity',
                'approver_role' => 'registrar',
                'min_priority' => 1,
                'max_priority' => 10,
                'auto_approve_conditions' => json_encode(['graduating_senior' => true]),
                'escalation_days' => 2,
                'escalation_role' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'request_type' => 'time_conflict',
                'approver_role' => 'registrar',
                'min_priority' => 1,
                'max_priority' => 10,
                'auto_approve_conditions' => null,
                'escalation_days' => 3,
                'escalation_role' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'request_type' => 'late_registration',
                'approver_role' => 'registrar',
                'min_priority' => 1,
                'max_priority' => 10,
                'auto_approve_conditions' => null,
                'escalation_days' => 1,
                'escalation_role' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove foreign key and columns from existing tables first
        if (Schema::hasTable('registrations')) {
            Schema::table('registrations', function (Blueprint $table) {
                if (Schema::hasColumn('registrations', 'override_request_id')) {
                    $table->dropForeign(['override_request_id']);
                    $table->dropColumn('override_request_id');
                }
                if (Schema::hasColumn('registrations', 'override_type')) {
                    $table->dropColumn('override_type');
                }
                if (Schema::hasColumn('registrations', 'registered_with_override')) {
                    $table->dropColumn('registered_with_override');
                }
            });
        }

        if (Schema::hasTable('registration_cart')) {
            Schema::table('registration_cart', function (Blueprint $table) {
                if (Schema::hasColumn('registration_cart', 'has_overrides')) {
                    $table->dropColumn('has_overrides');
                }
                if (Schema::hasColumn('registration_cart', 'override_codes')) {
                    $table->dropColumn('override_codes');
                }
            });
        }

        // Drop tables in correct order (considering foreign key constraints)
        Schema::dropIfExists('override_approval_routes');
        Schema::dropIfExists('special_registration_flags');
        Schema::dropIfExists('prerequisite_waivers');
        Schema::dropIfExists('credit_overload_permissions');
        Schema::dropIfExists('registration_override_requests');
    }
};