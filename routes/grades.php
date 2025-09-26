<?php

/**
 * GRADE MANAGEMENT ROUTES
 * 
 * This file contains all grade-related routes for faculty, students, and administrators.
 * Routes are organized by user role and functionality.
 */

use App\Http\Controllers\GradeController;
use App\Http\Controllers\StudentGradeController;
use App\Http\Controllers\FacultyGradeController;
use App\Http\Controllers\GradeApprovalController;
use App\Http\Controllers\GradeReportController;
use App\Http\Controllers\GradeStatisticsController;
use App\Http\Controllers\GradeComponentController;
use App\Http\Controllers\GradeChangeController;
use Illuminate\Support\Facades\Route;

// ============================================================
// FACULTY GRADE MANAGEMENT
// ============================================================
Route::middleware(['auth', 'role:faculty,instructor'])->group(function () {
    
    // Main grade management dashboard
    Route::get('/', [GradeController::class, 'index'])->name('index');
    Route::get('/my-sections', [GradeController::class, 'mySections'])->name('my-sections');
    
    // Grade Entry
    Route::prefix('entry')->name('entry')->group(function () {
        Route::get('/', [GradeController::class, 'entryDashboard']);
        Route::get('/section/{section}', [GradeController::class, 'sectionGrades'])->name('.section');
        Route::post('/section/{section}', [GradeController::class, 'saveGrades'])->name('.save');
        Route::post('/section/{section}/submit', [GradeController::class, 'submitGrades'])->name('.submit');
        Route::post('/quick-save', [GradeController::class, 'quickSave'])->name('.quick');
        Route::get('/import/{section}', [GradeController::class, 'importForm'])->name('.import');
        Route::post('/import/{section}', [GradeController::class, 'importGrades'])->name('.import.process');
        Route::get('/export/{section}', [GradeController::class, 'exportGrades'])->name('.export');
    });
    
    // Grade Components Management
    Route::prefix('components')->name('components')->group(function () {
        Route::get('/', [GradeComponentController::class, 'index']);
        Route::get('/section/{section}', [GradeComponentController::class, 'sectionComponents'])->name('.section');
        Route::post('/section/{section}', [GradeComponentController::class, 'saveComponents'])->name('.save');
        Route::post('/add', [GradeComponentController::class, 'addComponent'])->name('.add');
        Route::put('/update/{component}', [GradeComponentController::class, 'updateComponent'])->name('.update');
        Route::delete('/delete/{component}', [GradeComponentController::class, 'deleteComponent'])->name('.delete');
        Route::post('/calculate/{section}', [GradeComponentController::class, 'calculateGrades'])->name('.calculate');
    });
    
    // Grade Statistics
    Route::prefix('statistics')->name('statistics')->group(function () {
        Route::get('/', [GradeStatisticsController::class, 'index']);
        Route::get('/overview', [GradeStatisticsController::class, 'overview'])->name('.overview'); // FIXED: Added missing route
        Route::get('/section/{section}', [GradeStatisticsController::class, 'sectionStats'])->name('.section');
        Route::get('/distribution/{section}', [GradeStatisticsController::class, 'gradeDistribution'])->name('.distribution');
        Route::get('/comparison', [GradeStatisticsController::class, 'comparison'])->name('.comparison');
        Route::get('/historical', [GradeStatisticsController::class, 'historical'])->name('.historical');
        Route::get('/export', [GradeStatisticsController::class, 'export'])->name('.export');
    });
    
    // Grade Preview & Submission
    Route::prefix('preview')->name('preview')->group(function () {
        Route::get('/section/{section}', [GradeController::class, 'previewGrades'])->name('.section');
        Route::post('/verify/{section}', [GradeController::class, 'verifyGrades'])->name('.verify');
        Route::get('/print/{section}', [GradeController::class, 'printPreview'])->name('.print');
    });
    
    // Grade History & Changes
    Route::prefix('history')->name('history')->group(function () {
        Route::get('/', [GradeController::class, 'history']);
        Route::get('/section/{section}', [GradeController::class, 'sectionHistory'])->name('.section');
        Route::get('/student/{student}', [GradeController::class, 'studentHistory'])->name('.student');
        Route::get('/changes', [GradeController::class, 'changeLog'])->name('.changes');
    });
    
    // Grade Change Requests (Faculty Side)
    Route::prefix('change-request')->name('change-request')->group(function () {
        Route::get('/', [GradeChangeController::class, 'index']);
        Route::get('/create', [GradeChangeController::class, 'create'])->name('.create');
        Route::post('/submit', [GradeChangeController::class, 'submit'])->name('.submit');
        Route::get('/status/{request}', [GradeChangeController::class, 'status'])->name('.status');
        Route::post('/withdraw/{request}', [GradeChangeController::class, 'withdraw'])->name('.withdraw');
    });
});

// ============================================================
// STUDENT GRADE ACCESS
// ============================================================
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/', [StudentGradeController::class, 'index'])->name('index');
    Route::get('/current', [StudentGradeController::class, 'currentGrades'])->name('current');
    Route::get('/history', [StudentGradeController::class, 'gradeHistory'])->name('history');
    Route::get('/term/{term}', [StudentGradeController::class, 'termGrades'])->name('term');
    Route::get('/course/{enrollment}', [StudentGradeController::class, 'courseDetails'])->name('course');
    Route::get('/transcript', [StudentGradeController::class, 'unofficialTranscript'])->name('transcript');
    Route::get('/gpa', [StudentGradeController::class, 'gpaDetails'])->name('gpa');
    Route::get('/standing', [StudentGradeController::class, 'academicStanding'])->name('standing');
    Route::get('/print', [StudentGradeController::class, 'printGrades'])->name('print');
});

// ============================================================
// GRADE REPORTS (Multiple Roles)
// ============================================================
Route::middleware(['auth', 'role:faculty,department-head,dean,registrar,admin'])->prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [GradeReportController::class, 'index'])->name('index');
    Route::get('/class-roster/{section}', [GradeReportController::class, 'classRoster'])->name('class-roster');
    Route::get('/grade-sheet/{section}', [GradeReportController::class, 'gradeSheet'])->name('grade-sheet');
    Route::get('/final-grades/{section}', [GradeReportController::class, 'finalGrades'])->name('final-grades');
    Route::get('/deans-list/{term?}', [GradeReportController::class, 'deansList'])->name('deans-list');
    Route::get('/probation-list/{term?}', [GradeReportController::class, 'probationList'])->name('probation-list');
    Route::get('/gpa-report', [GradeReportController::class, 'gpaReport'])->name('gpa');
    Route::get('/custom', [GradeReportController::class, 'customReport'])->name('custom');
    Route::post('/generate', [GradeReportController::class, 'generateReport'])->name('generate');
    Route::get('/export/{report}', [GradeReportController::class, 'exportReport'])->name('export');
});

// ============================================================
// GRADE APPROVAL WORKFLOW (Registrar/Admin)
// ============================================================
Route::middleware(['auth', 'role:registrar,admin'])->prefix('approval')->name('approval.')->group(function () {
    Route::get('/', [GradeApprovalController::class, 'index'])->name('index');
    Route::get('/pending', [GradeApprovalController::class, 'pendingApprovals'])->name('pending');
    Route::get('/submission/{submission}', [GradeApprovalController::class, 'reviewSubmission'])->name('review');
    Route::post('/submission/{submission}/approve', [GradeApprovalController::class, 'approveSubmission'])->name('approve');
    Route::post('/submission/{submission}/reject', [GradeApprovalController::class, 'rejectSubmission'])->name('reject');
    Route::post('/submission/{submission}/return', [GradeApprovalController::class, 'returnSubmission'])->name('return');
    Route::post('/bulk-approve', [GradeApprovalController::class, 'bulkApprove'])->name('bulk-approve');
    Route::get('/history', [GradeApprovalController::class, 'approvalHistory'])->name('history');
});

// ============================================================
// GRADE SCALES & POLICIES (Admin Only)
// ============================================================
Route::middleware(['auth', 'role:admin,registrar'])->prefix('admin')->name('admin.')->group(function () {
    // Grade Scale Management
    Route::prefix('scales')->name('scales')->group(function () {
        Route::get('/', [GradeController::class, 'gradeScales']);
        Route::post('/create', [GradeController::class, 'createScale'])->name('.create');
        Route::put('/update/{scale}', [GradeController::class, 'updateScale'])->name('.update');
        Route::delete('/delete/{scale}', [GradeController::class, 'deleteScale'])->name('.delete');
        Route::post('/set-default/{scale}', [GradeController::class, 'setDefaultScale'])->name('.default');
    });
    
    // Grade Deadlines
    Route::prefix('deadlines')->name('deadlines')->group(function () {
        Route::get('/', [GradeController::class, 'gradeDeadlines']);
        Route::post('/set', [GradeController::class, 'setDeadlines'])->name('.set');
        Route::post('/extend/{term}', [GradeController::class, 'extendDeadline'])->name('.extend');
        Route::post('/notify', [GradeController::class, 'sendDeadlineNotifications'])->name('.notify');
    });
    
    // Grade Reports for Dean's List
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/deans-list', [GradeReportController::class, 'deansListReport'])->name('deans-list');
        Route::get('/gpa', [GradeReportController::class, 'gpaAnalysisReport'])->name('gpa');
    });
});

// ============================================================
// GRADE CHANGE MANAGEMENT (Registrar/Admin)
// ============================================================
Route::middleware(['auth', 'role:registrar,admin'])->prefix('changes')->name('changes.')->group(function () {
    Route::get('/', [GradeChangeController::class, 'registrarIndex'])->name('index');
    Route::get('/pending', [GradeChangeController::class, 'pendingChanges'])->name('pending');
    Route::get('/request/{request}', [GradeChangeController::class, 'reviewRequest'])->name('review');
    Route::post('/approve/{request}', [GradeChangeController::class, 'approveRequest'])->name('approve');
    Route::post('/deny/{request}', [GradeChangeController::class, 'denyRequest'])->name('deny');
    Route::post('/administrative', [GradeChangeController::class, 'administrativeChange'])->name('administrative');
    Route::get('/history', [GradeChangeController::class, 'changeHistory'])->name('history');
    Route::get('/audit-trail', [GradeChangeController::class, 'auditTrail'])->name('audit');
});

// ============================================================
// API ROUTES FOR GRADE CALCULATIONS
// ============================================================
Route::middleware(['auth'])->prefix('api')->name('api.')->group(function () {
    Route::post('/calculate-gpa', [GradeController::class, 'calculateGPA'])->name('calculate-gpa');
    Route::post('/calculate-standing', [GradeController::class, 'calculateStanding'])->name('calculate-standing');
    Route::get('/grade-points/{grade}', [GradeController::class, 'getGradePoints'])->name('grade-points');
    Route::post('/validate-grades', [GradeController::class, 'validateGrades'])->name('validate');
});