<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\CandidateDateController;
use App\Http\Controllers\TripRequestController;
use App\Http\Controllers\Auth\LoginController;  // 追加
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// 認証不要のルート
Route::get('/', function () {
    return view('trips.toppage');
})->name('toppage');

// 認証関連のルート
Auth::routes();

// 未ログインユーザーのみアクセス可能
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// 共有リンク生成
Route::post('/trips/{trip}/share', [TripController::class, 'generateShareLink'])
    ->name('trips.generateShareLink')
    ->middleware('auth');

Route::get('/trips/{trip}/planning', [TripController::class, 'eachPlanning'])
    ->name('trips.each.planning');

// 共有リンクからの参加確認画面
Route::get('/trips/join/{token}', [TripController::class, 'showJoinConfirmation'])
    ->name('trips.join');

// 参加確認の処理
Route::post('/trips/join/{token}/confirm', [TripController::class, 'joinByToken'])
    ->name('trips.join.confirm')
    ->middleware('auth');

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
    Route::put('/trip-requests/{tripRequest}', [TripRequestController::class, 'update'])->name('requests.update');
    Route::delete('/trip-requests/{tripRequest}', [TripRequestController::class, 'destroy'])->name('requests.destroy');
    Route::post('/requests/{request}/comment', [TripRequestController::class, 'storeComment'])
        ->name('requests.comment');
    Route::post('/requests/{request}/like', [TripRequestController::class, 'toggleLike'])
        ->name('requests.like');

    // コメントの更新と削除
    Route::put('/request-comments/{comment}', [RequestCommentController::class, 'update'])->name('request.comments.update');
    Route::delete('/request-comments/{comment}', [RequestCommentController::class, 'destroy'])->name('request.comments.destroy');
});

// 候補日関連のAPI
Route::get('/get-candidate-dates', [CandidateDateController::class, 'getCandidateDates']);
Route::post('/set-judgement', [CandidateDateController::class, 'setJudgement']);

// ログアウト
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');