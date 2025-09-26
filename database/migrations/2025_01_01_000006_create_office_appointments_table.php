<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOfficeAppointmentsTable extends Migration
{
    public function up()
    {
        Schema::create('office_appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->date('appointment_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('purpose', 200);
            $table->text('notes')->nullable();
            $table->enum('status', ['scheduled', 'completed', 'cancelled', 'no-show']);
            $table->enum('type', ['in-person', 'virtual']);
            $table->string('meeting_url')->nullable();
            $table->timestamps();
            
            $table->index(['faculty_id', 'appointment_date']);
            $table->index(['student_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('office_appointments');
    }
}