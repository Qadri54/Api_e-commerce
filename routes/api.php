<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController; // Controller baru

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

require __DIR__ . '/auth.php';
