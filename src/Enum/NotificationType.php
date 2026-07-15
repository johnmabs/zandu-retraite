<?php

namespace App\Enum;

// Types d'alertes métier (Notification, lues/non lues, destinées aux admins)
enum NotificationType: string
{
    case RegistrationSubmitted = 'registration_submitted';   // inscription
    case PaymentReceived = 'payment_received';                // versement
    case PayslipGenerated = 'payslip_generated';               // paie_generee
    case PayslipAvailable = 'payslip_available';               // bulletin_disponible
    case NewChatMessage = 'new_chat_message';                  // nouveau_message_client
    case MessageSent = 'message_sent';                         // message_envoye
    case ChangeRequestSubmitted = 'change_request_submitted';  // demande_secteur / demande_wa_groupe
    case ContractIssued = 'contract_issued';
}
