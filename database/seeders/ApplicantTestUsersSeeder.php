<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ApplicantTestUsersSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Creating test applicant users...');
        
        // Create applicant users
        $this->createApplicantUsers();
        
        // Create applicant profiles
        $this->createApplicantProfiles();
        
        // Create sample applications
        $this->createSampleApplications();
        
        $this->command->info('✅ Test applicants created successfully!');
        $this->displayLoginCredentials();
    }
    
    private function createApplicantUsers()
    {
        $applicants = [
            [
                'name' => 'John Applicant',
                'email' => 'applicant@example.com',
                'password' => Hash::make('password'),
                'user_type' => 'applicant',
                'email_verified_at' => Carbon::now(), // Already verified
            ],
            [
                'name' => 'Jane Test',
                'email' => 'jane.applicant@test.com',
                'password' => Hash::make('TestPass123'),
                'user_type' => 'applicant',
                'email_verified_at' => Carbon::now(),
            ],
            [
                'name' => 'David International',
                'email' => 'intl.applicant@test.com',
                'password' => Hash::make('TestPass123'),
                'user_type' => 'applicant',
                'email_verified_at' => Carbon::now(),
            ],
            [
                'name' => 'Sarah Graduate',
                'email' => 'grad.applicant@test.com',
                'password' => Hash::make('TestPass123'),
                'user_type' => 'applicant',
                'email_verified_at' => Carbon::now(),
            ],
        ];
        
        foreach ($applicants as $applicantData) {
            $user = User::updateOrCreate(
                ['email' => $applicantData['email']],
                $applicantData
            );
            
            $this->command->info("✓ Created user: {$user->email}");
        }
    }
    
    private function createApplicantProfiles()
    {
        $this->command->info('Creating applicant profiles...');
        
        $users = User::where('user_type', 'applicant')->get();
        
        foreach ($users as $user) {
            // Skip if profile already exists
            if (DB::table('applicants')->where('user_id', $user->id)->exists()) {
                $this->command->info("⚠ Profile already exists for: {$user->email}");
                continue;
            }
            
            $names = explode(' ', $user->name);
            $firstName = $names[0] ?? 'First';
            $lastName = $names[1] ?? 'Last';
            
            // Generate unique applicant ID
            $year = date('Y');
            $count = DB::table('applicants')->count() + 1;
            $applicantId = sprintf('APP-%s-%05d', $year, $count);
            
            $profileData = [
                'user_id' => $user->id,
                'applicant_id' => $applicantId,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
            
            // Add specific data for each applicant (without email field)
            switch ($user->email) {
                case 'applicant@example.com':
                    $profileData = array_merge($profileData, [
                        'date_of_birth' => '2005-03-15',
                        'gender' => 'male',
                        'phone' => '555-0123',
                        'address' => '123 Main Street',
                        'city' => 'Springfield',
                        'state' => 'IL',
                        'country' => 'USA',
                        'postal_code' => '62701',
                        'citizenship' => 'USA',
                    ]);
                    break;
                    
                case 'jane.applicant@test.com':
                    $profileData = array_merge($profileData, [
                        'date_of_birth' => '2004-08-22',
                        'gender' => 'female',
                        'phone' => '555-0456',
                        'address' => '456 Oak Avenue',
                        'city' => 'Chicago',
                        'state' => 'IL',
                        'country' => 'USA',
                        'postal_code' => '60601',
                        'citizenship' => 'USA',
                    ]);
                    break;
                    
                case 'intl.applicant@test.com':
                    $profileData = array_merge($profileData, [
                        'date_of_birth' => '2003-12-10',
                        'gender' => 'male',
                        'phone' => '+44-20-7123-4567',
                        'address' => '10 Downing Street',
                        'city' => 'London',
                        'state' => '',
                        'country' => 'UK',
                        'postal_code' => 'SW1A 2AA',
                        'citizenship' => 'UK',
                        'passport_number' => 'UK123456789',
                    ]);
                    break;
                    
                case 'grad.applicant@test.com':
                    $profileData = array_merge($profileData, [
                        'date_of_birth' => '1998-06-30',
                        'gender' => 'female',
                        'phone' => '555-0789',
                        'address' => '789 University Blvd',
                        'city' => 'Boston',
                        'state' => 'MA',
                        'country' => 'USA',
                        'postal_code' => '02134',
                        'citizenship' => 'USA',
                    ]);
                    break;
            }
            
            DB::table('applicants')->insert($profileData);
            
            $this->command->info("✓ Created applicant profile: {$applicantId}");
        }
    }
    
    private function createSampleApplications()
    {
        $this->command->info('Creating sample applications...');
        
        // Get programs
        $bscsProgram = DB::table('programs')->where('code', 'BSCS')->first();
        $mbaProgram = DB::table('programs')->where('code', 'MBA')->first();
        
        if (!$bscsProgram) {
            $this->command->warn('BSCS program not found. Using first available program.');
            $bscsProgram = DB::table('programs')->first();
        }
        
        if (!$mbaProgram) {
            $this->command->warn('MBA program not found. Using second available program.');
            $mbaProgram = DB::table('programs')->skip(1)->first();
        }
        
        if (!$bscsProgram || !$mbaProgram) {
            $this->command->error('No programs found. Please run AcademicProgramsSeeder first.');
            return;
        }
        
        // Get or create a term
        $currentTerm = DB::table('academic_terms')->first();
        if (!$currentTerm) {
            $this->command->info('Creating default academic term...');
            $termId = DB::table('academic_terms')->insertGetId([
                'code' => 'FALL2025',
                'name' => 'Fall 2025',
                'type' => 'semester',
                'academic_year' => '2025-2026',
                'start_date' => '2025-08-25',
                'end_date' => '2025-12-20',
                'is_current' => true,
                'is_admission_open' => true,
                'admission_deadline' => '2025-07-15',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            $currentTerm = DB::table('academic_terms')->find($termId);
        }
        
        // Get applicants
        $johnApplicant = DB::table('applicants')
            ->join('users', 'applicants.user_id', '=', 'users.id')
            ->where('users.email', 'applicant@example.com')
            ->select('applicants.*', 'users.email')
            ->first();
            
        $gradApplicant = DB::table('applicants')
            ->join('users', 'applicants.user_id', '=', 'users.id')
            ->where('users.email', 'grad.applicant@test.com')
            ->select('applicants.*', 'users.email')
            ->first();
        
        if (!$johnApplicant || !$gradApplicant) {
            $this->command->warn('Applicants not found. Skipping application creation.');
            return;
        }
        
        // Map academic_programs.id to programs.id
        $bscsProgramId = DB::table('academic_programs')
            ->where('code', 'BSCS')
            ->value('id') ?? $bscsProgram->id;
            
        $mbaProgramId = DB::table('academic_programs')
            ->where('code', 'MBA')
            ->value('id') ?? $mbaProgram->id;
        
        // Check if applications already exist
        $existingDraft = DB::table('admission_applications')
            ->where('user_id', $johnApplicant->user_id)
            ->where('status', 'draft')
            ->first();
            
        if (!$existingDraft) {
            // Create draft application for John
            $appNumber = 'APP-2025-' . str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT);
            $appUuid = \Illuminate\Support\Str::uuid();
            
            $draftAppId = DB::table('admission_applications')->insertGetId([
                'application_number' => $appNumber,
                'application_uuid' => $appUuid,
                'user_id' => $johnApplicant->user_id,
                'applicant_id' => $johnApplicant->id,
                'program_id' => $bscsProgramId,
                'term_id' => $currentTerm->id,
                
                // Personal Information
                'first_name' => 'John',
                'last_name' => 'Applicant',
                'email' => $johnApplicant->email,
                'date_of_birth' => '2005-03-15',
                'gender' => 'male',
                'nationality' => 'American',
                'phone_primary' => '555-0123',
                'current_address' => '123 Main Street, Springfield, IL 62701',
                'permanent_address' => '123 Main Street, Springfield, IL 62701',
                'city' => 'Springfield',
                'state_province' => 'IL',
                'postal_code' => '62701',
                'country' => 'USA',
                
                // Academic Information  
                'application_type' => 'freshman',
                'entry_year' => 2025,
                'high_school_name' => 'Springfield High School',
                'high_school_country' => 'USA',
                'high_school_graduation_date' => '2023-05-30',
                'previous_gpa' => 3.8,
                'gpa_scale' => '4.0',
                
                // Essay
                'personal_statement' => 'I am passionate about computer science and technology...',
                
                // Status
                'status' => 'draft',
                'started_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            
            $this->command->info("✓ Created draft application for John Applicant");
        } else {
            $this->command->info("⚠ Draft application already exists for John Applicant");
        }
        
        // Check if submitted application exists
        $existingSubmitted = DB::table('admission_applications')
            ->where('user_id', $gradApplicant->user_id)
            ->where('status', 'submitted')
            ->first();
            
        if (!$existingSubmitted) {
            // Create submitted application for Graduate
            $appNumber = 'APP-2025-' . str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT);
            $appUuid = \Illuminate\Support\Str::uuid();
            
            $submittedAppId = DB::table('admission_applications')->insertGetId([
                'application_number' => $appNumber,
                'application_uuid' => $appUuid,
                'user_id' => $gradApplicant->user_id,
                'applicant_id' => $gradApplicant->id,
                'program_id' => $mbaProgramId,
                'term_id' => $currentTerm->id,
                
                // Personal Information
                'first_name' => 'Sarah',
                'last_name' => 'Graduate',
                'email' => $gradApplicant->email,
                'date_of_birth' => '1998-06-30',
                'gender' => 'female',
                'nationality' => 'American',
                'phone_primary' => '555-0789',
                'current_address' => '789 University Blvd, Boston, MA 02134',
                'permanent_address' => '789 University Blvd, Boston, MA 02134',
                'city' => 'Boston',
                'state_province' => 'MA',
                'postal_code' => '02134',
                'country' => 'USA',
                
                // Academic Information
                'application_type' => 'graduate',
                'entry_year' => 2025,
                'previous_institution' => 'Boston University',
                'previous_institution_country' => 'USA',
                'previous_institution_graduation_date' => '2021-05-15',
                'previous_degree' => 'Bachelor of Science',
                'previous_major' => 'Business Administration',
                'previous_gpa' => 3.6,
                'gpa_scale' => '4.0',
                
                // Test Scores
                'test_scores' => json_encode([
                    'GMAT' => ['score' => '680', 'date' => '2024-11-15']
                ]),
                
                // Essays
                'personal_statement' => 'I am passionate about pursuing an MBA...',
                'statement_of_purpose' => 'My goal is to become a strategic consultant...',
                
                // Work Experience
                'work_experience' => json_encode([
                    [
                        'company' => 'Tech Corp',
                        'position' => 'Business Analyst',
                        'start_date' => '2021-07',
                        'end_date' => '2024-12',
                        'description' => 'Led process improvement initiatives'
                    ]
                ]),
                
                // Status
                'status' => 'submitted',
                'application_fee_paid' => true,
                'application_fee_amount' => 75.00,
                'application_fee_date' => Carbon::now()->subDays(5),
                'started_at' => Carbon::now()->subDays(10),
                'submitted_at' => Carbon::now()->subDays(5),
                'created_at' => Carbon::now()->subDays(10),
                'updated_at' => Carbon::now()->subDays(5)
            ]);
            
            $this->command->info("✓ Created submitted application for Sarah Graduate");
            
            // Add sample documents only if we just created the application
            if (isset($submittedAppId)) {
                DB::table('application_documents')->insert([
                    [
                        'application_id' => $submittedAppId,
                        'document_type' => 'transcript',
                        'file_path' => 'documents/transcripts/grad_transcript.pdf',
                        'file_name' => 'Boston_University_Transcript.pdf',
                        'file_size' => 245678,
                        'is_verified' => true,
                        'verified_at' => Carbon::now()->subDays(3),
                        'verified_by' => 1,
                        'created_at' => Carbon::now()->subDays(5),
                        'updated_at' => Carbon::now()->subDays(3)
                    ],
                    [
                        'application_id' => $submittedAppId,
                        'document_type' => 'resume',
                        'file_path' => 'documents/resumes/grad_resume.pdf',
                        'file_name' => 'Sarah_Graduate_Resume.pdf',
                        'file_size' => 156789,
                        'is_verified' => false,
                        'created_at' => Carbon::now()->subDays(5),
                        'updated_at' => Carbon::now()->subDays(5)
                    ]
                ]);
                
                $this->command->info("✓ Added sample documents");
            }
        } else {
            $this->command->info("⚠ Submitted application already exists for Sarah Graduate");
        }
    }
    
    private function displayLoginCredentials()
    {
        $this->command->info("\n");
        $this->command->info("╔════════════════════════════════════════╗");
        $this->command->info("║   TEST APPLICANT ACCOUNTS READY! 🎉   ║");
        $this->command->info("╚════════════════════════════════════════╝");
        $this->command->info("");
        $this->command->info("📧 applicant@example.com / password");
        $this->command->info("   └─ Has DRAFT application (BS Computer Science)");
        $this->command->info("");
        $this->command->info("📧 jane.applicant@test.com / TestPass123");
        $this->command->info("   └─ Fresh account (no applications)");
        $this->command->info("");
        $this->command->info("📧 intl.applicant@test.com / TestPass123");
        $this->command->info("   └─ International applicant (no applications)");
        $this->command->info("");
        $this->command->info("📧 grad.applicant@test.com / TestPass123");
        $this->command->info("   └─ Has SUBMITTED application (MBA)");
        $this->command->info("");
        $this->command->info("🌐 Login at: http://localhost:8000/login");
        $this->command->info("📬 Check emails at: http://localhost:8025");
        $this->command->info("════════════════════════════════════════");
    }
}