<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Professions are a closed list in code because each one will bring its own registration fields.
 *
 * Adding a case must stay safe: values are stored as plain text, forms list them from cases(),
 * and stored values are read through tryFrom() so an unknown one is skipped, not fatal.
 */
enum Profession: string
{
    case Doctor = 'doctor';
    case Resident = 'resident';
    case Nurse = 'nurse';
    case Midwife = 'midwife';
    case LaboratoryDiagnostician = 'laboratory_diagnostician';
    case LaboratoryTechnician = 'laboratory_technician';
    case Paramedic = 'paramedic';
    case Student = 'student';
    case MedicalRepresentative = 'medical_representative';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Doctor => __('Doctor'),
            self::Resident => __('Resident doctor'),
            self::Nurse => __('Nurse'),
            self::Midwife => __('Midwife'),
            self::LaboratoryDiagnostician => __('Laboratory diagnostician'),
            self::LaboratoryTechnician => __('Medical laboratory technician'),
            self::Paramedic => __('Paramedic'),
            self::Student => __('Student'),
            self::MedicalRepresentative => __('Medical representative'),
            self::Other => __('Other'),
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $profession): array => ['value' => $profession->value, 'label' => $profession->label()],
            self::cases(),
        );
    }

    /**
     * Whether the profession has a number of the right to practise (PWZ).
     */
    public function hasLicenseNumber(): bool
    {
        return in_array($this, [self::Doctor, self::Resident, self::Nurse, self::Midwife, self::LaboratoryDiagnostician, self::Paramedic], true);
    }

    /**
     * Paramedics are still being entered into their new register, so many have no number yet.
     */
    public function requiresLicenseNumber(): bool
    {
        return $this->hasLicenseNumber() && $this !== self::Paramedic;
    }

    /**
     * Doctors pick the specializations they hold; residents are still training (decision of the author).
     */
    public function hasSpecializations(): bool
    {
        return $this === self::Doctor;
    }

    /**
     * Further required fields of the professional profile.
     *
     * @return list<'university'|'student_number'|'company'|'profession_name'>
     */
    public function detailFields(): array
    {
        return match ($this) {
            self::Student => ['university', 'student_number'],
            self::MedicalRepresentative => ['company'],
            self::Other => ['profession_name'],
            default => [],
        };
    }

    /**
     * Options for the registration and profile forms, with the fields each profession asks for.
     *
     * @return list<array{value: string, label: string, license_number: 'required'|'optional'|null, details: list<string>, specializations: bool}>
     */
    public static function profileOptions(): array
    {
        return array_map(
            fn (self $profession): array => [
                'value' => $profession->value,
                'label' => $profession->label(),
                'license_number' => match (true) {
                    $profession->requiresLicenseNumber() => 'required',
                    $profession->hasLicenseNumber() => 'optional',
                    default => null,
                },
                'details' => $profession->detailFields(),
                'specializations' => $profession->hasSpecializations(),
            ],
            self::cases(),
        );
    }
}
