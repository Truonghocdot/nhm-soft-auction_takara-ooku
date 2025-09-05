<?php

namespace App\Enums;

enum PaymentViewType: string
{
    case RECHARGE = '1';
    case MEMBERSHIP = '2';
    // case PRODUCT = '3';

    public function label(): string
    {
        return match ($this) {
            self::RECHARGE => 'Top-up transaction',
            self::MEMBERSHIP => 'Membership purchase transaction',
            // self::PRODUCT => 'Product transaction',
        };
    }
}
