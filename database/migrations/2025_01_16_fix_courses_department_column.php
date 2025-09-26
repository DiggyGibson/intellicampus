<?php

// database/migrations/2025_01_16_fix_courses_department_column.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixCoursesDepartmentColumn extends Migration
{
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            // Step 1: Make department nullable (keeps existing functionality)
            $table->string('department')->nullable()->change();
            
            // Step 2: Add department_id if it doesn't exist
            if (!Schema::hasColumn('courses', 'department_id')) {
                $table->unsignedBigInteger('department_id')->nullable()->after('department');
                $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            }
        });
        
        // Step 3: Populate department_id from existing department strings
        $courses = DB::table('courses')->whereNotNull('department')->get();
        foreach ($courses as $course) {
            $dept = DB::table('departments')->where('name', $course->department)->first();
            if ($dept) {
                DB::table('courses')
                    ->where('id', $course->id)
                    ->update([
                        'department_id' => $dept->id,
                        // Keep department string for backward compatibility
                        'department' => $dept->name
                    ]);
            }
        }
        
        $this->command->info('Migration complete. Both department (string) and department_id (foreign key) now exist.');
        $this->command->info('Existing functionality preserved while adding proper relationships.');
    }

    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
            // Restore department as non-nullable
            $table->string('department')->nullable(false)->change();
        });
    }
}