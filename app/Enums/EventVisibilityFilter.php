<?php

declare(strict_types=1);

namespace App\Enums;

enum EventVisibilityFilter: string
{
    case Visible = 'visible';
    case Hidden = 'hidden';

    public function label(): string
    {
        return match ($this) {
            self::Visible => __('Visible on the event list'),
            self::Hidden => __('Hidden on the event list'),
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $visibility): array => ['value' => $visibility->value, 'label' => $visibility->label()],
            self::cases(),
        );
    }
}
