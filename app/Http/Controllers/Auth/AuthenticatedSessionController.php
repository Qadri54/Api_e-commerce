<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse; // <--- 1. Pastikan import ini ada
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller {
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): JsonResponse // <--- 2. Ubah return type jadi JsonResponse
    {
        // A. Validasi User (Email & Password)
        // Fungsi ini otomatis mengecek database. Jika salah, dia melempar error 422.
        $request->authenticate();

        // B. Hapus Logic Session (Karena kita pakai Token)
        // $request->session()->regenerate(); <--- Baris ini dihapus/dikomen saja

        // C. Logic Token (Tambahkan ini)
        $user = auth()->user();

        // (Opsional) Hapus token lama agar user cuma bisa login di 1 device
        // $user->tokens()->delete();

        // Buat Token Baru
        // Property 'plainTextToken' inilah yang berisi string "1|AbCdEf..."
        $token = $user->createToken('auth_token')->plainTextToken;

        // D. Kembalikan Respon JSON
        return response()->json([
            'message' => 'Login berhasil',
            'user' => $user,
            'access_token' => $token, // <--- INI KUNCINYA
            'token_type' => 'Bearer'
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): JsonResponse {
        // Hapus token yang sedang dipakai saat ini
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil'
        ]);
    }
}
