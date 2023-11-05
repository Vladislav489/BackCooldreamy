<?php
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'management', 'as' => 'management.'], function () {
    Route::get('/login', [\App\Http\Controllers\ManagementController::class, 'loginPage'])->name('login');
    Route::post('/login', [\App\Http\Controllers\ManagementController::class, 'login'])->name('login.form');

    Route::middleware(['auth', 'role:management'])->group(function () {
        Route::post('/logout', [\App\Http\Controllers\ManagementController::class, 'logout'])->name('logout');
        Route::get('/', [\App\Http\Controllers\ManagementController::class, 'index'])->name('index');
        Route::get('/{id}', [\App\Http\Controllers\ManagementController::class, 'show'])->name('show');
        Route::post('/{user}/upload/photo', [\App\Http\Controllers\ManagementController::class, 'uploadPhoto'])->name('upload.photo');
        Route::post('/{userId}/photo/{id}/delete', [\App\Http\Controllers\ManagementController::class, 'deletePhoto'])->name('delete.photo');
        Route::post('/{user}/upload/video', [\App\Http\Controllers\ManagementController::class, 'uploadVideo'])->name('upload.video');
        Route::post('/{userId}/video/{id}/delete', [\App\Http\Controllers\ManagementController::class, 'deleteVideo'])->name('delete.video');
    });
});
