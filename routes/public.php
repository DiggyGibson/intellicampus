<?php
/**
 * IntelliCampus Public Routes
 * 
 * Routes accessible without authentication.
 * These routes are for public-facing pages and information.
 */

use App\Http\Controllers\PublicAdmissionsController;
use App\Http\Controllers\PublicExamController;
use App\Http\Controllers\AdmissionsPortalController;
use App\Http\Controllers\ApplicationFormController;
use App\Http\Controllers\ApplicationDocumentController;
use Illuminate\Support\Facades\Route;

// ============================================================
// PUBLIC INFORMATION PAGES
// ============================================================
Route::prefix('about')->name('about.')->group(function () {
    Route::get('/', function() { return view('public.about.index'); })->name('index');
    Route::get('/history', function() { return view('public.about.history'); })->name('history');
    Route::get('/mission', function() { return view('public.about.mission'); })->name('mission');
    Route::get('/leadership', function() { return view('public.about.leadership'); })->name('leadership');
    Route::get('/accreditation', function() { return view('public.about.accreditation'); })->name('accreditation');
    Route::get('/campus-map', function() { return view('public.about.campus-map'); })->name('campus-map');
    Route::get('/virtual-tour', function() { return view('public.about.virtual-tour'); })->name('virtual-tour');
});

// ============================================================
// ACADEMIC INFORMATION
// ============================================================
Route::prefix('academics')->name('academics.')->group(function () {
    Route::get('/', function() { return view('public.academics.index'); })->name('index');
    Route::get('/programs', function() { return view('public.academics.programs'); })->name('programs');
    Route::get('/programs/{slug}', function($slug) { 
        return view('public.academics.program-details', compact('slug')); 
    })->name('program-details');
    Route::get('/departments', function() { return view('public.academics.departments'); })->name('departments');
    Route::get('/calendar', function() { return view('public.academics.calendar'); })->name('calendar');
    Route::get('/catalog', function() { return view('public.academics.catalog'); })->name('catalog');
    Route::get('/faculty-directory', function() { return view('public.academics.faculty'); })->name('faculty');
});

// ============================================================
// ADMISSIONS INFORMATION
// ============================================================
Route::prefix('admissions')->name('admissions.')->group(function () {
    Route::get('/', [PublicAdmissionsController::class, 'index'])->name('index');
    Route::get('/requirements', [PublicAdmissionsController::class, 'requirements'])->name('requirements');
    Route::get('/programs', [PublicAdmissionsController::class, 'programs'])->name('programs');
    Route::get('/deadlines', [PublicAdmissionsController::class, 'deadlines'])->name('deadlines');
    Route::get('/tuition-fees', [PublicAdmissionsController::class, 'tuitionFees'])->name('tuition');
    Route::get('/financial-aid', [PublicAdmissionsController::class, 'financialAid'])->name('financial-aid');
    Route::get('/international-students', [PublicAdmissionsController::class, 'international'])->name('international');
    Route::get('/transfer-students', [PublicAdmissionsController::class, 'transfer'])->name('transfer');
    Route::get('/graduate-programs', [PublicAdmissionsController::class, 'graduate'])->name('graduate');
    Route::get('/contact', [PublicAdmissionsController::class, 'contact'])->name('contact');
    Route::post('/inquiry', [PublicAdmissionsController::class, 'submitInquiry'])->name('inquiry.submit');
    Route::get('/faq', [PublicAdmissionsController::class, 'faq'])->name('faq');
    Route::get('/visit', [PublicAdmissionsController::class, 'visitCampus'])->name('visit');
    Route::post('/schedule-visit', [PublicAdmissionsController::class, 'scheduleVisit'])->name('schedule-visit');
});

// ============================================================
// APPLICATION PORTAL (Public Entry)
// ============================================================
Route::prefix('apply')->name('apply.')->group(function () {
    // Initial application access
    Route::get('/', [AdmissionsPortalController::class, 'index'])->name('index');
    Route::get('/start', [AdmissionsPortalController::class, 'startApplication'])->name('start');
    Route::post('/create', [AdmissionsPortalController::class, 'createApplication'])->name('create');
    
    // Check application status (no auth required with UUID)
    Route::get('/status', [AdmissionsPortalController::class, 'checkStatusForm'])->name('status.form');
    Route::post('/status', [AdmissionsPortalController::class, 'checkStatus'])->name('status.check');
    Route::get('/status/{uuid}', [AdmissionsPortalController::class, 'viewStatus'])->name('status.view');
    
    // Continue application (with UUID)
    Route::get('/continue', [AdmissionsPortalController::class, 'continueForm'])->name('continue.form');
    Route::post('/continue', [AdmissionsPortalController::class, 'continueApplication'])->name('continue.check');
    Route::get('/continue/{uuid}', [AdmissionsPortalController::class, 'resumeApplication'])->name('continue.resume');
    
    // Application form sections (UUID-based, no auth required)
    Route::prefix('form/{uuid}')->name('form.')->group(function () {
        Route::get('/', [ApplicationFormController::class, 'index'])->name('index');
        
        // Personal Information
        Route::get('/personal', [ApplicationFormController::class, 'personalInfo'])->name('personal');
        Route::post('/personal', [ApplicationFormController::class, 'savePersonalInfo'])->name('personal.save');
        
        // Educational Background
        Route::get('/education', [ApplicationFormController::class, 'educationalBackground'])->name('education');
        Route::post('/education', [ApplicationFormController::class, 'saveEducationalBackground'])->name('education.save');
        Route::post('/education/add-school', [ApplicationFormController::class, 'addSchool'])->name('education.add-school');
        Route::delete('/education/remove-school/{id}', [ApplicationFormController::class, 'removeSchool'])->name('education.remove-school');
        
        // Test Scores
        Route::get('/test-scores', [ApplicationFormController::class, 'testScores'])->name('test-scores');
        Route::post('/test-scores', [ApplicationFormController::class, 'saveTestScores'])->name('test-scores.save');
        
        // Essays & Statements
        Route::get('/essays', [ApplicationFormController::class, 'essays'])->name('essays');
        Route::post('/essays', [ApplicationFormController::class, 'saveEssays'])->name('essays.save');
        Route::post('/essays/auto-save', [ApplicationFormController::class, 'autoSaveEssay'])->name('essays.auto-save');
        
        // Extracurricular Activities
        Route::get('/activities', [ApplicationFormController::class, 'activities'])->name('activities');
        Route::post('/activities', [ApplicationFormController::class, 'saveActivities'])->name('activities.save');
        Route::post('/activities/add', [ApplicationFormController::class, 'addActivity'])->name('activities.add');
        Route::delete('/activities/{id}', [ApplicationFormController::class, 'removeActivity'])->name('activities.remove');
        
        // References & Recommendations
        Route::get('/references', [ApplicationFormController::class, 'references'])->name('references');
        Route::post('/references', [ApplicationFormController::class, 'saveReferences'])->name('references.save');
        Route::post('/references/send-request', [ApplicationFormController::class, 'sendReferenceRequest'])->name('references.send');
        Route::get('/references/status/{id}', [ApplicationFormController::class, 'referenceStatus'])->name('references.status');
        
        // Documents Upload
        Route::get('/documents', [ApplicationFormController::class, 'documents'])->name('documents');
        Route::post('/documents/upload', [ApplicationDocumentController::class, 'upload'])->name('documents.upload');
        Route::delete('/documents/{id}', [ApplicationDocumentController::class, 'delete'])->name('documents.delete');
        Route::get('/documents/{id}/preview', [ApplicationDocumentController::class, 'preview'])->name('documents.preview');
        
        // Program Selection
        Route::get('/programs', [ApplicationFormController::class, 'programSelection'])->name('programs');
        Route::post('/programs', [ApplicationFormController::class, 'saveProgramSelection'])->name('programs.save');
        
        // Review & Submit
        Route::get('/review', [ApplicationFormController::class, 'review'])->name('review');
        Route::post('/validate', [ApplicationFormController::class, 'validateApplication'])->name('validate');
        Route::post('/submit', [ApplicationFormController::class, 'submit'])->name('submit');
        Route::get('/confirmation', [ApplicationFormController::class, 'confirmation'])->name('confirmation');
        
        // Payment
        Route::get('/payment', [ApplicationFormController::class, 'payment'])->name('payment');
        Route::post('/payment/process', [ApplicationFormController::class, 'processPayment'])->name('payment.process');
        Route::get('/payment/success', [ApplicationFormController::class, 'paymentSuccess'])->name('payment.success');
        Route::get('/payment/cancel', [ApplicationFormController::class, 'paymentCancel'])->name('payment.cancel');
        
        // AJAX endpoints
        Route::post('/save-progress', [ApplicationFormController::class, 'saveProgress'])->name('save-progress');
        Route::post('/validate-section', [ApplicationFormController::class, 'validateSection'])->name('validate-section');
    });
    
    // Download submitted application (requires verification)
    Route::get('/download/{uuid}', [AdmissionsPortalController::class, 'downloadApplication'])->name('download');
    Route::post('/download/verify', [AdmissionsPortalController::class, 'verifyDownload'])->name('download.verify');
});

// ============================================================
// ENTRANCE EXAM INFORMATION
// ============================================================
Route::prefix('entrance-exams')->name('exams.')->group(function () {
    Route::get('/', [PublicExamController::class, 'index'])->name('index');
    Route::get('/information', [PublicExamController::class, 'information'])->name('information');
    Route::get('/schedule', [PublicExamController::class, 'schedule'])->name('schedule');
    Route::get('/syllabus/{examCode}', [PublicExamController::class, 'syllabus'])->name('syllabus');
    Route::get('/sample-papers', [PublicExamController::class, 'samplePapers'])->name('sample-papers');
    Route::get('/sample-papers/{examCode}/download', [PublicExamController::class, 'downloadSamplePaper'])->name('sample.download');
    Route::get('/preparation-guide', [PublicExamController::class, 'preparationGuide'])->name('prep-guide');
    Route::get('/exam-centers', [PublicExamController::class, 'examCenters'])->name('centers');
    Route::get('/results', [PublicExamController::class, 'resultsForm'])->name('results.form');
    Route::post('/results', [PublicExamController::class, 'checkResults'])->name('results.check');
    Route::get('/statistics', [PublicExamController::class, 'statistics'])->name('statistics');
    Route::get('/faq', [PublicExamController::class, 'faq'])->name('faq');

    // Exam Portal Routes (candidate-facing, some require auth)
    Route::prefix('portal')->name('portal.')->group(function () {
        // Public portal pages
        Route::get('/available', [ExamPortalController::class, 'availableExams'])->name('available');
        Route::get('/register/{examId}', [ExamPortalController::class, 'registerForm'])->name('register');
        Route::post('/register/{examId}', [ExamPortalController::class, 'submitRegistration'])->name('register.submit');
        
        // Authenticated portal pages
        Route::middleware(['auth'])->group(function () {
            Route::get('/', [ExamPortalController::class, 'candidatePortal'])->name('index');
            Route::get('/my-registrations', [ExamPortalController::class, 'myRegistrations'])->name('my-registrations');
            Route::get('/registration/{registrationId}', [ExamPortalController::class, 'viewRegistration'])->name('registration.view');
            Route::get('/hall-ticket/{registrationId}', [ExamPortalController::class, 'hallTicket'])->name('hall-ticket');
            Route::get('/hall-ticket/{registrationId}/download', [ExamPortalController::class, 'downloadHallTicket'])->name('hall-ticket.download');
            Route::get('/results/{registrationId}', [ExamPortalController::class, 'viewResults'])->name('results');
            
            // Online exam routes
            Route::get('/online/{registrationId}/start', [ExamPortalController::class, 'startOnlineExam'])->name('online.start');
            Route::post('/online/save-answer', [ExamPortalController::class, 'saveAnswer'])->name('online.save-answer');
            Route::post('/online/{registrationId}/submit', [ExamPortalController::class, 'submitExam'])->name('online.submit');
        });
    });
});

// ============================================================
// STUDENT LIFE INFORMATION
// ============================================================
Route::prefix('student-life')->name('student-life.')->group(function () {
    Route::get('/', function() { return view('public.student-life.index'); })->name('index');
    Route::get('/housing', function() { return view('public.student-life.housing'); })->name('housing');
    Route::get('/dining', function() { return view('public.student-life.dining'); })->name('dining');
    Route::get('/health-wellness', function() { return view('public.student-life.health'); })->name('health');
    Route::get('/organizations', function() { return view('public.student-life.organizations'); })->name('organizations');
    Route::get('/athletics', function() { return view('public.student-life.athletics'); })->name('athletics');
    Route::get('/recreation', function() { return view('public.student-life.recreation'); })->name('recreation');
    Route::get('/safety', function() { return view('public.student-life.safety'); })->name('safety');
});

// ============================================================
// NEWS & EVENTS
// ============================================================
Route::prefix('news')->name('news.')->group(function () {
    Route::get('/', function() { return view('public.news.index'); })->name('index');
    Route::get('/announcements', function() { return view('public.news.announcements'); })->name('announcements');
    Route::get('/events', function() { return view('public.news.events'); })->name('events');
    Route::get('/article/{slug}', function($slug) { 
        return view('public.news.article', compact('slug')); 
    })->name('article');
    Route::get('/event/{slug}', function($slug) { 
        return view('public.news.event-details', compact('slug')); 
    })->name('event-details');
    Route::get('/archive', function() { return view('public.news.archive'); })->name('archive');
});

// ============================================================
// CONTACT & SUPPORT
// ============================================================
Route::prefix('contact')->name('contact.')->group(function () {
    Route::get('/', function() { return view('public.contact.index'); })->name('index');
    Route::post('/submit', function() { 
        // Handle contact form submission
        return back()->with('success', 'Your message has been sent successfully.');
    })->name('submit');
    Route::get('/directory', function() { return view('public.contact.directory'); })->name('directory');
    Route::get('/locations', function() { return view('public.contact.locations'); })->name('locations');
    Route::get('/emergency', function() { return view('public.contact.emergency'); })->name('emergency');
});

// ============================================================
// LEGAL & POLICIES
// ============================================================
Route::prefix('policies')->name('policies.')->group(function () {
    Route::get('/privacy', function() { return view('public.policies.privacy'); })->name('privacy');
    Route::get('/terms', function() { return view('public.policies.terms'); })->name('terms');
    Route::get('/accessibility', function() { return view('public.policies.accessibility'); })->name('accessibility');
    Route::get('/non-discrimination', function() { return view('public.policies.non-discrimination'); })->name('non-discrimination');
    Route::get('/ferpa', function() { return view('public.policies.ferpa'); })->name('ferpa');
    Route::get('/title-ix', function() { return view('public.policies.title-ix'); })->name('title-ix');
});

// ============================================================
// SEARCH
// ============================================================
Route::get('/search', function() {
    return view('public.search.results');
})->name('search');

Route::post('/search', function(\Illuminate\Http\Request $request) {
    $query = $request->input('q');
    // Implement public search logic
    return view('public.search.results', compact('query'));
})->name('search.results');

// ============================================================
// SITEMAP & ROBOTS
// ============================================================
Route::get('/sitemap.xml', function() {
    $content = view('public.sitemap')->render();
    return response($content, 200)->header('Content-Type', 'text/xml');
})->name('sitemap');

Route::get('/robots.txt', function() {
    $content = "User-agent: *\n";
    $content .= "Disallow: /admin/\n";
    $content .= "Disallow: /api/\n";
    $content .= "Disallow: /student/\n";
    $content .= "Disallow: /faculty/\n";
    $content .= "Allow: /\n";
    $content .= "Sitemap: " . url('/sitemap.xml');
    
    return response($content, 200)->header('Content-Type', 'text/plain');
})->name('robots');