<?php

use App\Http\Controllers\Bookings\BookingController;
use Illuminate\Support\Facades\Route;

Route::prefix('bookings')->name('bookings.')->group(function () {

    Route::middleware('jwt')->group(function () {
        Route::post('/', [BookingController::class, 'store'])->name('store');
        Route::get('/me/list', [BookingController::class, 'myBookings'])->name('my');
        Route::patch('/{id}/cancel', [BookingController::class, 'cancel'])->name('cancel');
    });

    // Deve vir após rotas estáticas para não engolir /me/list como {id}
    Route::get('/{id}', [BookingController::class, 'show'])->name('show');
});
