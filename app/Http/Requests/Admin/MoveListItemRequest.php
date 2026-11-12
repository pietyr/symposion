<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\MoveDirection;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Moving an item of an ordered list (sections, benefits, consents) one place up or down.
 */
final class MoveListItemRequest extends FormRequest
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
     * @return array<string, array<int, ValidationRule|string|object>>
     */
    public function rules(): array
    {
        return [
            'direction' => ['required', Rule::enum(MoveDirection::class)],
        ];
    }

    public function direction(): MoveDirection
    {
        return MoveDirection::from($this->string('direction')->value());
    }
}
