<?php

namespace App\Enum;

enum NotificationChannel: string
{
    case Whatsapp = 'whatsapp';
    case Email = 'email';
    case Sms = 'sms';
}
