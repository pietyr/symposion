<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Attendance;
use App\Enums\BenefitType;
use App\Models\Benefit;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Benefit>
 */
final class BenefitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'name' => fake()->randomElement(['Kolacja', 'Nocleg', 'Warsztaty', 'Materiały drukowane']),
            'type' => BenefitType::Checkbox,
            'is_required' => false,
            'attendance' => null,
            'position' => 1,
        ];
    }

    /**
     * Indicate that the participant picks one option from a drop-down list.
     */
    public function select(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => BenefitType::Select,
        ]);
    }

    /**
     * Indicate that only participants attending in person see the field.
     */
    public function inPersonOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'attendance' => Attendance::InPerson,
        ]);
    }
}
