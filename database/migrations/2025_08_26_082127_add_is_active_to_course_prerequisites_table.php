<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if the column already exists
        if (!Schema::hasColumn('course_prerequisites', 'is_active')) {
            Schema::table('course_prerequisites', function (Blueprint $table) {
                // Add is_active column with default true (all existing prerequisites remain active)
                $table->boolean('is_active')->default(true)->after('type');
            });
            
            echo "✓ Added is_active column to course_prerequisites table\n";
            
            // Create index for better query performance
            // This partial index only includes active prerequisites for faster lookups
            DB::statement('CREATE INDEX idx_prereq_active ON course_prerequisites(course_id, is_active) WHERE is_active = true');
            echo "✓ Created performance index for active prerequisites\n";
            
            // Set all existing prerequisites to active
            DB::table('course_prerequisites')->update(['is_active' => true]);
            echo "✓ Set all existing prerequisites to active\n";
        } else {
            echo "⚠ is_active column already exists in course_prerequisites table\n";
        }
        
        // Also add some related columns for better prerequisite management
        Schema::table('course_prerequisites', function (Blueprint $table) {
            // Add column for seasonal variations if it doesn't exist
            if (!Schema::hasColumn('course_prerequisites', 'applicable_terms')) {
                $table->json('applicable_terms')->nullable()->after('is_active')
                    ->comment('JSON array of term types where this prerequisite applies: ["fall", "spring", "summer"]');
            }
            
            // Add column for exemption conditions
            if (!Schema::hasColumn('course_prerequisites', 'exemption_conditions')) {
                $table->json('exemption_conditions')->nullable()->after('applicable_terms')
                    ->comment('JSON object defining exemption conditions');
            }
            
            // Add column for effective date
            if (!Schema::hasColumn('course_prerequisites', 'effective_from')) {
                $table->date('effective_from')->nullable()->after('exemption_conditions')
                    ->comment('Date when this prerequisite becomes effective');
            }
            
            // Add column for expiration
            if (!Schema::hasColumn('course_prerequisites', 'effective_until')) {
                $table->date('effective_until')->nullable()->after('effective_from')
                    ->comment('Date when this prerequisite expires');
            }
            
            // Add column for who made the last change
            if (!Schema::hasColumn('course_prerequisites', 'modified_by')) {
                $table->unsignedBigInteger('modified_by')->nullable()->after('effective_until');
                
                // Add foreign key if users table exists
                if (Schema::hasTable('users')) {
                    $table->foreign('modified_by')->references('id')->on('users')->onDelete('set null');
                }
            }
            
            // Add column for change reason/notes
            if (!Schema::hasColumn('course_prerequisites', 'modification_reason')) {
                $table->text('modification_reason')->nullable()->after('modified_by')
                    ->comment('Reason for adding, modifying, or deactivating this prerequisite');
            }
        });
        
        echo "✓ Added additional prerequisite management columns\n";
        
        // Create a view for easy access to active prerequisites
        DB::statement("
            CREATE OR REPLACE VIEW active_prerequisites AS
            SELECT 
                cp.*,
                c1.code as course_code,
                c1.title as course_title,
                c2.code as prerequisite_code,
                c2.title as prerequisite_title
            FROM course_prerequisites cp
            JOIN courses c1 ON cp.course_id = c1.id
            JOIN courses c2 ON cp.prerequisite_id = c2.id
            WHERE cp.is_active = true
            AND (cp.effective_from IS NULL OR cp.effective_from <= CURRENT_DATE)
            AND (cp.effective_until IS NULL OR cp.effective_until >= CURRENT_DATE)
        ");
        
        echo "✓ Created active_prerequisites view for easier querying\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the view first
        DB::statement('DROP VIEW IF EXISTS active_prerequisites');
        
        // Drop the index
        DB::statement('DROP INDEX IF EXISTS idx_prereq_active');
        
        // Remove the columns
        Schema::table('course_prerequisites', function (Blueprint $table) {
            $table->dropColumn([
                'is_active',
                'applicable_terms',
                'exemption_conditions',
                'effective_from',
                'effective_until',
                'modified_by',
                'modification_reason'
            ]);
        });
    }
};