<?php

namespace App\Enum;

// Statut de validation d'un versement
enum PaymentStatus: string
{
    case Pending = 'pending';       // en attente de confirmation
    case Confirmed = 'confirmed';   // confirmé (fonds reçus)
    case Rejected = 'rejected';     // rejeté (échec, fraude, etc.)
}
