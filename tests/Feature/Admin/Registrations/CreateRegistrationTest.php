<?php

declare(strict_types=1);

use App\Enums\RegistrationStatus;
use App\Models\Benefit;
use App\Models\BenefitOption;
use App\Models\Event;
use App\Models\Registration;
use App\Models\RegistrationBenefit;
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

    test('the form offers the benefit fields of each event without hidden options', function () {
        $dinner = Benefit::factory()->for($this->event)->create(['name' => 'Kolacja']);
        BenefitOption::factory()->for($dinner)->create(['name' => 'Udział', 'price' => 18000]);
        BenefitOption::factory()->for($dinner)->create(['name' => 'Ukryta', 'is_hidden' => true]);

        $this->get(route('admin.registrations.create'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('events.0.benefits.0.name', 'Kolacja')
                ->has('events.0.benefits.0.options', 1)
                ->where('events.0.benefits.0.options.0.price', 18000),
            );
    });

    test('the chosen options are kept with the amount the admin set', function () {
        $dinner = Benefit::factory()->for($this->event)->create(['name' => 'Kolacja']);
        $option = BenefitOption::factory()->for($dinner)->create(['name' => 'Udział', 'price' => 18000]);

        ($this->register)(['amount' => '300,00', 'benefits' => [$dinner->id => [$option->id]]])->assertSessionHasNoErrors();

        expect(Registration::query()->sole())
            ->amount->toBe(30000)
            ->benefits->pluck('option_name')->all()->toBe(['Udział']);
    });

    test('the benefit rules apply to a registration by hand', function () {
        $hotel = Benefit::factory()->for($this->event)->select()->create(['name' => 'Nocleg', 'is_required' => true]);
        $full = BenefitOption::factory()->for($hotel)->create(['name' => '1 noc', 'seat_limit' => 1]);
        BenefitOption::factory()->for($hotel)->create(['name' => '2 noce']);
        RegistrationBenefit::factory()->for(Registration::factory()->for($this->event))->for($full, 'option')->create();

        ($this->register)(['benefits' => [$hotel->id => $full->id]])
            ->assertSessionHasErrors(["benefits.{$hotel->id}" => 'Brak miejsc: 1 noc.']);
        ($this->register)()
            ->assertSessionHasErrors(["benefits.{$hotel->id}" => 'Wybierz opcję.']);

        expect(Registration::query()->whereBelongsTo($this->participant)->exists())->toBeFalse();
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
