<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Benefit;
use App\Models\BenefitOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BenefitOption>
 */
final class BenefitOptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'benefit_id' => Benefit::factory(),
            'name' => fake()->randomElement(['1 noc', '2 noce', 'Udział', 'Pakiet podstawowy']),
            'price' => fake()->randomElement([0, 15000, 30000]),
            'seat_limit' => null,
            'is_disabled' => false,
            'is_hidden' => false,
            'position' => 1,
        ];
    }
}
