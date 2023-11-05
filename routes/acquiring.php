<?php
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'acquiring', 'as' => 'acquiring.'], function () {
    Route::get('/login', [\App\Http\Controllers\AcquiringController::class, 'loginPage'])->name('login');
    Route::post('/login', [\App\Http\Controllers\AcquiringController::class, 'login'])->name('login.form');

    Route::middleware(['auth', 'role:acquiring'])->group(function () {
        Route::post('/logout', [\App\Http\Controllers\AcquiringController::class, 'logout'])->name('logout');
        Route::get('/', [\App\Http\Controllers\AcquiringController::class, 'index'])->name('index');
        Route::post('/block/{image}', [\App\Http\Controllers\AcquiringController::class, 'block'])->name('block');
        Route::post('/accept/{image}', [\App\Http\Controllers\AcquiringController::class, 'accept'])->name('accept');
        Route::get('/blocked', [\App\Http\Controllers\AcquiringController::class, 'blocked'])->name('blocked');
        Route::get('/accepted', [\App\Http\Controllers\AcquiringController::class, 'accepted'])->name('accepted');
    });
});
