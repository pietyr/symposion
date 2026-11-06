<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventSection>
 */
final class EventSectionFactory extends Factory
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
            'title' => 'Opis',
            'content' => '<p>'.fake()->paragraph().'</p>',
            'position' => 1,
        ];
    }
}
