<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Enums\Profession;
use App\Models\User;
use App\Rules\LicenseNumber;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

trait ProfileValidationRules
{
    /**
     * Get the validation rules used to validate user profiles.
     *
     * @return array<string, array<int, ValidationRule|Unique|array<mixed>|string>>
     */
    protected function profileRules(?int $userId = null): array
    {
        return [
            'name' => $this->nameRules(),
            'email' => $this->emailRules($userId),
        ];
    }

    /**
     * Get the validation rules for the professional profile; fields of other professions are left out.
     *
     * @return array<string, array<int, ValidationRule|object|string>>
     */
    protected function professionRules(mixed $profession): array
    {
        $profession = is_string($profession) ? Profession::tryFrom($profession) : null;

        return [
            'profession' => ['required', Rule::enum(Profession::class)],
            'license_number' => $profession?->hasLicenseNumber()
                ? [$profession->requiresLicenseNumber() ? 'required' : 'nullable', 'string', 'max:20', new LicenseNumber($profession)]
                : ['exclude'],
        ];
    }

    /**
     * Professional profile attributes from validated data, clearing what the profession does not use.
     *
     * @param  array<string, mixed>  $validated
     * @return array{profession: string|null, license_number: string|null}
     */
    protected function professionAttributes(array $validated): array
    {
        $profession = $validated['profession'] ?? null;
        $licenseNumber = $validated['license_number'] ?? null;

        return [
            'profession' => is_string($profession) ? $profession : null,
            'license_number' => is_string($licenseNumber) && trim($licenseNumber) !== ''
                ? mb_strtoupper(trim($licenseNumber))
                : null,
        ];
    }

    /**
     * Get the validation rules used to validate user names.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function nameRules(): array
    {
        return ['required', 'string', 'max:255'];
    }

    /**
     * Get the validation rules used to validate user emails.
     *
     * @return array<int, ValidationRule|Unique|array<mixed>|string>
     */
    protected function emailRules(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'email',
            'max:255',
            $userId === null
                ? Rule::unique(User::class)
                : Rule::unique(User::class)->ignore($userId),
        ];
    }
}
