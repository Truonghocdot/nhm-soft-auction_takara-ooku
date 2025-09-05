<?php

namespace App\Enums;

enum ConfigMembership: string
{
    case FREE_PRODUCT_LISTING = 'free_product_listing';
    case FREE_AUCTION_PARTICIPATION = 'free_auction_participation';
    case DISCOUNT_PERCENTAGE = 'discount_percentage';
    case MAX_PRODUCTS_PER_MONTH = 'max_products_per_month';
    case PRIORITY_SUPPORT = 'priority_support';
    case FEATURED_LISTING = 'featured_listing';

    public function label(): string
    {
        return match ($this) {
            self::FREE_PRODUCT_LISTING => 'Post a free product',
            self::FREE_AUCTION_PARTICIPATION => 'Join a free bid',
            self::DISCOUNT_PERCENTAGE => 'Discount percentage',
            self::MAX_PRODUCTS_PER_MONTH => 'Maximum number of products/month',
            self::PRIORITY_SUPPORT => 'Priority support',
            self::FEATURED_LISTING => 'Featured products',
        };
    }

    public function type(): string
    {
        return match ($this) {
            self::FREE_PRODUCT_LISTING => 'boolean',
            self::FREE_AUCTION_PARTICIPATION => 'boolean',
            self::PRIORITY_SUPPORT => 'boolean',
            self::FEATURED_LISTING => 'boolean',

            self::DISCOUNT_PERCENTAGE => 'percentage',

            self::MAX_PRODUCTS_PER_MONTH => 'number',
        };
    }
}
