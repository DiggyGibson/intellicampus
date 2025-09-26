<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the table if it exists (for clean slate)
        Schema::dropIfExists('notifications');
        
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable'); // This creates notifiable_type and notifiable_id columns
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            // The morphs() method already creates this index, so we don't need to add it manually
            // $table->index(['notifiable_type', 'notifiable_id']); // REMOVE THIS LINE
            $table->index('read_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};