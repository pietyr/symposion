<?php

declare(strict_types=1);

use App\Models\Registration;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('only administrators see the users', function () {
    $this->get(route('admin.users.index'))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create())
        ->get(route('admin.users.index'))
        ->assertForbidden();
});

describe('as an administrator', function () {
    beforeEach(function () {
        $this->admin = User::factory()->admin()->create(['created_at' => now()->subYear()]);
        $this->actingAs($this->admin);
    });

    test('users are listed newest first with their profession and account state', function () {
        $older = User::factory()->doctor()->create(['created_at' => now()->subDay()]);
        $newer = User::factory()->unverified()->blocked()->create(['profession' => 'nurse']);

        $this->get(route('admin.users.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/users/Index')
                ->has('users.data', 3)
                ->where('users.data.0.id', $newer->id)
                ->where('users.data.0.profession_label', 'Pielęgniarka')
                ->where('users.data.0.is_verified', false)
                ->where('users.data.0.is_blocked', true)
                ->where('users.data.1.id', $older->id)
                ->where('users.data.1.name', $older->name)
                ->where('users.data.1.email', $older->email)
                ->where('users.data.1.is_verified', true)
                ->where('users.data.1.is_admin', false)
                ->where('users.data.1.is_blocked', false)
                ->where('users.data.2.id', $this->admin->id)
                ->where('users.data.2.is_admin', true)
                ->where('users.data.2.profession_label', null),
            );
    });

    test('deleted accounts are left out', function () {
        User::factory()->create()->delete();

        $this->get(route('admin.users.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('users.data', 1)
                ->where('users.data.0.id', $this->admin->id),
            );
    });

    test('the deleted accounts have their own list, which takes the filters too', function () {
        $deletedNurse = User::factory()->create(['profession' => 'nurse']);
        $deletedNurse->delete();
        User::factory()->create(['profession' => 'doctor'])->delete();
        User::factory()->create(['profession' => 'nurse']);

        $this->get(route('admin.users.index', ['trashed' => 1, 'profession' => 'nurse']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('users.data', 1)
                ->where('users.data.0.id', $deletedNurse->id)
                ->where('users.data.0.is_deleted', true)
                ->where('filters.trashed', true),
            );
    });

    test('only pending and paid registrations are counted', function () {
        $user = User::factory()->create();
        Registration::factory()->for($user)->create();
        Registration::factory()->for($user)->paid()->create();
        Registration::factory()->for($user)->cancelled()->create();

        $this->get(route('admin.users.index', ['search' => $user->email]))
            ->assertInertia(fn (Assert $page) => $page->where('users.data.0.registrations_count', 2));
    });

    test('the list can be narrowed down', function (array $query, Closure $expected) {
        $users = [
            'doctor' => User::factory()->doctor()->create(['first_name' => 'Jan', 'last_name' => 'Brzęczyszczykiewicz', 'email' => 'jan.testowy@example.com']),
            'nurse' => User::factory()->unverified()->create(['first_name' => 'Anna', 'last_name' => 'Grzmotowska', 'email' => 'anna.testowa@example.com', 'profession' => 'nurse']),
            'blocked' => User::factory()->blocked()->create(['first_name' => 'Ewa', 'last_name' => 'Testowa', 'email' => 'ewa.testowa@example.com', 'profession' => 'midwife']),
        ];

        $this->get(route('admin.users.index', $query))
            ->assertInertia(fn (Assert $page) => $page
                ->where('users.data', fn ($data) => collect($data)->pluck('id')->sort()->values()->all() === collect($expected($users, $this->admin))->pluck('id')->sort()->values()->all()),
            );
    })->with([
        'name' => [['search' => 'brzęcz'], fn (array $users) => [$users['doctor']]],
        'email' => [['search' => 'anna.testowa'], fn (array $users) => [$users['nurse']]],
        'profession' => [['profession' => 'nurse'], fn (array $users) => [$users['nurse']]],
        'verified accounts' => [['state' => 'verified'], fn (array $users, User $admin) => [$users['doctor'], $users['blocked'], $admin]],
        'blocked accounts' => [['state' => 'blocked'], fn (array $users) => [$users['blocked']]],
        'unverified accounts' => [['state' => 'unverified'], fn (array $users) => [$users['nurse']]],
        'unknown values are ignored' => [['profession' => 'astronaut', 'state' => 'sleeping'], fn (array $users, User $admin) => [...array_values($users), $admin]],
    ]);

    test('the applied filters and their options come with the page', function () {
        $this->get(route('admin.users.index', ['profession' => 'doctor', 'state' => 'unverified', 'search' => ' jan ']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters', ['profession' => 'doctor', 'state' => 'unverified', 'search' => 'jan', 'trashed' => false])
                ->has('filterOptions.professions', 10)
                ->where('filterOptions.states', [
                    ['value' => 'verified', 'label' => 'Potwierdzone konta'],
                    ['value' => 'unverified', 'label' => 'Niepotwierdzone konta'],
                    ['value' => 'blocked', 'label' => 'Zablokowane konta'],
                ]),
            );
    });

    test('the list is paginated by fifteen', function () {
        User::factory()->count(15)->create();

        $this->get(route('admin.users.index', ['page' => 2]))
            ->assertInertia(fn (Assert $page) => $page
                ->has('users.data', 1)
                ->where('users.meta.total', 16),
            );
    });
});
