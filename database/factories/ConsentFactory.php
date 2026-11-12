<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Consent;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Consent>
 */
final class ConsentFactory extends Factory
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
            'content' => '<p>Akceptuję <a href="https://example.com/regulamin">regulamin wydarzenia</a>.</p>',
            'is_required' => false,
            'position' => 1,
        ];
    }

    /**
     * Indicate that the participant has to give the consent to register.
     */
    public function required(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_required' => true,
        ]);
    }
}
