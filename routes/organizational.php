<?php
/**
 * IntelliCampus Organizational Structure Routes
 * 
 * Routes for managing the institutional hierarchy: colleges, schools, departments, divisions.
 * These routes are automatically prefixed with 'organization' and named with 'organization.'
 * Base middleware: 'web', 'auth'
 */

use App\Http\Controllers\OrganizationalStructureController;
use App\Http\Controllers\CollegeManagementController;
use App\Http\Controllers\SchoolManagementController;
use App\Http\Controllers\DepartmentManagementController;
use Illuminate\Support\Facades\Route;

// ============================================================
// ORGANIZATIONAL OVERVIEW
// ============================================================
Route::get('/', [OrganizationalStructureController::class, 'index'])->name('index');
Route::get('/dashboard', [OrganizationalStructureController::class, 'dashboard'])->name('dashboard');
Route::get('/hierarchy', [OrganizationalStructureController::class, 'hierarchyView'])->name('hierarchy');
Route::get('/chart', [OrganizationalStructureController::class, 'organizationalChart'])->name('chart');
Route::get('/directory', [OrganizationalStructureController::class, 'directory'])->name('directory');
Route::get('/statistics', [OrganizationalStructureController::class, 'statistics'])->name('statistics');
Route::get('/search', [OrganizationalStructureController::class, 'search'])->name('search');

// ============================================================
// COLLEGES MANAGEMENT
// ============================================================
Route::prefix('colleges')->name('colleges.')->group(function () {
    Route::get('/', [CollegeManagementController::class, 'index'])->name('index');
    Route::get('/grid', [CollegeManagementController::class, 'gridView'])->name('grid');
    Route::get('/list', [CollegeManagementController::class, 'listView'])->name('list');
    
    // CRUD Operations (Admin/Dean level)
    Route::middleware(['role:admin,provost,president'])->group(function () {
        Route::get('/create', [CollegeManagementController::class, 'create'])->name('create');
        Route::post('/', [CollegeManagementController::class, 'store'])->name('store');
        Route::get('/{college}/edit', [CollegeManagementController::class, 'edit'])->name('edit');
        Route::put('/{college}', [CollegeManagementController::class, 'update'])->name('update');
        Route::delete('/{college}', [CollegeManagementController::class, 'destroy'])->name('destroy');
        Route::post('/{college}/activate', [CollegeManagementController::class, 'activate'])->name('activate');
        Route::post('/{college}/deactivate', [CollegeManagementController::class, 'deactivate'])->name('deactivate');
    });
    
    // View Operations
    Route::get('/{college}', [CollegeManagementController::class, 'show'])->name('show');
    Route::get('/{college}/overview', [CollegeManagementController::class, 'overview'])->name('overview');
    Route::get('/{college}/structure', [CollegeManagementController::class, 'structure'])->name('structure');
    
    // Leadership Management
    Route::middleware(['role:admin,provost,president'])->group(function () {
        Route::get('/{college}/leadership', [CollegeManagementController::class, 'leadership'])->name('leadership');
        Route::post('/{college}/dean', [CollegeManagementController::class, 'assignDean'])->name('dean.assign');
        Route::delete('/{college}/dean', [CollegeManagementController::class, 'removeDean'])->name('dean.remove');
        Route::post('/{college}/associate-dean', [CollegeManagementController::class, 'assignAssociateDean'])->name('associate-dean.assign');
        Route::delete('/{college}/associate-dean/{id}', [CollegeManagementController::class, 'removeAssociateDean'])->name('associate-dean.remove');
        Route::post('/{college}/assistant-dean', [CollegeManagementController::class, 'assignAssistantDean'])->name('assistant-dean.assign');
        Route::delete('/{college}/assistant-dean/{id}', [CollegeManagementController::class, 'removeAssistantDean'])->name('assistant-dean.remove');
    });
    
    // Departments & Schools
    Route::get('/{college}/departments', [CollegeManagementController::class, 'departments'])->name('departments');
    Route::get('/{college}/schools', [CollegeManagementController::class, 'schools'])->name('schools');
    Route::post('/{college}/department', [CollegeManagementController::class, 'addDepartment'])->name('department.add')
        ->middleware('role:dean,admin');
    Route::post('/{college}/school', [CollegeManagementController::class, 'addSchool'])->name('school.add')
        ->middleware('role:dean,admin');
    
    // Programs & Courses
    Route::get('/{college}/programs', [CollegeManagementController::class, 'programs'])->name('programs');
    Route::get('/{college}/courses', [CollegeManagementController::class, 'courses'])->name('courses');
    Route::get('/{college}/curriculum', [CollegeManagementController::class, 'curriculum'])->name('curriculum');
    
    // Faculty & Students
    Route::get('/{college}/faculty', [CollegeManagementController::class, 'faculty'])->name('faculty');
    Route::get('/{college}/students', [CollegeManagementController::class, 'students'])->name('students');
    Route::get('/{college}/enrollment', [CollegeManagementController::class, 'enrollment'])->name('enrollment');
    
    // Statistics & Reports
    Route::get('/{college}/statistics', [CollegeManagementController::class, 'statistics'])->name('statistics');
    Route::get('/{college}/reports', [CollegeManagementController::class, 'reports'])->name('reports');
    Route::get('/{college}/metrics', [CollegeManagementController::class, 'metrics'])->name('metrics');
    Route::get('/{college}/performance', [CollegeManagementController::class, 'performance'])->name('performance');
    
    // Budget & Resources
    Route::middleware(['role:dean,admin,financial-admin'])->group(function () {
        Route::get('/{college}/budget', [CollegeManagementController::class, 'budget'])->name('budget');
        Route::get('/{college}/resources', [CollegeManagementController::class, 'resources'])->name('resources');
        Route::get('/{college}/facilities', [CollegeManagementController::class, 'facilities'])->name('facilities');
    });
});

// ============================================================
// SCHOOLS MANAGEMENT
// ============================================================
Route::prefix('schools')->name('schools.')->group(function () {
    Route::get('/', [SchoolManagementController::class, 'index'])->name('index');
    Route::get('/all', [SchoolManagementController::class, 'allSchools'])->name('all');
    
    // CRUD Operations
    Route::middleware(['role:dean,admin'])->group(function () {
        Route::get('/create', [SchoolManagementController::class, 'create'])->name('create');
        Route::post('/', [SchoolManagementController::class, 'store'])->name('store');
        Route::get('/{school}/edit', [SchoolManagementController::class, 'edit'])->name('edit');
        Route::put('/{school}', [SchoolManagementController::class, 'update'])->name('update');
        Route::delete('/{school}', [SchoolManagementController::class, 'destroy'])->name('destroy');
    });
    
    // View Operations
    Route::get('/{school}', [SchoolManagementController::class, 'show'])->name('show');
    Route::get('/{school}/overview', [SchoolManagementController::class, 'overview'])->name('overview');
    Route::get('/{school}/structure', [SchoolManagementController::class, 'structure'])->name('structure');
    
    // Leadership
    Route::middleware(['role:dean,admin'])->group(function () {
        Route::get('/{school}/leadership', [SchoolManagementController::class, 'leadership'])->name('leadership');
        Route::post('/{school}/director', [SchoolManagementController::class, 'assignDirector'])->name('director.assign');
        Route::delete('/{school}/director', [SchoolManagementController::class, 'removeDirector'])->name('director.remove');
        Route::post('/{school}/associate-director', [SchoolManagementController::class, 'assignAssociateDirector'])->name('associate-director.assign');
        Route::delete('/{school}/associate-director/{id}', [SchoolManagementController::class, 'removeAssociateDirector'])->name('associate-director.remove');
    });
    
    // Departments
    Route::get('/{school}/departments', [SchoolManagementController::class, 'departments'])->name('departments');
    Route::post('/{school}/department', [SchoolManagementController::class, 'addDepartment'])->name('department.add')
        ->middleware('role:school-director,dean,admin');
    Route::delete('/{school}/department/{department}', [SchoolManagementController::class, 'removeDepartment'])->name('department.remove')
        ->middleware('role:school-director,dean,admin');
    
    // Academic Information
    Route::get('/{school}/programs', [SchoolManagementController::class, 'programs'])->name('programs');
    Route::get('/{school}/faculty', [SchoolManagementController::class, 'faculty'])->name('faculty');
    Route::get('/{school}/students', [SchoolManagementController::class, 'students'])->name('students');
    Route::get('/{school}/courses', [SchoolManagementController::class, 'courses'])->name('courses');
    
    // Statistics
    Route::get('/{school}/statistics', [SchoolManagementController::class, 'statistics'])->name('statistics');
    Route::get('/{school}/performance', [SchoolManagementController::class, 'performance'])->name('performance');
});

// ============================================================
// DEPARTMENTS MANAGEMENT
// ============================================================
Route::prefix('departments')->name('departments.')->group(function () {
    Route::get('/', [DepartmentManagementController::class, 'index'])->name('index');
    Route::get('/all', [DepartmentManagementController::class, 'allDepartments'])->name('all');
    Route::get('/search', [DepartmentManagementController::class, 'search'])->name('search');
    
    // CRUD Operations
    Route::middleware(['role:dean,school-director,admin'])->group(function () {
        Route::get('/create', [DepartmentManagementController::class, 'create'])->name('create');
        Route::post('/', [DepartmentManagementController::class, 'store'])->name('store');
        Route::get('/{department}/edit', [DepartmentManagementController::class, 'edit'])->name('edit');
        Route::put('/{department}', [DepartmentManagementController::class, 'update'])->name('update');
        Route::delete('/{department}', [DepartmentManagementController::class, 'destroy'])->name('destroy');
        Route::post('/{department}/merge', [DepartmentManagementController::class, 'merge'])->name('merge');
        Route::post('/{department}/split', [DepartmentManagementController::class, 'split'])->name('split');
    });
    
    // View Operations
    Route::get('/{department}', [DepartmentManagementController::class, 'show'])->name('show');
    Route::get('/{department}/overview', [DepartmentManagementController::class, 'overview'])->name('overview');
    Route::get('/{department}/profile', [DepartmentManagementController::class, 'profile'])->name('profile');
    
    // Leadership & Governance
    Route::middleware(['role:dean,school-director,admin'])->group(function () {
        Route::get('/{department}/leadership', [DepartmentManagementController::class, 'leadership'])->name('leadership');
        Route::post('/{department}/head', [DepartmentManagementController::class, 'assignHead'])->name('head.assign');
        Route::delete('/{department}/head', [DepartmentManagementController::class, 'removeHead'])->name('head.remove');
        Route::post('/{department}/deputy', [DepartmentManagementController::class, 'assignDeputy'])->name('deputy.assign');
        Route::delete('/{department}/deputy/{id}', [DepartmentManagementController::class, 'removeDeputy'])->name('deputy.remove');
        Route::post('/{department}/coordinator', [DepartmentManagementController::class, 'assignCoordinator'])->name('coordinator.assign');
        Route::delete('/{department}/coordinator/{id}', [DepartmentManagementController::class, 'removeCoordinator'])->name('coordinator.remove');
    });
    
    // Faculty Management
    Route::get('/{department}/faculty', [DepartmentManagementController::class, 'faculty'])->name('faculty');
    Route::middleware(['role:department-head,dean,admin'])->group(function () {
        Route::get('/{department}/faculty/manage', [DepartmentManagementController::class, 'manageFaculty'])->name('faculty.manage');
        Route::post('/{department}/faculty', [DepartmentManagementController::class, 'addFaculty'])->name('faculty.add');
        Route::delete('/{department}/faculty/{faculty}', [DepartmentManagementController::class, 'removeFaculty'])->name('faculty.remove');
        Route::post('/{department}/faculty/{faculty}/role', [DepartmentManagementController::class, 'assignFacultyRole'])->name('faculty.role');
        Route::get('/{department}/faculty/workload', [DepartmentManagementController::class, 'facultyWorkload'])->name('faculty.workload');
    });
    
    // Programs & Courses
    Route::get('/{department}/programs', [DepartmentManagementController::class, 'programs'])->name('programs');
    Route::get('/{department}/courses', [DepartmentManagementController::class, 'courses'])->name('courses');
    Route::middleware(['role:department-head,curriculum-committee,admin'])->group(function () {
        Route::post('/{department}/program', [DepartmentManagementController::class, 'addProgram'])->name('program.add');
        Route::delete('/{department}/program/{program}', [DepartmentManagementController::class, 'removeProgram'])->name('program.remove');
        Route::post('/{department}/course', [DepartmentManagementController::class, 'addCourse'])->name('course.add');
        Route::delete('/{department}/course/{course}', [DepartmentManagementController::class, 'removeCourse'])->name('course.remove');
    });
    
    // Students
    Route::get('/{department}/students', [DepartmentManagementController::class, 'students'])->name('students');
    Route::get('/{department}/majors', [DepartmentManagementController::class, 'majors'])->name('majors');
    Route::get('/{department}/minors', [DepartmentManagementController::class, 'minors'])->name('minors');
    Route::get('/{department}/graduates', [DepartmentManagementController::class, 'graduates'])->name('graduates');
    
    // Divisions (Sub-units within departments)
    Route::get('/{department}/divisions', [DepartmentManagementController::class, 'divisions'])->name('divisions');
    Route::middleware(['role:department-head,admin'])->group(function () {
        Route::post('/{department}/division', [DepartmentManagementController::class, 'createDivision'])->name('division.create');
        Route::put('/{department}/division/{division}', [DepartmentManagementController::class, 'updateDivision'])->name('division.update');
        Route::delete('/{department}/division/{division}', [DepartmentManagementController::class, 'deleteDivision'])->name('division.delete');
    });
    
    // Statistics & Analytics
    Route::get('/{department}/statistics', [DepartmentManagementController::class, 'statistics'])->name('statistics');
    Route::get('/{department}/metrics', [DepartmentManagementController::class, 'metrics'])->name('metrics');
    Route::get('/{department}/performance', [DepartmentManagementController::class, 'performance'])->name('performance');
    Route::get('/{department}/rankings', [DepartmentManagementController::class, 'rankings'])->name('rankings');
    
    // Resources & Budget
    Route::middleware(['role:department-head,dean,admin'])->group(function () {
        Route::get('/{department}/budget', [DepartmentManagementController::class, 'budget'])->name('budget');
        Route::get('/{department}/resources', [DepartmentManagementController::class, 'resources'])->name('resources');
        Route::get('/{department}/facilities', [DepartmentManagementController::class, 'facilities'])->name('facilities');
        Route::get('/{department}/equipment', [DepartmentManagementController::class, 'equipment'])->name('equipment');
    });
});

// ============================================================
// DIVISIONS MANAGEMENT
// ============================================================
Route::prefix('divisions')->name('divisions.')->group(function () {
    Route::get('/', [DepartmentManagementController::class, 'allDivisions'])->name('index');
    Route::get('/{division}', [DepartmentManagementController::class, 'showDivision'])->name('show');
    Route::get('/{division}/faculty', [DepartmentManagementController::class, 'divisionFaculty'])->name('faculty');
    Route::get('/{division}/courses', [DepartmentManagementController::class, 'divisionCourses'])->name('courses');
    Route::get('/{division}/students', [DepartmentManagementController::class, 'divisionStudents'])->name('students');
    
    Route::middleware(['role:department-head,division-coordinator,admin'])->group(function () {
        Route::get('/{division}/edit', [DepartmentManagementController::class, 'editDivision'])->name('edit');
        Route::put('/{division}', [DepartmentManagementController::class, 'updateDivision'])->name('update');
        Route::post('/{division}/coordinator', [DepartmentManagementController::class, 'assignDivisionCoordinator'])->name('coordinator');
    });
});

// ============================================================
// CENTERS & INSTITUTES
// ============================================================
Route::prefix('centers')->name('centers.')->group(function () {
    Route::get('/', [OrganizationalStructureController::class, 'centers'])->name('index');
    Route::get('/research', [OrganizationalStructureController::class, 'researchCenters'])->name('research');
    Route::get('/academic', [OrganizationalStructureController::class, 'academicCenters'])->name('academic');
    Route::get('/service', [OrganizationalStructureController::class, 'serviceCenters'])->name('service');
    
    // CRUD Operations
    Route::middleware(['role:provost,dean,admin'])->group(function () {
        Route::get('/create', [OrganizationalStructureController::class, 'createCenter'])->name('create');
        Route::post('/', [OrganizationalStructureController::class, 'storeCenter'])->name('store');
        Route::get('/{center}/edit', [OrganizationalStructureController::class, 'editCenter'])->name('edit');
        Route::put('/{center}', [OrganizationalStructureController::class, 'updateCenter'])->name('update');
        Route::delete('/{center}', [OrganizationalStructureController::class, 'destroyCenter'])->name('destroy');
    });
    
    // View Operations
    Route::get('/{center}', [OrganizationalStructureController::class, 'showCenter'])->name('show');
    Route::get('/{center}/staff', [OrganizationalStructureController::class, 'centerStaff'])->name('staff');
    Route::get('/{center}/projects', [OrganizationalStructureController::class, 'centerProjects'])->name('projects');
    Route::get('/{center}/publications', [OrganizationalStructureController::class, 'centerPublications'])->name('publications');
    Route::get('/{center}/funding', [OrganizationalStructureController::class, 'centerFunding'])->name('funding');
    
    // Leadership
    Route::middleware(['role:provost,dean,admin'])->group(function () {
        Route::post('/{center}/director', [OrganizationalStructureController::class, 'assignCenterDirector'])->name('director');
        Route::delete('/{center}/director', [OrganizationalStructureController::class, 'removeCenterDirector'])->name('director.remove');
    });
});

// ============================================================
// COMMITTEES
// ============================================================
Route::prefix('committees')->name('committees.')->group(function () {
    Route::get('/', [OrganizationalStructureController::class, 'committees'])->name('index');
    Route::get('/standing', [OrganizationalStructureController::class, 'standingCommittees'])->name('standing');
    Route::get('/ad-hoc', [OrganizationalStructureController::class, 'adHocCommittees'])->name('ad-hoc');
    Route::get('/search', [OrganizationalStructureController::class, 'searchCommittees'])->name('search');
    
    // Committee Management
    Route::middleware(['role:admin,provost,dean'])->group(function () {
        Route::get('/create', [OrganizationalStructureController::class, 'createCommittee'])->name('create');
        Route::post('/', [OrganizationalStructureController::class, 'storeCommittee'])->name('store');
        Route::get('/{committee}/edit', [OrganizationalStructureController::class, 'editCommittee'])->name('edit');
        Route::put('/{committee}', [OrganizationalStructureController::class, 'updateCommittee'])->name('update');
        Route::delete('/{committee}', [OrganizationalStructureController::class, 'dissolveCommittee'])->name('dissolve');
    });
    
    // View Operations
    Route::get('/{committee}', [OrganizationalStructureController::class, 'showCommittee'])->name('show');
    Route::get('/{committee}/members', [OrganizationalStructureController::class, 'committeeMembers'])->name('members');
    Route::get('/{committee}/meetings', [OrganizationalStructureController::class, 'committeeMeetings'])->name('meetings');
    Route::get('/{committee}/minutes', [OrganizationalStructureController::class, 'meetingMinutes'])->name('minutes');
    Route::get('/{committee}/documents', [OrganizationalStructureController::class, 'committeeDocuments'])->name('documents');
    
    // Member Management
    Route::middleware(['role:committee-chair,admin'])->group(function () {
        Route::post('/{committee}/member', [OrganizationalStructureController::class, 'addMember'])->name('member.add');
        Route::delete('/{committee}/member/{member}', [OrganizationalStructureController::class, 'removeMember'])->name('member.remove');
        Route::post('/{committee}/chair', [OrganizationalStructureController::class, 'appointChair'])->name('chair');
        Route::post('/{committee}/secretary', [OrganizationalStructureController::class, 'appointSecretary'])->name('secretary');
    });
});

// ============================================================
// ORGANIZATIONAL RELATIONSHIPS
// ============================================================
Route::prefix('relationships')->name('relationships.')->middleware(['role:admin'])->group(function () {
    Route::get('/', [OrganizationalStructureController::class, 'relationships'])->name('index');
    Route::get('/mapping', [OrganizationalStructureController::class, 'relationshipMapping'])->name('mapping');
    Route::post('/link', [OrganizationalStructureController::class, 'createLink'])->name('link');
    Route::delete('/link/{link}', [OrganizationalStructureController::class, 'removeLink'])->name('unlink');
    Route::get('/cross-appointments', [OrganizationalStructureController::class, 'crossAppointments'])->name('cross-appointments');
    Route::post('/cross-appointment', [OrganizationalStructureController::class, 'createCrossAppointment'])->name('cross-appointment.create');
});

// ============================================================
// ORGANIZATIONAL POLICIES
// ============================================================
Route::prefix('policies')->name('policies.')->group(function () {
    Route::get('/', [OrganizationalStructureController::class, 'policies'])->name('index');
    Route::get('/governance', [OrganizationalStructureController::class, 'governancePolicies'])->name('governance');
    Route::get('/academic', [OrganizationalStructureController::class, 'academicPolicies'])->name('academic');
    Route::get('/administrative', [OrganizationalStructureController::class, 'administrativePolicies'])->name('administrative');
    
    Route::middleware(['role:admin,policy-committee'])->group(function () {
        Route::get('/create', [OrganizationalStructureController::class, 'createPolicy'])->name('create');
        Route::post('/', [OrganizationalStructureController::class, 'storePolicy'])->name('store');
        Route::get('/{policy}/edit', [OrganizationalStructureController::class, 'editPolicy'])->name('edit');
        Route::put('/{policy}', [OrganizationalStructureController::class, 'updatePolicy'])->name('update');
        Route::post('/{policy}/approve', [OrganizationalStructureController::class, 'approvePolicy'])->name('approve');
        Route::post('/{policy}/archive', [OrganizationalStructureController::class, 'archivePolicy'])->name('archive');
    });
    
    Route::get('/{policy}', [OrganizationalStructureController::class, 'viewPolicy'])->name('view');
    Route::get('/{policy}/history', [OrganizationalStructureController::class, 'policyHistory'])->name('history');
});

// ============================================================
// REPORTING & ANALYTICS
// ============================================================
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [OrganizationalStructureController::class, 'reports'])->name('index');
    Route::get('/structure', [OrganizationalStructureController::class, 'structureReport'])->name('structure');
    Route::get('/staffing', [OrganizationalStructureController::class, 'staffingReport'])->name('staffing');
    Route::get('/enrollment', [OrganizationalStructureController::class, 'enrollmentReport'])->name('enrollment');
    Route::get('/performance', [OrganizationalStructureController::class, 'performanceReport'])->name('performance');
    Route::get('/comparison', [OrganizationalStructureController::class, 'comparisonReport'])->name('comparison');
    Route::get('/trends', [OrganizationalStructureController::class, 'trendsReport'])->name('trends');
    Route::post('/generate', [OrganizationalStructureController::class, 'generateReport'])->name('generate');
    Route::post('/export', [OrganizationalStructureController::class, 'exportReport'])->name('export');
});

// ============================================================
// REORGANIZATION & RESTRUCTURING
// ============================================================
Route::prefix('reorganization')->name('reorganization.')->middleware(['role:president,provost,admin'])->group(function () {
    Route::get('/', [OrganizationalStructureController::class, 'reorganizationDashboard'])->name('index');
    Route::get('/proposals', [OrganizationalStructureController::class, 'proposals'])->name('proposals');
    Route::get('/proposal/create', [OrganizationalStructureController::class, 'createProposal'])->name('proposal.create');
    Route::post('/proposal', [OrganizationalStructureController::class, 'submitProposal'])->name('proposal.submit');
    Route::get('/proposal/{proposal}', [OrganizationalStructureController::class, 'viewProposal'])->name('proposal.view');
    Route::post('/proposal/{proposal}/approve', [OrganizationalStructureController::class, 'approveProposal'])->name('proposal.approve');
    Route::post('/proposal/{proposal}/reject', [OrganizationalStructureController::class, 'rejectProposal'])->name('proposal.reject');
    Route::get('/impact-analysis', [OrganizationalStructureController::class, 'impactAnalysis'])->name('impact');
    Route::post('/simulate', [OrganizationalStructureController::class, 'simulateReorganization'])->name('simulate');
    Route::post('/implement/{proposal}', [OrganizationalStructureController::class, 'implementReorganization'])->name('implement');
    Route::get('/history', [OrganizationalStructureController::class, 'reorganizationHistory'])->name('history');
});