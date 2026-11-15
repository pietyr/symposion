<?php

declare(strict_types=1);

use App\Models\User;

test('only administrators search participants', function () {
    $this->getJson(route('admin.registrations.participants'))->assertUnauthorized();

    $this->actingAs(User::factory()->create())
        ->getJson(route('admin.registrations.participants'))
        ->assertForbidden();
});

describe('as an administrator', function () {
    beforeEach(function () {
        $this->actingAs(User::factory()->admin()->create(['first_name' => 'Administrator', 'last_name' => 'Systemu']));
    });

    test('matching users come back as options', function (string $search, array $names) {
        User::factory()->create(['first_name' => 'Paweł', 'last_name' => 'Grzmotowski', 'email' => 'pawel.testowy@example.com']);
        User::factory()->create(['first_name' => 'Anna', 'last_name' => 'Grzmotowska', 'email' => 'anna.testowa@example.com']);
        User::factory()->create(['first_name' => 'Jan', 'last_name' => 'Brzęczyszczykiewicz', 'email' => 'jan.zupelnie.inny@example.com']);

        $response = $this->getJson(route('admin.registrations.participants', ['search' => $search]))->assertOk();

        expect(collect($response->json())->pluck('label')->all())->toBe($names);
    })->with([
        'part of the surname' => ['grzmot', ['Anna Grzmotowska', 'Paweł Grzmotowski']],
        'email address' => ['anna.testowa@', ['Anna Grzmotowska']],
        'several words in any order' => ['grzmot anna', ['Anna Grzmotowska']],
        'without Polish letters' => ['pawel grzmot', ['Paweł Grzmotowski']],
        'diacritics typed as plain letters' => ['brzeczy', ['Jan Brzęczyszczykiewicz']],
        'nothing matches' => ['xyzzy', []],
    ]);

    test('blocked and deleted users cannot be picked', function () {
        User::factory()->blocked()->create(['first_name' => 'Anna', 'last_name' => 'Grzmotowska']);
        User::factory()->create(['first_name' => 'Paweł', 'last_name' => 'Grzmotowski'])->delete();

        $this->getJson(route('admin.registrations.participants', ['search' => 'grzmot']))
            ->assertExactJson([]);
    });

    test('an option carries the id, the name and the email address', function () {
        $user = User::factory()->create(['first_name' => 'Anna', 'last_name' => 'Grzmotowska', 'email' => 'anna.testowa@example.com']);

        $this->getJson(route('admin.registrations.participants', ['search' => 'grzmot']))
            ->assertExactJson([['value' => (string) $user->id, 'label' => 'Anna Grzmotowska', 'description' => 'anna.testowa@example.com']]);
    });

    test('without a search the first twenty users come alphabetically by surname', function () {
        User::factory()->count(25)->create();

        $response = $this->getJson(route('admin.registrations.participants'))->assertOk();
        $labels = collect($response->json())->pluck('label');

        expect($labels)->toHaveCount(20)
            ->and($labels->all())->toBe(User::query()->orderBy('last_name')->orderBy('first_name')->limit(20)->get()->pluck('name')->all());
    });
});
