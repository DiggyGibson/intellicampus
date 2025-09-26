<?php
/**
 * IntelliCampus Department Management Routes
 * 
 * Routes for department heads and department-level administration.
 * These routes are automatically prefixed with 'department' and named with 'department.'
 * Applied middleware: 'web', 'auth', 'role:department-head,department-chair,dean,admin'
 */

use App\Http\Controllers\DepartmentManagementController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SchedulingController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\AdvisorOverrideController;
use Illuminate\Support\Facades\Route;

// ============================================================
// DEPARTMENT DASHBOARD
// ============================================================
Route::get('/', function() {
    return redirect()->route('department.dashboard');
});

Route::get('/dashboard', [DepartmentManagementController::class, 'dashboard'])->name('dashboard');
Route::get('/overview', [DepartmentManagementController::class, 'overview'])->name('overview');
Route::get('/statistics', [DepartmentManagementController::class, 'statistics'])->name('statistics');
Route::get('/alerts', [DepartmentManagementController::class, 'departmentAlerts'])->name('alerts');
Route::get('/tasks', [DepartmentManagementController::class, 'pendingTasks'])->name('tasks');

// ============================================================
// DEPARTMENT PROFILE & SETTINGS
// ============================================================
Route::prefix('profile')->name('profile.')->group(function () {
    Route::get('/', [DepartmentManagementController::class, 'departmentProfile'])->name('index');
    Route::get('/edit', [DepartmentManagementController::class, 'editProfile'])->name('edit');
    Route::put('/update', [DepartmentManagementController::class, 'updateProfile'])->name('update');
    Route::post('/logo', [DepartmentManagementController::class, 'uploadLogo'])->name('logo');
    Route::get('/structure', [DepartmentManagementController::class, 'organizationalStructure'])->name('structure');
    Route::get('/history', [DepartmentManagementController::class, 'departmentHistory'])->name('history');
    
    // Contact Information
    Route::get('/contact', [DepartmentManagementController::class, 'contactInfo'])->name('contact');
    Route::put('/contact', [DepartmentManagementController::class, 'updateContactInfo'])->name('contact.update');
    Route::get('/office-hours', [DepartmentManagementController::class, 'officeHours'])->name('office-hours');
    Route::put('/office-hours', [DepartmentManagementController::class, 'updateOfficeHours'])->name('office-hours.update');
    
    // Mission & Goals
    Route::get('/mission', [DepartmentManagementController::class, 'missionStatement'])->name('mission');
    Route::put('/mission', [DepartmentManagementController::class, 'updateMission'])->name('mission.update');
    Route::get('/goals', [DepartmentManagementController::class, 'departmentGoals'])->name('goals');
    Route::post('/goal', [DepartmentManagementController::class, 'addGoal'])->name('goal.add');
    Route::put('/goal/{goal}', [DepartmentManagementController::class, 'updateGoal'])->name('goal.update');
    Route::delete('/goal/{goal}', [DepartmentManagementController::class, 'deleteGoal'])->name('goal.delete');
});

// ============================================================
// FACULTY MANAGEMENT
// ============================================================
Route::prefix('faculty')->name('faculty.')->group(function () {
    Route::get('/', [DepartmentManagementController::class, 'facultyList'])->name('index');
    Route::get('/directory', [DepartmentManagementController::class, 'facultyDirectory'])->name('directory');
    Route::get('/search', [DepartmentManagementController::class, 'searchFaculty'])->name('search');
    Route::get('/{faculty}', [DepartmentManagementController::class, 'facultyProfile'])->name('profile');
    
    // Faculty Assignments
    Route::get('/assignments', [DepartmentManagementController::class, 'facultyAssignments'])->name('assignments');
    Route::post('/assign', [DepartmentManagementController::class, 'assignFaculty'])->name('assign');
    Route::put('/assignment/{assignment}', [DepartmentManagementController::class, 'updateAssignment'])->name('assignment.update');
    Route::delete('/assignment/{assignment}', [DepartmentManagementController::class, 'removeAssignment'])->name('assignment.remove');
    Route::post('/affiliate/{faculty}', [DepartmentManagementController::class, 'affiliateFaculty'])->name('affiliate');
    Route::delete('/affiliate/{faculty}', [DepartmentManagementController::class, 'removeAffiliation'])->name('affiliate.remove');
    
    // Teaching Load
    Route::get('/teaching-load', [DepartmentManagementController::class, 'teachingLoads'])->name('teaching-load');
    Route::get('/teaching-load/{faculty}', [DepartmentManagementController::class, 'facultyTeachingLoad'])->name('teaching-load.faculty');
    Route::post('/teaching-load/{faculty}', [DepartmentManagementController::class, 'assignTeachingLoad'])->name('teaching-load.assign');
    Route::get('/load-distribution', [DepartmentManagementController::class, 'loadDistribution'])->name('load-distribution');
    Route::post('/load-balance', [DepartmentManagementController::class, 'balanceLoads'])->name('load-balance');
    
    // Faculty Evaluation
    Route::get('/evaluations', [DepartmentManagementController::class, 'facultyEvaluations'])->name('evaluations');
    Route::get('/evaluation/{faculty}', [DepartmentManagementController::class, 'facultyEvaluation'])->name('evaluation');
    Route::post('/evaluation/{faculty}', [DepartmentManagementController::class, 'submitEvaluation'])->name('evaluation.submit');
    Route::get('/evaluation/{evaluation}/review', [DepartmentManagementController::class, 'reviewEvaluation'])->name('evaluation.review');
    Route::post('/evaluation/{evaluation}/approve', [DepartmentManagementController::class, 'approveEvaluation'])->name('evaluation.approve');
    
    // Faculty Development
    Route::get('/development', [DepartmentManagementController::class, 'facultyDevelopment'])->name('development');
    Route::post('/development/plan/{faculty}', [DepartmentManagementController::class, 'createDevelopmentPlan'])->name('development.plan');
    Route::get('/sabbaticals', [DepartmentManagementController::class, 'sabbaticalRequests'])->name('sabbaticals');
    Route::post('/sabbatical/{request}/approve', [DepartmentManagementController::class, 'approveSabbatical'])->name('sabbatical.approve');
    Route::post('/sabbatical/{request}/deny', [DepartmentManagementController::class, 'denySabbatical'])->name('sabbatical.deny');
    
    // Hiring & Recruitment
    Route::get('/positions', [DepartmentManagementController::class, 'facultyPositions'])->name('positions');
    Route::get('/position/create', [DepartmentManagementController::class, 'createPosition'])->name('position.create');
    Route::post('/position', [DepartmentManagementController::class, 'storePosition'])->name('position.store');
    Route::get('/position/{position}', [DepartmentManagementController::class, 'viewPosition'])->name('position.view');
    Route::put('/position/{position}', [DepartmentManagementController::class, 'updatePosition'])->name('position.update');
    Route::post('/position/{position}/publish', [DepartmentManagementController::class, 'publishPosition'])->name('position.publish');
    Route::get('/applications', [DepartmentManagementController::class, 'facultyApplications'])->name('applications');
    Route::get('/application/{application}', [DepartmentManagementController::class, 'viewApplication'])->name('application.view');
});

// ============================================================
// COURSE & CURRICULUM MANAGEMENT
// ============================================================
Route::prefix('courses')->name('courses.')->group(function () {
    Route::get('/', [DepartmentManagementController::class, 'departmentCourses'])->name('index');
    Route::get('/catalog', [DepartmentManagementController::class, 'courseCatalog'])->name('catalog');
    Route::get('/planning', [DepartmentManagementController::class, 'coursePlanning'])->name('planning');
    
    // Course Management
    Route::get('/create', [CourseController::class, 'create'])->name('create');
    Route::post('/', [CourseController::class, 'store'])->name('store');
    Route::get('/{course}', [CourseController::class, 'show'])->name('show');
    Route::get('/{course}/edit', [CourseController::class, 'edit'])->name('edit');
    Route::put('/{course}', [CourseController::class, 'update'])->name('update');
    Route::delete('/{course}', [CourseController::class, 'destroy'])->name('destroy');
    Route::post('/{course}/activate', [CourseController::class, 'activate'])->name('activate');
    Route::post('/{course}/deactivate', [CourseController::class, 'deactivate'])->name('deactivate');
    
    // Course Proposals
    Route::get('/proposals', [DepartmentManagementController::class, 'courseProposals'])->name('proposals');
    Route::get('/proposal/create', [DepartmentManagementController::class, 'createProposal'])->name('proposal.create');
    Route::post('/proposal', [DepartmentManagementController::class, 'submitProposal'])->name('proposal.submit');
    Route::get('/proposal/{proposal}', [DepartmentManagementController::class, 'viewProposal'])->name('proposal.view');
    Route::post('/proposal/{proposal}/approve', [DepartmentManagementController::class, 'approveProposal'])->name('proposal.approve');
    Route::post('/proposal/{proposal}/reject', [DepartmentManagementController::class, 'rejectProposal'])->name('proposal.reject');
    Route::post('/proposal/{proposal}/revise', [DepartmentManagementController::class, 'requestRevision'])->name('proposal.revise');
    
    // Section Management
    Route::get('/sections', [DepartmentManagementController::class, 'courseSections'])->name('sections');
    Route::get('/sections/term/{term}', [DepartmentManagementController::class, 'termSections'])->name('sections.term');
    Route::post('/section/create', [DepartmentManagementController::class, 'createSection'])->name('section.create');
    Route::put('/section/{section}', [DepartmentManagementController::class, 'updateSection'])->name('section.update');
    Route::post('/section/{section}/cancel', [DepartmentManagementController::class, 'cancelSection'])->name('section.cancel');
    Route::post('/sections/merge', [DepartmentManagementController::class, 'mergeSections'])->name('sections.merge');
    
    // Instructor Assignment
    Route::get('/instructors', [DepartmentManagementController::class, 'instructorAssignments'])->name('instructors');
    Route::post('/section/{section}/assign-instructor', [DepartmentManagementController::class, 'assignInstructor'])->name('instructor.assign');
    Route::delete('/section/{section}/instructor/{instructor}', [DepartmentManagementController::class, 'removeInstructor'])->name('instructor.remove');
    Route::get('/instructor-availability', [DepartmentManagementController::class, 'instructorAvailability'])->name('instructor.availability');
    
    // Prerequisites & Requirements
    Route::get('/prerequisites', [DepartmentManagementController::class, 'prerequisiteManagement'])->name('prerequisites');
    Route::post('/prerequisite', [DepartmentManagementController::class, 'addPrerequisite'])->name('prerequisite.add');
    Route::delete('/prerequisite/{prerequisite}', [DepartmentManagementController::class, 'removePrerequisite'])->name('prerequisite.remove');
    Route::get('/prerequisite-chain/{course}', [DepartmentManagementController::class, 'prerequisiteChain'])->name('prerequisite.chain');
});

// ============================================================
// CURRICULUM MANAGEMENT
// ============================================================
Route::prefix('curriculum')->name('curriculum.')->group(function () {
    Route::get('/', [DepartmentManagementController::class, 'curriculumOverview'])->name('index');
    Route::get('/programs', [DepartmentManagementController::class, 'academicPrograms'])->name('programs');
    Route::get('/program/{program}', [DepartmentManagementController::class, 'programDetails'])->name('program.details');
    Route::get('/program/{program}/edit', [DepartmentManagementController::class, 'editProgram'])->name('program.edit');
    Route::put('/program/{program}', [DepartmentManagementController::class, 'updateProgram'])->name('program.update');
    
    // Curriculum Changes
    Route::get('/changes', [DepartmentManagementController::class, 'curriculumChanges'])->name('changes');
    Route::post('/change/propose', [DepartmentManagementController::class, 'proposeCurriculumChange'])->name('change.propose');
    Route::get('/change/{change}', [DepartmentManagementController::class, 'viewChange'])->name('change.view');
    Route::post('/change/{change}/approve', [DepartmentManagementController::class, 'approveChange'])->name('change.approve');
    Route::post('/change/{change}/reject', [DepartmentManagementController::class, 'rejectChange'])->name('change.reject');
    
    // Learning Outcomes
    Route::get('/outcomes', [DepartmentManagementController::class, 'learningOutcomes'])->name('outcomes');
    Route::post('/outcome', [DepartmentManagementController::class, 'addOutcome'])->name('outcome.add');
    Route::put('/outcome/{outcome}', [DepartmentManagementController::class, 'updateOutcome'])->name('outcome.update');
    Route::delete('/outcome/{outcome}', [DepartmentManagementController::class, 'deleteOutcome'])->name('outcome.delete');
    Route::get('/outcome-mapping', [DepartmentManagementController::class, 'outcomeMapping'])->name('outcome.mapping');
    Route::post('/outcome-mapping', [DepartmentManagementController::class, 'mapOutcomes'])->name('outcome.map');
    
    // Assessment
    Route::get('/assessment', [DepartmentManagementController::class, 'curriculumAssessment'])->name('assessment');
    Route::post('/assessment/plan', [DepartmentManagementController::class, 'createAssessmentPlan'])->name('assessment.plan');
    Route::get('/assessment/results', [DepartmentManagementController::class, 'assessmentResults'])->name('assessment.results');
    Route::post('/assessment/report', [DepartmentManagementController::class, 'generateAssessmentReport'])->name('assessment.report');
});

// ============================================================
// STUDENT MANAGEMENT
// ============================================================
Route::prefix('students')->name('students.')->group(function () {
    Route::get('/', [DepartmentManagementController::class, 'departmentStudents'])->name('index');
    Route::get('/majors', [DepartmentManagementController::class, 'studentsByMajor'])->name('majors');
    Route::get('/minors', [DepartmentManagementController::class, 'studentsByMinor'])->name('minors');
    Route::get('/year/{year}', [DepartmentManagementController::class, 'studentsByYear'])->name('year');
    Route::get('/search', [DepartmentManagementController::class, 'searchStudents'])->name('search');
    Route::get('/{student}', [DepartmentManagementController::class, 'studentProfile'])->name('profile');
    
    // Academic Progress
    Route::get('/progress', [DepartmentManagementController::class, 'studentProgress'])->name('progress');
    Route::get('/progress/{student}', [DepartmentManagementController::class, 'individualProgress'])->name('progress.individual');
    Route::get('/at-risk', [DepartmentManagementController::class, 'atRiskStudents'])->name('at-risk');
    Route::get('/honors', [DepartmentManagementController::class, 'honorsStudents'])->name('honors');
    Route::get('/standing', [DepartmentManagementController::class, 'academicStanding'])->name('standing');
    
    // Advising Oversight
    Route::get('/advising', [DepartmentManagementController::class, 'advisingOversight'])->name('advising');
    Route::get('/advising/assignments', [DepartmentManagementController::class, 'advisorAssignments'])->name('advising.assignments');
    Route::post('/advising/assign', [DepartmentManagementController::class, 'assignAdvisor'])->name('advising.assign');
    Route::post('/advising/reassign', [DepartmentManagementController::class, 'reassignAdvisor'])->name('advising.reassign');
    Route::get('/advising/load', [DepartmentManagementController::class, 'advisingLoad'])->name('advising.load');
    
    // Retention & Success
    Route::get('/retention', [DepartmentManagementController::class, 'retentionAnalysis'])->name('retention');
    Route::get('/success-metrics', [DepartmentManagementController::class, 'successMetrics'])->name('success');
    Route::post('/intervention/{student}', [DepartmentManagementController::class, 'createIntervention'])->name('intervention');
    Route::get('/graduation-tracking', [DepartmentManagementController::class, 'graduationTracking'])->name('graduation');
});

// ============================================================
// SCHEDULING & RESOURCES
// ============================================================
Route::prefix('scheduling')->name('scheduling.')->group(function () {
    Route::get('/', [SchedulingController::class, 'departmentScheduling'])->name('index');
    Route::get('/term/{term}', [SchedulingController::class, 'termSchedule'])->name('term');
    Route::get('/planning', [SchedulingController::class, 'schedulePlanning'])->name('planning');
    Route::post('/generate', [SchedulingController::class, 'generateSchedule'])->name('generate');
    Route::get('/conflicts', [SchedulingController::class, 'scheduleConflicts'])->name('conflicts');
    Route::post('/conflict/{conflict}/resolve', [SchedulingController::class, 'resolveConflict'])->name('conflict.resolve');
    
    // Room Management
    Route::get('/rooms', [SchedulingController::class, 'departmentRooms'])->name('rooms');
    Route::get('/room-requests', [SchedulingController::class, 'roomRequests'])->name('room-requests');
    Route::post('/room-request', [SchedulingController::class, 'submitRoomRequest'])->name('room-request.submit');
    Route::get('/room-utilization', [SchedulingController::class, 'roomUtilization'])->name('room-utilization');
    Route::post('/room/reserve', [SchedulingController::class, 'reserveRoom'])->name('room.reserve');
    
    // Time Slots
    Route::get('/time-slots', [SchedulingController::class, 'timeSlotManagement'])->name('time-slots');
    Route::get('/time-patterns', [SchedulingController::class, 'timePatterns'])->name('time-patterns');
    Route::post('/optimize', [SchedulingController::class, 'optimizeSchedule'])->name('optimize');
    
    // Special Scheduling
    Route::get('/special', [SchedulingController::class, 'specialScheduling'])->name('special');
    Route::post('/independent-study', [SchedulingController::class, 'scheduleIndependentStudy'])->name('independent-study');
    Route::post('/directed-reading', [SchedulingController::class, 'scheduleDirectedReading'])->name('directed-reading');
    Route::post('/thesis', [SchedulingController::class, 'scheduleThesis'])->name('thesis');
});

// ============================================================
// BUDGET & RESOURCES
// ============================================================
Route::prefix('budget')->name('budget.')->group(function () {
    Route::get('/', [DepartmentManagementController::class, 'budgetOverview'])->name('index');
    Route::get('/current', [DepartmentManagementController::class, 'currentBudget'])->name('current');
    Route::get('/allocations', [DepartmentManagementController::class, 'budgetAllocations'])->name('allocations');
    Route::get('/expenses', [DepartmentManagementController::class, 'expenses'])->name('expenses');
    Route::get('/projections', [DepartmentManagementController::class, 'budgetProjections'])->name('projections');
    
    // Budget Requests
    Route::get('/requests', [DepartmentManagementController::class, 'budgetRequests'])->name('requests');
    Route::get('/request/create', [DepartmentManagementController::class, 'createBudgetRequest'])->name('request.create');
    Route::post('/request', [DepartmentManagementController::class, 'submitBudgetRequest'])->name('request.submit');
    Route::get('/request/{request}', [DepartmentManagementController::class, 'viewBudgetRequest'])->name('request.view');
    Route::put('/request/{request}', [DepartmentManagementController::class, 'updateBudgetRequest'])->name('request.update');
    
    // Resource Management
    Route::get('/resources', [DepartmentManagementController::class, 'resourceManagement'])->name('resources');
    Route::get('/equipment', [DepartmentManagementController::class, 'equipmentInventory'])->name('equipment');
    Route::post('/equipment/request', [DepartmentManagementController::class, 'requestEquipment'])->name('equipment.request');
    Route::get('/supplies', [DepartmentManagementController::class, 'supplies'])->name('supplies');
    Route::post('/supplies/order', [DepartmentManagementController::class, 'orderSupplies'])->name('supplies.order');
    
    // Funding
    Route::get('/funding', [DepartmentManagementController::class, 'fundingSources'])->name('funding');
    Route::get('/grants', [DepartmentManagementController::class, 'departmentGrants'])->name('grants');
    Route::get('/endowments', [DepartmentManagementController::class, 'endowments'])->name('endowments');
    Route::get('/scholarships', [DepartmentManagementController::class, 'departmentScholarships'])->name('scholarships');
});

// ============================================================
// RESEARCH & SCHOLARLY ACTIVITIES
// ============================================================
Route::prefix('research')->name('research.')->group(function () {
    Route::get('/', [DepartmentManagementController::class, 'researchOverview'])->name('index');
    Route::get('/projects', [DepartmentManagementController::class, 'researchProjects'])->name('projects');
    Route::get('/project/{project}', [DepartmentManagementController::class, 'projectDetails'])->name('project.details');
    Route::post('/project', [DepartmentManagementController::class, 'createProject'])->name('project.create');
    Route::put('/project/{project}', [DepartmentManagementController::class, 'updateProject'])->name('project.update');
    
    // Publications
    Route::get('/publications', [DepartmentManagementController::class, 'departmentPublications'])->name('publications');
    Route::post('/publication', [DepartmentManagementController::class, 'recordPublication'])->name('publication.add');
    Route::get('/citations', [DepartmentManagementController::class, 'citationMetrics'])->name('citations');
    
    // Grants
    Route::get('/grants', [DepartmentManagementController::class, 'researchGrants'])->name('grants');
    Route::get('/grant-opportunities', [DepartmentManagementController::class, 'grantOpportunities'])->name('grant-opportunities');
    Route::post('/grant-application', [DepartmentManagementController::class, 'submitGrantApplication'])->name('grant.apply');
    Route::get('/grant/{grant}', [DepartmentManagementController::class, 'grantDetails'])->name('grant.details');
    
    // Research Students
    Route::get('/students', [DepartmentManagementController::class, 'researchStudents'])->name('students');
    Route::get('/assistantships', [DepartmentManagementController::class, 'researchAssistantships'])->name('assistantships');
    Route::post('/assistantship', [DepartmentManagementController::class, 'createAssistantship'])->name('assistantship.create');
    Route::post('/assistantship/{assistantship}/assign', [DepartmentManagementController::class, 'assignAssistantship'])->name('assistantship.assign');
});

// ============================================================
// COMMITTEES & GOVERNANCE
// ============================================================
Route::prefix('committees')->name('committees.')->group(function () {
    Route::get('/', [DepartmentManagementController::class, 'committees'])->name('index');
    Route::get('/create', [DepartmentManagementController::class, 'createCommittee'])->name('create');
    Route::post('/', [DepartmentManagementController::class, 'storeCommittee'])->name('store');
    Route::get('/{committee}', [DepartmentManagementController::class, 'committeeDetails'])->name('details');
    Route::put('/{committee}', [DepartmentManagementController::class, 'updateCommittee'])->name('update');
    Route::delete('/{committee}', [DepartmentManagementController::class, 'dissolveCommittee'])->name('dissolve');
    
    // Committee Members
    Route::get('/{committee}/members', [DepartmentManagementController::class, 'committeeMembers'])->name('members');
    Route::post('/{committee}/member', [DepartmentManagementController::class, 'addMember'])->name('member.add');
    Route::delete('/{committee}/member/{member}', [DepartmentManagementController::class, 'removeMember'])->name('member.remove');
    Route::post('/{committee}/chair', [DepartmentManagementController::class, 'appointChair'])->name('chair.appoint');
    
    // Committee Activities
    Route::get('/{committee}/meetings', [DepartmentManagementController::class, 'committeeMeetings'])->name('meetings');
    Route::post('/{committee}/meeting', [DepartmentManagementController::class, 'scheduleMeeting'])->name('meeting.schedule');
    Route::get('/{committee}/minutes', [DepartmentManagementController::class, 'meetingMinutes'])->name('minutes');
    Route::post('/{committee}/minutes', [DepartmentManagementController::class, 'uploadMinutes'])->name('minutes.upload');
    Route::get('/{committee}/decisions', [DepartmentManagementController::class, 'committeeDecisions'])->name('decisions');
});

// ============================================================
// ACCREDITATION & COMPLIANCE
// ============================================================
Route::prefix('accreditation')->name('accreditation.')->group(function () {
    Route::get('/', [DepartmentManagementController::class, 'accreditationOverview'])->name('index');
    Route::get('/status', [DepartmentManagementController::class, 'accreditationStatus'])->name('status');
    Route::get('/requirements', [DepartmentManagementController::class, 'accreditationRequirements'])->name('requirements');
    Route::get('/timeline', [DepartmentManagementController::class, 'accreditationTimeline'])->name('timeline');
    
    // Self-Study
    Route::get('/self-study', [DepartmentManagementController::class, 'selfStudy'])->name('self-study');
    Route::post('/self-study/section', [DepartmentManagementController::class, 'updateSelfStudySection'])->name('self-study.section');
    Route::get('/self-study/export', [DepartmentManagementController::class, 'exportSelfStudy'])->name('self-study.export');
    
    // Documentation
    Route::get('/documents', [DepartmentManagementController::class, 'accreditationDocuments'])->name('documents');
    Route::post('/document/upload', [DepartmentManagementController::class, 'uploadDocument'])->name('document.upload');
    Route::get('/document/{document}', [DepartmentManagementController::class, 'viewDocument'])->name('document.view');
    
    // Site Visits
    Route::get('/visits', [DepartmentManagementController::class, 'siteVisits'])->name('visits');
    Route::get('/visit/{visit}', [DepartmentManagementController::class, 'visitDetails'])->name('visit.details');
    Route::post('/visit/schedule', [DepartmentManagementController::class, 'scheduleSiteVisit'])->name('visit.schedule');
    Route::get('/visit/{visit}/preparation', [DepartmentManagementController::class, 'visitPreparation'])->name('visit.preparation');
});

// ============================================================
// EVENTS & ACTIVITIES
// ============================================================
Route::prefix('events')->name('events.')->group(function () {
    Route::get('/', [DepartmentManagementController::class, 'departmentEvents'])->name('index');
    Route::get('/calendar', [DepartmentManagementController::class, 'eventCalendar'])->name('calendar');
    Route::get('/create', [DepartmentManagementController::class, 'createEvent'])->name('create');
    Route::post('/', [DepartmentManagementController::class, 'storeEvent'])->name('store');
    Route::get('/{event}', [DepartmentManagementController::class, 'eventDetails'])->name('details');
    Route::put('/{event}', [DepartmentManagementController::class, 'updateEvent'])->name('update');
    Route::delete('/{event}', [DepartmentManagementController::class, 'cancelEvent'])->name('cancel');
    
    // Event Types
    Route::get('/seminars', [DepartmentManagementController::class, 'seminars'])->name('seminars');
    Route::get('/colloquia', [DepartmentManagementController::class, 'colloquia'])->name('colloquia');
    Route::get('/workshops', [DepartmentManagementController::class, 'workshops'])->name('workshops');
    Route::get('/conferences', [DepartmentManagementController::class, 'conferences'])->name('conferences');
    Route::get('/guest-lectures', [DepartmentManagementController::class, 'guestLectures'])->name('guest-lectures');
    
    // Event Management
    Route::post('/{event}/register', [DepartmentManagementController::class, 'registerForEvent'])->name('register');
    Route::get('/{event}/attendees', [DepartmentManagementController::class, 'eventAttendees'])->name('attendees');
    Route::post('/{event}/invite', [DepartmentManagementController::class, 'sendInvitations'])->name('invite');
    Route::get('/{event}/feedback', [DepartmentManagementController::class, 'eventFeedback'])->name('feedback');
});

// ============================================================
// COMMUNICATIONS
// ============================================================
Route::prefix('communications')->name('communications.')->group(function () {
    Route::get('/', [DepartmentManagementController::class, 'communicationsHub'])->name('index');
    
    // Announcements
    Route::get('/announcements', [DepartmentManagementController::class, 'announcements'])->name('announcements');
    Route::post('/announcement', [DepartmentManagementController::class, 'createAnnouncement'])->name('announcement.create');
    Route::put('/announcement/{announcement}', [DepartmentManagementController::class, 'updateAnnouncement'])->name('announcement.update');
    Route::delete('/announcement/{announcement}', [DepartmentManagementController::class, 'deleteAnnouncement'])->name('announcement.delete');
    
    // Newsletter
    Route::get('/newsletter', [DepartmentManagementController::class, 'newsletter'])->name('newsletter');
    Route::post('/newsletter/create', [DepartmentManagementController::class, 'createNewsletter'])->name('newsletter.create');
    Route::post('/newsletter/{newsletter}/send', [DepartmentManagementController::class, 'sendNewsletter'])->name('newsletter.send');
    Route::get('/newsletter/archive', [DepartmentManagementController::class, 'newsletterArchive'])->name('newsletter.archive');
    
    // Website Management
    Route::get('/website', [DepartmentManagementController::class, 'websiteManagement'])->name('website');
    Route::post('/website/update', [DepartmentManagementController::class, 'updateWebsite'])->name('website.update');
    Route::get('/website/analytics', [DepartmentManagementController::class, 'websiteAnalytics'])->name('website.analytics');
    
    // Social Media
    Route::get('/social-media', [DepartmentManagementController::class, 'socialMedia'])->name('social-media');
    Route::post('/social-media/post', [DepartmentManagementController::class, 'createSocialPost'])->name('social-media.post');
});

// ============================================================
// REPORTS & ANALYTICS
// ============================================================
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [DepartmentManagementController::class, 'reportsHub'])->name('index');
    
    // Academic Reports
    Route::get('/academic', [DepartmentManagementController::class, 'academicReports'])->name('academic');
    Route::get('/enrollment', [DepartmentManagementController::class, 'enrollmentReport'])->name('enrollment');
    Route::get('/grades', [DepartmentManagementController::class, 'gradesReport'])->name('grades');
    Route::get('/graduation', [DepartmentManagementController::class, 'graduationReport'])->name('graduation');
    Route::get('/retention', [DepartmentManagementController::class, 'retentionReport'])->name('retention');
    
    // Faculty Reports
    Route::get('/faculty', [DepartmentManagementController::class, 'facultyReports'])->name('faculty');
    Route::get('/teaching-load', [DepartmentManagementController::class, 'teachingLoadReport'])->name('teaching-load');
    Route::get('/faculty-productivity', [DepartmentManagementController::class, 'productivityReport'])->name('productivity');
    Route::get('/evaluations', [DepartmentManagementController::class, 'evaluationReports'])->name('evaluations');
    
    // Resource Reports
    Route::get('/budget', [DepartmentManagementController::class, 'budgetReport'])->name('budget');
    Route::get('/space-utilization', [DepartmentManagementController::class, 'spaceUtilizationReport'])->name('space');
    Route::get('/equipment', [DepartmentManagementController::class, 'equipmentReport'])->name('equipment');
    
    // Annual Report
    Route::get('/annual', [DepartmentManagementController::class, 'annualReport'])->name('annual');
    Route::post('/annual/generate', [DepartmentManagementController::class, 'generateAnnualReport'])->name('annual.generate');
    Route::get('/annual/archive', [DepartmentManagementController::class, 'annualReportArchive'])->name('annual.archive');
    
    // Custom Reports
    Route::get('/builder', [DepartmentManagementController::class, 'reportBuilder'])->name('builder');
    Route::post('/generate', [DepartmentManagementController::class, 'generateReport'])->name('generate');
    Route::post('/export', [DepartmentManagementController::class, 'exportReport'])->name('export');
});