<?php
// database/migrations/2024_12_03_add_applied_flag_to_payments.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->boolean('applied_to_account')->default(false)->after('processed_at');
            $table->index('applied_to_account');
        });
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('applied_to_account');
        });
    }
};