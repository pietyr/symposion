<?php

declare(strict_types=1);

use App\Models\Event;

function promotedEvents(int $count): void
{
    foreach (range(1, $count) as $number) {
        Event::factory()->visible()->promoted()->create([
            'title' => "Polecane {$number}",
            'starts_at' => now()->addDays($number),
            'ends_at' => now()->addDays($number),
        ]);
    }
}

function firstVisibleCard(): string
{
    return '(() => {
        const track = document.querySelector("[data-test=promoted-track]");
        const left = track.getBoundingClientRect().left;
        const card = [...track.children].find((child) => Math.abs(child.getBoundingClientRect().left - left) < 5);

        return card?.querySelector("[data-slot=card-title]")?.textContent.trim() ?? "none";
    })()';
}

test('the arrows go around in both directions', function () {
    promotedEvents(5);

    $page = visit('/');
    $page->wait(0.3);

    expect($page->script(firstVisibleCard()))->toBe('Polecane 1');

    $page->click('@promoted-previous')->wait(1);
    expect($page->script(firstVisibleCard()))->toBe('Polecane 5');

    $page->click('@promoted-next')->wait(1);
    expect($page->script(firstVisibleCard()))->toBe('Polecane 1');

    foreach (range(2, 5) as $number) {
        $page->click('@promoted-next')->wait(0.8);
        expect($page->script(firstVisibleCard()))->toBe("Polecane {$number}");
    }

    $page->click('@promoted-next')->wait(1);
    expect($page->script(firstVisibleCard()))->toBe('Polecane 1');

    $page->assertNoJavaScriptErrors();
});

test('it moves by itself', function () {
    promotedEvents(5);

    $page = visit('/');
    $page->wait(6);

    expect($page->script(firstVisibleCard()))->toBe('Polecane 2');
});

test('it stays still while the pointer is over it', function () {
    promotedEvents(5);

    $page = visit('/');
    $page->hover('@promoted-track')->wait(6);

    expect($page->script(firstVisibleCard()))->toBe('Polecane 1');
});

test('cards that all fit do not move or repeat', function () {
    promotedEvents(3);

    $page = visit('/');
    $page->wait(6)->assertMissing('@promoted-next');

    expect($page->script('document.querySelector("[data-test=promoted-track]").children.length'))->toBe(3)
        ->and($page->script(firstVisibleCard()))->toBe('Polecane 1');
});

test('only the original cards are exposed to assistive technology', function () {
    promotedEvents(5);

    $page = visit('/');
    $page->wait(0.3);

    expect($page->script('document.querySelectorAll("[data-test=promoted-track] > :not([aria-hidden])").length'))->toBe(5)
        ->and($page->script('document.querySelectorAll("[data-test=promoted-track] > [aria-hidden] a[tabindex=\'-1\']").length'))->toBe(10);
});
