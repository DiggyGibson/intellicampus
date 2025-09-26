<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations to fix the students table
     */
    public function up(): void
    {
        // First, let's make problematic columns nullable so we can insert data
        Schema::table('students', function (Blueprint $table) {
            // Check and modify columns that are causing issues
            
            // Make academic_level nullable if it exists
            if (Schema::hasColumn('students', 'academic_level')) {
                $table->string('academic_level')->nullable()->change();
            }
            
            // Make other potentially problematic columns nullable
            $columnsMakeNullable = [
                'academic_standing',
                'admission_status', 
                'admission_date',
                'department',
                'major',
                'minor',
                'expected_graduation_year',
                'graduation_date',
                'degree_awarded',
                'current_gpa',
                'cumulative_gpa',
                'credits_earned',
                'credits_completed',
                'credits_required',
                'user_id',
                'program_id'
            ];
            
            foreach ($columnsMakeNullable as $column) {
                if (Schema::hasColumn('students', $column)) {
                    try {
                        // Use raw SQL to alter column to nullable
                        DB::statement("ALTER TABLE students ALTER COLUMN $column DROP NOT NULL");
                    } catch (\Exception $e) {
                        // Column might already be nullable or doesn't exist
                    }
                }
            }
        });
        
        // Add any missing columns that we commonly need
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
            }
            
            if (!Schema::hasColumn('students', 'phone')) {
                $table->string('phone', 20)->nullable()->after('email');
            }
            
            if (!Schema::hasColumn('students', 'nationality')) {
                $table->string('nationality', 100)->nullable();
            }
            
            if (!Schema::hasColumn('students', 'address')) {
                $table->text('address')->nullable();
            }
        });
        
        // Show the actual structure for debugging
        echo "\n=== STUDENTS TABLE STRUCTURE ===\n";
        $columns = DB::select("
            SELECT column_name, data_type, is_nullable, column_default 
            FROM information_schema.columns 
            WHERE table_name = 'students' 
            ORDER BY ordinal_position
        ");
        
        foreach ($columns as $column) {
            echo sprintf(
                "%-30s %-20s %-10s %s\n",
                $column->column_name,
                $column->data_type,
                $column->is_nullable,
                $column->column_default ?? 'NULL'
            );
        }
        echo "================================\n\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We don't need to reverse these fixes
    }
};