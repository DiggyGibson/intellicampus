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
        Schema::create('requirement_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique(); // e.g., 'GEN_ED', 'MAJOR_CORE'
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', [
                'university',         // University-wide requirements
                'general_education',  // General education
                'major',             // Major requirements
                'minor',             // Minor requirements
                'concentration',     // Concentration/specialization
                'elective',          // Free electives
                'other'              // Other requirements
            ]);
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->index('type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requirement_categories');
    }
};