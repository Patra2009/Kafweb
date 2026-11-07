<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Qris = 'qris';
    case DebitCard = 'debit_card';
    case EWallet = 'e_wallet';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Tunai',
            self::Qris => 'QRIS',
            self::DebitCard => 'Kartu Debit',
            self::EWallet => 'E-Wallet',
        };
    }
}
