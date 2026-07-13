<?php

namespace App\Enum;

// Permissions granulaires assignables à un admin (par défaut héritées du rôle, personnalisables ensuite)
enum AdminPermission: string
{
    case ManageAdmins = 'manage_admins';           // gestion_admins
    case EditSettings = 'edit_settings';           // modifier_parametres
    case GlobalView = 'global_view';               // vue_globale
    case ManageMembers = 'manage_members';         // gestion_clients
    case EditMembers = 'edit_members';              // modifier_clients
    case DeleteMembers = 'delete_members';          // supprimer_clients
    case ManagePayments = 'manage_payments';        // gestion_versements
    case DeletePayments = 'delete_payments';        // supprimer_versements
    case ManageRegistrations = 'manage_registrations'; // gestion_inscriptions
    case ManagePayslips = 'manage_payslips';         // gestion_paies
    case ManageMessages = 'manage_messages';         // gestion_messages
    case ChatAccess = 'chat_access';                 // espace_echange
    case ExportData = 'export_data';                 // export_donnees
}
