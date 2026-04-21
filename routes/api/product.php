<?php

use App\Http\Controllers\Products\ProductsController;
use Illuminate\Support\Facades\Route;

Route::prefix('products')->name('products.')->group(function () {
    Route::middleware('jwt')->group(function () {
        Route::post('/', [ProductsController::class, 'store'])->name('store');
    });

    Route::get('/', [ProductsController::class, 'index'])->name('index');
});
