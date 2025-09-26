<?php
// File: app/Http/Controllers/LMSController.php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CourseSite;
use App\Models\ContentItem;
use App\Models\ContentFolder;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Quiz;
use App\Models\Announcement;
use App\Models\CourseSection;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LMSController extends Controller
{
    /**
     * Display LMS dashboard - list of course sites
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get course sites based on user role
        if ($user->hasRole(['faculty', 'instructor'])) {
            // Faculty sees courses they teach
            $courseSites = CourseSite::whereHas('section', function ($query) use ($user) {
                $query->where('primary_instructor_id', $user->id)
                      ->orWhere('secondary_instructor_id', $user->id);
            })->with(['section.course', 'section.term'])->get();
            
            $viewName = 'lms.faculty.dashboard';
        } elseif ($user->hasRole('student')) {
            // Students see courses they're enrolled in
            $student = Student::where('user_id', $user->id)->first();
            
            $courseSites = CourseSite::whereHas('section.enrollments', function ($query) use ($student) {
                $query->where('student_id', $student->id)
                      ->where('status', 'enrolled');
            })->with(['section.course', 'section.term'])->get();
            
            $viewName = 'lms.student.dashboard';
        } else {
            // Admin sees all course sites
            $courseSites = CourseSite::with(['section.course', 'section.term'])->get();
            $viewName = 'lms.admin.dashboard';
        }
        
        return view($viewName, compact('courseSites'));
    }

    /**
     * Display course site homepage
     */
    public function show($siteId)
    {
        $courseSite = CourseSite::with([
            'section.course',
            'section.term',
            'section.primaryInstructor',
            'announcements' => function ($query) {
                $query->visible()->orderBy('created_at', 'desc')->limit(5);
            }
        ])->findOrFail($siteId);
        
        // Check access
        $this->authorize('view', $courseSite);
        
        // Get recent activity
        $recentActivity = $courseSite->getRecentActivity();
        
        // Get upcoming assignments
        $upcomingAssignments = $courseSite->assignments()
            ->visible()
            ->upcoming()
            ->limit(5)
            ->get();
            
        // Get upcoming quizzes
        $upcomingQuizzes = $courseSite->quizzes()
            ->visible()
            ->available()
            ->limit(5)
            ->get();
        
        // Get statistics
        $statistics = $courseSite->getStatistics();
        
        return view('lms.course-site.show', compact(
            'courseSite',
            'recentActivity',
            'upcomingAssignments',
            'upcomingQuizzes',
            'statistics'
        ));
    }

    /**
     * Show course content page
     */
    public function content($siteId)
    {
        $courseSite = CourseSite::findOrFail($siteId);
        $this->authorize('view', $courseSite);
        
        // Get content organized by folders
        $folders = ContentFolder::where('course_site_id', $siteId)
            ->whereNull('parent_id')
            ->with(['children', 'contentItems' => function ($query) {
                $query->available()->orderBy('display_order');
            }])
            ->orderBy('display_order')
            ->get();
            
        // Get unfiled content
        $unfiledContent = ContentItem::where('course_site_id', $siteId)
            ->whereNull('folder_id')
            ->available()
            ->orderBy('display_order')
            ->get();
        
        return view('lms.course-site.content', compact('courseSite', 'folders', 'unfiledContent'));
    }

    /**
     * Upload content item
     */
    public function uploadContent(Request $request, $siteId)
    {
        $courseSite = CourseSite::findOrFail($siteId);
        $this->authorize('manage', $courseSite);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:document,video,link,text',
            'folder_id' => 'nullable|exists:content_folders,id',
            'file' => 'required_if:type,document,video|file|max:102400', // 100MB max
            'external_url' => 'required_if:type,link|nullable|url',
            'content_text' => 'required_if:type,text|nullable|string',
            'available_from' => 'nullable|date',
            'available_until' => 'nullable|date|after:available_from',
            'is_visible' => 'boolean'
        ]);
        
        DB::beginTransaction();
        try {
            $contentItem = new ContentItem($validated);
            $contentItem->course_site_id = $siteId;
            
            // Handle file upload
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $path = $file->store('lms/content/' . $siteId, 'private');
                
                $contentItem->file_path = $path;
                $contentItem->file_name = $file->getClientOriginalName();
                $contentItem->file_size = $file->getSize();
                $contentItem->mime_type = $file->getMimeType();
            }
            
            $contentItem->save();
            
            DB::commit();
            
            return redirect()
                ->route('lms.course-site.content', $siteId)
                ->with('success', 'Content uploaded successfully');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to upload content: ' . $e->getMessage());
        }
    }

    /**
     * Download content item
     */
    public function downloadContent($siteId, $contentId)
    {
        $courseSite = CourseSite::findOrFail($siteId);
        $this->authorize('view', $courseSite);
        
        $contentItem = ContentItem::where('course_site_id', $siteId)
            ->findOrFail($contentId);
            
        if (!$contentItem->file_path) {
            abort(404, 'File not found');
        }
        
        // Log access
        $contentItem->incrementDownloadCount();
        
        // Log to access log table
        DB::table('content_access_logs')->insert([
            'user_id' => Auth::id(),
            'content_item_id' => $contentId,
            'action' => 'download',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'accessed_at' => now()
        ]);
        
        return Storage::disk('private')->download(
            $contentItem->file_path,
            $contentItem->file_name
        );
    }

    /**
     * Show assignments page
     */
    public function assignments($siteId)
    {
        $courseSite = CourseSite::findOrFail($siteId);
        $this->authorize('view', $courseSite);
        
        $user = Auth::user();
        
        if ($user->hasRole('student')) {
            $student = Student::where('user_id', $user->id)->first();
            
            // Get assignments with submission status
            $assignments = $courseSite->assignments()
                ->visible()
                ->with(['submissions' => function ($query) use ($student) {
                    $query->where('student_id', $student->id);
                }])
                ->orderBy('due_date')
                ->get();
                
            return view('lms.course-site.student-assignments', compact('courseSite', 'assignments'));
        } else {
            // Faculty view - all assignments
            $assignments = $courseSite->assignments()
                ->withCount(['submissions', 'submissions as graded_count' => function ($query) {
                    $query->where('status', 'graded');
                }])
                ->orderBy('due_date')
                ->get();
                
            return view('lms.course-site.faculty-assignments', compact('courseSite', 'assignments'));
        }
    }

    /**
     * Create new assignment
     */
    public function createAssignment(Request $request, $siteId)
    {
        $courseSite = CourseSite::findOrFail($siteId);
        $this->authorize('manage', $courseSite);
        
        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'instructions' => 'required|string',
                'max_points' => 'required|numeric|min:0',
                'submission_type' => 'required|in:file,text,both,offline',
                'due_date' => 'required|date|after:now',
                'available_from' => 'nullable|date',
                'available_until' => 'nullable|date|after:available_from',
                'allow_late' => 'boolean',
                'late_penalty_percent' => 'nullable|integer|min:0|max:100',
                'max_attempts' => 'nullable|integer|min:1|max:10',
                'allowed_file_types' => 'nullable|array',
                'max_file_size' => 'nullable|integer|min:1|max:100', // MB
                'is_group_assignment' => 'boolean',
                'group_size' => 'nullable|integer|min:2|max:10'
            ]);
            
            $assignment = $courseSite->assignments()->create($validated);
            
            // Create gradebook item
            $courseSite->gradebookItems()->create([
                'name' => $assignment->title,
                'type' => 'assignment',
                'source_id' => $assignment->id,
                'max_points' => $assignment->max_points,
                'display_order' => $courseSite->gradebookItems()->max('display_order') + 1
            ]);
            
            return redirect()
                ->route('lms.course-site.assignments', $siteId)
                ->with('success', 'Assignment created successfully');
        }
        
        return view('lms.course-site.create-assignment', compact('courseSite'));
    }

    /**
     * Submit assignment
     */
    public function submitAssignment(Request $request, $siteId, $assignmentId)
    {
        $courseSite = CourseSite::findOrFail($siteId);
        $assignment = Assignment::where('course_site_id', $siteId)->findOrFail($assignmentId);
        
        $student = Student::where('user_id', Auth::id())->firstOrFail();
        
        // Check if can submit
        if (!$assignment->canSubmit($student->id)) {
            return back()->with('error', 'Cannot submit to this assignment');
        }
        
        $validated = $request->validate([
            'submission_text' => 'nullable|string',
            'submission_files.*' => 'nullable|file|max:51200' // 50MB per file
        ]);
        
        DB::beginTransaction();
        try {
            // Get attempt number
            $attemptNumber = AssignmentSubmission::where('assignment_id', $assignmentId)
                ->where('student_id', $student->id)
                ->max('attempt_number') ?? 0;
                
            $submission = new AssignmentSubmission([
                'assignment_id' => $assignmentId,
                'student_id' => $student->id,
                'attempt_number' => $attemptNumber + 1,
                'submission_text' => $validated['submission_text'] ?? null,
                'status' => 'draft'
            ]);
            
            // Handle file uploads
            if ($request->hasFile('submission_files')) {
                $files = [];
                foreach ($request->file('submission_files') as $file) {
                    $path = $file->store('lms/submissions/' . $assignmentId, 'private');
                    $files[] = [
                        'path' => $path,
                        'name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'mime' => $file->getMimeType()
                    ];
                }
                $submission->submission_files = $files;
            }
            
            $submission->save();
            $submission->submit();
            
            DB::commit();
            
            return redirect()
                ->route('lms.course-site.assignment.view', [$siteId, $assignmentId])
                ->with('success', 'Assignment submitted successfully');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to submit assignment: ' . $e->getMessage());
        }
    }

    /**
     * Grade assignment submission
     */
    public function gradeSubmission(Request $request, $siteId, $submissionId)
    {
        $courseSite = CourseSite::findOrFail($siteId);
        $this->authorize('manage', $courseSite);
        
        $submission = AssignmentSubmission::with('assignment')
            ->whereHas('assignment', function ($query) use ($siteId) {
                $query->where('course_site_id', $siteId);
            })
            ->findOrFail($submissionId);
            
        $validated = $request->validate([
            'score' => 'required|numeric|min:0|max:' . $submission->assignment->max_points,
            'feedback' => 'nullable|string'
        ]);
        
        $submission->grade($validated['score'], $validated['feedback']);
        
        // Update gradebook entry
        DB::table('gradebook_entries')->updateOrInsert(
            [
                'gradebook_item_id' => DB::table('gradebook_items')
                    ->where('source_id', $submission->assignment_id)
                    ->where('type', 'assignment')
                    ->value('id'),
                'student_id' => $submission->student_id
            ],
            [
                'score' => $submission->weighted_score ?? $submission->score,
                'percentage' => $submission->getPercentageScore(),
                'graded_at' => now(),
                'graded_by' => Auth::id(),
                'updated_at' => now()
            ]
        );
        
        return back()->with('success', 'Assignment graded successfully');
    }

    /**
     * Show announcements
     */
    public function announcements($siteId)
    {
        $courseSite = CourseSite::findOrFail($siteId);
        $this->authorize('view', $courseSite);
        
        $announcements = $courseSite->announcements()
            ->visible()
            ->with('poster')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('lms.course-site.announcements', compact('courseSite', 'announcements'));
    }

    /**
     * Create announcement
     */
    public function createAnnouncement(Request $request, $siteId)
    {
        $courseSite = CourseSite::findOrFail($siteId);
        $this->authorize('manage', $courseSite);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'priority' => 'required|in:low,normal,high,urgent',
            'send_email' => 'boolean',
            'display_from' => 'nullable|date',
            'display_until' => 'nullable|date|after:display_from'
        ]);
        
        $validated['posted_by'] = Auth::id();
        $validated['is_visible'] = true;
        
        $announcement = $courseSite->announcements()->create($validated);
        
        // Send notifications if requested
        if ($request->boolean('send_email')) {
            // Queue email notifications to enrolled students
            // This would be implemented with your notification system
        }
        
        return redirect()
            ->route('lms.course-site.announcements', $siteId)
            ->with('success', 'Announcement posted successfully');
    }

    /**
     * Publish/unpublish course site
     */
    public function togglePublish($siteId)
    {
        $courseSite = CourseSite::findOrFail($siteId);
        $this->authorize('manage', $courseSite);
        
        if ($courseSite->is_published) {
            $courseSite->unpublish();
            $message = 'Course site unpublished';
        } else {
            $courseSite->publish();
            $message = 'Course site published';
        }
        
        return back()->with('success', $message);
    }

    /**
     * Clone course site for new term
     */
    public function cloneSite(Request $request, $siteId)
    {
        $courseSite = CourseSite::findOrFail($siteId);
        $this->authorize('manage', $courseSite);
        
        $validated = $request->validate([
            'section_id' => 'required|exists:course_sections,id'
        ]);
        
        $newSection = CourseSection::findOrFail($validated['section_id']);
        
        // Check if site already exists for this section
        if (CourseSite::where('section_id', $newSection->id)->exists()) {
            return back()->with('error', 'Course site already exists for this section');
        }
        
        DB::beginTransaction();
        try {
            $newSite = $courseSite->cloneForSection($newSection->id);
            
            DB::commit();
            
            return redirect()
                ->route('lms.course-site.show', $newSite->id)
                ->with('success', 'Course site cloned successfully');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to clone course site: ' . $e->getMessage());
        }
    }
}