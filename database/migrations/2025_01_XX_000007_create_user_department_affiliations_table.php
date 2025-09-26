<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_department_affiliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            
            $table->enum('affiliation_type', [
                'primary',      // Primary department
                'secondary',    // Secondary appointment
                'adjunct',      // Adjunct faculty
                'visiting',     // Visiting faculty
                'emeritus',     // Emeritus status
                'courtesy',     // Courtesy appointment
                'affiliate',    // Affiliated member
                'cross_appointment' // Cross-department appointment
            ]);
            
            $table->enum('role', [
                'faculty',
                'staff',
                'researcher',
                'teaching_assistant',
                'research_assistant',
                'administrator'
            ])->default('faculty');
            
            $table->decimal('appointment_percentage', 5, 2)->default(100.00);
            // For split appointments (e.g., 60% in one dept, 40% in another)
            
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->string('position_title')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->unique(['user_id', 'department_id', 'affiliation_type']);
            $table->index(['department_id', 'is_active']);
            $table->index(['user_id', 'is_active']);
            $table->index('affiliation_type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_department_affiliations');
    }
};
