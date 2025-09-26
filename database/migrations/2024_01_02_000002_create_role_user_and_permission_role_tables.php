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
        // Create role_user pivot table
        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('assigned_at')->nullable();
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            
            // Composite unique key
            $table->unique(['role_id', 'user_id']);
            
            // Foreign key for assigned_by
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index('expires_at');
            $table->index('is_primary');
        });

        // Create permission_role pivot table
        Schema::create('permission_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->timestamp('granted_at')->nullable();
            $table->unsignedBigInteger('granted_by')->nullable();
            $table->timestamps();
            
            // Composite unique key
            $table->unique(['permission_id', 'role_id']);
            
            // Foreign key for granted_by
            $table->foreign('granted_by')->references('id')->on('users')->onDelete('set null');
        });

        // Create permission_user pivot table (for direct permission assignment)
        Schema::create('permission_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('granted_at')->nullable();
            $table->unsignedBigInteger('granted_by')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            // Composite unique key
            $table->unique(['permission_id', 'user_id']);
            
            // Foreign key for granted_by
            $table->foreign('granted_by')->references('id')->on('users')->onDelete('set null');
            
            // Index
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_user');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('role_user');
    }
};