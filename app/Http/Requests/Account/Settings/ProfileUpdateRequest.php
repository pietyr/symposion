<?php

declare(strict_types=1);

namespace App\Http\Requests\Account\Settings;

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class ProfileUpdateRequest extends FormRequest
{
    use ProfileValidationRules;

    /**
     * Get the validation rules that apply to the request; staff accounts may have no profession.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...$this->profileRules($this->user()?->id),
            ...$this->professionRules($this->input('profession'), isRequired: ! ($this->user()?->isAdmin() ?? false)),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function profileAttributes(): array
    {
        return [
            ...$this->safe()->only(['name', 'email']),
            ...$this->professionAttributes($this->validated()),
        ];
    }

    /**
     * @return list<int>
     */
    public function specializationIds(): array
    {
        return $this->professionSpecializations($this->validated());
    }
}
