<?php

namespace App\Entities\Auth\Services;

use App\Entities\Auth\Contracts\AuthServiceInterface;
use App\Entities\Auth\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService implements AuthServiceInterface
{
    public function attemptLogin(string $email, string $password): bool
    {
        return auth()->attempt(['email' => $email, 'password' => $password]);
    }

    public function resetPasswordWithRecoveryCode(string $recoveryCode, string $email, string $newPassword): bool
    {
        $expected = config('app.recovery_code');

        if ($expected === null || $expected === '') {
            return false;
        }

        if (! hash_equals((string) $expected, $recoveryCode)) {
            return false;
        }

        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            return false;
        }

        $user->update(['password' => Hash::make($newPassword)]);

        return true;
    }

    public function logout(): void
    {
        auth()->logout();
        session()->invalidate();
        session()->regenerateToken();
    }
}
