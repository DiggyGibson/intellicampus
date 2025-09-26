<?php
/**
 * IntelliCampus Faculty Routes
 * 
 * All faculty-specific routes and teaching-related functionality.
 * These routes are automatically prefixed with 'faculty' and named with 'faculty.'
 * Applied middleware: 'web', 'auth', 'verified', 'role:faculty,instructor'
 */

use App\Http\Controllers\FacultyController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LMSController;
use App\Http\Controllers\AdvisorController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\GradeComponentController;
use App\Http\Controllers\GradeStatisticsController;
use Illuminate\Support\Facades\Route;

// Note: grades.php handles most grade-related routes with proper prefixing
// We'll include only faculty-specific overrides here

// ============================================================
// FACULTY DASHBOARD
// ============================================================
Route::get('/', function() {
    return redirect()->route('faculty.dashboard');
});

Route::get('/dashboard', [FacultyController::class, 'dashboard'])->name('dashboard');
Route::get('/home', [FacultyController::class, 'dashboard'])->name('home');
Route::get('/profile', [FacultyController::class, 'profile'])->name('profile');
Route::get('/schedule', [FacultyController::class, 'schedule'])->name('schedule');
Route::get('/calendar', [FacultyController::class, 'calendar'])->name('calendar');
Route::get('/courses', [FacultyController::class, 'courses'])->name('courses');

// Quick Stats & Widgets
Route::get('/dashboard/stats', [FacultyController::class, 'dashboardStats'])->name('dashboard.stats');
Route::get('/dashboard/upcoming', [FacultyController::class, 'upcomingClasses'])->name('dashboard.upcoming');
Route::get('/dashboard/announcements', [FacultyController::class, 'recentAnnouncements'])->name('dashboard.announcements');

// ============================================================
// COURSE MANAGEMENT - FIXED TO AVOID CONFLICTS
// ============================================================

// Direct route that faculty dashboard expects
Route::get('/courses', [FacultyController::class, 'courses'])->name('courses');

Route::prefix('courses')->name('courses.')->group(function () {
    Route::get('/', [FacultyController::class, 'courses'])->name('index');
    Route::get('/current', [FacultyController::class, 'currentCourses'])->name('current');
    Route::get('/past', [FacultyController::class, 'pastCourses'])->name('past');
    Route::get('/upcoming', [FacultyController::class, 'upcomingCourses'])->name('upcoming');
    Route::get('/{course}', [FacultyController::class, 'courseDetails'])->name('show');
    Route::get('/{course}/syllabus', [FacultyController::class, 'syllabus'])->name('syllabus');
    Route::post('/{course}/syllabus', [FacultyController::class, 'updateSyllabus'])->name('syllabus.update');
});

// ============================================================
// SECTION MANAGEMENT
// ============================================================
Route::prefix('sections')->name('sections.')->group(function () {
    Route::get('/', [FacultyController::class, 'sections'])->name('index');
    Route::get('/current', [FacultyController::class, 'currentSections'])->name('current');
    Route::get('/{section}', [FacultyController::class, 'sectionDetails'])->name('show');
    Route::get('/{section}/roster', [FacultyController::class, 'sectionRoster'])->name('roster');
    Route::get('/{section}/roster/print', [FacultyController::class, 'printRoster'])->name('roster.print');
    Route::get('/{section}/roster/export', [FacultyController::class, 'exportRoster'])->name('roster.export');
    Route::get('/{section}/photo-roster', [FacultyController::class, 'photoRoster'])->name('photo-roster');
    
    // Section Settings
    Route::get('/{section}/settings', [FacultyController::class, 'sectionSettings'])->name('settings');
    Route::post('/{section}/settings', [FacultyController::class, 'updateSectionSettings'])->name('settings.update');
    
    // Section Communications
    Route::get('/{section}/announcements', [FacultyController::class, 'sectionAnnouncements'])->name('announcements');
    Route::post('/{section}/announcements', [FacultyController::class, 'createAnnouncement'])->name('announcements.create');
    Route::post('/{section}/email', [FacultyController::class, 'emailSection'])->name('email');
});

// ============================================================
// ATTENDANCE MANAGEMENT
// ============================================================
Route::prefix('attendance')->name('attendance.')->group(function () {
    Route::get('/', [AttendanceController::class, 'index'])->name('index');
    Route::get('/section/{section}', [AttendanceController::class, 'sectionAttendance'])->name('section');
    Route::get('/section/{section}/take', [AttendanceController::class, 'takeAttendance'])->name('take');
    Route::post('/section/{section}/save', [AttendanceController::class, 'saveAttendance'])->name('save');
    Route::get('/section/{section}/history', [AttendanceController::class, 'attendanceHistory'])->name('history');
    Route::get('/section/{section}/report', [AttendanceController::class, 'attendanceReport'])->name('report');
    Route::get('/section/{section}/export', [AttendanceController::class, 'exportAttendance'])->name('export');
    Route::post('/quick-mark', [AttendanceController::class, 'quickMark'])->name('quick-mark');
});

// ============================================================
// OFFICE HOURS MANAGEMENT
// ============================================================
Route::prefix('office-hours')->name('office-hours.')->group(function () {
    Route::get('/', [FacultyController::class, 'officeHours'])->name('index');
    Route::get('/schedule', [FacultyController::class, 'officeHoursSchedule'])->name('schedule');
    Route::get('/create', [FacultyController::class, 'createOfficeHours'])->name('create');
    Route::post('/store', [FacultyController::class, 'storeOfficeHours'])->name('store');
    Route::get('/{hours}/edit', [FacultyController::class, 'editOfficeHours'])->name('edit');
    Route::put('/{hours}', [FacultyController::class, 'updateOfficeHours'])->name('update');
    Route::delete('/{hours}', [FacultyController::class, 'deleteOfficeHours'])->name('delete');
    
    // Appointment Management
    Route::get('/appointments', [FacultyController::class, 'appointments'])->name('appointments');
    Route::get('/appointments/{appointment}', [FacultyController::class, 'appointmentDetails'])->name('appointments.show');
    Route::post('/appointments/{appointment}/confirm', [FacultyController::class, 'confirmAppointment'])->name('appointments.confirm');
    Route::post('/appointments/{appointment}/cancel', [FacultyController::class, 'cancelAppointment'])->name('appointments.cancel');
    Route::post('/appointments/{appointment}/notes', [FacultyController::class, 'addAppointmentNotes'])->name('appointments.notes');
});

// ============================================================
// ADVISING (For Faculty who are also Advisors)
// ============================================================
Route::middleware('role:advisor')->prefix('advising')->name('advising.')->group(function () {
    Route::get('/', [AdvisorController::class, 'dashboard'])->name('dashboard');
    Route::get('/advisees', [AdvisorController::class, 'advisees'])->name('advisees');
    Route::get('/advisee/{student}', [AdvisorController::class, 'adviseeDetails'])->name('advisee.details');
    Route::get('/advisee/{student}/academic-plan', [AdvisorController::class, 'academicPlan'])->name('advisee.plan');
    Route::get('/advisee/{student}/transcript', [AdvisorController::class, 'adviseeTranscript'])->name('advisee.transcript');
    Route::post('/advisee/{student}/notes', [AdvisorController::class, 'addNotes'])->name('advisee.notes');
    Route::get('/appointments', [AdvisorController::class, 'advisingAppointments'])->name('appointments');
    Route::get('/holds', [AdvisorController::class, 'advisingHolds'])->name('holds');
    Route::post('/holds/{hold}/release', [AdvisorController::class, 'releaseHold'])->name('holds.release');
});

// Simplified advisor routes (redirect to advising)
Route::prefix('advisor')->name('advisor.')->group(function () {
    Route::get('/advisees', [AdvisorController::class, 'advisees'])->name('advisees.index');
    Route::get('/dashboard', [AdvisorController::class, 'dashboard'])->name('dashboard');
});

// ============================================================
// LMS / COURSE CONTENT MANAGEMENT
// ============================================================
Route::prefix('lms')->name('lms.')->group(function () {
    // Course Materials
    Route::prefix('materials')->name('materials.')->group(function () {
        Route::get('/', [LMSController::class, 'materials'])->name('index');
        Route::get('/section/{section}', [LMSController::class, 'sectionMaterials'])->name('section');
        Route::get('/create/{section}', [LMSController::class, 'createMaterial'])->name('create');
        Route::post('/store/{section}', [LMSController::class, 'storeMaterial'])->name('store');
        Route::get('/{material}/edit', [LMSController::class, 'editMaterial'])->name('edit');
        Route::put('/{material}', [LMSController::class, 'updateMaterial'])->name('update');
        Route::delete('/{material}', [LMSController::class, 'deleteMaterial'])->name('delete');
        Route::get('/{material}/download', [LMSController::class, 'downloadMaterial'])->name('download');
    });
    
    // Assignments
    Route::prefix('assignments')->name('assignments.')->group(function () {
        Route::get('/', [LMSController::class, 'assignments'])->name('index');
        Route::get('/section/{section}', [LMSController::class, 'sectionAssignments'])->name('section');
        Route::get('/create/{section}', [LMSController::class, 'createAssignment'])->name('create');
        Route::post('/store/{section}', [LMSController::class, 'storeAssignment'])->name('store');
        Route::get('/{assignment}', [LMSController::class, 'assignmentDetails'])->name('show');
        Route::get('/{assignment}/edit', [LMSController::class, 'editAssignment'])->name('edit');
        Route::put('/{assignment}', [LMSController::class, 'updateAssignment'])->name('update');
        Route::delete('/{assignment}', [LMSController::class, 'deleteAssignment'])->name('delete');
        Route::get('/{assignment}/submissions', [LMSController::class, 'submissions'])->name('submissions');
        Route::get('/submission/{submission}', [LMSController::class, 'viewSubmission'])->name('submission.view');
        Route::post('/submission/{submission}/grade', [LMSController::class, 'gradeSubmission'])->name('submission.grade');
    });
    
    // Quizzes/Tests
    Route::prefix('quizzes')->name('quizzes.')->group(function () {
        Route::get('/', [LMSController::class, 'quizzes'])->name('index');
        Route::get('/section/{section}', [LMSController::class, 'sectionQuizzes'])->name('section');
        Route::get('/create/{section}', [LMSController::class, 'createQuiz'])->name('create');
        Route::post('/store/{section}', [LMSController::class, 'storeQuiz'])->name('store');
        Route::get('/{quiz}', [LMSController::class, 'quizDetails'])->name('show');
        Route::get('/{quiz}/edit', [LMSController::class, 'editQuiz'])->name('edit');
        Route::put('/{quiz}', [LMSController::class, 'updateQuiz'])->name('update');
        Route::delete('/{quiz}', [LMSController::class, 'deleteQuiz'])->name('delete');
        Route::get('/{quiz}/results', [LMSController::class, 'quizResults'])->name('results');
    });
    
    // Discussion Forums
    Route::prefix('discussions')->name('discussions.')->group(function () {
        Route::get('/section/{section}', [LMSController::class, 'discussions'])->name('section');
        Route::post('/section/{section}/create', [LMSController::class, 'createDiscussion'])->name('create');
        Route::get('/{discussion}', [LMSController::class, 'viewDiscussion'])->name('view');
        Route::post('/{discussion}/reply', [LMSController::class, 'replyDiscussion'])->name('reply');
    });
});

// ============================================================
// RESEARCH & PUBLICATIONS (Optional Module)
// ============================================================
Route::prefix('research')->name('research.')->group(function () {
    Route::get('/', [FacultyController::class, 'research'])->name('index');
    Route::get('/publications', [FacultyController::class, 'publications'])->name('publications');
    Route::get('/projects', [FacultyController::class, 'researchProjects'])->name('projects');
    Route::get('/grants', [FacultyController::class, 'grants'])->name('grants');
});

// ============================================================
// FACULTY REPORTS
// ============================================================
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [FacultyController::class, 'reports'])->name('index');
    Route::get('/teaching-load', [FacultyController::class, 'teachingLoad'])->name('teaching-load');
    Route::get('/student-performance', [FacultyController::class, 'studentPerformance'])->name('student-performance');
    Route::get('/attendance-summary', [FacultyController::class, 'attendanceSummary'])->name('attendance-summary');
    Route::get('/grade-distribution', [FacultyController::class, 'gradeDistribution'])->name('grade-distribution');
    Route::get('/custom', [FacultyController::class, 'customReport'])->name('custom');
    Route::post('/generate', [FacultyController::class, 'generateReport'])->name('generate');
});

// ============================================================
// FACULTY PROFILE & SETTINGS
// ============================================================
Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/', [FacultyController::class, 'settings'])->name('index');
    Route::get('/profile', [FacultyController::class, 'profileSettings'])->name('profile');
    Route::put('/profile', [FacultyController::class, 'updateProfile'])->name('profile.update');
    Route::get('/preferences', [FacultyController::class, 'preferences'])->name('preferences');
    Route::put('/preferences', [FacultyController::class, 'updatePreferences'])->name('preferences.update');
    Route::get('/notifications', [FacultyController::class, 'notificationSettings'])->name('notifications');
    Route::put('/notifications', [FacultyController::class, 'updateNotifications'])->name('notifications.update');
    Route::post('/photo', [FacultyController::class, 'updatePhoto'])->name('photo.update');
});