<?php
# database/migrations/***_add_user_lifecycle_management.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Analyze current data first
        $this->analyzeCurrentState();
        
        // Add missing columns to admission_applications
        if (!Schema::hasColumn('admission_applications', 'student_id')) {
            Schema::table('admission_applications', function (Blueprint $table) {
                $table->unsignedBigInteger('student_id')->nullable()->after('user_id');
                $table->foreign('student_id')->references('id')->on('students')->nullOnDelete();
            });
        }

        // Fix user types
        $this->fixUserTypes();
        
        // Link orphaned data
        $this->linkOrphanedData();
    }

    private function analyzeCurrentState(): void
    {
        echo "\n=== ANALYZING CURRENT STATE ===\n";
        
        // Check user types
        $userTypes = DB::table('users')
            ->select('user_type', DB::raw('COUNT(*) as count'))
            ->groupBy('user_type')
            ->get();
        
        echo "Current User Types:\n";
        foreach ($userTypes as $type) {
            echo "  - {$type->user_type}: {$type->count} users\n";
        }
        
        // Check orphaned applications
        $orphanedApps = DB::table('admission_applications')
            ->whereNull('user_id')
            ->count();
        echo "\nOrphaned Applications (no user_id): {$orphanedApps}\n";
        
        // Check students without user accounts
        $orphanedStudents = DB::table('students')
            ->whereNull('user_id')
            ->count();
        echo "Orphaned Students (no user_id): {$orphanedStudents}\n";
        
        // Check for email matches between tables
        $matchableApps = DB::select("
            SELECT COUNT(*) as count
            FROM admission_applications aa
            WHERE aa.user_id IS NULL
            AND EXISTS (
                SELECT 1 FROM users u 
                WHERE u.email = aa.email
            )
        ");
        echo "Applications that can be linked by email: {$matchableApps[0]->count}\n";
    }

    private function fixUserTypes(): void
    {
        echo "\n=== FIXING USER TYPES ===\n";
        
        // Add 'applicant' as valid user_type if needed
        $updated = DB::update("
            UPDATE users
            SET user_type = CASE
                WHEN user_type = 'student' AND NOT EXISTS (
                    SELECT 1 FROM students s WHERE s.user_id = users.id
                ) THEN 'applicant'
                WHEN user_type IS NULL OR user_type = '' THEN 'applicant'
                ELSE user_type
            END
            WHERE user_type IS NULL 
            OR user_type = ''
            OR (user_type = 'student' AND NOT EXISTS (
                SELECT 1 FROM students s WHERE s.user_id = users.id
            ))
        ");
        
        echo "Updated {$updated} users to correct user_type\n";
    }

    private function linkOrphanedData(): void
    {
        echo "\n=== LINKING ORPHANED DATA ===\n";
        
        // Link applications to users by email
        $linked = DB::update("
            UPDATE admission_applications aa
            SET user_id = (
                SELECT id FROM users u 
                WHERE u.email = aa.email 
                LIMIT 1
            )
            WHERE aa.user_id IS NULL
            AND EXISTS (
                SELECT 1 FROM users u2 
                WHERE u2.email = aa.email
            )
        ");
        
        echo "Linked {$linked} applications to existing users\n";
        
        // Link students to enrolled applications
        $linkedStudents = DB::update("
            UPDATE admission_applications aa
            SET student_id = (
                SELECT s.id FROM students s
                WHERE s.email = aa.email
                OR (s.user_id = aa.user_id AND aa.user_id IS NOT NULL)
                LIMIT 1
            )
            WHERE aa.student_id IS NULL
            AND aa.enrollment_confirmed = true
            AND EXISTS (
                SELECT 1 FROM students s2
                WHERE s2.email = aa.email
                OR (s2.user_id = aa.user_id AND aa.user_id IS NOT NULL)
            )
        ");
        
        echo "Linked {$linkedStudents} applications to student records\n";
    }

    public function down()
    {
        if (Schema::hasColumn('admission_applications', 'student_id')) {
            Schema::table('admission_applications', function (Blueprint $table) {
                $table->dropForeign(['student_id']);
                $table->dropColumn('student_id');
            });
        }
    }
};