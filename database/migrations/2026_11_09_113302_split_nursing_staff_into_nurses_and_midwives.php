<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * "Nursing staff" became two professions with different license numbers: nurse (P) and midwife (A).
 * An event open to nursing staff stays open to both.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $eventIds = DB::table('event_professions')->where('profession', 'nursing_staff')->pluck('event_id');

        DB::table('event_professions')->where('profession', 'nursing_staff')->update(['profession' => 'nurse']);
        DB::table('event_professions')->insertOrIgnore(
            $eventIds->map(fn (mixed $eventId): array => ['event_id' => $eventId, 'profession' => 'midwife'])->all(),
        );

        DB::table('users')->where('profession', 'nursing_staff')->update(['profession' => 'nurse']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $eventIds = DB::table('event_professions')->whereIn('profession', ['nurse', 'midwife'])->distinct()->pluck('event_id');

        DB::table('event_professions')->whereIn('profession', ['nurse', 'midwife'])->delete();
        DB::table('event_professions')->insert(
            $eventIds->map(fn (mixed $eventId): array => ['event_id' => $eventId, 'profession' => 'nursing_staff'])->all(),
        );

        DB::table('users')->whereIn('profession', ['nurse', 'midwife'])->update(['profession' => 'nursing_staff']);
    }
};
