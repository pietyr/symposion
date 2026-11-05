<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Profession;
use App\Models\Event;
use App\Models\EventProfession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventProfession>
 */
final class EventProfessionFactory extends Factory
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
            'profession' => Profession::Doctor->value,
        ];
    }
}
