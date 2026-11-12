<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Bank transfer
    |--------------------------------------------------------------------------
    |
    | Without a payment gateway a participant pays a registration by an ordinary
    | transfer and the administrator confirms it. The default account number is
    | made up: it has a valid IBAN check sum but no bank uses the routing number.
    |
    */

    'bank_transfer' => [
        'recipient' => env('BANK_TRANSFER_RECIPIENT', 'Symposion sp. z o.o.'),
        'account_number' => env('BANK_TRANSFER_ACCOUNT_NUMBER', 'PL46 9999 0000 0000 1234 5678 9012'),
    ],

];
