<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LogInController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RankController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\SignupController;
use App\Http\Controllers\TopController;
use App\Http\Controllers\QuestionController;


Route::get('/', function () {
    return view('welcome');
});
Route::middleware('guest')->group(function(){

Route::controller(LoginController::class)->group(function(){
Route::get('/login','login')->name('login');
Route::post('/login','loginCheck')->name('login_store');
Route::get('/passChange','passChange')->name('passChange');
});
Route::resource('signup',SignupController::class);
Route::post('/forgot-password',[PasswordResetController::class,'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', function ($token) {
        return view('auth.reset-password', ['token' => $token]);
    })->name('password.reset');
});

Route::middleware('auth')->group(function(){

Route::post('/logout', [LogInController::class, 'logout'])->name('logout');
Route::get('/top',[TopController::class,'index'])->name('top');
Route::resource('result',ResultController::class);
Route::resource('Rank',RankController::class);
Route::resource('/Profile',ProfileController::class);
Route::resource('admin',AdminController::class);
Route::resource('questions',QuestionController::class);

});

