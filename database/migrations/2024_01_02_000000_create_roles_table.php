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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');                    // Display name (e.g., "Super Administrator")
            $table->string('slug')->unique();           // Unique identifier (e.g., "super-administrator")
            $table->text('description')->nullable();   // Description of the role
            $table->boolean('is_system')->default(false); // System role flag
            $table->boolean('is_active')->default(true);  // Active status
            $table->integer('priority')->default(999);    // Role priority/hierarchy
            $table->jsonb('metadata')->nullable();        // Additional metadata
            $table->timestamps();
            $table->softDeletes();                        // Soft delete support
            
            // Indexes for performance
            $table->index('slug');
            $table->index('is_system');
            $table->index('is_active');
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};