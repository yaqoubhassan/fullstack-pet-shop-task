<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;

Route::prefix('v1/user')->group(function () {
    Route::post('create', [UserController::class, 'store'])->name('user.create');
    Route::post('login', [UserController::class, 'login'])->name('user.login');
    Route::get('logout', [UserController::class, 'logout'])->middleware('jwt.auth')->name('user.logout');
    Route::post('forgot-password', [UserController::class, 'forgotPassword'])->name('user.forgot-password');
    Route::post('reset-password', [UserController::class, 'resetPassword'])->name('password.reset');

    Route::middleware('jwt.auth')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('user.index');
        Route::put('edit', [UserController::class, 'update'])->name('user.update');
        Route::delete('/', [UserController::class, 'destroy'])->name('user.delete');
    });
});
