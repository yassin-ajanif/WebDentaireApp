<?php

namespace App\Entities\Auth\Ui\Livewire;

use App\Entities\Auth\Contracts\AuthServiceInterface;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.auth')]
class ResetPasswordPage extends Component
{
    public string $recovery_code = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $error = '';

    public bool $success = false;

    private function auth(): AuthServiceInterface
    {
        return app(AuthServiceInterface::class);
    }

    public function resetPassword(): void
    {
        $this->validate([
            'recovery_code' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:4', 'confirmed'],
        ]);

        $ok = $this->auth()->resetPasswordWithRecoveryCode(
            $this->recovery_code,
            $this->email,
            $this->password,
        );

        if ($ok) {
            $this->success = true;
            $this->error = '';
        } else {
            $this->error = __('Invalid recovery code or email.');
        }
    }

    public function render()
    {
        return view('auth::reset-password-page')->title(__('Reset password'));
    }
}
