<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\Registration;
use App\Models\RegistrationBenefit;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->event = Event::factory()->visible()->create(['starts_at' => now()->addDays(10), 'ends_at' => now()->addDays(10)]);
    $this->participant = User::factory()->create(['name' => 'Jan Kowalski', 'profession' => 'doctor']);
    $this->url = route('events.show', ['event' => $this->event, 'slug' => $this->event->slug]);
});

test('a pending paid registration comes with the transfer details', function () {
    $registration = Registration::factory()->for($this->event)->for($this->participant)->create(['amount' => 25000]);

    $this->actingAs($this->participant)
        ->get($this->url)
        ->assertInertia(fn (Assert $page) => $page
            ->where('myRegistration.id', $registration->id)
            ->where('myRegistration.status', 'pending')
            ->where('myRegistration.attendance_label', 'Stacjonarnie')
            ->where('myRegistration.transfer', [
                'recipient' => 'Symposion sp. z o.o.',
                'account_number' => 'PL46 9999 0000 0000 1234 5678 9012',
                'title' => "Jan Kowalski, {$this->event->title}, {$this->event->starts_at->year}, {$this->event->city}",
                'amount' => 25000,
            ]),
        );
});

test('a paid or free registration needs no transfer', function (array $attributes) {
    Registration::factory()->for($this->event)->for($this->participant)->create($attributes);

    $this->actingAs($this->participant)
        ->get($this->url)
        ->assertInertia(fn (Assert $page) => $page->where('myRegistration.transfer', null));
})->with([
    'paid' => [['status' => 'paid', 'confirmed_at' => now()]],
    'free' => [['amount' => 0, 'status' => 'paid']],
]);

test('the summary lists the chosen benefits', function () {
    $registration = Registration::factory()->for($this->event)->for($this->participant)->create();
    $chosen = RegistrationBenefit::factory()->for($registration)->create(['benefit_name' => 'Nocleg', 'option_name' => '2 noce', 'price' => 55000]);

    $this->actingAs($this->participant)
        ->get($this->url)
        ->assertInertia(fn (Assert $page) => $page->where('myRegistration.benefits', [
            ['id' => $chosen->id, 'benefit_name' => 'Nocleg', 'option_name' => '2 noce', 'price' => 55000],
        ]));
});

test('the summary stays after registration closes', function () {
    Registration::factory()->for($this->event)->for($this->participant)->create();
    $this->event->update(['registration_closes_at' => now()->subHour()]);

    $this->actingAs($this->participant)
        ->get($this->url)
        ->assertInertia(fn (Assert $page) => $page->has('myRegistration.id'));
});

test('without an active registration there is no summary', function () {
    Registration::factory()->for($this->event)->for($this->participant)->cancelled()->create();
    Registration::factory()->for($this->event)->create();

    $this->actingAs($this->participant)->get($this->url)->assertInertia(fn (Assert $page) => $page->where('myRegistration', null));
    $this->get($this->url)->assertInertia(fn (Assert $page) => $page->where('myRegistration', null));
});

test('the transfer title names an online participation and fits the bank limit', function () {
    $this->event->update(['title' => str_repeat('Bardzo długi tytuł wydarzenia ', 8)]);
    $registration = Registration::factory()->for($this->event)->for($this->participant)->online()->create();

    $title = $registration->transferTitle();

    expect(mb_strlen($title))->toBe(140)
        ->and($title)->toStartWith('Jan Kowalski, Bardzo długi tytuł')
        ->and($title)->toEndWith("…, {$this->event->starts_at->year}, Online");
});

test('the bank account comes from the configuration', function () {
    config(['symposion.bank_transfer.account_number' => 'PL00 1111']);
    Registration::factory()->for($this->event)->for($this->participant)->create();

    $this->actingAs($this->participant)
        ->get($this->url)
        ->assertInertia(fn (Assert $page) => $page->where('myRegistration.transfer.account_number', 'PL00 1111'));
});
