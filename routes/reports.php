<?php
/**
 * IntelliCampus Reports & Analytics Routes
 * 
 * Centralized reporting and analytics routes for all modules.
 * These routes are automatically prefixed with 'reports' and named with 'reports.'
 * Applied middleware: 'web', 'auth', 'permission:reports.view'
 */

use App\Http\Controllers\Admin\GradeReportController;
use App\Http\Controllers\AdmissionsReportController;
use App\Http\Controllers\FinancialController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\SystemConfigurationController;
use Illuminate\Support\Facades\Route;

// ============================================================
// REPORTS DASHBOARD
// ============================================================
Route::get('/', function() {
    return view('reports.dashboard');
})->name('index');

Route::get('/dashboard', function() {
    return view('reports.dashboard');
})->name('dashboard');

Route::get('/quick-access', function() {
    return view('reports.quick-access');
})->name('quick-access');

Route::get('/favorites', function() {
    return view('reports.favorites');
})->name('favorites');

// ============================================================
// ACADEMIC REPORTS
// ============================================================
Route::prefix('academic')->name('academic.')->group(function () {
    Route::get('/', [GradeReportController::class, 'academicReports'])->name('index');
    
    // Enrollment Reports
    Route::get('/enrollment', [RegistrationController::class, 'enrollmentReport'])->name('enrollment');
    Route::get('/enrollment/summary', [RegistrationController::class, 'enrollmentSummary'])->name('enrollment.summary');
    Route::get('/enrollment/trends', [RegistrationController::class, 'enrollmentTrends'])->name('enrollment.trends');
    Route::get('/enrollment/by-program', [RegistrationController::class, 'enrollmentByProgram'])->name('enrollment.program');
    Route::get('/enrollment/by-department', [RegistrationController::class, 'enrollmentByDepartment'])->name('enrollment.department');
    Route::get('/enrollment/demographics', [RegistrationController::class, 'enrollmentDemographics'])->name('enrollment.demographics');
    Route::get('/enrollment/geographic', [RegistrationController::class, 'enrollmentGeographic'])->name('enrollment.geographic');
    
    // Grade Reports
    Route::get('/grades', [GradeReportController::class, 'gradesReport'])->name('grades');
    Route::get('/grades/distribution', [GradeReportController::class, 'gradeDistribution'])->name('grades.distribution');
    Route::get('/grades/gpa-analysis', [GradeReportController::class, 'gpaAnalysis'])->name('grades.gpa');
    Route::get('/grades/deans-list', [GradeReportController::class, 'deansList'])->name('grades.deans-list');
    Route::get('/grades/probation', [GradeReportController::class, 'academicProbation'])->name('grades.probation');
    Route::get('/grades/standing', [GradeReportController::class, 'academicStanding'])->name('grades.standing');
    Route::get('/grades/incomplete', [GradeReportController::class, 'incompleteGrades'])->name('grades.incomplete');
    
    // Course Reports
    Route::get('/courses', [RegistrationController::class, 'courseReports'])->name('courses');
    Route::get('/courses/offerings', [RegistrationController::class, 'courseOfferings'])->name('courses.offerings');
    Route::get('/courses/capacity', [RegistrationController::class, 'courseCapacity'])->name('courses.capacity');
    Route::get('/courses/waitlists', [RegistrationController::class, 'waitlistReport'])->name('courses.waitlists');
    Route::get('/courses/cancellations', [RegistrationController::class, 'courseCancellations'])->name('courses.cancellations');
    
    // Student Progress
    Route::get('/progress', [GradeReportController::class, 'studentProgress'])->name('progress');
    Route::get('/progress/retention', [GradeReportController::class, 'retentionReport'])->name('progress.retention');
    Route::get('/progress/graduation', [GradeReportController::class, 'graduationRates'])->name('progress.graduation');
    Route::get('/progress/completion', [GradeReportController::class, 'completionRates'])->name('progress.completion');
    Route::get('/progress/time-to-degree', [GradeReportController::class, 'timeToDegree'])->name('progress.time-to-degree');
});

// ============================================================
// FINANCIAL REPORTS
// ============================================================
Route::prefix('financial')->name('financial.')->group(function () {
    Route::get('/', [FinancialController::class, 'financialReports'])->name('index');
    
    // Revenue Reports
    Route::get('/revenue', [FinancialController::class, 'revenueReport'])->name('revenue');
    Route::get('/revenue/tuition', [FinancialController::class, 'tuitionRevenue'])->name('revenue.tuition');
    Route::get('/revenue/fees', [FinancialController::class, 'feeRevenue'])->name('revenue.fees');
    Route::get('/revenue/projections', [FinancialController::class, 'revenueProjections'])->name('revenue.projections');
    
    // Receivables Reports
    Route::get('/receivables', [FinancialController::class, 'receivablesReport'])->name('receivables');
    Route::get('/receivables/aging', [FinancialController::class, 'agingReport'])->name('receivables.aging');
    Route::get('/receivables/outstanding', [FinancialController::class, 'outstandingBalances'])->name('receivables.outstanding');
    Route::get('/receivables/collections', [FinancialController::class, 'collectionsReport'])->name('receivables.collections');
    
    // Financial Aid Reports
    Route::get('/aid', [FinancialController::class, 'financialAidReport'])->name('aid');
    Route::get('/aid/disbursements', [FinancialController::class, 'aidDisbursements'])->name('aid.disbursements');
    Route::get('/aid/scholarships', [FinancialController::class, 'scholarshipReport'])->name('aid.scholarships');
    Route::get('/aid/loans', [FinancialController::class, 'loanReport'])->name('aid.loans');
    
    // Payment Reports
    Route::get('/payments', [FinancialController::class, 'paymentReport'])->name('payments');
    Route::get('/payments/methods', [FinancialController::class, 'paymentMethods'])->name('payments.methods');
    Route::get('/payments/plans', [FinancialController::class, 'paymentPlansReport'])->name('payments.plans');
    Route::get('/payments/refunds', [FinancialController::class, 'refundsReport'])->name('payments.refunds');
    
    // Daily Reports
    Route::get('/daily-cash', [FinancialController::class, 'dailyCashReport'])->name('daily-cash');
    Route::get('/daily-transactions', [FinancialController::class, 'dailyTransactions'])->name('daily-transactions');
    
    // Tax Reports
    Route::get('/tax/1098t', [FinancialController::class, 'form1098TReport'])->name('tax.1098t');
    Route::get('/tax/summary', [FinancialController::class, 'taxSummary'])->name('tax.summary');
});

// ============================================================
// ADMISSIONS REPORTS
// ============================================================
Route::prefix('admissions')->name('admissions.')->group(function () {
    Route::get('/', [AdmissionsReportController::class, 'index'])->name('index');
    
    // Application Reports
    Route::get('/applications', [AdmissionsReportController::class, 'applicationReport'])->name('applications');
    Route::get('/applications/status', [AdmissionsReportController::class, 'applicationStatus'])->name('applications.status');
    Route::get('/applications/sources', [AdmissionsReportController::class, 'applicationSources'])->name('applications.sources');
    Route::get('/applications/completion', [AdmissionsReportController::class, 'applicationCompletion'])->name('applications.completion');
    
    // Admission Funnel
    Route::get('/funnel', [AdmissionsReportController::class, 'admissionsFunnel'])->name('funnel');
    Route::get('/funnel/conversion', [AdmissionsReportController::class, 'conversionRates'])->name('funnel.conversion');
    Route::get('/funnel/yield', [AdmissionsReportController::class, 'yieldAnalysis'])->name('funnel.yield');
    Route::get('/funnel/melt', [AdmissionsReportController::class, 'summerMelt'])->name('funnel.melt');
    
    // Demographics
    Route::get('/demographics', [AdmissionsReportController::class, 'demographicAnalysis'])->name('demographics');
    Route::get('/demographics/diversity', [AdmissionsReportController::class, 'diversityReport'])->name('demographics.diversity');
    Route::get('/demographics/geographic', [AdmissionsReportController::class, 'geographicDistribution'])->name('demographics.geographic');
    Route::get('/demographics/academic', [AdmissionsReportController::class, 'academicProfile'])->name('demographics.academic');
    
    // Comparative Reports
    Route::get('/comparative', [AdmissionsReportController::class, 'comparativeAnalysis'])->name('comparative');
    Route::get('/comparative/year-over-year', [AdmissionsReportController::class, 'yearOverYear'])->name('comparative.yoy');
    Route::get('/comparative/program', [AdmissionsReportController::class, 'programComparison'])->name('comparative.program');
});

// ============================================================
// REGISTRATION REPORTS
// ============================================================
Route::prefix('registration')->name('registration.')->group(function () {
    Route::get('/', [RegistrationController::class, 'registrationReports'])->name('index');
    
    // Registration Activity
    Route::get('/activity', [RegistrationController::class, 'registrationActivity'])->name('activity');
    Route::get('/activity/daily', [RegistrationController::class, 'dailyRegistrations'])->name('activity.daily');
    Route::get('/activity/peak-times', [RegistrationController::class, 'peakTimes'])->name('activity.peak-times');
    Route::get('/activity/add-drop', [RegistrationController::class, 'addDropActivity'])->name('activity.add-drop');
    
    // Registration Status
    Route::get('/status', [RegistrationController::class, 'registrationStatus'])->name('status');
    Route::get('/status/holds', [RegistrationController::class, 'registrationHolds'])->name('status.holds');
    Route::get('/status/overrides', [RegistrationController::class, 'registrationOverrides'])->name('status.overrides');
    Route::get('/status/waitlists', [RegistrationController::class, 'waitlistStatus'])->name('status.waitlists');
});

// ============================================================
// FACULTY & STAFF REPORTS
// ============================================================
Route::prefix('faculty')->name('faculty.')->group(function () {
    Route::get('/', function() { return view('reports.faculty.index'); })->name('index');
    
    // Teaching Reports
    Route::get('/teaching-load', function() { return view('reports.faculty.teaching-load'); })->name('teaching-load');
    Route::get('/course-evaluations', function() { return view('reports.faculty.evaluations'); })->name('evaluations');
    Route::get('/office-hours', function() { return view('reports.faculty.office-hours'); })->name('office-hours');
    
    // Faculty Activity
    Route::get('/activity', function() { return view('reports.faculty.activity'); })->name('activity');
    Route::get('/research', function() { return view('reports.faculty.research'); })->name('research');
    Route::get('/service', function() { return view('reports.faculty.service'); })->name('service');
    
    // Advising Reports
    Route::get('/advising-load', function() { return view('reports.faculty.advising-load'); })->name('advising-load');
    Route::get('/advising-activity', function() { return view('reports.faculty.advising-activity'); })->name('advising-activity');
});

// ============================================================
// COMPLIANCE REPORTS
// ============================================================
Route::prefix('compliance')->name('compliance.')->middleware(['role:admin,registrar,compliance-officer'])->group(function () {
    Route::get('/', function() { return view('reports.compliance.index'); })->name('index');
    
    // Federal Reporting
    Route::get('/ipeds', [RegistrationController::class, 'ipedsReport'])->name('ipeds');
    Route::get('/clery', function() { return view('reports.compliance.clery'); })->name('clery');
    Route::get('/title-iv', [RegistrationController::class, 'titleIVReport'])->name('title-iv');
    Route::get('/gainful-employment', function() { return view('reports.compliance.gainful-employment'); })->name('gainful-employment');
    
    // State Reporting
    Route::get('/state', function() { return view('reports.compliance.state'); })->name('state');
    Route::get('/veterans', [RegistrationController::class, 'veteransReport'])->name('veterans');
    
    // Accreditation
    Route::get('/accreditation', function() { return view('reports.compliance.accreditation'); })->name('accreditation');
    Route::get('/program-review', function() { return view('reports.compliance.program-review'); })->name('program-review');
    
    // Audit Reports
    Route::get('/audit', function() { return view('reports.compliance.audit'); })->name('audit');
    Route::get('/ferpa', function() { return view('reports.compliance.ferpa'); })->name('ferpa');
});

// ============================================================
// OPERATIONAL REPORTS
// ============================================================
Route::prefix('operational')->name('operational.')->group(function () {
    Route::get('/', [SystemConfigurationController::class, 'operationalReports'])->name('index');
    
    // System Performance
    Route::get('/performance', [SystemConfigurationController::class, 'performanceReport'])->name('performance');
    Route::get('/performance/response-time', [SystemConfigurationController::class, 'responseTime'])->name('performance.response-time');
    Route::get('/performance/uptime', [SystemConfigurationController::class, 'uptimeReport'])->name('performance.uptime');
    Route::get('/performance/errors', [SystemConfigurationController::class, 'errorReport'])->name('performance.errors');
    
    // User Activity
    Route::get('/users', [SystemConfigurationController::class, 'userActivityReport'])->name('users');
    Route::get('/users/logins', [SystemConfigurationController::class, 'loginReport'])->name('users.logins');
    Route::get('/users/sessions', [SystemConfigurationController::class, 'sessionReport'])->name('users.sessions');
    Route::get('/users/actions', [SystemConfigurationController::class, 'userActionsReport'])->name('users.actions');
    
    // Module Usage
    Route::get('/modules', [SystemConfigurationController::class, 'moduleUsageReport'])->name('modules');
    Route::get('/modules/adoption', [SystemConfigurationController::class, 'moduleAdoption'])->name('modules.adoption');
    Route::get('/modules/performance', [SystemConfigurationController::class, 'modulePerformance'])->name('modules.performance');
    
    // Resource Utilization
    Route::get('/resources', [SystemConfigurationController::class, 'resourceReport'])->name('resources');
    Route::get('/resources/storage', [SystemConfigurationController::class, 'storageReport'])->name('resources.storage');
    Route::get('/resources/bandwidth', [SystemConfigurationController::class, 'bandwidthReport'])->name('resources.bandwidth');
});

// ============================================================
// DASHBOARD REPORTS
// ============================================================
Route::prefix('dashboards')->name('dashboards.')->group(function () {
    Route::get('/executive', function() { return view('reports.dashboards.executive'); })->name('executive');
    Route::get('/academic', function() { return view('reports.dashboards.academic'); })->name('academic');
    Route::get('/financial', function() { return view('reports.dashboards.financial'); })->name('financial');
    Route::get('/enrollment', function() { return view('reports.dashboards.enrollment'); })->name('enrollment');
    Route::get('/retention', function() { return view('reports.dashboards.retention'); })->name('retention');
});

// ============================================================
// CUSTOM REPORTS
// ============================================================
Route::prefix('custom')->name('custom.')->group(function () {
    Route::get('/', function() { return view('reports.custom.index'); })->name('index');
    Route::get('/builder', function() { return view('reports.custom.builder'); })->name('builder');
    Route::post('/generate', [SystemConfigurationController::class, 'generateCustomReport'])->name('generate');
    Route::get('/saved', function() { return view('reports.custom.saved'); })->name('saved');
    Route::get('/templates', function() { return view('reports.custom.templates'); })->name('templates');
    Route::post('/save', [SystemConfigurationController::class, 'saveReport'])->name('save');
    Route::get('/{report}', [SystemConfigurationController::class, 'viewCustomReport'])->name('view');
    Route::put('/{report}', [SystemConfigurationController::class, 'updateCustomReport'])->name('update');
    Route::delete('/{report}', [SystemConfigurationController::class, 'deleteCustomReport'])->name('delete');
});

// ============================================================
// SCHEDULED REPORTS
// ============================================================
Route::prefix('scheduled')->name('scheduled.')->group(function () {
    Route::get('/', [SystemConfigurationController::class, 'scheduledReports'])->name('index');
    Route::get('/create', [SystemConfigurationController::class, 'createScheduledReport'])->name('create');
    Route::post('/', [SystemConfigurationController::class, 'storeScheduledReport'])->name('store');
    Route::get('/{schedule}', [SystemConfigurationController::class, 'viewSchedule'])->name('view');
    Route::put('/{schedule}', [SystemConfigurationController::class, 'updateSchedule'])->name('update');
    Route::delete('/{schedule}', [SystemConfigurationController::class, 'deleteSchedule'])->name('delete');
    Route::post('/{schedule}/run', [SystemConfigurationController::class, 'runScheduledReport'])->name('run');
    Route::post('/{schedule}/pause', [SystemConfigurationController::class, 'pauseSchedule'])->name('pause');
    Route::post('/{schedule}/resume', [SystemConfigurationController::class, 'resumeSchedule'])->name('resume');
});

// ============================================================
// REPORT EXPORTS & DISTRIBUTION
// ============================================================
Route::prefix('export')->name('export.')->group(function () {
    Route::post('/pdf', [SystemConfigurationController::class, 'exportPDF'])->name('pdf');
    Route::post('/excel', [SystemConfigurationController::class, 'exportExcel'])->name('excel');
    Route::post('/csv', [SystemConfigurationController::class, 'exportCSV'])->name('csv');
    Route::post('/json', [SystemConfigurationController::class, 'exportJSON'])->name('json');
    Route::post('/email', [SystemConfigurationController::class, 'emailReport'])->name('email');
    Route::post('/schedule-email', [SystemConfigurationController::class, 'scheduleEmail'])->name('schedule-email');
});

// ============================================================
// REPORT ADMINISTRATION
// ============================================================
Route::prefix('admin')->name('admin.')->middleware(['role:admin,report-admin'])->group(function () {
    Route::get('/', function() { return view('reports.admin.index'); })->name('index');
    Route::get('/permissions', function() { return view('reports.admin.permissions'); })->name('permissions');
    Route::post('/permissions', [SystemConfigurationController::class, 'updateReportPermissions'])->name('permissions.update');
    Route::get('/audit', function() { return view('reports.admin.audit'); })->name('audit');
    Route::get('/usage', function() { return view('reports.admin.usage'); })->name('usage');
    Route::get('/performance', function() { return view('reports.admin.performance'); })->name('performance');
});