<?php

declare(strict_types=1);

use App\Enums\Attendance;
use App\Enums\BenefitType;
use App\Models\Benefit;
use App\Models\Event;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->event = Event::factory()->hybrid()->create();
    $this->benefit = Benefit::factory()->for($this->event)->create(['name' => 'Kolacja', 'position' => 1]);

    $this->routes = [
        ['post', route('admin.events.benefits.store', $this->event)],
        ['put', route('admin.events.benefits.update', [$this->event, $this->benefit])],
        ['delete', route('admin.events.benefits.destroy', [$this->event, $this->benefit])],
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

    test('the edit page lists the benefit fields in order with the choices for the form', function () {
        Benefit::factory()->for($this->event)->select()->inPersonOnly()->create(['name' => 'Nocleg', 'position' => 2, 'is_required' => true]);

        $this->get(route('admin.events.edit', $this->event))
            ->assertInertia(fn (Assert $page) => $page
                ->where('event.benefits.0.name', 'Kolacja')
                ->where('event.benefits.1', fn ($benefit) => $benefit['name'] === 'Nocleg'
                    && $benefit['type'] === 'select'
                    && $benefit['type_label'] === 'Lista rozwijana'
                    && $benefit['is_required'] === true
                    && $benefit['attendance_label'] === 'Tylko stacjonarnie')
                ->has('benefitTypes', 3)
                ->where('benefitAttendances.1', ['value' => 'in_person', 'label' => 'Tylko stacjonarnie']),
            );
    });

    test('a benefit field is added at the end', function () {
        $this->post(route('admin.events.benefits.store', $this->event), [
            'name' => '  Nocleg ',
            'type' => 'radio',
            'is_required' => '1',
            'attendance' => 'in_person',
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.events.edit', $this->event));

        expect(Benefit::query()->latest('id')->first())
            ->name->toBe('Nocleg')
            ->type->toBe(BenefitType::Radio)
            ->is_required->toBeTrue()
            ->attendance->toBe(Attendance::InPerson)
            ->position->toBe(2);
    });

    test('checkboxes never require a choice', function () {
        $this->post(route('admin.events.benefits.store', $this->event), ['name' => 'Warsztaty', 'type' => 'checkbox', 'is_required' => '1']);

        expect(Benefit::query()->latest('id')->first()->is_required)->toBeFalse();
    });

    test('only a hybrid event limits a field to one kind of attendance', function () {
        $event = Event::factory()->create();

        $this->post(route('admin.events.benefits.store', $event), ['name' => 'Kolacja', 'type' => 'checkbox', 'attendance' => 'online']);

        expect($event->benefits()->sole()->attendance)->toBeNull();
    });

    test('a field that leaves the attendance empty is for every participant', function () {
        $this->post(route('admin.events.benefits.store', $this->event), ['name' => 'Materiały', 'type' => 'checkbox', 'attendance' => '']);

        expect(Benefit::query()->latest('id')->first()->attendance)->toBeNull();
    });

    test('a benefit field is updated', function () {
        $this->put(route('admin.events.benefits.update', [$this->event, $this->benefit]), ['name' => 'Kolacja galowa', 'type' => 'select'])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.events.edit', $this->event));

        expect($this->benefit->fresh())
            ->name->toBe('Kolacja galowa')
            ->type->toBe(BenefitType::Select)
            ->position->toBe(1);
    });

    test('a benefit field is deleted', function () {
        $this->delete(route('admin.events.benefits.destroy', [$this->event, $this->benefit]))
            ->assertRedirect(route('admin.events.edit', $this->event));

        expect(Benefit::query()->count())->toBe(0);
    });

    test('a benefit field is only reachable through its own event', function () {
        $this->put(route('admin.events.benefits.update', [Event::factory()->create(), $this->benefit]), ['name' => 'X', 'type' => 'checkbox'])
            ->assertNotFound();
    });

    test('the field data has to be valid', function (array $data, string $field) {
        $this->post(route('admin.events.benefits.store', $this->event), [
            'name' => 'Nocleg',
            'type' => 'select',
            ...$data,
        ])->assertSessionHasErrors($field);
    })->with([
        'missing name' => [['name' => ''], 'name'],
        'name too long' => [['name' => str_repeat('a', 256)], 'name'],
        'unknown type' => [['type' => 'slider'], 'type'],
        'unknown attendance' => [['attendance' => 'remote'], 'attendance'],
    ]);

    test('a field stops being limited to one attendance when the event is no longer hybrid', function () {
        $this->benefit->update(['attendance' => Attendance::InPerson]);

        $this->put(route('admin.events.update', $this->event), [
            'title' => $this->event->title,
            'format' => 'in_person',
            'voivodeship' => 'slaskie',
            'city' => 'Katowice',
            'venue' => 'Centrum Kongresowe',
            'starts_at' => $this->event->starts_at->format('Y-m-d\TH:i'),
            'ends_at' => $this->event->ends_at->format('Y-m-d\TH:i'),
            'price_in_person' => '100,00',
            'professions' => ['doctor'],
        ])->assertSessionHasNoErrors();

        expect($this->benefit->fresh()->attendance)->toBeNull();
    });
});
