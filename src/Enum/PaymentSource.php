<?php

namespace App\Enum;

// Qui a initié l'enregistrement du versement
enum PaymentSource: string
{
    case MemberDeclared = 'member_declared'; // déclaré par le client
    case AdminRecorded = 'admin_recorded';   // saisi directement par un admin
}
