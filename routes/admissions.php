<?php
/**
 * IntelliCampus Admissions & Enrollment Routes
 * 
 * Complete routes for the admissions process, applications, and enrollment.
 * These routes have mixed authentication requirements - some are public, others require auth.
 * Base middleware: 'web'
 * 
 * Updated to support two-phase application architecture:
 * Phase 1: Start Application (collect basic info, create UUID)
 * Phase 2: Detailed Application (dynamic form based on type and program)
 */

use App\Http\Controllers\AdmissionsController;
use App\Http\Controllers\AdmissionsPortalController;
use App\Http\Controllers\ApplicationFormController;
use App\Http\Controllers\ApplicationDocumentController;
use App\Http\Controllers\EnrollmentPortalController;
use App\Http\Controllers\AdminApplicationController;
use App\Http\Controllers\ApplicationReviewController;
use App\Http\Controllers\AdmissionDecisionController;
use App\Http\Controllers\DocumentVerificationController;
use App\Http\Controllers\AdmissionsReportController;
use App\Http\Controllers\PublicAdmissionsController;
use App\Http\Controllers\ExamPortalController;
use Illuminate\Support\Facades\Route;

// ============================================================
// PUBLIC ADMISSIONS INFORMATION (No Auth Required)
// ============================================================
Route::prefix('admissions')->name('admissions.')->group(function () {
    
    // Main public pages
    Route::get('/', [PublicAdmissionsController::class, 'index'])->name('index');
    Route::get('/requirements', [PublicAdmissionsController::class, 'requirements'])->name('requirements');
    Route::get('/programs', [PublicAdmissionsController::class, 'programs'])->name('programs');
    Route::get('/program/{slug}', [PublicAdmissionsController::class, 'programDetails'])->name('program.details');
    Route::get('/deadlines', [PublicAdmissionsController::class, 'deadlines'])->name('deadlines');
    Route::get('/fees', [PublicAdmissionsController::class, 'fees'])->name('fees');
    Route::get('/international', [PublicAdmissionsController::class, 'international'])->name('international');
    Route::get('/transfer', [PublicAdmissionsController::class, 'transfer'])->name('transfer');
    Route::get('/graduate', [PublicAdmissionsController::class, 'graduate'])->name('graduate');
    Route::get('/faq', [PublicAdmissionsController::class, 'faq'])->name('faq');
    Route::get('/contact', [PublicAdmissionsController::class, 'contact'])->name('contact');
    Route::post('/inquiry', [PublicAdmissionsController::class, 'submitInquiry'])->name('inquiry');
    Route::get('/calendar', [PublicAdmissionsController::class, 'calendar'])->name('calendar');
    Route::get('/visit', [PublicAdmissionsController::class, 'visitCampus'])->name('visit');
    Route::post('/visit/schedule', [PublicAdmissionsController::class, 'scheduleVisit'])->name('visit.schedule');
    Route::get('/virtual-tour', [PublicAdmissionsController::class, 'virtualTour'])->name('virtual-tour');
    Route::get('/checklist', [PublicAdmissionsController::class, 'checklist'])->name('checklist');
    Route::get('/download/{type}', [PublicAdmissionsController::class, 'downloadBrochure'])->name('download');
});

// ============================================================
// ADMISSION PORTAL (Mixed Auth - Guest and Authenticated)
// ============================================================
Route::prefix('admissions/portal')->name('admissions.portal.')->group(function () {
    
    // Portal landing page (no auth required)
    Route::get('/', [AdmissionsPortalController::class, 'index'])->name('index');
    
    // ============================================================
    // PHASE 1: START APPLICATION (Creates UUID)
    // ============================================================
    Route::get('/start', [AdmissionsPortalController::class, 'startApplication'])->name('start');
    Route::post('/create', [AdmissionsPortalController::class, 'createApplication'])->name('create');
    
    // Application status check (no auth, uses application number)
    Route::get('/status', [ApplicationFormController::class, 'checkStatus'])->name('status');
    Route::post('/status', [AdmissionsPortalController::class, 'verifyStatus'])->name('status.verify');
    
    // Continue saved application
    Route::get('/continue', [AdmissionsPortalController::class, 'continueApplication'])->name('continue');
    Route::post('/continue', [AdmissionsPortalController::class, 'findApplication'])->name('continue.find');
    Route::post('/find', [AdmissionsPortalController::class, 'findApplication'])->name('find');
    Route::post('/recover', [AdmissionsPortalController::class, 'recoverApplications'])->name('recover');

    
    // ============================================================
    // AUTHENTICATED PORTAL ROUTES (For users with accounts)
    // ============================================================
    Route::middleware(['auth'])->group(function () {
        Route::get('/dashboard', [AdmissionsPortalController::class, 'applicantDashboard'])->name('dashboard');
        Route::get('/my-applications', [AdmissionsPortalController::class, 'myApplications'])->name('my-applications');
        Route::get('/create-new', [AdmissionsPortalController::class, 'createNewApplication'])->name('create-new');
        Route::get('/track/{uuid}', [AdmissionsPortalController::class, 'trackApplication'])->name('track');
    });
});

// ============================================================
// PHASE 2: DETAILED APPLICATION FORM (UUID-Based, No Auth Required)
// ============================================================
Route::prefix('application/{uuid}')->name('admissions.portal.application.')->group(function () {
    
    // Main consolidated form - dynamically shows relevant sections based on application type
    Route::get('/', [ApplicationFormController::class, 'showApplicationForm'])->name('index');
    
    // These all redirect to the consolidated form with appropriate section
    Route::get('/academic', [ApplicationFormController::class, 'academicInfo'])->name('academic');
    Route::get('/test-scores', [ApplicationFormController::class, 'testScores'])->name('test-scores');
    Route::get('/essays', [ApplicationFormController::class, 'essays'])->name('essays');
    Route::get('/documents', [ApplicationFormController::class, 'documents'])->name('documents');
    Route::get('/recommendations', [ApplicationFormController::class, 'recommendations'])->name('recommendations');
    
    // Additional sections for specific application types
    Route::get('/college-courses', [ApplicationFormController::class, 'collegeCourses'])->name('college-courses'); // Transfer students
    Route::get('/research', [ApplicationFormController::class, 'researchExperience'])->name('research'); // Graduate students
    Route::get('/portfolio', [ApplicationFormController::class, 'portfolio'])->name('portfolio'); // Arts programs
    Route::get('/english-proficiency', [ApplicationFormController::class, 'englishProficiency'])->name('english-proficiency'); // International
    Route::get('/financial', [ApplicationFormController::class, 'financialInfo'])->name('financial'); // International
    
    // Review and submission
    Route::get('/review', [ApplicationFormController::class, 'reviewApplication'])->name('review');
    Route::get('/preview', [ApplicationFormController::class, 'preview'])->name('preview');
    Route::get('/confirmation', [ApplicationFormController::class, 'confirmation'])->name('confirmation');
    
    // AJAX endpoints for saving and submission
    Route::post('/save-section', [ApplicationFormController::class, 'saveSection'])->name('save-section');
    Route::post('/save-draft', [ApplicationFormController::class, 'saveDraft'])->name('save-draft');
    Route::post('/validate-section', [ApplicationFormController::class, 'validateSection'])->name('validate-section');
    Route::get('/review-summary', [ApplicationFormController::class, 'getReviewSummary'])->name('review-summary');
    Route::post('/submit', [ApplicationFormController::class, 'submit'])->name('submit');
    
    // ============================================================
    // POST-SUBMISSION ROUTES (New additions)
    // ============================================================
    Route::get('/download-receipt', [ApplicationFormController::class, 'downloadReceipt'])->name('receipt');
    Route::get('/resend-confirmation', [ApplicationFormController::class, 'resendConfirmation'])->name('resend-confirmation');
    Route::post('/resend-confirmation', [ApplicationFormController::class, 'processResendConfirmation'])->name('resend-confirmation.process');
    
    // Document management
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/', [ApplicationDocumentController::class, 'index'])->name('index');
        Route::post('/upload', [ApplicationDocumentController::class, 'upload'])->name('upload');
        Route::delete('/{id}', [ApplicationDocumentController::class, 'delete'])->name('delete');
        Route::get('/{id}/download', [ApplicationDocumentController::class, 'download'])->name('download');
        Route::get('/{id}/preview', [ApplicationDocumentController::class, 'preview'])->name('preview');
        Route::post('/verify/{id}', [ApplicationDocumentController::class, 'markVerified'])->name('verify');
    });
    
    // Recommendation management
    Route::prefix('recommendations')->name('recommendations.')->group(function () {
        Route::post('/send-request', [ApplicationFormController::class, 'sendRecommendationRequest'])->name('send');
        Route::get('/status/{id}', [ApplicationFormController::class, 'recommendationStatus'])->name('status');
        Route::post('/resend/{id}', [ApplicationFormController::class, 'resendRequest'])->name('resend');
        Route::delete('/{id}', [ApplicationFormController::class, 'removeRecommender'])->name('delete');
    });
    
    // Payment
    Route::get('/payment', [ApplicationFormController::class, 'payment'])->name('payment');
    Route::post('/payment/process', [ApplicationFormController::class, 'processPayment'])->name('payment.process');
    Route::get('/payment/success', [ApplicationFormController::class, 'paymentSuccess'])->name('payment.success');
    Route::get('/payment/cancel', [ApplicationFormController::class, 'paymentCancel'])->name('payment.cancel');
});

// ============================================================
// RECOMMENDATION LETTER SUBMISSION (External Access)
// ============================================================
Route::prefix('recommendation/{token}')->name('recommendation.')->group(function () {
    Route::get('/', [ApplicationFormController::class, 'recommendationForm'])->name('form');
    Route::post('/submit', [ApplicationFormController::class, 'submitRecommendation'])->name('submit');
    Route::get('/thank-you', [ApplicationFormController::class, 'recommendationThankYou'])->name('thank-you');
});

// ============================================================
// APPLICATION STATUS (Public with Application Number)
// ============================================================
Route::prefix('application-status')->name('application.status.')->group(function () {
    Route::get('/', [AdmissionsPortalController::class, 'statusLookup'])->name('lookup');
    Route::post('/check', [AdmissionsPortalController::class, 'checkApplicationStatus'])->name('check');
    Route::get('/{uuid}/view', [AdmissionsPortalController::class, 'viewApplicationStatus'])->name('view');
    Route::get('/{uuid}/decision', [AdmissionsPortalController::class, 'viewDecision'])->name('decision');
    Route::get('/{uuid}/download-letter', [AdmissionsPortalController::class, 'downloadDecisionLetter'])->name('download-letter');
});

// ============================================================
// ENROLLMENT PORTAL (Post-Admission)
// ============================================================
Route::prefix('enrollment')->name('enrollment.')->group(function () {
    
    // Public enrollment confirmation (uses UUID)
    Route::get('/confirm/{uuid}', [EnrollmentPortalController::class, 'confirmEnrollment'])->name('confirm');
    Route::post('/accept/{uuid}', [EnrollmentPortalController::class, 'acceptOffer'])->name('accept');
    Route::post('/decline/{uuid}', [EnrollmentPortalController::class, 'declineOffer'])->name('decline');
    Route::get('/deposit/{uuid}', [EnrollmentPortalController::class, 'enrollmentDeposit'])->name('deposit');
    Route::post('/deposit/{uuid}/pay', [EnrollmentPortalController::class, 'payDeposit'])->name('deposit.pay');
    
    // Authenticated enrollment portal
    Route::middleware(['auth'])->group(function () {
        Route::get('/dashboard', [EnrollmentPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/status', [EnrollmentPortalController::class, 'enrollmentStatus'])->name('status');
        Route::get('/checklist', [EnrollmentPortalController::class, 'enrollmentChecklist'])->name('checklist');
        
        // Post-acceptance documents
        Route::get('/documents', [EnrollmentPortalController::class, 'enrollmentDocuments'])->name('documents');
        Route::post('/documents/upload', [EnrollmentPortalController::class, 'uploadDocument'])->name('documents.upload');
        Route::get('/documents/download/{id}', [EnrollmentPortalController::class, 'downloadDocument'])->name('documents.download');
        
        // Orientation registration
        Route::get('/orientation', [EnrollmentPortalController::class, 'orientationInfo'])->name('orientation');
        Route::post('/orientation/register', [EnrollmentPortalController::class, 'registerOrientation'])->name('orientation.register');
        Route::get('/orientation/schedule', [EnrollmentPortalController::class, 'orientationSchedule'])->name('orientation.schedule');
        
        // Housing application
        Route::get('/housing', [EnrollmentPortalController::class, 'housingApplication'])->name('housing');
        Route::post('/housing/apply', [EnrollmentPortalController::class, 'applyForHousing'])->name('housing.apply');
        
        // ID Card request
        Route::get('/id-card', [EnrollmentPortalController::class, 'idCardRequest'])->name('id-card');
        Route::post('/id-card/request', [EnrollmentPortalController::class, 'submitIdCardRequest'])->name('id-card.request');
        
        // Course selection (pre-registration)
        Route::get('/course-selection', [EnrollmentPortalController::class, 'courseSelection'])->name('course-selection');
        Route::post('/course-selection/save', [EnrollmentPortalController::class, 'saveCourseSelection'])->name('course-selection.save');
        
        // Final enrollment letter
        Route::get('/enrollment-letter', [EnrollmentPortalController::class, 'downloadEnrollmentLetter'])->name('enrollment-letter');
    });
});

// ============================================================
// ENTRANCE EXAMS (For programs requiring exams)
// ============================================================
Route::prefix('exams')->name('exams.')->group(function () {
    Route::get('/information', [ExamPortalController::class, 'information'])->name('information');
    Route::get('/register/{uuid}', [ExamPortalController::class, 'registerForExam'])->name('register');
    Route::post('/register/{uuid}', [ExamPortalController::class, 'processExamRegistration'])->name('register.process');
    Route::get('/hall-ticket/{uuid}', [ExamPortalController::class, 'downloadHallTicket'])->name('hall-ticket');
    Route::get('/results/{uuid}', [ExamPortalController::class, 'viewResults'])->name('results');
});

// ============================================================
// ADMISSIONS ADMINISTRATION (Staff/Admin Access)
// ============================================================
Route::prefix('admin/admissions')->name('admin.admissions.')->middleware(['auth', 'role:admissions-officer,admissions-admin,registrar,admin'])->group(function () {
    
    // Dashboard & Overview
    Route::get('/', [AdminApplicationController::class, 'dashboard'])->name('dashboard');
    Route::get('/overview', [AdminApplicationController::class, 'overview'])->name('overview');
    Route::get('/statistics', [AdminApplicationController::class, 'statistics'])->name('statistics');
    Route::get('/calendar', [AdminApplicationController::class, 'admissionsCalendar'])->name('calendar');
    
    // Application Management
    Route::prefix('applications')->name('applications.')->group(function () {
        Route::get('/', [AdminApplicationController::class, 'index'])->name('index');
        Route::get('/pending', [AdminApplicationController::class, 'pending'])->name('pending');
        Route::get('/under-review', [AdminApplicationController::class, 'underReview'])->name('under-review');
        Route::get('/incomplete', [AdminApplicationController::class, 'incomplete'])->name('incomplete');
        Route::get('/{id}', [AdminApplicationController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [AdminApplicationController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AdminApplicationController::class, 'update'])->name('update');
        
        // Status and workflow routes
        Route::post('/{id}/status', [AdminApplicationController::class, 'updateStatus'])->name('status');
        Route::post('/{id}/update-status', [AdminApplicationController::class, 'updateStatus'])->name('update-status');
        Route::post('/{id}/flag', [AdminApplicationController::class, 'flagApplication'])->name('flag');
        
        // Notes and communication
        Route::post('/{id}/notes', [AdminApplicationController::class, 'addNote'])->name('notes');
        Route::get('/{id}/audit-log', [AdminApplicationController::class, 'auditLog'])->name('audit-log');
        
        // Review management
        Route::post('/{id}/send-to-review', [AdminApplicationController::class, 'sendToReview'])->name('send-to-review');
        Route::post('/{id}/assign-reviewer', [AdminApplicationController::class, 'assignReviewer'])->name('assign-reviewer');
        
        // Decision management
        Route::post('/{id}/decide', [AdminApplicationController::class, 'makeDecision'])->name('decide');
        
        // Delete
        Route::delete('/{id}', [AdminApplicationController::class, 'destroy'])->name('destroy');
        
        // Export routes
        Route::get('/export/all', [AdminApplicationController::class, 'exportAll'])->name('export.all');
        Route::post('/export/selected', [AdminApplicationController::class, 'exportSelected'])->name('export.selected');
        
        // Bulk actions
        Route::post('/bulk-action', [AdminApplicationController::class, 'bulkAction'])->name('bulk-action');
    });
    
    // Review Management
    Route::prefix('reviews')->name('reviews.')->group(function () {
        Route::get('/', [ApplicationReviewController::class, 'index'])->name('index');
        Route::get('/my-reviews', [ApplicationReviewController::class, 'myReviews'])->name('my-reviews');
        Route::get('/pending', [ApplicationReviewController::class, 'pendingReviews'])->name('pending');
        Route::get('/application/{id}', [ApplicationReviewController::class, 'reviewApplication'])->name('application');
        Route::post('/application/{id}', [ApplicationReviewController::class, 'submitReview'])->name('submit');
        Route::get('/compare', [ApplicationReviewController::class, 'compareApplications'])->name('compare');
        Route::post('/assign', [ApplicationReviewController::class, 'assignReviewer'])->name('assign');
        Route::post('/reassign', [ApplicationReviewController::class, 'reassignReviewer'])->name('reassign');
        Route::get('/rubric', [ApplicationReviewController::class, 'reviewRubric'])->name('rubric');
        Route::get('/committee', [ApplicationReviewController::class, 'committeeReview'])->name('committee');
        Route::post('/committee/schedule', [ApplicationReviewController::class, 'scheduleCommittee'])->name('committee.schedule');
    });
    
    // Decision Management
    Route::prefix('decisions')->name('decisions.')->middleware('role:admissions-admin,admissions-director,dean')->group(function () {
        Route::get('/', [AdmissionDecisionController::class, 'index'])->name('index');
        Route::get('/pending', [AdmissionDecisionController::class, 'pendingDecisions'])->name('pending');
        Route::get('/review/{id}', [AdmissionDecisionController::class, 'reviewForDecision'])->name('review');
        Route::post('/{id}/decide', [AdmissionDecisionController::class, 'makeDecision'])->name('decide');
        Route::post('/batch', [AdmissionDecisionController::class, 'batchDecision'])->name('batch');
        Route::post('/{id}/revoke', [AdmissionDecisionController::class, 'revokeAdmission'])->name('revoke');
        Route::get('/letters/preview/{id}', [AdmissionDecisionController::class, 'previewLetter'])->name('letters.preview');
        Route::post('/letters/send', [AdmissionDecisionController::class, 'sendDecisionLetters'])->name('letters.send');
        Route::get('/waitlist', [AdmissionDecisionController::class, 'waitlistManagement'])->name('waitlist');
        Route::post('/waitlist/promote', [AdmissionDecisionController::class, 'promoteFromWaitlist'])->name('waitlist.promote');
        Route::get('/yield', [AdmissionDecisionController::class, 'yieldTracking'])->name('yield');
    });
    
    // Document Verification
    Route::prefix('verification')->name('verification.')->group(function () {
        Route::get('/', [DocumentVerificationController::class, 'index'])->name('index');
        Route::get('/pending', [DocumentVerificationController::class, 'pending'])->name('pending');
        Route::get('/document/{id}', [DocumentVerificationController::class, 'viewDocument'])->name('view');
        Route::post('/document/{id}/verify', [DocumentVerificationController::class, 'verifyDocument'])->name('verify');
        Route::post('/document/{id}/reject', [DocumentVerificationController::class, 'rejectDocument'])->name('reject');
        Route::post('/bulk-verify', [DocumentVerificationController::class, 'bulkVerify'])->name('bulk-verify');
    });
    
    // Communication
    Route::prefix('communications')->name('communications.')->group(function () {
        Route::get('/', [AdminApplicationController::class, 'communications'])->name('index');
        Route::get('/templates', [AdminApplicationController::class, 'emailTemplates'])->name('templates');
        Route::get('/templates/create', [AdminApplicationController::class, 'createTemplate'])->name('templates.create');
        Route::post('/templates', [AdminApplicationController::class, 'storeTemplate'])->name('templates.store');
        Route::get('/templates/{id}/edit', [AdminApplicationController::class, 'editTemplate'])->name('templates.edit');
        Route::put('/templates/{id}', [AdminApplicationController::class, 'updateTemplate'])->name('templates.update');
        Route::post('/send-email', [AdminApplicationController::class, 'sendEmail'])->name('send-email');
        Route::post('/send-bulk', [AdminApplicationController::class, 'sendBulkEmail'])->name('send-bulk');
        Route::get('/history', [AdminApplicationController::class, 'communicationHistory'])->name('history');
    });
    
    // Reports & Analytics
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [AdmissionsReportController::class, 'index'])->name('index');
        Route::get('/applications', [AdmissionsReportController::class, 'applicationsReport'])->name('applications');
        Route::get('/demographics', [AdmissionsReportController::class, 'demographicsReport'])->name('demographics');
        Route::get('/conversion', [AdmissionsReportController::class, 'conversionReport'])->name('conversion');
        Route::get('/geographic', [AdmissionsReportController::class, 'geographicReport'])->name('geographic');
        Route::get('/program-analysis', [AdmissionsReportController::class, 'programAnalysis'])->name('program-analysis');
        Route::get('/yield-analysis', [AdmissionsReportController::class, 'yieldAnalysis'])->name('yield-analysis');
        Route::get('/custom', [AdmissionsReportController::class, 'customReport'])->name('custom');
        Route::post('/generate', [AdmissionsReportController::class, 'generateReport'])->name('generate');
        Route::get('/export/{type}', [AdmissionsReportController::class, 'exportReport'])->name('export');
    });
    
    // Settings & Configuration
    Route::prefix('settings')->name('settings.')->middleware('role:admissions-admin,admin')->group(function () {
        Route::get('/', [AdminApplicationController::class, 'settings'])->name('index');
        Route::put('/general', [AdminApplicationController::class, 'updateGeneralSettings'])->name('general');
        
        // Admission cycles
        Route::get('/cycles', [AdminApplicationController::class, 'admissionCycles'])->name('cycles');
        Route::get('/cycles/create', [AdminApplicationController::class, 'createCycleForm'])->name('cycles.create');
        Route::post('/cycles', [AdminApplicationController::class, 'createCycle'])->name('cycles.store');
        Route::get('/cycles/{id}/edit', [AdminApplicationController::class, 'editCycle'])->name('cycles.edit');
        Route::put('/cycles/{id}', [AdminApplicationController::class, 'updateCycle'])->name('cycles.update');
        Route::delete('/cycles/{id}', [AdminApplicationController::class, 'deleteCycle'])->name('cycles.delete');
        
        // Requirements configuration
        Route::get('/requirements', [AdminApplicationController::class, 'requirements'])->name('requirements');
        Route::put('/requirements', [AdminApplicationController::class, 'updateRequirements'])->name('requirements.update');
        Route::get('/requirements/program/{programId}', [AdminApplicationController::class, 'programRequirements'])->name('requirements.program');
        Route::put('/requirements/program/{programId}', [AdminApplicationController::class, 'updateProgramRequirements'])->name('requirements.program.update');
        
        // Fee structure
        Route::get('/fees', [AdminApplicationController::class, 'feeStructure'])->name('fees');
        Route::put('/fees', [AdminApplicationController::class, 'updateFeeStructure'])->name('fees.update');
        Route::post('/fees/waiver', [AdminApplicationController::class, 'createFeeWaiver'])->name('fees.waiver');
        
        // Scoring rubric
        Route::get('/rubric', [AdminApplicationController::class, 'scoringRubric'])->name('rubric');
        Route::put('/rubric', [AdminApplicationController::class, 'updateScoringRubric'])->name('rubric.update');
        
        // Checklist templates
        Route::get('/checklists', [AdminApplicationController::class, 'checklistTemplates'])->name('checklists');
        Route::get('/checklists/create', [AdminApplicationController::class, 'createChecklistTemplate'])->name('checklists.create');
        Route::post('/checklists', [AdminApplicationController::class, 'saveChecklistTemplate'])->name('checklists.store');
        Route::get('/checklists/{id}/edit', [AdminApplicationController::class, 'editChecklistTemplate'])->name('checklists.edit');
        Route::put('/checklists/{id}', [AdminApplicationController::class, 'updateChecklistTemplate'])->name('checklists.update');
        Route::delete('/checklists/{id}', [AdminApplicationController::class, 'deleteChecklistTemplate'])->name('checklists.delete');
    });
});

// ============================================================
// REGISTRAR ACCESS (Special permissions for enrollment)
// ============================================================
Route::prefix('registrar/admissions')->name('registrar.admissions.')->middleware(['auth', 'role:registrar,academic-administrator'])->group(function () {
    Route::get('/enrolled', [AdmissionsController::class, 'enrolledStudents'])->name('enrolled');
    Route::get('/matriculation', [AdmissionsController::class, 'matriculationList'])->name('matriculation');
    Route::post('/convert-to-student/{id}', [AdmissionsController::class, 'convertToStudent'])->name('convert');
    Route::get('/enrollment-verification/{id}', [AdmissionsController::class, 'enrollmentVerification'])->name('verification');
});

// ============================================================
// WEBHOOKS & CALLBACKS (No Auth - External Services)
// ============================================================
Route::prefix('webhooks/admissions')->name('webhooks.admissions.')->group(function () {
    Route::post('/payment-callback', [ApplicationFormController::class, 'paymentCallback'])->name('payment.callback');
    Route::post('/document-verification', [DocumentVerificationController::class, 'externalVerification'])->name('document.verification');
    Route::post('/test-scores', [ApplicationFormController::class, 'receiveTestScores'])->name('test-scores');
});