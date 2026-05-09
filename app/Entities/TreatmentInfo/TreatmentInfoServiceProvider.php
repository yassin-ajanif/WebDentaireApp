<?php

namespace App\Entities\TreatmentInfo;

use App\Entities\TreatmentInfo\Contracts\TreatmentCatalogServiceInterface;
use App\Entities\TreatmentInfo\Contracts\TreatmentInfoServiceInterface;
use App\Entities\TreatmentInfo\Services\TreatmentCatalogService;
use App\Entities\TreatmentInfo\Services\TreatmentInfoService;
use Illuminate\Support\ServiceProvider;

class TreatmentInfoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TreatmentInfoServiceInterface::class, TreatmentInfoService::class);
        $this->app->singleton(TreatmentCatalogServiceInterface::class, TreatmentCatalogService::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.DIRECTORY_SEPARATOR.'Ui'.DIRECTORY_SEPARATOR.'Views', 'treatment_info');
        $this->loadRoutesFrom(__DIR__.DIRECTORY_SEPARATOR.'Ui'.DIRECTORY_SEPARATOR.'routes'.DIRECTORY_SEPARATOR.'web.php');
    }
}
