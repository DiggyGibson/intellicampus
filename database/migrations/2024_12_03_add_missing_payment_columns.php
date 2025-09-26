<?php
// database/migrations/2024_12_03_add_missing_payment_columns.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            // Add the missing columns that the Payment model expects
            $table->string('ip_address', 45)->nullable()->after('processed_at');
            $table->text('user_agent')->nullable()->after('ip_address');
            $table->decimal('refunded_amount', 10, 2)->nullable()->after('amount');
            $table->timestamp('refund_date')->nullable()->after('refunded_amount');
            $table->text('refund_reason')->nullable()->after('refund_date');
            $table->json('gateway_response')->nullable()->after('payment_details');
        });
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['ip_address', 'user_agent', 'refunded_amount', 
                               'refund_date', 'refund_reason', 'gateway_response']);
        });
    }
};