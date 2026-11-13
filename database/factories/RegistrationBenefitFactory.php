<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\BenefitOption;
use App\Models\Registration;
use App\Models\RegistrationBenefit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RegistrationBenefit>
 */
final class RegistrationBenefitFactory extends Factory
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
            'benefit_option_id' => BenefitOption::factory(),
            'benefit_name' => 'Nocleg',
            'option_name' => '1 noc',
            'price' => 30000,
        ];
    }
}
