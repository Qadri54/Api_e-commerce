<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController; // Controller baru
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;


Route::prefix('auth')->group(function () {

    // --- PUBLIC ROUTES (Tidak butuh Token) ---

    // Register User Baru
    Route::post('/register', [RegisteredUserController::class, 'store'])
        ->name('api.register');

    // Login (Mendapatkan Token)
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->name('api.login');

    // Lupa Password (Kirim Link Reset)
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('api.password.email');

    // Reset Password (Submit Password Baru)
    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->name('api.password.store');


    // --- PROTECTED ROUTES (Butuh Token Bearer) ---
    Route::middleware('auth:sanctum')->group(function () {

        // Logout (Hapus Token)
        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
            ->name('api.logout');

        // Verifikasi Email (Klik Link dari Email)
        // Catatan: Biasanya link di email mengarah ke Frontend, lalu Frontend hit endpoint ini
        Route::get('/verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
            ->middleware(['signed', 'throttle:6,1'])
            ->name('verification.verify');

        // Kirim Ulang Email Verifikasi
        Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
            ->middleware(['throttle:6,1'])
            ->name('verification.send');
    });

});

/*
|--------------------------------------------------------------------------
| Public Routes (Bisa diakses tanpa login)
|--------------------------------------------------------------------------
*/
// 1, 2, 3: Lihat daftar, detail, dan cari produk
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);

// Webhook Midtrans (Harus POST dan tidak boleh kena CSRF/Auth)
Route::post('/notification', [OrderController::class, 'notificationHandler']);


/*
|--------------------------------------------------------------------------
| Protected Routes (Harus Login)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/profile', function (Request $request) {
        return $request->user();
    });

    // --- FITUR CUSTOMER ---
    // 6 & 7: Checkout & Payment
    Route::post('/checkout', [OrderController::class, 'checkout']);

    // 9: Lihat riwayat pembelian
    Route::get('/orders', [OrderController::class, 'index']);

    // 8: Lihat detail & status pesanan spesifik
    Route::get('/orders/{order}', [OrderController::class, 'show']);


    // --- FITUR ADMIN ---
    Route::middleware('role:admin')->group(function () {
        // CRUD Produk (kecuali index & show yang sudah di public)
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{product}', [ProductController::class, 'update']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);
    });
});
