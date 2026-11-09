<?php

declare(strict_types=1);

namespace App\Rules;

use App\Enums\Profession;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * Number of the right to practise (PWZ), checked the way each professional chamber describes it.
 */
final class LicenseNumber implements ValidationRule
{
    public function __construct(private readonly Profession $profession) {}

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $number = is_string($value) ? mb_strtoupper(trim($value)) : '';

        $isValid = match ($this->profession) {
            Profession::Doctor, Profession::Resident => self::isValidDoctorNumber($number),
            Profession::Nurse => preg_match('/^\d{7}P$/D', $number) === 1,
            Profession::Midwife => preg_match('/^\d{7}A$/D', $number) === 1,
            default => preg_match('/^\d{1,10}$/D', $number) === 1,
        };

        if (! $isValid) {
            $fail(match ($this->profession) {
                Profession::Doctor, Profession::Resident => __('Enter a valid seven-digit license number.'),
                Profession::Nurse => __('The license number of a nurse has 7 digits and the letter P.'),
                Profession::Midwife => __('The license number of a midwife has 7 digits and the letter A.'),
                default => __('The license number may only contain digits.'),
            });
        }
    }

    /**
     * Naczelna Izba Lekarska: seven digits, not starting with 0, the first being a check digit
     * equal to the sum of the other six multiplied by the weights 1 to 6, modulo 11.
     * A remainder of 10 fits no single digit, so such a number is never issued.
     *
     * @see https://nil.org.pl/rejestry/centralny-rejestr-lekarzy/zasady-weryfikowania-nr-prawa-wykonywania-zawodu
     */
    public static function isValidDoctorNumber(string $number): bool
    {
        if (preg_match('/^[1-9]\d{6}$/D', $number) !== 1) {
            return false;
        }

        $sum = 0;

        foreach (range(1, 6) as $weight) {
            $sum += $weight * (int) $number[$weight];
        }

        return $sum % 11 === (int) $number[0];
    }
}
