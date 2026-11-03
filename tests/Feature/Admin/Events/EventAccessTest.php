<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\User;

dataset('event admin routes', [
    'index' => ['get', fn () => route('admin.events.index')],
    'create' => ['get', fn () => route('admin.events.create')],
    'store' => ['post', fn () => route('admin.events.store')],
    'edit' => ['get', fn () => route('admin.events.edit', Event::factory()->create())],
    'update' => ['put', fn () => route('admin.events.update', Event::factory()->create())],
    'destroy' => ['delete', fn () => route('admin.events.destroy', Event::factory()->create())],
    'restore' => ['patch', fn () => route('admin.events.restore', tap(Event::factory()->create())->delete())],
]);

test('guests are redirected to the login page', function (string $method, string $url) {
    $this->{$method}($url)->assertRedirect(route('login'));
})->with('event admin routes');

test('users without the admin role are forbidden', function (string $method, string $url) {
    $this->actingAs(User::factory()->create())
        ->{$method}($url)
        ->assertForbidden();
})->with('event admin routes');
