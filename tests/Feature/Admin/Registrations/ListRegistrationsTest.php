<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('only administrators see the registrations', function () {
    $this->get(route('admin.registrations.index'))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create())
        ->get(route('admin.registrations.index'))
        ->assertForbidden();
});

describe('as an administrator', function () {
    beforeEach(function () {
        $this->actingAs(User::factory()->admin()->create());
    });

    test('registrations are listed newest first with their event and participant', function () {
        $older = Registration::factory()->create(['created_at' => now()->subDay()]);
        $newer = Registration::factory()->paid()->online()->create(['amount' => 0]);

        $this->get(route('admin.registrations.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/registrations/Index')
                ->has('registrations.data', 2)
                ->where('registrations.data.0.id', $newer->id)
                ->where('registrations.data.0.status', 'paid')
                ->where('registrations.data.0.status_label', 'Opłacony')
                ->where('registrations.data.0.attendance_label', 'Online')
                ->where('registrations.data.0.amount', 0)
                ->where('registrations.data.1.id', $older->id)
                ->where('registrations.data.1.user.email', $older->user->email)
                ->where('registrations.data.1.event.title', $older->event->title),
            );
    });

    test('registrations of a deleted event stay on the list, marked', function () {
        $registration = Registration::factory()->create();
        $registration->event->delete();

        $this->get(route('admin.registrations.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('registrations.data.0.event.is_deleted', true)
                ->where('filterOptions.events.0.label', fn (string $label) => str_ends_with($label, '(usunięte)')),
            );
    });

    test('the list can be narrowed down', function (Closure $query, Closure $expected) {
        $event = Event::factory()->hybrid()->create();
        $registrations = [
            'kowalski' => Registration::factory()->for($event)->for(User::factory()->create(['name' => 'Jan Kowalski', 'email' => 'jan@example.com']))->paid()->create(),
            'nowak' => Registration::factory()->for($event)->for(User::factory()->create(['name' => 'Anna Nowak', 'email' => 'anna@example.com']))->online()->create(),
            'other event' => Registration::factory()->cancelled()->create(),
        ];

        $this->get(route('admin.registrations.index', $query($event)))
            ->assertInertia(fn (Assert $page) => $page->where(
                'registrations.data',
                fn ($data) => collect($data)->pluck('id')->sort()->values()->all() === collect($expected($registrations))->pluck('id')->sort()->values()->all(),
            ));
    })->with([
        'event' => [fn (Event $event) => ['event' => $event->id], fn (array $r) => [$r['kowalski'], $r['nowak']]],
        'status' => [fn () => ['status' => 'cancelled'], fn (array $r) => [$r['other event']]],
        'attendance' => [fn () => ['attendance' => 'online'], fn (array $r) => [$r['nowak']]],
        'name' => [fn () => ['search' => 'kowal'], fn (array $r) => [$r['kowalski']]],
        'email' => [fn () => ['search' => 'anna@'], fn (array $r) => [$r['nowak']]],
        'several words' => [fn () => ['search' => 'anna nowak'], fn (array $r) => [$r['nowak']]],
        'unknown values are ignored' => [fn () => ['status' => 'lost', 'attendance' => 'x', 'event' => 'abc'], fn (array $r) => array_values($r)],
    ]);

    test('the selected filters and the options come back to the page', function () {
        $event = Event::factory()->create(['title' => 'Forum']);

        $this->get(route('admin.registrations.index', ['event' => $event->id, 'status' => 'pending', 'search' => 'jan']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters', ['event' => (string) $event->id, 'status' => 'pending', 'attendance' => '', 'search' => 'jan'])
                ->where('filterOptions.events.0', ['value' => (string) $event->id, 'label' => 'Forum'])
                ->has('filterOptions.statuses', 3)
                ->has('filterOptions.attendances', 2),
            );
    });

    test('the list is paginated', function () {
        Registration::factory()->count(16)->create();

        $this->get(route('admin.registrations.index'))
            ->assertInertia(fn (Assert $page) => $page->has('registrations.data', 15)->where('registrations.meta.total', 16));
    });
});
