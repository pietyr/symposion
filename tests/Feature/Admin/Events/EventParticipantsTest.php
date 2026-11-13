<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\Registration;
use App\Models\RegistrationBenefit;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->event = Event::factory()->create(['title' => 'Forum']);
    $this->url = route('admin.events.participants.index', $this->event);
});

test('only administrators see the participants', function () {
    $this->get($this->url)->assertRedirect(route('login'));
    $this->actingAs(User::factory()->create())->get($this->url)->assertForbidden();
});

describe('as an administrator', function () {
    beforeEach(function () {
        $this->actingAs(User::factory()->admin()->create());
    });

    test('the participants come by surname with their profession and benefits', function () {
        $zielinska = Registration::factory()->for($this->event)->for(User::factory()->create(['name' => 'Anna Zielińska', 'profession' => 'nurse']))->paid()->create();
        $adamski = Registration::factory()->for($this->event)->for(User::factory()->create(['name' => 'Piotr Adamski', 'profession' => 'doctor']))->create();
        RegistrationBenefit::factory()->for($adamski)->create(['benefit_name' => 'Nocleg', 'option_name' => '2 noce']);
        Registration::factory()->create();

        $this->get($this->url)
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/events/Participants')
                ->where('event', ['id' => $this->event->id, 'title' => 'Forum', 'is_deleted' => false])
                ->has('registrations.data', 2)
                ->where('registrations.data.0.id', $adamski->id)
                ->where('registrations.data.0.user.profession_label', 'Lekarz')
                ->where('registrations.data.0.benefits.0.option_name', '2 noce')
                ->where('registrations.data.1.id', $zielinska->id)
                ->where('registrations.meta.total', 2),
            );
    });

    test('cancelled registrations are left out', function () {
        Registration::factory()->for($this->event)->cancelled()->create();

        $this->get($this->url)->assertInertia(fn (Assert $page) => $page->has('registrations.data', 0));
    });

    test('the participants of a deleted event can still be seen', function () {
        Registration::factory()->for($this->event)->create();
        $this->event->delete();

        $this->get($this->url)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('event.is_deleted', true)
                ->has('registrations.data', 1),
            );
    });

    test('the list is paginated by fifty', function () {
        Registration::factory()->for($this->event)->count(51)->create();

        $this->get($this->url)
            ->assertInertia(fn (Assert $page) => $page
                ->has('registrations.data', 50)
                ->where('registrations.meta.total', 51),
            );
    });
});
