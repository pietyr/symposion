<?php

declare(strict_types=1);

use App\Models\Benefit;
use App\Models\BenefitOption;
use App\Models\Event;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->event = Event::factory()->create();
    $this->benefit = Benefit::factory()->for($this->event)->select()->create(['name' => 'Nocleg']);
    $this->option = BenefitOption::factory()->for($this->benefit)->create(['name' => '1 noc', 'price' => 30000, 'position' => 1]);

    $this->routes = [
        ['post', route('admin.events.benefits.options.store', [$this->event, $this->benefit])],
        ['put', route('admin.events.benefits.options.update', [$this->event, $this->benefit, $this->option])],
        ['delete', route('admin.events.benefits.options.destroy', [$this->event, $this->benefit, $this->option])],
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

    test('the edit page lists the options of each benefit field in order', function () {
        BenefitOption::factory()->for($this->benefit)->create(['name' => '2 noce', 'price' => 55000, 'seat_limit' => 40, 'position' => 2]);

        $this->get(route('admin.events.edit', $this->event))
            ->assertInertia(fn (Assert $page) => $page
                ->where('event.benefits.0.options.0.name', '1 noc')
                ->where('event.benefits.0.options.1', fn ($option) => $option['name'] === '2 noce'
                    && $option['price'] === 55000
                    && $option['seat_limit'] === 40),
            );
    });

    test('an option is added at the end with its price in grosze', function () {
        $this->post(route('admin.events.benefits.options.store', [$this->event, $this->benefit]), [
            'name' => ' 2 noce ',
            'price' => '1 550,50',
            'seat_limit' => '40',
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.events.edit', $this->event));

        expect(BenefitOption::query()->latest('id')->first())
            ->name->toBe('2 noce')
            ->price->toBe(155050)
            ->seat_limit->toBe(40)
            ->position->toBe(2);
    });

    test('an option without a price or limit is free and unlimited', function () {
        $this->post(route('admin.events.benefits.options.store', [$this->event, $this->benefit]), ['name' => 'Bez noclegu', 'price' => '', 'seat_limit' => '']);

        expect(BenefitOption::query()->latest('id')->first())
            ->price->toBe(0)
            ->seat_limit->toBeNull();
    });

    test('an option is updated', function () {
        $this->put(route('admin.events.benefits.options.update', [$this->event, $this->benefit, $this->option]), ['name' => 'Jedna noc', 'price' => '320', 'seat_limit' => '10'])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.events.edit', $this->event));

        expect($this->option->fresh())
            ->name->toBe('Jedna noc')
            ->price->toBe(32000)
            ->seat_limit->toBe(10)
            ->position->toBe(1);
    });

    test('an option is deleted', function () {
        $this->delete(route('admin.events.benefits.options.destroy', [$this->event, $this->benefit, $this->option]))
            ->assertRedirect(route('admin.events.edit', $this->event));

        expect(BenefitOption::query()->count())->toBe(0);
    });

    test('deleting a benefit field deletes its options', function () {
        $this->delete(route('admin.events.benefits.destroy', [$this->event, $this->benefit]));

        expect(BenefitOption::query()->count())->toBe(0);
    });

    test('an option is only reachable through its own benefit field and event', function () {
        $otherBenefit = Benefit::factory()->for($this->event)->create();

        $this->put(route('admin.events.benefits.options.update', [$this->event, $otherBenefit, $this->option]), ['name' => 'X'])
            ->assertNotFound();
        $this->put(route('admin.events.benefits.options.update', [Event::factory()->create(), $this->benefit, $this->option]), ['name' => 'X'])
            ->assertNotFound();
    });

    test('the option data has to be valid', function (array $data, string $field) {
        $this->post(route('admin.events.benefits.options.store', [$this->event, $this->benefit]), [
            'name' => '1 noc',
            ...$data,
        ])->assertSessionHasErrors($field);
    })->with([
        'missing name' => [['name' => ''], 'name'],
        'negative price' => [['price' => '-5'], 'price'],
        'price with three decimals' => [['price' => '1,505'], 'price'],
        'price not a number' => [['price' => 'dużo'], 'price'],
        'zero seats' => [['seat_limit' => '0'], 'seat_limit'],
        'fractional seats' => [['seat_limit' => '2.5'], 'seat_limit'],
    ]);
});
