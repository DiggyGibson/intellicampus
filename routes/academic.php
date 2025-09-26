<?php
/**
 * IntelliCampus Academic Management Routes
 * 
 * Routes for courses, registration, grades, transcripts, and academic records.
 * These routes have mixed middleware requirements applied at the group level.
 * Base middleware: 'web', 'auth'
 */

use App\Http\Controllers\CourseController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\TranscriptController;
use App\Http\Controllers\DegreeAuditController;
use App\Http\Controllers\GraduationController;
use App\Http\Controllers\AcademicPlanController;
use App\Http\Controllers\RequirementManagementController;
use App\Http\Controllers\WhatIfAnalysisController;
use App\Http\Controllers\Admin\GradeApprovalController;
use App\Http\Controllers\Admin\GradeDeadlineController;
use App\Http\Controllers\Admin\GradeScaleController;
use App\Http\Controllers\Admin\GradeReportController;
use Illuminate\Support\Facades\Route;

// ============================================================
// COURSE CATALOG & MANAGEMENT
// ============================================================
Route::prefix('courses')->name('courses.')->group(function () {
    
    // Public Course Catalog (no additional auth required)
    Route::get('/catalog', [CourseController::class, 'publicCatalog'])->name('catalog');
    Route::get('/catalog/search', [CourseController::class, 'searchCatalog'])->name('catalog.search');
    Route::get('/catalog/{course}', [CourseController::class, 'catalogDetails'])->name('catalog.details');
    Route::get('/catalog/department/{department}', [CourseController::class, 'departmentCourses'])->name('catalog.department');
    Route::get('/catalog/program/{program}', [CourseController::class, 'programCourses'])->name('catalog.program');
    Route::get('/catalog/export', [CourseController::class, 'exportCatalog'])->name('catalog.export');
    
    // Course Management (Admin/Department)
    Route::middleware(['role:admin,department-head,curriculum-committee'])->group(function () {
        Route::get('/', [CourseController::class, 'index'])->name('index');
        Route::get('/create', [CourseController::class, 'create'])->name('create');
        Route::post('/', [CourseController::class, 'store'])->name('store');
        Route::get('/{course}/edit', [CourseController::class, 'edit'])->name('edit');
        Route::put('/{course}', [CourseController::class, 'update'])->name('update');
        Route::delete('/{course}', [CourseController::class, 'destroy'])->name('destroy');
        Route::post('/{course}/activate', [CourseController::class, 'activate'])->name('activate');
        Route::post('/{course}/deactivate', [CourseController::class, 'deactivate'])->name('deactivate');
        
        // Curriculum Management
        Route::get('/{course}/curriculum', [CourseController::class, 'curriculum'])->name('curriculum');
        Route::put('/{course}/curriculum', [CourseController::class, 'updateCurriculum'])->name('curriculum.update');
        Route::get('/{course}/syllabus-template', [CourseController::class, 'syllabusTemplate'])->name('syllabus-template');
        Route::put('/{course}/syllabus-template', [CourseController::class, 'updateSyllabusTemplate'])->name('syllabus-template.update');
        
        // Prerequisites & Corequisites
        Route::get('/{course}/prerequisites', [CourseController::class, 'prerequisites'])->name('prerequisites');
        Route::post('/{course}/prerequisites', [CourseController::class, 'addPrerequisite'])->name('prerequisites.add');
        Route::delete('/{course}/prerequisites/{prerequisite}', [CourseController::class, 'removePrerequisite'])->name('prerequisites.remove');
        Route::post('/{course}/corequisites', [CourseController::class, 'addCorequisite'])->name('corequisites.add');
        Route::delete('/{course}/corequisites/{corequisite}', [CourseController::class, 'removeCorequisite'])->name('corequisites.remove');
        
        // Course Equivalencies
        Route::get('/{course}/equivalencies', [CourseController::class, 'equivalencies'])->name('equivalencies');
        Route::post('/{course}/equivalencies', [CourseController::class, 'addEquivalency'])->name('equivalencies.add');
        Route::delete('/{course}/equivalencies/{equivalency}', [CourseController::class, 'removeEquivalency'])->name('equivalencies.remove');
        
        // Cross-listing
        Route::get('/{course}/cross-listings', [CourseController::class, 'crossListings'])->name('cross-listings');
        Route::post('/{course}/cross-list', [CourseController::class, 'addCrossListing'])->name('cross-list.add');
        Route::delete('/{course}/cross-list/{listing}', [CourseController::class, 'removeCrossListing'])->name('cross-list.remove');
    });
    
    // Course Section Management
    Route::prefix('sections')->name('sections.')->group(function () {
        Route::middleware(['role:admin,department-head,registrar,scheduling-coordinator'])->group(function () {
            Route::get('/', [CourseController::class, 'sections'])->name('index');
            Route::get('/create/{course}', [CourseController::class, 'createSection'])->name('create');
            Route::post('/', [CourseController::class, 'storeSection'])->name('store');
            Route::get('/{section}', [CourseController::class, 'showSection'])->name('show');
            Route::get('/{section}/edit', [CourseController::class, 'editSection'])->name('edit');
            Route::put('/{section}', [CourseController::class, 'updateSection'])->name('update');
            Route::delete('/{section}', [CourseController::class, 'deleteSection'])->name('delete');
            Route::post('/{section}/cancel', [CourseController::class, 'cancelSection'])->name('cancel');
            Route::post('/{section}/merge', [CourseController::class, 'mergeSections'])->name('merge');
            
            // Instructor Assignment
            Route::get('/{section}/instructors', [CourseController::class, 'sectionInstructors'])->name('instructors');
            Route::post('/{section}/assign-instructor', [CourseController::class, 'assignInstructor'])->name('assign-instructor');
            Route::delete('/{section}/remove-instructor/{instructor}', [CourseController::class, 'removeInstructor'])->name('remove-instructor');
            Route::post('/{section}/primary-instructor', [CourseController::class, 'setPrimaryInstructor'])->name('primary-instructor');
            
            // Enrollment Management
            Route::get('/{section}/enrollment', [CourseController::class, 'sectionEnrollment'])->name('enrollment');
            Route::put('/{section}/enrollment-caps', [CourseController::class, 'updateEnrollmentCaps'])->name('enrollment-caps');
            Route::get('/{section}/waitlist', [CourseController::class, 'sectionWaitlist'])->name('waitlist');
            Route::post('/{section}/process-waitlist', [CourseController::class, 'processWaitlist'])->name('process-waitlist');
        });
        
        // Public Section Information
        Route::get('/{section}/details', [CourseController::class, 'sectionDetails'])->name('details');
        Route::get('/term/{term}', [CourseController::class, 'termSections'])->name('term');
    });
});

// ============================================================
// REGISTRATION SYSTEM
// ============================================================
Route::prefix('registration')->name('registration.')->group(function () {
    
    // Student Registration (verified students only)
    Route::middleware(['verified', 'student.active'])->group(function () {
        Route::get('/', [RegistrationController::class, 'index'])->name('index');
        Route::get('/eligible-terms', [RegistrationController::class, 'eligibleTerms'])->name('eligible-terms');
        Route::get('/appointment', [RegistrationController::class, 'registrationAppointment'])->name('appointment');
        
        // Course Search & Planning
        Route::get('/search', [RegistrationController::class, 'searchCourses'])->name('search');
        Route::get('/planner', [RegistrationController::class, 'coursePlanner'])->name('planner');
        Route::post('/check-prerequisites', [RegistrationController::class, 'checkPrerequisites'])->name('check-prerequisites');
        Route::post('/check-conflicts', [RegistrationController::class, 'checkConflicts'])->name('check-conflicts');
        
        // Registration Cart
        Route::get('/cart', [RegistrationController::class, 'viewCart'])->name('cart');
        Route::post('/cart/add', [RegistrationController::class, 'addToCart'])->name('cart.add');
        Route::delete('/cart/remove/{item}', [RegistrationController::class, 'removeFromCart'])->name('cart.remove');
        Route::post('/cart/validate', [RegistrationController::class, 'validateCart'])->name('cart.validate');
        Route::post('/cart/save', [RegistrationController::class, 'saveCart'])->name('cart.save');
        Route::get('/cart/saved', [RegistrationController::class, 'savedCarts'])->name('cart.saved');
        Route::post('/cart/load/{cart}', [RegistrationController::class, 'loadCart'])->name('cart.load');
        
        // Registration Submission
        Route::post('/submit', [RegistrationController::class, 'submitRegistration'])->name('submit');
        Route::get('/confirmation/{registration}', [RegistrationController::class, 'confirmation'])->name('confirmation');
        Route::get('/receipt/{registration}', [RegistrationController::class, 'printReceipt'])->name('receipt');
        
        // Schedule Management
        Route::get('/schedule', [RegistrationController::class, 'viewSchedule'])->name('schedule');
        Route::get('/schedule/print', [RegistrationController::class, 'printSchedule'])->name('schedule.print');
        Route::get('/schedule/export', [RegistrationController::class, 'exportSchedule'])->name('schedule.export');
        Route::get('/schedule/ics', [RegistrationController::class, 'downloadICS'])->name('schedule.ics');
        
        // Add/Drop/Withdraw
        Route::get('/add-drop', [RegistrationController::class, 'addDropForm'])->name('add-drop');
        Route::post('/drop', [RegistrationController::class, 'dropCourse'])->name('drop');
        Route::post('/add', [RegistrationController::class, 'addCourse'])->name('add');
        Route::post('/swap', [RegistrationController::class, 'swapSection'])->name('swap');
        Route::post('/withdraw', [RegistrationController::class, 'withdrawFromCourse'])->name('withdraw');
        Route::get('/withdrawal-deadlines', [RegistrationController::class, 'withdrawalDeadlines'])->name('withdrawal-deadlines');
        
        // Waitlist Management
        Route::get('/waitlist', [RegistrationController::class, 'myWaitlists'])->name('waitlist');
        Route::post('/waitlist/join', [RegistrationController::class, 'joinWaitlist'])->name('waitlist.join');
        Route::delete('/waitlist/{waitlist}', [RegistrationController::class, 'leaveWaitlist'])->name('waitlist.leave');
        Route::post('/waitlist/{waitlist}/accept', [RegistrationController::class, 'acceptWaitlistOffer'])->name('waitlist.accept');
        
        // Registration History
        Route::get('/history', [RegistrationController::class, 'registrationHistory'])->name('history');
        Route::get('/history/{term}', [RegistrationController::class, 'termRegistrationHistory'])->name('history.term');
    });
    
    // Administrative Registration Functions
    Route::middleware(['role:registrar,admin,academic-administrator'])->group(function () {
        Route::get('/admin', [RegistrationController::class, 'adminDashboard'])->name('admin');
        Route::get('/admin/statistics', [RegistrationController::class, 'registrationStatistics'])->name('admin.statistics');
        Route::get('/admin/holds', [RegistrationController::class, 'registrationHolds'])->name('admin.holds');
        Route::post('/admin/hold', [RegistrationController::class, 'placeHold'])->name('admin.hold');
        Route::delete('/admin/hold/{hold}', [RegistrationController::class, 'releaseHold'])->name('admin.release-hold');
        Route::get('/admin/appointments', [RegistrationController::class, 'registrationAppointments'])->name('admin.appointments');
        Route::post('/admin/appointments/generate', [RegistrationController::class, 'generateAppointments'])->name('admin.appointments.generate');
        Route::get('/admin/reports', [RegistrationController::class, 'registrationReports'])->name('admin.reports');
        Route::post('/admin/manual-registration', [RegistrationController::class, 'manualRegistration'])->name('admin.manual');
        Route::post('/admin/bulk-registration', [RegistrationController::class, 'bulkRegistration'])->name('admin.bulk');
    });
});

// ============================================================
// GRADING SYSTEM
// ============================================================
Route::prefix('grades')->name('grades.')->group(function () {
    
    // Faculty Grade Entry
    Route::middleware(['role:faculty,instructor,teaching-assistant'])->group(function () {
        Route::get('/my-sections', [GradeController::class, 'mySections'])->name('my-sections');
        Route::get('/section/{section}', [GradeController::class, 'sectionGrades'])->name('section');
        Route::get('/section/{section}/components', [GradeController::class, 'gradeComponents'])->name('components');
        Route::post('/section/{section}/components', [GradeController::class, 'saveComponents'])->name('components.save');
        Route::get('/section/{section}/entry', [GradeController::class, 'gradeEntry'])->name('entry');
        Route::post('/section/{section}/save', [GradeController::class, 'saveGrades'])->name('save');
        Route::post('/section/{section}/calculate', [GradeController::class, 'calculateGrades'])->name('calculate');
        Route::get('/section/{section}/preview', [GradeController::class, 'previewGrades'])->name('preview');
        Route::post('/section/{section}/submit', [GradeController::class, 'submitGrades'])->name('submit');
        Route::get('/section/{section}/export', [GradeController::class, 'exportGrades'])->name('export');
        Route::post('/section/{section}/import', [GradeController::class, 'importGrades'])->name('import');
        Route::get('/section/{section}/template', [GradeController::class, 'downloadTemplate'])->name('template');
    });
    
    // Grade Administration
    Route::middleware(['role:registrar,admin,grade-administrator'])->group(function () {
        Route::get('/admin', [GradeApprovalController::class, 'dashboard'])->name('admin');
        Route::get('/pending-approval', [GradeApprovalController::class, 'pendingApproval'])->name('pending');
        Route::post('/approve/{submission}', [GradeApprovalController::class, 'approve'])->name('approve');
        Route::post('/reject/{submission}', [GradeApprovalController::class, 'reject'])->name('reject');
        Route::post('/bulk-approve', [GradeApprovalController::class, 'bulkApprove'])->name('bulk-approve');
        
        // Grade Changes
        Route::get('/changes', [GradeApprovalController::class, 'gradeChanges'])->name('changes');
        Route::get('/change/{change}', [GradeApprovalController::class, 'viewChangeRequest'])->name('change.view');
        Route::post('/change/{change}/approve', [GradeApprovalController::class, 'approveChange'])->name('change.approve');
        Route::post('/change/{change}/deny', [GradeApprovalController::class, 'denyChange'])->name('change.deny');
        Route::post('/administrative-change', [GradeApprovalController::class, 'administrativeChange'])->name('admin-change');
        
        // Grade Deadlines
        Route::get('/deadlines', [GradeDeadlineController::class, 'index'])->name('deadlines');
        Route::post('/deadlines', [GradeDeadlineController::class, 'store'])->name('deadlines.store');
        Route::put('/deadlines/{deadline}', [GradeDeadlineController::class, 'update'])->name('deadlines.update');
        Route::delete('/deadlines/{deadline}', [GradeDeadlineController::class, 'destroy'])->name('deadlines.destroy');
        Route::post('/deadlines/notify', [GradeDeadlineController::class, 'sendReminders'])->name('deadlines.notify');
        
        // Grade Scales
        Route::get('/scales', [GradeScaleController::class, 'index'])->name('scales');
        Route::get('/scales/create', [GradeScaleController::class, 'create'])->name('scales.create');
        Route::post('/scales', [GradeScaleController::class, 'store'])->name('scales.store');
        Route::get('/scales/{scale}/edit', [GradeScaleController::class, 'edit'])->name('scales.edit');
        Route::put('/scales/{scale}', [GradeScaleController::class, 'update'])->name('scales.update');
        Route::delete('/scales/{scale}', [GradeScaleController::class, 'destroy'])->name('scales.destroy');
        Route::post('/scales/{scale}/set-default', [GradeScaleController::class, 'setDefault'])->name('scales.set-default');
        
        // Grade Policies
        Route::get('/policies', [GradeController::class, 'policies'])->name('policies');
        Route::put('/policies', [GradeController::class, 'updatePolicies'])->name('policies.update');
        Route::get('/rounding-rules', [GradeController::class, 'roundingRules'])->name('rounding-rules');
        Route::put('/rounding-rules', [GradeController::class, 'updateRoundingRules'])->name('rounding-rules.update');
    });
    
    // Grade Reports
    Route::middleware(['role:admin,registrar,department-head,dean'])->group(function () {
        Route::get('/reports', [GradeReportController::class, 'index'])->name('reports');
        Route::get('/reports/gpa', [GradeReportController::class, 'gpaReport'])->name('reports.gpa');
        Route::get('/reports/deans-list', [GradeReportController::class, 'deansList'])->name('reports.deans-list');
        Route::get('/reports/probation', [GradeReportController::class, 'academicProbation'])->name('reports.probation');
        Route::get('/reports/standing', [GradeReportController::class, 'academicStanding'])->name('reports.standing');
        Route::get('/reports/distribution', [GradeReportController::class, 'gradeDistribution'])->name('reports.distribution');
        Route::get('/reports/incomplete', [GradeReportController::class, 'incompleteGrades'])->name('reports.incomplete');
        Route::get('/reports/missing', [GradeReportController::class, 'missingGrades'])->name('reports.missing');
        Route::post('/reports/export', [GradeReportController::class, 'exportReport'])->name('reports.export');
    });
    
    // Student Grade View
    Route::get('/my-grades', [GradeController::class, 'studentGrades'])->name('student')
        ->middleware('role:student');
});

// ============================================================
// TRANSCRIPTS
// ============================================================
Route::prefix('transcripts')->name('transcripts.')->group(function () {
    
    // Student Transcript Access
    Route::middleware(['verified'])->group(function () {
        Route::get('/my-transcript', [TranscriptController::class, 'viewMyTranscript'])->name('my');
        Route::get('/unofficial', [TranscriptController::class, 'generateUnofficial'])->name('unofficial');
        Route::post('/request-official', [TranscriptController::class, 'requestOfficial'])->name('request-official');
        Route::get('/requests', [TranscriptController::class, 'myRequests'])->name('my-requests');
        Route::get('/request/{request}', [TranscriptController::class, 'requestStatus'])->name('request.status');
        Route::post('/request/{request}/payment', [TranscriptController::class, 'payForTranscript'])->name('request.payment');
    });
    
    // Registrar Transcript Management
    Route::middleware(['role:registrar,transcript-coordinator,admin'])->group(function () {
        Route::get('/admin', [TranscriptController::class, 'adminDashboard'])->name('admin');
        Route::get('/requests/pending', [TranscriptController::class, 'pendingRequests'])->name('requests.pending');
        Route::get('/requests/all', [TranscriptController::class, 'allRequests'])->name('requests.all');
        Route::post('/process/{request}', [TranscriptController::class, 'processRequest'])->name('process');
        Route::post('/approve/{request}', [TranscriptController::class, 'approveRequest'])->name('approve');
        Route::post('/deny/{request}', [TranscriptController::class, 'denyRequest'])->name('deny');
        Route::post('/complete/{request}', [TranscriptController::class, 'completeRequest'])->name('complete');
        Route::post('/generate/{student}', [TranscriptController::class, 'generateOfficial'])->name('generate');
        Route::get('/preview/{student}', [TranscriptController::class, 'previewTranscript'])->name('preview');
        
        // Transcript Annotations
        Route::get('/annotations/{student}', [TranscriptController::class, 'annotations'])->name('annotations');
        Route::post('/annotation/{student}', [TranscriptController::class, 'addAnnotation'])->name('annotation.add');
        Route::put('/annotation/{annotation}', [TranscriptController::class, 'updateAnnotation'])->name('annotation.update');
        Route::delete('/annotation/{annotation}', [TranscriptController::class, 'removeAnnotation'])->name('annotation.remove');
        
        // Honors & Awards
        Route::get('/honors/{student}', [TranscriptController::class, 'studentHonors'])->name('honors');
        Route::post('/honor/{student}', [TranscriptController::class, 'addHonor'])->name('honor.add');
        Route::put('/honor/{honor}', [TranscriptController::class, 'updateHonor'])->name('honor.update');
        Route::delete('/honor/{honor}', [TranscriptController::class, 'removeHonor'])->name('honor.remove');
        
        // Transfer Credits
        Route::get('/transfer-credits/{student}', [TranscriptController::class, 'transferCredits'])->name('transfer-credits');
        Route::post('/transfer-credit/{student}', [TranscriptController::class, 'addTransferCredit'])->name('transfer-credit.add');
        Route::put('/transfer-credit/{credit}', [TranscriptController::class, 'updateTransferCredit'])->name('transfer-credit.update');
        Route::delete('/transfer-credit/{credit}', [TranscriptController::class, 'removeTransferCredit'])->name('transfer-credit.remove');
        Route::post('/transfer-evaluation/{student}', [TranscriptController::class, 'evaluateTransferCredits'])->name('transfer-evaluation');
    });
    
    // Verification System
    Route::get('/verify/{code}', [TranscriptController::class, 'verifyTranscript'])->name('verify');
    Route::post('/generate-verification', [TranscriptController::class, 'generateVerificationCode'])->name('generate-verification')
        ->middleware('role:registrar');
});

// ============================================================
// DEGREE AUDIT & REQUIREMENTS
// ============================================================
Route::prefix('degree-audit')->name('degree-audit.')->group(function () {
    
    // Student Degree Audit
    Route::middleware(['verified', 'role:student'])->group(function () {
        Route::get('/my-audit', [DegreeAuditController::class, 'myAudit'])->name('my');
        Route::get('/run', [DegreeAuditController::class, 'runAudit'])->name('run');
        Route::get('/report', [DegreeAuditController::class, 'generateReport'])->name('report');
        Route::get('/progress', [DegreeAuditController::class, 'degreeProgress'])->name('progress');
        Route::get('/requirements', [DegreeAuditController::class, 'viewRequirements'])->name('requirements');
        Route::get('/print', [DegreeAuditController::class, 'printAudit'])->name('print');
        Route::get('/export', [DegreeAuditController::class, 'exportAudit'])->name('export');
    });
    
    // What-If Analysis
    Route::get('/what-if', [WhatIfAnalysisController::class, 'index'])->name('what-if');
    Route::post('/what-if/run', [WhatIfAnalysisController::class, 'runAnalysis'])->name('what-if.run');
    Route::post('/what-if/save', [WhatIfAnalysisController::class, 'saveScenario'])->name('what-if.save');
    Route::get('/what-if/scenarios', [WhatIfAnalysisController::class, 'savedScenarios'])->name('what-if.scenarios');
    Route::delete('/what-if/scenario/{scenario}', [WhatIfAnalysisController::class, 'deleteScenario'])->name('what-if.delete');
    Route::post('/what-if/compare', [WhatIfAnalysisController::class, 'compareScenarios'])->name('what-if.compare');
    
    // Academic Planning
    Route::prefix('planning')->name('planning.')->group(function () {
        Route::get('/', [AcademicPlanController::class, 'index'])->name('index');
        Route::get('/create', [AcademicPlanController::class, 'create'])->name('create');
        Route::post('/store', [AcademicPlanController::class, 'store'])->name('store');
        Route::get('/plan/{plan}', [AcademicPlanController::class, 'show'])->name('show');
        Route::get('/plan/{plan}/edit', [AcademicPlanController::class, 'edit'])->name('edit');
        Route::put('/plan/{plan}', [AcademicPlanController::class, 'update'])->name('update');
        Route::delete('/plan/{plan}', [AcademicPlanController::class, 'destroy'])->name('destroy');
        Route::post('/plan/{plan}/validate', [AcademicPlanController::class, 'validatePlan'])->name('validate');
        Route::post('/plan/{plan}/approve', [AcademicPlanController::class, 'approvePlan'])->name('approve')
            ->middleware('role:advisor');
        Route::get('/templates', [AcademicPlanController::class, 'templates'])->name('templates');
        Route::post('/from-template/{template}', [AcademicPlanController::class, 'createFromTemplate'])->name('from-template');
    });
    
    // Administrative Degree Audit Functions
    Route::middleware(['role:advisor,registrar,admin'])->group(function () {
        Route::get('/admin', [DegreeAuditController::class, 'adminDashboard'])->name('admin');
        Route::get('/student/{student}', [DegreeAuditController::class, 'studentAudit'])->name('student');
        Route::post('/student/{student}/run', [DegreeAuditController::class, 'runStudentAudit'])->name('student.run');
        Route::post('/student/{student}/override', [DegreeAuditController::class, 'overrideRequirement'])->name('student.override');
        Route::post('/student/{student}/substitute', [DegreeAuditController::class, 'substituteCourse'])->name('student.substitute');
        Route::post('/student/{student}/waive', [DegreeAuditController::class, 'waiveRequirement'])->name('student.waive');
        Route::get('/batch', [DegreeAuditController::class, 'batchAudits'])->name('batch');
        Route::post('/batch/run', [DegreeAuditController::class, 'runBatchAudits'])->name('batch.run');
        Route::get('/exceptions', [DegreeAuditController::class, 'auditExceptions'])->name('exceptions');
        Route::post('/exception', [DegreeAuditController::class, 'createException'])->name('exception.create');
    });
    
    // Requirement Management
    Route::middleware(['role:admin,curriculum-committee'])->group(function () {
        Route::get('/requirements/manage', [RequirementManagementController::class, 'index'])->name('requirements.manage');
        Route::get('/requirements/create', [RequirementManagementController::class, 'create'])->name('requirements.create');
        Route::post('/requirements', [RequirementManagementController::class, 'store'])->name('requirements.store');
        Route::get('/requirements/{requirement}/edit', [RequirementManagementController::class, 'edit'])->name('requirements.edit');
        Route::put('/requirements/{requirement}', [RequirementManagementController::class, 'update'])->name('requirements.update');
        Route::delete('/requirements/{requirement}', [RequirementManagementController::class, 'destroy'])->name('requirements.destroy');
        Route::post('/requirements/{requirement}/activate', [RequirementManagementController::class, 'activate'])->name('requirements.activate');
        Route::post('/requirements/{requirement}/deactivate', [RequirementManagementController::class, 'deactivate'])->name('requirements.deactivate');
        Route::post('/requirements/import', [RequirementManagementController::class, 'import'])->name('requirements.import');
        Route::get('/requirements/export', [RequirementManagementController::class, 'export'])->name('requirements.export');
    });
});

// ============================================================
// GRADUATION
// ============================================================
Route::prefix('graduation')->name('graduation.')->group(function () {
    
    // Student Graduation Application
    Route::middleware(['verified', 'role:student'])->group(function () {
        Route::get('/apply', [GraduationController::class, 'applicationForm'])->name('apply');
        Route::post('/apply', [GraduationController::class, 'submitApplication'])->name('apply.submit');
        Route::get('/status', [GraduationController::class, 'applicationStatus'])->name('status');
        Route::get('/checklist', [GraduationController::class, 'graduationChecklist'])->name('checklist');
        Route::post('/confirm-attendance', [GraduationController::class, 'confirmCeremonyAttendance'])->name('confirm-attendance');
        Route::get('/diploma-info', [GraduationController::class, 'diplomaInformation'])->name('diploma-info');
        Route::post('/diploma-info', [GraduationController::class, 'updateDiplomaInfo'])->name('diploma-info.update');
    });
    
    // Administrative Graduation Management
    Route::middleware(['role:registrar,graduation-coordinator,admin'])->group(function () {
        Route::get('/admin', [GraduationController::class, 'adminDashboard'])->name('admin');
        Route::get('/applications', [GraduationController::class, 'applications'])->name('applications');
        Route::get('/application/{application}', [GraduationController::class, 'viewApplication'])->name('application.view');
        Route::post('/application/{application}/review', [GraduationController::class, 'reviewApplication'])->name('application.review');
        Route::post('/application/{application}/approve', [GraduationController::class, 'approveApplication'])->name('application.approve');
        Route::post('/application/{application}/deny', [GraduationController::class, 'denyApplication'])->name('application.deny');
        Route::post('/application/{application}/clear', [GraduationController::class, 'clearForGraduation'])->name('application.clear');
        
        // Graduation Lists
        Route::get('/candidates/{term}', [GraduationController::class, 'graduationCandidates'])->name('candidates');
        Route::post('/finalize-list/{term}', [GraduationController::class, 'finalizeGraduationList'])->name('finalize-list');
        Route::get('/honors/{term}', [GraduationController::class, 'honorsCalculation'])->name('honors');
        Route::post('/calculate-honors/{term}', [GraduationController::class, 'calculateHonors'])->name('calculate-honors');
        
        // Ceremony Management
        Route::get('/ceremonies', [GraduationController::class, 'ceremonies'])->name('ceremonies');
        Route::get('/ceremony/{ceremony}', [GraduationController::class, 'ceremonyDetails'])->name('ceremony.details');
        Route::post('/ceremony/{ceremony}/seating', [GraduationController::class, 'generateSeating'])->name('ceremony.seating');
        Route::get('/ceremony/{ceremony}/program', [GraduationController::class, 'ceremonyProgram'])->name('ceremony.program');
        Route::get('/ceremony/{ceremony}/export', [GraduationController::class, 'exportCeremonyData'])->name('ceremony.export');
        
        // Diploma Management
        Route::get('/diplomas', [GraduationController::class, 'diplomaManagement'])->name('diplomas');
        Route::post('/diplomas/order', [GraduationController::class, 'orderDiplomas'])->name('diplomas.order');
        Route::post('/diploma/{diploma}/print', [GraduationController::class, 'printDiploma'])->name('diploma.print');
        Route::post('/diploma/{diploma}/reprint', [GraduationController::class, 'reprintDiploma'])->name('diploma.reprint');
        Route::get('/diploma/tracking', [GraduationController::class, 'diplomaTracking'])->name('diploma.tracking');
    });
});

// ============================================================
// ACADEMIC CALENDAR
// ============================================================
Route::prefix('calendar')->name('calendar.')->group(function () {
    // Public Calendar View
    Route::get('/', [CourseController::class, 'academicCalendar'])->name('index');
    Route::get('/events', [CourseController::class, 'calendarEvents'])->name('events');
    Route::get('/term/{term}', [CourseController::class, 'termCalendar'])->name('term');
    Route::get('/export', [CourseController::class, 'exportCalendar'])->name('export');
    
    // Administrative Calendar Management
    Route::middleware(['role:registrar,admin'])->group(function () {
        Route::get('/manage', [CourseController::class, 'manageCalendar'])->name('manage');
        Route::post('/event', [CourseController::class, 'addEvent'])->name('event.add');
        Route::put('/event/{event}', [CourseController::class, 'updateEvent'])->name('event.update');
        Route::delete('/event/{event}', [CourseController::class, 'deleteEvent'])->name('event.delete');
        Route::post('/import', [CourseController::class, 'importCalendar'])->name('import');
    });
});

// ============================================================
// ACADEMIC PROGRAMS
// ============================================================
Route::prefix('programs')->name('programs.')->group(function () {
    // Public Program Information
    Route::get('/', [CourseController::class, 'programs'])->name('index');
    Route::get('/{program}', [CourseController::class, 'programDetails'])->name('show');
    Route::get('/{program}/requirements', [CourseController::class, 'programRequirements'])->name('requirements');
    Route::get('/{program}/courses', [CourseController::class, 'programCourses'])->name('courses');
    Route::get('/{program}/sample-plan', [CourseController::class, 'samplePlan'])->name('sample-plan');
    
    // Program Management
    Route::middleware(['role:admin,curriculum-committee'])->group(function () {
        Route::get('/manage/all', [CourseController::class, 'managePrograms'])->name('manage');
        Route::get('/create', [CourseController::class, 'createProgram'])->name('create');
        Route::post('/', [CourseController::class, 'storeProgram'])->name('store');
        Route::get('/{program}/edit', [CourseController::class, 'editProgram'])->name('edit');
        Route::put('/{program}', [CourseController::class, 'updateProgram'])->name('update');
        Route::delete('/{program}', [CourseController::class, 'deleteProgram'])->name('delete');
        Route::post('/{program}/activate', [CourseController::class, 'activateProgram'])->name('activate');
        Route::post('/{program}/deactivate', [CourseController::class, 'deactivateProgram'])->name('deactivate');
    });
});