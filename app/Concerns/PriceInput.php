<?php

declare(strict_types=1);

namespace App\Concerns;

/**
 * Amounts typed in zlotys the Polish way, read in a form request.
 */
trait PriceInput
{
    /**
     * Polish users type "1 500,50"; the validation rules expect "1500.50".
     */
    protected function normalizedPrice(string $field): ?string
    {
        return $this->filled($field)
            ? str_replace([' ', ','], ['', '.'], $this->string($field)->value())
            : null;
    }

    /**
     * Split the string instead of multiplying, because a float could lose a grosz.
     */
    protected function priceInGrosze(string $field): ?int
    {
        if (! $this->filled($field)) {
            return null;
        }

        [$zlotys, $grosze] = array_pad(explode('.', $this->string($field)->value(), 2), 2, '');

        return (int) $zlotys * 100 + (int) str_pad($grosze, 2, '0');
    }
}
