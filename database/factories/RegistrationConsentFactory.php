<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Consent;
use App\Models\Registration;
use App\Models\RegistrationConsent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RegistrationConsent>
 */
final class RegistrationConsentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'registration_id' => Registration::factory(),
            'consent_id' => Consent::factory(),
            'content' => '<p>Akceptuję regulamin wydarzenia.</p>',
        ];
    }
}
