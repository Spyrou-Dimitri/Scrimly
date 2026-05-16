<?php

namespace App\Enums;

enum StatusScrimRequest: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
}
