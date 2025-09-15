<?php

use Illuminate\Support\Facades\Route;
use admin\admin_auth\Controllers\AdminController;
use admin\admin_auth\Controllers\PackageController;
use admin\admin_auth\Controllers\Auth\AdminLoginController;
use admin\admin_auth\Controllers\Auth\ForgotPasswordController;
use admin\admin_auth\Controllers\Auth\ResetPasswordController;

Route::name('admin.')->namespace('Auth')->middleware('web')->group(function () {

    // Password reset should be reachable even if admin is logged in
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'resetPassword'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'postResetPassword'])->name('password.update');

    Route::middleware('guest:admin')->group(function () {
        Route::get('/', [AdminLoginController::class, 'showLoginForm'])->name('login');
        Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AdminLoginController::class, 'login']);
        
        // OTP Verification Routes
        Route::get('/otp-verify', [AdminLoginController::class, 'showOtpVerificationForm'])->name('otp.verify');
        Route::post('/otp-verify', [AdminLoginController::class, 'verifyOtp']);
        Route::post('/otp-resend', [AdminLoginController::class, 'resendOtp'])->name('otp.resend');
        
        Route::get('/forgot-password', [ForgotPasswordController::class, 'forgotPassword'])->name('forgotPassword');
        Route::post('/send-reset-password-link', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('sendResetLinkEmail');
    });

    Route::middleware('admin.auth')->group(function () {
        Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');
        Route::get('dashboard', function () {
            return view('admin::admin.dashboard');
        })->name('dashboard');

        Route::get('/profile', [AdminController::class, 'viewProfile'])->name('profile');
        Route::post('/profileUpdate', [AdminController::class, 'profileUpdate'])->name('profileUpdate');
        Route::get('/change-password', [AdminController::class, 'viewChangePassword'])->name('change-password');
        Route::post('/updatePassword', [AdminController::class, 'updatePassword'])->name('updatePassword');

        Route::get('/packages', [PackageController::class, 'viewpackages'])->name('packages');
        Route::get('/packages/dependencies/{vendor}/{package}', [PackageController::class, 'getPackageDependencies'])
        ->name('packages.dependencies');
        Route::post('/packages/toggle/{vendor}/{package}', [PackageController::class, 'installUninstallPackage'])
        ->name('packages.toggle');

    });
});
