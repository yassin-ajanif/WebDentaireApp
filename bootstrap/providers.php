<?php

use App\Entities\Appointment\AppointmentServiceProvider;
use App\Entities\Patient\PatientServiceProvider;
use App\Entities\Setting\SettingServiceProvider;
use App\Entities\TreatmentInfo\TreatmentInfoServiceProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    PatientServiceProvider::class,
    SettingServiceProvider::class,
    AppointmentServiceProvider::class,
    TreatmentInfoServiceProvider::class,
];
