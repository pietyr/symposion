<?php

declare(strict_types=1);

namespace App\Enums;

enum AccountStateFilter: string
{
    case Verified = 'verified';
    case Unverified = 'unverified';

    public function label(): string
    {
        return match ($this) {
            self::Verified => __('Verified accounts'),
            self::Unverified => __('Unverified accounts'),
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $state): array => ['value' => $state->value, 'label' => $state->label()],
            self::cases(),
        );
    }
}
