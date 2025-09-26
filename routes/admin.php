<?php
/**
 * IntelliCampus Admin Routes
 * 
 * All administrative routes for system management.
 * These routes are automatically prefixed with 'admin' and named with 'admin.'
 * Applied middleware: 'web', 'auth', 'verified', 'role:admin,super-administrator'
 */

use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionManagementController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\FinancialController;
use App\Http\Controllers\SystemConfigurationController;
use App\Http\Controllers\AdmissionsController;
use App\Http\Controllers\Admin\AdminStudentHelperController;
use App\Http\Controllers\Admin\GradeApprovalController;
use App\Http\Controllers\Admin\GradeDeadlineController;
use App\Http\Controllers\Admin\GradeReportController;
use App\Http\Controllers\Admin\GradeScaleController;
use App\Http\Controllers\AdminApplicationController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// ============================================================
// ADMIN DASHBOARD
// ============================================================
Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('main');
Route::get('/quick-stats', [AdminController::class, 'quickStats'])->name('quick-stats');
Route::get('/notifications', [AdminController::class, 'notifications'])->name('notifications');

// ============================================================
// USER MANAGEMENT
// ============================================================
Route::prefix('users')->name('users.')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::get('/search', [UserController::class, 'search'])->name('search');
    Route::get('/create', [UserController::class, 'create'])->name('create');
    Route::post('/', [UserController::class, 'store'])->name('store');
    Route::get('/{user}', [UserController::class, 'show'])->name('show');
    Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
    Route::put('/{user}', [UserController::class, 'update'])->name('update');
    Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    
    // User Status & Access
    Route::post('/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
    Route::post('/{user}/activate', [UserController::class, 'activate'])->name('activate');
    Route::post('/{user}/deactivate', [UserController::class, 'deactivate'])->name('deactivate');
    Route::post('/{user}/suspend', [UserController::class, 'suspend'])->name('suspend');
    Route::post('/{user}/unsuspend', [UserController::class, 'unsuspend'])->name('unsuspend');
    Route::post('/{user}/reset-password', [UserController::class, 'resetPassword'])->name('reset-password');
    Route::post('/{user}/force-logout', [UserController::class, 'forceLogout'])->name('force-logout');
    Route::post('/{user}/unlock', [UserController::class, 'unlockAccount'])->name('unlock');
    
    // Role & Permission Assignment
    Route::get('/{user}/roles', [UserController::class, 'manageRoles'])->name('roles');
    Route::post('/{user}/roles', [UserController::class, 'syncRoles'])->name('roles.sync');
    Route::post('/{user}/role/{role}', [UserController::class, 'assignRole'])->name('role.assign');
    Route::delete('/{user}/role/{role}', [UserController::class, 'removeRole'])->name('role.remove');
    Route::get('/{user}/permissions', [UserController::class, 'permissions'])->name('permissions');
    Route::post('/{user}/permissions', [UserController::class, 'syncPermissions'])->name('permissions.sync');
    
    // Organizational Assignment
    Route::post('/{user}/assign-department', [UserController::class, 'assignDepartment'])->name('assign-department');
    Route::post('/{user}/assign-college', [UserController::class, 'assignCollege'])->name('assign-college');
    Route::post('/{user}/assign-school', [UserController::class, 'assignSchool'])->name('assign-school');
    Route::delete('/{user}/remove-org-assignment/{assignment}', [UserController::class, 'removeOrgAssignment'])->name('remove-org');
    
    // Activity & Audit
    Route::get('/{user}/activity', [UserController::class, 'activityLog'])->name('activity');
    Route::get('/{user}/login-history', [UserController::class, 'loginHistory'])->name('login-history');
    Route::get('/{user}/audit-trail', [UserController::class, 'auditTrail'])->name('audit-trail');
    Route::get('/{user}/sessions', [UserController::class, 'activeSessions'])->name('sessions');
    Route::post('/{user}/sessions/terminate', [UserController::class, 'terminateSessions'])->name('sessions.terminate');
    
    // Bulk Operations
    Route::post('/bulk-action', [UserController::class, 'bulkAction'])->name('bulk-action');
    Route::post('/bulk-import', [UserController::class, 'bulkImport'])->name('bulk-import');
    Route::get('/export', [UserController::class, 'export'])->name('export');
    Route::get('/import-template', [UserController::class, 'downloadImportTemplate'])->name('import-template');
    
    // User Impersonation (Super Admin only)
    Route::middleware('role:super-administrator')->group(function () {
        Route::post('/{user}/impersonate', [UserController::class, 'impersonate'])->name('impersonate');
        Route::post('/stop-impersonation', [UserController::class, 'stopImpersonation'])->name('stop-impersonation');
    });
});

// ============================================================
// ROLE MANAGEMENT
// ============================================================
Route::prefix('roles')->name('roles.')->group(function () {
    Route::get('/', [RoleController::class, 'index'])->name('index');
    Route::get('/create', [RoleController::class, 'create'])->name('create');
    Route::post('/', [RoleController::class, 'store'])->name('store');
    Route::get('/{role}', [RoleController::class, 'show'])->name('show');
    Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');
    Route::put('/{role}', [RoleController::class, 'update'])->name('update');
    Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
    Route::get('/{role}/permissions', [RoleController::class, 'permissions'])->name('permissions');
    Route::post('/{role}/permissions', [RoleController::class, 'syncPermissions'])->name('permissions.sync');
    Route::get('/{role}/users', [RoleController::class, 'users'])->name('users');
});

// ============================================================
// PERMISSION MANAGEMENT
// ============================================================
Route::prefix('permissions')->name('permissions.')->group(function () {
    Route::get('/', [PermissionManagementController::class, 'index'])->name('index');
    Route::get('/matrix', [PermissionManagementController::class, 'matrix'])->name('matrix');
    Route::post('/sync', [PermissionManagementController::class, 'syncPermissions'])->name('sync');
    Route::get('/by-module', [PermissionManagementController::class, 'byModule'])->name('by-module');
    Route::post('/create', [PermissionManagementController::class, 'create'])->name('create');
    Route::delete('/{permission}', [PermissionManagementController::class, 'destroy'])->name('destroy');
});

// ============================================================
// STUDENT MANAGEMENT (Admin View)
// ============================================================
Route::prefix('students')->name('students.')->group(function () {
    Route::get('/', [StudentController::class, 'adminIndex'])->name('index');
    Route::get('/search', [StudentController::class, 'adminSearch'])->name('search');
    Route::get('/create', [StudentController::class, 'create'])->name('create');
    Route::post('/', [StudentController::class, 'store'])->name('store');
    Route::get('/{student}', [StudentController::class, 'show'])->name('show');
    Route::get('/{student}/edit', [StudentController::class, 'edit'])->name('edit');
    Route::put('/{student}', [StudentController::class, 'update'])->name('update');
    Route::delete('/{student}', [StudentController::class, 'destroy'])->name('destroy');
    
    // Student Status Management
    Route::post('/{student}/activate', [StudentController::class, 'activate'])->name('activate');
    Route::post('/{student}/deactivate', [StudentController::class, 'deactivate'])->name('deactivate');
    Route::post('/{student}/suspend', [StudentController::class, 'suspend'])->name('suspend');
    Route::post('/{student}/graduate', [StudentController::class, 'graduate'])->name('graduate');
    
    // Import/Export
    Route::get('/import', [StudentController::class, 'importForm'])->name('import');
    Route::post('/import', [StudentController::class, 'import'])->name('import.process');
    Route::get('/export', [StudentController::class, 'export'])->name('export');
});

// ============================================================
// FACULTY MANAGEMENT (Admin View)
// ============================================================
Route::prefix('faculty')->name('faculty.')->group(function () {
    Route::get('/', [FacultyController::class, 'adminIndex'])->name('index');
    Route::get('/create', [FacultyController::class, 'create'])->name('create');
    Route::post('/', [FacultyController::class, 'store'])->name('store');
    Route::get('/{faculty}', [FacultyController::class, 'show'])->name('show');
    Route::get('/{faculty}/edit', [FacultyController::class, 'edit'])->name('edit');
    Route::put('/{faculty}', [FacultyController::class, 'update'])->name('update');
    Route::delete('/{faculty}', [FacultyController::class, 'destroy'])->name('destroy');
    
    // Assignment Management
    Route::get('/{faculty}/assignments', [FacultyController::class, 'assignments'])->name('assignments');
    Route::post('/{faculty}/assign-course', [FacultyController::class, 'assignCourse'])->name('assign-course');
    Route::delete('/{faculty}/remove-course/{course}', [FacultyController::class, 'removeCourse'])->name('remove-course');
});

// ============================================================
// COURSE MANAGEMENT (Admin)
// ============================================================
Route::prefix('courses')->name('courses.')->group(function () {
    Route::get('/', [CourseController::class, 'adminIndex'])->name('admin.index');
    Route::get('/create', [CourseController::class, 'create'])->name('admin.create');
    Route::post('/', [CourseController::class, 'store'])->name('admin.store');
    Route::get('/{course}', [CourseController::class, 'show'])->name('admin.show');
    Route::get('/{course}/edit', [CourseController::class, 'edit'])->name('admin.edit');
    Route::put('/{course}', [CourseController::class, 'update'])->name('admin.update');
    Route::delete('/{course}', [CourseController::class, 'destroy'])->name('admin.destroy');
    
    // Section Management
    Route::get('/{course}/sections', [CourseController::class, 'sections'])->name('admin.sections');
    Route::post('/{course}/sections', [CourseController::class, 'createSection'])->name('admin.sections.create');
    Route::put('/sections/{section}', [CourseController::class, 'updateSection'])->name('admin.sections.update');
    Route::delete('/sections/{section}', [CourseController::class, 'deleteSection'])->name('admin.sections.delete');
});

// ============================================================
// FINANCIAL ADMINISTRATION
// ============================================================
Route::prefix('financial')->name('financial.')->group(function () {
    Route::get('/', [FinancialController::class, 'adminDashboard'])->name('admin.dashboard');
    Route::get('/accounts', [FinancialController::class, 'accounts'])->name('admin.accounts');
    Route::get('/billing', [FinancialController::class, 'billing'])->name('admin.billing');
    Route::get('/aid', [FinancialController::class, 'financialAid'])->name('admin.aid');
    Route::get('/reports', [FinancialController::class, 'reports'])->name('reports.index');
    Route::get('/transactions', [FinancialController::class, 'transactions'])->name('admin.transactions');
    Route::get('/fee-structure', [FinancialController::class, 'feeStructure'])->name('admin.fee-structure');
    Route::post('/fee-structure', [FinancialController::class, 'updateFeeStructure'])->name('admin.fee-structure.update');
});

// ============================================================
// ADMISSIONS ADMINISTRATION
// ============================================================
/*
Route::prefix('admissions')->name('admissions.')->group(function () {
    Route::get('/', [AdminApplicationController::class, 'dashboard'])->name('dashboard');
    Route::get('/applications', [AdminApplicationController::class, 'index'])->name('applications.index');
    Route::get('/applications/pending', [AdminApplicationController::class, 'pending'])->name('applications.pending');
    Route::get('/applications/reviewing', [AdminApplicationController::class, 'reviewing'])->name('applications.reviewing');
    Route::get('/applications/{application}', [AdminApplicationController::class, 'show'])->name('applications.show');
    Route::post('/applications/{application}/review', [AdminApplicationController::class, 'review'])->name('applications.review');
    Route::post('/applications/{application}/decide', [AdminApplicationController::class, 'decide'])->name('applications.decide');
    Route::get('/decisions', [AdminApplicationController::class, 'decisions'])->name('decisions.index');
    Route::get('/statistics', [AdminApplicationController::class, 'statistics'])->name('statistics');
    Route::get('/calendar', [AdminApplicationController::class, 'calendar'])->name('calendar');
});
*/

// ============================================================
// GRADE MANAGEMENT (Admin Side)
// ============================================================
Route::prefix('grades')->name('grades.')->group(function () {
    Route::get('/', [GradeApprovalController::class, 'adminIndex'])->name('index');
    Route::get('/approvals', [GradeApprovalController::class, 'pendingApprovals'])->name('approvals');
    Route::get('/changes', [GradeApprovalController::class, 'gradeChanges'])->name('changes');
    Route::get('/deadlines', [GradeDeadlineController::class, 'index'])->name('deadlines');
    Route::post('/deadlines', [GradeDeadlineController::class, 'update'])->name('deadlines.update');
    Route::get('/scales', [GradeScaleController::class, 'index'])->name('scales');
    Route::post('/scales', [GradeScaleController::class, 'update'])->name('scales.update');
    
    // Grade Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/deans-list', [GradeReportController::class, 'deansList'])->name('deans-list');
        Route::get('/gpa', [GradeReportController::class, 'gpaReport'])->name('gpa');
        Route::get('/probation', [GradeReportController::class, 'probationList'])->name('probation');
        Route::get('/statistics', [GradeReportController::class, 'statistics'])->name('statistics');
    });
});

// ============================================================
// REPORTS & ANALYTICS
// ============================================================
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');
    Route::get('/dashboard', [ReportController::class, 'dashboard'])->name('dashboard');
    Route::get('/enrollment', [ReportController::class, 'enrollment'])->name('enrollment');
    Route::get('/academic', [ReportController::class, 'academic'])->name('academic');
    Route::get('/financial', [ReportController::class, 'financial'])->name('financial');
    Route::get('/custom', [ReportController::class, 'custom'])->name('custom');
    Route::post('/generate', [ReportController::class, 'generate'])->name('generate');
    Route::get('/export/{report}', [ReportController::class, 'export'])->name('export');
});

// ============================================================
// SYSTEM CONFIGURATION (Admin Side)
// ============================================================
Route::prefix('system')->name('system.')->group(function () {
    Route::get('/settings', [SystemConfigurationController::class, 'settings'])->name('settings');
    Route::post('/settings', [SystemConfigurationController::class, 'updateSettings'])->name('settings.update');
    Route::get('/modules', [SystemConfigurationController::class, 'modules'])->name('modules');
    Route::post('/modules/toggle', [SystemConfigurationController::class, 'toggleModule'])->name('modules.toggle');
    Route::get('/integrations', [SystemConfigurationController::class, 'integrations'])->name('integrations');
    Route::get('/backups', [SystemConfigurationController::class, 'backups'])->name('backups');
    Route::post('/backups/create', [SystemConfigurationController::class, 'createBackup'])->name('backups.create');
});

// ============================================================
// STUDENT HELPER TOOLS
// ============================================================
Route::prefix('student-helper')->name('student-helper.')->group(function () {
    Route::get('/', [AdminStudentHelperController::class, 'index'])->name('index');
    Route::get('/dashboard', [AdminStudentHelperController::class, 'dashboard'])->name('dashboard');
    Route::post('/impersonate/{student}', [AdminStudentHelperController::class, 'impersonate'])->name('impersonate');
    Route::post('/stop-impersonation', [AdminStudentHelperController::class, 'stopImpersonation'])->name('stop-impersonation');
    Route::get('/student/{student}/overview', [AdminStudentHelperController::class, 'studentOverview'])->name('overview');
    Route::post('/student/{student}/reset-password', [AdminStudentHelperController::class, 'resetPassword'])->name('reset-password');
    Route::post('/student/{student}/clear-holds', [AdminStudentHelperController::class, 'clearHolds'])->name('clear-holds');
});