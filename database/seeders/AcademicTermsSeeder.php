<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AcademicTermsSeeder extends Seeder
{
    public function run()
    {
        // Clear existing test data
        DB::table('academic_terms')
            ->whereIn('code', ['FALL-2025', 'SPRING-2026', 'SUMMER-2026', 'FALL-2026'])
            ->delete();

        $terms = [
            [
                'code' => 'FALL-2025',
                'name' => 'Fall 2025',
                'type' => 'fall',
                'academic_year' => 2025,
                'start_date' => '2025-09-02',
                'end_date' => '2025-12-19',
                'registration_start' => '2025-05-01',
                'registration_end' => '2025-08-25',
                'add_drop_deadline' => '2025-09-16',
                'withdrawal_deadline' => '2025-11-15',
                'grades_due_date' => '2025-12-23', // ADD THIS
                'is_admission_open' => false,
                'admission_start_date' => '2024-10-01',
                'admission_deadline' => '2025-03-01',
                'early_admission_deadline' => '2024-12-15',
                'admission_notification_date' => '2025-04-15',
                'total_spots' => 500,
                'is_current' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'SPRING-2026',
                'name' => 'Spring 2026',
                'type' => 'spring',
                'academic_year' => 2026,
                'start_date' => '2026-01-19',
                'end_date' => '2026-05-15',
                'registration_start' => '2025-10-15',
                'registration_end' => '2026-01-12',
                'add_drop_deadline' => '2026-02-02',
                'withdrawal_deadline' => '2026-04-01',
                'grades_due_date' => '2026-05-20', // ADD THIS
                'is_admission_open' => true,
                'admission_start_date' => '2025-08-01',
                'admission_deadline' => '2025-11-15',
                'early_admission_deadline' => '2025-10-15',
                'admission_notification_date' => '2025-12-15',
                'total_spots' => 400,
                'is_current' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'SUMMER-2026',
                'name' => 'Summer 2026',
                'type' => 'summer',
                'academic_year' => 2026,
                'start_date' => '2026-06-01',
                'end_date' => '2026-08-14',
                'registration_start' => '2026-03-01',
                'registration_end' => '2026-05-25',
                'add_drop_deadline' => '2026-06-12',
                'withdrawal_deadline' => '2026-07-15',
                'grades_due_date' => '2026-08-18', // ADD THIS
                'is_admission_open' => true,
                'admission_start_date' => '2025-09-01',
                'admission_deadline' => '2026-04-01',
                'early_admission_deadline' => '2026-02-01',
                'admission_notification_date' => '2026-05-01',
                'total_spots' => 200,
                'is_current' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('academic_terms')->insert($terms);
        
        $this->command->info('Academic terms seeded successfully!');
    }
}