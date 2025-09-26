<?php
// database/migrations/2025_01_20_create_payment_gateway_tables_fixed.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 1. Payment Gateways Configuration
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('provider');
            $table->text('api_key_encrypted')->nullable();
            $table->text('api_secret_encrypted')->nullable();
            $table->string('webhook_secret')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_test_mode')->default(true);
            $table->json('settings')->nullable();
            $table->json('supported_currencies')->nullable();
            $table->decimal('transaction_fee_percent', 5, 2)->default(2.9);
            $table->decimal('transaction_fee_fixed', 10, 2)->default(0.30);
            $table->timestamps();
            
            $table->index('provider');
            $table->index('is_active');
        });

        // 2. Payment Gateway Transactions Log
        Schema::create('payment_gateway_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_id');
            $table->unsignedBigInteger('gateway_id');
            $table->string('transaction_id')->unique();
            $table->string('gateway_transaction_id')->nullable();
            $table->enum('type', ['payment', 'refund', 'authorization', 'capture']);
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled']);
            $table->json('gateway_request')->nullable();
            $table->json('gateway_response')->nullable();
            $table->string('error_code')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            $table->foreign('payment_id')->references('id')->on('payments');
            $table->foreign('gateway_id')->references('id')->on('payment_gateways');
            $table->index(['status', 'created_at']);
            $table->index('transaction_id');
        });

        // 3. Refunds Management
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->string('refund_number')->unique();
            $table->unsignedBigInteger('payment_id');
            $table->unsignedBigInteger('student_id');
            $table->decimal('amount', 10, 2);
            $table->string('reason');
            $table->text('detailed_reason')->nullable();
            $table->enum('type', ['full', 'partial']);
            $table->enum('status', ['pending', 'approved', 'processing', 'completed', 'rejected', 'failed']);
            $table->enum('refund_method', ['original_payment', 'check', 'account_credit']);
            $table->string('gateway_refund_id')->nullable();
            $table->json('gateway_response')->nullable();
            $table->date('requested_date');
            $table->date('approved_date')->nullable();
            $table->date('processed_date')->nullable();
            $table->unsignedBigInteger('requested_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            
            $table->foreign('payment_id')->references('id')->on('payments');
            $table->foreign('student_id')->references('id')->on('students');
            $table->foreign('requested_by')->references('id')->on('users');
            $table->foreign('approved_by')->references('id')->on('users');
            $table->foreign('processed_by')->references('id')->on('users');
            $table->index(['status', 'requested_date']);
        });

        // 4. Third-party Sponsors
        Schema::create('third_party_sponsors', function (Blueprint $table) {
            $table->id();
            $table->string('sponsor_code')->unique();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->enum('type', ['company', 'government', 'foundation', 'individual', 'other']);
            $table->decimal('credit_limit', 12, 2)->nullable();
            $table->decimal('current_balance', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('billing_preferences')->nullable();
            $table->date('contract_start_date')->nullable();
            $table->date('contract_end_date')->nullable();
            $table->timestamps();
            
            $table->index('sponsor_code');
            $table->index('is_active');
        });

        // 5. Sponsor Authorizations
        Schema::create('sponsor_authorizations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sponsor_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('term_id');
            $table->string('authorization_number')->unique();
            $table->decimal('authorized_amount', 10, 2);
            $table->decimal('used_amount', 10, 2)->default(0);
            $table->json('covered_items')->nullable();
            $table->enum('status', ['pending', 'approved', 'active', 'expired', 'cancelled']);
            $table->date('valid_from');
            $table->date('valid_until');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->foreign('sponsor_id')->references('id')->on('third_party_sponsors');
            $table->foreign('student_id')->references('id')->on('students');
            $table->foreign('term_id')->references('id')->on('academic_terms');
            $table->foreign('approved_by')->references('id')->on('users');
            $table->index(['student_id', 'term_id']);
            $table->index('status');
        });

        // 6. UPDATE EXISTING TABLES - HANDLE EXISTING DATA PROPERLY
        
        // Update student_accounts table - Handle account_number properly
        Schema::table('student_accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('student_accounts', 'account_number')) {
                // First add as nullable
                $table->string('account_number')->nullable()->after('student_id');
            }
            
            // Add other missing columns as nullable first
            if (!Schema::hasColumn('student_accounts', 'credit_limit')) {
                $table->decimal('credit_limit', 10, 2)->default(0)->after('balance');
            }
            if (!Schema::hasColumn('student_accounts', 'total_charges')) {
                $table->decimal('total_charges', 12, 2)->default(0);
            }
            if (!Schema::hasColumn('student_accounts', 'total_payments')) {
                $table->decimal('total_payments', 12, 2)->default(0);
            }
            if (!Schema::hasColumn('student_accounts', 'total_aid')) {
                $table->decimal('total_aid', 12, 2)->default(0);
            }
            if (!Schema::hasColumn('student_accounts', 'last_payment_date')) {
                $table->date('last_payment_date')->nullable();
            }
            if (!Schema::hasColumn('student_accounts', 'hold_amount')) {
                $table->decimal('hold_amount', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('student_accounts', 'hold_placed_date')) {
                $table->date('hold_placed_date')->nullable();
            }
            if (!Schema::hasColumn('student_accounts', 'hold_removed_date')) {
                $table->date('hold_removed_date')->nullable();
            }
            if (!Schema::hasColumn('student_accounts', 'has_financial_hold')) {
                $table->boolean('has_financial_hold')->default(false);
            }
            if (!Schema::hasColumn('student_accounts', 'hold_reason')) {
                $table->text('hold_reason')->nullable();
            }
        });

        // Generate account numbers for existing records
        $this->generateAccountNumbers();

        // Now make account_number unique and not null
        Schema::table('student_accounts', function (Blueprint $table) {
            $table->unique('account_number');
        });

        // Update payments table
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'gateway_id')) {
                $table->unsignedBigInteger('gateway_id')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('payments', 'description')) {
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('payments', 'receipt_url')) {
                $table->string('receipt_url')->nullable();
            }
            if (!Schema::hasColumn('payments', 'is_recurring')) {
                $table->boolean('is_recurring')->default(false);
            }
        });

        // Add foreign key for gateway_id after table is created
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'gateway_id')) {
                $table->foreign('gateway_id')->references('id')->on('payment_gateways');
            }
        });

        // Update payment_plans table
        Schema::table('payment_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_plans', 'account_id')) {
                $table->unsignedBigInteger('account_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('payment_plans', 'down_payment_paid')) {
                $table->boolean('down_payment_paid')->default(false);
            }
            if (!Schema::hasColumn('payment_plans', 'next_payment_date')) {
                $table->date('next_payment_date')->nullable();
            }
            if (!Schema::hasColumn('payment_plans', 'last_payment_date')) {
                $table->date('last_payment_date')->nullable();
            }
            if (!Schema::hasColumn('payment_plans', 'total_paid')) {
                $table->decimal('total_paid', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('payment_plans', 'missed_payments')) {
                $table->integer('missed_payments')->default(0);
            }
        });

        // Add foreign key for account_id
        Schema::table('payment_plans', function (Blueprint $table) {
            if (Schema::hasColumn('payment_plans', 'account_id')) {
                $existingForeignKeys = DB::select("
                    SELECT constraint_name 
                    FROM information_schema.table_constraints 
                    WHERE table_name = 'payment_plans' 
                    AND constraint_type = 'FOREIGN KEY'
                    AND constraint_name LIKE '%account%'
                ");
                
                if (empty($existingForeignKeys)) {
                    $table->foreign('account_id')->references('id')->on('student_accounts');
                }
            }
        });

        // 7. Collections Management
        Schema::create('collection_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('account_id');
            $table->decimal('original_amount', 10, 2);
            $table->decimal('current_balance', 10, 2);
            $table->enum('status', ['active', 'payment_plan', 'paid', 'written_off']);
            $table->date('sent_to_collections_date');
            $table->string('collection_agency')->nullable();
            $table->string('agency_reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('student_id')->references('id')->on('students');
            $table->foreign('account_id')->references('id')->on('student_accounts');
            $table->index('status');
        });

        // 8. Write-offs
        Schema::create('write_offs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('account_id');
            $table->decimal('amount', 10, 2);
            $table->string('reason');
            $table->text('detailed_reason')->nullable();
            $table->date('write_off_date');
            $table->unsignedBigInteger('approved_by');
            $table->timestamps();
            
            $table->foreign('student_id')->references('id')->on('students');
            $table->foreign('account_id')->references('id')->on('student_accounts');
            $table->foreign('approved_by')->references('id')->on('users');
        });

        // 9. Financial Holds History
        Schema::create('financial_holds_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('account_id');
            $table->enum('action', ['placed', 'removed']);
            $table->string('reason');
            $table->decimal('balance_at_time', 10, 2);
            $table->unsignedBigInteger('performed_by');
            $table->timestamps();
            
            $table->foreign('student_id')->references('id')->on('students');
            $table->foreign('account_id')->references('id')->on('student_accounts');
            $table->foreign('performed_by')->references('id')->on('users');
            $table->index(['student_id', 'created_at']);
        });
    }

    /**
     * Generate account numbers for existing student accounts
     */
    private function generateAccountNumbers()
    {
        $accounts = DB::table('student_accounts')
            ->whereNull('account_number')
            ->orWhere('account_number', '')
            ->get();

        foreach ($accounts as $account) {
            $year = date('Y');
            $sequence = str_pad($account->student_id, 6, '0', STR_PAD_LEFT);
            $accountNumber = "ACC{$year}{$sequence}";
            
            // Ensure uniqueness
            $counter = 1;
            $finalAccountNumber = $accountNumber;
            while (DB::table('student_accounts')->where('account_number', $finalAccountNumber)->exists()) {
                $finalAccountNumber = $accountNumber . '-' . $counter;
                $counter++;
            }
            
            DB::table('student_accounts')
                ->where('id', $account->id)
                ->update(['account_number' => $finalAccountNumber]);
        }
    }

    public function down()
    {
        // Drop new tables
        Schema::dropIfExists('financial_holds_history');
        Schema::dropIfExists('write_offs');
        Schema::dropIfExists('collection_accounts');
        Schema::dropIfExists('sponsor_authorizations');
        Schema::dropIfExists('third_party_sponsors');
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('payment_gateway_transactions');
        Schema::dropIfExists('payment_gateways');
        
        // Remove added columns from payment_plans
        Schema::table('payment_plans', function (Blueprint $table) {
            if (Schema::hasColumn('payment_plans', 'account_id')) {
                $table->dropForeign(['account_id']);
                $table->dropColumn(['account_id', 'down_payment_paid', 'next_payment_date', 
                                   'last_payment_date', 'total_paid', 'missed_payments']);
            }
        });
        
        // Remove added columns from payments
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'gateway_id')) {
                $table->dropForeign(['gateway_id']);
                $table->dropColumn(['gateway_id', 'description', 'receipt_url', 'is_recurring']);
            }
        });
        
        // Remove added columns from student_accounts
        Schema::table('student_accounts', function (Blueprint $table) {
            $table->dropColumn([
                'account_number', 'credit_limit', 'total_charges', 
                'total_payments', 'total_aid', 'last_payment_date',
                'hold_amount', 'hold_placed_date', 'hold_removed_date',
                'has_financial_hold', 'hold_reason'
            ]);
        });
    }
};