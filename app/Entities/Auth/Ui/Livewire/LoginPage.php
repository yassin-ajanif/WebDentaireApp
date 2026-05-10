<?php

namespace App\Entities\Auth\Ui\Livewire;

use App\Entities\Auth\Contracts\AuthServiceInterface;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.auth')]
class LoginPage extends Component
{
    public string $email = '';

    public string $password = '';

    public string $error = '';

    private function auth(): AuthServiceInterface
    {
        return app(AuthServiceInterface::class);
    }

    public function login(): void
    {
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($this->auth()->attemptLogin($this->email, $this->password)) {
            $this->redirect(route('queue.index'));

            return;
        }

        $this->error = __('Invalid email or password.');
    }

    public function render()
    {
        return view('auth::login-page')->title(__('Login'));
    }
}
