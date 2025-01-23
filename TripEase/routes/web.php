<?php

use Illuminate\Support\Facades\Route;
/*追加*/
use App\Http\Controllers\UserController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\CandidateDateController;

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
Route::get('/trips/{trip}/schedule', [ScheduleController::class, 'showDatePlanning'])
    ->name('trips.schedule')
    ->middleware('auth');
Route::post('/trips/{tripId}/add-date', [ScheduleController::class, 'addCandidateDate'])->name('schedule.addDate');
Route::post('/trips/{tripId}/finalize', [ScheduleController::class, 'finalizeSchedule'])->name('schedule.finalize');

Route::get('/get-candidate-dates', [CandidateDateController::class, 'getCandidateDates']);
Route::post('/set-judgement', [CandidateDateController::class, 'setJudgement']);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::post('/trips/{trip}/vote-date', [ScheduleController::class, 'voteDate'])
    ->name('schedule.voteDate')
    ->middleware('auth');
