<?php

namespace App\Entities\Auth;

use App\Entities\Auth\Contracts\AuthServiceInterface;
use App\Entities\Auth\Services\AuthService;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AuthService::class);
        $this->app->singleton(AuthServiceInterface::class, fn ($app) => $app->make(AuthService::class));
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.DIRECTORY_SEPARATOR.'Ui'.DIRECTORY_SEPARATOR.'Views', 'auth');
        $this->loadRoutesFrom(__DIR__.DIRECTORY_SEPARATOR.'Ui'.DIRECTORY_SEPARATOR.'routes'.DIRECTORY_SEPARATOR.'web.php');
    }
}
