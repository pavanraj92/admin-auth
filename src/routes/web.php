<?php

use Illuminate\Support\Facades\Route;
use admin\admin_auth\Controllers\AdminController;
use admin\admin_auth\Controllers\Auth\AdminLoginController;

Route::name('admin.')->namespace('Auth')->middleware('web')->group(function () {
    Route::get('/', [AdminLoginController::class, 'showLoginForm'])->name('login');
    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminLoginController::class, 'login']);
    // Register routes   

    Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');
        Route::get('dashboard', function () {
            return view('admin::admin.dashboard');
        })->name('dashboard');

        Route::get('/profile', [AdminController::class, 'viewProfile'])->name('profile');
        Route::post('/profileUpdate', [AdminController::class, 'profileUpdate'])->name('profileUpdate');
        Route::get('/change-password', [AdminController::class, 'viewChangePassword'])->name('change-password');
        Route::post('/updatePassword', [AdminController::class, 'updatePassword'])->name('updatePassword');
    });

});
