<?php

namespace App\Entities\Auth\Contracts;

interface AuthServiceInterface
{
    public function attemptLogin(string $email, string $password): bool;

    public function resetPasswordWithRecoveryCode(string $recoveryCode, string $email, string $newPassword): bool;

    public function logout(): void;
}
