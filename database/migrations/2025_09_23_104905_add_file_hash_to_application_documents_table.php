<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('application_documents', 'file_hash')) {
            Schema::table('application_documents', function (Blueprint $table) {
                $table->string('file_hash', 64)->nullable()->after('file_size');
                $table->index(['application_id', 'file_hash']);
            });
        }
    }

    public function down()
    {
        Schema::table('application_documents', function (Blueprint $table) {
            $table->dropColumn('file_hash');
        });
    }
};