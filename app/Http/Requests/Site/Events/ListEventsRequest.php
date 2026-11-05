<?php

declare(strict_types=1);

namespace App\Http\Requests\Site\Events;

use App\Enums\EventFormat;
use App\Enums\EventPriceFilter;
use App\Enums\Profession;
use App\Enums\Voivodeship;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Filters of the public event lists; a value that is not recognised is simply not applied.
 */
final class ListEventsRequest extends FormRequest
{
    /**
     * The lists are public.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Arguments for the Event::filteredBy() scope.
     *
     * @return array{format: EventFormat|null, profession: Profession|null, voivodeship: Voivodeship|null, price: EventPriceFilter|null, search: string}
     */
    public function eventFilters(): array
    {
        return [
            'format' => $this->enum('forma', EventFormat::class),
            'profession' => $this->enum('zawod', Profession::class),
            'voivodeship' => $this->enum('wojewodztwo', Voivodeship::class),
            'price' => $this->enum('cena', EventPriceFilter::class),
            'search' => $this->string('szukaj')->trim()->value(),
        ];
    }

    /**
     * The applied filters as the page's form fields show them.
     *
     * @return array{format: string, profession: string, voivodeship: string, price: string, search: string}
     */
    public function selectedFilters(): array
    {
        $filters = $this->eventFilters();

        return [
            'format' => $filters['format']->value ?? '',
            'profession' => $filters['profession']->value ?? '',
            'voivodeship' => $filters['voivodeship']->value ?? '',
            'price' => $filters['price']->value ?? '',
            'search' => $filters['search'],
        ];
    }

    /**
     * @return array<string, list<array{value: string, label: string}>>
     */
    public function filterOptions(): array
    {
        return [
            'formats' => EventFormat::options(),
            'professions' => Profession::options(),
            'voivodeships' => Voivodeship::options(),
            'prices' => EventPriceFilter::options(),
        ];
    }
}
