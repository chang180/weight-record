<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WeightController;
use App\Http\Controllers\WeightGoalController;
use App\Http\Controllers\DailyLogController;
use App\Http\Controllers\AchievementController;
use App\Http\Controllers\RewardController;
use App\Http\Controllers\GamificationStatsController;
use App\Http\Controllers\WeeklyReportController;
use App\Http\Controllers\MonthlyReportController;
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

    // 遊戲化功能路由
    Route::resource('daily-logs', DailyLogController::class);
    Route::post('/daily-logs/{dailyLog}/toggle-task', [DailyLogController::class, 'toggleTask'])->name('daily-logs.toggle-task');

    Route::get('/achievements', [AchievementController::class, 'index'])->name('achievements.index');
    Route::get('/achievements/{achievement}', [AchievementController::class, 'show'])->name('achievements.show');

    Route::get('/rewards', [RewardController::class, 'index'])->name('rewards.index');
    Route::post('/rewards', [RewardController::class, 'store'])->name('rewards.store');
    Route::get('/rewards/history', [RewardController::class, 'history'])->name('rewards.history');

    // 遊戲化統計路由
    Route::get('/gamification/stats', [GamificationStatsController::class, 'index'])->name('gamification.stats');
    Route::prefix('api/gamification')->group(function () {
        Route::get('/stats/points-trend', [GamificationStatsController::class, 'pointsTrend'])->name('api.gamification.points-trend');
        Route::get('/stats/task-completion', [GamificationStatsController::class, 'taskCompletion'])->name('api.gamification.task-completion');
        Route::get('/stats/streak-trend', [GamificationStatsController::class, 'streakTrend'])->name('api.gamification.streak-trend');
    });

    // 報表路由
    Route::get('/reports/weekly', [WeeklyReportController::class, 'show'])->name('reports.weekly');
    Route::get('/reports/monthly', [MonthlyReportController::class, 'show'])->name('reports.monthly');
});

require __DIR__.'/auth.php';
