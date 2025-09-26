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
        // Check if registration_logs table exists
        if (!Schema::hasTable('registration_logs')) {
            // Create the table if it doesn't exist
            Schema::create('registration_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('section_id')->nullable();
                $table->unsignedBigInteger('term_id');
                $table->string('action', 50); // enrolled, dropped, waitlisted, swapped, etc.
                $table->json('details')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();
                
                $table->index(['student_id', 'term_id']);
                $table->index('action');
                $table->index('created_at');
                
                // Foreign keys
                if (Schema::hasTable('students')) {
                    $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
                }
                if (Schema::hasTable('course_sections')) {
                    $table->foreign('section_id')->references('id')->on('course_sections')->onDelete('set null');
                }
                if (Schema::hasTable('academic_terms')) {
                    $table->foreign('term_id')->references('id')->on('academic_terms')->onDelete('cascade');
                }
            });
            
            echo "Created registration_logs table with term_id column.\n";
        } else {
            // Table exists, check if it has term_id column
            if (!Schema::hasColumn('registration_logs', 'term_id')) {
                Schema::table('registration_logs', function (Blueprint $table) {
                    $table->unsignedBigInteger('term_id')->nullable()->after('section_id');
                });
                
                echo "Added term_id column to registration_logs table.\n";
                
                // Try to populate term_id from course_sections
                DB::statement('
                    UPDATE registration_logs rl
                    SET term_id = (
                        SELECT cs.term_id 
                        FROM course_sections cs 
                        WHERE cs.id = rl.section_id
                        LIMIT 1
                    )
                    WHERE rl.term_id IS NULL 
                    AND rl.section_id IS NOT NULL
                ');
                
                echo "Populated term_id from course_sections where possible.\n";
                
                // For any remaining null term_ids, try to use current term
                $currentTerm = DB::table('academic_terms')->where('is_current', true)->first();
                if ($currentTerm) {
                    DB::table('registration_logs')
                        ->whereNull('term_id')
                        ->update(['term_id' => $currentTerm->id]);
                    
                    echo "Set remaining null term_ids to current term.\n";
                }
                
                // Now make it non-nullable if we have values for all rows
                $nullCount = DB::table('registration_logs')->whereNull('term_id')->count();
                if ($nullCount === 0) {
                    Schema::table('registration_logs', function (Blueprint $table) {
                        $table->unsignedBigInteger('term_id')->nullable(false)->change();
                    });
                    
                    // Add foreign key
                    if (Schema::hasTable('academic_terms')) {
                        Schema::table('registration_logs', function (Blueprint $table) {
                            $table->foreign('term_id')->references('id')->on('academic_terms')->onDelete('cascade');
                        });
                    }
                    
                    echo "Made term_id non-nullable and added foreign key.\n";
                }
            }
            
            // Add index if it doesn't exist
            $indexes = DB::select("SELECT indexname FROM pg_indexes WHERE tablename = 'registration_logs'");
            $indexNames = array_column($indexes, 'indexname');
            
            if (!in_array('registration_logs_student_id_term_id_index', $indexNames)) {
                Schema::table('registration_logs', function (Blueprint $table) {
                    $table->index(['student_id', 'term_id']);
                });
                echo "Added index on student_id and term_id.\n";
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('registration_logs', 'term_id')) {
            // Remove foreign key first
            Schema::table('registration_logs', function (Blueprint $table) {
                $table->dropForeign(['term_id']);
            });
            
            // Remove column
            Schema::table('registration_logs', function (Blueprint $table) {
                $table->dropColumn('term_id');
            });
        }
    }
};