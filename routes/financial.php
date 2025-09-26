<?php
/**
 * IntelliCampus Financial Management Routes
 * 
 * Routes for financial accounts, payments, billing, and financial aid.
 * These routes are automatically prefixed with 'financial' and named with 'financial.'
 * Base middleware: 'web', 'auth'
 */

use App\Http\Controllers\FinancialController;
use Illuminate\Support\Facades\Route;

// ============================================================
// STUDENT FINANCIAL PORTAL
// ============================================================
Route::prefix('student')->name('student.')->middleware(['verified', 'role:student'])->group(function () {
    // Student Financial Dashboard
    Route::get('/', [FinancialController::class, 'studentDashboard'])->name('dashboard');
    Route::get('/summary', [FinancialController::class, 'accountSummary'])->name('summary');
    
    // Account Information
    Route::get('/account', [FinancialController::class, 'studentAccount'])->name('account');
    Route::get('/balance', [FinancialController::class, 'currentBalance'])->name('balance');
    Route::get('/activity', [FinancialController::class, 'accountActivity'])->name('activity');
    Route::get('/holds', [FinancialController::class, 'financialHolds'])->name('holds');
    
    // Statements & Bills
    Route::get('/statement', [FinancialController::class, 'currentStatement'])->name('statement');
    Route::get('/statement/{term}', [FinancialController::class, 'termStatement'])->name('statement.term');
    Route::get('/statements', [FinancialController::class, 'statementHistory'])->name('statements');
    Route::get('/statement/download/{id}', [FinancialController::class, 'downloadStatement'])->name('statement.download');
    Route::get('/bill', [FinancialController::class, 'currentBill'])->name('bill');
    Route::get('/bill/print', [FinancialController::class, 'printBill'])->name('bill.print');
    
    // Charges & Credits Details
    Route::get('/charges', [FinancialController::class, 'detailedCharges'])->name('charges');
    Route::get('/charges/{term}', [FinancialController::class, 'termCharges'])->name('charges.term');
    Route::get('/charge/{id}', [FinancialController::class, 'chargeDetails'])->name('charge.details');
    Route::get('/credits', [FinancialController::class, 'accountCredits'])->name('credits');
    
    // Make Payment
    Route::get('/pay', [FinancialController::class, 'makePayment'])->name('pay');
    Route::post('/pay/calculate', [FinancialController::class, 'calculatePayment'])->name('pay.calculate');
    Route::post('/pay/process', [FinancialController::class, 'processPayment'])->name('pay.process');
    Route::get('/pay/confirm/{payment}', [FinancialController::class, 'paymentConfirmation'])->name('pay.confirm');
    Route::get('/pay/receipt/{payment}', [FinancialController::class, 'paymentReceipt'])->name('pay.receipt');
    
    // Payment Methods
    Route::get('/payment-methods', [FinancialController::class, 'paymentMethods'])->name('payment-methods');
    Route::get('/payment-method/add', [FinancialController::class, 'addPaymentMethodForm'])->name('payment-method.add');
    Route::post('/payment-method', [FinancialController::class, 'storePaymentMethod'])->name('payment-method.store');
    Route::delete('/payment-method/{id}', [FinancialController::class, 'deletePaymentMethod'])->name('payment-method.delete');
    Route::post('/payment-method/{id}/default', [FinancialController::class, 'setDefaultPaymentMethod'])->name('payment-method.default');
    
    // Payment History
    Route::get('/payments', [FinancialController::class, 'paymentHistory'])->name('payments');
    Route::get('/payment/{id}', [FinancialController::class, 'paymentDetails'])->name('payment.details');
    Route::get('/payments/export', [FinancialController::class, 'exportPaymentHistory'])->name('payments.export');
    
    // Payment Plans
    Route::get('/payment-plans', [FinancialController::class, 'availablePaymentPlans'])->name('payment-plans');
    Route::get('/payment-plan/enroll', [FinancialController::class, 'enrollmentForm'])->name('payment-plan.enroll');
    Route::post('/payment-plan/enroll', [FinancialController::class, 'enrollInPaymentPlan'])->name('payment-plan.submit');
    Route::get('/payment-plan/current', [FinancialController::class, 'currentPaymentPlan'])->name('payment-plan.current');
    Route::get('/payment-plan/{id}', [FinancialController::class, 'paymentPlanDetails'])->name('payment-plan.details');
    Route::post('/payment-plan/{id}/pay', [FinancialController::class, 'payInstallment'])->name('payment-plan.pay');
    Route::get('/payment-plan/{id}/schedule', [FinancialController::class, 'installmentSchedule'])->name('payment-plan.schedule');
    
    // Financial Aid
    Route::get('/financial-aid', [FinancialController::class, 'financialAidOverview'])->name('aid');
    Route::get('/aid/status', [FinancialController::class, 'aidStatus'])->name('aid.status');
    Route::get('/aid/awards', [FinancialController::class, 'aidAwards'])->name('aid.awards');
    Route::get('/aid/award/{id}', [FinancialController::class, 'awardDetails'])->name('aid.award');
    Route::post('/aid/award/{id}/accept', [FinancialController::class, 'acceptAward'])->name('aid.accept');
    Route::post('/aid/award/{id}/decline', [FinancialController::class, 'declineAward'])->name('aid.decline');
    Route::post('/aid/award/{id}/adjust', [FinancialController::class, 'requestAdjustment'])->name('aid.adjust');
    Route::get('/aid/requirements', [FinancialController::class, 'aidRequirements'])->name('aid.requirements');
    Route::get('/aid/documents', [FinancialController::class, 'aidDocuments'])->name('aid.documents');
    Route::post('/aid/document/upload', [FinancialController::class, 'uploadAidDocument'])->name('aid.document.upload');
    
    // Scholarships
    Route::get('/scholarships', [FinancialController::class, 'myScholarships'])->name('scholarships');
    Route::get('/scholarships/available', [FinancialController::class, 'availableScholarships'])->name('scholarships.available');
    Route::get('/scholarship/{id}', [FinancialController::class, 'scholarshipDetails'])->name('scholarship.details');
    Route::post('/scholarship/{id}/apply', [FinancialController::class, 'applyForScholarship'])->name('scholarship.apply');
    Route::get('/scholarship/{id}/status', [FinancialController::class, 'scholarshipStatus'])->name('scholarship.status');
    
    // Refunds
    Route::get('/refunds', [FinancialController::class, 'refundHistory'])->name('refunds');
    Route::get('/refund/request', [FinancialController::class, 'refundRequestForm'])->name('refund.request');
    Route::post('/refund/request', [FinancialController::class, 'submitRefundRequest'])->name('refund.submit');
    Route::get('/refund/{id}', [FinancialController::class, 'refundStatus'])->name('refund.status');
    Route::get('/refund/{id}/details', [FinancialController::class, 'refundDetails'])->name('refund.details');
    
    // Tax Documents
    Route::get('/tax-documents', [FinancialController::class, 'taxDocuments'])->name('tax');
    Route::get('/1098t/{year}', [FinancialController::class, 'form1098T'])->name('1098t');
    Route::get('/1098t/{year}/download', [FinancialController::class, 'download1098T'])->name('1098t.download');
    Route::post('/1098t/consent', [FinancialController::class, 'electronic1098TConsent'])->name('1098t.consent');
    
    // Third-Party Access
    Route::get('/third-party', [FinancialController::class, 'thirdPartyAccess'])->name('third-party');
    Route::post('/third-party/grant', [FinancialController::class, 'grantThirdPartyAccess'])->name('third-party.grant');
    Route::delete('/third-party/{id}', [FinancialController::class, 'revokeThirdPartyAccess'])->name('third-party.revoke');
});

// ============================================================
// ADMINISTRATIVE FINANCIAL MANAGEMENT
// ============================================================
Route::prefix('admin')->name('admin.')->middleware(['role:bursar,financial-admin,admin'])->group(function () {
    
    // Financial Dashboard
    Route::get('/', [FinancialController::class, 'adminDashboard'])->name('dashboard');
    Route::get('/overview', [FinancialController::class, 'financialOverview'])->name('overview');
    Route::get('/metrics', [FinancialController::class, 'keyMetrics'])->name('metrics');
    
    // Student Accounts Management
    Route::prefix('accounts')->name('accounts.')->group(function () {
        Route::get('/', [FinancialController::class, 'studentAccounts'])->name('index');
        Route::get('/search', [FinancialController::class, 'searchAccounts'])->name('search');
        Route::get('/{student}', [FinancialController::class, 'viewStudentAccount'])->name('view');
        Route::get('/{student}/statement', [FinancialController::class, 'generateStatement'])->name('statement');
        Route::get('/{student}/activity', [FinancialController::class, 'accountActivity'])->name('activity');
        Route::get('/{student}/history', [FinancialController::class, 'accountHistory'])->name('history');
        Route::post('/{student}/hold', [FinancialController::class, 'placeHold'])->name('hold');
        Route::delete('/{student}/hold/{hold}', [FinancialController::class, 'releaseHold'])->name('release-hold');
        Route::post('/{student}/note', [FinancialController::class, 'addAccountNote'])->name('note');
        Route::get('/aging', [FinancialController::class, 'agingAccounts'])->name('aging');
        Route::get('/delinquent', [FinancialController::class, 'delinquentAccounts'])->name('delinquent');
    });
    
    // Billing Management
    Route::prefix('billing')->name('billing.')->group(function () {
        Route::get('/', [FinancialController::class, 'billingDashboard'])->name('index');
        Route::get('/items', [FinancialController::class, 'billingItems'])->name('items');
        Route::get('/generate', [FinancialController::class, 'generateBillsForm'])->name('generate');
        Route::post('/generate', [FinancialController::class, 'generateBills'])->name('generate.process');
        Route::post('/generate-term-fees', [FinancialController::class, 'generateTermFees'])->name('generate-term');
        
        // Charge Management
        Route::get('/charges', [FinancialController::class, 'chargeManagement'])->name('charges');
        Route::post('/charge', [FinancialController::class, 'addCharge'])->name('charge.add');
        Route::put('/charge/{id}', [FinancialController::class, 'updateCharge'])->name('charge.update');
        Route::delete('/charge/{id}', [FinancialController::class, 'deleteCharge'])->name('charge.delete');
        Route::post('/charge/{id}/waive', [FinancialController::class, 'waiveCharge'])->name('charge.waive');
        Route::post('/charges/bulk', [FinancialController::class, 'bulkCharges'])->name('charges.bulk');
        
        // Credit Management
        Route::get('/credits', [FinancialController::class, 'creditManagement'])->name('credits');
        Route::post('/credit', [FinancialController::class, 'addCredit'])->name('credit.add');
        Route::put('/credit/{id}', [FinancialController::class, 'updateCredit'])->name('credit.update');
        Route::delete('/credit/{id}', [FinancialController::class, 'deleteCredit'])->name('credit.delete');
        
        // Adjustment Management
        Route::get('/adjustments', [FinancialController::class, 'adjustments'])->name('adjustments');
        Route::post('/adjustment', [FinancialController::class, 'makeAdjustment'])->name('adjustment.make');
        Route::post('/adjustment/{id}/approve', [FinancialController::class, 'approveAdjustment'])->name('adjustment.approve');
        Route::post('/adjustment/{id}/deny', [FinancialController::class, 'denyAdjustment'])->name('adjustment.deny');
        
        // Late Fees
        Route::get('/late-fees', [FinancialController::class, 'lateFeeManagement'])->name('late-fees');
        Route::post('/late-fees/calculate', [FinancialController::class, 'calculateLateFees'])->name('late-fees.calculate');
        Route::post('/late-fees/apply', [FinancialController::class, 'applyLateFees'])->name('late-fees.apply');
        Route::post('/late-fees/waive/{id}', [FinancialController::class, 'waiveLateFee'])->name('late-fees.waive');
    });
    
    // Fee Structure Management
    Route::prefix('fees')->name('fees.')->group(function () {
        Route::get('/', [FinancialController::class, 'feeStructure'])->name('index');
        Route::get('/create', [FinancialController::class, 'createFee'])->name('create');
        Route::post('/', [FinancialController::class, 'storeFee'])->name('store');
        Route::get('/{fee}/edit', [FinancialController::class, 'editFee'])->name('edit');
        Route::put('/{fee}', [FinancialController::class, 'updateFee'])->name('update');
        Route::delete('/{fee}', [FinancialController::class, 'deleteFee'])->name('delete');
        Route::get('/schedules', [FinancialController::class, 'feeSchedules'])->name('schedules');
        Route::post('/schedule', [FinancialController::class, 'createFeeSchedule'])->name('schedule.create');
        Route::get('/categories', [FinancialController::class, 'feeCategories'])->name('categories');
        Route::post('/category', [FinancialController::class, 'createFeeCategory'])->name('category.create');
        Route::get('/export', [FinancialController::class, 'exportFeeStructure'])->name('export');
        Route::post('/import', [FinancialController::class, 'importFeeStructure'])->name('import');
    });
    
    // Payment Processing
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [FinancialController::class, 'paymentManagement'])->name('index');
        Route::get('/pending', [FinancialController::class, 'pendingPayments'])->name('pending');
        Route::get('/recent', [FinancialController::class, 'recentPayments'])->name('recent');
        Route::get('/{payment}', [FinancialController::class, 'viewPayment'])->name('view');
        Route::post('/manual', [FinancialController::class, 'processManualPayment'])->name('manual');
        Route::post('/{payment}/verify', [FinancialController::class, 'verifyPayment'])->name('verify');
        Route::post('/{payment}/void', [FinancialController::class, 'voidPayment'])->name('void');
        Route::get('/batch', [FinancialController::class, 'batchPayments'])->name('batch');
        Route::post('/batch/process', [FinancialController::class, 'processBatchPayments'])->name('batch.process');
        Route::get('/reconciliation', [FinancialController::class, 'paymentReconciliation'])->name('reconciliation');
        Route::post('/reconcile', [FinancialController::class, 'reconcilePayments'])->name('reconcile');
    });
    
    // Refund Management
    Route::prefix('refunds')->name('refunds.')->group(function () {
        Route::get('/', [FinancialController::class, 'refundManagement'])->name('index');
        Route::get('/pending', [FinancialController::class, 'pendingRefunds'])->name('pending');
        Route::get('/{refund}', [FinancialController::class, 'viewRefund'])->name('view');
        Route::post('/{refund}/approve', [FinancialController::class, 'approveRefund'])->name('approve');
        Route::post('/{refund}/deny', [FinancialController::class, 'denyRefund'])->name('deny');
        Route::post('/{refund}/process', [FinancialController::class, 'processRefund'])->name('process');
        Route::post('/create', [FinancialController::class, 'createRefund'])->name('create');
        Route::get('/batch', [FinancialController::class, 'batchRefunds'])->name('batch');
        Route::post('/batch/process', [FinancialController::class, 'processBatchRefunds'])->name('batch.process');
    });
    
    // Payment Plan Administration
    Route::prefix('payment-plans')->name('payment-plans.')->group(function () {
        Route::get('/', [FinancialController::class, 'paymentPlanManagement'])->name('index');
        Route::get('/templates', [FinancialController::class, 'planTemplates'])->name('templates');
        Route::post('/template', [FinancialController::class, 'createTemplate'])->name('template.create');
        Route::get('/active', [FinancialController::class, 'activePaymentPlans'])->name('active');
        Route::get('/pending', [FinancialController::class, 'pendingPaymentPlans'])->name('pending');
        Route::get('/{plan}', [FinancialController::class, 'viewPaymentPlan'])->name('view');
        Route::post('/{plan}/approve', [FinancialController::class, 'approvePaymentPlan'])->name('approve');
        Route::post('/{plan}/reject', [FinancialController::class, 'rejectPaymentPlan'])->name('reject');
        Route::post('/{plan}/modify', [FinancialController::class, 'modifyPaymentPlan'])->name('modify');
        Route::post('/{plan}/cancel', [FinancialController::class, 'cancelPaymentPlan'])->name('cancel');
        Route::get('/defaulted', [FinancialController::class, 'defaultedPlans'])->name('defaulted');
        Route::post('/reminder/{plan}', [FinancialController::class, 'sendPaymentReminder'])->name('reminder');
    });
    
    // Financial Aid Administration
    Route::prefix('financial-aid')->name('aid.')->group(function () {
        Route::get('/', [FinancialController::class, 'financialAidAdmin'])->name('index');
        Route::get('/applications', [FinancialController::class, 'aidApplications'])->name('applications');
        Route::get('/application/{id}', [FinancialController::class, 'viewAidApplication'])->name('application.view');
        Route::post('/application/{id}/review', [FinancialController::class, 'reviewApplication'])->name('application.review');
        Route::get('/awards', [FinancialController::class, 'aidAwardsManagement'])->name('awards');
        Route::post('/award', [FinancialController::class, 'createAward'])->name('award.create');
        Route::put('/award/{id}', [FinancialController::class, 'updateAward'])->name('award.update');
        Route::post('/award/{id}/disburse', [FinancialController::class, 'disburseAid'])->name('award.disburse');
        Route::get('/packages', [FinancialController::class, 'aidPackages'])->name('packages');
        Route::post('/package', [FinancialController::class, 'createPackage'])->name('package.create');
        Route::get('/verification', [FinancialController::class, 'aidVerification'])->name('verification');
        Route::post('/verification/{id}', [FinancialController::class, 'verifyAid'])->name('verify');
        Route::get('/sap', [FinancialController::class, 'sapMonitoring'])->name('sap');
        Route::post('/sap/calculate', [FinancialController::class, 'calculateSAP'])->name('sap.calculate');
    });
    
    // Scholarship Administration
    Route::prefix('scholarships')->name('scholarships.')->group(function () {
        Route::get('/', [FinancialController::class, 'scholarshipAdmin'])->name('index');
        Route::get('/create', [FinancialController::class, 'createScholarship'])->name('create');
        Route::post('/', [FinancialController::class, 'storeScholarship'])->name('store');
        Route::get('/{scholarship}/edit', [FinancialController::class, 'editScholarship'])->name('edit');
        Route::put('/{scholarship}', [FinancialController::class, 'updateScholarship'])->name('update');
        Route::delete('/{scholarship}', [FinancialController::class, 'deleteScholarship'])->name('delete');
        Route::get('/{scholarship}/recipients', [FinancialController::class, 'scholarshipRecipients'])->name('recipients');
        Route::post('/{scholarship}/award', [FinancialController::class, 'awardScholarship'])->name('award');
        Route::get('/applications', [FinancialController::class, 'scholarshipApplications'])->name('applications');
        Route::post('/application/{id}/review', [FinancialController::class, 'reviewScholarshipApplication'])->name('review');
    });
    
    // Third-Party Billing
    Route::prefix('third-party')->name('third-party.')->group(function () {
        Route::get('/', [FinancialController::class, 'thirdPartyBilling'])->name('index');
        Route::get('/sponsors', [FinancialController::class, 'sponsors'])->name('sponsors');
        Route::post('/sponsor', [FinancialController::class, 'addSponsor'])->name('sponsor.add');
        Route::get('/sponsor/{id}', [FinancialController::class, 'viewSponsor'])->name('sponsor.view');
        Route::put('/sponsor/{id}', [FinancialController::class, 'updateSponsor'])->name('sponsor.update');
        Route::get('/authorizations', [FinancialController::class, 'sponsorAuthorizations'])->name('authorizations');
        Route::post('/authorization', [FinancialController::class, 'createAuthorization'])->name('authorization.create');
        Route::post('/invoice/{sponsor}', [FinancialController::class, 'generateSponsorInvoice'])->name('invoice');
        Route::get('/invoices', [FinancialController::class, 'sponsorInvoices'])->name('invoices');
    });
    
    // Collections Management
    Route::prefix('collections')->name('collections.')->group(function () {
        Route::get('/', [FinancialController::class, 'collectionsManagement'])->name('index');
        Route::get('/accounts', [FinancialController::class, 'collectionAccounts'])->name('accounts');
        Route::post('/account/{id}/assign', [FinancialController::class, 'assignToCollections'])->name('assign');
        Route::post('/account/{id}/release', [FinancialController::class, 'releaseFromCollections'])->name('release');
        Route::get('/agencies', [FinancialController::class, 'collectionAgencies'])->name('agencies');
        Route::post('/agency/{id}/send', [FinancialController::class, 'sendToAgency'])->name('agency.send');
        Route::get('/write-offs', [FinancialController::class, 'writeOffs'])->name('write-offs');
        Route::post('/write-off', [FinancialController::class, 'createWriteOff'])->name('write-off.create');
        Route::post('/write-off/{id}/approve', [FinancialController::class, 'approveWriteOff'])->name('write-off.approve');
    });
    
    // Reports & Analytics
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [FinancialController::class, 'reportsHub'])->name('index');
        Route::get('/revenue', [FinancialController::class, 'revenueReport'])->name('revenue');
        Route::get('/receivables', [FinancialController::class, 'receivablesReport'])->name('receivables');
        Route::get('/aging', [FinancialController::class, 'agingReport'])->name('aging');
        Route::get('/collections', [FinancialController::class, 'collectionsReport'])->name('collections');
        Route::get('/financial-aid', [FinancialController::class, 'financialAidReport'])->name('aid');
        Route::get('/payment-plans', [FinancialController::class, 'paymentPlansReport'])->name('payment-plans');
        Route::get('/daily-cash', [FinancialController::class, 'dailyCashReport'])->name('daily-cash');
        Route::get('/term-summary', [FinancialController::class, 'termSummaryReport'])->name('term-summary');
        Route::get('/gl-interface', [FinancialController::class, 'glInterfaceReport'])->name('gl-interface');
        Route::get('/1098t-report', [FinancialController::class, 'form1098TReport'])->name('1098t');
        Route::get('/custom', [FinancialController::class, 'customReportBuilder'])->name('custom');
        Route::post('/generate', [FinancialController::class, 'generateReport'])->name('generate');
        Route::post('/export', [FinancialController::class, 'exportReport'])->name('export');
        Route::post('/schedule', [FinancialController::class, 'scheduleReport'])->name('schedule');
    });
    
    // System Configuration
    Route::prefix('settings')->name('settings.')->middleware('role:admin')->group(function () {
        Route::get('/', [FinancialController::class, 'financialSettings'])->name('index');
        Route::put('/general', [FinancialController::class, 'updateGeneralSettings'])->name('general');
        Route::get('/payment-gateways', [FinancialController::class, 'paymentGateways'])->name('gateways');
        Route::post('/gateway', [FinancialController::class, 'configureGateway'])->name('gateway.configure');
        Route::get('/accounting-codes', [FinancialController::class, 'accountingCodes'])->name('codes');
        Route::post('/accounting-code', [FinancialController::class, 'createAccountingCode'])->name('code.create');
        Route::get('/fiscal-years', [FinancialController::class, 'fiscalYears'])->name('fiscal-years');
        Route::post('/fiscal-year', [FinancialController::class, 'createFiscalYear'])->name('fiscal-year.create');
        Route::get('/gl-mapping', [FinancialController::class, 'glMapping'])->name('gl-mapping');
        Route::put('/gl-mapping', [FinancialController::class, 'updateGLMapping'])->name('gl-mapping.update');
    });
});

// ============================================================
// PAYMENT GATEWAY CALLBACKS (No Auth - Webhooks)
// ============================================================
Route::prefix('webhooks')->name('webhooks.')->withoutMiddleware(['auth'])->group(function () {
    Route::post('/stripe', [FinancialController::class, 'stripeWebhook'])->name('stripe');
    Route::post('/paypal', [FinancialController::class, 'paypalWebhook'])->name('paypal');
    Route::post('/authorize', [FinancialController::class, 'authorizeWebhook'])->name('authorize');
    Route::post('/square', [FinancialController::class, 'squareWebhook'])->name('square');
    Route::any('/payment-callback', [FinancialController::class, 'paymentCallback'])->name('callback');
    Route::post('/payment-notification', [FinancialController::class, 'paymentNotification'])->name('notification');
});

// ============================================================
// PUBLIC PAYMENT PAGES (For external payers)
// ============================================================
Route::prefix('pay')->name('pay.')->withoutMiddleware(['auth'])->group(function () {
    Route::get('/guest', [FinancialController::class, 'guestPaymentForm'])->name('guest');
    Route::post('/guest/lookup', [FinancialController::class, 'lookupAccount'])->name('guest.lookup');
    Route::post('/guest/process', [FinancialController::class, 'processGuestPayment'])->name('guest.process');
    Route::get('/confirmation/{reference}', [FinancialController::class, 'paymentConfirmation'])->name('confirmation');
    Route::get('/receipt/{reference}', [FinancialController::class, 'guestReceipt'])->name('receipt');
});