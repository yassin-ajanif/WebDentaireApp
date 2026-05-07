<?php

namespace App\Entities\Appointment\Enums;

enum AppointmentStatus: string
{
    case Waiting = 'waiting';
    case InProgress = 'in_progress';
    case Done = 'done';
    case Cancelled = 'cancelled';
}
