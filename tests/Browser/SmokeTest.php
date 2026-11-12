<?php

declare(strict_types=1);

use App\Enums\RegistrationStatus;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Specialization;
use App\Models\User;
use Database\Seeders\SpecializationSeeder;

test('public pages render without javascript errors or console output', function () {
    visit([
        route('home', absolute: false),
        route('events.index', absolute: false),
        route('events.past', absolute: false),
        route('login', absolute: false),
        route('register', absolute: false),
    ])->assertNoSmoke();
});

test('a visitor opens an event from the home page', function () {
    $event = Event::factory()->visible()->create();

    visit(route('home', absolute: false))
        ->click('@event-card')
        ->assertPathIs(route('events.show', ['event' => $event, 'slug' => $event->slug], absolute: false))
        ->assertSee($event->title)
        ->assertNoSmoke();
});

test('an administrator signs in and opens the admin panel from the user menu', function () {
    User::factory()->admin()->create([
        'email' => 'admin@example.com',
    ]);

    visit(route('login', absolute: false))
        ->fill('email', 'admin@example.com')
        ->fill('password', 'password')
        ->click('@login-button')
        ->assertPathIs(route('account.dashboard', absolute: false))
        ->assertSee('Panel użytkownika')
        ->click('@sidebar-menu-button')
        ->click('[role="menuitem"]:has-text("Panel admina")')
        ->assertPathIs(route('admin.dashboard', absolute: false))
        ->assertSee('Administracja')
        ->assertSee('Narzędzia')
        ->assertNoJavaScriptErrors();

    $this->assertAuthenticated();
});

test('an administrator switches between panels from the sidebar', function () {
    $this->actingAs(User::factory()->admin()->create());

    visit(route('account.dashboard', absolute: false))
        ->click('Panel admina')
        ->assertPathIs(route('admin.dashboard', absolute: false))
        ->click('Panel użytkownika')
        ->assertPathIs(route('account.dashboard', absolute: false))
        ->assertNoJavaScriptErrors();
});

test('other users do not get a link to the admin panel', function () {
    $this->actingAs(User::factory()->create());

    visit(route('account.dashboard', absolute: false))
        ->assertSee('Panel użytkownika')
        ->assertDontSee('Panel admina');
});

test('event cards load their default banner', function () {
    Event::factory()->visible()->create();

    $page = visit(route('events.index', absolute: false));
    $page->assertVisible('@event-card-banner')->wait(1);

    expect($page->script('document.querySelector("[data-test=event-card-banner]").naturalWidth'))->toBeGreaterThan(0)
        ->and($page->script('document.querySelector("[data-test=event-card-banner]").currentSrc'))->toContain('/images/event-banners/thumbnail-');
});

test('the event page banner spans the window without a horizontal scrollbar', function () {
    $event = Event::factory()->visible()->create();

    $page = visit(route('events.show', ['event' => $event, 'slug' => $event->slug], absolute: false));
    $page->assertVisible('@event-page-banner')->wait(1);

    expect($page->script('document.querySelector("[data-test=event-page-banner]").naturalWidth'))->toBeGreaterThan(0)
        ->and($page->script('document.querySelector("[data-test=event-page-banner]").currentSrc'))->toContain('/images/event-banners/banner-')
        ->and($page->script('document.querySelector("[data-test=event-page-banner]").getBoundingClientRect().left'))->toBeLessThanOrEqual(0)
        ->and($page->script('document.documentElement.scrollWidth <= window.innerWidth'))->toBeTrue();
});

test('content sections render their html on the event page', function () {
    $event = Event::factory()->visible()->withSections()->create();

    visit(route('events.show', ['event' => $event, 'slug' => $event->slug], absolute: false))
        ->assertSee('Program')
        ->assertVisible('.event-content table')
        ->assertSee('Patronat naukowy')
        ->assertDontSee('<table>')
        ->assertNoJavaScriptErrors();
});

test('the table of contents jumps to a section and stays in view', function () {
    $event = Event::factory()->visible()->create();
    $event->sections()->createMany([
        ['title' => 'Opis', 'position' => 1, 'content' => str_repeat('<p>Tekst</p>', 200)],
        ['title' => 'Program', 'position' => 2, 'content' => str_repeat('<p>Tekst</p>', 200)],
    ]);
    $program = $event->sections()->where('title', 'Program')->sole();

    $page = visit(route('events.show', ['event' => $event, 'slug' => $event->slug], absolute: false));
    $page->click('[data-test="event-toc"] a:has-text("Program")')->wait(2);

    expect($page->script("Math.abs(document.getElementById('program').getBoundingClientRect().top) < 100"))->toBeTrue()
        ->and($page->script('document.querySelector("[data-test=event-toc]").getBoundingClientRect().top >= 0'))->toBeTrue();

    $page->assertVisible('@event-toc')->assertNoJavaScriptErrors();
});

test('the table of contents starts next to the event details and links back to them', function () {
    $event = Event::factory()->visible()->create();
    $event->sections()->create(['title' => 'Opis', 'position' => 1, 'content' => str_repeat('<p>Tekst</p>', 200)]);

    $page = visit(route('events.show', ['event' => $event, 'slug' => $event->slug], absolute: false));

    expect($page->script('Math.abs(document.querySelector("[data-test=event-toc]").getBoundingClientRect().top - document.getElementById("informacje").getBoundingClientRect().top) < 5'))->toBeTrue();

    $page->script('window.scrollTo(0, 2000)');
    $page->click('[data-test="event-toc"] a:has-text("Informacje")')->wait(2);

    expect($page->script('Math.abs(document.getElementById("informacje").getBoundingClientRect().top) < 100'))->toBeTrue();
});

test('an event without sections has no table of contents', function () {
    $event = Event::factory()->visible()->create();

    visit(route('events.show', ['event' => $event, 'slug' => $event->slug], absolute: false))
        ->assertSee($event->title)
        ->assertMissing('@event-toc');
});

test('the site header stays in view while scrolling', function () {
    $event = Event::factory()->visible()->create();
    $event->sections()->create(['title' => 'Opis', 'position' => 1, 'content' => str_repeat('<p>Tekst</p>', 200)]);

    $page = visit(route('events.show', ['event' => $event, 'slug' => $event->slug], absolute: false));
    $page->script('window.scrollTo(0, 1500)');
    $page->wait(0.3);

    expect($page->script('window.scrollY'))->toBeGreaterThan(1000)
        ->and($page->script('document.querySelector("header").getBoundingClientRect().top'))->toBe(0);
});

test('the table of contents stays in the middle of the window while scrolling', function () {
    $event = Event::factory()->visible()->create();
    $event->sections()->create(['title' => 'Opis', 'position' => 1, 'content' => str_repeat('<p>Tekst</p>', 200)]);

    $page = visit(route('events.show', ['event' => $event, 'slug' => $event->slug], absolute: false));
    $page->script('window.scrollTo(0, 2000)');
    $page->wait(0.3);

    expect($page->script('(() => { const box = document.querySelector("[data-test=event-toc]").getBoundingClientRect(); return Math.abs(box.top + box.height / 2 - window.innerHeight / 2) < 3; })()'))->toBeTrue();
});

test('a link with a section anchor opens at that section', function () {
    $event = Event::factory()->visible()->create();
    $event->sections()->create(['title' => 'Opis', 'position' => 1, 'content' => str_repeat('<p>Tekst</p>', 200)]);
    $program = $event->sections()->create(['title' => 'Program', 'position' => 2, 'content' => str_repeat('<p>Tekst</p>', 200)]);

    $url = route('events.show', ['event' => $event, 'slug' => $event->slug], absolute: false);
    $page = visit(route('home', absolute: false));

    // visit() drops the fragment, so the page navigates by itself, as a shared link would.
    $page->script("window.location.href = new URL('{$url}#program', window.location.origin).href");
    $page->wait(1.5);

    expect($page->script("Math.abs(document.getElementById('program').getBoundingClientRect().top) < 100"))->toBeTrue();
});

test('brand colors reach the event page and stay optional', function () {
    $branded = Event::factory()->visible()->withSections()->create(['primary_color' => '#0f766e', 'accent_color' => '#f59e0b']);
    $plain = Event::factory()->visible()->withSections()->create();
    $mark = 'getComputedStyle(document.querySelector("[data-test=event-page-section] h2")).borderLeftColor';

    $page = visit(route('events.show', ['event' => $branded, 'slug' => $branded->slug], absolute: false));
    expect($page->script($mark))->toBe('rgb(15, 118, 110)');

    $page = visit(route('events.show', ['event' => $plain, 'slug' => $plain->slug], absolute: false));
    expect($page->script($mark))->not->toBe('rgb(15, 118, 110)');
});

test('event cards show where the event takes place instead of its price', function () {
    Event::factory()->visible()->create(['title' => 'Stacjonarne', 'city' => 'Kraków', 'price_in_person' => 12300, 'starts_at' => now()->addDays(1), 'ends_at' => now()->addDays(1)]);
    Event::factory()->visible()->online()->create(['title' => 'Zdalne', 'starts_at' => now()->addDays(2), 'ends_at' => now()->addDays(2)]);
    Event::factory()->visible()->hybrid()->create(['title' => 'Mieszane', 'city' => 'Gdańsk', 'starts_at' => now()->addDays(3), 'ends_at' => now()->addDays(3)]);

    $page = visit(route('events.index', absolute: false));
    $places = fn (int $card): string => $page->script("[...document.querySelectorAll('[data-test=event-card]')[{$card}].querySelectorAll('[data-test^=event-place]')].map((badge) => badge.textContent.trim()).join(',')");

    expect($places(0))->toBe('Kraków')
        ->and($places(1))->toBe('Online')
        ->and($places(2))->toBe('Gdańsk,Online');

    $page->assertDontSee('123,00');
});

test('the event page banner carries the name, place and short date', function () {
    $event = Event::factory()->visible()->hybrid()->create([
        'title' => 'Forum Testowe',
        'city' => 'Gdańsk',
        'starts_at' => '2030-04-25 09:00',
        'ends_at' => '2030-04-27 17:00',
    ]);

    $page = visit(route('events.show', ['event' => $event, 'slug' => $event->slug], absolute: false));
    $bar = $page->script('document.querySelector("[data-test=event-banner-bar]").textContent');

    expect($bar)->toContain('Forum Testowe', 'Gdańsk', 'Online', '25–27.04.2030')
        ->and($page->script('document.querySelectorAll("h1").length'))->toBe(1);

    $page->assertSee('Informacje')->assertNoJavaScriptErrors();
});

test('the app logo appears on the site, in the panels and on the auth pages', function () {
    visit(route('home', absolute: false))->assertVisible('@app-logo-icon');
    visit(route('login', absolute: false))->assertVisible('@app-logo-icon');

    $this->actingAs(User::factory()->admin()->create());

    visit(route('admin.dashboard', absolute: false))->assertVisible('@app-logo-icon');
});

test('a visitor registers with a profession', function () {
    visit(route('register', absolute: false))
        ->fill('name', 'Anna Nowak')
        ->fill('email', 'anna@example.com')
        ->click('@profession-select')
        ->click('[role="option"]:has-text("Student")')
        ->fill('university', 'Uniwersytet Medyczny w Łodzi')
        ->fill('student_number', '123456')
        ->fill('password', 'password')
        ->fill('password_confirmation', 'password')
        ->click('@register-user-button')
        ->assertPathIs(route('verification.notice', absolute: false))
        ->assertNoJavaScriptErrors();

    expect(User::query()->where('email', 'anna@example.com')->sole()->only('profession', 'university', 'student_number'))
        ->toBe(['profession' => 'student', 'university' => 'Uniwersytet Medyczny w Łodzi', 'student_number' => '123456']);
});

test('the license number field follows the chosen profession', function () {
    $page = visit(route('register', absolute: false))
        ->assertMissing('@license-number')
        ->click('@profession-select')
        ->click('[role="option"]:has-text("Lekarz rezydent")')
        ->assertVisible('@license-number')
        ->assertSee('Numer prawa wykonywania zawodu (PWZ)');

    $page->click('@profession-select')
        ->click('[role="option"]:has-text("Ratownik")')
        ->assertSee('(PWZ, opcjonalnie)')
        ->click('@profession-select')
        ->click('[role="option"]:has-text("Student")')
        ->assertMissing('@license-number');

    $page->click('@profession-select')
        ->click('[role="option"]:has-text("Pielęgniarka")')
        ->fill('name', 'Anna Nowak')
        ->fill('email', 'anna@example.com')
        ->fill('license_number', '1234567A')
        ->fill('password', 'password')
        ->fill('password_confirmation', 'password')
        ->click('@register-user-button')
        ->assertSee('Numer PWZ pielęgniarki to 7 cyfr i litera P.')
        ->fill('license_number', '1234567P')
        ->click('@register-user-button')
        ->assertPathIs(route('verification.notice', absolute: false));

    expect(User::query()->sole()->license_number)->toBe('1234567P');
});

test('a doctor picks specializations by typing', function () {
    Specialization::factory()->create(['name' => 'Kardiologia']);
    Specialization::factory()->create(['name' => 'Kardiochirurgia']);
    Specialization::factory()->create(['name' => 'Chirurgia ogólna']);

    $page = visit(route('register', absolute: false))
        ->click('@profession-select')
        ->click('[role="option"]:has(span:text-is("Lekarz"))')
        ->click('#specializations')
        ->type('#specializations', 'kardio')
        ->assertSee('Kardiochirurgia')
        ->assertDontSee('Chirurgia ogólna')
        ->click('[role="option"]:has-text("Kardiologia")')
        ->type('#specializations', 'ogol')
        ->click('[role="option"]:has-text("Chirurgia ogólna")');

    $page->fill('name', 'Jan Kowalski')
        ->fill('email', 'jan@example.com')
        ->fill('license_number', '5425740')
        ->fill('password', 'password')
        ->fill('password_confirmation', 'password')
        ->click('@register-user-button')
        ->assertPathIs(route('verification.notice', absolute: false))
        ->assertNoJavaScriptErrors();

    expect(User::query()->sole()->specializations->pluck('name')->sort()->values()->all())->toBe(['Chirurgia ogólna', 'Kardiologia']);
});

test('a long list of specializations scrolls', function () {
    $this->seed(SpecializationSeeder::class);

    $page = visit(route('register', absolute: false))
        ->click('@profession-select')
        ->click('[role="option"]:has(span:text-is("Lekarz"))')
        ->click('#specializations')
        ->assertVisible('[data-slot=combobox-list]');

    // A mouse wheel only moves an element that scrolls; an overflowing one with "hidden" ignores it.
    $overflowing = $page->script('[...document.querySelectorAll("[data-slot=combobox-list], [data-slot=combobox-list] *")]
        .filter((element) => element.scrollHeight > element.clientHeight + 1)
        .map((element) => getComputedStyle(element).overflowY)');

    expect($overflowing)->toBe(['auto']);
});

test('a user edits the professional profile in the settings', function () {
    Specialization::factory()->create(['name' => 'Kardiologia']);
    $user = User::factory()->create(['profession' => 'laboratory_technician']);
    $this->actingAs($user);

    visit(route('account.profile.edit', absolute: false))
        ->assertSee('Technik analityki medycznej')
        ->assertMissing('@license-number')
        ->click('@profession-select')
        ->click('[role="option"]:has(span:text-is("Lekarz"))')
        ->fill('license_number', '5425740')
        ->click('#specializations')
        ->type('#specializations', 'kard')
        ->click('[role="option"]:text-is("Kardiologia")')
        ->click('@update-profile-button')
        ->assertSee('Profil zaktualizowany')
        ->assertNoJavaScriptErrors();

    $user->refresh();
    expect($user->license_number)->toBe('5425740')
        ->and($user->specializations->pluck('name')->all())->toBe(['Kardiologia']);

    visit(route('account.profile.edit', absolute: false))
        ->assertValue('#license_number', '5425740')
        ->assertSee('Kardiologia');
});

test('an administrator opens the registrations from the sidebar and filters them', function () {
    $this->actingAs(User::factory()->admin()->create());
    Registration::factory()->for(User::factory()->create(['name' => 'Jan Kowalski', 'email' => 'jan.kowalski@example.com']))->paid()->create();
    Registration::factory()->for(User::factory()->create(['name' => 'Anna Nowak', 'email' => 'anna.nowak@example.com']))->create();

    visit(route('admin.dashboard', absolute: false))
        ->click('Zapisy')
        ->assertPathIs(route('admin.registrations.index', absolute: false))
        ->assertSee('Jan Kowalski')
        ->assertSee('Opłacony')
        ->fill('@registration-search', 'nowak')
        ->wait(1)
        ->assertSee('Anna Nowak')
        ->assertDontSee('Jan Kowalski')
        ->assertNoJavaScriptErrors();
});

test('an administrator opens a registration from the list', function () {
    $this->actingAs(User::factory()->admin()->create());
    $student = User::factory()->create(['name' => 'Anna Nowak', 'profession' => 'student', 'university' => 'UMED Łódź', 'student_number' => '123456']);
    Registration::factory()->for($student)->create();

    visit(route('admin.registrations.index', absolute: false))
        ->click('Anna Nowak')
        ->assertSee('Szczegóły zapisu')
        ->assertSee('UMED Łódź')
        ->assertSee('123456')
        ->assertSee('Oczekuje')
        ->assertNoJavaScriptErrors();
});

test('an administrator adds a registration by hand', function () {
    $this->actingAs(User::factory()->admin()->create());
    $event = Event::factory()->create(['title' => 'Forum Testowe', 'price_in_person' => 25000]);
    User::factory()->create(['name' => 'Anna Grzmotowska', 'email' => 'anna.testowa@example.com']);
    User::factory()->create(['name' => 'Jan Brzęczyszczykiewicz']);

    visit(route('admin.registrations.index', absolute: false))
        ->click('Dodaj zapis')
        ->click('@event-select')
        ->click('[role="option"]:has-text("Forum Testowe")')
        ->assertSee('Stacjonarnie')
        ->assertValue('#amount', '250,00')
        ->click('@participant-input')
        ->type('@participant-input', 'grzmot')
        ->assertDontSee('Jan Brzęczyszczykiewicz')
        ->click('[role="option"]:has-text("Anna Grzmotowska")')
        ->fill('#amount', '100')
        ->click('@save-registration-button')
        ->assertSee('Zapis został dodany')
        ->assertSee('Anna Grzmotowska')
        ->assertSee('Oczekuje')
        ->assertNoJavaScriptErrors();

    expect(Registration::query()->sole())
        ->event_id->toBe($event->id)
        ->amount->toBe(10000);
});

test('an administrator confirms a payment', function () {
    $this->actingAs(User::factory()->admin()->create());
    $registration = Registration::factory()->create(['amount' => 25000]);

    visit(route('admin.registrations.show', $registration, absolute: false))
        ->click('@confirm-payment')
        ->assertSee('Potwierdzić płatność 250,00 zł?')
        ->click('@confirm-payment-confirm')
        ->assertSee('Płatność została potwierdzona')
        ->assertSee('Opłacony')
        ->assertMissing('@confirm-payment')
        ->assertNoJavaScriptErrors();
});

test('an administrator cancels a registration', function () {
    $this->actingAs(User::factory()->admin()->create());
    $registration = Registration::factory()->paid()->create();

    visit(route('admin.registrations.show', $registration, absolute: false))
        ->click('@cancel-registration')
        ->assertSee('Zwrot płatności trzeba wykonać poza systemem')
        ->click('@cancel-registration-confirm')
        ->assertSee('Zapis został anulowany')
        ->assertSee('Anulowany')
        ->assertMissing('@cancel-registration')
        ->assertMissing('@confirm-payment')
        ->assertNoJavaScriptErrors();
});

test('a guest signs in from the event page and registers', function () {
    $event = Event::factory()->visible()->hybrid()->create([
        'starts_at' => now()->addDays(10),
        'ends_at' => now()->addDays(10),
        'price_online' => 9900,
        'price_in_person' => 25000,
    ]);
    $event->professionEntries()->create(['profession' => 'nurse']);
    $nurse = User::factory()->create(['profession' => 'nurse', 'email' => 'nurse@example.com']);

    visit(route('events.show', ['event' => $event, 'slug' => $event->slug], absolute: false))
        ->click('@register-button')
        ->assertPathIs(route('login', absolute: false))
        ->fill('email', 'nurse@example.com')
        ->fill('password', 'password')
        ->click('@login-button')
        ->assertPathIs(route('events.registration.create', ['event' => $event, 'slug' => $event->slug], absolute: false))
        ->assertSee('Zapis na wydarzenie')
        ->click('@attendance-online')
        ->assertSee('99,00 zł')
        ->click('@confirm-registration-button')
        ->assertPathIs(route('events.show', ['event' => $event, 'slug' => $event->slug], absolute: false))
        ->assertSee('Zapisano na wydarzenie')
        ->assertSee('Twój zapis')
        ->assertSee('Dane do przelewu')
        ->assertSee('PL46 9999 0000 0000 1234 5678 9012')
        ->assertSee('Zapis nr '.Registration::query()->sole()->id)
        ->assertMissing('@register-button')
        ->assertNoJavaScriptErrors();

    expect(Registration::query()->sole())
        ->user_id->toBe($nurse->id)
        ->amount->toBe(9900);
});

test('a participant opens an event from their registrations', function () {
    $participant = User::factory()->create();
    $event = Event::factory()->visible()->create(['title' => 'Zjazd Kardiologów', 'starts_at' => now()->addDays(10), 'ends_at' => now()->addDays(10)]);
    Registration::factory()->for($participant)->for($event)->create();
    $this->actingAs($participant);

    visit(route('account.dashboard', absolute: false))
        ->click('Moje zapisy')
        ->assertPathIs(route('account.registrations.index', absolute: false))
        ->assertSee('Oczekuje')
        ->click('Zjazd Kardiologów')
        ->assertPathIs(route('events.show', ['event' => $event, 'slug' => $event->slug], absolute: false))
        ->assertSee('Dane do przelewu')
        ->assertNoJavaScriptErrors();
});

test('a participant cancels a pending registration', function () {
    $participant = User::factory()->create();
    $registration = Registration::factory()->for($participant)->create();
    $this->actingAs($participant);

    visit(route('account.registrations.index', absolute: false))
        ->click('@cancel-registration')
        ->click('@cancel-registration-confirm')
        ->assertSee('Zapis został anulowany')
        ->assertSee('Anulowany')
        ->assertMissing('@cancel-registration')
        ->assertNoJavaScriptErrors();

    expect($registration->fresh()->status)->toBe(RegistrationStatus::Cancelled);
});

test('the event page says why the visitor cannot register', function () {
    $event = Event::factory()->visible()->create(['starts_at' => now()->addDays(10), 'ends_at' => now()->addDays(10)]);
    $event->professionEntries()->create(['profession' => 'doctor']);
    $url = route('events.show', ['event' => $event, 'slug' => $event->slug], absolute: false);

    $this->actingAs(User::factory()->create(['profession' => 'student']));
    visit($url)
        ->assertMissing('@register-button')
        ->assertSee('To wydarzenie nie jest przeznaczone dla Twojego zawodu.');

    $this->actingAs(User::factory()->create());
    visit($url)
        ->assertSee('Aby się zapisać, uzupełnij zawód w ustawieniach profilu.')
        ->click('Przejdź do ustawień profilu')
        ->assertPathIs(route('account.profile.edit', absolute: false));

    $event->update(['registration_opens_at' => now()->addDays(2)]);
    visit($url)
        ->assertSee('Zapisy rozpoczną się')
        ->assertNoJavaScriptErrors();
});
