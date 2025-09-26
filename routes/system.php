<?php
/**
 * IntelliCampus System Configuration Routes
 * 
 * Routes for system-wide configuration and settings.
 * These routes are automatically prefixed with 'system' and named with 'system.'
 * Applied middleware: 'web', 'auth', 'role:admin,system-administrator'
 */

use App\Http\Controllers\SystemConfigurationController;
use Illuminate\Support\Facades\Route;

// ============================================================
// SYSTEM DASHBOARD
// ============================================================
Route::get('/', [SystemConfigurationController::class, 'dashboard'])->name('index');
Route::get('/dashboard', [SystemConfigurationController::class, 'dashboard'])->name('dashboard');
Route::get('/health', [SystemConfigurationController::class, 'healthCheck'])->name('health');
Route::get('/status', [SystemConfigurationController::class, 'systemStatus'])->name('status');
Route::get('/monitoring', [SystemConfigurationController::class, 'monitoring'])->name('monitoring');

// ============================================================
// GENERAL SETTINGS
// ============================================================
Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/', [SystemConfigurationController::class, 'settings'])->name('index');
    Route::get('/general', [SystemConfigurationController::class, 'generalSettings'])->name('general');
    Route::put('/general', [SystemConfigurationController::class, 'updateGeneralSettings'])->name('general.update');
    Route::get('/defaults', [SystemConfigurationController::class, 'defaultSettings'])->name('defaults');
    Route::put('/defaults', [SystemConfigurationController::class, 'updateDefaults'])->name('defaults.update');
    Route::post('/reset', [SystemConfigurationController::class, 'resetToDefaults'])->name('reset');
    Route::get('/export', [SystemConfigurationController::class, 'exportSettings'])->name('export');
    Route::post('/import', [SystemConfigurationController::class, 'importSettings'])->name('import');
});

// ============================================================
// INSTITUTION CONFIGURATION
// ============================================================
Route::prefix('institution')->name('institution.')->group(function () {
    Route::get('/', [SystemConfigurationController::class, 'institution'])->name('index');
    Route::put('/', [SystemConfigurationController::class, 'updateInstitution'])->name('update');
    Route::get('/profile', [SystemConfigurationController::class, 'institutionProfile'])->name('profile');
    Route::put('/profile', [SystemConfigurationController::class, 'updateProfile'])->name('profile.update');
    Route::post('/logo', [SystemConfigurationController::class, 'uploadLogo'])->name('logo');
    Route::post('/branding', [SystemConfigurationController::class, 'updateBranding'])->name('branding');
    Route::get('/campuses', [SystemConfigurationController::class, 'campuses'])->name('campuses');
    Route::post('/campus', [SystemConfigurationController::class, 'addCampus'])->name('campus.add');
    Route::put('/campus/{campus}', [SystemConfigurationController::class, 'updateCampus'])->name('campus.update');
    Route::delete('/campus/{campus}', [SystemConfigurationController::class, 'deleteCampus'])->name('campus.delete');
});

// ============================================================
// ACADEMIC CONFIGURATION
// ============================================================
Route::prefix('academic')->name('academic.')->group(function () {
    Route::get('/', [SystemConfigurationController::class, 'academic'])->name('index');
    Route::put('/{type}', [SystemConfigurationController::class, 'updateAcademicConfig'])->name('update');
});

// ============================================================
// ACADEMIC CALENDAR
// ============================================================
Route::prefix('calendar')->name('calendar.')->group(function () {
    Route::get('/', [SystemConfigurationController::class, 'calendar'])->name('index');
    Route::get('/create', [SystemConfigurationController::class, 'createCalendar'])->name('create');
    Route::post('/', [SystemConfigurationController::class, 'storeCalendar'])->name('store');
    Route::get('/{calendar}/events', [SystemConfigurationController::class, 'calendarEvents'])->name('events');
    Route::post('/{calendar}/events', [SystemConfigurationController::class, 'storeCalendarEvent'])->name('events.store');
    Route::get('/terms', [SystemConfigurationController::class, 'terms'])->name('terms');
    Route::post('/term', [SystemConfigurationController::class, 'createTerm'])->name('term.create');
    Route::put('/term/{term}', [SystemConfigurationController::class, 'updateTerm'])->name('term.update');
    Route::delete('/term/{term}', [SystemConfigurationController::class, 'deleteTerm'])->name('term.delete');
    Route::post('/term/{term}/activate', [SystemConfigurationController::class, 'activateTerm'])->name('term.activate');
    Route::get('/holidays', [SystemConfigurationController::class, 'holidays'])->name('holidays');
    Route::post('/holiday', [SystemConfigurationController::class, 'addHoliday'])->name('holiday.add');
});

// ============================================================
// MODULE MANAGEMENT
// ============================================================
Route::prefix('modules')->name('modules.')->group(function () {
    Route::get('/', [SystemConfigurationController::class, 'modules'])->name('index');
    Route::get('/{module}', [SystemConfigurationController::class, 'moduleDetails'])->name('details');
    Route::post('/{module}/toggle', [SystemConfigurationController::class, 'toggleModule'])->name('toggle');
    Route::get('/{module}/settings', [SystemConfigurationController::class, 'moduleSettings'])->name('settings');
    Route::put('/{module}/settings', [SystemConfigurationController::class, 'updateModuleSettings'])->name('settings.update');
    Route::get('/{module}/dependencies', [SystemConfigurationController::class, 'moduleDependencies'])->name('dependencies');
    Route::post('/{module}/install', [SystemConfigurationController::class, 'installModule'])->name('install');
    Route::post('/{module}/uninstall', [SystemConfigurationController::class, 'uninstallModule'])->name('uninstall');
    Route::get('/available', [SystemConfigurationController::class, 'availableModules'])->name('available');
    Route::post('/install-new', [SystemConfigurationController::class, 'installNewModule'])->name('install-new');
});

// ============================================================
// EMAIL CONFIGURATION
// ============================================================
Route::prefix('email')->name('email.')->group(function () {
    Route::get('/', [SystemConfigurationController::class, 'emailTemplates'])->name('index');
    Route::get('/{template}/edit', [SystemConfigurationController::class, 'editEmailTemplate'])->name('edit');
    Route::put('/{template}', [SystemConfigurationController::class, 'updateEmailTemplate'])->name('update');
    Route::get('/settings', [SystemConfigurationController::class, 'emailSettings'])->name('settings');
    Route::put('/settings', [SystemConfigurationController::class, 'updateEmailSettings'])->name('settings.update');
    Route::post('/test', [SystemConfigurationController::class, 'testEmailConfiguration'])->name('test');
    Route::get('/templates', [SystemConfigurationController::class, 'emailTemplates'])->name('templates');
    Route::get('/template/{template}/edit', [SystemConfigurationController::class, 'editTemplate'])->name('template.edit');
    Route::put('/template/{template}', [SystemConfigurationController::class, 'updateTemplate'])->name('template.update');
    Route::post('/template/{template}/preview', [SystemConfigurationController::class, 'previewTemplate'])->name('template.preview');
    Route::post('/template/{template}/test', [SystemConfigurationController::class, 'testTemplate'])->name('template.test');
    Route::get('/logs', [SystemConfigurationController::class, 'emailLogs'])->name('logs');
    Route::get('/queue', [SystemConfigurationController::class, 'emailQueue'])->name('queue');
    Route::post('/queue/process', [SystemConfigurationController::class, 'processEmailQueue'])->name('queue.process');
});

// ============================================================
// SECURITY CONFIGURATION
// ============================================================
Route::prefix('security')->name('security.')->group(function () {
    Route::get('/', [SystemConfigurationController::class, 'security'])->name('index');
    Route::get('/settings', [SystemConfigurationController::class, 'securitySettings'])->name('settings');
    Route::put('/settings', [SystemConfigurationController::class, 'updateSecuritySettings'])->name('settings.update');
    
    // Password Policy
    Route::get('/password-policy', [SystemConfigurationController::class, 'passwordPolicy'])->name('password-policy');
    Route::put('/password-policy', [SystemConfigurationController::class, 'updatePasswordPolicy'])->name('password-policy.update');
    Route::post('/password/force-reset', [SystemConfigurationController::class, 'forcePasswordReset'])->name('password.force-reset');
    
    // Two-Factor Authentication
    Route::get('/2fa', [SystemConfigurationController::class, 'twoFactorSettings'])->name('2fa');
    Route::put('/2fa', [SystemConfigurationController::class, 'update2FASettings'])->name('2fa.update');
    Route::post('/2fa/enforce', [SystemConfigurationController::class, 'enforce2FA'])->name('2fa.enforce');
    
    // Session Management
    Route::get('/sessions', [SystemConfigurationController::class, 'sessionManagement'])->name('sessions');
    Route::put('/sessions', [SystemConfigurationController::class, 'updateSessionSettings'])->name('sessions.update');
    Route::get('/active-sessions', [SystemConfigurationController::class, 'activeSessions'])->name('active-sessions');
    Route::post('/sessions/terminate', [SystemConfigurationController::class, 'terminateSessions'])->name('sessions.terminate');
    
    // IP Management
    Route::get('/ip-whitelist', [SystemConfigurationController::class, 'ipWhitelist'])->name('ip-whitelist');
    Route::post('/ip-whitelist', [SystemConfigurationController::class, 'addIPWhitelist'])->name('ip-whitelist.add');
    Route::delete('/ip-whitelist/{ip}', [SystemConfigurationController::class, 'removeIPWhitelist'])->name('ip-whitelist.remove');
    Route::get('/ip-blacklist', [SystemConfigurationController::class, 'ipBlacklist'])->name('ip-blacklist');
    Route::post('/ip-blacklist', [SystemConfigurationController::class, 'addIPBlacklist'])->name('ip-blacklist.add');
    Route::delete('/ip-blacklist/{ip}', [SystemConfigurationController::class, 'removeIPBlacklist'])->name('ip-blacklist.remove');
    
    // Security Audit
    Route::get('/audit', [SystemConfigurationController::class, 'securityAudit'])->name('audit');
    Route::post('/audit/run', [SystemConfigurationController::class, 'runSecurityAudit'])->name('audit.run');
    Route::get('/vulnerabilities', [SystemConfigurationController::class, 'vulnerabilities'])->name('vulnerabilities');
    Route::post('/vulnerability/{vulnerability}/fix', [SystemConfigurationController::class, 'fixVulnerability'])->name('vulnerability.fix');
});

// ============================================================
// CACHE MANAGEMENT
// ============================================================
Route::prefix('cache')->name('cache.')->group(function () {
    Route::get('/', [SystemConfigurationController::class, 'cacheManagement'])->name('index');
    Route::get('/status', [SystemConfigurationController::class, 'cacheStatus'])->name('status');
    Route::get('/statistics', [SystemConfigurationController::class, 'cacheStatistics'])->name('statistics');
    Route::post('/clear', [SystemConfigurationController::class, 'clearCache'])->name('clear');
    Route::post('/clear/{type}', [SystemConfigurationController::class, 'clearSpecificCache'])->name('clear.specific');
    Route::post('/warm', [SystemConfigurationController::class, 'warmCache'])->name('warm');
    Route::get('/config', [SystemConfigurationController::class, 'cacheConfig'])->name('config');
    Route::put('/config', [SystemConfigurationController::class, 'updateCacheConfig'])->name('config.update');
});

// ============================================================
// MAINTENANCE MODE
// ============================================================
Route::prefix('maintenance')->name('maintenance.')->group(function () {
    Route::get('/', [SystemConfigurationController::class, 'maintenanceMode'])->name('index');
    Route::post('/toggle', [SystemConfigurationController::class, 'toggleMaintenance'])->name('toggle');
    Route::post('/enable', [SystemConfigurationController::class, 'enableMaintenance'])->name('enable');
    Route::post('/disable', [SystemConfigurationController::class, 'disableMaintenance'])->name('disable');
    Route::get('/schedule', [SystemConfigurationController::class, 'maintenanceSchedule'])->name('schedule');
    Route::post('/schedule', [SystemConfigurationController::class, 'scheduleMaintenance'])->name('schedule.create');
    Route::put('/schedule/{schedule}', [SystemConfigurationController::class, 'updateSchedule'])->name('schedule.update');
    Route::delete('/schedule/{schedule}', [SystemConfigurationController::class, 'cancelSchedule'])->name('schedule.cancel');
    Route::get('/bypass-tokens', [SystemConfigurationController::class, 'bypassTokens'])->name('bypass-tokens');
    Route::post('/bypass-token', [SystemConfigurationController::class, 'generateBypassToken'])->name('bypass-token.generate');
    Route::delete('/bypass-token/{token}', [SystemConfigurationController::class, 'revokeBypassToken'])->name('bypass-token.revoke');
});

// The rest of the routes remain the same...
// Including: INTEGRATIONS, DATABASE, QUEUES, LOCALIZATION, FILE STORAGE, BACKUP & RESTORE, SYSTEM LOGS, SYSTEM MONITORING, SCHEDULED TASKS, UPDATES & PATCHES, LICENSING

// ============================================================
// INTEGRATION SETTINGS
// ============================================================
Route::prefix('integrations')->name('integrations.')->group(function () {
    Route::get('/', [SystemConfigurationController::class, 'integrations'])->name('index');
    Route::get('/available', [SystemConfigurationController::class, 'availableIntegrations'])->name('available');
    Route::get('/{integration}', [SystemConfigurationController::class, 'integrationDetails'])->name('details');
    Route::post('/{integration}/enable', [SystemConfigurationController::class, 'enableIntegration'])->name('enable');
    Route::post('/{integration}/disable', [SystemConfigurationController::class, 'disableIntegration'])->name('disable');
    Route::get('/{integration}/settings', [SystemConfigurationController::class, 'integrationSettings'])->name('settings');
    Route::put('/{integration}/settings', [SystemConfigurationController::class, 'updateIntegrationSettings'])->name('settings.update');
    Route::post('/{integration}/test', [SystemConfigurationController::class, 'testIntegration'])->name('test');
    Route::post('/{integration}/sync', [SystemConfigurationController::class, 'syncIntegration'])->name('sync');
    Route::get('/{integration}/logs', [SystemConfigurationController::class, 'integrationLogs'])->name('logs');
    
    // API Keys
    Route::get('/api-keys', [SystemConfigurationController::class, 'apiKeys'])->name('api-keys');
    Route::post('/api-key', [SystemConfigurationController::class, 'generateApiKey'])->name('api-key.generate');
    Route::delete('/api-key/{key}', [SystemConfigurationController::class, 'revokeApiKey'])->name('api-key.revoke');
    Route::post('/api-key/{key}/regenerate', [SystemConfigurationController::class, 'regenerateApiKey'])->name('api-key.regenerate');
});

// ============================================================
// DATABASE CONFIGURATION
// ============================================================
Route::prefix('database')->name('database.')->group(function () {
    Route::get('/', [SystemConfigurationController::class, 'database'])->name('index');
    Route::get('/status', [SystemConfigurationController::class, 'databaseStatus'])->name('status');
    Route::get('/statistics', [SystemConfigurationController::class, 'databaseStatistics'])->name('statistics');
    Route::post('/optimize', [SystemConfigurationController::class, 'optimizeDatabase'])->name('optimize');
    Route::post('/analyze', [SystemConfigurationController::class, 'analyzeDatabase'])->name('analyze');
    Route::post('/vacuum', [SystemConfigurationController::class, 'vacuumDatabase'])->name('vacuum');
    Route::get('/connections', [SystemConfigurationController::class, 'databaseConnections'])->name('connections');
    Route::get('/slow-queries', [SystemConfigurationController::class, 'slowQueries'])->name('slow-queries');
    Route::get('/indexes', [SystemConfigurationController::class, 'databaseIndexes'])->name('indexes');
    Route::post('/index', [SystemConfigurationController::class, 'createIndex'])->name('index.create');
    Route::delete('/index/{index}', [SystemConfigurationController::class, 'dropIndex'])->name('index.drop');
});

// ============================================================
// QUEUE MANAGEMENT
// ============================================================
Route::prefix('queues')->name('queues.')->group(function () {
    Route::get('/', [SystemConfigurationController::class, 'queueManagement'])->name('index');
    Route::get('/status', [SystemConfigurationController::class, 'queueStatus'])->name('status');
    Route::get('/workers', [SystemConfigurationController::class, 'queueWorkers'])->name('workers');
    Route::post('/worker/restart', [SystemConfigurationController::class, 'restartWorkers'])->name('worker.restart');
    Route::get('/failed', [SystemConfigurationController::class, 'failedJobs'])->name('failed');
    Route::post('/failed/{job}/retry', [SystemConfigurationController::class, 'retryJob'])->name('failed.retry');
    Route::delete('/failed/{job}', [SystemConfigurationController::class, 'deleteFailedJob'])->name('failed.delete');
    Route::post('/failed/retry-all', [SystemConfigurationController::class, 'retryAllJobs'])->name('failed.retry-all');
    Route::post('/failed/flush', [SystemConfigurationController::class, 'flushFailedJobs'])->name('failed.flush');
    Route::get('/monitor', [SystemConfigurationController::class, 'queueMonitor'])->name('monitor');
});

// ============================================================
// LOCALIZATION
// ============================================================
Route::prefix('localization')->name('localization.')->group(function () {
    Route::get('/', [SystemConfigurationController::class, 'localization'])->name('index');
    Route::get('/languages', [SystemConfigurationController::class, 'languages'])->name('languages');
    Route::post('/language', [SystemConfigurationController::class, 'addLanguage'])->name('language.add');
    Route::put('/language/{language}', [SystemConfigurationController::class, 'updateLanguage'])->name('language.update');
    Route::delete('/language/{language}', [SystemConfigurationController::class, 'deleteLanguage'])->name('language.delete');
    Route::post('/language/{language}/activate', [SystemConfigurationController::class, 'activateLanguage'])->name('language.activate');
    Route::post('/language/{language}/default', [SystemConfigurationController::class, 'setDefaultLanguage'])->name('language.default');
    Route::get('/translations', [SystemConfigurationController::class, 'translations'])->name('translations');
    Route::get('/translations/{language}', [SystemConfigurationController::class, 'languageTranslations'])->name('translations.language');
    Route::put('/translation', [SystemConfigurationController::class, 'updateTranslation'])->name('translation.update');
    Route::post('/translations/import', [SystemConfigurationController::class, 'importTranslations'])->name('translations.import');
    Route::get('/translations/export/{language}', [SystemConfigurationController::class, 'exportTranslations'])->name('translations.export');
    Route::get('/timezones', [SystemConfigurationController::class, 'timezones'])->name('timezones');
    Route::put('/timezone', [SystemConfigurationController::class, 'setTimezone'])->name('timezone.update');
});

// ============================================================
// FILE STORAGE
// ============================================================
Route::prefix('storage')->name('storage.')->group(function () {
    Route::get('/', [SystemConfigurationController::class, 'storageManagement'])->name('index');
    Route::get('/disks', [SystemConfigurationController::class, 'storageDiks'])->name('disks');
    Route::get('/disk/{disk}', [SystemConfigurationController::class, 'diskDetails'])->name('disk.details');
    Route::get('/usage', [SystemConfigurationController::class, 'storageUsage'])->name('usage');
    Route::get('/quotas', [SystemConfigurationController::class, 'storageQuotas'])->name('quotas');
    Route::put('/quota/{entity}', [SystemConfigurationController::class, 'updateQuota'])->name('quota.update');
    Route::post('/cleanup', [SystemConfigurationController::class, 'storageCleanup'])->name('cleanup');
    Route::get('/temp-files', [SystemConfigurationController::class, 'tempFiles'])->name('temp-files');
    Route::post('/temp-files/clean', [SystemConfigurationController::class, 'cleanTempFiles'])->name('temp-files.clean');
});

// ============================================================
// BACKUP & RESTORE
// ============================================================
Route::prefix('backup')->name('backup.')->group(function () {
    Route::get('/', [SystemConfigurationController::class, 'backupManagement'])->name('index');
    Route::get('/list', [SystemConfigurationController::class, 'backupList'])->name('list');
    Route::post('/create', [SystemConfigurationController::class, 'createBackup'])->name('create');
    Route::get('/{backup}/download', [SystemConfigurationController::class, 'downloadBackup'])->name('download');
    Route::delete('/{backup}', [SystemConfigurationController::class, 'deleteBackup'])->name('delete');
    Route::post('/{backup}/restore', [SystemConfigurationController::class, 'restoreBackup'])->name('restore')
        ->middleware('role:super-administrator');
    Route::get('/schedule', [SystemConfigurationController::class, 'backupSchedule'])->name('schedule');
    Route::put('/schedule', [SystemConfigurationController::class, 'updateBackupSchedule'])->name('schedule.update');
    Route::get('/storage-config', [SystemConfigurationController::class, 'backupStorageConfig'])->name('storage-config');
    Route::put('/storage-config', [SystemConfigurationController::class, 'updateBackupStorage'])->name('storage-config.update');
    Route::post('/test-restore', [SystemConfigurationController::class, 'testRestore'])->name('test-restore');
});

// ============================================================
// SYSTEM LOGS
// ============================================================
Route::prefix('logs')->name('logs.')->group(function () {
    Route::get('/', [SystemConfigurationController::class, 'systemLogs'])->name('index');
    Route::get('/application', [SystemConfigurationController::class, 'applicationLogs'])->name('application');
    Route::get('/error', [SystemConfigurationController::class, 'errorLogs'])->name('error');
    Route::get('/access', [SystemConfigurationController::class, 'accessLogs'])->name('access');
    Route::get('/audit', [SystemConfigurationController::class, 'auditLogs'])->name('audit');
    Route::get('/security', [SystemConfigurationController::class, 'securityLogs'])->name('security');
    Route::get('/performance', [SystemConfigurationController::class, 'performanceLogs'])->name('performance');
    Route::get('/view/{log}', [SystemConfigurationController::class, 'viewLog'])->name('view');
    Route::get('/download/{log}', [SystemConfigurationController::class, 'downloadLog'])->name('download');
    Route::post('/cleanup', [SystemConfigurationController::class, 'cleanupLogs'])->name('cleanup');
    Route::get('/settings', [SystemConfigurationController::class, 'logSettings'])->name('settings');
    Route::put('/settings', [SystemConfigurationController::class, 'updateLogSettings'])->name('settings.update');
});

// ============================================================
// SYSTEM MONITORING
// ============================================================
Route::prefix('monitoring')->name('monitoring.')->group(function () {
    Route::get('/', [SystemConfigurationController::class, 'monitoringDashboard'])->name('index');
    Route::get('/metrics', [SystemConfigurationController::class, 'systemMetrics'])->name('metrics');
    Route::get('/performance', [SystemConfigurationController::class, 'performanceMetrics'])->name('performance');
    Route::get('/resources', [SystemConfigurationController::class, 'resourceUsage'])->name('resources');
    Route::get('/alerts', [SystemConfigurationController::class, 'monitoringAlerts'])->name('alerts');
    Route::post('/alert', [SystemConfigurationController::class, 'createAlert'])->name('alert.create');
    Route::put('/alert/{alert}', [SystemConfigurationController::class, 'updateAlert'])->name('alert.update');
    Route::delete('/alert/{alert}', [SystemConfigurationController::class, 'deleteAlert'])->name('alert.delete');
    Route::get('/uptime', [SystemConfigurationController::class, 'uptimeReport'])->name('uptime');
    Route::get('/availability', [SystemConfigurationController::class, 'availabilityReport'])->name('availability');
});

// ============================================================
// SCHEDULED TASKS
// ============================================================
Route::prefix('scheduler')->name('scheduler.')->group(function () {
    Route::get('/', [SystemConfigurationController::class, 'scheduledTasks'])->name('index');
    Route::get('/list', [SystemConfigurationController::class, 'taskList'])->name('list');
    Route::get('/task/{task}', [SystemConfigurationController::class, 'taskDetails'])->name('task.details');
    Route::post('/task/{task}/run', [SystemConfigurationController::class, 'runTask'])->name('task.run');
    Route::post('/task/{task}/enable', [SystemConfigurationController::class, 'enableTask'])->name('task.enable');
    Route::post('/task/{task}/disable', [SystemConfigurationController::class, 'disableTask'])->name('task.disable');
    Route::get('/history', [SystemConfigurationController::class, 'taskHistory'])->name('history');
    Route::get('/upcoming', [SystemConfigurationController::class, 'upcomingTasks'])->name('upcoming');
});

// ============================================================
// UPDATES & PATCHES
// ============================================================
Route::prefix('updates')->name('updates.')->group(function () {
    Route::get('/', [SystemConfigurationController::class, 'systemUpdates'])->name('index');
    Route::get('/check', [SystemConfigurationController::class, 'checkUpdates'])->name('check');
    Route::get('/available', [SystemConfigurationController::class, 'availableUpdates'])->name('available');
    Route::post('/download/{update}', [SystemConfigurationController::class, 'downloadUpdate'])->name('download');
    Route::post('/install/{update}', [SystemConfigurationController::class, 'installUpdate'])->name('install')
        ->middleware('role:super-administrator');
    Route::get('/history', [SystemConfigurationController::class, 'updateHistory'])->name('history');
    Route::post('/rollback/{update}', [SystemConfigurationController::class, 'rollbackUpdate'])->name('rollback')
        ->middleware('role:super-administrator');
    Route::get('/changelog', [SystemConfigurationController::class, 'changelog'])->name('changelog');
});

// ============================================================
// LICENSING
// ============================================================
Route::prefix('license')->name('license.')->group(function () {
    Route::get('/', [SystemConfigurationController::class, 'licenseInfo'])->name('index');
    Route::get('/details', [SystemConfigurationController::class, 'licenseDetails'])->name('details');
    Route::post('/activate', [SystemConfigurationController::class, 'activateLicense'])->name('activate');
    Route::post('/validate', [SystemConfigurationController::class, 'validateLicense'])->name('validate');
    Route::post('/renew', [SystemConfigurationController::class, 'renewLicense'])->name('renew');
    Route::get('/usage', [SystemConfigurationController::class, 'licenseUsage'])->name('usage');
});