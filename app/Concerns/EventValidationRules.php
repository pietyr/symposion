<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Enums\EventFormat;
use App\Enums\Profession;
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
        $format = EventFormat::tryFrom($this->string('format')->value());
        $hasVenue = $format?->hasVenue() ?? false;
        $hasOnlineAttendance = $format?->hasOnlineAttendance() ?? false;
        $priceRules = ['nullable', 'decimal:0,2', 'min:0', 'max:100000'];

        return [
            'title' => ['required', 'string', 'max:255'],
            'invoice_title' => ['nullable', 'string', 'max:255'],
            'format' => ['required', Rule::enum(EventFormat::class)],
            'voivodeship' => [Rule::requiredIf($hasVenue), 'nullable', Rule::enum(Voivodeship::class)],
            'city' => [Rule::requiredIf($hasVenue), 'nullable', 'string', 'max:255'],
            'venue' => [Rule::requiredIf($hasVenue), 'nullable', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'price_online' => [Rule::requiredIf($hasOnlineAttendance), ...$priceRules],
            'price_in_person' => [Rule::requiredIf($hasVenue), ...$priceRules],
            'professions' => ['required', 'array'],
            'professions.*' => ['distinct', Rule::enum(Profession::class)],
            'is_visible' => ['required', 'boolean'],
            'is_promoted' => ['required', 'boolean'],
        ];
    }

    /**
     * The form asks whether the event is hidden, the database stores whether it is visible;
     * an unchecked checkbox is not submitted at all, so a missing flag means visible.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'price_online' => $this->normalizedPrice('price_online'),
            'price_in_person' => $this->normalizedPrice('price_in_person'),
            'is_visible' => ! $this->boolean('is_hidden'),
            'is_promoted' => $this->boolean('is_promoted'),
        ]);
    }

    /**
     * Validated form data with prices converted from zlotys to grosze.
     *
     * @return array<string, mixed>
     */
    public function eventAttributes(): array
    {
        return [
            ...$this->safe()->except('professions'),
            'price_online' => $this->priceInGrosze('price_online'),
            'price_in_person' => $this->priceInGrosze('price_in_person'),
        ];
    }

    /**
     * @return list<string>
     */
    public function eventProfessions(): array
    {
        return array_values($this->array('professions'));
    }

    /**
     * Polish users type "1 500,50"; the validation rules expect "1500.50".
     */
    private function normalizedPrice(string $field): ?string
    {
        return $this->filled($field)
            ? str_replace([' ', ','], ['', '.'], $this->string($field)->value())
            : null;
    }

    /**
     * Split the string instead of multiplying, because a float could lose a grosz.
     */
    private function priceInGrosze(string $field): ?int
    {
        if (! $this->filled($field)) {
            return null;
        }

        [$zlotys, $grosze] = array_pad(explode('.', $this->string($field)->value(), 2), 2, '');

        return (int) $zlotys * 100 + (int) str_pad($grosze, 2, '0');
    }
}
