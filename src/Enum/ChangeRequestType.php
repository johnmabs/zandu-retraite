<?php

namespace App\Enum;

enum ChangeRequestType: string
{
    case Phone = 'phone';
    case Sector = 'sector';
    case WhatsappGroupIntegration = 'whatsapp_group_integration';
}
