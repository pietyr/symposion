<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Events;

use App\Models\Event;
use App\Models\EventSection;
use App\Rules\UniqueEventSectionTitle;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class SaveEventSectionRequest extends FormRequest
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
        /** @var Event $event */
        $event = $this->route('event');
        /** @var EventSection|null $section */
        $section = $this->route('section');

        return [
            'title' => ['required', 'string', 'max:255', new UniqueEventSectionTitle($event, $section)],
            'content' => ['required', 'string', 'max:500000'],
        ];
    }
}
