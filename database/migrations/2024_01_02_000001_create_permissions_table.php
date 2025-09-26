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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');                    // Display name (e.g., "View Students")
            $table->string('slug')->unique();           // Unique identifier (e.g., "students.view")
            $table->string('module')->nullable();      // Module name (e.g., "students")
            $table->text('description')->nullable();   // Description of the permission
            $table->boolean('is_system')->default(false); // System permission flag
            $table->jsonb('metadata')->nullable();     // Additional metadata
            $table->timestamps();
            $table->softDeletes();                     // Soft delete support
            
            // Indexes for performance
            $table->index('module');
            $table->index('is_system');
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};