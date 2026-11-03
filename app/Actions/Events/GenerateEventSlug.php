<?php

declare(strict_types=1);

namespace App\Actions\Events;

use App\Models\Event;
use Illuminate\Support\Str;

final class GenerateEventSlug
{
    /**
     * Build a readable slug from the title and city; events sharing both get "-2", "-3", ...
     *
     * Public URLs also carry the event id, so the slug only needs to tell
     * same-named events apart, not to identify them.
     */
    public function handle(Event $event): string
    {
        $base = Str::slug(trim("{$event->title} {$event->city}")) ?: 'event';

        $takenSlugs = Event::withTrashed()
            ->whereKeyNot($event->getKey())
            ->where(fn ($query) => $query->where('slug', $base)->orWhereLike('slug', "{$base}-%"))
            ->pluck('slug');

        if (! $takenSlugs->contains($base)) {
            return $base;
        }

        $number = 2;

        while ($takenSlugs->contains("{$base}-{$number}")) {
            $number++;
        }

        return "{$base}-{$number}";
    }
}
