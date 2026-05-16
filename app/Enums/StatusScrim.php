<?php

namespace App\Enums;

enum StatusScrim: string
{
    case SCHEDULED = 'upcoming';
    case ABORTED = 'aborted';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}
