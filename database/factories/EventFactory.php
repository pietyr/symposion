<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EventFormat;
use App\Enums\Profession;
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
     * Adjective stems of the voivodeships with their capitals, so a city always matches its region.
     *
     * @var array<string, array{string, non-empty-list<string>}>
     */
    private const array REGIONS = [
        'dolnoslaskie' => ['Dolnośląsk', ['Wrocław']],
        'kujawsko-pomorskie' => ['Kujawsko-Pomorsk', ['Bydgoszcz', 'Toruń']],
        'lubelskie' => ['Lubelsk', ['Lublin']],
        'lubuskie' => ['Lubusk', ['Gorzów Wielkopolski', 'Zielona Góra']],
        'lodzkie' => ['Łódzk', ['Łódź']],
        'malopolskie' => ['Małopolsk', ['Kraków']],
        'mazowieckie' => ['Mazowieck', ['Warszawa']],
        'opolskie' => ['Opolsk', ['Opole']],
        'podkarpackie' => ['Podkarpack', ['Rzeszów']],
        'podlaskie' => ['Podlask', ['Białystok']],
        'pomorskie' => ['Pomorsk', ['Gdańsk']],
        'slaskie' => ['Śląsk', ['Katowice']],
        'swietokrzyskie' => ['Świętokrzysk', ['Kielce']],
        'warminsko-mazurskie' => ['Warmińsko-Mazursk', ['Olsztyn']],
        'wielkopolskie' => ['Wielkopolsk', ['Poznań']],
        'zachodniopomorskie' => ['Zachodniopomorsk', ['Szczecin']],
    ];

    /**
     * Event kinds with the grammatical gender their adjectives must agree with.
     *
     * @var non-empty-list<array{string, string}>
     */
    private const array KINDS = [
        ['Kongres', 'masculine'],
        ['Zjazd', 'masculine'],
        ['Konferencja', 'feminine'],
        ['Akademia', 'feminine'],
        ['Sympozjum', 'neuter'],
        ['Spotkanie', 'neuter'],
        ['Forum', 'neuter'],
        ['Warsztaty', 'neuter'],
    ];

    /**
     * @var non-empty-list<string>
     */
    private const array SPECIALTIES = [
        'Anestezjologiczn', 'Chirurgiczn', 'Dermatologiczn', 'Diabetologiczn', 'Geriatryczn',
        'Ginekologiczn', 'Hematologiczn', 'Internistyczn', 'Kardiologiczn', 'Neurologiczn',
        'Okulistyczn', 'Onkologiczn', 'Ortopedyczn', 'Pediatryczn', 'Psychiatryczn',
    ];

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

        $voivodeship = $this->pick(Voivodeship::cases());

        return [
            // Resolved after the states, so online events are not named after a region.
            'title' => fn (array $attributes): string => $this->titleFor($attributes['voivodeship']),
            'slug' => fn (array $attributes): string => Str::slug(trim($attributes['title'].' '.$attributes['city'])),
            'format' => EventFormat::InPerson,
            'voivodeship' => $voivodeship,
            'city' => $this->pick(self::REGIONS[$voivodeship->value][1]),
            'venue' => $this->pick(['Centrum Kongresowe', 'Centrum Konferencyjne', 'Hotel Novotel', 'Hotel Mercure', 'Collegium Medicum', 'Uniwersytet Medyczny'])
                .', ul. '.fake()->streetName().' '.fake()->numberBetween(1, 120),
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->addDays(fake()->numberBetween(0, 2))->setTime(17, 0),
            'price_online' => null,
            'price_in_person' => $this->randomPrice(),
            'is_visible' => false,
            'is_promoted' => false,
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
     * Indicate that the event is featured on the home page.
     */
    public function promoted(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_promoted' => true,
        ]);
    }

    /**
     * Open the event to a random selection of professions.
     */
    public function withProfessions(): static
    {
        return $this->afterCreating(function (Event $event): void {
            $professions = fake()->randomElements(Profession::cases(), fake()->numberBetween(1, count(Profession::cases())));

            $event->professionEntries()->createMany(array_map(
                fn (Profession $profession): array => ['profession' => $profession->value],
                $professions,
            ));
        });
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
            'price_online' => $this->randomPrice(),
            'price_in_person' => null,
        ]);
    }

    /**
     * Indicate that the event takes place both online and at a venue.
     */
    public function hybrid(): static
    {
        return $this->state(fn (array $attributes) => [
            'format' => EventFormat::Hybrid,
            'price_online' => $this->randomPrice(),
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

    /**
     * Compose a title such as "IV Małopolskie Warsztaty Onkologiczne".
     */
    private function titleFor(?Voivodeship $voivodeship): string
    {
        [$kind, $gender] = $this->pick(self::KINDS);

        $regionEnding = ['masculine' => 'i', 'feminine' => 'a', 'neuter' => 'ie'][$gender];
        $specialtyEnding = ['masculine' => 'y', 'feminine' => 'a', 'neuter' => 'e'][$gender];

        $region = match (true) {
            $voivodeship === null => 'Ogólnopolsk'.$regionEnding,
            fake()->boolean(70) => self::REGIONS[$voivodeship->value][0].$regionEnding,
            default => null,
        };

        $edition = fake()->boolean(35)
            ? $this->pick(['II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII', 'XV'])
            : null;

        return implode(' ', array_filter([
            $edition,
            $region,
            $kind,
            $this->pick(self::SPECIALTIES).$specialtyEnding,
        ]));
    }

    /**
     * Gross price in grosze; some events are free.
     */
    private function randomPrice(): int
    {
        return $this->pick([0, 0, 15000, 25000, 39900, 49900, 79900, 120000]);
    }

    /**
     * @template TItem
     *
     * @param  non-empty-list<TItem>  $items
     * @return TItem
     */
    private function pick(array $items): mixed
    {
        return $items[fake()->numberBetween(0, count($items) - 1)];
    }
}
