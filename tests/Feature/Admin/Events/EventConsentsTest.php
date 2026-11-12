<?php

declare(strict_types=1);

use App\Models\Consent;
use App\Models\Event;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->event = Event::factory()->create();
    $this->consent = Consent::factory()->for($this->event)->required()->create(['position' => 1]);

    $this->routes = [
        ['post', route('admin.events.consents.store', $this->event)],
        ['put', route('admin.events.consents.update', [$this->event, $this->consent])],
        ['delete', route('admin.events.consents.destroy', [$this->event, $this->consent])],
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

    test('the edit page lists the consents in order', function () {
        Consent::factory()->for($this->event)->create(['content' => '<p>Zgoda na zdjęcia</p>', 'position' => 2]);

        $this->get(route('admin.events.edit', $this->event))
            ->assertInertia(fn (Assert $page) => $page
                ->where('event.consents.0.is_required', true)
                ->where('event.consents.1.content', '<p>Zgoda na zdjęcia</p>')
                ->where('event.consents.1.is_required', false),
            );
    });

    test('a consent is added at the end with its markup sanitized', function () {
        $this->post(route('admin.events.consents.store', $this->event), [
            'content' => '<p>Akceptuję <a href="https://example.com/regulamin">regulamin</a></p><script>alert(1)</script>',
            'is_required' => '1',
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.events.edit', $this->event));

        expect(Consent::query()->latest('id')->first())
            ->content->toBe('<p>Akceptuję <a href="https://example.com/regulamin">regulamin</a></p>')
            ->is_required->toBeTrue()
            ->position->toBe(2);
    });

    test('a consent is optional unless marked as required', function () {
        $this->post(route('admin.events.consents.store', $this->event), ['content' => '<p>Newsletter</p>']);

        expect(Consent::query()->latest('id')->first()->is_required)->toBeFalse();
    });

    test('a consent is updated', function () {
        $this->put(route('admin.events.consents.update', [$this->event, $this->consent]), ['content' => '<p>Nowa treść</p><img src="x" onerror="alert(1)">'])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.events.edit', $this->event));

        expect($this->consent->fresh())
            ->content->not->toContain('onerror')
            ->content->toContain('<p>Nowa treść</p>')
            ->is_required->toBeFalse()
            ->position->toBe(1);
    });

    test('a consent is deleted', function () {
        $this->delete(route('admin.events.consents.destroy', [$this->event, $this->consent]))
            ->assertRedirect(route('admin.events.edit', $this->event));

        expect(Consent::query()->count())->toBe(0);
    });

    test('a consent is only reachable through its own event', function () {
        $this->put(route('admin.events.consents.update', [Event::factory()->create(), $this->consent]), ['content' => '<p>X</p>'])
            ->assertNotFound();
    });

    test('the consent data has to be valid', function (array $data, string $field) {
        $this->post(route('admin.events.consents.store', $this->event), [
            'content' => '<p>Regulamin</p>',
            ...$data,
        ])->assertSessionHasErrors($field);
    })->with([
        'missing content' => [['content' => ''], 'content'],
        'content too long' => [['content' => str_repeat('a', 10001)], 'content'],
        'required not a boolean' => [['is_required' => 'maybe'], 'is_required'],
    ]);
});
