<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\AdmissionsController;
use App\Http\Controllers\PublicAdmissionsController;
use App\Http\Controllers\RegistrationOverrideController;
use App\Http\Controllers\AdvisorOverrideController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// =====================================
// DOCUMENT MANAGEMENT API ROUTES (UNIFIED SYSTEM)
// =====================================
Route::prefix('documents')->group(function () {
    // Public routes (for testing - add auth middleware in production)
    Route::post('upload', [DocumentController::class, 'upload']);
    Route::get('list', [DocumentController::class, 'list']);
    
    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('{id}', [DocumentController::class, 'show']);
        Route::get('{id}/download', [DocumentController::class, 'download']);
        Route::post('{id}/verify', [DocumentController::class, 'verify']);
        Route::put('{id}/metadata', [DocumentController::class, 'updateMetadata']);
        Route::delete('{id}', [DocumentController::class, 'delete']);
        Route::get('search', [DocumentController::class, 'search']);
        Route::post('{id}/version', [DocumentController::class, 'createVersion']);
    });
});

// =====================================
// ADMISSIONS API ROUTES
// =====================================

// Public admission routes (no auth required)
Route::prefix('admissions/public')->group(function () {
    Route::get('requirements', [PublicAdmissionsController::class, 'requirements']);
    Route::get('programs', [PublicAdmissionsController::class, 'programs']);
    Route::get('calendar', [PublicAdmissionsController::class, 'calendar']);
    Route::get('faq', [PublicAdmissionsController::class, 'faq']);
    Route::get('contact', [PublicAdmissionsController::class, 'contact']);
    Route::get('statistics', [PublicAdmissionsController::class, 'statistics']);
    Route::get('virtual-tour', [PublicAdmissionsController::class, 'virtualTour']);
});

// Admin admission routes (requires authentication)
Route::prefix('admissions')->middleware('auth:sanctum')->group(function () {
    // Application management
    Route::get('/', [AdmissionsController::class, 'index']);
    Route::get('dashboard', [AdmissionsController::class, 'dashboard']);
    Route::get('export', [AdmissionsController::class, 'export']);
    Route::get('{id}', [AdmissionsController::class, 'show']);
    Route::post('{id}/review', [AdmissionsController::class, 'review']);
    Route::post('{id}/decision', [AdmissionsController::class, 'decision']);
    Route::post('{id}/communication', [AdmissionsController::class, 'sendCommunication']);
    Route::post('{id}/interview', [AdmissionsController::class, 'scheduleInterview']);
    
    // Document management using unified system
    Route::prefix('{application}/documents')->group(function () {
        Route::get('/', [AdmissionsController::class, 'getDocuments']);
        Route::post('upload', [AdmissionsController::class, 'uploadDocument']);
        Route::get('{document}/download', [AdmissionsController::class, 'downloadDocument']);
        Route::post('{document}/verify', [AdmissionsController::class, 'verifyDocument']);
    });
    
    // Settings management (admin only)
    Route::middleware('role:admissions-admin')->group(function () {
        Route::get('settings', [AdmissionsController::class, 'settings']);
        Route::put('settings', [AdmissionsController::class, 'updateSettings']);
    });
});

// =====================================
// REGISTRATION OVERRIDE API ROUTES
// =====================================
Route::middleware('auth:sanctum')->group(function () {
    // Student API endpoints
    Route::prefix('registration')->group(function () {
        // Override requests
        Route::post('/override-request', [RegistrationOverrideController::class, 'create']);
        Route::get('/override-requests', [RegistrationOverrideController::class, 'myRequests']);
        Route::get('/override-requests/{id}', [RegistrationOverrideController::class, 'show']);
        
        // Use override code during registration
        Route::post('/use-override', [RegistrationOverrideController::class, 'useOverride']);
        
        // Check available overrides
        Route::get('/check-overrides', [RegistrationOverrideController::class, 'checkOverrides']);
    });
    
    // Approver API endpoints
    Route::prefix('override-management')->middleware(['role:advisor,department-head,registrar,academic-administrator'])->group(function () {
        Route::get('/pending', [AdvisorOverrideController::class, 'index']);
        Route::post('/{id}/approve', [AdvisorOverrideController::class, 'approve']);
        Route::post('/{id}/deny', [AdvisorOverrideController::class, 'deny']);
        Route::post('/bulk-process', [AdvisorOverrideController::class, 'bulkProcess']);
        
        // Get specific request details for review
        Route::get('/{id}/details', function ($id) {
            $request = \App\Models\RegistrationOverrideRequest::with([
                'student.user',
                'student.program',
                'course',
                'section.course',
                'section.instructor',
                'term'
            ])->findOrFail($id);
            
            // Add additional context for decision-making
            $context = [
                'student_gpa' => $request->student->cumulative_gpa,
                'student_credits_completed' => $request->student->credits_completed,
                'student_academic_standing' => $request->student->academic_standing,
                'is_graduating_senior' => $request->is_graduating_senior,
                'previous_overrides' => \App\Models\RegistrationOverrideRequest::where('student_id', $request->student_id)
                    ->where('id', '!=', $request->id)
                    ->where('status', 'approved')
                    ->count()
            ];
            
            return response()->json([
                'success' => true,
                'request' => $request,
                'context' => $context
            ]);
        });
    });
});

// =====================================
// FUTURE MODULE API ROUTES (Placeholders)
// =====================================

// Student Information Module
Route::prefix('students')->middleware('auth:sanctum')->group(function () {
    // Documents will use unified system
    // Route::post('{student}/documents/upload', [StudentController::class, 'uploadDocument']);
    // Route::get('{student}/documents', [StudentController::class, 'getDocuments']);
});

// Faculty Portal Module
Route::prefix('faculty')->middleware('auth:sanctum')->group(function () {
    // Documents will use unified system
    // Route::post('{faculty}/documents/upload', [FacultyController::class, 'uploadDocument']);
    // Route::get('{faculty}/documents', [FacultyController::class, 'getDocuments']);
});

// LMS Module
Route::prefix('lms')->middleware('auth:sanctum')->group(function () {
    // Course materials will use unified system
    // Route::post('courses/{course}/materials/upload', [LMSController::class, 'uploadMaterial']);
    // Route::get('courses/{course}/materials', [LMSController::class, 'getMaterials']);
});

// Transcript Module
Route::prefix('transcripts')->middleware('auth:sanctum')->group(function () {
    // Transcript documents will use unified system
    // Route::post('{request}/documents/upload', [TranscriptController::class, 'uploadDocument']);
    // Route::get('{request}/documents', [TranscriptController::class, 'getDocuments']);
});

// Financial Aid Module
Route::prefix('financial-aid')->middleware('auth:sanctum')->group(function () {
    // Financial documents will use unified system
    // Route::post('applications/{application}/documents/upload', [FinancialAidController::class, 'uploadDocument']);
    // Route::get('applications/{application}/documents', [FinancialAidController::class, 'getDocuments']);
});

// HR Module  
Route::prefix('hr')->middleware('auth:sanctum')->group(function () {
    // Employment documents will use unified system
    // Route::post('employees/{employee}/documents/upload', [HRController::class, 'uploadDocument']);
    // Route::get('employees/{employee}/documents', [HRController::class, 'getDocuments']);
});