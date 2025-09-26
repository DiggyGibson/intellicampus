<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_programs', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->enum('level', ['certificate', 'diploma', 'associate', 'bachelor', 'master', 'doctorate']);
            $table->string('department');
            $table->string('faculty');
            $table->integer('duration_years')->default(4);
            $table->integer('total_credits')->default(120);
            $table->integer('core_credits')->default(40);
            $table->integer('major_credits')->default(60);
            $table->integer('elective_credits')->default(20);
            $table->decimal('min_gpa', 3, 2)->default(2.00);
            $table->decimal('graduation_gpa', 3, 2)->default(2.00);
            $table->text('description')->nullable();
            $table->text('learning_outcomes')->nullable();
            $table->text('career_prospects')->nullable();
            $table->text('admission_requirements')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('accreditation_status')->nullable();
            $table->date('accreditation_date')->nullable();
            $table->date('next_review_date')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('code');
            $table->index('department');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_programs');
    }
};