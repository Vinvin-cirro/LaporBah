<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportAssignmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportTrackingController;
use App\Http\Controllers\GovernanceReportController;
use App\Http\Controllers\OfficerReportController;
use App\Http\Controllers\CitizenDashboardController;
use App\Http\Controllers\GovernanceDashboardController;
use App\Http\Controllers\GovernanceMapController;
use App\Http\Controllers\GovernanceOperationController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/reports/create', [ReportController::class, 'create'])
    ->name('reports.create');
// Citizen
Route::middleware(['auth', 'role:citizen'])->group(function () {
    Route::get('/citizen/dashboard', [
        CitizenDashboardController::class,
        'index'
    ])->name('citizen.dashboard');
    
});


// Officer
Route::middleware(['auth', 'role:officer'])->group(function () {
    Route::get('/officer/dashboard', function () {
        return 'Officer Dashboard';
    });
});

// Governance
Route::middleware(['auth', 'role:governance'])->group(function () {
    Route::get('/governance/dashboard', [
        GovernanceDashboardController::class,
        'index'
    ])->name('governance.dashboard');

    Route::get('/governance/map', [
        GovernanceMapController::class,
        'index'
    ])->name('governance.map');
});

// Superadmin
Route::middleware(['auth', 'role:superadmin'])->group(function () {
    Route::get('/superadmin/dashboard', function () {
        return 'Superadmin Dashboard';
    });
});


Route::middleware(['auth', 'role:governance,superadmin'])
    ->group(function () {
        Route::post(
            '/governance/reports/{report}/assignments',
            [ReportAssignmentController::class, 'store']
        )->name('governance.reports.assign');
    });

    // Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.process');

    Route::get('/register', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->name('register.process');
});

Route::middleware(['auth', 'role:superadmin'])
    ->patch(
        '/governance/reports/{report}/status',
        [GovernanceReportController::class, 'updateStatus']
    )
    ->name('governance.reports.update-status');
    
Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');
Route::get('/register', [AuthController::class, 'showRegister'])
    ->name('register');
// Logout
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::post('/reports', [ReportController::class, 'store'])
    ->name('reports.store');

Route::get('/reports/track/{trackingCode}', [ReportTrackingController::class, 'show'])
    ->name('reports.track');

Route::middleware(['auth', 'role:governance,superadmin'])
    ->prefix('governance')
    ->group(function () {
        Route::get('/reports', [GovernanceReportController::class, 'index'])
            ->name('governance.reports.index');
        Route::get('/reports/{report}', [GovernanceReportController::class, 'show'])
            ->name('governance.reports.show');
        Route::get('/operations', [
            GovernanceOperationController::class,
            'index'
        ])->name('governance.operations');
    });

Route::middleware(['auth', 'role:officer'])->prefix('officer')->name('officer.')->group(function () {
    Route::get('/reports', [OfficerReportController::class, 'index'])
        ->name('reports.index');
    Route::get('/reports/{report}', [OfficerReportController::class, 'show'])
        ->name('reports.show');
    Route::patch('/reports/{report}/status', [OfficerReportController::class, 'updateStatus'])
        ->name('reports.status');
    Route::get(
        '/reports/{report}/history',
        [OfficerReportController::class, 'history']
    )->name('reports.history');
});


    