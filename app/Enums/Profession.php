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
    case NursingStaff = 'nursing_staff';
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
            self::NursingStaff => __('Nursing staff'),
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
}
