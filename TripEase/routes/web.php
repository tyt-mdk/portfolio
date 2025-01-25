<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\CandidateDateController;
use App\Http\Controllers\TripRequestController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// 認証不要のルート
Route::get('/', function () {
    return view('trips.toppage');
})->name('toppage');

// 認証関連のルート
Auth::routes();

// 認証済みユーザーのリダイレクト
Route::get('/home', function () {
    return redirect()->route('trips.dashboard');
})->middleware('auth');

// 認証が必要なルート
Route::middleware(['auth'])->group(function () {
    // ダッシュボード（ログイン後のリダイレクト先）
    Route::get('/trips/dashboard', [UserController::class, 'index'])->name('dashboard');

    // ユーザー関連のルート
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    
    // 既存のルート
    Route::resource('trips', TripController::class);
    Route::get('/trips/{trip}/schedule', [ScheduleController::class, 'showDatePlanning'])
        ->name('trips.schedule');
    Route::post('/trips/{tripId}/add-date', [ScheduleController::class, 'addCandidateDate'])
        ->name('schedule.addDate');
    Route::post('/trips/{tripId}/finalize', [ScheduleController::class, 'finalizeSchedule'])
        ->name('schedule.finalize');
    Route::post('/trips/{trip}/vote-date', [ScheduleController::class, 'voteDate'])
        ->name('schedule.voteDate');

    // 要望関連のルート
    Route::post('/trips/{trip}/request', [TripController::class, 'storeRequest'])
        ->name('trips.request');
    Route::post('/requests/{request}/comment', [TripRequestController::class, 'storeComment'])
        ->name('requests.comment');
    Route::post('/requests/{request}/like', [TripRequestController::class, 'toggleLike'])
        ->name('requests.like');
});

// 候補日関連のAPI
Route::get('/get-candidate-dates', [CandidateDateController::class, 'getCandidateDates']);
Route::post('/set-judgement', [CandidateDateController::class, 'setJudgement']);