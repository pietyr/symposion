<?php

declare(strict_types=1);

use App\Models\Benefit;
use App\Models\BenefitOption;
use App\Models\User;

beforeEach(function () {
    $this->benefit = Benefit::factory()->create();
    $this->event = $this->benefit->event;
    $this->oneNight = BenefitOption::factory()->for($this->benefit)->create(['name' => '1 noc', 'position' => 1]);
    $this->twoNights = BenefitOption::factory()->for($this->benefit)->create(['name' => '2 noce', 'position' => 2]);

    $this->url = route('admin.events.benefits.options.move', [$this->event, $this->benefit, $this->twoNights]);
    $this->order = fn (): array => $this->benefit->options()->pluck('name')->all();
});

test('only administrators move options', function () {
    $this->patch($this->url, ['direction' => 'up'])->assertRedirect(route('login'));
    $this->actingAs(User::factory()->create())->patch($this->url, ['direction' => 'up'])->assertForbidden();

    expect(($this->order)())->toBe(['1 noc', '2 noce']);
});

test('an option moves one place within its benefit field', function () {
    $other = BenefitOption::factory()->create(['position' => 1]);
    $this->actingAs(User::factory()->admin()->create());

    $this->patch($this->url, ['direction' => 'up'])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.events.edit', $this->event));

    expect(($this->order)())->toBe(['2 noce', '1 noc'])
        ->and($other->fresh()->position)->toBe(1);
});
