<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->name('auth.')->group(function () {
    Route::post('register', RegisterController::class)->name('register');
    Route::post('login', LoginController::class)->name('login');

    Route::middleware('jwt.auth')->group(function () {
        Route::get('profile', ProfileController::class)->name('profile');
        Route::post('logout', LogoutController::class)->name('logout');
    });
});
