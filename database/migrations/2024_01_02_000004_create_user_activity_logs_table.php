<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('activity_type');           // login, logout, create, update, delete, etc.
            $table->text('description');               // Human-readable description
            $table->string('model_type')->nullable();  // Model class that was affected
            $table->unsignedBigInteger('model_id')->nullable(); // ID of affected model
            $table->jsonb('changes')->nullable();      // What changed (for updates)
            $table->string('ip_address')->nullable();  // User's IP address
            $table->text('user_agent')->nullable();    // Browser/device info
            $table->string('session_id')->nullable();  // Session ID
            $table->jsonb('metadata')->nullable();     // Additional metadata
            $table->timestamp('performed_at')->nullable(); // When activity was performed
            $table->timestamps();
            
            // Indexes for performance
            $table->index('user_id');
            $table->index('activity_type');
            $table->index('performed_at');
            $table->index(['model_type', 'model_id']);
            $table->index('session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activity_logs');
    }
};