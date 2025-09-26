<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // First, analyze current state
        $this->analyzeGenderValues();
        
        // IMPORTANT: Drop any existing constraints BEFORE trying to update data
        echo "\n=== Removing Existing Constraints ===\n";
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_gender_check");
        DB::statement("ALTER TABLE students DROP CONSTRAINT IF EXISTS students_gender_check");
        DB::statement("ALTER TABLE admission_applications DROP CONSTRAINT IF EXISTS admission_applications_gender_check");
        echo "Constraints removed\n";
        
        // Now we can safely normalize the values
        $this->normalizeGenderValues();
        
        // Finally, add the new constraint
        echo "\n=== Adding New Constraint ===\n";
        DB::statement("
            ALTER TABLE users ADD CONSTRAINT users_gender_check 
            CHECK (gender IS NULL OR gender IN ('male', 'female', 'other', 'prefer_not_to_say'))
        ");
        echo "Gender constraint added successfully\n";
    }
    
    private function analyzeGenderValues()
    {
        echo "\n=== Analyzing Gender Values ===\n";
        
        // Check all three tables
        $tables = ['users', 'admission_applications', 'students'];
        
        foreach ($tables as $table) {
            $genders = DB::select("
                SELECT gender, COUNT(*) as count 
                FROM {$table}
                WHERE gender IS NOT NULL
                GROUP BY gender
                ORDER BY gender
            ");
            
            echo "\n{$table} table gender values:\n";
            foreach ($genders as $g) {
                echo "  - {$g->gender}: {$g->count}\n";
            }
        }
    }
    
    private function normalizeGenderValues()
    {
        echo "\n=== Normalizing Gender Values ===\n";
        
        // Normalize each table
        $tables = ['users', 'students'];  // admission_applications already looks clean
        
        foreach ($tables as $table) {
            echo "\nNormalizing {$table} table:\n";
            
            // First, fix case issues (Male -> male, Female -> female, Other -> other)
            $updates = [
                ["UPDATE {$table} SET gender = 'male' WHERE gender IN ('Male', 'M', 'male')", 'male'],
                ["UPDATE {$table} SET gender = 'female' WHERE gender IN ('Female', 'F', 'female')", 'female'],
                ["UPDATE {$table} SET gender = 'other' WHERE gender IN ('Other', 'O', 'other')", 'other'],
            ];
            
            foreach ($updates as [$query, $type]) {
                $affected = DB::update($query);
                if ($affected > 0) {
                    echo "  Updated {$affected} rows to '{$type}'\n";
                }
            }
            
            // Check for any remaining non-standard values
            $nonStandard = DB::select("
                SELECT gender, COUNT(*) as count 
                FROM {$table}
                WHERE gender IS NOT NULL 
                AND gender NOT IN ('male', 'female', 'other', 'prefer_not_to_say')
                GROUP BY gender
            ");
            
            if (count($nonStandard) > 0) {
                echo "  Non-standard values found (will be set to 'other'):\n";
                foreach ($nonStandard as $g) {
                    echo "    - {$g->gender}: {$g->count}\n";
                }
                
                // Set any remaining non-standard values to 'other'
                $affected = DB::update("
                    UPDATE {$table}
                    SET gender = 'other' 
                    WHERE gender IS NOT NULL 
                    AND gender NOT IN ('male', 'female', 'other', 'prefer_not_to_say')
                ");
                
                if ($affected > 0) {
                    echo "  Set {$affected} non-standard values to 'other'\n";
                }
            } else {
                echo "  All values are now standard\n";
            }
        }
    }

    public function down()
    {
        // Just remove the constraint
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_gender_check");
    }
};