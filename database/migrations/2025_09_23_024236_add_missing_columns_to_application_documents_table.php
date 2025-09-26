<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('application_documents', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('application_documents', 'mime_type')) {
                $table->string('mime_type', 100)->nullable()->after('file_size');
            }
            if (!Schema::hasColumn('application_documents', 'file_size')) {
                $table->integer('file_size')->nullable()->after('file_path');
            }
            if (!Schema::hasColumn('application_documents', 'status')) {
                $table->string('status', 50)->default('uploaded')->after('mime_type');
            }
            if (!Schema::hasColumn('application_documents', 'uploaded_at')) {
                $table->timestamp('uploaded_at')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('application_documents', function (Blueprint $table) {
            $table->dropColumn(['mime_type', 'file_size', 'status', 'uploaded_at']);
        });
    }
};