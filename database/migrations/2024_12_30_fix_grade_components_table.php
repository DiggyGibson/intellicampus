<?php
// Save as: database/migrations/2024_12_30_fix_grade_components_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('grade_components', function (Blueprint $table) {
            if (!Schema::hasColumn('grade_components', 'is_extra_credit')) {
                $table->boolean('is_extra_credit')->default(false)->after('max_points');
            }
            if (!Schema::hasColumn('grade_components', 'category')) {
                $table->string('category')->nullable()->after('type');
            }
            if (!Schema::hasColumn('grade_components', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
        });
    }

    public function down()
    {
        Schema::table('grade_components', function (Blueprint $table) {
            $table->dropColumn(['is_extra_credit', 'category', 'description']);
        });
    }
};