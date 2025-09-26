<?php
// database/migrations/2024_12_03_add_missing_columns_to_payments.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            // Add soft deletes
            if (!Schema::hasColumn('payments', 'deleted_at')) {
                $table->softDeletes();
            }
            
            // Add tracking columns
            if (!Schema::hasColumn('payments', 'applied_to_account')) {
                $table->boolean('applied_to_account')->default(false);
            }
            
            if (!Schema::hasColumn('payments', 'refunded_amount')) {
                $table->decimal('refunded_amount', 10, 2)->nullable();
            }
            
            if (!Schema::hasColumn('payments', 'refund_date')) {
                $table->timestamp('refund_date')->nullable();
            }
            
            if (!Schema::hasColumn('payments', 'refund_reason')) {
                $table->text('refund_reason')->nullable();
            }
            
            if (!Schema::hasColumn('payments', 'gateway_response')) {
                $table->json('gateway_response')->nullable();
            }
            
            if (!Schema::hasColumn('payments', 'ip_address')) {
                $table->string('ip_address', 45)->nullable();
            }
            
            if (!Schema::hasColumn('payments', 'user_agent')) {
                $table->text('user_agent')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $columnsToRemove = [
                'deleted_at',
                'applied_to_account',
                'refunded_amount',
                'refund_date',
                'refund_reason',
                'gateway_response',
                'ip_address',
                'user_agent'
            ];
            
            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('payments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};