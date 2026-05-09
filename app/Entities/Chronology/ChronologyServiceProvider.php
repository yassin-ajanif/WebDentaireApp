<?php

namespace App\Entities\Chronology;

use App\Entities\Chronology\Contracts\ChronologyServiceInterface;
use App\Entities\Chronology\Services\ChronologyService;
use Illuminate\Support\ServiceProvider;

class ChronologyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ChronologyServiceInterface::class, ChronologyService::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/Ui/Views', 'chronology');
        $this->loadRoutesFrom(__DIR__ . '/Ui/routes/web.php');
    }
}
