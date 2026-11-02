<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;

test('the home page is displayed', function () {
    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page->component('site/Home'));
});
