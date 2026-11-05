<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EventFormat;
use App\Enums\Voivodeship;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
final class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = now()
            ->addDays(fake()->numberBetween(7, 180))
            ->setTime(9, 0);

        $kinds = ['Konferencja', 'Kongres', 'Sympozjum', 'Warsztaty'];
        $fields = ['Kardiologii', 'Pediatrii', 'Onkologii', 'Neurologii', 'Diabetologii', 'Medycyny Rodzinnej', 'Psychiatrii', 'Pielęgniarstwa'];
        $title = $kinds[fake()->numberBetween(0, count($kinds) - 1)]
            .' '.$fields[fake()->numberBetween(0, count($fields) - 1)]
            .' '.$startsAt->year;

        return [
            'title' => $title,
            // Resolved last, so states that drop the city (online) get a title-only slug.
            'slug' => fn (array $attributes): string => Str::slug(trim($attributes['title'].' '.$attributes['city'])),
            'format' => EventFormat::InPerson,
            'voivodeship' => fake()->randomElement(Voivodeship::cases()),
            'city' => fake()->city(),
            'venue' => fake()->company().', '.fake()->streetAddress(),
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->addDays(fake()->numberBetween(0, 2))->setTime(17, 0),
            'is_visible' => false,
        ];
    }

    /**
     * Indicate that the event appears on the public event lists.
     */
    public function visible(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_visible' => true,
        ]);
    }

    /**
     * Indicate that the event takes place online only.
     */
    public function online(): static
    {
        return $this->state(fn (array $attributes) => [
            'format' => EventFormat::Online,
            'voivodeship' => null,
            'city' => null,
            'venue' => null,
        ]);
    }

    /**
     * Indicate that the event takes place both online and at a venue.
     */
    public function hybrid(): static
    {
        return $this->state(fn (array $attributes) => [
            'format' => EventFormat::Hybrid,
        ]);
    }

    /**
     * Indicate that the event has already taken place.
     */
    public function past(): static
    {
        return $this->state(function (array $attributes) {
            $startsAt = now()
                ->subDays(fake()->numberBetween(7, 365))
                ->setTime(9, 0);

            return [
                'starts_at' => $startsAt,
                'ends_at' => $startsAt->addDays(fake()->numberBetween(0, 2))->setTime(17, 0),
            ];
        });
    }
}
