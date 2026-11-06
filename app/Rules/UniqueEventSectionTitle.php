<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\Event;
use App\Models\EventSection;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * The anchor of a section comes from its title, so "Opis" and "opis!" would point at the same place.
 */
final class UniqueEventSectionTitle implements ValidationRule
{
    public function __construct(
        private readonly Event $event,
        private readonly ?EventSection $section = null,
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $slug = Str::slug(is_string($value) ? $value : '');

        if ($slug === '') {
            $fail(__('The section title must contain a letter or a digit.'));

            return;
        }

        $isTaken = $slug === EventSection::DETAILS_ANCHOR
            || $this->event->sections()->get()->contains(
                fn (EventSection $other): bool => $other->slug === $slug && $other->id !== $this->section?->id,
            );

        if ($isTaken) {
            $fail(__('This event already has a section with this title.'));
        }
    }
}
