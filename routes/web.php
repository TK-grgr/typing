<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LogInController;
use App\Http\Controllers\SignupController;

Route::get('/', function () {
    return view('welcome');
});

Route::controller(LoginController::class)->group(function(){
Route::get('/login','login')->name('login');
Route::post('/login','loginCheck')->name('login_store');
Route::post('/logout','logout')->name('logout');
Route::get('/passChange','passChange')->name('passChange');
}
);
Route::resource('signup',SignupController::class);