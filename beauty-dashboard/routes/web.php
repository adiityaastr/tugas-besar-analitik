<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnalyticsController;

Route::get('/', function () {
    return redirect()->route('analytics.apriori');
});

Route::get('/analytics/apriori', [AnalyticsController::class, 'index'])->name('analytics.apriori');
Route::post('/analytics/apriori/run', [AnalyticsController::class, 'runAnalysis'])->name('analytics.apriori.run');

