<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding students...');
        
        // Clear existing students
        DB::table('students')->truncate();
        
        // Create specific test students for easier testing
        $this->createTestStudents();
        
        // Create random students
        $this->createRandomStudents();
        
        $totalStudents = Student::count();
        $this->command->info("Created {$totalStudents} students successfully!");
    }
    
    /**
     * Create specific test students with known data
     */
    private function createTestStudents(): void
    {
        $testStudents = [
            [
                'student_id' => '24000001',
                'first_name' => 'John',
                'middle_name' => 'Michael',
                'last_name' => 'Doe',
                'preferred_name' => 'Johnny',
                'email' => 'john.doe@university.edu',
                'secondary_email' => 'johndoe@gmail.com',
                'phone' => '+1234567890',
                'home_phone' => '+1234567891',
                'date_of_birth' => '2000-01-15',
                'place_of_birth' => 'New York, USA',
                'gender' => 'male',
                'marital_status' => 'single',
                'nationality' => 'American',
                'national_id_number' => '123456789',
                'address' => '123 Main St, New York, NY 10001',
                'permanent_address' => '456 Home Ave, Boston, MA 02101',
                'program_name' => 'Computer Science',
                'department' => 'Computer Science',
                'major' => 'Software Engineering',
                'minor' => 'Mathematics',
                'academic_level' => 'junior',
                'enrollment_status' => 'active',
                'academic_standing' => 'good',
                'admission_status' => 'enrolled',
                'current_gpa' => 3.75,
                'cumulative_gpa' => 3.68,
                'credits_earned' => 75,
                'credits_completed' => 75,
                'credits_required' => 120,
                'admission_date' => '2021-09-01',
                'expected_graduation_year' => 2025,
                'high_school_name' => 'Lincoln High School',
                'high_school_graduation_year' => 2021,
                'high_school_gpa' => 3.85,
                'advisor_name' => 'Dr. Robert Smith',
                'guardian_name' => 'Michael Doe',
                'guardian_phone' => '+1234567892',
                'guardian_email' => 'michael.doe@email.com',
                'emergency_contact_name' => 'Jane Doe',
                'emergency_contact_phone' => '+1234567893',
                'next_of_kin_name' => 'Jane Doe',
                'next_of_kin_relationship' => 'Mother',
                'next_of_kin_phone' => '+1234567893',
                'blood_group' => 'O+',
                'has_profile_photo' => true,
                'has_national_id_copy' => true,
                'has_high_school_certificate' => true,
                'has_high_school_transcript' => true,
                'has_immunization_records' => true,
            ],
            [
                'student_id' => '24000002',
                'first_name' => 'Jane',
                'middle_name' => 'Elizabeth',
                'last_name' => 'Smith',
                'preferred_name' => 'Jane',
                'email' => 'jane.smith@university.edu',
                'phone' => '+1234567894',
                'date_of_birth' => '1999-05-20',
                'place_of_birth' => 'Toronto, Canada',
                'gender' => 'female',
                'marital_status' => 'single',
                'nationality' => 'Canadian',
                'national_id_number' => '987654321',
                'address' => '789 College Ave, New York, NY 10002',
                'permanent_address' => '321 Maple St, Toronto, ON M5V 3A8',
                'program_name' => 'Business Administration',
                'department' => 'Business',
                'major' => 'Finance',
                'minor' => 'Economics',
                'academic_level' => 'senior',
                'enrollment_status' => 'active',
                'academic_standing' => 'good',
                'admission_status' => 'enrolled',
                'current_gpa' => 3.92,
                'cumulative_gpa' => 3.85,
                'credits_earned' => 108,
                'credits_completed' => 108,
                'credits_required' => 120,
                'admission_date' => '2020-09-01',
                'expected_graduation_year' => 2024,
                'high_school_name' => 'Toronto Central High',
                'high_school_graduation_year' => 2020,
                'high_school_gpa' => 3.95,
                'advisor_name' => 'Dr. Emily Johnson',
                'guardian_name' => 'Robert Smith',
                'guardian_phone' => '+1234567895',
                'guardian_email' => 'robert.smith@email.com',
                'emergency_contact_name' => 'Robert Smith',
                'emergency_contact_phone' => '+1234567895',
                'next_of_kin_name' => 'Mary Smith',
                'next_of_kin_relationship' => 'Mother',
                'next_of_kin_phone' => '+1234567896',
                'blood_group' => 'A+',
                'is_international' => true,
                'passport_number' => 'CA1234567',
                'visa_status' => 'F-1',
                'visa_expiry' => '2025-08-31',
                'has_profile_photo' => true,
                'has_national_id_copy' => true,
                'has_high_school_certificate' => true,
                'has_high_school_transcript' => true,
                'has_immunization_records' => true,
            ],
            [
                'student_id' => '24000003',
                'first_name' => 'Robert',
                'middle_name' => 'James',
                'last_name' => 'Johnson',
                'email' => 'robert.johnson@university.edu',
                'phone' => '+1234567897',
                'date_of_birth' => '2001-11-30',
                'place_of_birth' => 'Chicago, USA',
                'gender' => 'male',
                'marital_status' => 'single',
                'nationality' => 'American',
                'national_id_number' => '555666777',
                'address' => '555 University Blvd, New York, NY 10003',
                'permanent_address' => '999 Lake Shore Dr, Chicago, IL 60601',
                'program_name' => 'Engineering',
                'department' => 'Engineering',
                'major' => 'Mechanical Engineering',
                'academic_level' => 'sophomore',
                'enrollment_status' => 'active',
                'academic_standing' => 'probation',
                'admission_status' => 'enrolled',
                'current_gpa' => 1.85,
                'cumulative_gpa' => 1.92,
                'credits_earned' => 42,
                'credits_completed' => 42,
                'credits_required' => 120,
                'admission_date' => '2022-09-01',
                'expected_graduation_year' => 2026,
                'high_school_name' => 'Chicago Technical High',
                'high_school_graduation_year' => 2022,
                'high_school_gpa' => 3.2,
                'advisor_name' => 'Dr. Michael Brown',
                'guardian_name' => 'James Johnson',
                'guardian_phone' => '+1234567898',
                'guardian_email' => 'james.johnson@email.com',
                'emergency_contact_name' => 'Sarah Johnson',
                'emergency_contact_phone' => '+1234567899',
                'next_of_kin_name' => 'Sarah Johnson',
                'next_of_kin_relationship' => 'Sister',
                'next_of_kin_phone' => '+1234567899',
                'blood_group' => 'B+',
                'medical_conditions' => 'Mild asthma',
                'has_profile_photo' => true,
                'has_national_id_copy' => true,
                'has_high_school_certificate' => true,
                'has_high_school_transcript' => true,
                'has_immunization_records' => false,
            ],
            [
                'student_id' => '24000004',
                'first_name' => 'Maria',
                'middle_name' => 'Isabel',
                'last_name' => 'Garcia',
                'email' => 'maria.garcia@university.edu',
                'phone' => '+1234567900',
                'date_of_birth' => '2000-07-22',
                'place_of_birth' => 'Madrid, Spain',
                'gender' => 'female',
                'marital_status' => 'single',
                'nationality' => 'Spanish',
                'national_id_number' => '111222333',
                'address' => '777 Campus Way, New York, NY 10004',
                'permanent_address' => 'Calle Mayor 123, Madrid, Spain 28001',
                'program_name' => 'Medicine',
                'department' => 'Medical Sciences',
                'major' => 'Pre-Medicine',
                'academic_level' => 'senior',
                'enrollment_status' => 'active',
                'academic_standing' => 'good',
                'admission_status' => 'enrolled',
                'current_gpa' => 3.95,
                'cumulative_gpa' => 3.91,
                'credits_earned' => 110,
                'credits_completed' => 110,
                'credits_required' => 120,
                'admission_date' => '2020-09-01',
                'expected_graduation_year' => 2024,
                'high_school_name' => 'Instituto San Isidro',
                'high_school_graduation_year' => 2020,
                'high_school_gpa' => 4.0,
                'advisor_name' => 'Dr. Patricia Williams',
                'guardian_name' => 'Carlos Garcia',
                'guardian_phone' => '+34612345678',
                'guardian_email' => 'carlos.garcia@email.com',
                'emergency_contact_name' => 'Ana Garcia',
                'emergency_contact_phone' => '+34612345679',
                'next_of_kin_name' => 'Ana Garcia',
                'next_of_kin_relationship' => 'Mother',
                'next_of_kin_phone' => '+34612345679',
                'blood_group' => 'AB+',
                'is_international' => true,
                'passport_number' => 'ES9876543',
                'visa_status' => 'F-1',
                'visa_expiry' => '2024-12-31',
                'has_profile_photo' => true,
                'has_national_id_copy' => true,
                'has_high_school_certificate' => true,
                'has_high_school_transcript' => true,
                'has_immunization_records' => true,
                'insurance_provider' => 'International Student Insurance',
                'insurance_policy_number' => 'ISI123456',
                'insurance_expiry' => '2024-12-31',
            ],
            [
                'student_id' => '23000005',
                'first_name' => 'David',
                'middle_name' => 'Lee',
                'last_name' => 'Kim',
                'email' => 'david.kim@university.edu',
                'phone' => '+1234567901',
                'date_of_birth' => '1999-03-15',
                'place_of_birth' => 'Seoul, South Korea',
                'gender' => 'male',
                'marital_status' => 'single',
                'nationality' => 'South Korean',
                'national_id_number' => '999888777',
                'address' => '888 Student Hall, New York, NY 10005',
                'program_name' => 'Computer Science',
                'department' => 'Computer Science',
                'major' => 'Artificial Intelligence',
                'minor' => 'Data Science',
                'academic_level' => 'graduate',
                'enrollment_status' => 'graduated',
                'academic_standing' => 'good',
                'admission_status' => 'enrolled',
                'current_gpa' => 3.88,
                'cumulative_gpa' => 3.85,
                'credits_earned' => 120,
                'credits_completed' => 120,
                'credits_required' => 120,
                'admission_date' => '2019-09-01',
                'graduation_date' => '2023-05-15',
                'degree_awarded' => 'Bachelor of Science in Computer Science',
                'expected_graduation_year' => 2023,
                'high_school_name' => 'Seoul International School',
                'high_school_graduation_year' => 2019,
                'high_school_gpa' => 3.92,
                'advisor_name' => 'Dr. Chang Liu',
                'blood_group' => 'O-',
                'is_alumni' => true,
                'has_profile_photo' => true,
                'has_national_id_copy' => true,
                'has_high_school_certificate' => true,
                'has_high_school_transcript' => true,
                'has_immunization_records' => true,
            ],
        ];

        foreach ($testStudents as $studentData) {
            Student::create($studentData);
        }
        
        $this->command->info('Created 5 test students');
    }
    
    /**
     * Create random students using factory
     */
    private function createRandomStudents(): void
    {
        $count = 95; // Create 95 more to total 100
        
        Student::factory()->count($count)->create();
        
        $this->command->info("Created {$count} random students");
    }
}