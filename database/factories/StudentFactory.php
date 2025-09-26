<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Student;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Student::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $isInternational = $this->faker->boolean(20); // 20% international students
        $academicLevel = $this->faker->randomElement(['freshman', 'sophomore', 'junior', 'senior', 'graduate']);
        $enrollmentStatus = $this->faker->randomElement(['active', 'active', 'active', 'inactive', 'graduated']); // More active students
        
        // Calculate credits based on academic level
        $creditsRequired = 120;
        $creditsEarned = match($academicLevel) {
            'freshman' => $this->faker->numberBetween(0, 30),
            'sophomore' => $this->faker->numberBetween(31, 60),
            'junior' => $this->faker->numberBetween(61, 90),
            'senior' => $this->faker->numberBetween(91, 119),
            'graduate' => $this->faker->numberBetween(30, 60),
            default => 0
        };
        $creditsCompleted = $creditsEarned; // For simplicity, make them equal

        // GPA calculation
        $currentGpa = $this->faker->randomFloat(2, 2.0, 4.0);
        $cumulativeGpa = $this->faker->randomFloat(2, max(2.0, $currentGpa - 0.3), min(4.0, $currentGpa + 0.3));
        
        // Academic standing based on GPA
        $academicStanding = match(true) {
            $cumulativeGpa >= 2.0 => 'good',
            $cumulativeGpa >= 1.5 => 'probation',
            default => 'suspension'
        };

        // Departments and their related programs/majors
        $departments = [
            'Computer Science' => ['Computer Science', 'Software Engineering', 'Data Science', 'Cybersecurity'],
            'Business' => ['Business Administration', 'Finance', 'Marketing', 'Accounting'],
            'Engineering' => ['Mechanical Engineering', 'Civil Engineering', 'Electrical Engineering', 'Chemical Engineering'],
            'Medical Sciences' => ['Medicine', 'Nursing', 'Pharmacy', 'Biology'],
            'Law' => ['Law', 'Criminal Justice', 'Political Science'],
            'Liberal Arts' => ['English', 'History', 'Philosophy', 'Psychology'],
            'Education' => ['Elementary Education', 'Secondary Education', 'Special Education'],
        ];
        
        $department = $this->faker->randomElement(array_keys($departments));
        $programName = $this->faker->randomElement($departments[$department]);

        // Generate Student ID with proper format YYXXXXXX
        $year = $enrollmentStatus === 'graduated' ? '23' : '24';
        $sequence = str_pad($this->faker->unique()->numberBetween(6, 9999), 6, '0', STR_PAD_LEFT);
        $studentId = $year . $sequence;

        return [
            // System Fields
            'student_id' => $studentId,
            
            // Personal Information
            'first_name' => $this->faker->firstName(),
            'middle_name' => $this->faker->optional(0.7)->firstName(), // 70% have middle names
            'last_name' => $this->faker->lastName(),
            'preferred_name' => $this->faker->optional(0.2)->firstName(), // 20% use preferred names
            'email' => $this->faker->unique()->safeEmail(),
            'secondary_email' => $this->faker->optional(0.3)->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'home_phone' => $this->faker->optional(0.5)->phoneNumber(),
            'work_phone' => $this->faker->optional(0.1)->phoneNumber(), // Few students have work phones
            'date_of_birth' => $this->faker->dateTimeBetween('-30 years', '-18 years'),
            'place_of_birth' => $this->faker->city() . ', ' . $this->faker->country(),
            'gender' => $this->faker->randomElement(['male', 'female', 'other']),
            'marital_status' => $this->faker->randomElement(['single', 'single', 'single', 'married']), // Most students are single
            'religion' => $this->faker->optional(0.6)->randomElement(['Christianity', 'Islam', 'Hinduism', 'Buddhism', 'Judaism', 'Other']),
            'ethnicity' => $this->faker->optional(0.5)->randomElement(['African', 'Asian', 'Caucasian', 'Hispanic', 'Middle Eastern', 'Mixed', 'Other']),
            'nationality' => $this->faker->country(),
            'national_id_number' => $this->faker->numerify('##########'),
            
            // Addresses
            'address' => $this->faker->streetAddress() . ', ' . $this->faker->city() . ', ' . $this->faker->stateAbbr() . ' ' . $this->faker->postcode(),
            'permanent_address' => $this->faker->streetAddress() . ', ' . $this->faker->city() . ', ' . $this->faker->stateAbbr() . ' ' . $this->faker->postcode(),
            
            // Academic Information
            'program_name' => $programName,
            'program_id' => $this->faker->numberBetween(1, 50),
            'department' => $department,
            'major' => $programName,
            'minor' => $this->faker->optional(0.3)->randomElement(['Mathematics', 'Psychology', 'Economics', 'Philosophy', 'Languages']),
            'academic_level' => $academicLevel,
            'enrollment_status' => $enrollmentStatus,
            'academic_standing' => $academicStanding,
            'admission_status' => $enrollmentStatus === 'graduated' ? 'enrolled' : 'enrolled',
            'admission_date' => $this->faker->dateTimeBetween('-5 years', '-6 months'),
            'expected_graduation_year' => $enrollmentStatus === 'graduated' ? 2023 : $this->faker->numberBetween(2024, 2028),
            'graduation_date' => $enrollmentStatus === 'graduated' ? $this->faker->dateTimeBetween('-1 year', '-1 month') : null,
            'degree_awarded' => $enrollmentStatus === 'graduated' ? "Bachelor of Science in {$programName}" : null,
            'current_gpa' => $currentGpa,
            'cumulative_gpa' => $cumulativeGpa,
            'credits_earned' => $creditsEarned,
            'credits_completed' => $creditsCompleted,
            'credits_required' => $creditsRequired,
            
            // Previous Education
            'high_school_name' => $this->faker->company() . ' High School',
            'high_school_graduation_year' => $this->faker->numberBetween(2018, 2023),
            'high_school_gpa' => $this->faker->randomFloat(2, 2.5, 4.0),
            'previous_university' => $this->faker->optional(0.1)->company() . ' University', // 10% are transfer students
            'transfer_credits_info' => null,
            'previous_education' => null,
            
            // Advisory
            'advisor_name' => 'Dr. ' . $this->faker->name(),
            
            // Guardian/Emergency Contacts
            'guardian_name' => $this->faker->name(),
            'guardian_phone' => $this->faker->phoneNumber(),
            'guardian_email' => $this->faker->safeEmail(),
            'emergency_contact_name' => $this->faker->name(),
            'emergency_contact_phone' => $this->faker->phoneNumber(),
            'next_of_kin_name' => $this->faker->name(),
            'next_of_kin_relationship' => $this->faker->randomElement(['Parent', 'Mother', 'Father', 'Sibling', 'Spouse', 'Guardian']),
            'next_of_kin_phone' => $this->faker->phoneNumber(),
            
            // Medical Information
            'blood_group' => $this->faker->randomElement(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-']),
            'medical_conditions' => $this->faker->optional(0.2)->sentence(), // 20% have medical conditions
            'insurance_provider' => $this->faker->optional(0.8)->company() . ' Insurance',
            'insurance_policy_number' => $this->faker->optional(0.8)->numerify('POL#######'),
            'insurance_expiry' => $this->faker->optional(0.8)->dateTimeBetween('now', '+2 years'),
            
            // Document Flags
            'has_profile_photo' => $this->faker->boolean(80), // 80% have uploaded photo
            'has_national_id_copy' => $this->faker->boolean(90),
            'has_high_school_certificate' => $this->faker->boolean(95),
            'has_high_school_transcript' => $this->faker->boolean(95),
            'has_immunization_records' => $this->faker->boolean(85),
            
            // International Student
            'is_international' => $isInternational,
            'passport_number' => $isInternational ? strtoupper($this->faker->bothify('??#######')) : null,
            'visa_status' => $isInternational ? $this->faker->randomElement(['F-1', 'J-1', 'M-1']) : null,
            'visa_expiry' => $isInternational ? $this->faker->dateTimeBetween('now', '+4 years') : null,
            
            // Enrollment Lifecycle
            'application_date' => $this->faker->dateTimeBetween('-6 years', '-7 months'),
            'admission_decision_date' => $this->faker->dateTimeBetween('-6 years', '-6 months'),
            'enrollment_confirmation_date' => $this->faker->dateTimeBetween('-5 years', '-6 months'),
            'last_enrollment_date' => $enrollmentStatus === 'active' ? $this->faker->dateTimeBetween('-6 months', 'now') : null,
            'leave_start_date' => null,
            'leave_end_date' => null,
            'leave_reason' => null,
            'withdrawal_date' => $enrollmentStatus === 'withdrawn' ? $this->faker->dateTimeBetween('-1 year', 'now') : null,
            'withdrawal_reason' => $enrollmentStatus === 'withdrawn' ? $this->faker->sentence() : null,
            'readmission_date' => null,
            'is_alumni' => $enrollmentStatus === 'graduated',
            
            // Audit Fields (will be set by model events)
            'created_by' => null,
            'updated_by' => null,
            'change_history' => null,
            'last_activity_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }
}