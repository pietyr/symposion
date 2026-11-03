<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Events;

use App\Concerns\EventValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateEventRequest extends FormRequest
{
    use EventValidationRules;

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
     * @return array<string, array<int, ValidationRule|object|string>>
     */
    public function rules(): array
    {
        return $this->eventRules();
    }
}
