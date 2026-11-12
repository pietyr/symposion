<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * How the options of a benefit field appear in the registration form (ADR-021).
 */
enum BenefitType: string
{
    case Checkbox = 'checkbox';
    case Radio = 'radio';
    case Select = 'select';

    public function label(): string
    {
        return match ($this) {
            self::Checkbox => __('Checkboxes'),
            self::Radio => __('Single choice'),
            self::Select => __('Drop-down list'),
        };
    }

    /**
     * Single choice and a drop-down list take one option, so only they can require a choice.
     */
    public function takesOneOption(): bool
    {
        return $this !== self::Checkbox;
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $type): array => ['value' => $type->value, 'label' => $type->label()],
            self::cases(),
        );
    }
}
