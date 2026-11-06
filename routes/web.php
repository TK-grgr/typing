<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LogInController;

Route::get('/', function () {
    return view('welcome');
});

Route::controller(LoginController::class)->group(function(){
Route::get('/login','login')->name('login');
Route::post('/login','loginCheck')->name('login_store');
Route::post('/logout','logout')->name('logout');
}
);
