<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportAssignmentController;

Route::get('/', function () {
    return view('welcome');
});

// Citizen
Route::middleware(['auth', 'role:citizen'])->group(function () {
    Route::get('/citizen/dashboard', function () {
        return 'Citizen Dashboard';
    });
});


// Officer
Route::middleware(['auth', 'role:officer'])->group(function () {
    Route::get('/officer/dashboard', function () {
        return 'Officer Dashboard';
    });
});

// Governance
Route::middleware(['auth', 'role:governance'])->group(function () {
    Route::get('/governance/dashboard', function () {
        return 'Governance Dashboard';
    });
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
