<?php
/**
 * IntelliCampus Library Management Routes
 * 
 * Routes for library services and management (Future Module).
 * These routes are automatically prefixed with 'library' and named with 'library.'
 * Applied middleware: 'web', 'auth'
 * Module check: config('app.intellicampus.modules.library.enabled')
 */

use Illuminate\Support\Facades\Route;

// ============================================================
// LIBRARY PUBLIC SERVICES
// ============================================================
Route::get('/', function() {
    return view('library.index');
})->name('index');

Route::get('/catalog', function() {
    return view('library.catalog');
})->name('catalog');

Route::get('/search', function() {
    return view('library.search');
})->name('search');

Route::get('/hours', function() {
    return view('library.hours');
})->name('hours');

// ============================================================
// PATRON SERVICES
// ============================================================
Route::prefix('patron')->name('patron.')->middleware(['auth'])->group(function () {
    Route::get('/account', function() {
        return view('coming-soon', ['module' => 'Library Management']);
    })->name('account');
    
    Route::get('/loans', function() {
        return view('coming-soon', ['module' => 'Library Management']);
    })->name('loans');
    
    Route::get('/renewals', function() {
        return view('coming-soon', ['module' => 'Library Management']);
    })->name('renewals');
    
    Route::get('/holds', function() {
        return view('coming-soon', ['module' => 'Library Management']);
    })->name('holds');
    
    Route::get('/fines', function() {
        return view('coming-soon', ['module' => 'Library Management']);
    })->name('fines');
    
    Route::get('/history', function() {
        return view('coming-soon', ['module' => 'Library Management']);
    })->name('history');
});

// ============================================================
// DIGITAL RESOURCES
// ============================================================
Route::prefix('digital')->name('digital.')->group(function () {
    Route::get('/databases', function() {
        return view('coming-soon', ['module' => 'Library Management']);
    })->name('databases');
    
    Route::get('/e-books', function() {
        return view('coming-soon', ['module' => 'Library Management']);
    })->name('ebooks');
    
    Route::get('/e-journals', function() {
        return view('coming-soon', ['module' => 'Library Management']);
    })->name('ejournals');
    
    Route::get('/repository', function() {
        return view('coming-soon', ['module' => 'Library Management']);
    })->name('repository');
});

// ============================================================
// RESEARCH SERVICES
// ============================================================
Route::prefix('research')->name('research.')->group(function () {
    Route::get('/guides', function() {
        return view('coming-soon', ['module' => 'Library Management']);
    })->name('guides');
    
    Route::get('/consultations', function() {
        return view('coming-soon', ['module' => 'Library Management']);
    })->name('consultations');
    
    Route::get('/citations', function() {
        return view('coming-soon', ['module' => 'Library Management']);
    })->name('citations');
});

// ============================================================
// ROOM RESERVATIONS
// ============================================================
Route::prefix('rooms')->name('rooms.')->middleware(['auth'])->group(function () {
    Route::get('/', function() {
        return view('coming-soon', ['module' => 'Library Management']);
    })->name('index');
    
    Route::get('/study-rooms', function() {
        return view('coming-soon', ['module' => 'Library Management']);
    })->name('study');
    
    Route::get('/reserve', function() {
        return view('coming-soon', ['module' => 'Library Management']);
    })->name('reserve');
    
    Route::get('/my-reservations', function() {
        return view('coming-soon', ['module' => 'Library Management']);
    })->name('my-reservations');
});

// ============================================================
// LIBRARY ADMINISTRATION
// ============================================================
Route::prefix('admin')->name('admin.')->middleware(['role:librarian,library-admin,admin'])->group(function () {
    // Placeholder routes for future implementation
    Route::get('/', function() {
        return view('coming-soon', ['module' => 'Library Administration']);
    })->name('index');
    
    Route::get('/circulation', function() {
        return view('coming-soon', ['module' => 'Library Circulation']);
    })->name('circulation');
    
    Route::get('/acquisitions', function() {
        return view('coming-soon', ['module' => 'Library Acquisitions']);
    })->name('acquisitions');
    
    Route::get('/cataloging', function() {
        return view('coming-soon', ['module' => 'Library Cataloging']);
    })->name('cataloging');
    
    Route::get('/reports', function() {
        return view('coming-soon', ['module' => 'Library Reports']);
    })->name('reports');
});

// ============================================================
// NOTE: This is a placeholder for future library module implementation
// All routes currently return 'coming-soon' views
// ============================================================