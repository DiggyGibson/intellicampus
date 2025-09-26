<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fixed LMS migration with correct table order and references
 * Save as: database/migrations/2025_01_16_000001_create_lms_tables_fixed.php
 */
class CreateLmsTablesFixed extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Course Sites - Virtual classrooms for each course
        Schema::create('course_sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('course_sections')->onDelete('cascade');
            $table->string('site_code')->unique();
            $table->string('site_name');
            $table->text('description')->nullable();
            $table->text('welcome_message')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_published')->default(false);
            $table->json('settings')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            
            $table->index(['section_id', 'is_active']);
            $table->index('site_code');
        });

        // 2. Content Folders - MUST BE CREATED BEFORE content_items
        Schema::create('content_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_site_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('content_folders');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
            
            $table->index(['course_site_id', 'parent_id']);
        });

        // 3. Content Items - Course materials, documents, videos
        Schema::create('content_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_site_id')->constrained()->onDelete('cascade');
            $table->foreignId('folder_id')->nullable()->constrained('content_folders');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['document', 'video', 'link', 'embed', 'text']);
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->text('content_text')->nullable();
            $table->string('external_url')->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamp('available_from')->nullable();
            $table->timestamp('available_until')->nullable();
            $table->integer('download_count')->default(0);
            $table->integer('view_count')->default(0);
            $table->timestamps();
            
            $table->index(['course_site_id', 'is_visible']);
            $table->index('type');
        });

        // 4. Assignments
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_site_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('instructions');
            $table->decimal('max_points', 8, 2);
            $table->enum('submission_type', ['file', 'text', 'both', 'offline']);
            $table->boolean('allow_late')->default(false);
            $table->integer('late_penalty_percent')->nullable();
            $table->timestamp('due_date');
            $table->timestamp('available_from')->nullable();
            $table->timestamp('available_until')->nullable();
            $table->integer('max_attempts')->default(1);
            $table->boolean('is_group_assignment')->default(false);
            $table->integer('group_size')->nullable();
            $table->json('allowed_file_types')->nullable();
            $table->integer('max_file_size')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->boolean('use_rubric')->default(false);
            $table->timestamps();
            
            $table->index(['course_site_id', 'due_date']);
            $table->index(['due_date', 'is_visible']);
        });

        // 5. Assignment Groups (for group assignments) - BEFORE assignment_submissions
        Schema::create('assignment_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->onDelete('cascade');
            $table->string('group_name');
            $table->foreignId('leader_id')->constrained('students');
            $table->timestamps();
            
            $table->index('assignment_id');
        });

        // 6. Assignment Submissions
        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('group_id')->nullable()->constrained('assignment_groups');
            $table->integer('attempt_number')->default(1);
            $table->text('submission_text')->nullable();
            $table->json('submission_files')->nullable();
            $table->decimal('score', 8, 2)->nullable();
            $table->decimal('weighted_score', 8, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->enum('status', ['draft', 'submitted', 'graded', 'returned']);
            $table->boolean('is_late')->default(false);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('graded_at')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users');
            $table->timestamps();
            
            $table->unique(['assignment_id', 'student_id', 'attempt_number']);
            $table->index(['assignment_id', 'status']);
            $table->index(['student_id', 'submitted_at']);
        });

        // 7. Assignment Group Members
        Schema::create('assignment_group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('assignment_groups')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['group_id', 'student_id']);
        });

        // 8. Rubrics
        Schema::create('rubrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_site_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('max_points', 8, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 9. Rubric Criteria
        Schema::create('rubric_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rubric_id')->constrained()->onDelete('cascade');
            $table->string('criterion');
            $table->text('description')->nullable();
            $table->decimal('max_points', 8, 2);
            $table->integer('display_order')->default(0);
            $table->timestamps();
            
            $table->index('rubric_id');
        });

        // 10. Quizzes/Tests
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_site_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('instructions')->nullable();
            $table->enum('type', ['quiz', 'test', 'exam', 'practice']);
            $table->integer('time_limit')->nullable();
            $table->integer('max_attempts')->default(1);
            $table->decimal('max_points', 8, 2);
            $table->boolean('shuffle_questions')->default(false);
            $table->boolean('shuffle_answers')->default(false);
            $table->boolean('show_results')->default(false);
            $table->boolean('show_correct_answers')->default(false);
            $table->timestamp('available_from')->nullable();
            $table->timestamp('available_until')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->boolean('use_lockdown_browser')->default(false);
            $table->string('password')->nullable();
            $table->timestamps();
            
            $table->index(['course_site_id', 'type']);
            $table->index(['available_from', 'available_until']);
        });

        // 11. Quiz Questions
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['multiple_choice', 'true_false', 'short_answer', 'essay', 'matching', 'fill_blank']);
            $table->text('question');
            $table->json('options')->nullable();
            $table->json('correct_answers');
            $table->decimal('points', 8, 2);
            $table->integer('display_order')->default(0);
            $table->text('explanation')->nullable();
            $table->timestamps();
            
            $table->index(['quiz_id', 'display_order']);
        });

        // 12. Quiz Attempts
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students');
            $table->integer('attempt_number')->default(1);
            $table->json('answers');
            $table->decimal('score', 8, 2)->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->integer('time_taken')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->enum('status', ['in_progress', 'completed', 'abandoned']);
            $table->timestamps();
            
            $table->unique(['quiz_id', 'student_id', 'attempt_number']);
            $table->index(['quiz_id', 'status']);
            $table->index(['student_id', 'completed_at']);
        });

        // 13. Discussion Forums
        Schema::create('discussion_forums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_site_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['general', 'topic', 'q_and_a', 'group']);
            $table->boolean('is_active')->default(true);
            $table->boolean('allow_student_posts')->default(true);
            $table->boolean('require_approval')->default(false);
            $table->boolean('allow_anonymous')->default(false);
            $table->integer('display_order')->default(0);
            $table->timestamps();
            
            $table->index(['course_site_id', 'is_active']);
        });

        // 14. Discussion Posts
        Schema::create('discussion_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forum_id')->constrained('discussion_forums')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('discussion_posts');
            $table->foreignId('user_id')->constrained('users');
            $table->string('title')->nullable();
            $table->text('content');
            $table->boolean('is_anonymous')->default(false);
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_approved')->default(true);
            $table->integer('like_count')->default(0);
            $table->integer('reply_count')->default(0);
            $table->timestamp('edited_at')->nullable();
            $table->timestamps();
            
            $table->index(['forum_id', 'parent_id']);
            $table->index(['user_id', 'created_at']);
        });

        // 15. LMS Announcements (Note: you already have an announcements table, so naming this lms_announcements)
        Schema::create('lms_announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_site_id')->constrained()->onDelete('cascade');
            $table->foreignId('posted_by')->constrained('users');
            $table->string('title');
            $table->text('content');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->boolean('send_email')->default(false);
            $table->boolean('send_sms')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->timestamp('display_from')->nullable();
            $table->timestamp('display_until')->nullable();
            $table->integer('view_count')->default(0);
            $table->timestamps();
            
            $table->index(['course_site_id', 'is_visible', 'priority']);
            $table->index(['display_from', 'display_until']);
        });

        // 16. Gradebook Items (for custom grade columns)
        Schema::create('gradebook_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_site_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->enum('type', ['assignment', 'quiz', 'exam', 'participation', 'custom']);
            $table->foreignId('source_id')->nullable();
            $table->decimal('max_points', 8, 2);
            $table->decimal('weight', 5, 2)->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->boolean('include_in_final')->default(true);
            $table->timestamps();
            
            $table->index(['course_site_id', 'type']);
        });

        // 17. Gradebook Entries
        Schema::create('gradebook_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gradebook_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students');
            $table->decimal('score', 8, 2)->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->char('letter_grade', 2)->nullable();
            $table->text('comments')->nullable();
            $table->boolean('is_excused')->default(false);
            $table->timestamp('graded_at')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users');
            $table->timestamps();
            
            $table->unique(['gradebook_item_id', 'student_id']);
            $table->index(['student_id', 'graded_at']);
        });

        // 18. Student Progress Tracking
        Schema::create('student_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('course_site_id')->constrained()->onDelete('cascade');
            $table->integer('content_viewed')->default(0);
            $table->integer('assignments_submitted')->default(0);
            $table->integer('quizzes_completed')->default(0);
            $table->integer('forum_posts')->default(0);
            $table->decimal('current_grade', 5, 2)->nullable();
            $table->integer('login_count')->default(0);
            $table->integer('time_spent')->default(0);
            $table->timestamp('last_access')->nullable();
            $table->timestamps();
            
            $table->unique(['student_id', 'course_site_id']);
            $table->index('course_site_id');
        });

        // 19. Content Access Log
        Schema::create('content_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('content_item_id')->constrained()->onDelete('cascade');
            $table->enum('action', ['view', 'download']);
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('accessed_at');
            
            $table->index(['content_item_id', 'accessed_at']);
            $table->index(['user_id', 'accessed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop in reverse order to avoid foreign key constraints
        Schema::dropIfExists('content_access_logs');
        Schema::dropIfExists('student_progress');
        Schema::dropIfExists('gradebook_entries');
        Schema::dropIfExists('gradebook_items');
        Schema::dropIfExists('lms_announcements');
        Schema::dropIfExists('discussion_posts');
        Schema::dropIfExists('discussion_forums');
        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('quiz_questions');
        Schema::dropIfExists('quizzes');
        Schema::dropIfExists('rubric_criteria');
        Schema::dropIfExists('rubrics');
        Schema::dropIfExists('assignment_group_members');
        Schema::dropIfExists('assignment_submissions');
        Schema::dropIfExists('assignment_groups');
        Schema::dropIfExists('assignments');
        Schema::dropIfExists('content_items');
        Schema::dropIfExists('content_folders');
        Schema::dropIfExists('course_sites');
    }
}