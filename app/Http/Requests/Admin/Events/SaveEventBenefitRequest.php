<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Events;

use App\Enums\Attendance;
use App\Enums\BenefitType;
use App\Enums\EventFormat;
use App\Models\Event;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SaveEventBenefitRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(BenefitType::class)],
            'is_required' => ['boolean'],
            'attendance' => ['nullable', Rule::enum(Attendance::class)],
        ];
    }

    /**
     * "name" means a person's name everywhere else in the validation messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['name' => __('benefit name'), 'type' => __('field type')];
    }

    /**
     * A checkbox field cannot require a choice, and only a hybrid event has two kinds of attendance to limit to.
     *
     * @return array{name: string, type: BenefitType, is_required: bool, attendance: Attendance|null}
     */
    public function benefitAttributes(): array
    {
        /** @var Event $event */
        $event = $this->route('event');
        $type = BenefitType::from($this->string('type')->value());

        return [
            'name' => $this->string('name')->trim()->value(),
            'type' => $type,
            'is_required' => $type->takesOneOption() && $this->boolean('is_required'),
            'attendance' => $event->format === EventFormat::Hybrid ? $this->enum('attendance', Attendance::class) : null,
        ];
    }
}
