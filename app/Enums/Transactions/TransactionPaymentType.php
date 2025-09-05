<?php

namespace App\Enums\Transactions;

enum TransactionPaymentType: int
{
    case BUY_PRODUCT = 1;
    case BID_PRODUCT = 2;
    case RECHANGE_POINT = 3;
    case UPGRADE_MEMBERSHIP = 4;

    public static function label(int $type): string
    {
        return match ($type) {
            self::BUY_PRODUCT->value => 'Buy Product',
            self::BID_PRODUCT->value => 'Bid Bid ',
            self::RECHANGE_POINT->value => 'Buy Package & Recharge',
            self::UPGRADE_MEMBERSHIP->value => 'Upgrade package'
        };
    }
}
