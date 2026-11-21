
<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportTrackingController;
use App\Http\Controllers\CitizenDashboardController;
use App\Http\Controllers\GovernanceDashboardController;
use App\Http\Controllers\GovernanceMapController;
use App\Http\Controllers\GovernanceReportController;
use App\Http\Controllers\GovernanceOperationController;
use App\Http\Controllers\GovernanceStatisticsController;
use App\Http\Controllers\OfficerReportController;
use App\Http\Controllers\ReportAssignmentController;


// ==================================================
// PUBLIC ROUTES
// ==================================================

// Landing Page - Citizen Dashboard
// Bisa diakses tanpa login
Route::get('/', [
    CitizenDashboardController::class,
    'index'
])->name('citizen.dashboard');

// Create Report - Guest Access
Route::get('/reports/create', [
    ReportController::class,
    'create'
])->name('reports.create');

// Submit Report - Guest Access
Route::post('/reports', [
    ReportController::class,
    'store'
])->name('reports.store');

// Track Report - Guest Access
Route::get('/reports/track/{trackingCode}', [
    ReportTrackingController::class,
    'show'
])->name('reports.track');


// ==================================================
// AUTHENTICATION
// ==================================================

Route::middleware('guest')->group(function () {

    Route::get('/login', [
        AuthController::class,
        'showLogin'
    ])->name('login');

    Route::post('/login', [
        AuthController::class,
        'login'
    ])->name('login.process');

    Route::get('/register', [
        AuthController::class,
        'showRegister'
    ])->name('register');

    Route::post('/register', [
        AuthController::class,
        'register'
    ])->name('register.process');

});

// Logout
Route::post('/logout', [
    AuthController::class,
    'logout'
])->middleware('auth')->name('logout');


// ==================================================
// OFFICER ROUTES
// ==================================================

Route::middleware(['auth', 'role:officer'])
    ->prefix('officer')
    ->name('officer.')
    ->group(function () {

        Route::get('/dashboard', function () {
            return 'Officer Dashboard';
        })->name('dashboard');

        Route::get('/reports', [
            OfficerReportController::class,
            'index'
        ])->name('reports.index');

        Route::get('/reports/{report}', [
            OfficerReportController::class,
            'show'
        ])->name('reports.show');

        Route::patch('/reports/{report}/status', [
            OfficerReportController::class,
            'updateStatus'
        ])->name('reports.status');

        Route::get('/reports/{report}/history', [
            OfficerReportController::class,
            'history'
        ])->name('reports.history');

    });


// ==================================================
// GOVERNANCE ROUTES
// ==================================================

Route::middleware(['auth', 'role:governance,superadmin'])
    ->prefix('governance')
    ->name('governance.')
    ->group(function () {

        Route::get('/dashboard', [
            GovernanceDashboardController::class,
            'index'
        ])->name('dashboard');

        Route::get('/map', [
            GovernanceMapController::class,
            'index'
        ])->name('map');

        Route::get('/reports', [
            GovernanceReportController::class,
            'index'
        ])->name('reports.index');

        Route::get('/reports/{report}', [
            GovernanceReportController::class,
            'show'
        ])->name('reports.show');

        Route::patch('/reports/{report}/status', [
            GovernanceReportController::class,
            'updateStatus'
        ])->name('reports.update-status');

        Route::post('/reports/{report}/assignments', [
            ReportAssignmentController::class,
            'store'
        ])->name('reports.assign');

        Route::get('/operations', [
            GovernanceOperationController::class,
            'index'
        ])->name('operations');

        Route::get('/statistics', [
            GovernanceStatisticsController::class,
            'index'
        ])->name('statistics');

    });


// ==================================================
// SUPERADMIN ROUTES
// ==================================================

Route::middleware(['auth', 'role:superadmin'])
    ->prefix('superadmin')
    ->name('superadmin.')
    ->group(function () {

        Route::get('/dashboard', function () {
            return 'Superadmin Dashboard';
        })->name('dashboard');

    });
