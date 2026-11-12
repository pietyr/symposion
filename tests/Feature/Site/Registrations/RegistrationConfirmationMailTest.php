<?php

declare(strict_types=1);

use App\Mail\RegistrationConfirmation;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();

    $this->event = Event::factory()->hybrid()->create([
        'title' => 'Forum Kardiologiczne',
        'city' => 'Katowice',
        'venue' => 'Centrum Kongresowe',
        'starts_at' => now()->addDays(10)->setTime(9, 0),
        'ends_at' => now()->addDays(10)->setTime(17, 0),
        'price_online' => 0,
        'price_in_person' => 25000,
    ]);
    $this->participant = User::factory()->create(['profession' => 'doctor']);
    $this->storeUrl = route('events.registration.store', ['event' => $this->event, 'slug' => $this->event->slug]);
});

test('registering queues a confirmation for the participant', function () {
    $this->actingAs($this->participant)->post($this->storeUrl, ['attendance' => 'in_person']);

    Mail::assertQueued(RegistrationConfirmation::class, fn (RegistrationConfirmation $mail) => $mail->hasTo($this->participant->email)
        && $mail->registration->is(Registration::query()->sole()));
});

test('no confirmation goes out when registration fails', function () {
    $this->event->update(['registration_closes_at' => now()->subHour()]);

    $this->actingAs($this->participant)->post($this->storeUrl, ['attendance' => 'in_person']);

    Mail::assertNothingQueued();
});

test('a registration added by the administrator sends no confirmation', function () {
    $this->actingAs(User::factory()->admin()->create())->post(route('admin.registrations.store'), [
        'event_id' => $this->event->id,
        'user_id' => $this->participant->id,
        'attendance' => 'in_person',
        'amount' => '250,00',
    ])->assertSessionHasNoErrors();

    Mail::assertNothingQueued();
});

test('a pending registration comes with the transfer details', function () {
    $registration = Registration::factory()->for($this->event)->for($this->participant)->create(['amount' => 25000]);

    $mail = new RegistrationConfirmation($registration);

    $mail->assertHasSubject('Zapis na „Forum Kardiologiczne”');
    $mail->assertSeeInOrderInHtml([
        'Przyjęliśmy Twój zapis na „Forum Kardiologiczne”.',
        'Zapis zostanie potwierdzony po zaksięgowaniu wpłaty.',
        $this->event->starts_at->format('d.m.Y').', 09:00–17:00',
        'Katowice, Centrum Kongresowe',
        'Stacjonarnie',
        'Dane do przelewu',
        'Symposion sp. z o.o.',
        'PL46 9999 0000 0000 1234 5678 9012',
        "Zapis nr {$registration->id}",
        'Przejdź do wydarzenia',
    ]);
    $mail->assertSeeInHtml(route('events.show', ['event' => $this->event, 'slug' => $this->event->slug]));
});

test('a free online registration is confirmed without transfer details', function () {
    $registration = Registration::factory()->for($this->event)->for($this->participant)->online()->paid()->create(['amount' => 0]);

    $mail = new RegistrationConfirmation($registration);

    $mail->assertSeeInOrderInHtml(['Zapis jest potwierdzony.', 'Online', 'Bezpłatne']);
    $mail->assertDontSeeInHtml('Dane do przelewu');
    $mail->assertDontSeeInHtml('Miejsce');
});
