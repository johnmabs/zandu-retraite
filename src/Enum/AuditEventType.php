<?php

namespace App\Enum;

// Types d'événements du journal d'audit de sécurité (AuditLog, table immuable)
enum AuditEventType: string
{
    // Authentification
    case MemberLogin = 'member_login';                 // connexion_client
    case MemberLoginFailed = 'member_login_failed';     // tentative échouée (absent du proto, ajouté)
    case AdminLogin = 'admin_login';                    // connexion_admin
    case AdminLoginFailed = 'admin_login_failed';       // tentative échouée (absent du proto, ajouté)
    case RemoteAccess = 'remote_access';                // acces_distant

        // Membres
    case MemberRegistered = 'member_registered';        // inscription
    case MemberUpdated = 'member_updated';               // modification_client
    case MemberDeleted = 'member_deleted';               // suppression_client

        // Versements & paie
    case PaymentRecorded = 'payment_recorded';           // versement
    case PaymentFailed = 'payment_failed';               // echec_paiement
    case PaymentDeleted = 'payment_deleted';             // suppression (versement)
    case PayslipGenerated = 'payslip_generated';         // paie_generee

        // Administration
    case AdminUserUpdated = 'admin_user_updated';        // modif_admin
    case SettingsUpdated = 'settings_updated';           // modif_params
    case RecordDeleted = 'record_deleted';               // suppression (générique)

        // Communication
    case MessageSent = 'message_sent';                   // message_envoye (message groupé admin)
    case ChatMessageReceived = 'chat_message_received';  // nouveau_message_client

        // Intégrations externes
    case ApiCallSucceeded = 'api_call_succeeded';        // api_call
    case ApiCallFailed = 'api_call_failed';              // api_error

    case ContractIssued = 'contract_issued';
}
