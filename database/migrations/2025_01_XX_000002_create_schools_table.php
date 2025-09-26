<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration 2: 2025_01_XX_000002_create_schools_table.php
class CreateSchoolsTable extends Migration
{
    public function up()
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            
            // Hierarchy
            $table->foreignId('college_id')->nullable()->constrained('colleges')->cascadeOnDelete();
            
            // Leadership
            $table->foreignId('director_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Contact
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('location')->nullable();
            
            // Status
            $table->boolean('is_active')->default(true);
            
            // Metadata
            $table->json('settings')->nullable();
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('college_id');
            $table->index('director_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('schools');
    }
}