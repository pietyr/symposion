<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Enums\EventFormat;
use App\Enums\Voivodeship;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait EventValidationRules
{
    /**
     * Get the validation rules for the event form; a venue is required unless the event is online.
     *
     * @return array<string, array<int, ValidationRule|object|string>>
     */
    protected function eventRules(): array
    {
        $hasVenue = EventFormat::tryFrom($this->string('format')->value())?->hasVenue() ?? false;

        return [
            'title' => ['required', 'string', 'max:255'],
            'format' => ['required', Rule::enum(EventFormat::class)],
            'voivodeship' => [Rule::requiredIf($hasVenue), 'nullable', Rule::enum(Voivodeship::class)],
            'city' => [Rule::requiredIf($hasVenue), 'nullable', 'string', 'max:255'],
            'venue' => [Rule::requiredIf($hasVenue), 'nullable', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'is_visible' => ['required', 'boolean'],
        ];
    }

    /**
     * The form asks whether the event is hidden, the database stores whether it is visible;
     * an unchecked checkbox is not submitted at all, so a missing flag means visible.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['is_visible' => ! $this->boolean('is_hidden')]);
    }
}
