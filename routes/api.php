<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\Admin\UserController as AdminUserController;
use App\Http\Controllers\API\CategoryController;

Route::prefix('v1')->middleware('api')->group(function () {
    Route::prefix('admin')->group(function () {
        Route::post('create', [AdminUserController::class, 'store'])->name('admin.user.create');
        Route::post('login', [AdminUserController::class, 'login'])->name('admin.user.login');

        Route::middleware('admin.auth')->group(function () {
            Route::get('user-listing', [AdminUserController::class, 'index'])->name('admin.user.list');
            Route::get('user/{uuid}', [AdminUserController::class, 'show'])->name('admin.user.show');
            Route::put('user-edit/{uuid}', [AdminUserController::class, 'update'])->name('admin.user.update');
            Route::delete('user-delete/{uuid}', [AdminUserController::class, 'destroy'])->name('admin.user.delete');
        });
    });


    Route::prefix('user')->group(function () {
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

    //Category
    Route::get('categories', [CategoryController::class, 'index'])->middleware('jwt.auth')->name('category.list');
    Route::prefix('category')->middleware('jwt.auth')->group(function () {
        Route::post('create', [CategoryController::class, 'store'])->name('category.create');
        Route::get('{uuid}', [CategoryController::class, 'show'])->name('category.show');
        Route::put('{uuid}', [CategoryController::class, 'update'])->name('category.update');
        Route::delete('{uuid}', [CategoryController::class, 'destroy'])->name('category.delete');
    });
});
