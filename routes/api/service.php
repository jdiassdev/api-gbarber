<?php

use App\Http\Controllers\Services\ServicesController;
use Illuminate\Support\Facades\Route;

Route::prefix('services')->name('services.')->group(function () {

    Route::get('/', [ServicesController::class, 'index'])->name('index');
});
