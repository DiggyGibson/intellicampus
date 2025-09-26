<?php
// database/migrations/2024_12_03_create_financial_management_system.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. FEE STRUCTURE
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['tuition', 'lab', 'library', 'registration', 'technology', 'activity', 'health', 'housing', 'meal', 'other']);
            $table->enum('frequency', ['once', 'per_term', 'per_year', 'per_credit', 'monthly']);
            $table->decimal('amount', 10, 2);
            $table->string('academic_level')->nullable(); // undergraduate, graduate
            $table->string('program_id')->nullable(); // specific program fees
            $table->boolean('is_mandatory')->default(true);
            $table->boolean('is_active')->default(true);
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            $table->timestamps();
            
            $table->index(['type', 'is_active']);
            $table->index('academic_level');
        });

        // 2. STUDENT ACCOUNTS
        Schema::create('student_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id')->unique();
            $table->decimal('balance', 10, 2)->default(0);
            $table->decimal('total_charges', 10, 2)->default(0);
            $table->decimal('total_payments', 10, 2)->default(0);
            $table->decimal('total_aid', 10, 2)->default(0);
            $table->decimal('credit_limit', 10, 2)->default(0);
            $table->enum('status', ['active', 'hold', 'suspended', 'closed'])->default('active');
            $table->boolean('has_payment_plan')->default(false);
            $table->date('last_payment_date')->nullable();
            $table->date('next_due_date')->nullable();
            $table->timestamps();
            
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->index('status');
        });

        // 3. BILLING ITEMS (Charges to student accounts)
        Schema::create('billing_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('term_id');
            $table->unsignedBigInteger('fee_structure_id')->nullable();
            $table->string('description');
            $table->enum('type', ['charge', 'credit', 'adjustment']);
            $table->decimal('amount', 10, 2);
            $table->decimal('balance', 10, 2); // remaining balance on this item
            $table->date('due_date');
            $table->enum('status', ['pending', 'billed', 'paid', 'partial', 'waived', 'cancelled'])->default('pending');
            $table->string('reference_type')->nullable(); // enrollment, course, service
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('term_id')->references('id')->on('academic_terms');
            $table->foreign('fee_structure_id')->references('id')->on('fee_structures');
            $table->foreign('created_by')->references('id')->on('users');
            
            $table->index(['student_id', 'term_id']);
            $table->index(['status', 'due_date']);
        });

        // 4. INVOICES
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('term_id');
            $table->date('invoice_date');
            $table->date('due_date');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('balance', 10, 2);
            $table->enum('status', ['draft', 'sent', 'viewed', 'partial', 'paid', 'overdue', 'cancelled'])->default('draft');
            $table->json('line_items')->nullable(); // Detailed breakdown
            $table->text('notes')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('term_id')->references('id')->on('academic_terms');
            
            $table->index(['student_id', 'status']);
            $table->index(['term_id', 'status']);
            $table->index('due_date');
        });

        // 5. PAYMENTS
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->unsignedBigInteger('student_id');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['cash', 'check', 'card', 'bank_transfer', 'mobile_money', 'financial_aid', 'scholarship', 'other']);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'refunded', 'cancelled'])->default('pending');
            $table->string('reference_number')->nullable(); // External reference
            $table->string('transaction_id')->nullable(); // Payment gateway ID
            $table->date('payment_date');
            $table->json('payment_details')->nullable(); // Gateway response, check number, etc.
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('processed_by')->references('id')->on('users');
            
            $table->index(['student_id', 'status']);
            $table->index('payment_date');
            $table->index('payment_method');
        });

        // 6. PAYMENT ALLOCATIONS (Link payments to specific charges)
        Schema::create('payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_id');
            $table->unsignedBigInteger('billing_item_id');
            $table->decimal('amount', 10, 2);
            $table->timestamps();
            
            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('cascade');
            $table->foreign('billing_item_id')->references('id')->on('billing_items');
            
            $table->unique(['payment_id', 'billing_item_id']);
        });

        // 7. FINANCIAL AIDS
        Schema::create('financial_aid', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('term_id');
            $table->string('aid_name');
            $table->enum('type', ['grant', 'scholarship', 'loan', 'work_study', 'waiver', 'discount']);
            $table->decimal('amount', 10, 2);
            $table->decimal('disbursed_amount', 10, 2)->default(0);
            $table->enum('status', ['pending', 'approved', 'disbursed', 'cancelled'])->default('pending');
            $table->date('award_date');
            $table->date('disbursement_date')->nullable();
            $table->text('conditions')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamps();
            
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('term_id')->references('id')->on('academic_terms');
            $table->foreign('approved_by')->references('id')->on('users');
            
            $table->index(['student_id', 'term_id']);
            $table->index('status');
        });

        // 8. PAYMENT PLANS
        Schema::create('payment_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('term_id');
            $table->string('plan_name');
            $table->decimal('total_amount', 10, 2);
            $table->integer('number_of_installments');
            $table->decimal('installment_amount', 10, 2);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['active', 'completed', 'defaulted', 'cancelled'])->default('active');
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->integer('paid_installments')->default(0);
            $table->date('next_due_date')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('term_id')->references('id')->on('academic_terms');
            $table->foreign('approved_by')->references('id')->on('users');
            
            $table->index(['student_id', 'status']);
        });

        // 9. PAYMENT PLAN SCHEDULES
        Schema::create('payment_plan_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_plan_id');
            $table->integer('installment_number');
            $table->decimal('amount', 10, 2);
            $table->date('due_date');
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->date('paid_date')->nullable();
            $table->enum('status', ['pending', 'paid', 'partial', 'overdue', 'waived'])->default('pending');
            $table->timestamps();
            
            $table->foreign('payment_plan_id')->references('id')->on('payment_plans')->onDelete('cascade');
            
            $table->index(['payment_plan_id', 'status']);
            $table->index('due_date');
        });

        // 10. FINANCIAL TRANSACTIONS LOG
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->enum('type', ['charge', 'payment', 'adjustment', 'refund', 'aid', 'transfer']);
            $table->decimal('amount', 10, 2);
            $table->decimal('balance_before', 10, 2);
            $table->decimal('balance_after', 10, 2);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('description');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users');
            
            $table->index(['student_id', 'type']);
            $table->index('created_at');
        });

        // Add financial hold column to registration_holds if it exists
        if (Schema::hasTable('registration_holds')) {
            Schema::table('registration_holds', function (Blueprint $table) {
                if (!Schema::hasColumn('registration_holds', 'amount_owed')) {
                    $table->decimal('amount_owed', 10, 2)->nullable()->after('reason');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('registration_holds')) {
            Schema::table('registration_holds', function (Blueprint $table) {
                $table->dropColumn('amount_owed');
            });
        }
        
        Schema::dropIfExists('financial_transactions');
        Schema::dropIfExists('payment_plan_schedules');
        Schema::dropIfExists('payment_plans');
        Schema::dropIfExists('financial_aid');
        Schema::dropIfExists('payment_allocations');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('billing_items');
        Schema::dropIfExists('student_accounts');
        Schema::dropIfExists('fee_structures');
    }
};