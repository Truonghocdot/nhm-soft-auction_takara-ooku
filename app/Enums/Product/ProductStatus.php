<?php

namespace App\Enums\Product;

enum ProductStatus: int
{
    case INACTIVE = 0;
    case ACTIVE = 1;

    public static function getOptions(): array
    {
        return [
            self::INACTIVE->value => 'Not for sale',
            self::ACTIVE->value => 'Open for sale',
        ];
    }
}
