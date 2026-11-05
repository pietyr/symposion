<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Values double as filter values in public (Polish) URLs.
 */
enum EventPriceFilter: string
{
    case Free = 'bezplatne';
    case Paid = 'platne';

    public function label(): string
    {
        return match ($this) {
            self::Free => __('Free'),
            self::Paid => __('Paid'),
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $price): array => ['value' => $price->value, 'label' => $price->label()],
            self::cases(),
        );
    }
}
