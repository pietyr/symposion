<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * How a participant takes part; a hybrid event offers both.
 */
enum Attendance: string
{
    case Online = 'online';
    case InPerson = 'in_person';

    public function label(): string
    {
        return match ($this) {
            self::Online => __('Online'),
            self::InPerson => __('Attending in person'),
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $attendance): array => ['value' => $attendance->value, 'label' => $attendance->label()],
            self::cases(),
        );
    }
}
