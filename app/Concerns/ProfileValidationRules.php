<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Enums\Profession;
use App\Models\Specialization;
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
     * Fields that only some professions fill in.
     *
     * @var list<string>
     */
    private const array PROFESSION_DETAIL_FIELDS = ['university', 'student_number', 'company', 'profession_name'];

    /**
     * Get the validation rules for the professional profile; fields of other professions are left out.
     *
     * @return array<string, array<int, ValidationRule|object|string>>
     */
    protected function professionRules(mixed $profession, bool $isRequired = true): array
    {
        $profession = is_string($profession) ? Profession::tryFrom($profession) : null;

        $detailFields = $profession?->detailFields() ?? [];
        $rules = [
            'profession' => [$isRequired ? 'required' : 'nullable', Rule::enum(Profession::class)],
            'license_number' => $profession?->hasLicenseNumber()
                ? [$profession->requiresLicenseNumber() ? 'required' : 'nullable', 'string', 'max:20', new LicenseNumber($profession)]
                : ['exclude'],
        ];

        $hasSpecializations = $profession?->hasSpecializations() ?? false;
        $rules['specializations'] = $hasSpecializations ? ['nullable', 'array'] : ['exclude'];
        $rules['specializations.*'] = $hasSpecializations
            ? ['integer', 'distinct', Rule::exists(Specialization::class, 'id')]
            : ['exclude'];

        foreach (self::PROFESSION_DETAIL_FIELDS as $field) {
            $rules[$field] = in_array($field, $detailFields, true)
                ? ['required', 'string', 'max:'.($field === 'student_number' ? 30 : 255)]
                : ['exclude'];
        }

        return $rules;
    }

    /**
     * Professional profile attributes from validated data, clearing what the profession does not use.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, string|null>
     */
    protected function professionAttributes(array $validated): array
    {
        $text = function (string $field) use ($validated): ?string {
            $value = $validated[$field] ?? null;

            return is_string($value) && trim($value) !== '' ? trim($value) : null;
        };

        $attributes = [
            'profession' => $text('profession'),
            'license_number' => ($licenseNumber = $text('license_number')) !== null ? mb_strtoupper($licenseNumber) : null,
        ];

        foreach (self::PROFESSION_DETAIL_FIELDS as $field) {
            $attributes[$field] = $text($field);
        }

        return $attributes;
    }

    /**
     * Specializations from validated data; empty when the profession has none.
     *
     * @param  array<string, mixed>  $validated
     * @return list<int>
     */
    protected function professionSpecializations(array $validated): array
    {
        $specializations = $validated['specializations'] ?? [];

        return is_array($specializations)
            ? array_values(array_map(fn (mixed $id): int => is_numeric($id) ? (int) $id : 0, $specializations))
            : [];
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
