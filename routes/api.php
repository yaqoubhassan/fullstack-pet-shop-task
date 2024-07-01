<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;

Route::prefix('v1/user')->group(function () {
    Route::post('create', [UserController::class, 'store'])->name('user.create');
    Route::post('login', [UserController::class, 'login'])->name('user.login');

    Route::middleware('jwt.auth')->group(function () {
        Route::get('logout', [UserController::class, 'logout'])->name('user.logout');

        Route::get('/', [UserController::class, 'index'])->name('user.index');
        Route::put('edit', [UserController::class, 'update'])->name('user.update');
        Route::delete('/', [UserController::class, 'destroy'])->name('user.delete');
    });
});
