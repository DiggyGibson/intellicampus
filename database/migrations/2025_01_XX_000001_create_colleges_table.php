<?php

// Migration 1: 2025_01_XX_000001_create_colleges_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCollegesTable extends Migration
{
    public function up()
    {
        Schema::create('colleges', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type')->default('academic'); // academic, professional, graduate
            
            // Leadership
            $table->foreignId('dean_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('associate_dean_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Contact Information
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('building')->nullable();
            $table->string('office')->nullable();
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->date('established_date')->nullable();
            
            // Metadata
            $table->json('settings')->nullable();
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('code');
            $table->index('dean_id');
            $table->index('is_active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('colleges');
    }
}