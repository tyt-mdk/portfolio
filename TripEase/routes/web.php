<?php

use Illuminate\Support\Facades\Route;
/*追加*/
use App\Http\Controllers\UserController;
use App\Http\Controllers\TripController;

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

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
