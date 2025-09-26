<?php
/**
 * IntelliCampus Examination Management Routes
 * 
 * Routes for exam administration, conducting, and evaluation.
 * These routes are automatically prefixed with 'exams' and named with 'exams.'
 * Base middleware: 'web', 'auth'
 */

use App\Http\Controllers\ExamAdminController;
use App\Http\Controllers\ExamConductController;
use App\Http\Controllers\ExamEvaluationController;
use App\Http\Controllers\ExamPortalController;
use Illuminate\Support\Facades\Route;

// ============================================================
// EXAM DASHBOARD
// ============================================================
Route::get('/', [ExamAdminController::class, 'dashboard'])->name('index');
Route::get('/dashboard', [ExamAdminController::class, 'dashboard'])->name('dashboard');
Route::get('/calendar', [ExamAdminController::class, 'examCalendar'])->name('calendar');
Route::get('/upcoming', [ExamAdminController::class, 'upcomingExams'])->name('upcoming');
Route::get('/statistics', [ExamAdminController::class, 'examStatistics'])->name('statistics');

// ============================================================
// EXAM MANAGEMENT (Admin/Coordinator)
// ============================================================
Route::prefix('manage')->name('manage.')->middleware('role:exam-coordinator,admin')->group(function () {
    Route::get('/', [ExamAdminController::class, 'manageExams'])->name('index');
    Route::get('/create', [ExamAdminController::class, 'create'])->name('create');
    Route::post('/', [ExamAdminController::class, 'store'])->name('store');
    Route::get('/{exam}', [ExamAdminController::class, 'show'])->name('show');
    Route::get('/{exam}/edit', [ExamAdminController::class, 'edit'])->name('edit');
    Route::put('/{exam}', [ExamAdminController::class, 'update'])->name('update');
    Route::delete('/{exam}', [ExamAdminController::class, 'destroy'])->name('delete');
    Route::post('/{exam}/publish', [ExamAdminController::class, 'publish'])->name('publish');
    Route::post('/{exam}/cancel', [ExamAdminController::class, 'cancel'])->name('cancel');
    Route::post('/{exam}/reschedule', [ExamAdminController::class, 'reschedule'])->name('reschedule');
    Route::post('/{exam}/clone', [ExamAdminController::class, 'cloneExam'])->name('clone');
    Route::get('/{exam}/export', [ExamAdminController::class, 'exportExamData'])->name('export');
});

// ============================================================
// EXAM TYPES & CONFIGURATIONS
// ============================================================
Route::prefix('types')->name('types.')->middleware('role:exam-coordinator,admin')->group(function () {
    Route::get('/', [ExamAdminController::class, 'examTypes'])->name('index');
    Route::post('/', [ExamAdminController::class, 'createExamType'])->name('create');
    Route::put('/{type}', [ExamAdminController::class, 'updateExamType'])->name('update');
    Route::delete('/{type}', [ExamAdminController::class, 'deleteExamType'])->name('delete');
    
    // Exam Formats
    Route::get('/formats', [ExamAdminController::class, 'examFormats'])->name('formats');
    Route::post('/format', [ExamAdminController::class, 'createFormat'])->name('format.create');
    Route::put('/format/{format}', [ExamAdminController::class, 'updateFormat'])->name('format.update');
    
    // Grading Schemes
    Route::get('/grading-schemes', [ExamAdminController::class, 'gradingSchemes'])->name('grading-schemes');
    Route::post('/grading-scheme', [ExamAdminController::class, 'createGradingScheme'])->name('grading-scheme.create');
    Route::put('/grading-scheme/{scheme}', [ExamAdminController::class, 'updateGradingScheme'])->name('grading-scheme.update');
});

// ============================================================
// QUESTION BANK
// ============================================================
Route::prefix('questions')->name('questions.')->middleware('role:faculty,exam-coordinator,admin')->group(function () {
    Route::get('/', [ExamAdminController::class, 'questionBank'])->name('index');
    Route::get('/search', [ExamAdminController::class, 'searchQuestions'])->name('search');
    Route::get('/create', [ExamAdminController::class, 'createQuestion'])->name('create');
    Route::post('/', [ExamAdminController::class, 'storeQuestion'])->name('store');
    Route::get('/{question}', [ExamAdminController::class, 'viewQuestion'])->name('view');
    Route::get('/{question}/edit', [ExamAdminController::class, 'editQuestion'])->name('edit');
    Route::put('/{question}', [ExamAdminController::class, 'updateQuestion'])->name('update');
    Route::delete('/{question}', [ExamAdminController::class, 'deleteQuestion'])->name('delete');
    
    // Question Categories
    Route::get('/categories', [ExamAdminController::class, 'questionCategories'])->name('categories');
    Route::post('/category', [ExamAdminController::class, 'createCategory'])->name('category.create');
    Route::put('/category/{category}', [ExamAdminController::class, 'updateCategory'])->name('category.update');
    Route::delete('/category/{category}', [ExamAdminController::class, 'deleteCategory'])->name('category.delete');
    
    // Question Import/Export
    Route::get('/import', [ExamAdminController::class, 'importForm'])->name('import.form');
    Route::post('/import', [ExamAdminController::class, 'importQuestions'])->name('import');
    Route::get('/export', [ExamAdminController::class, 'exportQuestions'])->name('export');
    Route::get('/template', [ExamAdminController::class, 'downloadTemplate'])->name('template');
    
    // Question Review
    Route::get('/review', [ExamAdminController::class, 'reviewQueue'])->name('review');
    Route::post('/{question}/approve', [ExamAdminController::class, 'approveQuestion'])->name('approve');
    Route::post('/{question}/reject', [ExamAdminController::class, 'rejectQuestion'])->name('reject');
    Route::post('/{question}/flag', [ExamAdminController::class, 'flagQuestion'])->name('flag');
    
    // Question Analytics
    Route::get('/analytics', [ExamAdminController::class, 'questionAnalytics'])->name('analytics');
    Route::get('/{question}/statistics', [ExamAdminController::class, 'questionStatistics'])->name('statistics');
    Route::get('/{question}/difficulty', [ExamAdminController::class, 'difficultyAnalysis'])->name('difficulty');
});

// ============================================================
// EXAM PAPERS
// ============================================================
Route::prefix('papers')->name('papers.')->middleware('role:faculty,exam-coordinator,admin')->group(function () {
    Route::get('/', [ExamAdminController::class, 'examPapers'])->name('index');
    Route::get('/generate/{exam}', [ExamAdminController::class, 'generatePaperForm'])->name('generate.form');
    Route::post('/generate', [ExamAdminController::class, 'generatePaper'])->name('generate');
    Route::get('/{paper}', [ExamAdminController::class, 'viewPaper'])->name('view');
    Route::get('/{paper}/preview', [ExamAdminController::class, 'previewPaper'])->name('preview');
    Route::get('/{paper}/edit', [ExamAdminController::class, 'editPaper'])->name('edit');
    Route::put('/{paper}', [ExamAdminController::class, 'updatePaper'])->name('update');
    Route::post('/{paper}/approve', [ExamAdminController::class, 'approvePaper'])->name('approve');
    Route::post('/{paper}/lock', [ExamAdminController::class, 'lockPaper'])->name('lock');
    Route::get('/{paper}/print', [ExamAdminController::class, 'printPaper'])->name('print');
    Route::post('/{paper}/seal', [ExamAdminController::class, 'sealPaper'])->name('seal');
    
    // Paper Sets
    Route::get('/sets/{exam}', [ExamAdminController::class, 'paperSets'])->name('sets');
    Route::post('/sets/generate', [ExamAdminController::class, 'generatePaperSets'])->name('sets.generate');
    Route::get('/sets/{set}/distribute', [ExamAdminController::class, 'distributeSets'])->name('sets.distribute');
    
    // Answer Keys
    Route::get('/{paper}/answer-key', [ExamAdminController::class, 'answerKey'])->name('answer-key');
    Route::post('/{paper}/answer-key', [ExamAdminController::class, 'saveAnswerKey'])->name('answer-key.save');
    Route::post('/{paper}/answer-key/publish', [ExamAdminController::class, 'publishAnswerKey'])->name('answer-key.publish');
});

// ============================================================
// EXAM CENTERS
// ============================================================
Route::prefix('centers')->name('centers.')->middleware('role:exam-coordinator,admin')->group(function () {
    Route::get('/', [ExamAdminController::class, 'examCenters'])->name('index');
    Route::get('/create', [ExamAdminController::class, 'createCenter'])->name('create');
    Route::post('/', [ExamAdminController::class, 'storeCenter'])->name('store');
    Route::get('/{center}', [ExamAdminController::class, 'viewCenter'])->name('view');
    Route::get('/{center}/edit', [ExamAdminController::class, 'editCenter'])->name('edit');
    Route::put('/{center}', [ExamAdminController::class, 'updateCenter'])->name('update');
    Route::delete('/{center}', [ExamAdminController::class, 'deleteCenter'])->name('delete');
    Route::get('/{center}/capacity', [ExamAdminController::class, 'centerCapacity'])->name('capacity');
    Route::post('/{center}/allocate', [ExamAdminController::class, 'allocateSeats'])->name('allocate');
    Route::get('/map', [ExamAdminController::class, 'centersMap'])->name('map');
    
    // Center Staff
    Route::get('/{center}/staff', [ExamAdminController::class, 'centerStaff'])->name('staff');
    Route::post('/{center}/staff/assign', [ExamAdminController::class, 'assignStaff'])->name('staff.assign');
    Route::delete('/{center}/staff/{staff}', [ExamAdminController::class, 'removeStaff'])->name('staff.remove');
});

// ============================================================
// EXAM SESSIONS
// ============================================================
Route::prefix('sessions')->name('sessions.')->middleware('role:exam-coordinator,admin')->group(function () {
    Route::get('/', [ExamAdminController::class, 'examSessions'])->name('index');
    Route::get('/{exam}/create', [ExamAdminController::class, 'createSession'])->name('create');
    Route::post('/{exam}', [ExamAdminController::class, 'storeSession'])->name('store');
    Route::get('/{session}', [ExamAdminController::class, 'viewSession'])->name('view');
    Route::put('/{session}', [ExamAdminController::class, 'updateSession'])->name('update');
    Route::delete('/{session}', [ExamAdminController::class, 'deleteSession'])->name('delete');
    Route::post('/{session}/allocate', [ExamAdminController::class, 'allocateStudents'])->name('allocate');
    Route::get('/{session}/seating', [ExamAdminController::class, 'seatingArrangement'])->name('seating');
    Route::post('/{session}/seating/generate', [ExamAdminController::class, 'generateSeating'])->name('seating.generate');
    Route::get('/{session}/seating/export', [ExamAdminController::class, 'exportSeating'])->name('seating.export');
});

// ============================================================
// EXAM REGISTRATIONS
// ============================================================
Route::prefix('registrations')->name('registrations.')->group(function () {
    Route::get('/', [ExamAdminController::class, 'registrations'])->name('index');
    Route::get('/{exam}', [ExamAdminController::class, 'examRegistrations'])->name('exam');
    Route::get('/registration/{registration}', [ExamAdminController::class, 'viewRegistration'])->name('view');
    
    // Registration Management (Admin)
    Route::middleware('role:exam-coordinator,admin')->group(function () {
        Route::post('/registration/{registration}/approve', [ExamAdminController::class, 'approveRegistration'])->name('approve');
        Route::post('/registration/{registration}/reject', [ExamAdminController::class, 'rejectRegistration'])->name('reject');
        Route::post('/bulk-approve', [ExamAdminController::class, 'bulkApprove'])->name('bulk-approve');
        Route::post('/import/{exam}', [ExamAdminController::class, 'importRegistrations'])->name('import');
        Route::get('/export/{exam}', [ExamAdminController::class, 'exportRegistrations'])->name('export');
        
        // Hall Tickets
        Route::get('/hall-tickets/{exam}', [ExamAdminController::class, 'hallTickets'])->name('hall-tickets');
        Route::post('/hall-tickets/{exam}/generate', [ExamAdminController::class, 'generateHallTickets'])->name('hall-tickets.generate');
        Route::post('/hall-tickets/{exam}/send', [ExamAdminController::class, 'sendHallTickets'])->name('hall-tickets.send');
        Route::get('/hall-ticket/{registration}/print', [ExamAdminController::class, 'printHallTicket'])->name('hall-ticket.print');
    });
    
    // Student Registration
    Route::middleware('role:student')->group(function () {
        Route::get('/available', [ExamPortalController::class, 'availableExams'])->name('available');
        Route::get('/register/{exam}', [ExamPortalController::class, 'registerForm'])->name('register');
        Route::post('/register/{exam}', [ExamPortalController::class, 'submitRegistration'])->name('register.submit');
        Route::get('/my-registrations', [ExamPortalController::class, 'myRegistrations'])->name('my');
        Route::get('/hall-ticket/{registration}', [ExamPortalController::class, 'downloadHallTicket'])->name('hall-ticket');
    });
});

// ============================================================
// EXAM CONDUCT
// ============================================================
Route::prefix('conduct')->name('conduct.')->middleware('role:proctor,exam-coordinator,admin')->group(function () {
    Route::get('/{session}', [ExamConductController::class, 'sessionDashboard'])->name('session');
    
    // Attendance
    Route::get('/{session}/attendance', [ExamConductController::class, 'attendanceSheet'])->name('attendance');
    Route::post('/{session}/attendance/mark', [ExamConductController::class, 'markAttendance'])->name('attendance.mark');
    Route::get('/{session}/attendance/export', [ExamConductController::class, 'exportAttendance'])->name('attendance.export');
    Route::post('/{session}/attendance/verify', [ExamConductController::class, 'verifyAttendance'])->name('attendance.verify');
    
    // Paper Distribution
    Route::get('/{session}/papers', [ExamConductController::class, 'paperDistribution'])->name('papers');
    Route::post('/{session}/papers/distribute', [ExamConductController::class, 'distributePapers'])->name('papers.distribute');
    Route::post('/{session}/papers/collect', [ExamConductController::class, 'collectPapers'])->name('papers.collect');
    Route::get('/{session}/papers/status', [ExamConductController::class, 'paperStatus'])->name('papers.status');
    
    // Monitoring
    Route::get('/{session}/monitor', [ExamConductController::class, 'monitorSession'])->name('monitor');
    Route::get('/{session}/live-status', [ExamConductController::class, 'liveStatus'])->name('live-status');
    Route::post('/{session}/incident', [ExamConductController::class, 'reportIncident'])->name('incident');
    Route::get('/{session}/incidents', [ExamConductController::class, 'viewIncidents'])->name('incidents');
    Route::post('/incident/{incident}/resolve', [ExamConductController::class, 'resolveIncident'])->name('incident.resolve');
    
    // Candidate Verification
    Route::post('/verify-candidate', [ExamConductController::class, 'verifyCandidate'])->name('verify-candidate');
    Route::post('/biometric-verify', [ExamConductController::class, 'biometricVerification'])->name('biometric-verify');
    Route::post('/photo-capture/{registration}', [ExamConductController::class, 'capturePhoto'])->name('photo-capture');
    
    // Proctoring
    Route::get('/{session}/proctoring', [ExamConductController::class, 'proctoringDashboard'])->name('proctoring');
    Route::post('/{session}/proctor/assign', [ExamConductController::class, 'assignProctor'])->name('proctor.assign');
    Route::get('/{session}/proctor/duties', [ExamConductController::class, 'proctorDuties'])->name('proctor.duties');
    Route::post('/violation/{registration}', [ExamConductController::class, 'reportViolation'])->name('violation');
    
    // Session Completion
    Route::post('/{session}/complete', [ExamConductController::class, 'completeSession'])->name('complete');
    Route::get('/{session}/report', [ExamConductController::class, 'sessionReport'])->name('report');
});

// ============================================================
// ONLINE EXAM PLATFORM
// ============================================================
Route::prefix('online')->name('online.')->group(function () {
    // Exam Taking (Student)
    Route::middleware('role:student')->group(function () {
        Route::get('/{registration}/ready', [ExamPortalController::class, 'examReady'])->name('ready');
        Route::post('/{registration}/start', [ExamPortalController::class, 'startExam'])->name('start');
        Route::get('/{registration}/exam', [ExamPortalController::class, 'examInterface'])->name('exam');
        Route::post('/save-answer', [ExamPortalController::class, 'saveAnswer'])->name('save-answer');
        Route::post('/mark-review', [ExamPortalController::class, 'markForReview'])->name('mark-review');
        Route::post('/clear-response', [ExamPortalController::class, 'clearResponse'])->name('clear-response');
        Route::get('/{registration}/question/{number}', [ExamPortalController::class, 'getQuestion'])->name('question');
        Route::get('/{registration}/summary', [ExamPortalController::class, 'examSummary'])->name('summary');
        Route::post('/{registration}/submit', [ExamPortalController::class, 'submitExam'])->name('submit');
        Route::get('/{registration}/complete', [ExamPortalController::class, 'examComplete'])->name('complete');
        Route::get('/{registration}/review', [ExamPortalController::class, 'reviewAnswers'])->name('review');
    });
    
    // Online Monitoring (Admin)
    Route::middleware('role:proctor,exam-coordinator,admin')->group(function () {
        Route::get('/monitor', [ExamConductController::class, 'onlineMonitor'])->name('monitor');
        Route::get('/monitor/{session}', [ExamConductController::class, 'monitorOnlineSession'])->name('monitor.session');
        Route::get('/candidates/{session}', [ExamConductController::class, 'onlineCandidates'])->name('candidates');
        Route::get('/candidate/{registration}/screen', [ExamConductController::class, 'viewCandidateScreen'])->name('candidate.screen');
        Route::get('/candidate/{registration}/activity', [ExamConductController::class, 'candidateActivity'])->name('candidate.activity');
        Route::post('/candidate/{registration}/flag', [ExamConductController::class, 'flagCandidate'])->name('candidate.flag');
        Route::post('/candidate/{registration}/terminate', [ExamConductController::class, 'terminateExam'])->name('candidate.terminate');
        Route::get('/analytics/{session}', [ExamConductController::class, 'onlineAnalytics'])->name('analytics');
    });
});

// ============================================================
// EVALUATION & GRADING
// ============================================================
Route::prefix('evaluation')->name('evaluation.')->middleware('role:evaluator,faculty,exam-coordinator,admin')->group(function () {
    Route::get('/', [ExamEvaluationController::class, 'dashboard'])->name('index');
    Route::get('/{exam}', [ExamEvaluationController::class, 'examEvaluation'])->name('exam');
    
    // Answer Scripts
    Route::get('/{exam}/scripts', [ExamEvaluationController::class, 'answerScripts'])->name('scripts');
    Route::get('/script/{script}', [ExamEvaluationController::class, 'viewScript'])->name('script.view');
    Route::post('/assign', [ExamEvaluationController::class, 'assignEvaluator'])->name('assign');
    Route::get('/my-assignments', [ExamEvaluationController::class, 'myAssignments'])->name('my-assignments');
    
    // Evaluation Process
    Route::get('/evaluate/{script}', [ExamEvaluationController::class, 'evaluateScript'])->name('evaluate');
    Route::post('/evaluate/{script}', [ExamEvaluationController::class, 'saveEvaluation'])->name('save');
    Route::post('/evaluate/{script}/submit', [ExamEvaluationController::class, 'submitEvaluation'])->name('submit');
    Route::get('/rubric/{exam}', [ExamEvaluationController::class, 'evaluationRubric'])->name('rubric');
    
    // Auto Evaluation
    Route::post('/{exam}/auto-evaluate', [ExamEvaluationController::class, 'autoEvaluate'])->name('auto');
    Route::get('/{exam}/auto-status', [ExamEvaluationController::class, 'autoEvaluationStatus'])->name('auto.status');
    Route::post('/{exam}/ocr-process', [ExamEvaluationController::class, 'processOCR'])->name('ocr');
    
    // Moderation
    Route::get('/moderation/{exam}', [ExamEvaluationController::class, 'moderationQueue'])->name('moderation');
    Route::get('/moderate/{script}', [ExamEvaluationController::class, 'moderateScript'])->name('moderate');
    Route::post('/moderate/{script}', [ExamEvaluationController::class, 'saveModerationadjacentHTML'])->name('moderate.save');
    Route::post('/moderation/batch', [ExamEvaluationController::class, 'batchModeration'])->name('moderation.batch');
    
    // Revaluation
    Route::get('/revaluation', [ExamEvaluationController::class, 'revaluationRequests'])->name('revaluation');
    Route::get('/revaluation/{request}', [ExamEvaluationController::class, 'viewRevaluationRequest'])->name('revaluation.view');
    Route::post('/revaluation/{request}/process', [ExamEvaluationController::class, 'processRevaluation'])->name('revaluation.process');
    Route::post('/revaluation/{request}/complete', [ExamEvaluationController::class, 'completeRevaluation'])->name('revaluation.complete');
});

// ============================================================
// RESULTS
// ============================================================
Route::prefix('results')->name('results.')->group(function () {
    // Result Processing (Admin)
    Route::middleware('role:exam-coordinator,admin')->group(function () {
        Route::get('/processing', [ExamEvaluationController::class, 'resultProcessing'])->name('processing');
        Route::post('/{exam}/calculate', [ExamEvaluationController::class, 'calculateResults'])->name('calculate');
        Route::get('/{exam}/preview', [ExamEvaluationController::class, 'previewResults'])->name('preview');
        Route::post('/{exam}/normalize', [ExamEvaluationController::class, 'normalizeScores'])->name('normalize');
        Route::post('/{exam}/generate-ranks', [ExamEvaluationController::class, 'generateRanks'])->name('ranks');
        Route::post('/{exam}/approve', [ExamEvaluationController::class, 'approveResults'])->name('approve');
        Route::post('/{exam}/publish', [ExamEvaluationController::class, 'publishResults'])->name('publish');
        Route::get('/{exam}/export', [ExamEvaluationController::class, 'exportResults'])->name('export');
        
        // Grade Boundaries
        Route::get('/{exam}/boundaries', [ExamEvaluationController::class, 'gradeBoundaries'])->name('boundaries');
        Route::post('/{exam}/boundaries', [ExamEvaluationController::class, 'setGradeBoundaries'])->name('boundaries.set');
        Route::get('/{exam}/distribution', [ExamEvaluationController::class, 'gradeDistribution'])->name('distribution');
    });
    
    // Student Results
    Route::middleware('role:student')->group(function () {
        Route::get('/my-results', [ExamPortalController::class, 'myResults'])->name('my');
        Route::get('/{registration}', [ExamPortalController::class, 'viewResult'])->name('view');
        Route::get('/{registration}/scorecard', [ExamPortalController::class, 'scorecard'])->name('scorecard');
        Route::get('/{registration}/download', [ExamPortalController::class, 'downloadScorecard'])->name('download');
        Route::post('/{registration}/revaluation', [ExamPortalController::class, 'requestRevaluation'])->name('revaluation.request');
    });
    
    // Public Results
    Route::get('/check', [ExamPortalController::class, 'checkResultForm'])->name('check');
    Route::post('/check', [ExamPortalController::class, 'checkResult'])->name('check.submit');
    Route::get('/statistics/{exam}', [ExamPortalController::class, 'resultStatistics'])->name('statistics');
});

// ============================================================
// ANSWER KEY & CHALLENGES
// ============================================================
Route::prefix('answer-keys')->name('answer-keys.')->group(function () {
    Route::get('/{exam}', [ExamEvaluationController::class, 'answerKeys'])->name('index');
    Route::post('/{exam}/upload', [ExamEvaluationController::class, 'uploadAnswerKey'])->name('upload');
    Route::post('/{exam}/publish', [ExamEvaluationController::class, 'publishAnswerKey'])->name('publish');
    Route::get('/{exam}/view', [ExamEvaluationController::class, 'viewAnswerKey'])->name('view');
    
    // Challenges
    Route::get('/{exam}/challenges', [ExamEvaluationController::class, 'challenges'])->name('challenges');
    Route::post('/{exam}/challenge', [ExamPortalController::class, 'submitChallenge'])->name('challenge.submit');
    Route::get('/challenge/{challenge}', [ExamEvaluationController::class, 'viewChallenge'])->name('challenge.view');
    Route::post('/challenge/{challenge}/review', [ExamEvaluationController::class, 'reviewChallenge'])->name('challenge.review');
    Route::post('/challenge/{challenge}/accept', [ExamEvaluationController::class, 'acceptChallenge'])->name('challenge.accept');
    Route::post('/challenge/{challenge}/reject', [ExamEvaluationController::class, 'rejectChallenge'])->name('challenge.reject');
});

// ============================================================
// REPORTS & ANALYTICS
// ============================================================
Route::prefix('reports')->name('reports.')->middleware('role:exam-coordinator,admin')->group(function () {
    Route::get('/', [ExamAdminController::class, 'reportsHub'])->name('index');
    Route::get('/performance/{exam}', [ExamAdminController::class, 'performanceReport'])->name('performance');
    Route::get('/attendance/{exam}', [ExamAdminController::class, 'attendanceReport'])->name('attendance');
    Route::get('/center-wise/{exam}', [ExamAdminController::class, 'centerWiseReport'])->name('center-wise');
    Route::get('/item-analysis/{exam}', [ExamAdminController::class, 'itemAnalysisReport'])->name('item-analysis');
    Route::get('/comparative', [ExamAdminController::class, 'comparativeAnalysis'])->name('comparative');
    Route::get('/trends', [ExamAdminController::class, 'trendAnalysis'])->name('trends');
    Route::get('/feedback/{exam}', [ExamAdminController::class, 'feedbackReport'])->name('feedback');
    Route::post('/custom', [ExamAdminController::class, 'generateCustomReport'])->name('custom');
    Route::post('/export', [ExamAdminController::class, 'exportReport'])->name('export');
});