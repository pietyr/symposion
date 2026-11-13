<?php

declare(strict_types=1);

use App\Models\Registration;
use App\Models\RegistrationBenefit;
use App\Models\RegistrationConsent;
use App\Models\Specialization;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('only administrators see a registration', function () {
    $registration = Registration::factory()->create();

    $this->get(route('admin.registrations.show', $registration))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create())
        ->get(route('admin.registrations.show', $registration))
        ->assertForbidden();
});

describe('as an administrator', function () {
    beforeEach(function () {
        $this->actingAs(User::factory()->admin()->create());
    });

    test('the registration comes with the professional profile of the participant', function () {
        $doctor = User::factory()->doctor()->create();
        $doctor->specializations()->attach([
            Specialization::factory()->create(['name' => 'Urologia'])->id,
            Specialization::factory()->create(['name' => 'Kardiologia'])->id,
        ]);
        $registration = Registration::factory()->for($doctor)->paid()->create();

        $this->get(route('admin.registrations.show', $registration))
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/registrations/Show')
                ->where('registration.id', $registration->id)
                ->where('registration.status', 'paid')
                ->where('participant.profession_label', 'Lekarz')
                ->where('participant.license_number', $doctor->license_number)
                ->where('participant.specializations', ['Kardiologia', 'Urologia'])
                ->where('participant.university', null),
            );
    });

    test('the registration comes with the chosen benefits and the given consents', function () {
        $registration = Registration::factory()->create();
        RegistrationBenefit::factory()->for($registration)->create(['benefit_name' => 'Nocleg', 'option_name' => '2 noce', 'price' => 55000]);
        $consent = RegistrationConsent::factory()->for($registration)->create(['content' => '<p>Akceptuję regulamin.</p>']);

        $this->get(route('admin.registrations.show', $registration))
            ->assertInertia(fn (Assert $page) => $page
                ->where('registration.benefits.0.option_name', '2 noce')
                ->where('registration.benefits.0.price', 55000)
                ->where('registration.consents.0.content', '<p>Akceptuję regulamin.</p>')
                ->where('registration.consents.0.given_at', $consent->created_at->toIso8601String()),
            );
    });

    test('a registration of a deleted event can still be opened', function () {
        $registration = Registration::factory()->create();
        $registration->event->delete();

        $this->get(route('admin.registrations.show', $registration))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('registration.event.is_deleted', true));
    });

    test('a participant without a profession has no profession label', function () {
        $registration = Registration::factory()->create();

        $this->get(route('admin.registrations.show', $registration))
            ->assertInertia(fn (Assert $page) => $page->where('participant.profession_label', null));
    });
});
