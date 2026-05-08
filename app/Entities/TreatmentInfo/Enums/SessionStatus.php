<?php

namespace App\Entities\TreatmentInfo\Enums;

enum SessionStatus: string
{
    case Active = 'active';
    case Cancelled = 'cancelled';
}
