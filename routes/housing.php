<?php
/**
 * IntelliCampus Housing & Residence Life Routes
 * 
 * Routes for campus housing, residence halls, and residential life management.
 * These routes are automatically prefixed with 'housing' and named with 'housing.'
 * Base middleware: 'web', 'auth'
 * Note: This is an optional module - check if enabled before loading
 */

use App\Http\Controllers\HousingController;
use App\Http\Controllers\HousingApplicationController;
use App\Http\Controllers\ResidenceLifeController;
use App\Http\Controllers\HousingMaintenanceController;
use Illuminate\Support\Facades\Route;

// ============================================================
// PUBLIC HOUSING INFORMATION (No Auth Required)
// ============================================================
Route::prefix('info')->name('info.')->group(function () {
    Route::get('/', [HousingController::class, 'publicInfo'])->name('index');
    Route::get('/residence-halls', [HousingController::class, 'residenceHalls'])->name('halls');
    Route::get('/hall/{hall}', [HousingController::class, 'hallDetails'])->name('hall.details');
    Route::get('/virtual-tour/{hall}', [HousingController::class, 'virtualTour'])->name('virtual-tour');
    Route::get('/floor-plans', [HousingController::class, 'floorPlans'])->name('floor-plans');
    Route::get('/rates', [HousingController::class, 'housingRates'])->name('rates');
    Route::get('/meal-plans', [HousingController::class, 'mealPlans'])->name('meal-plans');
    Route::get('/policies', [HousingController::class, 'housingPolicies'])->name('policies');
    Route::get('/faq', [HousingController::class, 'housingFaq'])->name('faq');
    Route::get('/contact', [HousingController::class, 'contactInfo'])->name('contact');
});

// ============================================================
// STUDENT HOUSING PORTAL
// ============================================================
Route::middleware(['verified', 'role:student'])->group(function () {
    
    // Housing Dashboard
    Route::get('/dashboard', [HousingController::class, 'studentDashboard'])->name('dashboard');
    Route::get('/status', [HousingController::class, 'housingStatus'])->name('status');
    
    // Housing Application
    Route::prefix('application')->name('application.')->group(function () {
        Route::get('/', [HousingApplicationController::class, 'index'])->name('index');
        Route::get('/start', [HousingApplicationController::class, 'startApplication'])->name('start');
        Route::get('/continue', [HousingApplicationController::class, 'continueApplication'])->name('continue');
        Route::post('/personal', [HousingApplicationController::class, 'savePersonalInfo'])->name('personal');
        Route::post('/preferences', [HousingApplicationController::class, 'savePreferences'])->name('preferences');
        Route::post('/roommates', [HousingApplicationController::class, 'saveRoommatePreferences'])->name('roommates');
        Route::post('/lifestyle', [HousingApplicationController::class, 'saveLifestyleInfo'])->name('lifestyle');
        Route::post('/medical', [HousingApplicationController::class, 'saveMedicalInfo'])->name('medical');
        Route::post('/emergency', [HousingApplicationController::class, 'saveEmergencyContacts'])->name('emergency');
        Route::get('/review', [HousingApplicationController::class, 'reviewApplication'])->name('review');
        Route::post('/submit', [HousingApplicationController::class, 'submitApplication'])->name('submit');
        Route::get('/confirmation', [HousingApplicationController::class, 'confirmation'])->name('confirmation');
        Route::get('/status', [HousingApplicationController::class, 'applicationStatus'])->name('status');
        Route::post('/withdraw', [HousingApplicationController::class, 'withdrawApplication'])->name('withdraw');
        
        // Returning Student Application
        Route::get('/returning', [HousingApplicationController::class, 'returningStudent'])->name('returning');
        Route::post('/renewal', [HousingApplicationController::class, 'renewalApplication'])->name('renewal');
        
        // Waitlist
        Route::get('/waitlist', [HousingApplicationController::class, 'waitlistStatus'])->name('waitlist');
        Route::post('/waitlist/accept', [HousingApplicationController::class, 'acceptWaitlistOffer'])->name('waitlist.accept');
        Route::post('/waitlist/decline', [HousingApplicationController::class, 'declineWaitlistOffer'])->name('waitlist.decline');
    });
    
    // Room Selection
    Route::prefix('room-selection')->name('room-selection.')->group(function () {
        Route::get('/', [HousingController::class, 'roomSelectionPortal'])->name('index');
        Route::get('/available', [HousingController::class, 'availableRooms'])->name('available');
        Route::get('/search', [HousingController::class, 'searchRooms'])->name('search');
        Route::get('/room/{room}', [HousingController::class, 'roomDetails'])->name('room.details');
        Route::post('/select/{room}', [HousingController::class, 'selectRoom'])->name('select');
        Route::get('/confirmation', [HousingController::class, 'selectionConfirmation'])->name('confirmation');
        Route::get('/lottery', [HousingController::class, 'lotteryInformation'])->name('lottery');
        Route::get('/lottery/number', [HousingController::class, 'myLotteryNumber'])->name('lottery.number');
        Route::get('/timeline', [HousingController::class, 'selectionTimeline'])->name('timeline');
    });
    
    // Roommate Matching
    Route::prefix('roommates')->name('roommates.')->group(function () {
        Route::get('/', [HousingController::class, 'roommatePortal'])->name('index');
        Route::get('/profile', [HousingController::class, 'roommateProfile'])->name('profile');
        Route::put('/profile', [HousingController::class, 'updateRoommateProfile'])->name('profile.update');
        Route::get('/search', [HousingController::class, 'searchRoommates'])->name('search');
        Route::get('/matches', [HousingController::class, 'potentialMatches'])->name('matches');
        Route::post('/request/{student}', [HousingController::class, 'sendRoommateRequest'])->name('request');
        Route::get('/requests', [HousingController::class, 'roommateRequests'])->name('requests');
        Route::post('/request/{request}/accept', [HousingController::class, 'acceptRequest'])->name('request.accept');
        Route::post('/request/{request}/decline', [HousingController::class, 'declineRequest'])->name('request.decline');
        Route::get('/group', [HousingController::class, 'roommateGroup'])->name('group');
        Route::post('/group/create', [HousingController::class, 'createGroup'])->name('group.create');
        Route::post('/group/join/{code}', [HousingController::class, 'joinGroup'])->name('group.join');
        Route::post('/group/leave', [HousingController::class, 'leaveGroup'])->name('group.leave');
    });
    
    // Current Resident Services
    Route::prefix('resident')->name('resident.')->group(function () {
        Route::get('/', [HousingController::class, 'residentPortal'])->name('index');
        Route::get('/assignment', [HousingController::class, 'myAssignment'])->name('assignment');
        Route::get('/roommate', [HousingController::class, 'myRoommate'])->name('roommate');
        Route::get('/room-change', [HousingController::class, 'roomChangeForm'])->name('room-change');
        Route::post('/room-change', [HousingController::class, 'submitRoomChange'])->name('room-change.submit');
        Route::get('/room-swap', [HousingController::class, 'roomSwap'])->name('room-swap');
        Route::post('/room-swap', [HousingController::class, 'proposeSwap'])->name('room-swap.propose');
        
        // Check-in/Check-out
        Route::get('/check-in', [HousingController::class, 'checkInForm'])->name('check-in');
        Route::post('/check-in', [HousingController::class, 'completeCheckIn'])->name('check-in.complete');
        Route::get('/check-out', [HousingController::class, 'checkOutForm'])->name('check-out');
        Route::post('/check-out/schedule', [HousingController::class, 'scheduleCheckOut'])->name('check-out.schedule');
        Route::post('/check-out/complete', [HousingController::class, 'completeCheckOut'])->name('check-out.complete');
        Route::get('/express-check-out', [HousingController::class, 'expressCheckOut'])->name('express-check-out');
        
        // Room Condition
        Route::get('/room-condition', [HousingController::class, 'roomConditionForm'])->name('room-condition');
        Route::post('/room-condition', [HousingController::class, 'submitRoomCondition'])->name('room-condition.submit');
        Route::get('/damages', [HousingController::class, 'damageAssessment'])->name('damages');
        Route::post('/damage/appeal', [HousingController::class, 'appealDamage'])->name('damage.appeal');
    });
    
    // Maintenance Requests
    Route::prefix('maintenance')->name('maintenance.')->group(function () {
        Route::get('/', [HousingMaintenanceController::class, 'index'])->name('index');
        Route::get('/request', [HousingMaintenanceController::class, 'requestForm'])->name('request');
        Route::post('/request', [HousingMaintenanceController::class, 'submitRequest'])->name('request.submit');
        Route::get('/requests', [HousingMaintenanceController::class, 'myRequests'])->name('requests');
        Route::get('/request/{request}', [HousingMaintenanceController::class, 'viewRequest'])->name('request.view');
        Route::post('/request/{request}/cancel', [HousingMaintenanceController::class, 'cancelRequest'])->name('request.cancel');
        Route::post('/request/{request}/feedback', [HousingMaintenanceController::class, 'submitFeedback'])->name('request.feedback');
        Route::get('/emergency', [HousingMaintenanceController::class, 'emergencyMaintenance'])->name('emergency');
    });
    
    // Guest Registration
    Route::prefix('guests')->name('guests.')->group(function () {
        Route::get('/', [HousingController::class, 'guestManagement'])->name('index');
        Route::get('/register', [HousingController::class, 'registerGuestForm'])->name('register');
        Route::post('/register', [HousingController::class, 'registerGuest'])->name('register.submit');
        Route::get('/my-guests', [HousingController::class, 'myGuests'])->name('my');
        Route::delete('/guest/{guest}', [HousingController::class, 'removeGuest'])->name('remove');
        Route::get('/overnight', [HousingController::class, 'overnightGuestForm'])->name('overnight');
        Route::post('/overnight', [HousingController::class, 'registerOvernightGuest'])->name('overnight.register');
        Route::get('/policy', [HousingController::class, 'guestPolicy'])->name('policy');
    });
    
    // Billing & Payments
    Route::prefix('billing')->name('billing.')->group(function () {
        Route::get('/', [HousingController::class, 'housingBilling'])->name('index');
        Route::get('/charges', [HousingController::class, 'housingCharges'])->name('charges');
        Route::get('/payment', [HousingController::class, 'makePayment'])->name('payment');
        Route::post('/payment', [HousingController::class, 'processPayment'])->name('payment.process');
        Route::get('/payment-plan', [HousingController::class, 'paymentPlan'])->name('payment-plan');
        Route::post('/payment-plan/enroll', [HousingController::class, 'enrollPaymentPlan'])->name('payment-plan.enroll');
        Route::get('/refund', [HousingController::class, 'refundInformation'])->name('refund');
        Route::post('/refund/request', [HousingController::class, 'requestRefund'])->name('refund.request');
    });
});

// ============================================================
// RESIDENCE LIFE
// ============================================================
Route::prefix('residence-life')->name('residence-life.')->middleware(['auth'])->group(function () {
    Route::get('/', [ResidenceLifeController::class, 'index'])->name('index');
    Route::get('/community', [ResidenceLifeController::class, 'community'])->name('community');
    Route::get('/staff', [ResidenceLifeController::class, 'residenceStaff'])->name('staff');
    Route::get('/ra/{ra}', [ResidenceLifeController::class, 'raProfile'])->name('ra.profile');
    
    // Programs & Events
    Route::get('/programs', [ResidenceLifeController::class, 'programs'])->name('programs');
    Route::get('/program/{program}', [ResidenceLifeController::class, 'programDetails'])->name('program.details');
    Route::post('/program/{program}/register', [ResidenceLifeController::class, 'registerForProgram'])->name('program.register');
    Route::get('/calendar', [ResidenceLifeController::class, 'eventCalendar'])->name('calendar');
    
    // Floor/Hall Meetings
    Route::get('/meetings', [ResidenceLifeController::class, 'hallMeetings'])->name('meetings');
    Route::get('/meeting/{meeting}', [ResidenceLifeController::class, 'meetingDetails'])->name('meeting.details');
    Route::post('/meeting/{meeting}/rsvp', [ResidenceLifeController::class, 'rsvpMeeting'])->name('meeting.rsvp');
    
    // Learning Communities
    Route::get('/learning-communities', [ResidenceLifeController::class, 'learningCommunities'])->name('learning-communities');
    Route::get('/learning-community/{community}', [ResidenceLifeController::class, 'communityDetails'])->name('community.details');
    Route::post('/learning-community/{community}/join', [ResidenceLifeController::class, 'joinCommunity'])->name('community.join');
    
    // Resources
    Route::get('/resources', [ResidenceLifeController::class, 'resources'])->name('resources');
    Route::get('/handbook', [ResidenceLifeController::class, 'residentHandbook'])->name('handbook');
    Route::get('/safety', [ResidenceLifeController::class, 'safetyResources'])->name('safety');
    Route::get('/quiet-hours', [ResidenceLifeController::class, 'quietHours'])->name('quiet-hours');
});

// ============================================================
// HOUSING STAFF PORTAL
// ============================================================
Route::prefix('staff')->name('staff.')->middleware(['auth', 'role:housing-staff,ra,rd,admin'])->group(function () {
    
    // Staff Dashboard
    Route::get('/dashboard', [HousingController::class, 'staffDashboard'])->name('dashboard');
    Route::get('/tasks', [HousingController::class, 'staffTasks'])->name('tasks');
    Route::get('/schedule', [HousingController::class, 'staffSchedule'])->name('schedule');
    
    // Resident Management
    Route::prefix('residents')->name('residents.')->group(function () {
        Route::get('/', [HousingController::class, 'residentList'])->name('index');
        Route::get('/search', [HousingController::class, 'searchResidents'])->name('search');
        Route::get('/{resident}', [HousingController::class, 'residentProfile'])->name('profile');
        Route::get('/floor/{floor}', [HousingController::class, 'floorResidents'])->name('floor');
        Route::get('/building/{building}', [HousingController::class, 'buildingResidents'])->name('building');
        Route::post('/note/{resident}', [HousingController::class, 'addResidentNote'])->name('note');
    });
    
    // Duty & On-Call
    Route::prefix('duty')->name('duty.')->group(function () {
        Route::get('/', [ResidenceLifeController::class, 'dutySchedule'])->name('index');
        Route::get('/my-shifts', [ResidenceLifeController::class, 'myDutyShifts'])->name('my-shifts');
        Route::post('/check-in', [ResidenceLifeController::class, 'dutyCheckIn'])->name('check-in');
        Route::post('/check-out', [ResidenceLifeController::class, 'dutyCheckOut'])->name('check-out');
        Route::get('/rounds', [ResidenceLifeController::class, 'dutyRounds'])->name('rounds');
        Route::post('/rounds/log', [ResidenceLifeController::class, 'logRounds'])->name('rounds.log');
        Route::get('/report', [ResidenceLifeController::class, 'dutyReportForm'])->name('report');
        Route::post('/report', [ResidenceLifeController::class, 'submitDutyReport'])->name('report.submit');
    });
    
    // Incident Reports
    Route::prefix('incidents')->name('incidents.')->group(function () {
        Route::get('/', [ResidenceLifeController::class, 'incidents'])->name('index');
        Route::get('/create', [ResidenceLifeController::class, 'createIncident'])->name('create');
        Route::post('/', [ResidenceLifeController::class, 'storeIncident'])->name('store');
        Route::get('/{incident}', [ResidenceLifeController::class, 'viewIncident'])->name('view');
        Route::put('/{incident}', [ResidenceLifeController::class, 'updateIncident'])->name('update');
        Route::post('/{incident}/follow-up', [ResidenceLifeController::class, 'addFollowUp'])->name('follow-up');
        Route::get('/reports', [ResidenceLifeController::class, 'incidentReports'])->name('reports');
    });
    
    // Room Inspections
    Route::prefix('inspections')->name('inspections.')->group(function () {
        Route::get('/', [HousingController::class, 'inspections'])->name('index');
        Route::get('/schedule', [HousingController::class, 'inspectionSchedule'])->name('schedule');
        Route::post('/schedule', [HousingController::class, 'scheduleInspection'])->name('schedule.create');
        Route::get('/conduct/{room}', [HousingController::class, 'conductInspection'])->name('conduct');
        Route::post('/submit', [HousingController::class, 'submitInspection'])->name('submit');
        Route::get('/health-safety', [HousingController::class, 'healthSafetyInspections'])->name('health-safety');
        Route::get('/report/{inspection}', [HousingController::class, 'inspectionReport'])->name('report');
    });
    
    // Lockouts & Keys
    Route::prefix('lockouts')->name('lockouts.')->group(function () {
        Route::get('/', [HousingController::class, 'lockouts'])->name('index');
        Route::post('/assist', [HousingController::class, 'assistLockout'])->name('assist');
        Route::get('/history', [HousingController::class, 'lockoutHistory'])->name('history');
        Route::get('/keys', [HousingController::class, 'keyManagement'])->name('keys');
        Route::post('/key/issue', [HousingController::class, 'issueKey'])->name('key.issue');
        Route::post('/key/return', [HousingController::class, 'returnKey'])->name('key.return');
    });
});

// ============================================================
// HOUSING ADMINISTRATION
// ============================================================
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:housing-director,housing-admin,admin'])->group(function () {
    
    // Admin Dashboard
    Route::get('/', [HousingController::class, 'adminDashboard'])->name('dashboard');
    Route::get('/overview', [HousingController::class, 'housingOverview'])->name('overview');
    Route::get('/occupancy', [HousingController::class, 'occupancyReport'])->name('occupancy');
    
    // Building Management
    Route::prefix('buildings')->name('buildings.')->group(function () {
        Route::get('/', [HousingController::class, 'buildings'])->name('index');
        Route::get('/create', [HousingController::class, 'createBuilding'])->name('create');
        Route::post('/', [HousingController::class, 'storeBuilding'])->name('store');
        Route::get('/{building}', [HousingController::class, 'buildingDetails'])->name('details');
        Route::get('/{building}/edit', [HousingController::class, 'editBuilding'])->name('edit');
        Route::put('/{building}', [HousingController::class, 'updateBuilding'])->name('update');
        Route::post('/{building}/status', [HousingController::class, 'updateBuildingStatus'])->name('status');
        Route::get('/{building}/rooms', [HousingController::class, 'buildingRooms'])->name('rooms');
        Route::get('/{building}/occupancy', [HousingController::class, 'buildingOccupancy'])->name('occupancy');
    });
    
    // Room Management
    Route::prefix('rooms')->name('rooms.')->group(function () {
        Route::get('/', [HousingController::class, 'rooms'])->name('index');
        Route::get('/availability', [HousingController::class, 'roomAvailability'])->name('availability');
        Route::get('/{room}', [HousingController::class, 'adminRoomDetails'])->name('details');
        Route::put('/{room}', [HousingController::class, 'updateRoom'])->name('update');
        Route::post('/{room}/status', [HousingController::class, 'updateRoomStatus'])->name('status');
        Route::post('/{room}/block', [HousingController::class, 'blockRoom'])->name('block');
        Route::post('/{room}/unblock', [HousingController::class, 'unblockRoom'])->name('unblock');
        Route::post('/bulk-update', [HousingController::class, 'bulkUpdateRooms'])->name('bulk-update');
    });
    
    // Assignment Management
    Route::prefix('assignments')->name('assignments.')->group(function () {
        Route::get('/', [HousingController::class, 'assignments'])->name('index');
        Route::get('/pending', [HousingController::class, 'pendingAssignments'])->name('pending');
        Route::post('/assign', [HousingController::class, 'manualAssignment'])->name('assign');
        Route::post('/bulk-assign', [HousingController::class, 'bulkAssignment'])->name('bulk-assign');
        Route::post('/auto-assign', [HousingController::class, 'autoAssignment'])->name('auto-assign');
        Route::put('/{assignment}', [HousingController::class, 'updateAssignment'])->name('update');
        Route::delete('/{assignment}', [HousingController::class, 'cancelAssignment'])->name('cancel');
        Route::get('/changes', [HousingController::class, 'roomChanges'])->name('changes');
        Route::post('/change/{change}/approve', [HousingController::class, 'approveRoomChange'])->name('change.approve');
        Route::post('/change/{change}/deny', [HousingController::class, 'denyRoomChange'])->name('change.deny');
    });
    
    // Application Management
    Route::prefix('applications')->name('applications.')->group(function () {
        Route::get('/', [HousingApplicationController::class, 'adminIndex'])->name('index');
        Route::get('/review', [HousingApplicationController::class, 'reviewQueue'])->name('review');
        Route::get('/{application}', [HousingApplicationController::class, 'viewApplication'])->name('view');
        Route::post('/{application}/approve', [HousingApplicationController::class, 'approveApplication'])->name('approve');
        Route::post('/{application}/deny', [HousingApplicationController::class, 'denyApplication'])->name('deny');
        Route::post('/{application}/waitlist', [HousingApplicationController::class, 'waitlistApplication'])->name('waitlist');
        Route::get('/settings', [HousingApplicationController::class, 'applicationSettings'])->name('settings');
        Route::put('/settings', [HousingApplicationController::class, 'updateSettings'])->name('settings.update');
        Route::get('/periods', [HousingApplicationController::class, 'applicationPeriods'])->name('periods');
        Route::post('/period', [HousingApplicationController::class, 'createPeriod'])->name('period.create');
    });
    
    // Waitlist Management
    Route::prefix('waitlist')->name('waitlist.')->group(function () {
        Route::get('/', [HousingController::class, 'waitlistManagement'])->name('index');
        Route::get('/rankings', [HousingController::class, 'waitlistRankings'])->name('rankings');
        Route::post('/reorder', [HousingController::class, 'reorderWaitlist'])->name('reorder');
        Route::post('/offer/{waitlist}', [HousingController::class, 'makeOffer'])->name('offer');
        Route::post('/clear', [HousingController::class, 'clearWaitlist'])->name('clear');
    });
    
    // Contract Management
    Route::prefix('contracts')->name('contracts.')->group(function () {
        Route::get('/', [HousingController::class, 'contracts'])->name('index');
        Route::get('/templates', [HousingController::class, 'contractTemplates'])->name('templates');
        Route::post('/template', [HousingController::class, 'createTemplate'])->name('template.create');
        Route::get('/{contract}', [HousingController::class, 'viewContract'])->name('view');
        Route::post('/generate', [HousingController::class, 'generateContracts'])->name('generate');
        Route::post('/{contract}/cancel', [HousingController::class, 'cancelContract'])->name('cancel');
        Route::get('/cancellations', [HousingController::class, 'cancellationRequests'])->name('cancellations');
        Route::post('/cancellation/{request}/approve', [HousingController::class, 'approveCancellation'])->name('cancellation.approve');
    });
    
    // Maintenance Administration
    Route::prefix('maintenance')->name('maintenance.')->group(function () {
        Route::get('/', [HousingMaintenanceController::class, 'adminDashboard'])->name('index');
        Route::get('/requests', [HousingMaintenanceController::class, 'allRequests'])->name('requests');
        Route::get('/request/{request}', [HousingMaintenanceController::class, 'adminViewRequest'])->name('request.view');
        Route::post('/request/{request}/assign', [HousingMaintenanceController::class, 'assignRequest'])->name('request.assign');
        Route::post('/request/{request}/status', [HousingMaintenanceController::class, 'updateStatus'])->name('request.status');
        Route::post('/request/{request}/complete', [HousingMaintenanceController::class, 'completeRequest'])->name('request.complete');
        Route::get('/work-orders', [HousingMaintenanceController::class, 'workOrders'])->name('work-orders');
        Route::post('/work-order', [HousingMaintenanceController::class, 'createWorkOrder'])->name('work-order.create');
        Route::get('/preventive', [HousingMaintenanceController::class, 'preventiveMaintenance'])->name('preventive');
        Route::post('/preventive/schedule', [HousingMaintenanceController::class, 'schedulePreventive'])->name('preventive.schedule');
    });
    
    // Financial Management
    Route::prefix('financial')->name('financial.')->group(function () {
        Route::get('/', [HousingController::class, 'financialDashboard'])->name('index');
        Route::get('/rates', [HousingController::class, 'rateManagement'])->name('rates');
        Route::put('/rates', [HousingController::class, 'updateRates'])->name('rates.update');
        Route::get('/billing', [HousingController::class, 'billingManagement'])->name('billing');
        Route::post('/billing/generate', [HousingController::class, 'generateBilling'])->name('billing.generate');
        Route::get('/damages', [HousingController::class, 'damageCharges'])->name('damages');
        Route::post('/damage/assess', [HousingController::class, 'assessDamage'])->name('damage.assess');
        Route::get('/refunds', [HousingController::class, 'refundManagement'])->name('refunds');
        Route::post('/refund/{refund}/process', [HousingController::class, 'processRefund'])->name('refund.process');
        Route::get('/reports', [HousingController::class, 'financialReports'])->name('reports');
    });
    
    // Reports & Analytics
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [HousingController::class, 'reportsHub'])->name('index');
        Route::get('/occupancy', [HousingController::class, 'occupancyReports'])->name('occupancy');
        Route::get('/retention', [HousingController::class, 'retentionReport'])->name('retention');
        Route::get('/demographics', [HousingController::class, 'demographicsReport'])->name('demographics');
        Route::get('/maintenance', [HousingMaintenanceController::class, 'maintenanceReports'])->name('maintenance');
        Route::get('/incidents', [ResidenceLifeController::class, 'incidentReports'])->name('incidents');
        Route::get('/financial', [HousingController::class, 'financialAnalysis'])->name('financial');
        Route::get('/satisfaction', [HousingController::class, 'satisfactionReport'])->name('satisfaction');
        Route::post('/custom', [HousingController::class, 'generateCustomReport'])->name('custom');
        Route::post('/export', [HousingController::class, 'exportReport'])->name('export');
    });
    
    // Settings & Configuration
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [HousingController::class, 'settings'])->name('index');
        Route::put('/general', [HousingController::class, 'updateGeneralSettings'])->name('general');
        Route::get('/policies', [HousingController::class, 'policyManagement'])->name('policies');
        Route::put('/policy/{policy}', [HousingController::class, 'updatePolicy'])->name('policy.update');
        Route::get('/notifications', [HousingController::class, 'notificationSettings'])->name('notifications');
        Route::put('/notifications', [HousingController::class, 'updateNotifications'])->name('notifications.update');
        Route::get('/email-templates', [HousingController::class, 'emailTemplates'])->name('email-templates');
        Route::put('/email-template/{template}', [HousingController::class, 'updateEmailTemplate'])->name('email-template.update');
    });
});