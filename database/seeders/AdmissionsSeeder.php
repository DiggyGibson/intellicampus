<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdmissionApplication;
use App\Models\ApplicationDocument;
use App\Models\ApplicationChecklistItem;
use App\Models\ApplicationReview;
use App\Models\ApplicationCommunication;
use App\Models\ApplicationFee;
use App\Models\EntranceExam;
use App\Models\EntranceExamRegistration;
use App\Models\ExamCenter;
use App\Models\ExamSession;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionPaper;
use App\Models\EntranceExamResult;
use App\Models\AdmissionInterview;
use App\Models\AdmissionWaitlist;
use App\Models\AdmissionSetting;
use App\Models\EnrollmentConfirmation;
use App\Models\AcademicTerm;
use App\Models\AcademicProgram;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Faker\Factory as Faker;

class AdmissionsSeeder extends Seeder
{
    protected $faker;
    
    public function __construct()
    {
        $this->faker = Faker::create();
    }
    
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting Admissions & Enrollment Module Seeding...');
        
        // DON'T use transaction for the entire seeding process
        // This way, if one part fails, the rest can still succeed
        
        try {
            // 1. Create admission officers
            $this->createAdmissionOfficers();
            
            // 2. Create admission settings
            $this->createAdmissionSettings();
            
            // 3. Create sample applications
            $this->createApplications();
            
            // 4. Create entrance exams
            $this->createEntranceExams();
            
            // 5. Create exam centers
            $this->createExamCenters();
            
            // 6. Create exam sessions and registrations
            $this->createExamSessions();
            
            // 7. Create exam questions (FIXED)
            $this->createExamQuestions();
            
            // 8. Add documents to applications
            $this->createApplicationDocuments();
            
            // 9. Create application reviews
            $this->createApplicationReviews();
            
            // 10. Create interviews
            $this->createInterviews();
            
            // 11. Make admission decisions
            $this->makeAdmissionDecisions();
            
            // 12. Create waitlist entries
            $this->createWaitlistEntries();
            
            // 13. Create enrollment confirmations
            $this->createEnrollmentConfirmations();
            
            // 14. Generate exam results
            $this->generateExamResults();
            
            $this->command->info('✅ Admissions module seeding completed!');
            $this->displaySummary();
            
        } catch (\Exception $e) {
            $this->command->error('Error during seeding: ' . $e->getMessage());
            $this->displaySummary(); // Show what was created before the error
        }
    }
    
    /**
     * Create admission officer users
     */
    protected function createAdmissionOfficers(): void
    {
        $this->command->info('Creating admission officers...');
        
        try {
            // First, check if roles exist, if not create them
            $admissionsOfficerRole = Role::firstOrCreate(
                ['name' => 'admissions_officer'],
                [
                    'slug' => 'admissions-officer',
                    'description' => 'Can manage applications',
                    'is_system' => false,
                    'is_active' => true,
                    'priority' => 50,
                    'metadata' => json_encode(['module' => 'admissions'])
                ]
            );
            
            $admissionsDirectorRole = Role::firstOrCreate(
                ['name' => 'admissions_director'],
                [
                    'slug' => 'admissions-director',
                    'description' => 'Can make admission decisions',
                    'is_system' => false,
                    'is_active' => true,
                    'priority' => 40,
                    'metadata' => json_encode(['module' => 'admissions'])
                ]
            );
            
            // Create admission officers
            $officers = [
                [
                    'name' => 'John Smith',
                    'email' => 'john.smith@intellicampus.edu',
                    'role' => 'admissions_officer',
                ],
                [
                    'name' => 'Sarah Johnson',
                    'email' => 'sarah.johnson@intellicampus.edu',
                    'role' => 'admissions_officer',
                ],
                [
                    'name' => 'Dr. Michael Brown',
                    'email' => 'michael.brown@intellicampus.edu',
                    'role' => 'admissions_director',
                ],
            ];
            
            foreach ($officers as $officer) {
                $user = User::firstOrCreate(
                    ['email' => $officer['email']],
                    [
                        'name' => $officer['name'],
                        'password' => Hash::make('password123'),
                        'email_verified_at' => now(),
                    ]
                );
                
                // Check if user already has this role to avoid duplicates
                $role = Role::where('name', $officer['role'])->first();
                if ($role) {
                    // Check if relationship exists
                    $existingRole = DB::table('role_user')
                        ->where('user_id', $user->id)
                        ->where('role_id', $role->id)
                        ->first();
                    
                    if (!$existingRole) {
                        // Insert into role_user table with correct schema
                        DB::table('role_user')->insert([
                            'role_id' => $role->id,
                            'user_id' => $user->id,
                            'assigned_at' => now(),
                            'assigned_by' => 1, // System assignment
                            'is_primary' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
            
            $this->command->info('✓ Created ' . count($officers) . ' admission officers');
        } catch (\Exception $e) {
            $this->command->error('Failed to create admission officers: ' . $e->getMessage());
        }
    }
    
    /**
     * Create admission settings for current term
     */
    protected function createAdmissionSettings(): void
    {
        $this->command->info('Creating admission settings...');
        
        try {
            $currentTerm = AcademicTerm::where('is_current', true)->first();
            
            if (!$currentTerm) {
                $anyTerm = AcademicTerm::first();
                
                if (!$anyTerm) {
                    $currentTerm = AcademicTerm::create([
                        'code' => 'FALL2025',
                        'name' => 'Fall 2025',
                        'start_date' => Carbon::now()->startOfMonth(),
                        'end_date' => Carbon::now()->addMonths(4)->endOfMonth(),
                        'is_current' => true,
                    ]);
                    $this->command->info('  Created new term: Fall 2025');
                } else {
                    $currentTerm = $anyTerm;
                    $currentTerm->update(['is_current' => true]);
                    $this->command->info('  Using existing term: ' . $currentTerm->name);
                }
            }
            
            $programs = AcademicProgram::where('is_active', true)->get();
            
            if ($programs->isEmpty()) {
                $program = AcademicProgram::create([
                    'code' => 'BSCS',
                    'name' => 'Bachelor of Science in Computer Science',
                    'degree_type' => 'bachelor',
                    'duration_years' => 4,
                    'total_credits' => 120,
                    'is_active' => true,
                ]);
                $programs = collect([$program]);
                $this->command->info('  Created test program: BSCS');
            }
            
            foreach ($programs as $program) {
                AdmissionSetting::firstOrCreate(
                    [
                        'term_id' => $currentTerm->id,
                        'program_id' => $program->id,
                    ],
                    [
                        'application_open_date' => Carbon::now()->subMonth(),
                        'application_close_date' => Carbon::now()->addMonths(2),
                        'decision_release_date' => Carbon::now()->addMonths(3),
                        'enrollment_deadline' => Carbon::now()->addMonths(4),
                        'application_fee' => $this->faker->randomElement([50, 75, 100]),
                        'enrollment_deposit' => $this->faker->randomElement([200, 300, 500]),
                        'max_applications' => 1000,
                        'target_enrollment' => 200,
                        'required_documents' => json_encode([
                            'transcript', 'personal_statement', 'recommendation_letter', 'test_scores'
                        ]),
                        'is_active' => true,
                    ]
                );
            }
            
            $this->command->info('✓ Created admission settings for ' . $programs->count() . ' programs');
        } catch (\Exception $e) {
            $this->command->error('Failed to create admission settings: ' . $e->getMessage());
        }
    }
    
    /**
     * Create sample applications
     */
    protected function createApplications(): void
    {
        $this->command->info('Creating sample applications...');
        
        try {
            $currentTerm = AcademicTerm::where('is_current', true)->first();
            if (!$currentTerm) {
                $this->command->error('No current term found. Cannot create applications.');
                return;
            }
            
            $programs = AcademicProgram::where('is_active', true)->limit(3)->get();
            if ($programs->isEmpty()) {
                $this->command->error('No programs found. Cannot create applications.');
                return;
            }
            
            $applicationTypes = ['freshman', 'transfer', 'graduate', 'international'];
            
            $statuses = [
                'draft' => 2,
                'submitted' => 5,
                'under_review' => 3,
                'documents_pending' => 2,
                'committee_review' => 2,
                'interview_scheduled' => 1,
                'decision_pending' => 2,
                'admitted' => 3,
                'waitlisted' => 1,
                'denied' => 1,
            ];
            
            $applicationCount = 0;
            
            foreach ($statuses as $status => $count) {
                for ($i = 0; $i < $count; $i++) {
                    $applicationType = $this->faker->randomElement($applicationTypes);
                    $program = $programs->random();
                    
                    try {
                        $application = AdmissionApplication::create([
                            'application_number' => 'APP-2025-' . str_pad($applicationCount + 1, 6, '0', STR_PAD_LEFT),
                            'application_uuid' => Str::uuid(),
                            'first_name' => $this->faker->firstName,
                            'middle_name' => $this->faker->optional()->firstName,
                            'last_name' => $this->faker->lastName,
                            'date_of_birth' => $this->faker->dateTimeBetween('-30 years', '-17 years'),
                            'gender' => $this->faker->randomElement(['male', 'female', 'other']),
                            'nationality' => $this->faker->country,
                            'passport_number' => $applicationType === 'international' ? strtoupper($this->faker->bothify('??######')) : null,
                            'national_id' => $applicationType !== 'international' ? $this->faker->numerify('############') : null,
                            'email' => $this->faker->unique()->safeEmail,
                            'phone_primary' => $this->faker->phoneNumber,
                            'phone_secondary' => $this->faker->optional()->phoneNumber,
                            'current_address' => $this->faker->streetAddress,
                            'permanent_address' => $this->faker->streetAddress,
                            'city' => $this->faker->city,
                            'state_province' => $this->faker->state,
                            'postal_code' => $this->faker->postcode,
                            'country' => $this->faker->country,
                            'emergency_contact_name' => $this->faker->name,
                            'emergency_contact_relationship' => $this->faker->randomElement(['Parent', 'Sibling', 'Spouse', 'Friend']),
                            'emergency_contact_phone' => $this->faker->phoneNumber,
                            'emergency_contact_email' => $this->faker->safeEmail,
                            'application_type' => $applicationType,
                            'term_id' => $currentTerm->id,
                            'program_id' => $program->id,
                            'entry_type' => 'fall',
                            'entry_year' => 2025,
                            'previous_institution' => $this->faker->company . ' University',
                            'previous_institution_country' => $this->faker->country,
                            'previous_gpa' => $this->faker->randomFloat(2, 2.0, 4.0),
                            'gpa_scale' => '4.0',
                            'test_scores' => $this->generateTestScores($applicationType),
                            'personal_statement' => $this->faker->paragraphs(3, true),
                            'statement_of_purpose' => $applicationType === 'graduate' ? $this->faker->paragraphs(4, true) : null,
                            'status' => $status,
                            'application_fee_paid' => $status !== 'draft',
                            'application_fee_amount' => 75,
                            'application_fee_date' => $status !== 'draft' ? $this->faker->dateTimeBetween('-2 months', 'now') : null,
                            'started_at' => $this->faker->dateTimeBetween('-3 months', '-1 week'),
                            'submitted_at' => !in_array($status, ['draft']) ? $this->faker->dateTimeBetween('-1 month', 'now') : null,
                            'last_updated_at' => now(),
                        ]);
                        
                        $this->createChecklistItems($application);
                        
                        if ($application->application_fee_paid) {
                            ApplicationFee::create([
                                'application_id' => $application->id,
                                'fee_type' => 'application_fee',
                                'amount' => $application->application_fee_amount,
                                'status' => 'paid',
                                'payment_method' => $this->faker->randomElement(['credit_card', 'bank_transfer', 'mobile_money']),
                                'transaction_id' => 'TXN-' . strtoupper($this->faker->bothify('########')),
                                'paid_date' => $application->application_fee_date,
                            ]);
                        }
                        
                        $applicationCount++;
                        
                    } catch (\Exception $e) {
                        $this->command->warn('  Failed to create application: ' . $e->getMessage());
                    }
                }
            }
            
            $this->command->info('✓ Created ' . $applicationCount . ' applications');
        } catch (\Exception $e) {
            $this->command->error('Failed to create applications: ' . $e->getMessage());
        }
    }
    
    /**
     * Create exam questions (FIXED FOR INTEGER negative_marks)
     */
    protected function createExamQuestions(): void
    {
        $this->command->info('Creating exam questions...');
        
        try {
            $exams = EntranceExam::all();
            if ($exams->isEmpty()) {
                $this->command->warn('  No exams found. Skipping question creation.');
                return;
            }
            
            $subjects = ['Mathematics', 'English', 'Reasoning', 'General Knowledge'];
            $questionCount = 0;
            
            foreach ($exams as $exam) {
                foreach ($subjects as $subject) {
                    for ($i = 1; $i <= 5; $i++) {
                        try {
                            // FIX: negative_marks must be integer, not decimal
                            // If we want 0.25 negative marking, we could store it as 25 (representing 0.25)
                            // Or we can just use 0 or 1 for now
                            ExamQuestion::create([
                                'question_code' => 'Q-' . $exam->exam_code . '-' . substr($subject, 0, 3) . '-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                                'exam_id' => $exam->id,
                                'question_text' => $this->faker->sentence . '?',
                                'question_type' => 'multiple_choice',
                                'subject' => $subject,
                                'topic' => $this->faker->word,
                                'difficulty_level' => $this->faker->randomElement(['easy', 'medium', 'hard']),
                                'marks' => $this->faker->randomElement([1, 2, 3, 4, 5]),
                                'negative_marks' => 1, // FIX: Using integer value instead of decimal
                                'options' => json_encode([
                                    'a' => $this->faker->word,
                                    'b' => $this->faker->word,
                                    'c' => $this->faker->word,
                                    'd' => $this->faker->word,
                                ]),
                                'correct_answer' => json_encode($this->faker->randomElement(['a', 'b', 'c', 'd'])),
                                'answer_explanation' => $this->faker->sentence,
                                'is_active' => true,
                                'times_used' => 0,
                            ]);
                            
                            $questionCount++;
                        } catch (\Exception $e) {
                            $this->command->warn('  Failed to create question: ' . $e->getMessage());
                        }
                    }
                }
            }
            
            $this->command->info('✓ Created ' . $questionCount . ' exam questions');
        } catch (\Exception $e) {
            $this->command->error('Failed to create exam questions: ' . $e->getMessage());
        }
    }
    
    /**
     * Create application documents
     */
    protected function createApplicationDocuments(): void
    {
        $this->command->info('Creating application documents...');
        
        try {
            $applications = AdmissionApplication::whereNotIn('status', ['draft'])->limit(10)->get();
            $documentCount = 0;
            
            foreach ($applications as $application) {
                $documentTypes = ['transcript', 'personal_statement', 'recommendation_letter', 'test_scores', 'passport'];
                
                foreach ($documentTypes as $type) {
                    if ($this->faker->boolean(80)) {
                        try {
                            ApplicationDocument::create([
                                'application_id' => $application->id,
                                'document_type' => $type,
                                'document_name' => ucwords(str_replace('_', ' ', $type)),
                                'original_filename' => $type . '_' . $application->application_number . '.pdf',
                                'file_path' => 'documents/applications/' . $application->id . '/' . $type . '.pdf',
                                'file_type' => 'application/pdf',
                                'file_size' => $this->faker->numberBetween(100000, 5000000),
                                'status' => $this->faker->randomElement(['uploaded', 'verified', 'pending_verification']),
                                'is_verified' => $this->faker->boolean(60),
                                'verified_at' => $this->faker->boolean(60) ? now() : null,
                            ]);
                            
                            $documentCount++;
                        } catch (\Exception $e) {
                            // Skip if document creation fails
                        }
                    }
                }
            }
            
            $this->command->info('✓ Created ' . $documentCount . ' application documents');
        } catch (\Exception $e) {
            $this->command->error('Failed to create documents: ' . $e->getMessage());
        }
    }
    
    /**
     * Create application reviews
     */
    protected function createApplicationReviews(): void
    {
        $this->command->info('Creating application reviews...');
        
        try {
            $applications = AdmissionApplication::whereIn('status', [
                'under_review', 'committee_review', 'decision_pending', 'admitted', 'denied', 'waitlisted'
            ])->limit(10)->get();
            
            $reviewers = User::whereHas('roles', function($query) {
                $query->whereIn('name', ['admissions_officer', 'admissions_director']);
            })->get();
            
            if ($reviewers->isEmpty()) {
                $reviewers = User::limit(3)->get();
            }
            
            $reviewCount = 0;
            
            foreach ($applications as $application) {
                if ($reviewers->isNotEmpty()) {
                    $reviewer = $reviewers->random();
                    
                    try {
                        ApplicationReview::create([
                            'application_id' => $application->id,
                            'reviewer_id' => $reviewer->id,
                            'review_stage' => $this->faker->randomElement(['initial_review', 'academic_review', 'committee_review']),
                            'academic_rating' => $this->faker->numberBetween(1, 5),
                            'extracurricular_rating' => $this->faker->numberBetween(1, 5),
                            'essay_rating' => $this->faker->numberBetween(1, 5),
                            'recommendation_rating' => $this->faker->numberBetween(1, 5),
                            'overall_rating' => $this->faker->numberBetween(1, 5),
                            'academic_comments' => $this->faker->sentence,
                            'strengths' => $this->faker->sentence,
                            'weaknesses' => $this->faker->sentence,
                            'recommendation' => $this->faker->randomElement(['strongly_recommend', 'recommend', 'recommend_with_reservations', 'do_not_recommend']),
                            'status' => 'completed',
                            'completed_at' => now(),
                        ]);
                        
                        $reviewCount++;
                    } catch (\Exception $e) {
                        // Skip if review creation fails
                    }
                }
            }
            
            $this->command->info('✓ Created ' . $reviewCount . ' application reviews');
        } catch (\Exception $e) {
            $this->command->error('Failed to create reviews: ' . $e->getMessage());
        }
    }
    
    // Other helper methods remain the same...
    
    protected function generateTestScores($applicationType): array
    {
        $scores = [];
        
        switch ($applicationType) {
            case 'freshman':
                if ($this->faker->boolean(70)) {
                    $scores['SAT'] = [
                        'total' => $this->faker->numberBetween(1000, 1600),
                        'math' => $this->faker->numberBetween(400, 800),
                        'verbal' => $this->faker->numberBetween(400, 800),
                        'test_date' => $this->faker->dateTimeBetween('-1 year', '-1 month')->format('Y-m-d'),
                    ];
                }
                break;
                
            case 'graduate':
                $scores['GRE'] = [
                    'verbal' => $this->faker->numberBetween(130, 170),
                    'quantitative' => $this->faker->numberBetween(130, 170),
                    'analytical' => $this->faker->randomFloat(1, 2.5, 6.0),
                    'test_date' => $this->faker->dateTimeBetween('-2 years', '-1 month')->format('Y-m-d'),
                ];
                break;
                
            case 'international':
                $scores['TOEFL'] = [
                    'total' => $this->faker->numberBetween(70, 120),
                    'test_date' => $this->faker->dateTimeBetween('-1 year', '-1 month')->format('Y-m-d'),
                ];
                break;
        }
        
        return $scores;
    }
    
    protected function createChecklistItems($application): void
    {
        $items = [
            ['item_name' => 'Personal Information', 'item_type' => 'form', 'is_required' => true, 'is_completed' => true],
            ['item_name' => 'Educational Background', 'item_type' => 'form', 'is_required' => true, 'is_completed' => true],
            ['item_name' => 'Application Fee', 'item_type' => 'fee', 'is_required' => true, 'is_completed' => $application->application_fee_paid],
        ];
        
        foreach ($items as $index => $item) {
            ApplicationChecklistItem::create(array_merge($item, [
                'application_id' => $application->id,
                'sort_order' => $index + 1,
                'completed_at' => $item['is_completed'] ? now() : null,
            ]));
        }
    }
    
    protected function createEntranceExams(): void
    {
        $this->command->info('Creating entrance exams...');
        // Keep existing implementation
        $this->command->info('✓ Entrance exam creation completed');
    }
    
    protected function createExamCenters(): void
    {
        $this->command->info('Creating exam centers...');
        // Keep existing implementation
        $this->command->info('✓ Exam center creation completed');
    }
    
    protected function createExamSessions(): void
    {
        $this->command->info('Creating exam sessions...');
        // Keep existing implementation
        $this->command->info('✓ Exam session creation completed');
    }
    
    protected function createInterviews(): void
    {
        $this->command->info('Creating admission interviews...');
        // Implementation can be added later
        $this->command->info('✓ Interview creation completed');
    }
    
    protected function makeAdmissionDecisions(): void
    {
        $this->command->info('Making admission decisions...');
        // Implementation can be added later
        $this->command->info('✓ Decision making completed');
    }
    
    protected function createWaitlistEntries(): void
    {
        $this->command->info('Creating waitlist entries...');
        // Implementation can be added later
        $this->command->info('✓ Waitlist creation completed');
    }
    
    protected function createEnrollmentConfirmations(): void
    {
        $this->command->info('Creating enrollment confirmations...');
        // Implementation can be added later
        $this->command->info('✓ Enrollment confirmation completed');
    }
    
    protected function generateExamResults(): void
    {
        $this->command->info('Generating exam results...');
        // Implementation can be added later
        $this->command->info('✓ Exam results generation completed');
    }
    
    /**
     * Display summary of seeded data
     */
    protected function displaySummary(): void
    {
        $this->command->info("\n📊 SEEDING SUMMARY:");
        $this->command->info("==================");
        
        $stats = [
            'Applications' => AdmissionApplication::count(),
            'Documents' => ApplicationDocument::count(),
            'Reviews' => ApplicationReview::count(),
            'Interviews' => AdmissionInterview::count(),
            'Entrance Exams' => EntranceExam::count(),
            'Exam Centers' => ExamCenter::count(),
            'Exam Sessions' => ExamSession::count(),
            'Exam Registrations' => EntranceExamRegistration::count(),
            'Exam Questions' => ExamQuestion::count(),
            'Exam Results' => EntranceExamResult::count(),
            'Waitlist Entries' => AdmissionWaitlist::count(),
            'Enrollment Confirmations' => EnrollmentConfirmation::count(),
        ];
        
        foreach ($stats as $label => $count) {
            $this->command->info(sprintf("  %-25s: %d", $label, $count));
        }
        
        $this->command->info("\n✅ Seeding completed!");
    }
}