<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Events;

use App\Enums\EventFormat;
use App\Enums\EventPriceFilter;
use App\Enums\EventSort;
use App\Enums\EventVisibilityFilter;
use App\Enums\Profession;
use App\Enums\Voivodeship;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Sorting and filters of the admin event list; a value that is not recognised is simply not applied.
 */
final class ListEventsRequest extends FormRequest
{
    /**
     * Authorization happens in the controller through the event policy.
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

    public function sort(): EventSort
    {
        return $this->enum('sort', EventSort::class) ?? EventSort::UpcomingThenPast;
    }

    public function showsDeleted(): bool
    {
        return $this->boolean('trashed');
    }

    /**
     * Arguments for the Event::filteredBy() scope.
     *
     * @return array{format: EventFormat|null, profession: Profession|null, voivodeship: Voivodeship|null, price: EventPriceFilter|null, isVisible: bool|null, search: string}
     */
    public function eventFilters(): array
    {
        $visibility = $this->enum('visibility', EventVisibilityFilter::class);

        return [
            'format' => $this->enum('format', EventFormat::class),
            'profession' => $this->enum('profession', Profession::class),
            'voivodeship' => $this->enum('voivodeship', Voivodeship::class),
            'price' => $this->enum('price', EventPriceFilter::class),
            'isVisible' => $visibility === null ? null : $visibility === EventVisibilityFilter::Visible,
            'search' => $this->string('search')->trim()->value(),
        ];
    }

    /**
     * The overview only makes sense when nothing narrows down or reorders the list.
     */
    public function wantsOverview(): bool
    {
        return $this->sort() === EventSort::UpcomingThenPast
            && ! $this->showsDeleted()
            && array_filter($this->eventFilters(), fn (mixed $filter): bool => $filter !== null && $filter !== '') === [];
    }

    /**
     * The applied filters as the page's form fields show them.
     *
     * @return array{format: string, profession: string, voivodeship: string, price: string, visibility: string, search: string, trashed: bool, sort: string}
     */
    public function selectedFilters(): array
    {
        $filters = $this->eventFilters();

        return [
            'format' => $filters['format']->value ?? '',
            'profession' => $filters['profession']->value ?? '',
            'voivodeship' => $filters['voivodeship']->value ?? '',
            'price' => $filters['price']->value ?? '',
            'visibility' => $this->enum('visibility', EventVisibilityFilter::class)->value ?? '',
            'search' => $filters['search'],
            'trashed' => $this->showsDeleted(),
            'sort' => $this->sort()->value,
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
            'visibilities' => EventVisibilityFilter::options(),
        ];
    }
}
