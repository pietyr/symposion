<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Users;

use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * The same rules as the participant's own profile settings.
 */
final class UpdateUserRequest extends FormRequest
{
    use ProfileValidationRules;

    /**
     * Authorization happens in the controller through the user policy.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->route('user');

        return [
            ...$this->profileRules($user->id),
            ...$this->professionRules($this->input('profession')),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function profileAttributes(): array
    {
        return [
            ...$this->safe()->only(['academic_title', 'first_name', 'last_name', 'email']),
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
