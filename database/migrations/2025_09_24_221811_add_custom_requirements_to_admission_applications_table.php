<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // First check if the column already exists
        if (!Schema::hasColumn('admission_applications', 'custom_requirements')) {
            Schema::table('admission_applications', function (Blueprint $table) {
                $table->json('custom_requirements')->nullable()->after('documents');
            });
        }
    }

    public function down()
    {
        Schema::table('admission_applications', function (Blueprint $table) {
            if (Schema::hasColumn('admission_applications', 'custom_requirements')) {
                $table->dropColumn('custom_requirements');
            }
        });
    }
};