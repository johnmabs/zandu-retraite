<?php

namespace App\Enum;

// Distinct de PaymentMethod (module Versement) : le Visa/carte n'a de sens
// qu'ici, pas pour les versements récurrents qu'on a déjà modélisés.
enum RegistrationFeeMethod: string
{
    case MtnMomo = 'mtn_momo';
    case AirtelMoney = 'airtel_money';
    case Visa = 'visa';
    case BankTransfer = 'bank_transfer';
}
