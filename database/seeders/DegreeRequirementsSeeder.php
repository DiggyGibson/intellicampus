<?php
// Save as: backend/database/seeders/DegreeRequirementsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RequirementCategory;
use App\Models\DegreeRequirement;
use App\Models\ProgramRequirement;
use App\Models\AcademicProgram;
use App\Models\Course;
use Illuminate\Support\Facades\DB;

class DegreeRequirementsSeeder extends Seeder
{
    /**
     * Run the database seeds to populate degree requirements
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Create requirement categories
            $categories = $this->createCategories();
            
            // Create degree requirements for each category
            $requirements = $this->createDegreeRequirements($categories);
            
            // Get or create a Computer Science program for testing
            $csProgram = $this->getOrCreateCSProgram();
            
            // Assign requirements to the CS program
            $this->assignRequirementsToProgram($csProgram, $requirements);
            
            // Map courses to requirements (if courses exist)
            $this->mapCoursesToRequirements($requirements);
            
            echo "Degree requirements seeded successfully!\n";
        });
    }

    /**
     * Create requirement categories
     */
    protected function createCategories(): array
    {
        $categoriesData = [
            [
                'code' => 'GEN_ED',
                'name' => 'General Education',
                'description' => 'University-wide general education requirements',
                'type' => 'general_education',
                'display_order' => 1
            ],
            [
                'code' => 'MAJOR_CORE',
                'name' => 'Major Core Requirements',
                'description' => 'Core courses required for the major',
                'type' => 'major',
                'display_order' => 2
            ],
            [
                'code' => 'MAJOR_ELEC',
                'name' => 'Major Electives',
                'description' => 'Elective courses within the major',
                'type' => 'major',
                'display_order' => 3
            ],
            [
                'code' => 'MINOR',
                'name' => 'Minor Requirements',
                'description' => 'Requirements for minor programs',
                'type' => 'minor',
                'display_order' => 4
            ],
            [
                'code' => 'FREE_ELEC',
                'name' => 'Free Electives',
                'description' => 'Open elective credits',
                'type' => 'elective',
                'display_order' => 5
            ],
            [
                'code' => 'UNIVERSITY',
                'name' => 'University Requirements',
                'description' => 'University-wide requirements',
                'type' => 'university',
                'display_order' => 6
            ]
        ];

        $categories = [];
        foreach ($categoriesData as $data) {
            $category = RequirementCategory::firstOrCreate(
                ['code' => $data['code']],
                $data
            );
            $categories[$data['code']] = $category;
            echo "Created/Found category: {$category->name}\n";
        }

        return $categories;
    }

    /**
     * Create degree requirements
     */
    protected function createDegreeRequirements(array $categories): array
    {
        $requirements = [];

        // General Education Requirements
        $requirements['ge_english'] = DegreeRequirement::firstOrCreate(
            ['code' => 'GE_ENGLISH'],
            [
                'category_id' => $categories['GEN_ED']->id,
                'name' => 'English Composition',
                'description' => 'English composition and writing skills',
                'requirement_type' => 'specific_courses',
                'parameters' => json_encode([
                    'required_courses' => ['ENG101', 'ENG102'],
                    'min_grade' => 'C'
                ]),
                'display_order' => 1,
                'is_required' => true
            ]
        );
        echo "Created/Found requirement: {$requirements['ge_english']->name}\n";

        $requirements['ge_math'] = DegreeRequirement::firstOrCreate(
            ['code' => 'GE_MATH'],
            [
                'category_id' => $categories['GEN_ED']->id,
                'name' => 'Mathematics',
                'description' => 'College-level mathematics',
                'requirement_type' => 'course_list',
                'parameters' => json_encode([
                    'choose_from' => ['MATH151', 'MATH161', 'MATH171'],
                    'min_to_choose' => 1,
                    'min_grade' => 'C'
                ]),
                'display_order' => 2,
                'is_required' => true
            ]
        );
        echo "Created/Found requirement: {$requirements['ge_math']->name}\n";

        $requirements['ge_science'] = DegreeRequirement::firstOrCreate(
            ['code' => 'GE_SCIENCE'],
            [
                'category_id' => $categories['GEN_ED']->id,
                'name' => 'Natural Sciences',
                'description' => 'Natural science courses with lab',
                'requirement_type' => 'credit_hours',
                'parameters' => json_encode([
                    'min_credits' => 8,
                    'min_courses' => 2,
                    'must_include_lab' => true,
                    'min_grade' => 'D'
                ]),
                'display_order' => 3,
                'is_required' => true
            ]
        );
        echo "Created/Found requirement: {$requirements['ge_science']->name}\n";

        $requirements['ge_humanities'] = DegreeRequirement::firstOrCreate(
            ['code' => 'GE_HUMANITIES'],
            [
                'category_id' => $categories['GEN_ED']->id,
                'name' => 'Humanities',
                'description' => 'Humanities and arts courses',
                'requirement_type' => 'credit_hours',
                'parameters' => json_encode([
                    'min_credits' => 9,
                    'min_courses' => 3,
                    'min_grade' => 'D'
                ]),
                'display_order' => 4,
                'is_required' => true
            ]
        );
        echo "Created/Found requirement: {$requirements['ge_humanities']->name}\n";

        $requirements['ge_social'] = DegreeRequirement::firstOrCreate(
            ['code' => 'GE_SOCIAL'],
            [
                'category_id' => $categories['GEN_ED']->id,
                'name' => 'Social Sciences',
                'description' => 'Social science courses',
                'requirement_type' => 'credit_hours',
                'parameters' => json_encode([
                    'min_credits' => 9,
                    'min_courses' => 3,
                    'min_grade' => 'D'
                ]),
                'display_order' => 5,
                'is_required' => true
            ]
        );
        echo "Created/Found requirement: {$requirements['ge_social']->name}\n";

        // Major Core Requirements (Computer Science)
        $requirements['cs_intro'] = DegreeRequirement::firstOrCreate(
            ['code' => 'CS_INTRO'],
            [
                'category_id' => $categories['MAJOR_CORE']->id,
                'name' => 'Introduction to Computer Science',
                'description' => 'Introductory CS sequence',
                'requirement_type' => 'specific_courses',
                'parameters' => json_encode([
                    'required_courses' => ['CS101', 'CS102'],
                    'min_grade' => 'C'
                ]),
                'display_order' => 1,
                'is_required' => true
            ]
        );
        echo "Created/Found requirement: {$requirements['cs_intro']->name}\n";

        $requirements['cs_programming'] = DegreeRequirement::firstOrCreate(
            ['code' => 'CS_PROGRAMMING'],
            [
                'category_id' => $categories['MAJOR_CORE']->id,
                'name' => 'Programming Fundamentals',
                'description' => 'Core programming courses',
                'requirement_type' => 'specific_courses',
                'parameters' => json_encode([
                    'required_courses' => ['CS201', 'CS202', 'CS203'],
                    'min_grade' => 'C'
                ]),
                'display_order' => 2,
                'is_required' => true
            ]
        );
        echo "Created/Found requirement: {$requirements['cs_programming']->name}\n";

        $requirements['cs_systems'] = DegreeRequirement::firstOrCreate(
            ['code' => 'CS_SYSTEMS'],
            [
                'category_id' => $categories['MAJOR_CORE']->id,
                'name' => 'Computer Systems',
                'description' => 'Computer architecture and operating systems',
                'requirement_type' => 'specific_courses',
                'parameters' => json_encode([
                    'required_courses' => ['CS301', 'CS302'],
                    'min_grade' => 'C'
                ]),
                'display_order' => 3,
                'is_required' => true
            ]
        );
        echo "Created/Found requirement: {$requirements['cs_systems']->name}\n";

        $requirements['cs_theory'] = DegreeRequirement::firstOrCreate(
            ['code' => 'CS_THEORY'],
            [
                'category_id' => $categories['MAJOR_CORE']->id,
                'name' => 'Theoretical Computer Science',
                'description' => 'Algorithms and theory courses',
                'requirement_type' => 'specific_courses',
                'parameters' => json_encode([
                    'required_courses' => ['CS311', 'CS312'],
                    'min_grade' => 'C'
                ]),
                'display_order' => 4,
                'is_required' => true
            ]
        );
        echo "Created/Found requirement: {$requirements['cs_theory']->name}\n";

        $requirements['cs_capstone'] = DegreeRequirement::firstOrCreate(
            ['code' => 'CS_CAPSTONE'],
            [
                'category_id' => $categories['MAJOR_CORE']->id,
                'name' => 'Senior Capstone',
                'description' => 'Senior capstone project',
                'requirement_type' => 'specific_courses',
                'parameters' => json_encode([
                    'required_courses' => ['CS490'],
                    'min_grade' => 'C'
                ]),
                'display_order' => 5,
                'is_required' => true
            ]
        );
        echo "Created/Found requirement: {$requirements['cs_capstone']->name}\n";

        // Major Electives
        $requirements['cs_electives'] = DegreeRequirement::firstOrCreate(
            ['code' => 'CS_ELECTIVES'],
            [
                'category_id' => $categories['MAJOR_ELEC']->id,
                'name' => 'Computer Science Electives',
                'description' => 'Upper-level CS elective courses',
                'requirement_type' => 'credit_hours',
                'parameters' => json_encode([
                    'min_credits' => 12,
                    'min_courses' => 4,
                    'course_level_min' => 300,
                    'min_grade' => 'C'
                ]),
                'display_order' => 1,
                'is_required' => true
            ]
        );
        echo "Created/Found requirement: {$requirements['cs_electives']->name}\n";

        // Free Electives
        $requirements['free_electives'] = DegreeRequirement::firstOrCreate(
            ['code' => 'FREE_ELECTIVES'],
            [
                'category_id' => $categories['FREE_ELEC']->id,
                'name' => 'Free Electives',
                'description' => 'Additional elective credits',
                'requirement_type' => 'credit_hours',
                'parameters' => json_encode([
                    'min_credits' => 15,
                    'allow_pass_fail' => true
                ]),
                'display_order' => 1,
                'is_required' => true
            ]
        );
        echo "Created/Found requirement: {$requirements['free_electives']->name}\n";

        // University Requirements
        $requirements['total_credits'] = DegreeRequirement::firstOrCreate(
            ['code' => 'TOTAL_CREDITS'],
            [
                'category_id' => $categories['UNIVERSITY']->id,
                'name' => 'Total Credit Hours',
                'description' => 'Minimum total credits for graduation',
                'requirement_type' => 'credit_hours',
                'parameters' => json_encode([
                    'min_credits' => 120
                ]),
                'display_order' => 1,
                'is_required' => true
            ]
        );
        echo "Created/Found requirement: {$requirements['total_credits']->name}\n";

        $requirements['gpa_cumulative'] = DegreeRequirement::firstOrCreate(
            ['code' => 'GPA_CUMULATIVE'],
            [
                'category_id' => $categories['UNIVERSITY']->id,
                'name' => 'Cumulative GPA',
                'description' => 'Minimum cumulative GPA',
                'requirement_type' => 'gpa',
                'parameters' => json_encode([
                    'min_gpa' => 2.0
                ]),
                'display_order' => 2,
                'is_required' => true
            ]
        );
        echo "Created/Found requirement: {$requirements['gpa_cumulative']->name}\n";

        $requirements['gpa_major'] = DegreeRequirement::firstOrCreate(
            ['code' => 'GPA_MAJOR'],
            [
                'category_id' => $categories['UNIVERSITY']->id,
                'name' => 'Major GPA',
                'description' => 'Minimum GPA in major courses',
                'requirement_type' => 'gpa',
                'parameters' => json_encode([
                    'min_gpa' => 2.0,
                    'apply_to' => 'major'
                ]),
                'display_order' => 3,
                'is_required' => true
            ]
        );
        echo "Created/Found requirement: {$requirements['gpa_major']->name}\n";

        $requirements['residency'] = DegreeRequirement::firstOrCreate(
            ['code' => 'RESIDENCY'],
            [
                'category_id' => $categories['UNIVERSITY']->id,
                'name' => 'Residency Requirement',
                'description' => 'Minimum credits at this institution',
                'requirement_type' => 'residency',
                'parameters' => json_encode([
                    'min_credits' => 30,
                    'of_last_credits' => 60
                ]),
                'display_order' => 4,
                'is_required' => true
            ]
        );
        echo "Created/Found requirement: {$requirements['residency']->name}\n";

        return $requirements;
    }

    /**
     * Get or create Computer Science program
     */
    protected function getOrCreateCSProgram(): AcademicProgram
    {
        // First try to find existing CS program
        $program = AcademicProgram::where('code', 'BSCS')
            ->orWhere('name', 'LIKE', '%Computer Science%')
            ->first();
            
        if ($program) {
            echo "Found existing CS program: {$program->name}\n";
            return $program;
        }
        
        // Create new program if not found
        $program = AcademicProgram::create([
            'code' => 'BSCS',
            'name' => 'Bachelor of Science in Computer Science',
            'level' => 'bachelor',
            'department' => 'Computer Science',
            'faculty' => 'Engineering and Technology',
            'duration_years' => 4,
            'total_credits' => 120,
            'core_credits' => 45,
            'major_credits' => 45,
            'elective_credits' => 30,
            'min_gpa' => 2.00,
            'graduation_gpa' => 2.00,
            'is_active' => true
        ]);
        
        echo "Created new CS program: {$program->name}\n";
        return $program;
    }

    /**
     * Assign requirements to program
     */
    protected function assignRequirementsToProgram(AcademicProgram $program, array $requirements): void
    {
        $catalogYear = date('Y') . '-' . (date('Y') + 1);
        echo "Assigning requirements to {$program->name} for catalog year {$catalogYear}\n";

        foreach ($requirements as $code => $requirement) {
            // Determine credits based on requirement parameters
            $params = json_decode($requirement->parameters, true);
            $creditsRequired = null;
            $coursesRequired = null;

            if ($requirement->requirement_type === 'credit_hours') {
                $creditsRequired = $params['min_credits'] ?? null;
            } elseif ($requirement->requirement_type === 'course_count') {
                $coursesRequired = $params['min_courses'] ?? null;
            } elseif ($requirement->requirement_type === 'specific_courses') {
                $coursesRequired = count($params['required_courses'] ?? []);
                // Estimate credits (3 per course as default)
                $creditsRequired = $coursesRequired * 3;
            }

            ProgramRequirement::firstOrCreate(
                [
                    'program_id' => $program->id,
                    'requirement_id' => $requirement->id,
                    'catalog_year' => $catalogYear
                ],
                [
                    'credits_required' => $creditsRequired,
                    'courses_required' => $coursesRequired,
                    'applies_to' => 'all',
                    'is_active' => true
                ]
            );
            echo "  - Assigned: {$requirement->name}\n";
        }
    }

    /**
     * Map courses to requirements (simplified example)
     * FIXED: Using 'code' column instead of 'course_code'
     */
    protected function mapCoursesToRequirements(array $requirements): void
    {
        echo "\nMapping courses to requirements...\n";
        
        // Check if we have any courses in the database
        $courseCount = Course::count();
        if ($courseCount == 0) {
            echo "No courses found in database. Skipping course mapping.\n";
            echo "You can map courses later when they are created.\n";
            return;
        }
        
        // Example: Map some courses if they exist
        foreach ($requirements as $requirement) {
            $params = json_decode($requirement->parameters, true);
            
            if ($requirement->requirement_type === 'specific_courses' && isset($params['required_courses'])) {
                foreach ($params['required_courses'] as $courseCode) {
                    // FIXED: Using 'code' column instead of 'course_code'
                    $course = Course::where('code', $courseCode)->first();
                    
                    if ($course) {
                        // Create mapping if course exists
                        DB::table('course_requirement_mappings')->insertOrIgnore([
                            'course_id' => $course->id,
                            'requirement_id' => $requirement->id,
                            'fulfillment_type' => 'full',
                            'min_grade' => $params['min_grade'] ?? null,
                            'is_active' => true,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                        echo "  - Mapped {$courseCode} to {$requirement->name}\n";
                    } else {
                        // Course doesn't exist yet - that's okay
                        echo "  - Course {$courseCode} not found (will map when created)\n";
                    }
                }
            }
        }
        
        echo "Course mapping complete.\n";
    }
}