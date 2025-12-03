<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnalyticsController;
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
    // Q1: Market Basket Analysis
    Route::get('/market-basket', [AnalyticsController::class, 'marketBasket'])->name('dashboard.market-basket');
    
    // Q2: Territory Discount vs Profit
    Route::get('/territory-discount', [AnalyticsController::class, 'territoryDiscount'])->name('dashboard.territory-discount');
    
    // Q3: Customer Segmentation
    Route::get('/customer-segmentation', [AnalyticsController::class, 'customerSegmentation'])->name('dashboard.customer-segmentation');
    
    // Q4: Salesperson Retention
    Route::get('/salesperson-retention', [AnalyticsController::class, 'salespersonRetention'])->name('dashboard.salesperson-retention');
    
    // Q5: Inventory Turnover
    Route::get('/inventory-turnover', [AnalyticsController::class, 'inventoryTurnover'])->name('dashboard.inventory-turnover');
    
    // Drill-down routes
    Route::get('/territory/{territoryId}', [DrillDownController::class, 'territoryDetails'])->name('dashboard.territory-drilldown');
});
