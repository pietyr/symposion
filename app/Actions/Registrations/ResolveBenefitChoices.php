<?php

declare(strict_types=1);

namespace App\Actions\Registrations;

use App\Enums\Attendance;
use App\Models\Benefit;
use App\Models\BenefitOption;
use App\Models\Event;
use Illuminate\Validation\ValidationException;

final class ResolveBenefitChoices
{
    /**
     * Turn the submitted choices into benefit options, checking them against the fields the
     * participant sees: only visible fields count, a hidden, disabled or full option cannot be
     * chosen, a single choice takes one option, and a required field needs one when any is free.
     *
     * @param  array<array-key, mixed>  $choices  benefit id => option id, or a list of option ids for checkboxes
     * @return list<BenefitOption>
     *
     * @throws ValidationException listing every field with a wrong choice
     */
    public function handle(Event $event, Attendance $attendance, array $choices): array
    {
        $benefits = $event->benefitsForRegistration()->filter(fn (Benefit $benefit): bool => $benefit->isFor($attendance));

        $chosen = [];
        $errors = [];

        foreach ($benefits as $benefit) {
            $ids = $this->submittedIds($choices[$benefit->id] ?? null);
            $key = "benefits.{$benefit->id}";

            if ($benefit->type->takesOneOption() && count($ids) > 1) {
                $errors[$key] = __('Choose one option.');

                continue;
            }

            foreach ($ids as $id) {
                $option = $benefit->options->firstWhere('id', $id);

                if (! $option instanceof BenefitOption || $option->is_disabled) {
                    $errors[$key] = __('This option is not available.');
                } elseif ($option->seatsLeft() === 0) {
                    $errors[$key] = __('There are no seats left for :option.', ['option' => $option->name]);
                } else {
                    $chosen[] = $option->setRelation('benefit', $benefit);
                }
            }

            $canChoose = $benefit->options->contains(fn (BenefitOption $option): bool => $option->isAvailable());

            if ($ids === [] && $benefit->is_required && $canChoose) {
                $errors[$key] = __('Choose an option.');
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $chosen;
    }

    /**
     * @return list<int>
     */
    private function submittedIds(mixed $value): array
    {
        $values = is_array($value) ? $value : [$value];

        return array_values(array_unique(array_map(
            intval(...),
            array_filter($values, fn (mixed $id): bool => is_numeric($id)),
        )));
    }
}
