<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LogInController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RankController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\SignupController;
use App\Http\Controllers\TopController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\TypingController;
use App\Http\Controllers\TypingGameController;
use App\Http\Controllers\TypingResultController;
use App\Http\Controllers\TypingResultStoreController;


Route::get('/', function () {
    return view('welcome');
});

Route::controller(LoginController::class)->group(function(){
Route::get('/login','login')->name('login');
Route::post('/login','loginCheck')->name('login_store');
Route::post('/logout','logout')->name('logout');
Route::get('/passChange','passChange')->name('passChange');
});

Route::get('/top',[TopController::class,'index'])->name('top');
Route::resource('signup',SignupController::class);
Route::resource('result',ResultController::class);
Route::resource('Rank',RankController::class);
Route::resource('/Profile',ProfileController::class);
Route::resource('admin',AdminController::class);
Route::resource('questions',QuestionController::class);

Route::middleware('auth')->group(function () {
    Route::get('/typing', [TypingController::class, 'select'])->name('typing.select');
    Route::get('/typing/game', [TypingGameController::class, 'show'])->name('typing.game');
    Route::post('/typing/game/resume', [TypingGameController::class, 'resume'])->name('typing.game.resume');
    Route::get('/typing/result', [TypingResultController::class, 'show'])->name('typing.result');
    Route::post('/typing/result', TypingResultStoreController::class)->name('typing.result.store');
});
