<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\API\UserController;

Route::post('v1/user/create', [UserController::class, 'store'])->name('user.create');
