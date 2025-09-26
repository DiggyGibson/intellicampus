<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration 10: 2025_01_XX_000010_create_scope_audit_logs_table.php
return new class extends Migration
{
    public function up()
    {
        Schema::create('scope_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            
            $table->string('action'); // view, create, update, delete, access_denied
            $table->string('entity_type'); // course, student, grade, etc.
            $table->unsignedBigInteger('entity_id');
            
            $table->string('scope_type'); // department, college, etc.
            $table->unsignedBigInteger('scope_id');
            
            $table->boolean('was_allowed');
            $table->string('denial_reason')->nullable();
            
            $table->json('context')->nullable(); // Additional context
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            $table->timestamp('occurred_at');
            $table->index(['user_id', 'occurred_at']);
            $table->index(['entity_type', 'entity_id']);
            $table->index(['scope_type', 'scope_id']);
            $table->index('action');
            $table->index('was_allowed');
        });
    }

    public function down()
    {
        Schema::dropIfExists('scope_audit_logs');
    }
};
