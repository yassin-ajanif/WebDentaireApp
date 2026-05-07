<?php

namespace App\Entities\Patient;

use App\Entities\Appointment\Contracts\PatientLookupInterface;
use App\Entities\Patient\Contracts\PatientServiceInterface;
use App\Entities\Patient\Services\PatientService;
use Illuminate\Support\ServiceProvider;

class PatientServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PatientService::class);
        $this->app->singleton(PatientServiceInterface::class, fn ($app) => $app->make(PatientService::class));
        $this->app->singleton(PatientLookupInterface::class, fn ($app) => $app->make(PatientService::class));
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.DIRECTORY_SEPARATOR.'Ui'.DIRECTORY_SEPARATOR.'Views', 'patient');
        $this->loadRoutesFrom(__DIR__.DIRECTORY_SEPARATOR.'Ui'.DIRECTORY_SEPARATOR.'routes'.DIRECTORY_SEPARATOR.'web.php');
    }
}
