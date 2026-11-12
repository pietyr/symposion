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
     * Label of something meant for this kind of attendance only, such as a benefit.
     */
    public function onlyLabel(): string
    {
        return match ($this) {
            self::Online => __('Online only'),
            self::InPerson => __('In person only'),
        };
    }

    /**
     * Options for limiting something to one kind of attendance.
     *
     * @return list<array{value: string, label: string}>
     */
    public static function onlyOptions(): array
    {
        return array_map(
            fn (self $attendance): array => ['value' => $attendance->value, 'label' => $attendance->onlyLabel()],
            self::cases(),
        );
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
