<?php
/**
 * IntelliCampus Academic Advising Routes
 * 
 * Routes for academic advisors and advising operations.
 * These routes are automatically prefixed with 'advisor' and named with 'advisor.'
 * Applied middleware: 'web', 'auth', 'role:advisor,academic-advisor,faculty'
 */

use App\Http\Controllers\AdvisorOverrideController;
use App\Http\Controllers\DegreeAuditController;
use App\Http\Controllers\AcademicPlanController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\RequirementManagementController;
use App\Http\Controllers\WhatIfAnalysisController;
use Illuminate\Support\Facades\Route;

// ============================================================
// ADVISOR DASHBOARD
// ============================================================
Route::get('/', function() {
    return redirect()->route('advisor.dashboard');
});

Route::get('/dashboard', [FacultyController::class, 'advisorDashboard'])->name('dashboard');
Route::get('/overview', [FacultyController::class, 'advisingOverview'])->name('overview');
Route::get('/alerts', [FacultyController::class, 'advisingAlerts'])->name('alerts');
Route::get('/tasks', [FacultyController::class, 'advisingTasks'])->name('tasks');
Route::get('/calendar', [FacultyController::class, 'advisingCalendar'])->name('calendar');

// ============================================================
// ADVISEE MANAGEMENT
// ============================================================
Route::prefix('advisees')->name('advisees.')->group(function () {
    Route::get('/', [FacultyController::class, 'adviseeList'])->name('index');
    Route::get('/search', [FacultyController::class, 'searchAdvisees'])->name('search');
    Route::get('/assigned', [FacultyController::class, 'assignedAdvisees'])->name('assigned');
    Route::get('/prospective', [FacultyController::class, 'prospectiveAdvisees'])->name('prospective');
    Route::post('/claim/{student}', [FacultyController::class, 'claimAdvisee'])->name('claim');
    Route::post('/release/{student}', [FacultyController::class, 'releaseAdvisee'])->name('release');
    Route::post('/transfer/{student}', [FacultyController::class, 'transferAdvisee'])->name('transfer');
    
    // Advisee Groups
    Route::get('/groups', [FacultyController::class, 'adviseeGroups'])->name('groups');
    Route::post('/group', [FacultyController::class, 'createGroup'])->name('group.create');
    Route::put('/group/{group}', [FacultyController::class, 'updateGroup'])->name('group.update');
    Route::delete('/group/{group}', [FacultyController::class, 'deleteGroup'])->name('group.delete');
    Route::post('/group/{group}/add/{student}', [FacultyController::class, 'addToGroup'])->name('group.add');
    Route::delete('/group/{group}/remove/{student}', [FacultyController::class, 'removeFromGroup'])->name('group.remove');
    
    // Batch Operations
    Route::post('/batch-message', [FacultyController::class, 'batchMessage'])->name('batch-message');
    Route::post('/batch-hold', [FacultyController::class, 'batchHold'])->name('batch-hold');
    Route::post('/batch-release', [FacultyController::class, 'batchRelease'])->name('batch-release');
});

// ============================================================
// INDIVIDUAL STUDENT ADVISING
// ============================================================
Route::prefix('student/{student}')->name('student.')->group(function () {
    Route::get('/', [FacultyController::class, 'adviseeProfile'])->name('profile');
    Route::get('/summary', [FacultyController::class, 'adviseeSummary'])->name('summary');
    
    // Academic Record
    Route::get('/academic', [FacultyController::class, 'adviseeAcademicRecord'])->name('academic');
    Route::get('/transcript', [FacultyController::class, 'adviseeTranscript'])->name('transcript');
    Route::get('/grades', [FacultyController::class, 'adviseeGrades'])->name('grades');
    Route::get('/gpa', [FacultyController::class, 'adviseeGPA'])->name('gpa');
    Route::get('/standing', [FacultyController::class, 'adviseeStanding'])->name('standing');
    Route::get('/progress', [FacultyController::class, 'adviseeProgress'])->name('progress');
    
    // Course History
    Route::get('/courses', [FacultyController::class, 'adviseeCourses'])->name('courses');
    Route::get('/courses/completed', [FacultyController::class, 'completedCourses'])->name('courses.completed');
    Route::get('/courses/current', [FacultyController::class, 'currentCourses'])->name('courses.current');
    Route::get('/courses/planned', [FacultyController::class, 'plannedCourses'])->name('courses.planned');
    Route::get('/courses/dropped', [FacultyController::class, 'droppedCourses'])->name('courses.dropped');
    Route::get('/courses/withdrawn', [FacultyController::class, 'withdrawnCourses'])->name('courses.withdrawn');
    
    // Advising Notes
    Route::get('/notes', [FacultyController::class, 'advisingNotes'])->name('notes');
    Route::post('/note', [FacultyController::class, 'addAdvisingNote'])->name('note.add');
    Route::put('/note/{note}', [FacultyController::class, 'updateNote'])->name('note.update');
    Route::delete('/note/{note}', [FacultyController::class, 'deleteNote'])->name('note.delete');
    Route::post('/note/{note}/flag', [FacultyController::class, 'flagNote'])->name('note.flag');
    Route::get('/notes/export', [FacultyController::class, 'exportNotes'])->name('notes.export');
    
    // Advising History
    Route::get('/history', [FacultyController::class, 'advisingHistory'])->name('history');
    Route::get('/appointments', [FacultyController::class, 'appointmentHistory'])->name('appointments');
    Route::get('/communications', [FacultyController::class, 'communicationHistory'])->name('communications');
    Route::get('/recommendations', [FacultyController::class, 'advisingRecommendations'])->name('recommendations');
    
    // Alerts & Interventions
    Route::get('/alerts', [FacultyController::class, 'studentAlerts'])->name('alerts');
    Route::post('/alert', [FacultyController::class, 'createAlert'])->name('alert.create');
    Route::post('/intervention', [FacultyController::class, 'recordIntervention'])->name('intervention');
    Route::get('/early-alert', [FacultyController::class, 'earlyAlert'])->name('early-alert');
    Route::post('/early-alert', [FacultyController::class, 'submitEarlyAlert'])->name('early-alert.submit');
    
    // Documents
    Route::get('/documents', [FacultyController::class, 'adviseeDocuments'])->name('documents');
    Route::post('/document/upload', [FacultyController::class, 'uploadDocument'])->name('document.upload');
    Route::get('/document/{document}/download', [FacultyController::class, 'downloadDocument'])->name('document.download');
    Route::delete('/document/{document}', [FacultyController::class, 'deleteDocument'])->name('document.delete');
});

// ============================================================
// DEGREE PLANNING & AUDIT
// ============================================================
Route::prefix('degree-planning')->name('degree-planning.')->group(function () {
    // Degree Audit
    Route::get('/audit/{student}', [DegreeAuditController::class, 'advisorAudit'])->name('audit');
    Route::post('/audit/{student}/run', [DegreeAuditController::class, 'runAudit'])->name('audit.run');
    Route::get('/audit/{student}/report', [DegreeAuditController::class, 'auditReport'])->name('audit.report');
    Route::get('/audit/{student}/print', [DegreeAuditController::class, 'printAudit'])->name('audit.print');
    Route::post('/audit/{student}/email', [DegreeAuditController::class, 'emailAudit'])->name('audit.email');
    
    // Requirement Management
    Route::get('/requirements/{student}', [RequirementManagementController::class, 'studentRequirements'])->name('requirements');
    Route::post('/requirement/{student}/override', [RequirementManagementController::class, 'overrideRequirement'])->name('requirement.override');
    Route::post('/requirement/{student}/substitute', [RequirementManagementController::class, 'substituteCourse'])->name('requirement.substitute');
    Route::post('/requirement/{student}/waive', [RequirementManagementController::class, 'waiveRequirement'])->name('requirement.waive');
    Route::get('/requirement/{student}/exceptions', [RequirementManagementController::class, 'viewExceptions'])->name('requirement.exceptions');
    
    // Academic Planning
    Route::get('/plan/{student}', [AcademicPlanController::class, 'studentPlan'])->name('plan');
    Route::get('/plan/{student}/create', [AcademicPlanController::class, 'createPlan'])->name('plan.create');
    Route::post('/plan/{student}', [AcademicPlanController::class, 'storePlan'])->name('plan.store');
    Route::get('/plan/{plan}/edit', [AcademicPlanController::class, 'editPlan'])->name('plan.edit');
    Route::put('/plan/{plan}', [AcademicPlanController::class, 'updatePlan'])->name('plan.update');
    Route::post('/plan/{plan}/approve', [AcademicPlanController::class, 'approvePlan'])->name('plan.approve');
    Route::post('/plan/{plan}/reject', [AcademicPlanController::class, 'rejectPlan'])->name('plan.reject');
    Route::post('/plan/{plan}/lock', [AcademicPlanController::class, 'lockPlan'])->name('plan.lock');
    Route::post('/plan/{plan}/unlock', [AcademicPlanController::class, 'unlockPlan'])->name('plan.unlock');
    
    // Course Sequencing
    Route::get('/sequence/{student}', [AcademicPlanController::class, 'courseSequence'])->name('sequence');
    Route::post('/sequence/{student}/generate', [AcademicPlanController::class, 'generateSequence'])->name('sequence.generate');
    Route::post('/sequence/{student}/optimize', [AcademicPlanController::class, 'optimizeSequence'])->name('sequence.optimize');
    Route::post('/sequence/{student}/validate', [AcademicPlanController::class, 'validateSequence'])->name('sequence.validate');
    
    // What-If Analysis
    Route::get('/what-if/{student}', [WhatIfAnalysisController::class, 'advisorWhatIf'])->name('what-if');
    Route::post('/what-if/{student}/analyze', [WhatIfAnalysisController::class, 'runAnalysis'])->name('what-if.analyze');
    Route::post('/what-if/{student}/save', [WhatIfAnalysisController::class, 'saveScenario'])->name('what-if.save');
    Route::get('/what-if/{student}/scenarios', [WhatIfAnalysisController::class, 'studentScenarios'])->name('what-if.scenarios');
    Route::post('/what-if/{scenario}/recommend', [WhatIfAnalysisController::class, 'recommendScenario'])->name('what-if.recommend');
    
    // Graduation Planning
    Route::get('/graduation/{student}', [DegreeAuditController::class, 'graduationPlanning'])->name('graduation');
    Route::post('/graduation/{student}/check', [DegreeAuditController::class, 'graduationCheck'])->name('graduation.check');
    Route::post('/graduation/{student}/apply', [DegreeAuditController::class, 'applyForGraduation'])->name('graduation.apply');
    Route::get('/graduation/{student}/timeline', [DegreeAuditController::class, 'graduationTimeline'])->name('graduation.timeline');
});

// ============================================================
// REGISTRATION ADVISING
// ============================================================
Route::prefix('registration')->name('registration.')->group(function () {
    Route::get('/', [RegistrationController::class, 'advisorRegistration'])->name('index');
    
    // Registration Planning
    Route::get('/planning/{student}', [RegistrationController::class, 'registrationPlanning'])->name('planning');
    Route::get('/recommendations/{student}', [RegistrationController::class, 'courseRecommendations'])->name('recommendations');
    Route::post('/recommend/{student}', [RegistrationController::class, 'recommendCourses'])->name('recommend');
    Route::post('/approve-plan/{student}', [RegistrationController::class, 'approvePlan'])->name('approve-plan');
    
    // Registration Holds
    Route::get('/holds', [RegistrationController::class, 'adviseeHolds'])->name('holds');
    Route::get('/holds/{student}', [RegistrationController::class, 'studentHolds'])->name('holds.student');
    Route::post('/hold/{student}/release', [RegistrationController::class, 'releaseHold'])->name('hold.release');
    Route::post('/hold/{student}/place', [RegistrationController::class, 'placeHold'])->name('hold.place');
    Route::get('/advising-clearance', [RegistrationController::class, 'advisingClearance'])->name('clearance');
    Route::post('/clearance/{student}/grant', [RegistrationController::class, 'grantClearance'])->name('clearance.grant');
    
    // Registration Monitoring
    Route::get('/monitor', [RegistrationController::class, 'monitorRegistrations'])->name('monitor');
    Route::get('/pending-approval', [RegistrationController::class, 'pendingApprovals'])->name('pending');
    Route::post('/approve/{registration}', [RegistrationController::class, 'approveRegistration'])->name('approve');
    Route::post('/deny/{registration}', [RegistrationController::class, 'denyRegistration'])->name('deny');
    
    // Course Load Management
    Route::get('/course-load/{student}', [RegistrationController::class, 'courseLoad'])->name('course-load');
    Route::post('/course-load/{student}/approve', [RegistrationController::class, 'approveCourseLoad'])->name('course-load.approve');
    Route::post('/course-load/{student}/adjust', [RegistrationController::class, 'adjustCourseLoad'])->name('course-load.adjust');
    Route::post('/overload/{student}/approve', [RegistrationController::class, 'approveOverload'])->name('overload.approve');
    Route::post('/underload/{student}/approve', [RegistrationController::class, 'approveUnderload'])->name('underload.approve');
});

// ============================================================
// REGISTRATION OVERRIDES
// ============================================================
Route::prefix('overrides')->name('overrides.')->group(function () {
    Route::get('/', [AdvisorOverrideController::class, 'index'])->name('index');
    Route::get('/pending', [AdvisorOverrideController::class, 'pendingOverrides'])->name('pending');
    Route::get('/request/{override}', [AdvisorOverrideController::class, 'viewRequest'])->name('request.view');
    Route::post('/request/{override}/approve', [AdvisorOverrideController::class, 'approve'])->name('request.approve');
    Route::post('/request/{override}/deny', [AdvisorOverrideController::class, 'deny'])->name('request.deny');
    Route::post('/request/{override}/comment', [AdvisorOverrideController::class, 'addComment'])->name('request.comment');
    
    // Issue Overrides
    Route::get('/issue', [AdvisorOverrideController::class, 'issueOverrideForm'])->name('issue');
    Route::post('/issue', [AdvisorOverrideController::class, 'issueOverride'])->name('issue.submit');
    Route::get('/templates', [AdvisorOverrideController::class, 'overrideTemplates'])->name('templates');
    Route::post('/batch', [AdvisorOverrideController::class, 'batchOverrides'])->name('batch');
    
    // Override History
    Route::get('/history', [AdvisorOverrideController::class, 'overrideHistory'])->name('history');
    Route::get('/student/{student}', [AdvisorOverrideController::class, 'studentOverrides'])->name('student');
    Route::get('/report', [AdvisorOverrideController::class, 'overrideReport'])->name('report');
    Route::get('/export', [AdvisorOverrideController::class, 'exportOverrides'])->name('export');
});

// ============================================================
// APPOINTMENTS & SCHEDULING
// ============================================================
Route::prefix('appointments')->name('appointments.')->group(function () {
    Route::get('/', [FacultyController::class, 'appointmentCalendar'])->name('index');
    Route::get('/calendar', [FacultyController::class, 'appointmentCalendar'])->name('calendar');
    Route::get('/list', [FacultyController::class, 'appointmentList'])->name('list');
    
    // Appointment Management
    Route::get('/upcoming', [FacultyController::class, 'upcomingAppointments'])->name('upcoming');
    Route::get('/past', [FacultyController::class, 'pastAppointments'])->name('past');
    Route::get('/{appointment}', [FacultyController::class, 'viewAppointment'])->name('view');
    Route::post('/{appointment}/confirm', [FacultyController::class, 'confirmAppointment'])->name('confirm');
    Route::post('/{appointment}/cancel', [FacultyController::class, 'cancelAppointment'])->name('cancel');
    Route::post('/{appointment}/reschedule', [FacultyController::class, 'rescheduleAppointment'])->name('reschedule');
    Route::post('/{appointment}/no-show', [FacultyController::class, 'markNoShow'])->name('no-show');
    Route::post('/{appointment}/complete', [FacultyController::class, 'completeAppointment'])->name('complete');
    
    // Appointment Notes
    Route::get('/{appointment}/notes', [FacultyController::class, 'appointmentNotes'])->name('notes');
    Route::post('/{appointment}/note', [FacultyController::class, 'addAppointmentNote'])->name('note.add');
    Route::put('/note/{note}', [FacultyController::class, 'updateAppointmentNote'])->name('note.update');
    
    // Availability Management
    Route::get('/availability', [FacultyController::class, 'advisingAvailability'])->name('availability');
    Route::post('/availability', [FacultyController::class, 'setAvailability'])->name('availability.set');
    Route::put('/availability/{availability}', [FacultyController::class, 'updateAvailability'])->name('availability.update');
    Route::delete('/availability/{availability}', [FacultyController::class, 'deleteAvailability'])->name('availability.delete');
    Route::get('/slots', [FacultyController::class, 'availableSlots'])->name('slots');
    Route::post('/slots/generate', [FacultyController::class, 'generateSlots'])->name('slots.generate');
    Route::post('/slots/block', [FacultyController::class, 'blockSlots'])->name('slots.block');
    
    // Walk-in Management
    Route::get('/walk-ins', [FacultyController::class, 'walkIns'])->name('walk-ins');
    Route::post('/walk-in', [FacultyController::class, 'recordWalkIn'])->name('walk-in.record');
    Route::get('/queue', [FacultyController::class, 'advisingQueue'])->name('queue');
    Route::post('/queue/next', [FacultyController::class, 'callNext'])->name('queue.next');
});

// ============================================================
// COMMUNICATIONS
// ============================================================
Route::prefix('communications')->name('communications.')->group(function () {
    Route::get('/', [FacultyController::class, 'communicationCenter'])->name('index');
    
    // Messaging
    Route::get('/messages', [FacultyController::class, 'advisingMessages'])->name('messages');
    Route::get('/compose', [FacultyController::class, 'composeMessage'])->name('compose');
    Route::post('/send', [FacultyController::class, 'sendMessage'])->name('send');
    Route::post('/send-bulk', [FacultyController::class, 'sendBulkMessage'])->name('send-bulk');
    Route::get('/templates', [FacultyController::class, 'messageTemplates'])->name('templates');
    Route::post('/template', [FacultyController::class, 'saveTemplate'])->name('template.save');
    
    // Email Communications
    Route::get('/emails', [FacultyController::class, 'emailHistory'])->name('emails');
    Route::post('/email/student/{student}', [FacultyController::class, 'emailStudent'])->name('email.student');
    Route::post('/email/group/{group}', [FacultyController::class, 'emailGroup'])->name('email.group');
    Route::post('/email/all-advisees', [FacultyController::class, 'emailAllAdvisees'])->name('email.all');
    
    // Announcements
    Route::get('/announcements', [FacultyController::class, 'advisingAnnouncements'])->name('announcements');
    Route::post('/announcement', [FacultyController::class, 'createAnnouncement'])->name('announcement.create');
    Route::put('/announcement/{announcement}', [FacultyController::class, 'updateAnnouncement'])->name('announcement.update');
    Route::delete('/announcement/{announcement}', [FacultyController::class, 'deleteAnnouncement'])->name('announcement.delete');
    
    // Notifications
    Route::get('/notifications', [FacultyController::class, 'notificationSettings'])->name('notifications');
    Route::put('/notifications', [FacultyController::class, 'updateNotificationSettings'])->name('notifications.update');
    Route::get('/alerts/settings', [FacultyController::class, 'alertSettings'])->name('alerts.settings');
    Route::put('/alerts/settings', [FacultyController::class, 'updateAlertSettings'])->name('alerts.settings.update');
});

// ============================================================
// AT-RISK STUDENTS
// ============================================================
Route::prefix('at-risk')->name('at-risk.')->group(function () {
    Route::get('/', [FacultyController::class, 'atRiskDashboard'])->name('index');
    Route::get('/students', [FacultyController::class, 'atRiskStudents'])->name('students');
    Route::get('/criteria', [FacultyController::class, 'riskCriteria'])->name('criteria');
    Route::post('/criteria', [FacultyController::class, 'updateRiskCriteria'])->name('criteria.update');
    
    // Early Alert System
    Route::get('/early-alerts', [FacultyController::class, 'earlyAlerts'])->name('early-alerts');
    Route::post('/early-alert', [FacultyController::class, 'submitEarlyAlert'])->name('early-alert.submit');
    Route::get('/early-alert/{alert}', [FacultyController::class, 'viewEarlyAlert'])->name('early-alert.view');
    Route::post('/early-alert/{alert}/respond', [FacultyController::class, 'respondToAlert'])->name('early-alert.respond');
    
    // Interventions
    Route::get('/interventions', [FacultyController::class, 'interventions'])->name('interventions');
    Route::post('/intervention', [FacultyController::class, 'createIntervention'])->name('intervention.create');
    Route::get('/intervention/{intervention}', [FacultyController::class, 'viewIntervention'])->name('intervention.view');
    Route::post('/intervention/{intervention}/outcome', [FacultyController::class, 'recordOutcome'])->name('intervention.outcome');
    
    // Success Plans
    Route::get('/success-plans', [FacultyController::class, 'successPlans'])->name('success-plans');
    Route::get('/success-plan/{student}/create', [FacultyController::class, 'createSuccessPlan'])->name('success-plan.create');
    Route::post('/success-plan/{student}', [FacultyController::class, 'storeSuccessPlan'])->name('success-plan.store');
    Route::get('/success-plan/{plan}', [FacultyController::class, 'viewSuccessPlan'])->name('success-plan.view');
    Route::put('/success-plan/{plan}', [FacultyController::class, 'updateSuccessPlan'])->name('success-plan.update');
    Route::post('/success-plan/{plan}/review', [FacultyController::class, 'reviewSuccessPlan'])->name('success-plan.review');
    
    // Retention Tracking
    Route::get('/retention', [FacultyController::class, 'retentionTracking'])->name('retention');
    Route::get('/retention/report', [FacultyController::class, 'retentionReport'])->name('retention.report');
    Route::post('/retention/flag/{student}', [FacultyController::class, 'flagForRetention'])->name('retention.flag');
});

// ============================================================
// REPORTS & ANALYTICS
// ============================================================
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [FacultyController::class, 'advisingReports'])->name('index');
    
    // Advisee Reports
    Route::get('/advisees', [FacultyController::class, 'adviseeReport'])->name('advisees');
    Route::get('/advisees/summary', [FacultyController::class, 'adviseeSummaryReport'])->name('advisees.summary');
    Route::get('/advisees/progress', [FacultyController::class, 'progressReport'])->name('advisees.progress');
    Route::get('/advisees/standing', [FacultyController::class, 'standingReport'])->name('advisees.standing');
    Route::get('/advisees/graduation', [FacultyController::class, 'graduationCandidates'])->name('advisees.graduation');
    
    // Registration Reports
    Route::get('/registration', [FacultyController::class, 'registrationReport'])->name('registration');
    Route::get('/registration/holds', [FacultyController::class, 'holdsReport'])->name('registration.holds');
    Route::get('/registration/overrides', [FacultyController::class, 'overridesReport'])->name('registration.overrides');
    
    // Academic Performance
    Route::get('/performance', [FacultyController::class, 'performanceReport'])->name('performance');
    Route::get('/performance/gpa', [FacultyController::class, 'gpaAnalysis'])->name('performance.gpa');
    Route::get('/performance/trends', [FacultyController::class, 'performanceTrends'])->name('performance.trends');
    Route::get('/performance/comparison', [FacultyController::class, 'performanceComparison'])->name('performance.comparison');
    
    // Advising Activity
    Route::get('/activity', [FacultyController::class, 'advisingActivityReport'])->name('activity');
    Route::get('/appointments/report', [FacultyController::class, 'appointmentReport'])->name('appointments');
    Route::get('/interventions/report', [FacultyController::class, 'interventionReport'])->name('interventions');
    Route::get('/communications/report', [FacultyController::class, 'communicationReport'])->name('communications');
    
    // Custom Reports
    Route::get('/builder', [FacultyController::class, 'reportBuilder'])->name('builder');
    Route::post('/generate', [FacultyController::class, 'generateReport'])->name('generate');
    Route::get('/scheduled', [FacultyController::class, 'scheduledReports'])->name('scheduled');
    Route::post('/schedule', [FacultyController::class, 'scheduleReport'])->name('schedule');
    Route::post('/export', [FacultyController::class, 'exportReport'])->name('export');
});

// ============================================================
// RESOURCES & TOOLS
// ============================================================
Route::prefix('resources')->name('resources.')->group(function () {
    Route::get('/', [FacultyController::class, 'advisingResources'])->name('index');
    Route::get('/handbook', [FacultyController::class, 'advisingHandbook'])->name('handbook');
    Route::get('/policies', [FacultyController::class, 'advisingPolicies'])->name('policies');
    Route::get('/forms', [FacultyController::class, 'advisingForms'])->name('forms');
    Route::get('/training', [FacultyController::class, 'advisingTraining'])->name('training');
    Route::get('/best-practices', [FacultyController::class, 'bestPractices'])->name('best-practices');
    Route::get('/ferpa', [FacultyController::class, 'ferpaGuidelines'])->name('ferpa');
    Route::get('/referrals', [FacultyController::class, 'referralResources'])->name('referrals');
    Route::get('/contacts', [FacultyController::class, 'importantContacts'])->name('contacts');
    
    // Tools
    Route::get('/tools', [FacultyController::class, 'advisingTools'])->name('tools');
    Route::get('/calculator/gpa', [FacultyController::class, 'gpaCalculator'])->name('calculator.gpa');
    Route::get('/calculator/graduation', [FacultyController::class, 'graduationCalculator'])->name('calculator.graduation');
    Route::get('/planner', [FacultyController::class, 'academicPlanner'])->name('planner');
    Route::get('/course-finder', [FacultyController::class, 'courseFinder'])->name('course-finder');
});

// ============================================================
// PROFESSIONAL DEVELOPMENT
// ============================================================
Route::prefix('development')->name('development.')->group(function () {
    Route::get('/', [FacultyController::class, 'professionalDevelopment'])->name('index');
    Route::get('/training', [FacultyController::class, 'trainingModules'])->name('training');
    Route::get('/training/{module}', [FacultyController::class, 'viewTraining'])->name('training.view');
    Route::post('/training/{module}/complete', [FacultyController::class, 'completeTraining'])->name('training.complete');
    Route::get('/certifications', [FacultyController::class, 'advisingCertifications'])->name('certifications');
    Route::get('/conferences', [FacultyController::class, 'conferences'])->name('conferences');
    Route::get('/webinars', [FacultyController::class, 'webinars'])->name('webinars');
    Route::post('/webinar/{webinar}/register', [FacultyController::class, 'registerForWebinar'])->name('webinar.register');
});