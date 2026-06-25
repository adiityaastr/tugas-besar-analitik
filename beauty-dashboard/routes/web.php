<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OperationalController;

Route::get('/', function () {
    return redirect()->route('analytics.sales');
});

// Existing Apriori route
Route::get('/analytics/apriori',      [AnalyticsController::class, 'index'])->name('analytics.apriori');
Route::post('/analytics/apriori/run', [AnalyticsController::class, 'runAnalysis'])->name('analytics.apriori.run');

// New Dashboard routes
Route::get('/analytics/sales',        [SalesController::class,       'index'])->name('analytics.sales');
Route::get('/analytics/marketplace',  [MarketplaceController::class,  'index'])->name('analytics.marketplace');
Route::get('/analytics/products',     [ProductController::class,      'index'])->name('analytics.products');
Route::get('/analytics/customers',    [CustomerController::class,     'index'])->name('analytics.customers');
Route::get('/analytics/operational',  [OperationalController::class,  'index'])->name('analytics.operational');
