<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WeightController;

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
});

require __DIR__.'/auth.php';
