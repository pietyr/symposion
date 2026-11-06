<?php

declare(strict_types=1);

namespace App\Actions\Events;

use App\Models\Event;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final class SaveEventBanners
{
    /**
     * Store the uploaded banners and remove the ones they replace or the form asked to drop.
     * A new file wins over the removal, and a banner with neither stays as it is.
     */
    public function handle(Event $event, ?UploadedFile $large, ?UploadedFile $small, bool $removeLarge, bool $removeSmall): void
    {
        $event->large_banner_path = $this->replaced($event, $event->large_banner_path, $large, $removeLarge);
        $event->small_banner_path = $this->replaced($event, $event->small_banner_path, $small, $removeSmall);
        $event->save();
    }

    private function replaced(Event $event, ?string $currentPath, ?UploadedFile $file, bool $remove): ?string
    {
        if ($file === null && ! $remove) {
            return $currentPath;
        }

        if ($currentPath !== null) {
            Storage::disk('public')->delete($currentPath);
        }

        return $file?->store("events/{$event->id}", 'public') ?: null;
    }
}
