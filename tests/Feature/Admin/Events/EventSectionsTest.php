<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\EventSection;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->event = Event::factory()->create();
    $this->section = EventSection::factory()->for($this->event)->create(['title' => 'Opis', 'position' => 1]);

    $this->routes = [
        ['post', route('admin.events.sections.store', $this->event)],
        ['put', route('admin.events.sections.update', [$this->event, $this->section])],
        ['delete', route('admin.events.sections.destroy', [$this->event, $this->section])],
    ];
});

test('guests are redirected and users without the admin role are forbidden', function () {
    foreach ($this->routes as [$method, $url]) {
        $this->{$method}($url)->assertRedirect(route('login'));
    }

    $this->actingAs(User::factory()->create());

    foreach ($this->routes as [$method, $url]) {
        $this->{$method}($url)->assertForbidden();
    }
});

describe('as an administrator', function () {
    beforeEach(function () {
        $this->actingAs(User::factory()->admin()->create());
    });

    test('a section is added at the end', function () {
        $this->post(route('admin.events.sections.store', $this->event), ['title' => 'Program', 'content' => '<p>9:00</p>'])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.events.edit', $this->event));

        expect($this->event->sections()->pluck('position', 'title')->all())->toBe(['Opis' => 1, 'Program' => 2]);
    });

    test('the content is sanitized when adding and updating', function () {
        $dirty = '<p>Tekst</p><script>alert(1)</script>';

        $this->post(route('admin.events.sections.store', $this->event), ['title' => 'Program', 'content' => $dirty]);
        $this->put(route('admin.events.sections.update', [$this->event, $this->section]), ['title' => 'Opis', 'content' => $dirty]);

        expect(EventSection::query()->pluck('content')->all())->each->toBe('<p>Tekst</p>');
    });

    test('a section can be updated', function () {
        $this->put(route('admin.events.sections.update', [$this->event, $this->section]), ['title' => 'Nowy', 'content' => '<p>Nowa</p>'])
            ->assertRedirect(route('admin.events.edit', $this->event));

        expect($this->section->fresh())
            ->title->toBe('Nowy')
            ->content->toBe('<p>Nowa</p>')
            ->position->toBe(1);
    });

    test('a section can be deleted', function () {
        $this->delete(route('admin.events.sections.destroy', [$this->event, $this->section]))
            ->assertRedirect(route('admin.events.edit', $this->event));

        expect($this->event->sections()->count())->toBe(0);
    });

    test('a section is only reachable through its own event', function () {
        $other = Event::factory()->create();

        $this->put(route('admin.events.sections.update', [$other, $this->section]), ['title' => 'x', 'content' => 'y'])
            ->assertNotFound();
    });

    test('the edit form lists sections in order', function () {
        EventSection::factory()->for($this->event)->create(['title' => 'Trzecia', 'position' => 3]);
        EventSection::factory()->for($this->event)->create(['title' => 'Druga', 'position' => 2]);

        $this->get(route('admin.events.edit', $this->event))
            ->assertInertia(fn (Assert $page) => $page
                ->where('event.sections.0.title', 'Opis')
                ->where('event.sections.1.title', 'Druga')
                ->where('event.sections.2.title', 'Trzecia'),
            );
    });

    test('invalid sections are rejected', function (array $data, string $field) {
        $this->post(route('admin.events.sections.store', $this->event), $data)
            ->assertSessionHasErrors($field);

        expect($this->event->sections()->count())->toBe(1);
    })->with([
        'no title' => [['title' => '', 'content' => 'x'], 'title'],
        'no content' => [['title' => 'x', 'content' => ''], 'content'],
        'too long title' => [['title' => str_repeat('a', 256), 'content' => 'x'], 'title'],
    ]);
});

test('deleting an event for good removes its sections', function () {
    $this->event->forceDelete();

    expect(EventSection::count())->toBe(0);
});
