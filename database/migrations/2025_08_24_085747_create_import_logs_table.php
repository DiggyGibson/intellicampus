<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50); // students, faculty, courses, etc.
            $table->string('filename');
            $table->integer('total_rows');
            $table->integer('success_count');
            $table->integer('error_count');
            $table->json('errors')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
            
            $table->index('type');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_logs');
    }
};