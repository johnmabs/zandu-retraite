<?php

namespace App\Enum;

// Comment le versement a été confirmé
enum PaymentConfirmationMethod: string
{
    case ApiAuto = 'api_auto';         // confirmation automatique via API MoMo/Airtel
    case ManualReview = 'manual_review'; // validation manuelle par un admin
}
