<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Specializations;

use App\Models\Specialization;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SaveSpecializationRequest extends FormRequest
{
    /**
     * Authorization happens in the controller through the specialization policy.
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
        /** @var Specialization|null $specialization */
        $specialization = $this->route('specialization');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Specialization::class)->ignore($specialization),
            ],
        ];
    }

    /**
     * "name" means a person's name everywhere else in the validation messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['name' => __('specialization name')];
    }
}
