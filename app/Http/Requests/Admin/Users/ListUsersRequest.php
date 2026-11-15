<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Users;

use App\Enums\AccountStateFilter;
use App\Enums\Profession;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Filters of the admin user list; an unknown value is ignored rather than rejected.
 */
final class ListUsersRequest extends FormRequest
{
    /**
     * Authorization happens in the controller through the user policy.
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

    public function showsDeleted(): bool
    {
        return $this->boolean('trashed');
    }

    /**
     * Arguments for the User::filteredBy() scope.
     *
     * @return array{profession: Profession|null, state: AccountStateFilter|null, search: string}
     */
    public function userFilters(): array
    {
        return [
            'profession' => $this->enum('profession', Profession::class),
            'state' => $this->enum('state', AccountStateFilter::class),
            'search' => $this->string('search')->trim()->value(),
        ];
    }

    /**
     * The applied filters as the page's form fields show them.
     *
     * @return array{profession: string, state: string, search: string, trashed: bool}
     */
    public function selectedFilters(): array
    {
        $filters = $this->userFilters();

        return [
            'profession' => $filters['profession']->value ?? '',
            'state' => $filters['state']->value ?? '',
            'search' => $filters['search'],
            'trashed' => $this->showsDeleted(),
        ];
    }

    /**
     * @return array{professions: list<array{value: string, label: string}>, states: list<array{value: string, label: string}>}
     */
    public function filterOptions(): array
    {
        return [
            'professions' => Profession::options(),
            'states' => AccountStateFilter::options(),
        ];
    }
}
