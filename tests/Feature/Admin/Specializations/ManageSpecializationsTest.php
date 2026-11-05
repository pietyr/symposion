<?php

declare(strict_types=1);

use App\Models\Specialization;
use App\Models\User;
use Database\Seeders\SpecializationSeeder;
use Inertia\Testing\AssertableInertia as Assert;

dataset('specialization admin routes', [
    'index' => ['get', fn () => route('admin.specializations.index')],
    'create' => ['get', fn () => route('admin.specializations.create')],
    'store' => ['post', fn () => route('admin.specializations.store')],
    'edit' => ['get', fn () => route('admin.specializations.edit', Specialization::factory()->create())],
    'update' => ['put', fn () => route('admin.specializations.update', Specialization::factory()->create())],
    'destroy' => ['delete', fn () => route('admin.specializations.destroy', Specialization::factory()->create())],
]);

test('guests are redirected to the login page', function (string $method, string $url) {
    $this->{$method}($url)->assertRedirect(route('login'));
})->with('specialization admin routes');

test('users without the admin role are forbidden', function (string $method, string $url) {
    $this->actingAs(User::factory()->create())
        ->{$method}($url)
        ->assertForbidden();
})->with('specialization admin routes');

describe('as an administrator', function () {
    beforeEach(function () {
        $this->actingAs(User::factory()->admin()->create());
    });

    test('specializations are listed alphabetically and paginated', function () {
        Specialization::factory()->create(['name' => 'Urologia']);
        Specialization::factory()->create(['name' => 'Alergologia']);
        Specialization::factory()->count(15)->create();

        $this->get(route('admin.specializations.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/specializations/Index')
                ->has('specializations.data', 15)
                ->where('specializations.data.0.name', 'Alergologia')
                ->where('specializations.meta.total', 17),
            );
    });

    test('a specialization can be created', function () {
        $this->post(route('admin.specializations.store'), ['name' => 'Hematologia'])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.specializations.index'));

        expect(Specialization::sole()->name)->toBe('Hematologia');
    });

    test('a specialization can be renamed, also to its own name', function (string $name) {
        $specialization = Specialization::factory()->create(['name' => 'Hematologia']);

        $this->put(route('admin.specializations.update', $specialization), ['name' => $name])
            ->assertSessionHasNoErrors();

        expect($specialization->fresh()->name)->toBe($name);
    })->with(['Hematologia kliniczna', 'Hematologia']);

    test('the edit form shows the specialization', function () {
        $specialization = Specialization::factory()->create();

        $this->get(route('admin.specializations.edit', $specialization))
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/specializations/Edit')
                ->where('specialization.id', $specialization->id),
            );
    });

    test('a specialization can be deleted', function () {
        $specialization = Specialization::factory()->create();

        $this->delete(route('admin.specializations.destroy', $specialization))
            ->assertRedirect(route('admin.specializations.index'));

        expect(Specialization::count())->toBe(0);
    });

    test('invalid names are rejected', function (string $name) {
        Specialization::factory()->create(['name' => 'Kardiologia']);

        $this->post(route('admin.specializations.store'), ['name' => $name])
            ->assertSessionHasErrors('name');

        expect(Specialization::count())->toBe(1);
    })->with([
        'empty' => [''],
        'taken' => ['Kardiologia'],
        'too long' => [str_repeat('a', 256)],
    ]);

    test('validation messages call the field a specialization name', function () {
        $this->post(route('admin.specializations.store'), ['name' => ''])
            ->assertSessionHasErrors(['name' => 'Pole nazwa specjalizacji jest wymagane.']);
    });
});

test('the seeder fills the list and can run again', function () {
    $this->seed(SpecializationSeeder::class);
    $count = Specialization::count();

    $this->seed(SpecializationSeeder::class);

    expect($count)->toBeGreaterThan(0)
        ->and(Specialization::count())->toBe($count)
        ->and(Specialization::where('name', 'Hematologia')->exists())->toBeTrue();
});
