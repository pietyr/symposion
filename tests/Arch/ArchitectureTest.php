<?php

declare(strict_types=1);

use App\Http\Controllers\Controller;

arch()->preset()->php();
arch()->preset()->security();
arch()->preset()->laravel();

// The strict preset also forbids protected methods, which clashes with
// framework conventions such as Model::casts(), so its rules are picked here.
arch('application code uses strict types')
    ->expect('App')
    ->toUseStrictTypes()
    ->toUseStrictEquality();

arch('application classes are final')
    ->expect('App')
    ->classes()
    ->toBeFinal()
    ->ignoring(Controller::class);

arch('actions expose a single handle method')
    ->expect('App\\Actions')
    ->toHaveMethod('handle')
    ->ignoring('App\\Actions\\Fortify');
