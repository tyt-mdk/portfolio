<?php

use Illuminate\Support\Facades\Route;
/*追加*/
use App\Http\Controllers\UserController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\ScheduleController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
/*追加*/
Route::resource('users', UserController::class);
Auth::routes();
Route::resource('trips', TripController::class);
Route::get('/trips/{tripId}/schedule', [ScheduleController::class, 'showSchedule'])->name('schedule.show');
Route::post('/trips/{tripId}/add-date', [ScheduleController::class, 'addCandidateDate'])->name('schedule.addDate');
Route::post('/trips/{tripId}/vote-date', [ScheduleController::class, 'voteDate'])->name('schedule.voteDate');
Route::post('/trips/{tripId}/finalize', [ScheduleController::class, 'finalizeSchedule'])->name('schedule.finalize');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
