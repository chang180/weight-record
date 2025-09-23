<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WeightController;
use App\Http\Controllers\WeightGoalController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// 靜態頁面路由
Route::get('/privacy', function () {
    return view('pages.privacy');
});

Route::get('/terms', function () {
    return view('pages.terms');
});

Route::get('/contact', function () {
    return view('pages.contact');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::resource('weights', WeightController::class);
    Route::get('/record', [WeightController::class, 'index'])->name('record');
    Route::get('/chart', [WeightController::class, 'show'])->name('chart');
    Route::get('/api/weights/latest', [WeightController::class, 'latest'])->name('weights.latest');
    Route::get('/weights/export/csv', [WeightController::class, 'exportCsv'])->name('weights.export.csv');
    Route::get('/weights/export/pdf', [WeightController::class, 'exportPdf'])->name('weights.export.pdf');
    Route::get('/analysis/trend', [WeightController::class, 'trendAnalysis'])->name('analysis.trend');
    Route::get('/analysis/health', [WeightController::class, 'healthMetrics'])->name('analysis.health');
    
    // 體重目標路由
    Route::resource('goals', WeightGoalController::class);
    Route::patch('/goals/{goal}/activate', [WeightGoalController::class, 'activate'])->name('goals.activate');
    
    // 個人資料路由
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
});

require __DIR__.'/auth.php';
