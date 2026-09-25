<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = [
            'username' => $request->validated('username'),
            'password' => $request->validated('password'),
            'is_active' => true, // user nonaktif otomatis gagal login
        ];

        $token = $this->guard()->attempt($credentials);

        if (! $token) {
            // Pesan sengaja umum: jangan beri tahu mana yang salah
            return response()->json([
                'message' => 'Username atau password salah.',
            ], 401);
        }

        /** @var User $user */
        $user = $this->guard()->user();
        $user->forceFill(['last_login_at' => now()])->save();

        return $this->tokenResponse($token, $user, 'Login berhasil.');
    }

    public function me(): JsonResponse
    {
        return response()->json([
            'data' => $this->guard()->user(),
        ]);
    }

    public function refresh(): JsonResponse
    {
        try {
            // Token lama otomatis masuk blacklist
            $token = $this->guard()->refresh();
        } catch (JWTException) {
            // Token kosong, rusak, sudah di-blacklist, atau lewat JWT_REFRESH_TTL
            return $this->sessionEnded();
        }

        /** @var User|null $user */
        $user = $this->guard()->setToken($token)->user();

        // User dihapus atau dinonaktifkan sejak login -> tolak
        if (! $user || ! $user->is_active) {
            $this->guard()->invalidate();

            return $this->sessionEnded();
        }

        return $this->tokenResponse($token, $user, 'Token diperbarui.');
    }

    public function logout(): JsonResponse
    {
        $this->guard()->logout();

        return response()->json([
            'message' => 'Berhasil logout.',
        ]);
    }

    /**
     * auth('api') secara tipe dikenali sebagai Guard biasa oleh editor,
     * padahal saat runtime selalu berupa JWTGuard. Method ini memberi
     * tahu editor tipe aslinya, supaya attempt()/refresh()/dll dikenali.
     */
    private function guard(): JWTGuard
    {
        /** @var JWTGuard $guard */
        $guard = auth('api');

        return $guard;
    }

    private function tokenResponse(string $token, User $user, string $message): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => $this->guard()->factory()->getTTL() * 60,
                'user' => $user,
            ],
        ]);
    }

    private function sessionEnded(): JsonResponse
    {
        return response()->json([
            'message' => 'Sesi berakhir. Silakan login kembali.',
        ], 401);
    }
}