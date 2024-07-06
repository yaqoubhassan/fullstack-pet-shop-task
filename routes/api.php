<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\FileController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\BrandController;
use App\Http\Controllers\API\Admin\UserController as AdminUserController;

Route::prefix('v1')->middleware('api')->group(function () {

    //Admin routes
    Route::prefix('admin')->group(function () {
        Route::post('create', [AdminUserController::class, 'store'])->name('admin.user.create');
        Route::post('login', [AdminUserController::class, 'login'])->name('admin.user.login');
        Route::get('logout', [AdminUserController::class, 'logout'])
            ->middleware('admin.auth')->name('admin.user.logout');

        Route::middleware('admin.auth')->group(function () {
            Route::get('user-listing', [AdminUserController::class, 'index'])->name('admin.user.list');
            Route::get('user/{uuid}', [AdminUserController::class, 'show'])->name('admin.user.show');
            Route::put('user-edit/{uuid}', [AdminUserController::class, 'update'])->name('admin.user.update');
            Route::delete('user-delete/{uuid}', [AdminUserController::class, 'destroy'])->name('admin.user.delete');
        });
    });

    // User routes
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

    //Files
    Route::prefix('file')->group(function () {
        Route::post('upload', [FileController::class, 'uploadFile'])->middleware('jwt.auth')->name('file.upload');
        Route::get('{uuid}', [FileController::class, 'downloadFile'])->name('file.download');
    });

    //Brands
    Route::get('brands', [BrandController::class, 'index'])->name('brand.list');
    Route::prefix('brand')->group(function () {
        Route::get('{uuid}', [BrandController::class, 'show'])->name('brand.show');
        Route::middleware('admin.auth')->group(function () {
            Route::post('create', [BrandController::class, 'store'])->name('brand.create');
            Route::put('{uuid}', [BrandController::class, 'update'])->name('brand.update');
            Route::delete('{uuid}', [BrandController::class, 'destroy'])->name('brand.delete');
        });
    });

    //Categories
    Route::get('categories', [CategoryController::class, 'index'])->name('category.list');
    Route::prefix('category')->group(function () {
        Route::get('{uuid}', [CategoryController::class, 'show'])->name('category.show');
        Route::middleware('admin.auth')->group(function () {
            Route::post('create', [CategoryController::class, 'store'])->name('category.create');
            Route::put('{uuid}', [CategoryController::class, 'update'])->name('category.update');
            Route::delete('{uuid}', [CategoryController::class, 'destroy'])->name('category.delete');
        });
    });

    //Products
    Route::get('products', [ProductController::class, 'index'])->name('product.list');
    Route::prefix('product')->group(function () {
        Route::get('{uuid}', [ProductController::class, 'show'])->name('product.show');
        Route::middleware('jwt.auth')->group(function () {
            Route::post('create', [ProductController::class, 'store'])->name('product.create');
            Route::put('{uuid}', [ProductController::class, 'update'])->name('product.update');
            Route::delete('{uuid}', [ProductController::class, 'destroy'])->name('product.delete');
        });
    });
});
