
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration 9: 2025_01_XX_000009_create_organizational_permissions_table.php
return new class extends Migration
{
    public function up()
    {
        Schema::create('organizational_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            
            // Scope type and ID
            $table->enum('scope_type', ['college', 'school', 'department', 'division', 'program']);
            $table->unsignedBigInteger('scope_id');
            
            // Permission details
            $table->string('permission_key'); // e.g., 'view_grades', 'edit_courses', 'manage_faculty'
            $table->enum('access_level', ['view', 'create', 'edit', 'delete', 'manage']);
            
            // Grant details
            $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('granted_at');
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Composite index for scope
            $table->index(['scope_type', 'scope_id']);
            $table->index(['user_id', 'is_active']);
            $table->index('permission_key');
            $table->unique(['user_id', 'scope_type', 'scope_id', 'permission_key']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('organizational_permissions');
    }
};
