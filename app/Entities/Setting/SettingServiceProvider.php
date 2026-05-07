<?php

namespace App\Entities\Setting;

use App\Entities\Setting\Contracts\QueueSettingsServiceInterface;
use App\Entities\Setting\Services\QueueSettingsService;
use Illuminate\Support\ServiceProvider;

class SettingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(QueueSettingsServiceInterface::class, QueueSettingsService::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.DIRECTORY_SEPARATOR.'Ui'.DIRECTORY_SEPARATOR.'Views', 'setting');
        $this->loadRoutesFrom(__DIR__.DIRECTORY_SEPARATOR.'Ui'.DIRECTORY_SEPARATOR.'routes'.DIRECTORY_SEPARATOR.'web.php');
    }
}
