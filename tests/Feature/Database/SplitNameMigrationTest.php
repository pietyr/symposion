<?php

declare(strict_types=1);

test('an existing name is split before its last word', function (string $name, string $firstName, string $lastName) {
    $migration = require database_path('migrations/'.basename(glob(database_path('migrations/*split_name_into_first_and_last_name_on_users_table.php'))[0]));

    expect($migration->splitName($name))->toBe([$firstName, $lastName]);
})->with([
    'first and last name' => ['Jan Kowalski', 'Jan', 'Kowalski'],
    'two first names' => ['Anna Maria Nowak', 'Anna Maria', 'Nowak'],
    'double-barrelled surname' => ['Ewa Nowak-Kowalska', 'Ewa', 'Nowak-Kowalska'],
    'extra spaces' => ['  Jan   Kowalski ', 'Jan', 'Kowalski'],
    'a single word' => ['Admin', 'Admin', ''],
    'Polish letters' => ['Łukasz Żółć', 'Łukasz', 'Żółć'],
]);
