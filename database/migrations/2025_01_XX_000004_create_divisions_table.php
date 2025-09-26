<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDivisionsTable extends Migration
{
    public function up()
    {
        Schema::create('divisions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            
            // Hierarchy
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            
            // Leadership
            $table->foreignId('coordinator_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Settings
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('department_id');
            $table->index('coordinator_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('divisions');
    }
}