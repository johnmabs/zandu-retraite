<?php

namespace App\Enum;

// Rôle fonctionnel de l'administrateur (détermine les droits par défaut)
enum AdminRole: string
{
    case SuperAdmin = 'super_admin';
    case Supervisor = 'superviseur';
    case Manager = 'gestionnaire';
    case Cashier = 'caissier';
}
