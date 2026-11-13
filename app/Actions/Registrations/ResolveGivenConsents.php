<?php

declare(strict_types=1);

namespace App\Actions\Registrations;

use App\Models\Consent;
use App\Models\Event;
use Illuminate\Validation\ValidationException;

final class ResolveGivenConsents
{
    /**
     * The event's consents the participant ticked; every required one has to be among them.
     * Ids of other events' consents are ignored.
     *
     * @param  array<array-key, mixed>  $given  ids of the ticked consents, as submitted
     * @return list<Consent>
     *
     * @throws ValidationException naming every required consent left unticked
     */
    public function handle(Event $event, array $given): array
    {
        $ids = array_map(intval(...), array_filter($given, fn (mixed $id): bool => is_numeric($id)));
        $consents = $event->consents()->get();

        $errors = [];

        foreach ($consents as $consent) {
            if ($consent->is_required && ! in_array($consent->id, $ids, true)) {
                $errors["consents.{$consent->id}"] = __('This consent is required to register.');
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return array_values($consents->filter(fn (Consent $consent): bool => in_array($consent->id, $ids, true))->all());
    }
}
