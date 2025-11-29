<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DwReportController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DrillDownController;

Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Data Warehouse Dashboard Routes (Protected)
Route::middleware(['web'])->prefix('dashboard')->group(function () {
    Route::get('/sales-overview', [DwReportController::class, 'salesOverview'])->name('dashboard.sales-overview');
    Route::get('/product-analysis', [DwReportController::class, 'productAnalysis'])->name('dashboard.product-analysis');
    Route::get('/customer-geo', [DwReportController::class, 'customerGeo'])->name('dashboard.customer-geo');
    
    // Drill-down routes
    Route::get('/territory/{territoryId}', [DrillDownController::class, 'territoryDetails'])->name('dashboard.territory-drilldown');
});
