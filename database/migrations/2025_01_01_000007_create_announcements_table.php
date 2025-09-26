<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnnouncementsTable extends Migration
{
    public function up()
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->nullable()->constrained('course_sections')->onDelete('cascade');
            $table->foreignId('posted_by')->constrained('users');
            $table->string('title', 200);
            $table->text('content');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->datetime('publish_at')->nullable();
            $table->datetime('expires_at')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->boolean('send_email')->default(false);
            $table->timestamps();
            
            $table->index(['section_id', 'is_pinned']);
            $table->index('posted_by');
        });
    }

    public function down()
    {
        Schema::dropIfExists('announcements');
    }
}