<?php

declare(strict_types=1);

use App\Enums\RegistrationStatus;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('only administrators add registrations', function () {
    $this->get(route('admin.registrations.create'))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create());

    $this->get(route('admin.registrations.create'))->assertForbidden();
    $this->post(route('admin.registrations.store'))->assertForbidden();
});

describe('as an administrator', function () {
    beforeEach(function () {
        $this->actingAs(User::factory()->admin()->create());

        $this->event = Event::factory()->hybrid()->create([
            'price_online' => 9900,
            'price_in_person' => 25000,
            'seats_in_person' => 2,
        ]);
        $this->participant = User::factory()->create();

        $this->register = fn (array $data = []) => $this->post(route('admin.registrations.store'), [
            'event_id' => $this->event->id,
            'user_id' => $this->participant->id,
            'attendance' => 'in_person',
            'amount' => '250,00',
            ...$data,
        ]);
    });

    test('the form offers events with their prices and free seats', function () {
        Registration::factory()->for($this->event)->create(['attendance' => 'in_person']);

        $this->get(route('admin.registrations.create'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/registrations/Create')
                ->where('events.0.value', (string) $this->event->id)
                ->where('events.0.attendances', [
                    ['value' => 'online', 'label' => 'Online', 'price' => 9900, 'seats_left' => null],
                    ['value' => 'in_person', 'label' => 'Stacjonarnie', 'price' => 25000, 'seats_left' => 1],
                ])
                ->missing('participants'),
            );
    });

    test('a paid event gets a pending registration with the amount', function () {
        ($this->register)()->assertSessionHasNoErrors();

        $registration = Registration::query()->sole();

        expect($registration)
            ->status->toBe(RegistrationStatus::Pending)
            ->amount->toBe(25000)
            ->confirmed_at->toBeNull();
    });

    test('the admin can change the amount, and a free registration is paid at once', function () {
        ($this->register)(['amount' => '0'])->assertSessionHasNoErrors();

        expect(Registration::query()->sole())
            ->status->toBe(RegistrationStatus::Paid)
            ->amount->toBe(0)
            ->confirmed_at->not->toBeNull();
    });

    test('amounts are typed the Polish way', function () {
        ($this->register)(['amount' => '1 500,50']);

        expect(Registration::query()->sole()->amount)->toBe(150050);
    });

    test('the admin lands on the new registration', function () {
        ($this->register)()->assertRedirect(route('admin.registrations.show', Registration::query()->sole()));
    });

    test('a participant cannot be registered twice', function () {
        Registration::factory()->for($this->event)->for($this->participant)->create();

        ($this->register)()->assertSessionHasErrors('user_id');

        expect(Registration::query()->count())->toBe(1);
    });

    test('a cancelled registration does not block a new one', function () {
        Registration::factory()->for($this->event)->for($this->participant)->cancelled()->create();

        ($this->register)()->assertSessionHasNoErrors();

        expect(Registration::query()->count())->toBe(2);
    });

    test('pending and paid registrations take seats, cancelled ones do not', function () {
        Registration::factory()->for($this->event)->create(['attendance' => 'in_person']);
        Registration::factory()->for($this->event)->paid()->create(['attendance' => 'in_person']);
        Registration::factory()->for($this->event)->cancelled()->create(['attendance' => 'in_person']);

        ($this->register)()->assertSessionHasErrors('attendance');

        ($this->register)(['attendance' => 'online', 'amount' => '99'])->assertSessionHasNoErrors();
    });

    test('invalid registrations are rejected', function (Closure $data, string $field) {
        ($this->register)($data($this))->assertSessionHasErrors($field);

        expect(Registration::query()->count())->toBe(0);
    })->with([
        'no event' => [fn () => ['event_id' => ''], 'event_id'],
        'deleted event' => [fn ($test) => ['event_id' => tap(Event::factory()->create())->delete()->id], 'event_id'],
        'no participant' => [fn () => ['user_id' => 999], 'user_id'],
        'attendance the event does not offer' => [fn () => ['event_id' => Event::factory()->online()->create()->id], 'attendance'],
        'unknown attendance' => [fn () => ['attendance' => 'teleport'], 'attendance'],
        'negative amount' => [fn () => ['amount' => '-1'], 'amount'],
        'no amount' => [fn () => ['amount' => ''], 'amount'],
    ]);
});
