<?php

declare(strict_types=1);

use App\Actions\Registrations\CreateRegistration;
use App\Enums\Attendance;
use App\Enums\BenefitType;
use App\Enums\RegistrationStatus;
use App\Models\Benefit;
use App\Models\BenefitOption;
use App\Models\Event;
use App\Models\Registration;
use App\Models\RegistrationBenefit;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Mail::fake();

    $this->event = Event::factory()->hybrid()->create([
        'starts_at' => now()->addDays(10),
        'ends_at' => now()->addDays(11),
        'price_online' => 9900,
        'price_in_person' => 25000,
    ]);
    $this->participant = User::factory()->create(['profession' => 'doctor']);

    $this->hotel = Benefit::factory()->for($this->event)->select()->inPersonOnly()->create(['name' => 'Nocleg', 'position' => 1]);
    $this->oneNight = BenefitOption::factory()->for($this->hotel)->create(['name' => '1 noc', 'price' => 30000, 'seat_limit' => 1, 'position' => 1]);
    $this->twoNights = BenefitOption::factory()->for($this->hotel)->create(['name' => '2 noce', 'price' => 55000, 'position' => 2]);
    $this->threeNights = BenefitOption::factory()->for($this->hotel)->create(['name' => '3 noce', 'price' => 80000, 'is_disabled' => true, 'position' => 3]);
    $this->suite = BenefitOption::factory()->for($this->hotel)->create(['name' => 'Apartament', 'price' => 150000, 'is_hidden' => true, 'position' => 4]);

    $this->dinner = Benefit::factory()->for($this->event)->create(['name' => 'Wieczór', 'position' => 2]);
    $this->gala = BenefitOption::factory()->for($this->dinner)->create(['name' => 'Kolacja', 'price' => 18000, 'position' => 1]);
    $this->cocktail = BenefitOption::factory()->for($this->dinner)->create(['name' => 'Koktajl', 'price' => 5000, 'position' => 2]);

    $this->workshop = Benefit::factory()->for($this->event)->create(['name' => 'Warsztaty', 'type' => BenefitType::Radio, 'is_required' => true, 'position' => 3]);
    $this->ekg = BenefitOption::factory()->for($this->workshop)->create(['name' => 'EKG', 'price' => 0, 'position' => 1]);

    $this->storeUrl = route('events.registration.store', ['event' => $this->event, 'slug' => $this->event->slug]);
    $this->register = fn (array $data) => $this->actingAs($this->participant)->post($this->storeUrl, $data);
});

test('the form gets the fields with the options a participant can see', function () {
    Benefit::factory()->for($this->event)->create(['name' => 'Tylko ukryte', 'position' => 4])
        ->options()->create(['name' => 'Ukryta', 'price' => 0, 'is_hidden' => true, 'position' => 1]);
    $taken = Registration::factory()->for($this->event)->create();
    RegistrationBenefit::factory()->for($taken)->for($this->oneNight, 'option')->create();

    $this->actingAs($this->participant)
        ->get(route('events.registration.create', ['event' => $this->event, 'slug' => $this->event->slug]))
        ->assertInertia(fn (Assert $page) => $page
            ->has('benefits', 3)
            ->where('benefits.0.name', 'Nocleg')
            ->where('benefits.0.type', 'select')
            ->where('benefits.0.attendance', 'in_person')
            ->has('benefits.0.options', 3)
            ->where('benefits.0.options.0', ['id' => $this->oneNight->id, 'name' => '1 noc', 'price' => 30000, 'is_disabled' => false, 'seats_left' => 0])
            ->where('benefits.0.options.2.is_disabled', true)
            ->where('benefits.2.is_required', true),
        );
});

test('the amount adds the chosen options to the attendance price and the choices are kept', function () {
    ($this->register)([
        'attendance' => 'in_person',
        'benefits' => [
            $this->hotel->id => (string) $this->twoNights->id,
            $this->dinner->id => [(string) $this->gala->id, (string) $this->cocktail->id],
            $this->workshop->id => (string) $this->ekg->id,
        ],
    ])->assertSessionHasNoErrors();

    $registration = Registration::query()->sole();

    expect($registration->amount)->toBe(25000 + 55000 + 18000 + 5000)
        ->and($registration->status)->toBe(RegistrationStatus::Pending)
        ->and($registration->benefits()->orderBy('id')->get()->map->only(['benefit_name', 'option_name', 'price'])->all())->toBe([
            ['benefit_name' => 'Nocleg', 'option_name' => '2 noce', 'price' => 55000],
            ['benefit_name' => 'Wieczór', 'option_name' => 'Kolacja', 'price' => 18000],
            ['benefit_name' => 'Wieczór', 'option_name' => 'Koktajl', 'price' => 5000],
            ['benefit_name' => 'Warsztaty', 'option_name' => 'EKG', 'price' => 0],
        ]);
});

test('a free attendance with a paid option waits for the payment', function () {
    $this->event->update(['price_online' => 0]);

    ($this->register)(['attendance' => 'online', 'benefits' => [$this->dinner->id => [$this->gala->id], $this->workshop->id => $this->ekg->id]]);

    expect(Registration::query()->sole())
        ->amount->toBe(18000)
        ->status->toBe(RegistrationStatus::Pending);
});

test('a registration that costs nothing with its options is paid at once', function () {
    $this->event->update(['price_online' => 0]);

    ($this->register)(['attendance' => 'online', 'benefits' => [$this->workshop->id => $this->ekg->id]]);

    expect(Registration::query()->sole()->status)->toBe(RegistrationStatus::Paid);
});

test('a field meant for the other attendance is left out', function () {
    ($this->register)([
        'attendance' => 'online',
        'benefits' => [$this->hotel->id => $this->twoNights->id, $this->workshop->id => $this->ekg->id],
    ])->assertSessionHasNoErrors();

    expect(Registration::query()->sole())
        ->amount->toBe(9900)
        ->benefits->pluck('option_name')->all()->toBe(['EKG']);
});

test('a wrong choice is refused and nothing is saved', function (Closure $choices, string $field, string $message) {
    ($this->register)(['attendance' => 'in_person', 'benefits' => $choices($this) + [$this->workshop->id => $this->ekg->id]])
        ->assertSessionHasErrors(["benefits.{$this->{$field}->id}" => $message]);

    expect(Registration::query()->count())->toBe(0);
})->with([
    'a hidden option' => [fn ($test) => [$test->hotel->id => $test->suite->id], 'hotel', 'Ta opcja jest niedostępna.'],
    'a disabled option' => [fn ($test) => [$test->hotel->id => $test->threeNights->id], 'hotel', 'Ta opcja jest niedostępna.'],
    'an option of another field' => [fn ($test) => [$test->hotel->id => $test->gala->id], 'hotel', 'Ta opcja jest niedostępna.'],
    'an option that does not exist' => [fn ($test) => [$test->dinner->id => [999999]], 'dinner', 'Ta opcja jest niedostępna.'],
    'two options of a single choice' => [fn ($test) => [$test->hotel->id => [$test->oneNight->id, $test->twoNights->id]], 'hotel', 'Wybierz jedną opcję.'],
]);

test('a required field needs a choice', function () {
    ($this->register)(['attendance' => 'online'])
        ->assertSessionHasErrors(["benefits.{$this->workshop->id}" => 'Wybierz opcję.']);
});

test('a required field without any free option does not stop the registration', function () {
    $this->ekg->update(['seat_limit' => 1]);
    RegistrationBenefit::factory()->for(Registration::factory()->for($this->event))->for($this->ekg, 'option')->create();

    ($this->register)(['attendance' => 'online'])->assertSessionHasNoErrors();

    expect(Registration::query()->whereBelongsTo($this->participant)->exists())->toBeTrue();
});

test('pending and paid registrations take the seats of an option, cancelled ones do not', function () {
    $cancelled = Registration::factory()->for($this->event)->cancelled()->create();
    RegistrationBenefit::factory()->for($cancelled)->for($this->oneNight, 'option')->create();

    ($this->register)(['attendance' => 'in_person', 'benefits' => [$this->hotel->id => $this->oneNight->id, $this->workshop->id => $this->ekg->id]])
        ->assertSessionHasNoErrors();

    $this->actingAs(User::factory()->create(['profession' => 'doctor']))
        ->post($this->storeUrl, ['attendance' => 'in_person', 'benefits' => [$this->hotel->id => $this->oneNight->id, $this->workshop->id => $this->ekg->id]])
        ->assertSessionHasErrors(["benefits.{$this->hotel->id}" => 'Brak miejsc: 1 noc.']);
});

test('the last seat of an option is checked again while the event is locked', function () {
    RegistrationBenefit::factory()->for(Registration::factory()->for($this->event))->for($this->oneNight, 'option')->create();

    expect(fn () => app(CreateRegistration::class)->handle($this->event, $this->participant, Attendance::InPerson, 55000, [$this->oneNight->load('benefit')]))
        ->toThrow(ValidationException::class);

    expect(Registration::query()->whereBelongsTo($this->participant)->exists())->toBeFalse();
});

describe('deleting what registrations include', function () {
    beforeEach(function () {
        RegistrationBenefit::factory()->for(Registration::factory()->for($this->event))->for($this->twoNights, 'option')->create();
        $this->actingAs(User::factory()->admin()->create());
    });

    test('an option chosen in a registration cannot be deleted', function () {
        $this->delete(route('admin.events.benefits.options.destroy', [$this->event, $this->hotel, $this->twoNights]))
            ->assertSessionHasErrors(['option' => 'Ta opcja jest w zapisach. Ukryj ją zamiast usuwać.']);

        expect($this->twoNights->fresh())->not->toBeNull();
    });

    test('a field with an option chosen in a registration cannot be deleted', function () {
        $this->delete(route('admin.events.benefits.destroy', [$this->event, $this->hotel]))
            ->assertSessionHasErrors(['benefit' => 'To świadczenie jest w zapisach. Ukryj jego opcje zamiast usuwać.']);

        expect($this->hotel->fresh())->not->toBeNull();
    });

    test('options nobody chose can still be deleted', function () {
        $this->delete(route('admin.events.benefits.options.destroy', [$this->event, $this->hotel, $this->oneNight]))
            ->assertSessionHasNoErrors();

        expect($this->oneNight->fresh())->toBeNull();
    });
});
