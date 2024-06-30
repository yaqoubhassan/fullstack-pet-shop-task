<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\API\UserController;

Route::prefix('v1/user')->group(function () {
    Route::post('create', [UserController::class, 'store'])->name('user.create');
    Route::post('login', [UserController::class, 'login'])->name('user.login');
    Route::middleware('jwt.auth')->group(function () {
        Route::get('logout', [UserController::class, 'logout'])->name('user.logout');
    });
});
