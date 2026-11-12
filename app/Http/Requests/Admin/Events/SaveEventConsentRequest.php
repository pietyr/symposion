<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Events;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class SaveEventConsentRequest extends FormRequest
{
    /**
     * Authorization happens in the controller through the event policy.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'max:10000'],
            'is_required' => ['boolean'],
        ];
    }
}
