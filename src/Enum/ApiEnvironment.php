<?php

namespace App\Enum;

enum ApiEnvironment: string
{
    case Sandbox = 'sandbox';
    case Production = 'production';
}
