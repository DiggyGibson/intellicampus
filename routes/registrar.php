<?php
/**
 * IntelliCampus Registrar Routes
 * 
 * Routes for registrar office operations and student records management.
 * These routes are automatically prefixed with 'registrar' and named with 'registrar.'
 * Applied middleware: 'web', 'auth', 'role:registrar,academic-administrator'
 */

use App\Http\Controllers\StudentController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\TranscriptController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\DegreeAuditController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DocumentVerificationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

// ============================================================
// REGISTRAR DASHBOARD
// ============================================================
Route::get('/', function() {
    return redirect()->route('registrar.dashboard');
});

Route::get('/dashboard', function() {
    // Gather dashboard statistics
    $students = \App\Models\Student::count();
    $activeStudents = \App\Models\Student::where('enrollment_status', 'active')->count();
    $activeEnrollments = DB::table('enrollments')
        ->join('academic_terms', 'enrollments.term_id', '=', 'academic_terms.id')
        ->where('academic_terms.is_current', true)
        ->where('enrollments.enrollment_status', 'enrolled')
        ->count();
    $pendingTranscripts = DB::table('transcript_requests')
        ->where('status', 'pending')
        ->count();
    $recentGradeChanges = DB::table('grade_change_requests')
        ->where('status', 'pending')
        ->count();
    $graduationCandidates = DB::table('students')
        ->where('expected_graduation_date', '>=', now())
        ->where('expected_graduation_date', '<=', now()->addMonths(6))
        ->count();
    
    return view('registrar.dashboard', compact(
        'students',
        'activeStudents', 
        'activeEnrollments', 
        'pendingTranscripts',
        'recentGradeChanges',
        'graduationCandidates'
    ));
})->name('dashboard');

// ============================================================
// STUDENT RECORDS MANAGEMENT
// ============================================================
Route::prefix('students')->name('students.')->group(function () {
    // Use existing StudentController views
    Route::get('/', [StudentController::class, 'index'])->name('index');
    Route::get('/search', [StudentController::class, 'index'])->name('search');
    Route::get('/create', [StudentController::class, 'create'])->name('create');
    Route::post('/', [StudentController::class, 'store'])->name('store');
    Route::get('/{student}', [StudentController::class, 'show'])->name('show');
    Route::get('/{student}/edit', [StudentController::class, 'edit'])->name('edit');
    Route::put('/{student}', [StudentController::class, 'update'])->name('update');
    Route::delete('/{student}', [StudentController::class, 'destroy'])->name('destroy');
    
    // Academic Records
    Route::get('/{student}/academic-record', [StudentController::class, 'academicRecord'])->name('academic-record');
    Route::get('/{student}/enrollment-history', [EnrollmentController::class, 'history'])->name('enrollment-history');
    Route::get('/{student}/grade-history', [GradeController::class, 'studentHistory'])->name('grade-history');
    
    // Status Management
    Route::post('/{student}/status', [StudentController::class, 'updateStatus'])->name('update-status');
    Route::post('/{student}/hold', [StudentController::class, 'addHold'])->name('add-hold');
    Route::delete('/{student}/hold/{hold}', [StudentController::class, 'removeHold'])->name('remove-hold');
    
    // Import/Export
    Route::get('/import', [StudentController::class, 'importForm'])->name('import');
    Route::post('/import', [StudentController::class, 'import'])->name('import.process');
    Route::get('/export', [StudentController::class, 'export'])->name('export');
});

// ============================================================
// ENROLLMENT MANAGEMENT
// ============================================================
Route::prefix('enrollment')->name('enrollment.')->group(function () {
    // Use existing enrollment views
    Route::get('/', function() {
        return view('students.index'); // Reuse students index with enrollment filter
    })->name('index');
    
    Route::get('/verification', function() {
        $pendingVerifications = DB::table('enrollment_verifications')
            ->where('status', 'pending')
            ->get();
        return view('registrar.enrollment-verification', compact('pendingVerifications'));
    })->name('verification');
    
    Route::get('/{student}/history', [EnrollmentController::class, 'history'])->name('history');
    Route::get('/{student}/manage', [EnrollmentController::class, 'manage'])->name('manage');
    Route::post('/{student}/enroll', [EnrollmentController::class, 'enroll'])->name('enroll');
    Route::post('/{student}/drop', [EnrollmentController::class, 'drop'])->name('drop');
    Route::post('/{student}/withdraw', [EnrollmentController::class, 'withdraw'])->name('withdraw');
    
    // Verification Actions
    Route::post('/verify/{verification}', [EnrollmentController::class, 'verify'])->name('verify');
    Route::get('/certificate/{student}', [EnrollmentController::class, 'certificate'])->name('certificate');
    
    // Statistics
    Route::get('/statistics', function() {
        $stats = [
            'total_enrolled' => DB::table('enrollments')->where('enrollment_status', 'enrolled')->count(),
            'by_program' => DB::table('enrollments')
                ->join('students', 'enrollments.student_id', '=', 'students.id')
                ->join('academic_programs', 'students.program_id', '=', 'academic_programs.id')
                ->select('academic_programs.name', DB::raw('count(*) as count'))
                ->where('enrollments.enrollment_status', 'enrolled')
                ->groupBy('academic_programs.name')
                ->get(),
        ];
        return view('registrar.enrollment-statistics', compact('stats'));
    })->name('statistics');
});

// ============================================================
// GRADE MANAGEMENT
// ============================================================
Route::prefix('grades')->name('grades.')->group(function () {
    // Use existing grade views
    Route::get('/changes', function() {
        $changeRequests = DB::table('grade_change_requests')
            ->join('students', 'grade_change_requests.student_id', '=', 'students.id')
            ->join('courses', 'grade_change_requests.course_id', '=', 'courses.id')
            ->select('grade_change_requests.*', 'students.student_id', 'students.name', 'courses.code')
            ->orderBy('grade_change_requests.created_at', 'desc')
            ->paginate(20);
        return view('grades.change-request', compact('changeRequests'));
    })->name('changes');
    
    Route::get('/history', function() {
        return view('grades.history');
    })->name('history');
    
    Route::post('/approve/{request}', [GradeController::class, 'approveChange'])->name('approve-change');
    Route::post('/reject/{request}', [GradeController::class, 'rejectChange'])->name('reject-change');
    Route::get('/audit-trail', [GradeController::class, 'auditTrail'])->name('audit-trail');
});

// ============================================================
// TRANSCRIPT MANAGEMENT
// ============================================================
Route::prefix('transcripts')->name('transcripts.')->group(function () {
    // Use existing transcript views
    Route::get('/', [TranscriptController::class, 'adminIndex'])->name('admin');
    Route::get('/requests', [TranscriptController::class, 'adminRequests'])->name('requests');
    Route::get('/pending', function() {
        $requests = DB::table('transcript_requests')
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->paginate(20);
        return view('transcripts.admin-request', compact('requests'));
    })->name('pending');
    
    Route::post('/process/{request}', [TranscriptController::class, 'process'])->name('process');
    Route::post('/generate/{student}', [TranscriptController::class, 'generate'])->name('generate');
    Route::get('/preview/{student}', [TranscriptController::class, 'preview'])->name('preview');
    Route::get('/download/{transcript}', [TranscriptController::class, 'download'])->name('download');
});

// ============================================================
// GRADUATION & DEGREE VERIFICATION
// ============================================================
Route::prefix('graduation')->name('graduation.')->group(function () {
    Route::get('/candidates', function() {
        $candidates = DB::table('students')
            ->where('expected_graduation_date', '>=', now())
            ->where('expected_graduation_date', '<=', now()->addMonths(6))
            ->orderBy('expected_graduation_date')
            ->get();
        return view('degree-audit.graduation.check', compact('candidates'));
    })->name('candidates');
    
    Route::get('/verify/{student}', [DegreeAuditController::class, 'verifyGraduation'])->name('verify');
    Route::post('/clear/{student}', [DegreeAuditController::class, 'clearForGraduation'])->name('clear');
    Route::get('/checklist/{student}', [DegreeAuditController::class, 'graduationChecklist'])->name('checklist');
});

// ============================================================
// DOCUMENT VERIFICATION
// ============================================================
Route::prefix('documents')->name('documents.')->group(function () {
    Route::get('/verification-requests', [DocumentVerificationController::class, 'index'])->name('verification');
    Route::post('/verify/{document}', [DocumentVerificationController::class, 'verify'])->name('verify');
    Route::get('/generate-letter/{type}/{student}', [DocumentVerificationController::class, 'generateLetter'])->name('generate-letter');
});

// ============================================================
// REPORTS
// ============================================================
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', function() {
        return view('registrar.reports.index');
    })->name('index');
    
    Route::get('/enrollment', [ReportController::class, 'enrollment'])->name('enrollment');
    Route::get('/academic-standing', [ReportController::class, 'academicStanding'])->name('academic-standing');
    Route::get('/graduation-statistics', [ReportController::class, 'graduationStats'])->name('graduation-stats');
    Route::get('/retention', [ReportController::class, 'retention'])->name('retention');
    Route::get('/grade-distribution', [ReportController::class, 'gradeDistribution'])->name('grade-distribution');
    Route::post('/generate', [ReportController::class, 'generate'])->name('generate');
    Route::get('/download/{report}', [ReportController::class, 'download'])->name('download');
});

// ============================================================
// NAME CHANGES & CORRECTIONS
// ============================================================
Route::prefix('name-changes')->name('name-changes.')->group(function () {
    Route::get('/pending', function() {
        $requests = DB::table('name_change_requests')
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();
        return view('registrar.name-changes', compact('requests'));
    })->name('pending');
    
    Route::post('/approve/{request}', [StudentController::class, 'approveNameChange'])->name('approve');
    Route::post('/reject/{request}', [StudentController::class, 'rejectNameChange'])->name('reject');
});