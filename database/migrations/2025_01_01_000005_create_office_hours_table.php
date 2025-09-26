<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOfficeHoursTable extends Migration
{
    public function up()
    {
        Schema::create('office_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_id')->constrained('users')->onDelete('cascade');
            $table->enum('day_of_week', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->string('location', 100);
            $table->enum('type', ['in-person', 'virtual', 'both']);
            $table->string('meeting_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['faculty_id', 'day_of_week']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('office_hours');
    }
}