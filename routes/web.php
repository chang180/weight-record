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

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::get('/record', [WeightController::class,'index'])->middleware(['auth'])->name('record');

Route::post('/record', [WeightController::class,'store'])->middleware(['auth'])->name('record');

require __DIR__.'/auth.php';
