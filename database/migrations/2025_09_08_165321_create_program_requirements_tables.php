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
        Schema::create('program_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('academic_programs');
            $table->foreignId('requirement_id')->constrained('degree_requirements');
            $table->string('catalog_year', 10); // e.g., '2024-2025'
            
            // Override default requirement parameters for this program
            $table->json('program_parameters')->nullable();
            
            // Credits/courses required for this specific program
            $table->decimal('credits_required', 5, 1)->nullable();
            $table->integer('courses_required')->nullable();
            
            // When this requirement applies
            $table->enum('applies_to', [
                'all',          // All students in program
                'major_only',   // Only if this is primary major
                'minor_only',   // Only if this is minor
                'concentration' // Only for specific concentration
            ])->default('all');
            
            $table->string('concentration_code', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->unique(['program_id', 'requirement_id', 'catalog_year'], 'unique_program_requirement');
            $table->index(['program_id', 'catalog_year', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_requirements');
    }
};