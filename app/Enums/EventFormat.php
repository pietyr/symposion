<?php

declare(strict_types=1);

namespace App\Enums;

enum EventFormat: string
{
    case Online = 'online';
    case InPerson = 'in_person';
    case Hybrid = 'hybrid';

    public function label(): string
    {
        return match ($this) {
            self::Online => __('Online'),
            self::InPerson => __('In person'),
            self::Hybrid => __('Hybrid'),
        };
    }

    /**
     * Whether participants attend at a physical venue.
     */
    public function hasVenue(): bool
    {
        return $this !== self::Online;
    }

    /**
     * Whether participants can attend remotely.
     */
    public function hasOnlineAttendance(): bool
    {
        return $this !== self::InPerson;
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $format): array => ['value' => $format->value, 'label' => $format->label()],
            self::cases(),
        );
    }
}
