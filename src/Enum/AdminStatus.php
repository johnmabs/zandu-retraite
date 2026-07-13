<?php

namespace App\Enum;

enum AdminStatus: string
{
    case Active = 'active';
    case Suspended = 'suspended';
}
