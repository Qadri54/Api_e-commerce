<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule; // Tambahkan ini untuk validasi enum

class RegisteredUserController extends Controller {
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            // Validasi: Role hanya boleh 'admin' atau 'customer' (atau null)
            'role' => ['nullable', 'string', Rule::in(['admin', 'customer'])],
        ]);

        // Logic penentuan role: Jika request kosong, default ke 'customer'
        // (Sesuai dengan migration Anda: 'customer')
        $role = $request->role ?? 'customer';

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $role,
            'password' => Hash::make($request->string('password')),
        ]);

        event(new Registered($user));

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Register berhasil',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ], 201);
    }
}
