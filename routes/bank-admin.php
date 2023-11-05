<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'bank-admin', 'as' => 'bank-admin.'], function () {
    Route::get('/login', [\App\Http\Controllers\BankAdminController::class, 'loginPage'])->name('login');
    Route::post('/login', [\App\Http\Controllers\BankAdminController::class, 'login'])->name('login.form');

    Route::middleware(['auth', 'role:bank-admin'])->group(function () {
        Route::post('/logout', [\App\Http\Controllers\BankAdminController::class, 'logout'])->name('logout');
        Route::get('/', [\App\Http\Controllers\BankAdminController::class, 'index'])->name('index');
        Route::get('/{id}', [\App\Http\Controllers\BankAdminController::class, 'show'])->name('show');
    });
});
