<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AdmissionApplication;
use App\Models\User;
use App\Services\UserLifecycleService;

class LinkOrphanedApplications extends Command
{
    protected $signature = 'admissions:link-orphaned {--create : Create user accounts for orphaned applications}';
    protected $description = 'Link orphaned admission applications to users';

    protected UserLifecycleService $lifecycleService;

    public function __construct(UserLifecycleService $lifecycleService)
    {
        parent::__construct();
        $this->lifecycleService = $lifecycleService;
    }

    public function handle()
    {
        $orphaned = AdmissionApplication::whereNull('user_id')->get();
        
        $this->info("Found {$orphaned->count()} orphaned applications");
        
        foreach ($orphaned as $application) {
            $this->line("Processing: {$application->application_number} - {$application->email}");
            
            // Check if user exists with this email
            $user = User::where('email', $application->email)->first();
            
            if ($user) {
                $application->user_id = $user->id;
                $application->save();
                $this->info("  ✓ Linked to existing user ID: {$user->id}");
            } elseif ($this->option('create')) {
                // Create new applicant user
                $user = $this->lifecycleService->createApplicant([
                    'email' => $application->email,
                    'first_name' => $application->first_name,
                    'middle_name' => $application->middle_name,
                    'last_name' => $application->last_name,
                    'phone' => $application->phone_primary,
                    'date_of_birth' => $application->date_of_birth,
                    'gender' => $application->gender,
                    'nationality' => $application->nationality,
                    'auto_verify' => false,
                ]);
                
                $application->user_id = $user->id;
                $application->save();
                
                $this->info("  ✓ Created new user ID: {$user->id}");
            } else {
                $this->warn("  ⚠ No user found for email: {$application->email}");
            }
        }
        
        $remaining = AdmissionApplication::whereNull('user_id')->count();
        if ($remaining > 0) {
            $this->warn("\nStill have {$remaining} orphaned applications.");
            $this->info("Run with --create option to create user accounts for them.");
        } else {
            $this->info("\n✓ All applications are now linked to users!");
        }
    }
}