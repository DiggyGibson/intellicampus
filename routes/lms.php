<?php
/**
 * IntelliCampus Learning Management System Routes
 * 
 * Routes for the LMS module including course sites, assignments, quizzes, and discussions.
 * These routes are automatically prefixed with 'lms' and named with 'lms.'
 * Base middleware: 'web', 'auth'
 */

use App\Http\Controllers\LMSController;
use Illuminate\Support\Facades\Route;

// ============================================================
// LMS DASHBOARD
// ============================================================
Route::get('/', [LMSController::class, 'dashboard'])->name('dashboard');
Route::get('/home', [LMSController::class, 'home'])->name('home');
Route::get('/my-courses', [LMSController::class, 'myCourses'])->name('my-courses');
Route::get('/calendar', [LMSController::class, 'lmsCalendar'])->name('calendar');
Route::get('/notifications', [LMSController::class, 'lmsNotifications'])->name('notifications');
Route::get('/recent-activity', [LMSController::class, 'recentActivity'])->name('recent-activity');

// ============================================================
// COURSE DISCOVERY & ENROLLMENT
// ============================================================
Route::prefix('browse')->name('browse.')->group(function () {
    Route::get('/', [LMSController::class, 'browseCourses'])->name('index');
    Route::get('/search', [LMSController::class, 'searchCourses'])->name('search');
    Route::get('/categories', [LMSController::class, 'courseCategories'])->name('categories');
    Route::get('/category/{category}', [LMSController::class, 'categoryCoursesces'])->name('category');
    Route::get('/featured', [LMSController::class, 'featuredCourses'])->name('featured');
    Route::get('/new', [LMSController::class, 'newCourses'])->name('new');
    Route::get('/popular', [LMSController::class, 'popularCourses'])->name('popular');
    Route::get('/preview/{course}', [LMSController::class, 'previewCourse'])->name('preview');
    Route::post('/enroll/{course}', [LMSController::class, 'enrollInCourse'])->name('enroll');
    Route::post('/unenroll/{course}', [LMSController::class, 'unenrollFromCourse'])->name('unenroll');
});

// ============================================================
// COURSE SITE CREATION & MANAGEMENT (Faculty/Admin)
// ============================================================
Route::prefix('manage')->name('manage.')->middleware(['role:faculty,instructor,admin'])->group(function () {
    Route::get('/', [LMSController::class, 'manageSites'])->name('index');
    Route::get('/sites', [LMSController::class, 'mySites'])->name('sites');
    Route::get('/create', [LMSController::class, 'createSiteForm'])->name('create');
    Route::post('/create', [LMSController::class, 'createSite'])->name('store');
    Route::get('/templates', [LMSController::class, 'siteTemplates'])->name('templates');
    Route::post('/from-template', [LMSController::class, 'createFromTemplate'])->name('from-template');
    Route::post('/copy/{site}', [LMSController::class, 'copySite'])->name('copy');
    Route::post('/import', [LMSController::class, 'importSite'])->name('import');
    Route::get('/export/{site}', [LMSController::class, 'exportSite'])->name('export');
    Route::delete('/{site}', [LMSController::class, 'deleteSite'])->name('delete');
    Route::post('/{site}/archive', [LMSController::class, 'archiveSite'])->name('archive');
    Route::post('/{site}/restore', [LMSController::class, 'restoreSite'])->name('restore');
});

// ============================================================
// COURSE SITE ROUTES
// ============================================================
Route::prefix('course/{site}')->name('course.')->group(function () {
    
    // Course Home & Overview
    Route::get('/', [LMSController::class, 'courseSite'])->name('home');
    Route::get('/overview', [LMSController::class, 'courseOverview'])->name('overview');
    Route::get('/syllabus', [LMSController::class, 'syllabus'])->name('syllabus');
    Route::get('/schedule', [LMSController::class, 'courseSchedule'])->name('schedule');
    Route::get('/roster', [LMSController::class, 'courseRoster'])->name('roster');
    Route::get('/instructors', [LMSController::class, 'courseInstructors'])->name('instructors');
    
    // Course Settings (Faculty only)
    Route::middleware(['role:faculty,instructor,admin'])->group(function () {
        Route::get('/settings', [LMSController::class, 'courseSettings'])->name('settings');
        Route::put('/settings', [LMSController::class, 'updateCourseSettings'])->name('settings.update');
        Route::post('/publish', [LMSController::class, 'publishCourse'])->name('publish');
        Route::post('/unpublish', [LMSController::class, 'unpublishCourse'])->name('unpublish');
        Route::get('/enrollment', [LMSController::class, 'enrollmentManagement'])->name('enrollment');
        Route::post('/enrollment/add', [LMSController::class, 'addStudent'])->name('enrollment.add');
        Route::delete('/enrollment/{student}', [LMSController::class, 'removeStudent'])->name('enrollment.remove');
        Route::post('/enrollment/bulk', [LMSController::class, 'bulkEnrollment'])->name('enrollment.bulk');
    });
    
    // ============================================================
    // CONTENT & MODULES
    // ============================================================
    Route::prefix('content')->name('content.')->group(function () {
        Route::get('/', [LMSController::class, 'courseContent'])->name('index');
        Route::get('/modules', [LMSController::class, 'contentModules'])->name('modules');
        Route::get('/module/{module}', [LMSController::class, 'viewModule'])->name('module.view');
        Route::get('/item/{item}', [LMSController::class, 'viewContentItem'])->name('item.view');
        Route::get('/download/{item}', [LMSController::class, 'downloadContent'])->name('download');
        Route::post('/track/{item}', [LMSController::class, 'trackProgress'])->name('track');
        
        // Content Management (Faculty)
        Route::middleware(['role:faculty,instructor,admin'])->group(function () {
            Route::get('/manage', [LMSController::class, 'manageContent'])->name('manage');
            Route::post('/module', [LMSController::class, 'createModule'])->name('module.create');
            Route::put('/module/{module}', [LMSController::class, 'updateModule'])->name('module.update');
            Route::delete('/module/{module}', [LMSController::class, 'deleteModule'])->name('module.delete');
            Route::post('/module/{module}/reorder', [LMSController::class, 'reorderModule'])->name('module.reorder');
            
            // File Upload
            Route::post('/upload', [LMSController::class, 'uploadContent'])->name('upload');
            Route::post('/upload/video', [LMSController::class, 'uploadVideo'])->name('upload.video');
            Route::post('/upload/document', [LMSController::class, 'uploadDocument'])->name('upload.document');
            Route::post('/upload/scorm', [LMSController::class, 'uploadSCORM'])->name('upload.scorm');
            
            // Content Items
            Route::post('/item', [LMSController::class, 'createContentItem'])->name('item.create');
            Route::put('/item/{item}', [LMSController::class, 'updateContentItem'])->name('item.update');
            Route::delete('/item/{item}', [LMSController::class, 'deleteContentItem'])->name('item.delete');
            Route::post('/item/{item}/publish', [LMSController::class, 'publishItem'])->name('item.publish');
            Route::post('/item/{item}/unpublish', [LMSController::class, 'unpublishItem'])->name('item.unpublish');
            
            // Pages
            Route::get('/pages', [LMSController::class, 'contentPages'])->name('pages');
            Route::get('/page/create', [LMSController::class, 'createPage'])->name('page.create');
            Route::post('/page', [LMSController::class, 'storePage'])->name('page.store');
            Route::get('/page/{page}/edit', [LMSController::class, 'editPage'])->name('page.edit');
            Route::put('/page/{page}', [LMSController::class, 'updatePage'])->name('page.update');
            Route::delete('/page/{page}', [LMSController::class, 'deletePage'])->name('page.delete');
        });
    });
    
    // ============================================================
    // ASSIGNMENTS
    // ============================================================
    Route::prefix('assignments')->name('assignments.')->group(function () {
        Route::get('/', [LMSController::class, 'assignments'])->name('index');
        Route::get('/upcoming', [LMSController::class, 'upcomingAssignments'])->name('upcoming');
        Route::get('/past', [LMSController::class, 'pastAssignments'])->name('past');
        Route::get('/{assignment}', [LMSController::class, 'viewAssignment'])->name('view');
        Route::get('/{assignment}/instructions', [LMSController::class, 'assignmentInstructions'])->name('instructions');
        Route::get('/{assignment}/rubric', [LMSController::class, 'assignmentRubric'])->name('rubric');
        
        // Student Submission
        Route::get('/{assignment}/submit', [LMSController::class, 'submitForm'])->name('submit.form');
        Route::post('/{assignment}/submit', [LMSController::class, 'submitAssignment'])->name('submit');
        Route::post('/{assignment}/save-draft', [LMSController::class, 'saveDraft'])->name('draft');
        Route::get('/{assignment}/submission', [LMSController::class, 'mySubmission'])->name('submission');
        Route::put('/{assignment}/submission', [LMSController::class, 'updateSubmission'])->name('submission.update');
        Route::get('/{assignment}/feedback', [LMSController::class, 'viewFeedback'])->name('feedback');
        
        // Assignment Management (Faculty)
        Route::middleware(['role:faculty,instructor,admin'])->group(function () {
            Route::get('/create/new', [LMSController::class, 'createAssignment'])->name('create');
            Route::post('/store', [LMSController::class, 'storeAssignment'])->name('store');
            Route::get('/{assignment}/edit', [LMSController::class, 'editAssignment'])->name('edit');
            Route::put('/{assignment}', [LMSController::class, 'updateAssignment'])->name('update');
            Route::delete('/{assignment}', [LMSController::class, 'deleteAssignment'])->name('delete');
            Route::post('/{assignment}/publish', [LMSController::class, 'publishAssignment'])->name('publish');
            Route::post('/{assignment}/duplicate', [LMSController::class, 'duplicateAssignment'])->name('duplicate');
            
            // Submissions Management
            Route::get('/{assignment}/submissions', [LMSController::class, 'viewSubmissions'])->name('submissions');
            Route::get('/{assignment}/submission/{submission}', [LMSController::class, 'reviewSubmission'])->name('submission.review');
            Route::post('/{assignment}/submission/{submission}/grade', [LMSController::class, 'gradeSubmission'])->name('submission.grade');
            Route::post('/{assignment}/submission/{submission}/feedback', [LMSController::class, 'provideFeedback'])->name('submission.feedback');
            Route::post('/{assignment}/submission/{submission}/return', [LMSController::class, 'returnSubmission'])->name('submission.return');
            Route::get('/{assignment}/download-all', [LMSController::class, 'downloadAllSubmissions'])->name('download-all');
            Route::get('/{assignment}/statistics', [LMSController::class, 'assignmentStatistics'])->name('statistics');
            
            // Rubric Management
            Route::get('/{assignment}/rubric/create', [LMSController::class, 'createRubric'])->name('rubric.create');
            Route::post('/{assignment}/rubric', [LMSController::class, 'storeRubric'])->name('rubric.store');
            Route::put('/{assignment}/rubric', [LMSController::class, 'updateRubric'])->name('rubric.update');
            Route::delete('/{assignment}/rubric', [LMSController::class, 'deleteRubric'])->name('rubric.delete');
            
            // Peer Review
            Route::post('/{assignment}/peer-review/enable', [LMSController::class, 'enablePeerReview'])->name('peer-review.enable');
            Route::post('/{assignment}/peer-review/assign', [LMSController::class, 'assignPeerReviewers'])->name('peer-review.assign');
            Route::get('/{assignment}/peer-reviews', [LMSController::class, 'managePeerReviews'])->name('peer-reviews');
        });
        
        // Peer Review (Student)
        Route::get('/{assignment}/peer-review', [LMSController::class, 'myPeerReviews'])->name('peer-review');
        Route::post('/{assignment}/peer-review/{review}', [LMSController::class, 'submitPeerReview'])->name('peer-review.submit');
    });
    
    // ============================================================
    // QUIZZES & TESTS
    // ============================================================
    Route::prefix('quizzes')->name('quizzes.')->group(function () {
        Route::get('/', [LMSController::class, 'quizzes'])->name('index');
        Route::get('/upcoming', [LMSController::class, 'upcomingQuizzes'])->name('upcoming');
        Route::get('/past', [LMSController::class, 'pastQuizzes'])->name('past');
        Route::get('/{quiz}', [LMSController::class, 'viewQuiz'])->name('view');
        
        // Quiz Taking
        Route::get('/{quiz}/start', [LMSController::class, 'startQuiz'])->name('start');
        Route::post('/{quiz}/begin', [LMSController::class, 'beginQuiz'])->name('begin');
        Route::get('/{attempt}/question/{question}', [LMSController::class, 'viewQuestion'])->name('question');
        Route::post('/{attempt}/answer', [LMSController::class, 'saveAnswer'])->name('answer');
        Route::post('/{attempt}/flag', [LMSController::class, 'flagQuestion'])->name('flag');
        Route::get('/{attempt}/review', [LMSController::class, 'reviewQuiz'])->name('review');
        Route::post('/{attempt}/submit', [LMSController::class, 'submitQuiz'])->name('submit');
        Route::get('/{attempt}/results', [LMSController::class, 'quizResults'])->name('results');
        Route::get('/{attempt}/feedback', [LMSController::class, 'quizFeedback'])->name('feedback');
        
        // Quiz Management (Faculty)
        Route::middleware(['role:faculty,instructor,admin'])->group(function () {
            Route::get('/create/new', [LMSController::class, 'createQuiz'])->name('create');
            Route::post('/store', [LMSController::class, 'storeQuiz'])->name('store');
            Route::get('/{quiz}/edit', [LMSController::class, 'editQuiz'])->name('edit');
            Route::put('/{quiz}', [LMSController::class, 'updateQuiz'])->name('update');
            Route::delete('/{quiz}', [LMSController::class, 'deleteQuiz'])->name('delete');
            Route::post('/{quiz}/publish', [LMSController::class, 'publishQuiz'])->name('publish');
            Route::post('/{quiz}/duplicate', [LMSController::class, 'duplicateQuiz'])->name('duplicate');
            
            // Question Management
            Route::get('/{quiz}/questions', [LMSController::class, 'manageQuestions'])->name('questions');
            Route::get('/{quiz}/question/create', [LMSController::class, 'createQuestion'])->name('question.create');
            Route::post('/{quiz}/question', [LMSController::class, 'storeQuestion'])->name('question.store');
            Route::get('/{quiz}/question/{question}/edit', [LMSController::class, 'editQuestion'])->name('question.edit');
            Route::put('/{quiz}/question/{question}', [LMSController::class, 'updateQuestion'])->name('question.update');
            Route::delete('/{quiz}/question/{question}', [LMSController::class, 'deleteQuestion'])->name('question.delete');
            Route::post('/{quiz}/questions/reorder', [LMSController::class, 'reorderQuestions'])->name('questions.reorder');
            Route::post('/{quiz}/questions/import', [LMSController::class, 'importQuestions'])->name('questions.import');
            
            // Question Bank
            Route::get('/bank/questions', [LMSController::class, 'questionBank'])->name('bank');
            Route::post('/bank/question', [LMSController::class, 'addToBank'])->name('bank.add');
            Route::post('/{quiz}/bank/import', [LMSController::class, 'importFromBank'])->name('bank.import');
            
            // Quiz Results Management
            Route::get('/{quiz}/attempts', [LMSController::class, 'viewAttempts'])->name('attempts');
            Route::get('/{quiz}/attempt/{attempt}', [LMSController::class, 'reviewAttempt'])->name('attempt.review');
            Route::post('/{quiz}/attempt/{attempt}/grade', [LMSController::class, 'gradeAttempt'])->name('attempt.grade');
            Route::post('/{quiz}/attempt/{attempt}/regrade', [LMSController::class, 'regradeAttempt'])->name('attempt.regrade');
            Route::get('/{quiz}/statistics', [LMSController::class, 'quizStatistics'])->name('statistics');
            Route::get('/{quiz}/item-analysis', [LMSController::class, 'itemAnalysis'])->name('item-analysis');
        });
    });
    
    // ============================================================
    // DISCUSSIONS
    // ============================================================
    Route::prefix('discussions')->name('discussions.')->group(function () {
        Route::get('/', [LMSController::class, 'discussions'])->name('index');
        Route::get('/search', [LMSController::class, 'searchDiscussions'])->name('search');
        Route::get('/my-posts', [LMSController::class, 'myPosts'])->name('my-posts');
        Route::get('/subscribed', [LMSController::class, 'subscribedThreads'])->name('subscribed');
        
        // Forums
        Route::get('/forum/{forum}', [LMSController::class, 'viewForum'])->name('forum');
        Route::get('/forum/{forum}/new-topic', [LMSController::class, 'newTopic'])->name('new-topic');
        Route::post('/forum/{forum}/topic', [LMSController::class, 'createTopic'])->name('topic.create');
        
        // Topics/Threads
        Route::get('/topic/{topic}', [LMSController::class, 'viewTopic'])->name('topic');
        Route::post('/topic/{topic}/reply', [LMSController::class, 'replyToTopic'])->name('reply');
        Route::put('/post/{post}', [LMSController::class, 'editPost'])->name('post.edit');
        Route::delete('/post/{post}', [LMSController::class, 'deletePost'])->name('post.delete');
        Route::post('/post/{post}/like', [LMSController::class, 'likePost'])->name('post.like');
        Route::post('/post/{post}/report', [LMSController::class, 'reportPost'])->name('post.report');
        Route::post('/topic/{topic}/subscribe', [LMSController::class, 'subscribeTopic'])->name('topic.subscribe');
        Route::delete('/topic/{topic}/unsubscribe', [LMSController::class, 'unsubscribeTopic'])->name('topic.unsubscribe');
        
        // Forum Management (Faculty)
        Route::middleware(['role:faculty,instructor,admin'])->group(function () {
            Route::get('/manage', [LMSController::class, 'manageForums'])->name('manage');
            Route::post('/forum', [LMSController::class, 'createForum'])->name('forum.create');
            Route::put('/forum/{forum}', [LMSController::class, 'updateForum'])->name('forum.update');
            Route::delete('/forum/{forum}', [LMSController::class, 'deleteForum'])->name('forum.delete');
            Route::post('/topic/{topic}/pin', [LMSController::class, 'pinTopic'])->name('topic.pin');
            Route::post('/topic/{topic}/lock', [LMSController::class, 'lockTopic'])->name('topic.lock');
            Route::post('/topic/{topic}/move', [LMSController::class, 'moveTopic'])->name('topic.move');
            Route::get('/moderation', [LMSController::class, 'moderationQueue'])->name('moderation');
            Route::post('/post/{post}/approve', [LMSController::class, 'approvePost'])->name('post.approve');
            Route::post('/post/{post}/hide', [LMSController::class, 'hidePost'])->name('post.hide');
        });
    });
    
    // ============================================================
    // ANNOUNCEMENTS
    // ============================================================
    Route::prefix('announcements')->name('announcements.')->group(function () {
        Route::get('/', [LMSController::class, 'announcements'])->name('index');
        Route::get('/{announcement}', [LMSController::class, 'viewAnnouncement'])->name('view');
        Route::post('/{announcement}/acknowledge', [LMSController::class, 'acknowledgeAnnouncement'])->name('acknowledge');
        
        // Announcement Management (Faculty)
        Route::middleware(['role:faculty,instructor,admin'])->group(function () {
            Route::get('/create/new', [LMSController::class, 'createAnnouncement'])->name('create');
            Route::post('/store', [LMSController::class, 'storeAnnouncement'])->name('store');
            Route::get('/{announcement}/edit', [LMSController::class, 'editAnnouncement'])->name('edit');
            Route::put('/{announcement}', [LMSController::class, 'updateAnnouncement'])->name('update');
            Route::delete('/{announcement}', [LMSController::class, 'deleteAnnouncement'])->name('delete');
            Route::post('/{announcement}/email', [LMSController::class, 'emailAnnouncement'])->name('email');
        });
    });
    
    // ============================================================
    // GRADEBOOK
    // ============================================================
    Route::prefix('gradebook')->name('gradebook.')->group(function () {
        // Student View
        Route::get('/my-grades', [LMSController::class, 'myGrades'])->name('my-grades');
        Route::get('/progress', [LMSController::class, 'gradeProgress'])->name('progress');
        
        // Faculty Gradebook
        Route::middleware(['role:faculty,instructor,admin'])->group(function () {
            Route::get('/', [LMSController::class, 'gradebook'])->name('index');
            Route::get('/setup', [LMSController::class, 'gradebookSetup'])->name('setup');
            
            // Grade Categories
            Route::get('/categories', [LMSController::class, 'gradeCategories'])->name('categories');
            Route::post('/category', [LMSController::class, 'createCategory'])->name('category.create');
            Route::put('/category/{category}', [LMSController::class, 'updateCategory'])->name('category.update');
            Route::delete('/category/{category}', [LMSController::class, 'deleteCategory'])->name('category.delete');
            
            // Grade Items
            Route::get('/items', [LMSController::class, 'gradeItems'])->name('items');
            Route::post('/item', [LMSController::class, 'createGradeItem'])->name('item.create');
            Route::put('/item/{item}', [LMSController::class, 'updateGradeItem'])->name('item.update');
            Route::delete('/item/{item}', [LMSController::class, 'deleteGradeItem'])->name('item.delete');
            
            // Grade Entry
            Route::get('/entry', [LMSController::class, 'gradeEntry'])->name('entry');
            Route::post('/grade', [LMSController::class, 'enterGrade'])->name('grade.enter');
            Route::post('/grades/bulk', [LMSController::class, 'bulkGradeEntry'])->name('grades.bulk');
            Route::post('/import', [LMSController::class, 'importGrades'])->name('import');
            Route::get('/export', [LMSController::class, 'exportGradebook'])->name('export');
            
            // Grade Calculation
            Route::get('/calculate', [LMSController::class, 'calculateGrades'])->name('calculate');
            Route::post('/weights', [LMSController::class, 'updateWeights'])->name('weights');
            Route::get('/statistics', [LMSController::class, 'gradeStatistics'])->name('statistics');
            Route::get('/report', [LMSController::class, 'gradeReport'])->name('report');
        });
    });
    
    // ============================================================
    // GROUPS & COLLABORATION
    // ============================================================
    Route::prefix('groups')->name('groups.')->group(function () {
        Route::get('/', [LMSController::class, 'groups'])->name('index');
        Route::get('/my-groups', [LMSController::class, 'myGroups'])->name('my');
        Route::get('/{group}', [LMSController::class, 'viewGroup'])->name('view');
        Route::get('/{group}/members', [LMSController::class, 'groupMembers'])->name('members');
        Route::get('/{group}/files', [LMSController::class, 'groupFiles'])->name('files');
        Route::post('/{group}/file', [LMSController::class, 'uploadGroupFile'])->name('file.upload');
        Route::get('/{group}/discussion', [LMSController::class, 'groupDiscussion'])->name('discussion');
        Route::post('/{group}/post', [LMSController::class, 'postToGroup'])->name('post');
        
        // Group Management (Faculty)
        Route::middleware(['role:faculty,instructor,admin'])->group(function () {
            Route::post('/create', [LMSController::class, 'createGroup'])->name('create');
            Route::put('/{group}', [LMSController::class, 'updateGroup'])->name('update');
            Route::delete('/{group}', [LMSController::class, 'deleteGroup'])->name('delete');
            Route::post('/{group}/member', [LMSController::class, 'addMember'])->name('member.add');
            Route::delete('/{group}/member/{member}', [LMSController::class, 'removeMember'])->name('member.remove');
            Route::post('/auto-create', [LMSController::class, 'autoCreateGroups'])->name('auto-create');
        });
    });
    
    // ============================================================
    // ANALYTICS & REPORTS
    // ============================================================
    Route::prefix('analytics')->name('analytics.')->group(function () {
        // Student Analytics
        Route::get('/my-analytics', [LMSController::class, 'myAnalytics'])->name('my');
        Route::get('/my-progress', [LMSController::class, 'myProgress'])->name('my-progress');
        Route::get('/my-engagement', [LMSController::class, 'myEngagement'])->name('my-engagement');
        
        // Faculty Analytics
        Route::middleware(['role:faculty,instructor,admin'])->group(function () {
            Route::get('/', [LMSController::class, 'courseAnalytics'])->name('index');
            Route::get('/engagement', [LMSController::class, 'engagementAnalytics'])->name('engagement');
            Route::get('/performance', [LMSController::class, 'performanceAnalytics'])->name('performance');
            Route::get('/content', [LMSController::class, 'contentAnalytics'])->name('content');
            Route::get('/student/{student}', [LMSController::class, 'studentAnalytics'])->name('student');
            Route::get('/comparison', [LMSController::class, 'comparisonReport'])->name('comparison');
            Route::get('/export', [LMSController::class, 'exportAnalytics'])->name('export');
        });
    });
    
    // ============================================================
    // TOOLS & INTEGRATIONS
    // ============================================================
    Route::prefix('tools')->name('tools.')->group(function () {
        Route::get('/', [LMSController::class, 'tools'])->name('index');
        Route::get('/turnitin', [LMSController::class, 'turnitin'])->name('turnitin');
        Route::post('/turnitin/check', [LMSController::class, 'turnitinCheck'])->name('turnitin.check');
        Route::get('/zoom', [LMSController::class, 'zoomIntegration'])->name('zoom');
        Route::post('/zoom/schedule', [LMSController::class, 'scheduleZoom'])->name('zoom.schedule');
        Route::get('/library', [LMSController::class, 'libraryResources'])->name('library');
        Route::get('/citation', [LMSController::class, 'citationTool'])->name('citation');
    });
});

// ============================================================
// LMS ADMINISTRATION (System-wide)
// ============================================================
Route::prefix('admin')->name('admin.')->middleware(['role:admin,lms-admin'])->group(function () {
    Route::get('/', [LMSController::class, 'adminDashboard'])->name('dashboard');
    Route::get('/sites', [LMSController::class, 'allSites'])->name('sites');
    Route::get('/site/{site}', [LMSController::class, 'adminViewSite'])->name('site.view');
    Route::post('/site/{site}/disable', [LMSController::class, 'disableSite'])->name('site.disable');
    Route::post('/site/{site}/enable', [LMSController::class, 'enableSite'])->name('site.enable');
    
    // System Settings
    Route::get('/settings', [LMSController::class, 'systemSettings'])->name('settings');
    Route::put('/settings', [LMSController::class, 'updateSystemSettings'])->name('settings.update');
    Route::get('/themes', [LMSController::class, 'themes'])->name('themes');
    Route::post('/theme', [LMSController::class, 'installTheme'])->name('theme.install');
    Route::get('/plugins', [LMSController::class, 'plugins'])->name('plugins');
    Route::post('/plugin', [LMSController::class, 'installPlugin'])->name('plugin.install');
    
    // System Analytics
    Route::get('/analytics', [LMSController::class, 'systemAnalytics'])->name('analytics');
    Route::get('/usage', [LMSController::class, 'usageStatistics'])->name('usage');
    Route::get('/performance', [LMSController::class, 'performanceMetrics'])->name('performance');
    Route::get('/reports', [LMSController::class, 'systemReports'])->name('reports');
});