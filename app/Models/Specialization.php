<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SpecializationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name'])]
final class Specialization extends Model
{
    /** @use HasFactory<SpecializationFactory> */
    use HasFactory;

    /**
     * Options for the forms, alphabetically.
     *
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_values(self::query()
            ->orderBy('name')
            ->get()
            ->map(fn (self $specialization): array => [
                'value' => (string) $specialization->id,
                'label' => $specialization->name,
            ])
            ->all());
    }
}
