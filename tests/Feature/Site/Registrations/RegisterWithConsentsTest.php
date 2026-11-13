<?php

declare(strict_types=1);

use App\Models\Consent;
use App\Models\Event;
use App\Models\Registration;
use App\Models\RegistrationConsent;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Mail::fake();

    $this->event = Event::factory()->create(['starts_at' => now()->addDays(10), 'ends_at' => now()->addDays(10)]);
    $this->participant = User::factory()->create(['profession' => 'doctor']);
    $this->terms = Consent::factory()->for($this->event)->required()->create(['content' => '<p>Akceptuję regulamin.</p>', 'position' => 1]);
    $this->photos = Consent::factory()->for($this->event)->create(['content' => '<p>Zgoda na zdjęcia.</p>', 'position' => 2]);

    $this->register = fn (array $consents) => $this->actingAs($this->participant)->post(
        route('events.registration.store', ['event' => $this->event, 'slug' => $this->event->slug]),
        ['attendance' => 'in_person', 'consents' => $consents],
    );
});

test('the form gets the consents in order', function () {
    $this->actingAs($this->participant)
        ->get(route('events.registration.create', ['event' => $this->event, 'slug' => $this->event->slug]))
        ->assertInertia(fn (Assert $page) => $page
            ->has('consents', 2)
            ->where('consents.0.content', '<p>Akceptuję regulamin.</p>')
            ->where('consents.0.is_required', true)
            ->where('consents.1.is_required', false),
        );
});

test('a required consent has to be given', function () {
    ($this->register)([$this->photos->id])
        ->assertSessionHasErrors(["consents.{$this->terms->id}" => 'Bez tej zgody nie można się zapisać.']);

    expect(Registration::query()->count())->toBe(0);
});

test('the given consents are kept with the content they had', function () {
    ($this->register)([$this->terms->id])->assertSessionHasNoErrors();
    $this->terms->update(['content' => '<p>Nowy regulamin.</p>']);

    expect(Registration::query()->sole()->consents)
        ->toHaveCount(1)
        ->first()->consent_id->toBe($this->terms->id)
        ->first()->content->toBe('<p>Akceptuję regulamin.</p>')
        ->first()->created_at->not->toBeNull();
});

test('an optional consent is kept only when given', function () {
    ($this->register)([$this->terms->id, $this->photos->id]);

    expect(Registration::query()->sole()->consents->pluck('consent_id')->all())->toBe([$this->terms->id, $this->photos->id]);
});

test('consents of another event are ignored', function () {
    $other = Consent::factory()->create();

    ($this->register)([$this->terms->id, $other->id])->assertSessionHasNoErrors();

    expect(Registration::query()->sole()->consents->pluck('consent_id')->all())->toBe([$this->terms->id]);
});

test('a consent participants gave cannot be deleted', function () {
    RegistrationConsent::factory()->for(Registration::factory()->for($this->event))->for($this->terms)->create();

    $this->actingAs(User::factory()->admin()->create())
        ->delete(route('admin.events.consents.destroy', [$this->event, $this->terms]))
        ->assertSessionHasErrors(['consent' => 'Uczestnicy udzielili tej zgody, więc nie można jej usunąć.']);

    expect($this->terms->fresh())->not->toBeNull();
});
