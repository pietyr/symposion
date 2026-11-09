<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Registrations;

use App\Concerns\PriceInput;
use App\Enums\Attendance;
use App\Models\Event;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreRegistrationRequest extends FormRequest
{
    use PriceInput;

    /**
     * Authorization happens in the controller through the registration policy.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['amount' => $this->normalizedPrice('amount')]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, ValidationRule|Closure|object|string>>
     */
    public function rules(): array
    {
        return [
            'event_id' => ['required', 'integer', Rule::exists(Event::class, 'id')->whereNull('deleted_at')],
            'user_id' => ['required', 'integer', Rule::exists(User::class, 'id')],
            'attendance' => [
                'required',
                Rule::enum(Attendance::class),
                function (string $attribute, mixed $value, Closure $fail): void {
                    $event = Event::query()->find($this->integer('event_id'));
                    $attendance = Attendance::tryFrom(is_string($value) ? $value : '');

                    if ($event !== null && $attendance !== null && ! in_array($attendance, $event->attendances(), true)) {
                        $fail(__('The event does not offer this kind of attendance.'));
                    }
                },
            ],
            'amount' => ['required', 'decimal:0,2', 'min:0', 'max:100000'],
        ];
    }

    public function event(): Event
    {
        return Event::query()->findOrFail($this->integer('event_id'));
    }

    public function participant(): User
    {
        return User::query()->findOrFail($this->integer('user_id'));
    }

    public function attendance(): Attendance
    {
        return Attendance::from($this->string('attendance')->value());
    }

    public function amountInGrosze(): int
    {
        return $this->priceInGrosze('amount') ?? 0;
    }
}
