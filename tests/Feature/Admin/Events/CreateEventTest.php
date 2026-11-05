<?php

declare(strict_types=1);

use App\Enums\EventFormat;
use App\Enums\Voivodeship;
use App\Models\Event;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->payload = [
        'title' => 'Ogólnopolski Kongres Kardiologii',
        'format' => EventFormat::InPerson->value,
        'voivodeship' => Voivodeship::Masovian->value,
        'city' => 'Warszawa',
        'venue' => 'Hotel Marriott, Al. Jerozolimskie 65/79',
        'starts_at' => '2027-03-12T09:00',
        'ends_at' => '2027-03-13T17:00',
        'price_in_person' => '150',
    ];
});

test('the create form lists formats and voivodeships', function () {
    $this->get(route('admin.events.create'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/events/Create')
            ->has('formats', count(EventFormat::cases()))
            ->has('voivodeships', count(Voivodeship::cases())),
        );
});

test('an event can be created', function () {
    $this->post(route('admin.events.store'), $this->payload)
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.events.index'));

    $event = Event::sole();

    expect($event)
        ->title->toBe('Ogólnopolski Kongres Kardiologii')
        ->slug->toBe('ogolnopolski-kongres-kardiologii-warszawa')
        ->format->toBe(EventFormat::InPerson)
        ->voivodeship->toBe(Voivodeship::Masovian)
        ->city->toBe('Warszawa')
        ->is_visible->toBeTrue()
        ->and($event->starts_at->format('Y-m-d H:i'))->toBe('2027-03-12 09:00');
});

test('a checked hidden box creates an event outside the lists', function () {
    $this->post(route('admin.events.store'), [...$this->payload, 'is_hidden' => '1']);

    expect(Event::sole()->is_visible)->toBeFalse();
});

test('an event can be promoted', function (?string $checkbox, bool $promoted) {
    $this->post(route('admin.events.store'), [...$this->payload, 'is_promoted' => $checkbox]);

    expect(Event::sole()->is_promoted)->toBe($promoted);
})->with([
    'checked' => ['1', true],
    'unchecked' => [null, false],
]);

test('the invoice title is optional', function (?string $invoiceTitle) {
    $this->post(route('admin.events.store'), [...$this->payload, 'invoice_title' => $invoiceTitle])
        ->assertSessionHasNoErrors();

    expect(Event::sole()->invoice_title)->toBe($invoiceTitle);
})->with([
    'given' => ['Udział w Ogólnopolskim Kongresie Kardiologii'],
    'empty' => [null],
]);

test('prices are stored in grosze', function (string $typed, int $grosze) {
    $this->post(route('admin.events.store'), [...$this->payload, 'price_in_person' => $typed])
        ->assertSessionHasNoErrors();

    expect(Event::sole()->price_in_person)->toBe($grosze);
})->with([
    'whole' => ['150', 15000],
    'comma' => ['150,50', 15050],
    'dot' => ['150.5', 15050],
    'thousands' => ['1 500,99', 150099],
    'not distorted by floats' => ['19,99', 1999],
    'free' => ['0', 0],
]);

test('each format keeps only its own prices', function (EventFormat $format, ?int $online, ?int $inPerson) {
    $this->post(route('admin.events.store'), [
        ...$this->payload,
        'format' => $format->value,
        'price_online' => '100',
        'price_in_person' => '200',
    ])->assertSessionHasNoErrors();

    expect(Event::sole())
        ->price_online->toBe($online)
        ->price_in_person->toBe($inPerson);
})->with([
    'online' => [EventFormat::Online, 10000, null],
    'in person' => [EventFormat::InPerson, null, 20000],
    'hybrid' => [EventFormat::Hybrid, 10000, 20000],
]);

test('online events are stored without a venue', function () {
    $this->post(route('admin.events.store'), [...$this->payload, 'format' => EventFormat::Online->value, 'price_online' => '0'])
        ->assertSessionHasNoErrors();

    expect(Event::sole())
        ->voivodeship->toBeNull()
        ->city->toBeNull()
        ->venue->toBeNull()
        ->slug->toBe('ogolnopolski-kongres-kardiologii');
});

test('a submitted slug is ignored', function () {
    $this->post(route('admin.events.store'), [...$this->payload, 'slug' => 'wlasny-adres']);

    expect(Event::sole()->slug)->toBe('ogolnopolski-kongres-kardiologii-warszawa');
});

test('invalid event data is rejected', function (array $overrides, string $field) {
    $this->post(route('admin.events.store'), [...$this->payload, ...$overrides])
        ->assertSessionHasErrors($field);

    expect(Event::count())->toBe(0);
})->with([
    'missing title' => [['title' => ''], 'title'],
    'too long invoice title' => [['invoice_title' => str_repeat('a', 256)], 'invoice_title'],
    'unknown format' => [['format' => 'webinar'], 'format'],
    'in person without voivodeship' => [['voivodeship' => null], 'voivodeship'],
    'hybrid without city' => [['format' => EventFormat::Hybrid->value, 'city' => ''], 'city'],
    'in person without venue' => [['venue' => ''], 'venue'],
    'unknown voivodeship' => [['voivodeship' => 'bawaria'], 'voivodeship'],
    'in person without a price' => [['price_in_person' => ''], 'price_in_person'],
    'online without a price' => [['format' => EventFormat::Online->value, 'price_in_person' => null], 'price_online'],
    'hybrid without an online price' => [['format' => EventFormat::Hybrid->value], 'price_online'],
    'negative price' => [['price_in_person' => '-1'], 'price_in_person'],
    'too many decimals' => [['price_in_person' => '1,999'], 'price_in_person'],
    'not a number' => [['price_in_person' => 'dużo'], 'price_in_person'],
    'end before start' => [['ends_at' => '2027-03-11T09:00'], 'ends_at'],
    'invalid start date' => [['starts_at' => 'jutro'], 'starts_at'],
]);
