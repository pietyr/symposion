<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Events;

use App\Concerns\PriceInput;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class SaveBenefitOptionRequest extends FormRequest
{
    use PriceInput;

    /**
     * Authorization happens in the controller through the event policy.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['price' => $this->normalizedPrice('price')]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'price' => ['nullable', 'decimal:0,2', 'min:0', 'max:100000'],
            'seat_limit' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ];
    }

    /**
     * "name" means a person's name everywhere else in the validation messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['name' => __('option name'), 'price' => __('option price'), 'seat_limit' => __('seat limit')];
    }

    /**
     * An empty price means the option is included at no cost.
     *
     * @return array{name: string, price: int, seat_limit: int|null}
     */
    public function optionAttributes(): array
    {
        return [
            'name' => $this->string('name')->trim()->value(),
            'price' => $this->priceInGrosze('price') ?? 0,
            'seat_limit' => $this->filled('seat_limit') ? $this->integer('seat_limit') : null,
        ];
    }
}
