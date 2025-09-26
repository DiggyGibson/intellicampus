<?php
// database/migrations/2024_12_03_fix_registration_system_complete.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // PART 1: Fix registration_carts table
        echo "Starting registration_carts migration...\n";
        
        // Backup existing cart data
        $existingCarts = [];
        if (Schema::hasTable('registration_carts')) {
            $existingCarts = DB::table('registration_carts')->get();
            echo "Found " . count($existingCarts) . " existing cart records to migrate\n";
        }
        
        // Drop the old table
        Schema::dropIfExists('registration_carts');
        
        // Create new structure with JSON support
        Schema::create('registration_carts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('term_id');
            
            // JSON column for multiple sections - THIS IS THE KEY CHANGE
            $table->json('section_ids')->default('[]');
            $table->integer('total_credits')->default(0);
            
            // Validation tracking
            $table->timestamp('validated_at')->nullable();
            $table->boolean('has_time_conflicts')->default(false);
            $table->boolean('has_prerequisite_issues')->default(false);
            
            // Additional fields
            $table->text('validation_messages')->nullable();
            $table->enum('status', ['active', 'submitted', 'expired'])->default('active');
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('term_id')->references('id')->on('academic_terms')->onDelete('cascade');
            
            // One cart per student per term
            $table->unique(['student_id', 'term_id']);
            
            // Indexes
            $table->index('status');
            $table->index('validated_at');
        });
        
        echo "New registration_carts structure created\n";
        
        // Migrate old data if exists
        if (count($existingCarts) > 0) {
            echo "Migrating old cart data...\n";
            
            // Group by student and term (in case of multiple sections)
            $grouped = collect($existingCarts)->groupBy(function ($item) {
                return $item->student_id . '-' . $item->term_id;
            });
            
            foreach ($grouped as $key => $items) {
                list($studentId, $termId) = explode('-', $key);
                
                // Collect all section IDs for this student/term
                $sectionIds = $items->pluck('section_id')->filter()->values()->toArray();
                
                if (!empty($sectionIds)) {
                    // Calculate total credits
                    $totalCredits = DB::table('course_sections as cs')
                        ->join('courses as c', 'cs.course_id', '=', 'c.id')
                        ->whereIn('cs.id', $sectionIds)
                        ->sum('c.credits');
                    
                    // Determine status
                    $statuses = $items->pluck('status')->unique();
                    $finalStatus = 'active';
                    if ($statuses->contains('registered')) {
                        $finalStatus = 'submitted';
                    }
                    
                    // Insert consolidated cart
                    DB::table('registration_carts')->insert([
                        'student_id' => $studentId,
                        'term_id' => $termId,
                        'section_ids' => json_encode($sectionIds),
                        'total_credits' => $totalCredits,
                        'status' => $finalStatus,
                        'created_at' => $items->first()->created_at ?? now(),
                        'updated_at' => $items->first()->updated_at ?? now(),
                    ]);
                    
                    echo "Migrated cart for student $studentId with " . count($sectionIds) . " sections\n";
                }
            }
        }
        
        // PART 2: Fix waitlists table structure
        echo "\nFixing waitlists table...\n";
        
        // Backup existing waitlist data
        $existingWaitlists = [];
        if (Schema::hasTable('waitlists')) {
            $existingWaitlists = DB::table('waitlists')->get();
            echo "Found " . count($existingWaitlists) . " waitlist records\n";
        }
        
        // Drop and recreate with correct structure
        Schema::dropIfExists('waitlists');
        
        Schema::create('waitlists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('term_id');
            $table->integer('position');
            
            // Date fields matching controller expectations
            $table->timestamp('added_at');
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            
            // Status with correct values
            $table->enum('status', ['waiting', 'notified', 'enrolled', 'expired', 'cancelled'])
                  ->default('waiting');
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('section_id')->references('id')->on('course_sections')->onDelete('cascade');
            $table->foreign('term_id')->references('id')->on('academic_terms')->onDelete('cascade');
            
            // Constraints and indexes
            $table->unique(['student_id', 'section_id']);
            $table->index(['section_id', 'position']);
            $table->index(['status', 'position']);
        });
        
        echo "New waitlists structure created\n";
        
        // Migrate old waitlist data
        if (count($existingWaitlists) > 0) {
            echo "Migrating old waitlist data...\n";
            
            foreach ($existingWaitlists as $oldWaitlist) {
                // Get term_id from section
                $section = DB::table('course_sections')
                    ->where('id', $oldWaitlist->section_id)
                    ->first();
                
                if ($section) {
                    DB::table('waitlists')->insert([
                        'student_id' => $oldWaitlist->student_id,
                        'section_id' => $oldWaitlist->section_id,
                        'term_id' => $section->term_id,
                        'position' => $oldWaitlist->position,
                        'added_at' => $oldWaitlist->added_date ?? $oldWaitlist->created_at ?? now(),
                        'notified_at' => $oldWaitlist->offer_date,
                        'expires_at' => $oldWaitlist->expiry_date,
                        'status' => $this->mapWaitlistStatus($oldWaitlist->status),
                        'created_at' => $oldWaitlist->created_at ?? now(),
                        'updated_at' => $oldWaitlist->updated_at ?? now(),
                    ]);
                    
                    echo "Migrated waitlist entry for student {$oldWaitlist->student_id}\n";
                }
            }
        }
        
        echo "\nMigration completed successfully!\n";
    }
    
    /**
     * Map old status values to new ones
     */
    private function mapWaitlistStatus($oldStatus)
    {
        // Map any old status values to new enum values
        $mapping = [
            'active' => 'waiting',
            'pending' => 'waiting',
            'offered' => 'notified',
            'accepted' => 'enrolled',
            'declined' => 'cancelled',
            'expired' => 'expired'
        ];
        
        return $mapping[strtolower($oldStatus)] ?? 'waiting';
    }
    
    public function down()
    {
        // Backup current data
        $currentCarts = DB::table('registration_carts')->get();
        $currentWaitlists = DB::table('waitlists')->get();
        
        // Restore registration_carts to old structure
        Schema::dropIfExists('registration_carts');
        
        Schema::create('registration_carts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('term_id');
            $table->enum('status', ['pending', 'registered', 'dropped'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('section_id')->references('id')->on('course_sections')->onDelete('cascade');
            $table->foreign('term_id')->references('id')->on('academic_terms')->onDelete('cascade');
            
            $table->unique(['student_id', 'section_id']);
            $table->index(['student_id', 'status']);
        });
        
        // Restore cart data (expanding JSON to individual rows)
        foreach ($currentCarts as $cart) {
            $sectionIds = json_decode($cart->section_ids, true);
            foreach ($sectionIds as $sectionId) {
                DB::table('registration_carts')->insert([
                    'student_id' => $cart->student_id,
                    'term_id' => $cart->term_id,
                    'section_id' => $sectionId,
                    'status' => $cart->status == 'submitted' ? 'registered' : 'pending',
                    'created_at' => $cart->created_at,
                    'updated_at' => $cart->updated_at,
                ]);
            }
        }
        
        // Restore waitlists to old structure
        Schema::dropIfExists('waitlists');
        
        Schema::create('waitlists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('student_id');
            $table->integer('position');
            $table->timestamp('added_date')->nullable();
            $table->timestamp('expiry_date')->nullable();
            $table->string('status')->nullable();
            $table->timestamp('offer_date')->nullable();
            $table->timestamp('response_date')->nullable();
            $table->timestamps();
            
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('section_id')->references('id')->on('course_sections')->onDelete('cascade');
        });
        
        // Restore waitlist data
        foreach ($currentWaitlists as $waitlist) {
            DB::table('waitlists')->insert([
                'section_id' => $waitlist->section_id,
                'student_id' => $waitlist->student_id,
                'position' => $waitlist->position,
                'added_date' => $waitlist->added_at,
                'expiry_date' => $waitlist->expires_at,
                'status' => $waitlist->status,
                'offer_date' => $waitlist->notified_at,
                'created_at' => $waitlist->created_at,
                'updated_at' => $waitlist->updated_at,
            ]);
        }
    }
};