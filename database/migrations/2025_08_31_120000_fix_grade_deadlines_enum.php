<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixGradeDeadlinesEnum extends Migration
{
    public function up()
    {
        // For PostgreSQL, we need to modify the enum constraint
        DB::statement("ALTER TABLE grade_deadlines DROP CONSTRAINT IF EXISTS grade_deadlines_deadline_type_check");
        DB::statement("ALTER TABLE grade_deadlines ADD CONSTRAINT grade_deadlines_deadline_type_check CHECK (deadline_type IN ('midterm', 'final', 'incomplete', 'grade_change'))");
    }

    public function down()
    {
        DB::statement("ALTER TABLE grade_deadlines DROP CONSTRAINT IF EXISTS grade_deadlines_deadline_type_check");
        DB::statement("ALTER TABLE grade_deadlines ADD CONSTRAINT grade_deadlines_deadline_type_check CHECK (deadline_type IN ('midterm', 'final', 'incomplete'))");
    }
}