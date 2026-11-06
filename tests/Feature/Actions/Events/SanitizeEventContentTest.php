<?php

declare(strict_types=1);

use App\Actions\Events\SanitizeEventContent;

test('code that could run on the page is removed', function (string $html, string $forbidden) {
    expect(app(SanitizeEventContent::class)->handle($html))->not->toContain($forbidden);
})->with([
    'script' => ['<p>Tekst</p><script>alert(1)</script>', '<script'],
    'event handler' => ['<p onclick="alert(1)">Tekst</p>', 'onclick'],
    'image handler' => ['<img src="https://example.com/a.png" onerror="alert(1)">', 'onerror'],
    'javascript link' => ['<a href="javascript:alert(1)">Link</a>', 'javascript:'],
    'iframe' => ['<iframe src="https://example.com"></iframe>', '<iframe'],
    'style sheet' => ['<style>body { display: none }</style>', '<style'],
]);

test('regular markup survives', function (string $html, string $expected) {
    expect(app(SanitizeEventContent::class)->handle($html))->toContain($expected);
})->with([
    'heading' => ['<h2>Program</h2>', '<h2>Program</h2>'],
    'table' => ['<table><tr><td>9:00</td></tr></table>', '<td>9:00</td>'],
    'link' => ['<a href="https://example.com">Link</a>', 'href="https://example.com"'],
    'image' => ['<img src="https://example.com/a.png" alt="Logo">', 'src="https://example.com/a.png"'],
    'class' => ['<div class="partners">x</div>', 'class="partners"'],
    'inline style' => ['<p style="color: red">x</p>', 'style="color: red"'],
    'polish characters' => ['<p>Zażółć gęślą jaźń</p>', 'Zażółć gęślą jaźń'],
]);

test('long content is not cut off', function () {
    $html = '<p>'.str_repeat('a', 100_000).'</p>';

    expect(app(SanitizeEventContent::class)->handle($html))->toBe($html);
});
