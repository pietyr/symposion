<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Profession;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
final class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = fake()->randomElement(['male', 'female']);

        return [
            // Faker's name() sometimes puts an academic title in front, which does not belong in the name.
            'name' => fake()->firstName($gender).' '.fake()->lastName($gender),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'profession' => null,
            'license_number' => null,
            'university' => null,
            'student_number' => null,
            'company' => null,
            'profession_name' => null,
            'password' => self::$password ??= Hash::make('password'),
            'role' => UserRole::User,
            'remember_token' => Str::random(10),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ];
    }

    /**
     * Indicate that the user is an administrator.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Admin,
        ]);
    }

    /**
     * Indicate that the user is a doctor with a license number that passes the check digit.
     */
    public function doctor(): static
    {
        return $this->state(fn (array $attributes) => [
            'profession' => Profession::Doctor->value,
            'license_number' => self::doctorLicenseNumber(),
        ]);
    }

    /**
     * Seven digits whose first one is the weighted sum of the other six modulo 11 (LicenseNumber rule).
     */
    private static function doctorLicenseNumber(): string
    {
        do {
            $digits = array_map(fn (): int => fake()->numberBetween(0, 9), range(1, 6));
            $checkDigit = array_sum(array_map(fn (int $digit, int $index): int => $digit * ($index + 1), $digits, array_keys($digits))) % 11;
        } while ($checkDigit === 0 || $checkDigit === 10);

        return $checkDigit.implode('', $digits);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }
}
