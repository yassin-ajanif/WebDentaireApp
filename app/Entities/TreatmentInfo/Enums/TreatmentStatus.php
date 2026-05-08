<?php

namespace App\Entities\TreatmentInfo\Enums;

enum TreatmentStatus: string
{
    case Paid = 'paid';
    case Unpaid = 'unpaid';
    case Cancelled = 'cancelled';
}
