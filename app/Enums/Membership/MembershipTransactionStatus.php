<?php

namespace App\Enums\Membership;

enum MembershipTransactionStatus: int
{
    case WAITING = 1;
    case ACTIVE = 2;
    case FAILED = 3;

    public static function getLabel(int $value): string
    {
        return match ($value) {
            self::WAITING->value => 'Waiting',
            self::ACTIVE->value => 'Success',
            self::FAILED->value => 'Failed',
            default => 'Unknown',
        };
    }
}
