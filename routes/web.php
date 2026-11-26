
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
use App\Http\Controllers\GovernanceTeamController;
use App\Http\Controllers\CitizenProfileController;


// ==================================================
// PUBLIC ROUTES
// ==================================================

// Landing Page - Citizen Dashboard
// Bisa diakses tanpa login
Route::get('/', [
    CitizenDashboardController::class,
    'index'
])->name('index');

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

Route::get('/reports/categories', function () {
    return view('reports.categories');
})->name('reports.categories');


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
    ->prefix('officer')->name('officer.')->group(function () {
        Route::get('/dashboard', [OfficerReportController::class, 'dashboard'])->name('dashboard');
        Route::get('/map', [OfficerReportController::class, 'map'])->name('map');
        Route::get('/reports', [OfficerReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/{report}', [OfficerReportController::class, 'show'])->name('reports.show');
        Route::get('/reports/{report}/history', [OfficerReportController::class, 'history'])->name('reports.history');
        Route::post('/reports/{report}/accept', [OfficerReportController::class, 'accept'])->name('reports.accept');
        Route::post('/reports/{report}/start', [OfficerReportController::class, 'start'])->name('reports.start');
        Route::post('/reports/{report}/progress', [OfficerReportController::class, 'progress'])->name('reports.progress');
        Route::post('/reports/{report}/documents', [OfficerReportController::class, 'upload'])->name('reports.documents');
        Route::post('/reports/{report}/patrol', [OfficerReportController::class, 'patrol'])->name('reports.patrol');
        Route::post('/reports/{report}/complete', [OfficerReportController::class, 'complete'])->name('reports.complete');
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

        Route::get('/teams', [GovernanceTeamController::class, 'index'])->name('teams.index');
        Route::get('/teams/create', [GovernanceTeamController::class, 'create'])->name('teams.create');
        Route::post('/teams', [GovernanceTeamController::class, 'store'])->name('teams.store');
        Route::get('/teams/{team}/edit', [GovernanceTeamController::class, 'edit'])->name('teams.edit');
        Route::put('/teams/{team}', [GovernanceTeamController::class, 'update'])->name('teams.update');
        Route::patch('/teams/{team}/toggle', [GovernanceTeamController::class, 'toggle'])->name('teams.toggle');

        Route::get('/operations', [
            GovernanceOperationController::class,
            'index'
        ])->name('operations');

        Route::post('/operations/{assignment}/verify', [
            GovernanceOperationController::class,
            'verify'
        ])->name('operations.verify');

        Route::post('/reports/inspections/{assignment}/review', [
            GovernanceOperationController::class,
            'reviewInspection'
        ])->name('reports.inspections.review');

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


// ==================================================
// CITIZEN ACCOUNT
// ==================================================

Route::middleware(['auth', 'role:citizen'])
    ->prefix('citizen')
    ->name('citizen.')
    ->group(function () {

        Route::get('/profile', [
            CitizenProfileController::class,
            'show'
        ])->name('profile');

        Route::put('/profile', [
            CitizenProfileController::class,
            'update'
        ])->name('profile.update');

    });