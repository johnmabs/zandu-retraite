<?php

namespace App\Enum;

// Statut du cycle de vie d'un membre (client)
enum MemberStatus: string
{
    case Pending = 'pending';       // inscription en attente de validation
    case Active = 'active';         // membre actif
    case Suspended = 'suspended';   // suspendu (ex: impayés prolongés)
    case Rejected = 'rejected';     // inscription refusée par un admin
    case Closed = 'closed';         // compte clôturé
}
