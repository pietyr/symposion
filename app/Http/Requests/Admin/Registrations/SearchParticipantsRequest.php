<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Registrations;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class SearchParticipantsRequest extends FormRequest
{
    /**
     * Authorization happens in the controller through the registration policy.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function search(): string
    {
        return $this->string('search')->squish()->value();
    }
}
