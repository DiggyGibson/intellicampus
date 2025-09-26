<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SyncStudentUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:sync-users 
                            {--dry-run : Show what would be done without making changes}
                            {--limit=0 : Process only N students (0 for all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create user accounts for all students who don\'t have them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('========================================');
        $this->info('  Student-User Account Synchronization');
        $this->info('========================================');
        
        $dryRun = $this->option('dry-run');
        $limit = (int) $this->option('limit');
        
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        // Get the student role
        $studentRole = Role::where('slug', 'student')
                          ->orWhere('name', 'Student')
                          ->first();
        
        if (!$studentRole && !$dryRun) {
            $this->error('Student role not found! Please create a "Student" role first.');
            return 1;
        }

        // Step 1: Find students without user accounts
        $query = Student::whereNull('user_id')
                        ->orWhereDoesntHave('user');
        
        if ($limit > 0) {
            $query->limit($limit);
        }
        
        $studentsWithoutUsers = $query->get();

        $this->info("Found {$studentsWithoutUsers->count()} students without user accounts");
        $this->newLine();

        if ($studentsWithoutUsers->count() > 0) {
            // Show preview table
            $this->info('Students to process:');
            $this->table(
                ['ID', 'Student ID', 'Name', 'Email', 'Status'],
                $studentsWithoutUsers->take(10)->map(function ($s) {
                    return [
                        $s->id,
                        $s->student_id,
                        trim($s->first_name . ' ' . $s->last_name),
                        Str::limit($s->email, 30),
                        $s->enrollment_status
                    ];
                })
            );
            
            if ($studentsWithoutUsers->count() > 10) {
                $this->info("... and " . ($studentsWithoutUsers->count() - 10) . " more");
            }
            
            $this->newLine();
            
            if (!$dryRun) {
                if ($this->confirm('Do you want to create user accounts for these students?', true)) {
                    $this->createUsersForStudents($studentsWithoutUsers, $studentRole);
                } else {
                    $this->info('Operation cancelled.');
                }
            } else {
                $this->info('In live mode, user accounts would be created for these students.');
            }
        } else {
            $this->info('✓ All students have user accounts!');
        }

        // Show final summary
        $this->showSummary();
        
        return 0;
    }

    /**
     * Create user accounts for students
     */
    protected function createUsersForStudents($students, $studentRole)
    {
        $this->newLine();
        $this->info('Creating user accounts...');
        
        $progressBar = $this->output->createProgressBar($students->count());
        $progressBar->start();

        $created = 0;
        $failed = 0;
        $skipped = 0;
        $errors = [];

        foreach ($students as $student) {
            try {
                DB::beginTransaction();
                
                // Check if user with this email already exists
                $existingUser = User::where('email', $student->email)->first();
                
                if ($existingUser) {
                    // Link existing user to student
                    $student->user_id = $existingUser->id;
                    $student->save();
                    
                    // Update user type to student if not already
                    if ($existingUser->user_type !== 'student') {
                        $existingUser->user_type = 'student';
                        $existingUser->save();
                    }
                    
                    // Assign student role if not already assigned
                    if ($studentRole && !$existingUser->hasRole($studentRole)) {
                        $existingUser->assignRole($studentRole);
                    }
                    
                    $skipped++;
                } else {
                    // Generate username from name
                    $username = $this->generateUsername($student);
                    
                    // Create new user
                    $user = User::create([
                        'name' => trim($student->first_name . ' ' . $student->middle_name . ' ' . $student->last_name),
                        'first_name' => $student->first_name,
                        'middle_name' => $student->middle_name,
                        'last_name' => $student->last_name,
                        'email' => $student->email,
                        'username' => $username,
                        'password' => Hash::make($student->student_id), // Default password is student ID
                        'user_type' => 'student',
                        'status' => $this->mapStudentStatus($student->enrollment_status),
                        'phone' => $student->phone,
                        'date_of_birth' => $student->date_of_birth,
                        'gender' => ucfirst($student->gender),
                        'nationality' => $student->nationality,
                        'national_id' => $student->national_id_number,
                        'address' => $student->address,
                        'emergency_contact_name' => $student->emergency_contact_name,
                        'emergency_contact_phone' => $student->emergency_contact_phone,
                        'must_change_password' => true, // Force password change on first login
                        'email_verified_at' => now(), // Mark as verified since we trust student data
                    ]);
                    
                    // Link student to user
                    $student->user_id = $user->id;
                    $student->save();
                    
                    // Assign student role
                    if ($studentRole) {
                        $user->assignRole($studentRole);
                    }
                    
                    $created++;
                }
                
                DB::commit();
                
            } catch (\Exception $e) {
                DB::rollBack();
                $failed++;
                $errors[] = "Student {$student->student_id}: " . $e->getMessage();
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
        
        // Show results
        $this->info('✓ Process completed!');
        $this->newLine();
        
        $this->table(
            ['Result', 'Count'],
            [
                ['New accounts created', $created],
                ['Existing users linked', $skipped],
                ['Failed', $failed],
            ]
        );
        
        // Show errors if any
        if (count($errors) > 0) {
            $this->newLine();
            $this->error('Errors encountered:');
            foreach (array_slice($errors, 0, 5) as $error) {
                $this->line('  - ' . $error);
            }
            if (count($errors) > 5) {
                $this->line('  ... and ' . (count($errors) - 5) . ' more errors');
            }
        }
        
        // Success message
        if ($created > 0) {
            $this->newLine();
            $this->info("Successfully created {$created} user accounts!");
            $this->info("Default password for all accounts: [Student ID]");
            $this->warn("Students will be required to change password on first login.");
        }
    }

    /**
     * Generate unique username for student
     */
    protected function generateUsername($student)
    {
        // Start with first letter of first name + last name
        $base = strtolower(substr($student->first_name, 0, 1) . $student->last_name);
        $base = preg_replace('/[^a-z0-9]/', '', $base);
        
        // If too short, use full first name
        if (strlen($base) < 4) {
            $base = strtolower($student->first_name . $student->last_name);
            $base = preg_replace('/[^a-z0-9]/', '', $base);
        }
        
        // Make it unique
        $username = $base;
        $counter = 1;
        while (User::where('username', $username)->exists()) {
            $username = $base . $counter;
            $counter++;
        }
        
        return $username;
    }

    /**
     * Map student enrollment status to user status
     */
    protected function mapStudentStatus($enrollmentStatus)
    {
        return match($enrollmentStatus) {
            'active', 'enrolled' => 'active',
            'inactive', 'suspended' => 'suspended',
            'graduated', 'alumni' => 'inactive',
            default => 'pending'
        };
    }

    /**
     * Show final summary
     */
    protected function showSummary()
    {
        $this->newLine();
        $this->info('========================================');
        $this->info('  Final Summary');
        $this->info('========================================');
        
        $totalStudents = Student::count();
        $studentsWithUsers = Student::whereNotNull('user_id')->whereHas('user')->count();
        $studentsWithoutUsers = $totalStudents - $studentsWithUsers;
        $totalStudentUsers = User::where('user_type', 'student')->count();
        
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Students in Database', $totalStudents],
                ['Students with User Accounts', $studentsWithUsers],
                ['Students without User Accounts', $studentsWithoutUsers],
                ['Total Student Users', $totalStudentUsers],
            ]
        );
        
        if ($studentsWithoutUsers > 0) {
            $this->newLine();
            $this->warn("⚠ There are still {$studentsWithoutUsers} students without user accounts.");
            $this->info("Run this command again without --dry-run to create their accounts.");
        } else {
            $this->newLine();
            $this->info("✓ All students have user accounts!");
        }
    }
}