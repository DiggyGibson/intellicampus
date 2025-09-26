<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateGradeComponentsTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // First, drop the existing constraint
        Schema::table('grade_components', function (Blueprint $table) {
            // Drop the existing type column with its constraint
            $table->dropColumn('type');
        });
        
        // Then add it back with all the needed types
        Schema::table('grade_components', function (Blueprint $table) {
            // Add type column with expanded options
            $table->enum('type', [
                'exam',           // Tests and exams
                'assignment',     // Regular assignments
                'quiz',          // Quizzes
                'project',       // Projects
                'participation', // Class participation
                'lab',           // Lab work
                'presentation',  // Presentations
                'homework',      // Homework
                'paper',         // Research papers
                'discussion',    // Discussion posts
                'attendance',    // Attendance grade
                'midterm',       // Midterm exam (specific)
                'final',         // Final exam (specific)
                'other'          // Catch-all for special cases
            ])->after('max_points')->default('assignment');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert to original constraint if needed
        Schema::table('grade_components', function (Blueprint $table) {
            $table->dropColumn('type');
        });
        
        Schema::table('grade_components', function (Blueprint $table) {
            $table->enum('type', ['exam', 'assignment', 'quiz', 'project', 'participation', 'other'])
                  ->after('max_points');
        });
    }
}