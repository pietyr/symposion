<?php

declare(strict_types=1);

use App\Http\Controllers\Controller;

arch()->preset()->php();
arch()->preset()->security();
arch()->preset()->laravel();
arch()->preset()->strict()->ignoring(Controller::class);
