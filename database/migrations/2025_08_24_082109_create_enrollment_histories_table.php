<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollment_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('action_type', 50); // status_change, leave_request, withdrawal, readmission, etc.
            $table->string('from_status', 50)->nullable();
            $table->string('to_status', 50);
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->date('effective_date');
            $table->date('end_date')->nullable(); // For temporary statuses like leave
            $table->string('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('created_by');
            $table->json('supporting_documents')->nullable();
            $table->timestamps();
            
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->index(['student_id', 'action_type']);
            $table->index('effective_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollment_histories');
    }
};