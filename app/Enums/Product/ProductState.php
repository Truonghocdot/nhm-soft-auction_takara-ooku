<?php

namespace App\Enums\Product;

enum ProductState: int
{
    case UNUSED = 0;  // 'chưa sử dụng'
    case RARELY_USED = 1;  // 'hầu như không sử dụng'
    case EXCELLENT = 2;  // 'không có vết xước hoặc bụi bẩn đáng chú ý'
    case SOME_SCRATCHES = 3;  // 'có một số vết xước và bụi bẩn'
    case SCRATCHES_AND_DIRTY = 4;  // 'có vết xước và vết bẩn'
    case POOR_CONDITION = 5;  // 'tình trạng chung là kém'

    public static function getOptions(): array
    {
        return [
            self::UNUSED->value => 'Unused',
            self::RARELY_USED->value => 'Barely used',
            self::EXCELLENT->value => 'No noticeable scratches or dirt',
            self::SOME_SCRATCHES->value => 'Some scratches and dirt',
            self::SCRATCHES_AND_DIRTY->value => 'Scratch and dirt',
            self::POOR_CONDITION->value => 'Poor overall condition'
        ];
    }

    public static function getLabel(ProductState $state): string
    {
        return self::getOptions()[$state->value];
    }
}
