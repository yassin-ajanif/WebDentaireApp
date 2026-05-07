<?php

namespace App\Entities\Appointment;

use App\Entities\Appointment\Contracts\AppointmentServiceInterface;
use App\Entities\Appointment\Contracts\QueuePredictionServiceInterface;
use App\Entities\Appointment\Services\AppointmentService;
use App\Entities\Appointment\Services\QueuePredictionService;
use Illuminate\Support\ServiceProvider;

class AppointmentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AppointmentService::class);
        $this->app->singleton(AppointmentServiceInterface::class, fn ($app) => $app->make(AppointmentService::class));
        $this->app->singleton(QueuePredictionService::class);
        $this->app->singleton(QueuePredictionServiceInterface::class, fn ($app) => $app->make(QueuePredictionService::class));
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.DIRECTORY_SEPARATOR.'Ui'.DIRECTORY_SEPARATOR.'Views', 'appointment');
        $this->loadRoutesFrom(__DIR__.DIRECTORY_SEPARATOR.'Ui'.DIRECTORY_SEPARATOR.'routes'.DIRECTORY_SEPARATOR.'web.php');
    }
}
