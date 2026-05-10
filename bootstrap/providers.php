<?php

use App\Entities\Appointment\AppointmentServiceProvider;
use App\Entities\Auth\AuthServiceProvider;
use App\Entities\Chronology\ChronologyServiceProvider;
use App\Entities\Patient\PatientServiceProvider;
use App\Entities\Report\ReportServiceProvider;
use App\Entities\Setting\SettingServiceProvider;
use App\Entities\TreatmentInfo\TreatmentInfoServiceProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    AuthServiceProvider::class,
    PatientServiceProvider::class,
    SettingServiceProvider::class,
    AppointmentServiceProvider::class,
    TreatmentInfoServiceProvider::class,
    ReportServiceProvider::class,
    ChronologyServiceProvider::class,
];
