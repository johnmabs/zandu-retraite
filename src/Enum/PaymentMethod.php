<?php

namespace App\Enum;

// Moyen de paiement utilisé pour un versement
enum PaymentMethod: string
{
    case Cash = 'cash';
    case MtnMomo = 'mtn_momo';
    case AirtelMoney = 'airtel_money';
    case BankTransfer = 'bank_transfer';
}
