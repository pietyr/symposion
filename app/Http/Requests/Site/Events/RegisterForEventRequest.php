<?php

declare(strict_types=1);

namespace App\Http\Requests\Site\Events;

use App\Enums\Attendance;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class RegisterForEventRequest extends FormRequest
{
    /**
     * Who may register is decided by RegisterForEvent, which explains the reason.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, ValidationRule|object|string>>
     */
    public function rules(): array
    {
        return [
            'attendance' => ['required', Rule::enum(Attendance::class)],
        ];
    }

    public function attendance(): Attendance
    {
        return Attendance::from($this->string('attendance')->value());
    }
}
