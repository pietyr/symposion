<?php

declare(strict_types=1);

namespace App\Enums;

enum RegistrationStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('Pending'),
            self::Paid => __('Paid'),
            self::Cancelled => __('Cancelled'),
        };
    }

    /**
     * Whether the registration takes a seat (ADR-019: pending and paid ones do).
     */
    public function takesSeat(): bool
    {
        return $this !== self::Cancelled;
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $status): array => ['value' => $status->value, 'label' => $status->label()],
            self::cases(),
        );
    }
}
