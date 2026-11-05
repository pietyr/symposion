<?php

declare(strict_types=1);

namespace App\Enums;

enum EventSort: string
{
    case UpcomingThenPast = 'upcoming_then_past';
    case Nearest = 'nearest';
    case Latest = 'latest';
    case RecentlyAdded = 'recently_added';

    public function label(): string
    {
        return match ($this) {
            self::UpcomingThenPast => __('Upcoming and past'),
            self::Nearest => __('Nearest first'),
            self::Latest => __('Latest first'),
            self::RecentlyAdded => __('Recently added'),
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $sort): array => ['value' => $sort->value, 'label' => $sort->label()],
            self::cases(),
        );
    }
}
