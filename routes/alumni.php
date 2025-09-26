<?php
/**
 * IntelliCampus Alumni Management Routes
 * 
 * Routes for alumni relations, engagement, and services.
 * These routes are automatically prefixed with 'alumni' and named with 'alumni.'
 * Base middleware: 'web'
 * Note: This is an optional module - check if enabled before loading
 */

use App\Http\Controllers\AlumniController;
use App\Http\Controllers\AlumniServicesController;
use App\Http\Controllers\AlumniEventsController;
use App\Http\Controllers\AlumniDonationsController;
use Illuminate\Support\Facades\Route;

// ============================================================
// PUBLIC ALUMNI PORTAL (No Auth Required)
// ============================================================
Route::prefix('portal')->name('portal.')->group(function () {
    Route::get('/', [AlumniController::class, 'publicPortal'])->name('index');
    Route::get('/about', [AlumniController::class, 'about'])->name('about');
    Route::get('/benefits', [AlumniController::class, 'benefits'])->name('benefits');
    Route::get('/join', [AlumniController::class, 'joinForm'])->name('join');
    Route::post('/register', [AlumniController::class, 'register'])->name('register');
    Route::get('/verify-email/{token}', [AlumniController::class, 'verifyEmail'])->name('verify-email');
    Route::get('/news', [AlumniController::class, 'alumniNews'])->name('news');
    Route::get('/magazine', [AlumniController::class, 'alumniMagazine'])->name('magazine');
    Route::get('/success-stories', [AlumniController::class, 'successStories'])->name('success-stories');
    Route::get('/story/{slug}', [AlumniController::class, 'viewStory'])->name('story');
});

// ============================================================
// ALUMNI AUTHENTICATION
// ============================================================
Route::get('/login', [AlumniController::class, 'loginForm'])->name('login');
Route::post('/login', [AlumniController::class, 'login'])->name('login.submit');
Route::post('/logout', [AlumniController::class, 'logout'])->name('logout')->middleware('auth:alumni');
Route::get('/forgot-password', [AlumniController::class, 'forgotPasswordForm'])->name('password.forgot');
Route::post('/forgot-password', [AlumniController::class, 'sendPasswordReset'])->name('password.email');
Route::get('/reset-password/{token}', [AlumniController::class, 'resetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [AlumniController::class, 'resetPassword'])->name('password.update');

// ============================================================
// AUTHENTICATED ALUMNI ROUTES
// ============================================================
Route::middleware(['auth:alumni'])->group(function () {
    
    // Alumni Dashboard
    Route::get('/dashboard', [AlumniController::class, 'dashboard'])->name('dashboard');
    Route::get('/home', function() { return redirect()->route('alumni.dashboard'); })->name('home');
    
    // Profile Management
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [AlumniController::class, 'profile'])->name('index');
        Route::get('/edit', [AlumniController::class, 'editProfile'])->name('edit');
        Route::put('/update', [AlumniController::class, 'updateProfile'])->name('update');
        Route::post('/photo', [AlumniController::class, 'updatePhoto'])->name('photo');
        Route::get('/privacy', [AlumniController::class, 'privacySettings'])->name('privacy');
        Route::put('/privacy', [AlumniController::class, 'updatePrivacy'])->name('privacy.update');
        
        // Contact Information
        Route::get('/contact', [AlumniController::class, 'contactInfo'])->name('contact');
        Route::put('/contact', [AlumniController::class, 'updateContact'])->name('contact.update');
        Route::post('/contact/verify', [AlumniController::class, 'verifyContact'])->name('contact.verify');
        
        // Employment Information
        Route::get('/employment', [AlumniController::class, 'employmentInfo'])->name('employment');
        Route::post('/employment', [AlumniController::class, 'addEmployment'])->name('employment.add');
        Route::put('/employment/{employment}', [AlumniController::class, 'updateEmployment'])->name('employment.update');
        Route::delete('/employment/{employment}', [AlumniController::class, 'deleteEmployment'])->name('employment.delete');
        
        // Education History
        Route::get('/education', [AlumniController::class, 'educationHistory'])->name('education');
        Route::post('/education', [AlumniController::class, 'addEducation'])->name('education.add');
        Route::put('/education/{education}', [AlumniController::class, 'updateEducation'])->name('education.update');
    });
    
    // Alumni Directory
    Route::prefix('directory')->name('directory.')->group(function () {
        Route::get('/', [AlumniController::class, 'directory'])->name('index');
        Route::get('/search', [AlumniController::class, 'searchAlumni'])->name('search');
        Route::get('/class/{year}', [AlumniController::class, 'classmateDirectory'])->name('class');
        Route::get('/program/{program}', [AlumniController::class, 'programDirectory'])->name('program');
        Route::get('/location/{location}', [AlumniController::class, 'locationDirectory'])->name('location');
        Route::get('/industry/{industry}', [AlumniController::class, 'industryDirectory'])->name('industry');
        Route::get('/profile/{alumnus}', [AlumniController::class, 'viewAlumniProfile'])->name('profile');
    });
    
    // Networking
    Route::prefix('networking')->name('networking.')->group(function () {
        Route::get('/', [AlumniController::class, 'networkingHub'])->name('index');
        Route::get('/connections', [AlumniController::class, 'myConnections'])->name('connections');
        Route::post('/connect/{alumnus}', [AlumniController::class, 'sendConnectionRequest'])->name('connect');
        Route::post('/connection/{request}/accept', [AlumniController::class, 'acceptConnection'])->name('accept');
        Route::post('/connection/{request}/decline', [AlumniController::class, 'declineConnection'])->name('decline');
        Route::delete('/connection/{connection}', [AlumniController::class, 'removeConnection'])->name('remove');
        
        // Messages
        Route::get('/messages', [AlumniController::class, 'messages'])->name('messages');
        Route::get('/message/{message}', [AlumniController::class, 'viewMessage'])->name('message.view');
        Route::post('/message/send', [AlumniController::class, 'sendMessage'])->name('message.send');
        Route::post('/message/{message}/reply', [AlumniController::class, 'replyMessage'])->name('message.reply');
        
        // Groups
        Route::get('/groups', [AlumniController::class, 'groups'])->name('groups');
        Route::get('/group/{group}', [AlumniController::class, 'viewGroup'])->name('group.view');
        Route::post('/group/{group}/join', [AlumniController::class, 'joinGroup'])->name('group.join');
        Route::post('/group/{group}/leave', [AlumniController::class, 'leaveGroup'])->name('group.leave');
        Route::post('/group/create', [AlumniController::class, 'createGroup'])->name('group.create');
    });
    
    // Events
    Route::prefix('events')->name('events.')->group(function () {
        Route::get('/', [AlumniEventsController::class, 'index'])->name('index');
        Route::get('/upcoming', [AlumniEventsController::class, 'upcomingEvents'])->name('upcoming');
        Route::get('/past', [AlumniEventsController::class, 'pastEvents'])->name('past');
        Route::get('/{event}', [AlumniEventsController::class, 'eventDetails'])->name('details');
        Route::post('/{event}/register', [AlumniEventsController::class, 'registerForEvent'])->name('register');
        Route::delete('/{event}/cancel', [AlumniEventsController::class, 'cancelRegistration'])->name('cancel');
        Route::get('/{event}/attendees', [AlumniEventsController::class, 'eventAttendees'])->name('attendees');
        
        // Reunions
        Route::get('/reunions', [AlumniEventsController::class, 'reunions'])->name('reunions');
        Route::get('/reunion/{reunion}', [AlumniEventsController::class, 'reunionDetails'])->name('reunion.details');
        Route::post('/reunion/{reunion}/register', [AlumniEventsController::class, 'registerForReunion'])->name('reunion.register');
        Route::post('/reunion/organize', [AlumniEventsController::class, 'organizeReunion'])->name('reunion.organize');
        
        // Homecoming
        Route::get('/homecoming', [AlumniEventsController::class, 'homecoming'])->name('homecoming');
        Route::post('/homecoming/register', [AlumniEventsController::class, 'registerForHomecoming'])->name('homecoming.register');
    });
    
    // Career Services
    Route::prefix('career')->name('career.')->group(function () {
        Route::get('/', [AlumniServicesController::class, 'careerServices'])->name('index');
        
        // Job Board
        Route::get('/jobs', [AlumniServicesController::class, 'jobBoard'])->name('jobs');
        Route::get('/job/{job}', [AlumniServicesController::class, 'jobDetails'])->name('job.details');
        Route::post('/job', [AlumniServicesController::class, 'postJob'])->name('job.post');
        Route::put('/job/{job}', [AlumniServicesController::class, 'updateJob'])->name('job.update');
        Route::delete('/job/{job}', [AlumniServicesController::class, 'deleteJob'])->name('job.delete');
        Route::post('/job/{job}/apply', [AlumniServicesController::class, 'applyForJob'])->name('job.apply');
        
        // Mentorship
        Route::get('/mentorship', [AlumniServicesController::class, 'mentorshipProgram'])->name('mentorship');
        Route::post('/mentor/register', [AlumniServicesController::class, 'registerAsMentor'])->name('mentor.register');
        Route::get('/mentors', [AlumniServicesController::class, 'browseMentors'])->name('mentors');
        Route::post('/mentor/{mentor}/request', [AlumniServicesController::class, 'requestMentor'])->name('mentor.request');
        Route::get('/mentorship/dashboard', [AlumniServicesController::class, 'mentorshipDashboard'])->name('mentorship.dashboard');
        
        // Career Resources
        Route::get('/resources', [AlumniServicesController::class, 'careerResources'])->name('resources');
        Route::get('/resume-review', [AlumniServicesController::class, 'resumeReview'])->name('resume-review');
        Route::post('/resume/upload', [AlumniServicesController::class, 'uploadResume'])->name('resume.upload');
        Route::get('/career-counseling', [AlumniServicesController::class, 'careerCounseling'])->name('counseling');
        Route::post('/counseling/book', [AlumniServicesController::class, 'bookCounseling'])->name('counseling.book');
    });
    
    // Benefits & Services
    Route::prefix('benefits')->name('benefits.')->group(function () {
        Route::get('/', [AlumniServicesController::class, 'benefits'])->name('index');
        Route::get('/card', [AlumniServicesController::class, 'alumniCard'])->name('card');
        Route::post('/card/request', [AlumniServicesController::class, 'requestCard'])->name('card.request');
        Route::get('/card/digital', [AlumniServicesController::class, 'digitalCard'])->name('card.digital');
        
        // Library Access
        Route::get('/library', [AlumniServicesController::class, 'libraryAccess'])->name('library');
        Route::post('/library/register', [AlumniServicesController::class, 'registerLibraryAccess'])->name('library.register');
        
        // Email Forwarding
        Route::get('/email', [AlumniServicesController::class, 'emailForwarding'])->name('email');
        Route::post('/email/setup', [AlumniServicesController::class, 'setupEmailForwarding'])->name('email.setup');
        Route::put('/email/update', [AlumniServicesController::class, 'updateEmailForwarding'])->name('email.update');
        
        // Discounts & Perks
        Route::get('/discounts', [AlumniServicesController::class, 'discounts'])->name('discounts');
        Route::get('/discount/{discount}', [AlumniServicesController::class, 'discountDetails'])->name('discount.details');
        Route::post('/discount/{discount}/claim', [AlumniServicesController::class, 'claimDiscount'])->name('discount.claim');
        
        // Campus Facilities
        Route::get('/facilities', [AlumniServicesController::class, 'campusFacilities'])->name('facilities');
        Route::post('/facility/book', [AlumniServicesController::class, 'bookFacility'])->name('facility.book');
    });
    
    // Continuing Education
    Route::prefix('education')->name('education.')->group(function () {
        Route::get('/', [AlumniServicesController::class, 'continuingEducation'])->name('index');
        Route::get('/courses', [AlumniServicesController::class, 'availableCourses'])->name('courses');
        Route::get('/course/{course}', [AlumniServicesController::class, 'courseDetails'])->name('course.details');
        Route::post('/course/{course}/enroll', [AlumniServicesController::class, 'enrollInCourse'])->name('course.enroll');
        Route::get('/certificates', [AlumniServicesController::class, 'certificatePrograms'])->name('certificates');
        Route::get('/webinars', [AlumniServicesController::class, 'webinars'])->name('webinars');
        Route::post('/webinar/{webinar}/register', [AlumniServicesController::class, 'registerForWebinar'])->name('webinar.register');
        Route::get('/transcript', [AlumniServicesController::class, 'continuingEdTranscript'])->name('transcript');
    });
    
    // Volunteering
    Route::prefix('volunteer')->name('volunteer.')->group(function () {
        Route::get('/', [AlumniController::class, 'volunteerOpportunities'])->name('index');
        Route::get('/opportunities', [AlumniController::class, 'browseOpportunities'])->name('opportunities');
        Route::get('/opportunity/{opportunity}', [AlumniController::class, 'opportunityDetails'])->name('opportunity.details');
        Route::post('/opportunity/{opportunity}/apply', [AlumniController::class, 'applyToVolunteer'])->name('opportunity.apply');
        Route::get('/my-activities', [AlumniController::class, 'myVolunteerActivities'])->name('activities');
        Route::post('/hours/log', [AlumniController::class, 'logVolunteerHours'])->name('hours.log');
        Route::get('/recognition', [AlumniController::class, 'volunteerRecognition'])->name('recognition');
        
        // Alumni Ambassadors
        Route::get('/ambassador', [AlumniController::class, 'ambassadorProgram'])->name('ambassador');
        Route::post('/ambassador/apply', [AlumniController::class, 'applyAmbassador'])->name('ambassador.apply');
        
        // Student Mentoring
        Route::get('/student-mentor', [AlumniController::class, 'studentMentoring'])->name('student-mentor');
        Route::post('/student-mentor/register', [AlumniController::class, 'registerStudentMentor'])->name('student-mentor.register');
    });
    
    // Chapters & Clubs
    Route::prefix('chapters')->name('chapters.')->group(function () {
        Route::get('/', [AlumniController::class, 'chapters'])->name('index');
        Route::get('/map', [AlumniController::class, 'chapterMap'])->name('map');
        Route::get('/{chapter}', [AlumniController::class, 'chapterDetails'])->name('details');
        Route::post('/{chapter}/join', [AlumniController::class, 'joinChapter'])->name('join');
        Route::get('/{chapter}/events', [AlumniController::class, 'chapterEvents'])->name('events');
        Route::get('/{chapter}/members', [AlumniController::class, 'chapterMembers'])->name('members');
        Route::post('/start', [AlumniController::class, 'startChapter'])->name('start');
        
        // Regional Chapters
        Route::get('/regional/{region}', [AlumniController::class, 'regionalChapters'])->name('regional');
        
        // International Chapters
        Route::get('/international', [AlumniController::class, 'internationalChapters'])->name('international');
        
        // Affinity Groups
        Route::get('/affinity-groups', [AlumniController::class, 'affinityGroups'])->name('affinity');
    });
});

// ============================================================
// GIVING & DONATIONS (Mixed Auth)
// ============================================================
Route::prefix('giving')->name('giving.')->group(function () {
    // Public giving pages
    Route::get('/', [AlumniDonationsController::class, 'givingHome'])->name('index');
    Route::get('/ways-to-give', [AlumniDonationsController::class, 'waysToGive'])->name('ways');
    Route::get('/impact', [AlumniDonationsController::class, 'givingImpact'])->name('impact');
    Route::get('/recognition', [AlumniDonationsController::class, 'donorRecognition'])->name('recognition');
    Route::get('/annual-fund', [AlumniDonationsController::class, 'annualFund'])->name('annual-fund');
    Route::get('/campaigns', [AlumniDonationsController::class, 'campaigns'])->name('campaigns');
    Route::get('/campaign/{campaign}', [AlumniDonationsController::class, 'campaignDetails'])->name('campaign.details');
    
    // Donation Process
    Route::get('/donate', [AlumniDonationsController::class, 'donateForm'])->name('donate');
    Route::post('/donate/process', [AlumniDonationsController::class, 'processDonation'])->name('donate.process');
    Route::get('/donate/confirm/{donation}', [AlumniDonationsController::class, 'confirmDonation'])->name('donate.confirm');
    Route::get('/donate/receipt/{donation}', [AlumniDonationsController::class, 'donationReceipt'])->name('donate.receipt');
    
    // Authenticated donor features
    Route::middleware(['auth:alumni'])->group(function () {
        Route::get('/my-giving', [AlumniDonationsController::class, 'myGivingHistory'])->name('my-giving');
        Route::get('/pledges', [AlumniDonationsController::class, 'myPledges'])->name('pledges');
        Route::post('/pledge', [AlumniDonationsController::class, 'makePledge'])->name('pledge.make');
        Route::put('/pledge/{pledge}', [AlumniDonationsController::class, 'updatePledge'])->name('pledge.update');
        Route::post('/pledge/{pledge}/fulfill', [AlumniDonationsController::class, 'fulfillPledge'])->name('pledge.fulfill');
        Route::get('/recurring', [AlumniDonationsController::class, 'recurringGifts'])->name('recurring');
        Route::post('/recurring/setup', [AlumniDonationsController::class, 'setupRecurring'])->name('recurring.setup');
        Route::put('/recurring/{recurring}', [AlumniDonationsController::class, 'updateRecurring'])->name('recurring.update');
        Route::delete('/recurring/{recurring}', [AlumniDonationsController::class, 'cancelRecurring'])->name('recurring.cancel');
        Route::get('/tax-receipts', [AlumniDonationsController::class, 'taxReceipts'])->name('tax-receipts');
        Route::get('/giving-societies', [AlumniDonationsController::class, 'givingSocieties'])->name('societies');
    });
    
    // Major Gifts & Planned Giving
    Route::get('/major-gifts', [AlumniDonationsController::class, 'majorGifts'])->name('major-gifts');
    Route::get('/planned-giving', [AlumniDonationsController::class, 'plannedGiving'])->name('planned-giving');
    Route::post('/planned-giving/inquiry', [AlumniDonationsController::class, 'plannedGivingInquiry'])->name('planned-giving.inquiry');
    Route::get('/endowments', [AlumniDonationsController::class, 'endowments'])->name('endowments');
    Route::get('/naming-opportunities', [AlumniDonationsController::class, 'namingOpportunities'])->name('naming');
});

// ============================================================
// ADMINISTRATIVE ROUTES (Staff-facing)
// ============================================================
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:alumni-coordinator,development-officer,admin'])->group(function () {
    
    // Dashboard
    Route::get('/', [AlumniController::class, 'adminDashboard'])->name('dashboard');
    Route::get('/statistics', [AlumniController::class, 'statistics'])->name('statistics');
    
    // Alumni Management
    Route::prefix('alumni')->name('alumni.')->group(function () {
        Route::get('/', [AlumniController::class, 'manageAlumni'])->name('index');
        Route::get('/search', [AlumniController::class, 'searchAlumniAdmin'])->name('search');
        Route::get('/{alumnus}', [AlumniController::class, 'viewAlumnus'])->name('view');
        Route::get('/{alumnus}/edit', [AlumniController::class, 'editAlumnus'])->name('edit');
        Route::put('/{alumnus}', [AlumniController::class, 'updateAlumnus'])->name('update');
        Route::post('/import', [AlumniController::class, 'importAlumni'])->name('import');
        Route::get('/export', [AlumniController::class, 'exportAlumni'])->name('export');
        Route::post('/batch-update', [AlumniController::class, 'batchUpdate'])->name('batch-update');
        Route::get('/verification-queue', [AlumniController::class, 'verificationQueue'])->name('verification');
        Route::post('/{alumnus}/verify', [AlumniController::class, 'verifyAlumnus'])->name('verify');
    });
    
    // Communications
    Route::prefix('communications')->name('communications.')->group(function () {
        Route::get('/', [AlumniController::class, 'communications'])->name('index');
        Route::get('/campaigns', [AlumniController::class, 'emailCampaigns'])->name('campaigns');
        Route::post('/campaign', [AlumniController::class, 'createCampaign'])->name('campaign.create');
        Route::post('/campaign/{campaign}/send', [AlumniController::class, 'sendCampaign'])->name('campaign.send');
        Route::get('/templates', [AlumniController::class, 'emailTemplates'])->name('templates');
        Route::post('/template', [AlumniController::class, 'saveTemplate'])->name('template.save');
        Route::get('/newsletter', [AlumniController::class, 'newsletterAdmin'])->name('newsletter');
        Route::post('/newsletter/publish', [AlumniController::class, 'publishNewsletter'])->name('newsletter.publish');
    });
    
    // Event Management
    Route::prefix('events')->name('events.')->group(function () {
        Route::get('/', [AlumniEventsController::class, 'adminIndex'])->name('index');
        Route::get('/create', [AlumniEventsController::class, 'createEvent'])->name('create');
        Route::post('/', [AlumniEventsController::class, 'storeEvent'])->name('store');
        Route::get('/{event}/edit', [AlumniEventsController::class, 'editEvent'])->name('edit');
        Route::put('/{event}', [AlumniEventsController::class, 'updateEvent'])->name('update');
        Route::delete('/{event}', [AlumniEventsController::class, 'cancelEvent'])->name('cancel');
        Route::get('/{event}/registrations', [AlumniEventsController::class, 'eventRegistrations'])->name('registrations');
        Route::post('/{event}/check-in/{registration}', [AlumniEventsController::class, 'checkIn'])->name('check-in');
        Route::get('/{event}/report', [AlumniEventsController::class, 'eventReport'])->name('report');
    });
    
    // Fundraising
    Route::prefix('fundraising')->name('fundraising.')->group(function () {
        Route::get('/', [AlumniDonationsController::class, 'fundraisingDashboard'])->name('index');
        Route::get('/donors', [AlumniDonationsController::class, 'donorManagement'])->name('donors');
        Route::get('/donor/{donor}', [AlumniDonationsController::class, 'donorProfile'])->name('donor.profile');
        Route::get('/campaigns', [AlumniDonationsController::class, 'campaignManagement'])->name('campaigns');
        Route::post('/campaign', [AlumniDonationsController::class, 'createCampaign'])->name('campaign.create');
        Route::get('/prospects', [AlumniDonationsController::class, 'prospectResearch'])->name('prospects');
        Route::post('/prospect/{alumnus}', [AlumniDonationsController::class, 'addProspect'])->name('prospect.add');
        Route::get('/solicitations', [AlumniDonationsController::class, 'solicitations'])->name('solicitations');
        Route::post('/solicitation', [AlumniDonationsController::class, 'createSolicitation'])->name('solicitation.create');
        Route::get('/pledges', [AlumniDonationsController::class, 'pledgeManagement'])->name('pledges');
        Route::get('/stewardship', [AlumniDonationsController::class, 'donorStewardship'])->name('stewardship');
    });
    
    // Reports & Analytics
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [AlumniController::class, 'reportsHub'])->name('index');
        Route::get('/engagement', [AlumniController::class, 'engagementReport'])->name('engagement');
        Route::get('/demographics', [AlumniController::class, 'demographicsReport'])->name('demographics');
        Route::get('/giving', [AlumniDonationsController::class, 'givingReport'])->name('giving');
        Route::get('/events', [AlumniEventsController::class, 'eventsReport'])->name('events');
        Route::get('/career-outcomes', [AlumniController::class, 'careerOutcomesReport'])->name('career-outcomes');
        Route::get('/annual', [AlumniController::class, 'annualReport'])->name('annual');
        Route::post('/custom', [AlumniController::class, 'generateCustomReport'])->name('custom');
        Route::post('/export', [AlumniController::class, 'exportReport'])->name('export');
    });
});