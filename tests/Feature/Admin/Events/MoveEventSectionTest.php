<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\EventSection;
use App\Models\User;

beforeEach(function () {
    $this->event = Event::factory()->create();

    foreach (['Opis' => 1, 'Program' => 2, 'Partnerzy' => 3] as $title => $position) {
        $this->{strtolower($title)} = EventSection::factory()->for($this->event)->create(['title' => $title, 'position' => $position]);
    }

    $this->order = fn (): array => $this->event->sections()->pluck('title')->all();
});

test('guests are redirected and users without the admin role are forbidden', function () {
    $url = route('admin.events.sections.move', [$this->event, $this->program]);

    $this->patch($url, ['direction' => 'up'])->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create())
        ->patch($url, ['direction' => 'up'])
        ->assertForbidden();

    expect(($this->order)())->toBe(['Opis', 'Program', 'Partnerzy']);
});

describe('as an administrator', function () {
    beforeEach(function () {
        $this->actingAs(User::factory()->admin()->create());
    });

    test('a section moves one place', function (string $section, string $direction, array $expected) {
        $this->patch(route('admin.events.sections.move', [$this->event, $this->{$section}]), ['direction' => $direction])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.events.edit', $this->event));

        expect(($this->order)())->toBe($expected);
    })->with([
        'up' => ['program', 'up', ['Program', 'Opis', 'Partnerzy']],
        'down' => ['program', 'down', ['Opis', 'Partnerzy', 'Program']],
        'first stays first' => ['opis', 'up', ['Opis', 'Program', 'Partnerzy']],
        'last stays last' => ['partnerzy', 'down', ['Opis', 'Program', 'Partnerzy']],
    ]);

    test('a gap left by a deleted section does not take an extra move', function () {
        $this->program->delete();

        $this->patch(route('admin.events.sections.move', [$this->event, $this->partnerzy]), ['direction' => 'up']);

        expect(($this->order)())->toBe(['Partnerzy', 'Opis']);
    });

    test('sections of other events are left alone', function () {
        $other = EventSection::factory()->create(['position' => 1]);

        $this->patch(route('admin.events.sections.move', [$this->event, $this->opis]), ['direction' => 'up']);

        expect($other->fresh()->position)->toBe(1)
            ->and(($this->order)())->toBe(['Opis', 'Program', 'Partnerzy']);
    });

    test('moving does not count as editing the content', function () {
        $this->travel(1)->hour();

        $this->patch(route('admin.events.sections.move', [$this->event, $this->program]), ['direction' => 'up']);

        expect($this->program->fresh()->updated_at->equalTo($this->program->updated_at))->toBeTrue()
            ->and($this->opis->fresh()->updated_at->equalTo($this->opis->updated_at))->toBeTrue();
    });

    test('a section is only reachable through its own event', function () {
        $this->patch(route('admin.events.sections.move', [Event::factory()->create(), $this->program]), ['direction' => 'up'])
            ->assertNotFound();
    });

    test('the direction has to be valid', function (array $data) {
        $this->patch(route('admin.events.sections.move', [$this->event, $this->program]), $data)
            ->assertSessionHasErrors('direction');

        expect(($this->order)())->toBe(['Opis', 'Program', 'Partnerzy']);
    })->with([
        'missing' => [[]],
        'unknown' => [['direction' => 'sideways']],
    ]);
});
