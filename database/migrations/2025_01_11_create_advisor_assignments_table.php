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
        Schema::create('advisor_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('advisor_id')->constrained('users')->onDelete('cascade');
            $table->enum('advisor_type', ['primary', 'secondary', 'thesis', 'career', 'special'])->default('primary');
            $table->date('assigned_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_primary')->default(false);
            $table->text('notes')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users');
            $table->string('assignment_reason')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['student_id', 'is_active', 'is_primary']);
            $table->index(['advisor_id', 'is_active']);
            $table->index(['assigned_date', 'end_date']);
        });
        
        // Create initial advisor assignments for existing students (if needed)
        $this->createInitialAssignments();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advisor_assignments');
    }
    
    /**
     * Create initial advisor assignments for testing
     */
    private function createInitialAssignments(): void
    {
        try {
            // Get existing faculty/advisor users
            $advisors = DB::table('users')
                ->join('role_user', 'users.id', '=', 'role_user.user_id')
                ->join('roles', 'role_user.role_id', '=', 'roles.id')
                ->whereIn('roles.name', ['advisor', 'faculty', 'academic-advisor'])
                ->select('users.id')
                ->limit(5)
                ->get();
            
            if ($advisors->isEmpty()) {
                // Check if advisor user already exists
                $existingAdvisor = DB::table('users')->where('email', 'advisor@intellicampus.edu')->first();
                
                if ($existingAdvisor) {
                    $advisorId = $existingAdvisor->id;
                } else {
                    // Create a test advisor only if it doesn't exist
                    $advisorId = DB::table('users')->insertGetId([
                        'name' => 'Dr. Academic Advisor',
                        'email' => 'advisor@intellicampus.edu',
                        'password' => bcrypt('password'),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
                
                // Get or create advisor role using your actual schema
                $advisorRole = DB::table('roles')->where('name', 'advisor')->first();
                if (!$advisorRole) {
                    $advisorRoleId = DB::table('roles')->insertGetId([
                        'name' => 'advisor',
                        'slug' => 'advisor',
                        'description' => 'Academic advisor role',
                        'is_system' => false,
                        'is_active' => true,
                        'priority' => 50,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                } else {
                    $advisorRoleId = $advisorRole->id;
                }
                
                // Check if role assignment already exists
                $roleExists = DB::table('role_user')
                    ->where('user_id', $advisorId)
                    ->where('role_id', $advisorRoleId)
                    ->exists();
                    
                if (!$roleExists) {
                    DB::table('role_user')->insert([
                        'user_id' => $advisorId,
                        'role_id' => $advisorRoleId
                    ]);
                }
                
                $advisors = collect([['id' => $advisorId]]);
            }
            
            // Get students without advisors
            $students = DB::table('students')
                ->leftJoin('advisor_assignments', function($join) {
                    $join->on('students.id', '=', 'advisor_assignments.student_id')
                         ->where('advisor_assignments.is_active', '=', true);
                })
                ->whereNull('advisor_assignments.id')
                ->select('students.id')
                ->limit(20)
                ->get();
            
            if ($students->isEmpty()) {
                echo "No students need advisor assignments.\n";
                return;
            }
            
            // Assign advisors to students
            $assignmentCount = 0;
            foreach ($students as $index => $student) {
                $advisorIndex = $index % $advisors->count();
                $advisor = $advisors[$advisorIndex];
                
                // Double-check assignment doesn't exist
                $exists = DB::table('advisor_assignments')
                    ->where('student_id', $student->id)
                    ->where('is_active', true)
                    ->where('is_primary', true)
                    ->exists();
                    
                if (!$exists) {
                    DB::table('advisor_assignments')->insert([
                        'student_id' => $student->id,
                        'advisor_id' => $advisor->id ?? $advisor['id'],
                        'advisor_type' => 'primary',
                        'assigned_date' => now()->subMonths(rand(1, 12)),
                        'is_active' => true,
                        'is_primary' => true,
                        'assignment_reason' => 'Initial assignment based on department',
                        'notes' => 'Auto-assigned during system setup',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    $assignmentCount++;
                }
            }
            
            echo "Created $assignmentCount advisor assignments.\n";
            
        } catch (\Exception $e) {
            echo "Warning: Could not create initial assignments: " . $e->getMessage() . "\n";
            echo "You can manually assign advisors later.\n";
        }
    }
};