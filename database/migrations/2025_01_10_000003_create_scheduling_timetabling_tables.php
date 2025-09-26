<?php
// File: database/migrations/2025_01_10_000003_create_scheduling_timetabling_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Time Slots Configuration
        Schema::create('time_slots', function (Blueprint $table) {
            $table->id();
            $table->string('slot_name', 50); // e.g., "Period 1", "8:00-9:00"
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('duration_minutes');
            $table->string('slot_type', 30)->default('regular'); // regular, lab, tutorial
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['start_time', 'end_time']);
            $table->index('slot_type');
        });

        // Academic Buildings
        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->string('building_code', 20)->unique();
            $table->string('building_name');
            $table->text('address')->nullable();
            $table->integer('total_floors')->default(1);
            $table->json('facilities')->nullable(); // elevator, parking, cafeteria, etc.
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Classrooms/Labs
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained('buildings');
            $table->string('room_code', 20)->unique();
            $table->string('room_name');
            $table->string('room_type', 30); // classroom, lab, auditorium, seminar
            $table->integer('capacity');
            $table->integer('exam_capacity')->nullable();
            $table->json('equipment')->nullable(); // projector, whiteboard, computers, etc.
            $table->json('software')->nullable(); // For computer labs
            $table->boolean('is_accessible')->default(false); // Wheelchair accessible
            $table->boolean('has_ac')->default(false);
            $table->boolean('has_projector')->default(false);
            $table->boolean('has_computers')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['building_id', 'room_type']);
            $table->index('capacity');
        });

        // Room Availability Schedule
        Schema::create('room_availability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms');
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_available')->default(true);
            $table->string('unavailable_reason')->nullable();
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->timestamps();
            
            $table->index(['room_id', 'day_of_week']);
        });

        // Class Schedules (Main Timetable)
        Schema::create('class_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('course_sections');
            $table->foreignId('room_id')->nullable()->constrained('rooms');
            $table->foreignId('instructor_id')->nullable()->constrained('users');
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->string('schedule_type', 30)->default('lecture'); // lecture, lab, tutorial, seminar
            $table->date('effective_from');
            $table->date('effective_until');
            $table->boolean('is_recurring')->default(true);
            $table->string('recurrence_pattern')->nullable(); // weekly, biweekly, custom
            $table->boolean('is_online')->default(false);
            $table->string('online_link')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['section_id', 'day_of_week']);
            $table->index(['room_id', 'day_of_week', 'start_time']);
            $table->index(['instructor_id', 'day_of_week']);
        });

        // Schedule Conflicts
        Schema::create('schedule_conflicts', function (Blueprint $table) {
            $table->id();
            $table->string('conflict_type', 30); // room, instructor, student
            $table->foreignId('schedule_1_id')->constrained('class_schedules');
            $table->foreignId('schedule_2_id')->constrained('class_schedules');
            $table->text('description');
            $table->enum('severity', ['low', 'medium', 'high', 'critical']);
            $table->boolean('is_resolved')->default(false);
            $table->text('resolution_notes')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            
            $table->index(['conflict_type', 'is_resolved']);
        });

        // Schedule Changes/Swaps
        Schema::create('schedule_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('original_schedule_id')->constrained('class_schedules');
            $table->string('change_type', 30); // reschedule, cancel, room_change, instructor_change
            $table->date('change_date');
            $table->time('original_start_time')->nullable();
            $table->time('original_end_time')->nullable();
            $table->foreignId('original_room_id')->nullable()->constrained('rooms');
            $table->time('new_start_time')->nullable();
            $table->time('new_end_time')->nullable();
            $table->foreignId('new_room_id')->nullable()->constrained('rooms');
            $table->foreignId('new_instructor_id')->nullable()->constrained('users');
            $table->text('reason');
            $table->boolean('is_permanent')->default(false);
            $table->boolean('notification_sent')->default(false);
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
            
            $table->index(['original_schedule_id', 'change_date']);
            $table->index('status');
        });

        // Faculty Availability
        Schema::create('faculty_availability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_id')->constrained('users');
            $table->foreignId('term_id')->constrained('academic_terms');
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_available')->default(true);
            $table->string('preference_level', 20)->default('neutral'); // preferred, neutral, avoid
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['faculty_id', 'term_id', 'day_of_week', 'start_time']);
            $table->index(['faculty_id', 'term_id']);
        });

        // Teaching Load Configuration
        Schema::create('teaching_loads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_id')->constrained('users');
            $table->foreignId('term_id')->constrained('academic_terms');
            $table->integer('min_credit_hours')->default(9);
            $table->integer('max_credit_hours')->default(15);
            $table->integer('current_credit_hours')->default(0);
            $table->integer('max_courses')->default(4);
            $table->integer('current_courses')->default(0);
            $table->integer('max_preparations')->default(3); // Different courses
            $table->json('preferred_times')->nullable();
            $table->json('blocked_times')->nullable();
            $table->boolean('can_teach_evening')->default(true);
            $table->boolean('can_teach_weekend')->default(false);
            $table->boolean('can_teach_online')->default(true);
            $table->timestamps();
            
            $table->unique(['faculty_id', 'term_id']);
        });

        // Timetable Templates
        Schema::create('timetable_templates', function (Blueprint $table) {
            $table->id();
            $table->string('template_name');
            $table->string('template_type', 30); // standard, compressed, evening, weekend
            $table->json('time_slots');
            $table->json('break_times');
            $table->integer('days_per_week')->default(5);
            $table->time('day_start_time')->default('08:00:00');
            $table->time('day_end_time')->default('17:00:00');
            $table->boolean('include_saturday')->default(false);
            $table->boolean('include_sunday')->default(false);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Room Bookings (for special events)
        Schema::create('room_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms');
            $table->string('booking_type', 30); // event, meeting, exam, maintenance
            $table->string('event_name');
            $table->text('description')->nullable();
            $table->date('booking_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('expected_attendees')->nullable();
            $table->json('requirements')->nullable(); // special requirements
            $table->foreignId('booked_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
            
            $table->index(['room_id', 'booking_date']);
            $table->index(['status', 'booking_date']);
        });

        // Insert default data
        $this->insertDefaultData();
    }

    private function insertDefaultData()
    {
        // Default Time Slots
        $timeSlots = [
            ['slot_name' => 'Period 1', 'start_time' => '08:00', 'end_time' => '09:00', 'duration_minutes' => 60],
            ['slot_name' => 'Period 2', 'start_time' => '09:00', 'end_time' => '10:00', 'duration_minutes' => 60],
            ['slot_name' => 'Period 3', 'start_time' => '10:00', 'end_time' => '11:00', 'duration_minutes' => 60],
            ['slot_name' => 'Period 4', 'start_time' => '11:00', 'end_time' => '12:00', 'duration_minutes' => 60],
            ['slot_name' => 'Lunch Break', 'start_time' => '12:00', 'end_time' => '13:00', 'duration_minutes' => 60, 'slot_type' => 'break'],
            ['slot_name' => 'Period 5', 'start_time' => '13:00', 'end_time' => '14:00', 'duration_minutes' => 60],
            ['slot_name' => 'Period 6', 'start_time' => '14:00', 'end_time' => '15:00', 'duration_minutes' => 60],
            ['slot_name' => 'Period 7', 'start_time' => '15:00', 'end_time' => '16:00', 'duration_minutes' => 60],
            ['slot_name' => 'Period 8', 'start_time' => '16:00', 'end_time' => '17:00', 'duration_minutes' => 60],
        ];

        foreach ($timeSlots as $slot) {
            DB::table('time_slots')->insert(array_merge($slot, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }

        // Default Buildings
        $buildings = [
            ['building_code' => 'MAIN', 'building_name' => 'Main Building', 'total_floors' => 4],
            ['building_code' => 'SCI', 'building_name' => 'Science Block', 'total_floors' => 3],
            ['building_code' => 'ENG', 'building_name' => 'Engineering Building', 'total_floors' => 5],
            ['building_code' => 'LIB', 'building_name' => 'Library Complex', 'total_floors' => 2],
        ];

        foreach ($buildings as $building) {
            $buildingId = DB::table('buildings')->insertGetId(array_merge($building, [
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]));

            // Add sample rooms for each building
            $this->createSampleRooms($buildingId, $building['building_code']);
        }

        // Default Timetable Template
        DB::table('timetable_templates')->insert([
            'template_name' => 'Standard Weekly Schedule',
            'template_type' => 'standard',
            'time_slots' => json_encode($timeSlots),
            'break_times' => json_encode([
                ['start' => '10:00', 'end' => '10:15', 'name' => 'Morning Break'],
                ['start' => '12:00', 'end' => '13:00', 'name' => 'Lunch Break'],
                ['start' => '15:00', 'end' => '15:15', 'name' => 'Afternoon Break']
            ]),
            'days_per_week' => 5,
            'day_start_time' => '08:00:00',
            'day_end_time' => '17:00:00',
            'is_default' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    private function createSampleRooms($buildingId, $buildingCode)
    {
        $roomTypes = [
            'classroom' => ['prefix' => 'CR', 'capacity' => 40, 'count' => 5],
            'lab' => ['prefix' => 'LAB', 'capacity' => 30, 'count' => 3],
            'seminar' => ['prefix' => 'SR', 'capacity' => 20, 'count' => 2],
        ];

        foreach ($roomTypes as $type => $config) {
            for ($i = 1; $i <= $config['count']; $i++) {
                DB::table('rooms')->insert([
                    'building_id' => $buildingId,
                    'room_code' => $buildingCode . '-' . $config['prefix'] . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'room_name' => ucfirst($type) . ' ' . $i,
                    'room_type' => $type,
                    'capacity' => $config['capacity'],
                    'exam_capacity' => intval($config['capacity'] * 0.6),
                    'equipment' => json_encode($type === 'lab' ? ['computers', 'projector'] : ['projector', 'whiteboard']),
                    'has_projector' => true,
                    'has_computers' => $type === 'lab',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }

    public function down()
    {
        Schema::dropIfExists('room_bookings');
        Schema::dropIfExists('timetable_templates');
        Schema::dropIfExists('teaching_loads');
        Schema::dropIfExists('faculty_availability');
        Schema::dropIfExists('schedule_changes');
        Schema::dropIfExists('schedule_conflicts');
        Schema::dropIfExists('class_schedules');
        Schema::dropIfExists('room_availability');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('buildings');
        Schema::dropIfExists('time_slots');
    }
};