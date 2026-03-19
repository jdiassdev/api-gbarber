<?php

use App\Http\Controllers\Bookings\BookingController;
use Illuminate\Support\Facades\Route;

Route::prefix('bookigns')->name('bookigns.')->group(function () {
    Route::middleware('jwt')->group(function () {
        Route::post('/', [BookingController::class, 'store'])->name('store');
    });
});
