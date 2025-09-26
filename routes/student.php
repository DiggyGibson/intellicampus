<?php
/**
 * IntelliCampus Student Portal Routes
 * 
 * All routes specific to the student portal and student functionalities.
 * These routes are automatically prefixed with 'student' and named with 'student.'
 */

use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentGradeController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\RegistrationOverrideController;
use App\Http\Controllers\TranscriptController;
use App\Http\Controllers\FinancialController;
use App\Http\Controllers\DegreeAuditController;
use App\Http\Controllers\AcademicPlanController;
use App\Http\Controllers\WhatIfAnalysisController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LMSController;
use App\Http\Controllers\EnrollmentPortalController;
use Illuminate\Support\Facades\Route;

// All routes here are prefixed with 'student' and require auth + verified middleware
// Applied via RouteServiceProvider

// ============================================================
// STUDENT DASHBOARD
// ============================================================
Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
Route::get('/home', [StudentController::class, 'dashboard'])->name('home');
Route::get('/', function() {
    return redirect()->route('student.dashboard');
});

// Quick Stats Widget Data (AJAX)
Route::get('/dashboard/stats', [StudentController::class, 'dashboardStats'])->name('dashboard.stats');
Route::get('/dashboard/announcements', [StudentController::class, 'dashboardAnnouncements'])->name('dashboard.announcements');
Route::get('/dashboard/upcoming', [StudentController::class, 'dashboardUpcoming'])->name('dashboard.upcoming');

// ============================================================
// STUDENT PROFILE & INFORMATION
// ============================================================
Route::prefix('profile')->name('profile.')->group(function () {
    Route::get('/', [StudentController::class, 'profile'])->name('index');
    Route::get('/edit', [StudentController::class, 'editProfile'])->name('edit');
    Route::put('/update', [StudentController::class, 'updateProfile'])->name('update');
    Route::post('/photo', [StudentController::class, 'updatePhoto'])->name('photo.update');
    Route::get('/id-card', [StudentController::class, 'idCard'])->name('id-card');
    Route::get('/id-card/download', [StudentController::class, 'downloadIdCard'])->name('id-card.download');
    
    // Contact Information
    Route::get('/contact', [StudentController::class, 'contactInfo'])->name('contact');
    Route::put('/contact', [StudentController::class, 'updateContact'])->name('contact.update');
    
    // Emergency Contacts
    Route::get('/emergency', [StudentController::class, 'emergencyContacts'])->name('emergency');
    Route::post('/emergency', [StudentController::class, 'addEmergencyContact'])->name('emergency.add');
    Route::put('/emergency/{id}', [StudentController::class, 'updateEmergencyContact'])->name('emergency.update');
    Route::delete('/emergency/{id}', [StudentController::class, 'deleteEmergencyContact'])->name('emergency.delete');
});

// ============================================================
// GRADES & GPA - CRITICAL SECTION WITH MISSING ROUTES
// ============================================================
Route::prefix('grades')->name('grades.')->group(function () {
    // Main grades routes
    Route::get('/', [StudentGradeController::class, 'index'])->name('index');
    Route::get('/current', [StudentGradeController::class, 'currentGrades'])->name('current');
    Route::get('/history', [StudentGradeController::class, 'gradeHistory'])->name('history');
    Route::get('/term/{term}', [StudentGradeController::class, 'termGrades'])->name('term');
    Route::get('/course/{enrollment}', [StudentGradeController::class, 'courseGradeDetails'])->name('course');
    Route::get('/print/{term?}', [StudentGradeController::class, 'printGrades'])->name('print');
    
    // GPA routes - FIXED NAMING
    Route::get('/gpa', [StudentGradeController::class, 'gpaDetails'])->name('gpa');
    Route::get('/gpa-calculator', [StudentGradeController::class, 'gpaCalculator'])->name('gpa-calculator');
    Route::post('/gpa-calculate', [StudentGradeController::class, 'calculateGPA'])->name('gpa-calculate');
    Route::get('/gpa-projection', [StudentGradeController::class, 'gpaProjection'])->name('gpa-projection');
    
    // Academic Standing
    Route::get('/standing', [StudentGradeController::class, 'academicStanding'])->name('standing');
    Route::get('/honors', [StudentGradeController::class, 'honorsStatus'])->name('honors');
    Route::get('/probation', [StudentGradeController::class, 'probationStatus'])->name('probation');
});

// ============================================================
// ACADEMIC INFORMATION (Alternative access to grades)
// ============================================================
Route::prefix('academics')->name('academics.')->group(function () {
    Route::get('/', [StudentGradeController::class, 'academicOverview'])->name('index');
    
    // Redirect routes for backward compatibility
    Route::get('/grades', function() {
        return redirect()->route('student.grades.index');
    })->name('grades');
    Route::get('/grades/current', function() {
        return redirect()->route('student.grades.current');
    })->name('grades.current');
    Route::get('/grades/history', function() {
        return redirect()->route('student.grades.history');
    })->name('grades.history');
    Route::get('/gpa', function() {
        return redirect()->route('student.grades.gpa');
    })->name('gpa');
});

// ============================================================
// COURSE REGISTRATION
// ============================================================
Route::prefix('registration')->name('registration.')->group(function () {
    Route::get('/', [RegistrationController::class, 'studentDashboard'])->name('index');
    
    // Course Catalog & Search
    Route::get('/catalog', [RegistrationController::class, 'catalog'])->name('catalog');
    Route::get('/search', [RegistrationController::class, 'searchCourses'])->name('search');
    Route::get('/course/{course}', [RegistrationController::class, 'courseDetails'])->name('course.details');
    Route::get('/course/{course}/sections', [RegistrationController::class, 'courseSections'])->name('course.sections');
    Route::get('/section/{section}', [RegistrationController::class, 'sectionDetails'])->name('section.details');
    
    // Registration Cart
    Route::get('/cart', [RegistrationController::class, 'viewCart'])->name('cart');
    Route::post('/cart/add', [RegistrationController::class, 'addToCart'])->name('cart.add');
    Route::delete('/cart/remove/{item}', [RegistrationController::class, 'removeFromCart'])->name('cart.remove');
    Route::post('/cart/clear', [RegistrationController::class, 'clearCart'])->name('cart.clear');
    Route::post('/cart/validate', [RegistrationController::class, 'validateCart'])->name('cart.validate');
    
    // Registration Process
    Route::post('/register', [RegistrationController::class, 'register'])->name('register');
    Route::get('/confirmation', [RegistrationController::class, 'confirmation'])->name('confirmation');
    Route::get('/receipt/{registration}', [RegistrationController::class, 'receipt'])->name('receipt');
    
    // Current Schedule
    Route::get('/schedule', [RegistrationController::class, 'schedule'])->name('schedule');
    Route::get('/schedule/print', [RegistrationController::class, 'printSchedule'])->name('schedule.print');
    Route::get('/schedule/export', [RegistrationController::class, 'exportSchedule'])->name('schedule.export');
    Route::get('/schedule/calendar', [RegistrationController::class, 'scheduleCalendar'])->name('schedule.calendar');
    
    // Drop/Add/Swap
    Route::post('/drop', [RegistrationController::class, 'dropCourse'])->name('drop');
    Route::post('/swap', [RegistrationController::class, 'swapSection'])->name('swap');
    Route::get('/add-drop', [RegistrationController::class, 'addDropForm'])->name('add-drop');
    Route::post('/withdraw', [RegistrationController::class, 'withdrawFromCourse'])->name('withdraw');
    
    // Waitlist
    Route::get('/waitlist', [RegistrationController::class, 'waitlist'])->name('waitlist');
    Route::post('/waitlist/join', [RegistrationController::class, 'joinWaitlist'])->name('waitlist.join');
    Route::delete('/waitlist/leave/{waitlist}', [RegistrationController::class, 'leaveWaitlist'])->name('waitlist.leave');
    Route::get('/waitlist/position/{waitlist}', [RegistrationController::class, 'waitlistPosition'])->name('waitlist.position');
    
    // Registration History
    Route::get('/history', [RegistrationController::class, 'viewHistory'])->name('history');
    Route::get('/history/{term}', [RegistrationController::class, 'termHistory'])->name('history.term');
});

// ============================================================
// REGISTRATION OVERRIDES
// ============================================================
Route::prefix('overrides')->name('overrides.')->group(function () {
    Route::get('/', [RegistrationOverrideController::class, 'studentDashboard'])->name('index');
    Route::get('/available', [RegistrationOverrideController::class, 'checkOverrides'])->name('available');
    Route::get('/request', [RegistrationOverrideController::class, 'showCreateForm'])->name('request');
    Route::post('/request', [RegistrationOverrideController::class, 'create'])->name('request.submit');
    Route::get('/my-requests', [RegistrationOverrideController::class, 'myRequests'])->name('my-requests');
    Route::get('/request/{id}', [RegistrationOverrideController::class, 'show'])->name('show');
    Route::delete('/request/{id}', [RegistrationOverrideController::class, 'cancel'])->name('cancel');
    Route::get('/history', [RegistrationOverrideController::class, 'history'])->name('history');
    Route::get('/history/export', [RegistrationOverrideController::class, 'exportHistory'])->name('history.export');
    Route::post('/use/{override}', [RegistrationOverrideController::class, 'useOverride'])->name('use');
});

// ============================================================
// DEGREE AUDIT & PLANNING
// ============================================================
Route::prefix('degree-audit')->name('degree-audit.')->group(function () {
    Route::get('/', [DegreeAuditController::class, 'studentDashboard'])->name('index');
    Route::get('/my-audit', [DegreeAuditController::class, 'myAudit'])->name('my'); // ADDED missing route
    Route::get('/report', [DegreeAuditController::class, 'detailedReport'])->name('report');
    Route::get('/requirements', [DegreeAuditController::class, 'requirementsTracker'])->name('requirements');
    Route::get('/progress', [DegreeAuditController::class, 'progressOverview'])->name('progress');
    Route::post('/run', [DegreeAuditController::class, 'runAudit'])->name('run');
    Route::get('/export/{format}', [DegreeAuditController::class, 'exportAudit'])->name('export');
    Route::get('/print', [DegreeAuditController::class, 'printAudit'])->name('print');
    
    // What-If Analysis
    Route::get('/what-if', [WhatIfAnalysisController::class, 'index'])->name('what-if');
    Route::post('/what-if/analyze', [WhatIfAnalysisController::class, 'analyze'])->name('what-if.analyze');
    Route::post('/what-if/save', [WhatIfAnalysisController::class, 'saveScenario'])->name('what-if.save');
    Route::get('/what-if/scenarios', [WhatIfAnalysisController::class, 'myScenarios'])->name('what-if.scenarios');
    Route::delete('/what-if/scenario/{id}', [WhatIfAnalysisController::class, 'deleteScenario'])->name('what-if.delete');
    
    // Graduation Check
    Route::get('/graduation', [DegreeAuditController::class, 'graduationCheck'])->name('graduation');
    Route::post('/graduation/apply', [DegreeAuditController::class, 'applyForGraduation'])->name('graduation.apply');
});

// ============================================================
// ACADEMIC PLANNING
// ============================================================
Route::prefix('planner')->name('planner.')->group(function () {
    Route::get('/', [AcademicPlanController::class, 'studentPlanner'])->name('index');
    Route::get('/plans', [AcademicPlanController::class, 'myPlans'])->name('plans');
    Route::get('/plan/{id}', [AcademicPlanController::class, 'viewPlan'])->name('view');
    Route::post('/create', [AcademicPlanController::class, 'createPlan'])->name('create');
    Route::put('/plan/{id}', [AcademicPlanController::class, 'updatePlan'])->name('update');
    Route::delete('/plan/{id}', [AcademicPlanController::class, 'deletePlan'])->name('delete');
    Route::post('/plan/{id}/validate', [AcademicPlanController::class, 'validatePlan'])->name('validate');
    Route::get('/plan/{id}/export', [AcademicPlanController::class, 'exportPlan'])->name('export');
    Route::post('/plan/{id}/share', [AcademicPlanController::class, 'sharePlan'])->name('share');
    
    // Course Planning
    Route::get('/sequence', [AcademicPlanController::class, 'courseSequence'])->name('sequence');
    Route::post('/plan/{id}/add-course', [AcademicPlanController::class, 'addCourse'])->name('add-course');
    Route::delete('/plan/{id}/remove-course/{courseId}', [AcademicPlanController::class, 'removeCourse'])->name('remove-course');
    Route::post('/plan/{id}/move-course', [AcademicPlanController::class, 'moveCourse'])->name('move-course');
});

// ============================================================
// TRANSCRIPTS - FIXED ROUTES (Matching existing views)
// ============================================================
Route::prefix('transcripts')->name('transcripts.')->group(function () {
    // Main transcript index - uses transcripts/index.blade.php
    Route::get('/', function() {
        return view('transcripts.index');
    })->name('index');
    
    // My transcript - uses transcripts/view.blade.php
    Route::get('/my', function() {
        return view('transcripts.view');
    })->name('my');
    
    // View transcript - uses transcripts/view.blade.php
    Route::get('/view', function() {
        return view('transcripts.view');
    })->name('view');
    
    // Request transcript - uses transcripts/request.blade.php
    Route::get('/request', function() {
        return view('transcripts.request');
    })->name('request');
    
    // Submit transcript request
    Route::post('/request', function() {
        // Process request
        return redirect()->route('student.transcripts.request-status')
            ->with('success', 'Transcript request submitted successfully.');
    })->name('request.submit');
    
    // Request status - uses transcripts/request-status.blade.php
    Route::get('/request-status', function() {
        return view('transcripts.request-status');
    })->name('request-status');
    
    // My requests (list of all requests)
    Route::get('/requests', function() {
        return view('transcripts.request-status');
    })->name('requests');
    
    // Individual request status
    Route::get('/request/{id}/status', function($id) {
        return view('transcripts.request-status', ['requestId' => $id]);
    })->name('request.status');
    
    // Cancel request
    Route::get('/request/{id}/cancel', function($id) {
        return redirect()->route('student.transcripts.requests')
            ->with('success', 'Request cancelled.');
    })->name('request.cancel');
    
    // History
    Route::get('/history', function() {
        return view('transcripts.request-status');
    })->name('history');
    
    // Unofficial transcript
    Route::get('/unofficial', function() {
        return view('transcripts.view', ['unofficial' => true]);
    })->name('unofficial');
});

// ============================================================
// FINANCIAL ACCOUNTS
// ============================================================
Route::prefix('finances')->name('finances.')->group(function () {
    Route::get('/', [FinancialController::class, 'studentFinancialDashboard'])->name('index');
    Route::get('/balance', [FinancialController::class, 'accountBalance'])->name('balance');
    Route::get('/statement', [FinancialController::class, 'statement'])->name('statement');
    Route::get('/statement/{term}', [FinancialController::class, 'termStatement'])->name('statement.term');
    Route::get('/statement/download/{term?}', [FinancialController::class, 'downloadStatement'])->name('statement.download');
    
    // Payments
    Route::get('/pay', [FinancialController::class, 'makePayment'])->name('pay');
    Route::get('/payment', [FinancialController::class, 'makePayment'])->name('payment'); // ADDED for nav
    Route::post('/pay/process', [FinancialController::class, 'processStudentPayment'])->name('pay.process');
    Route::get('/payment/history', [FinancialController::class, 'paymentHistory'])->name('payment.history');
    Route::get('/payment/receipt/{payment}', [FinancialController::class, 'paymentReceipt'])->name('payment.receipt');
    Route::get('/payment/methods', [FinancialController::class, 'paymentMethods'])->name('payment.methods');
    Route::post('/payment/method/add', [FinancialController::class, 'addPaymentMethod'])->name('payment.method.add');
    Route::delete('/payment/method/{id}', [FinancialController::class, 'deletePaymentMethod'])->name('payment.method.delete');
    
    // Payment Plans
    Route::get('/payment-plans', [FinancialController::class, 'paymentPlans'])->name('payment-plans');
    Route::get('/payment-plan/enroll', [FinancialController::class, 'enrollPaymentPlan'])->name('payment-plan.enroll');
    Route::post('/payment-plan/request', [FinancialController::class, 'requestPaymentPlan'])->name('payment-plan.request');
    Route::get('/payment-plan/{id}', [FinancialController::class, 'viewPaymentPlan'])->name('payment-plan.view');
    
    // Financial Aid
    Route::get('/aid', [FinancialController::class, 'financialAid'])->name('aid');
    Route::get('/aid/dashboard', [FinancialController::class, 'aidDashboard'])->name('aid.dashboard'); // ADDED
    Route::get('/aid/status', [FinancialController::class, 'aidStatus'])->name('aid.status');
    Route::get('/aid/awards', [FinancialController::class, 'aidAwards'])->name('aid.awards');
    Route::post('/aid/accept/{award}', [FinancialController::class, 'acceptAward'])->name('aid.accept');
    Route::post('/aid/decline/{award}', [FinancialController::class, 'declineAward'])->name('aid.decline');
    
    // Tax Documents
    Route::get('/tax-documents', [FinancialController::class, 'taxDocuments'])->name('tax-documents');
    Route::get('/1098t/{year}', [FinancialController::class, 'download1098T'])->name('1098t');
});

// ============================================================
// ATTENDANCE
// ============================================================
Route::prefix('attendance')->name('attendance.')->group(function () {
    Route::get('/', [AttendanceController::class, 'studentAttendance'])->name('index');
    Route::get('/course/{enrollment}', [AttendanceController::class, 'courseAttendance'])->name('course');
    Route::get('/summary', [AttendanceController::class, 'attendanceSummary'])->name('summary');
    Route::get('/report', [AttendanceController::class, 'attendanceReport'])->name('report');
    Route::post('/excuse/submit', [AttendanceController::class, 'submitExcuse'])->name('excuse.submit');
    Route::get('/excuses', [AttendanceController::class, 'myExcuses'])->name('excuses');
    Route::get('/excuse/{id}', [AttendanceController::class, 'viewExcuse'])->name('excuse.view');
});

// ============================================================
// COURSE SITES (LMS)
// ============================================================
Route::prefix('courses')->name('courses.')->group(function () {
    Route::get('/', [LMSController::class, 'studentCourses'])->name('index');
    Route::get('/current', [LMSController::class, 'currentCourses'])->name('current');
    Route::get('/past', [LMSController::class, 'pastCourses'])->name('past');
    
    // Course site navigation
    Route::get('/{site}', [LMSController::class, 'viewCourseSite'])->name('site');
    Route::get('/{site}/syllabus', [LMSController::class, 'viewSyllabus'])->name('syllabus');
    Route::get('/{site}/announcements', [LMSController::class, 'courseAnnouncements'])->name('announcements');
    Route::get('/{site}/content', [LMSController::class, 'courseContent'])->name('content');
    Route::get('/{site}/assignments', [LMSController::class, 'courseAssignments'])->name('assignments');
    Route::get('/{site}/quizzes', [LMSController::class, 'courseQuizzes'])->name('quizzes');
    Route::get('/{site}/discussions', [LMSController::class, 'courseDiscussions'])->name('discussions');
    Route::get('/{site}/grades', [LMSController::class, 'courseGrades'])->name('grades');
    Route::get('/{site}/roster', [LMSController::class, 'courseRoster'])->name('roster');
    
    // Assignment submissions
    Route::get('/{site}/assignment/{id}', [LMSController::class, 'viewAssignment'])->name('assignment.view');
    Route::post('/{site}/assignment/{id}/submit', [LMSController::class, 'submitAssignment'])->name('assignment.submit');
    Route::get('/{site}/assignment/{id}/submission', [LMSController::class, 'viewSubmission'])->name('assignment.submission');
    
    // Quiz taking
    Route::get('/{site}/quiz/{id}', [LMSController::class, 'viewQuiz'])->name('quiz.view');
    Route::post('/{site}/quiz/{id}/start', [LMSController::class, 'startQuiz'])->name('quiz.start');
    Route::post('/{site}/quiz/{id}/submit', [LMSController::class, 'submitQuiz'])->name('quiz.submit');
    Route::get('/{site}/quiz/{id}/results', [LMSController::class, 'quizResults'])->name('quiz.results');
});

// ============================================================
// CAMPUS SERVICES
// ============================================================
Route::prefix('services')->name('services.')->group(function () {
    Route::get('/', function() { return view('student.services.index'); })->name('index');
    
    // Library Services
    Route::get('/library', function() { return view('student.services.library'); })->name('library');
    Route::get('/library/account', function() { return view('student.services.library-account'); })->name('library.account');
    
    // IT Services
    Route::get('/it', function() { return view('student.services.it'); })->name('it');
    Route::get('/it/password', function() { return view('student.services.password-reset'); })->name('it.password');
    
    // Health Services
    Route::get('/health', function() { return view('student.services.health'); })->name('health');
    Route::get('/health/appointments', function() { return view('student.services.health-appointments'); })->name('health.appointments');
    
    // Counseling Services
    Route::get('/counseling', function() { return view('student.services.counseling'); })->name('counseling');
    Route::get('/counseling/appointment', function() { return view('student.services.counseling-appointment'); })->name('counseling.appointment');
    
    // Career Services
    Route::get('/career', function() { return view('student.services.career'); })->name('career');
    Route::get('/career/appointments', function() { return view('student.services.career-appointments'); })->name('career.appointments');
});

// ============================================================
// ENROLLMENT STATUS & FORMS
// ============================================================
Route::prefix('enrollment')->name('enrollment.')->group(function () {
    Route::get('/', [EnrollmentPortalController::class, 'studentStatus'])->name('index');
    Route::get('/verification', [EnrollmentPortalController::class, 'verificationLetter'])->name('verification');
    Route::post('/verification/request', [EnrollmentPortalController::class, 'requestVerification'])->name('verification.request');
    Route::get('/forms', [EnrollmentPortalController::class, 'forms'])->name('forms');
    Route::get('/forms/{form}/download', [EnrollmentPortalController::class, 'downloadForm'])->name('forms.download');
    Route::post('/forms/{form}/submit', [EnrollmentPortalController::class, 'submitForm'])->name('forms.submit');
    Route::get('/holds', [EnrollmentPortalController::class, 'viewHolds'])->name('holds');
});

// ============================================================
// MESSAGES & COMMUNICATIONS
// ============================================================
Route::prefix('messages')->name('messages.')->group(function () {
    Route::get('/', function() { return view('student.messages.index'); })->name('index');
    Route::get('/inbox', function() { return view('student.messages.inbox'); })->name('inbox');
    Route::get('/sent', function() { return view('student.messages.sent'); })->name('sent');
    Route::get('/compose', function() { return view('student.messages.compose'); })->name('compose');
    Route::get('/message/{id}', function($id) { return view('student.messages.view', compact('id')); })->name('view');
});

// ============================================================
// RESOURCES & TOOLS
// ============================================================
Route::prefix('resources')->name('resources.')->group(function () {
    Route::get('/', function() { return view('student.resources.index'); })->name('index');
    Route::get('/handbook', function() { return view('student.resources.handbook'); })->name('handbook');
    Route::get('/forms', function() { return view('student.resources.forms'); })->name('forms');
    Route::get('/policies', function() { return view('student.resources.policies'); })->name('policies');
    Route::get('/calendar', function() { return view('student.resources.calendar'); })->name('calendar');
    Route::get('/directory', function() { return view('student.resources.directory'); })->name('directory');
});