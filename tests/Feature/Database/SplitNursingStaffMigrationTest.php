<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('events and users of nursing staff move to nurses and midwives', function () {
    $migration = require database_path('migrations/'.basename(glob(database_path('migrations/*split_nursing_staff_into_nurses_and_midwives.php'))[0]));

    $event = Event::factory()->create();
    $other = Event::factory()->create();
    DB::table('event_professions')->insert([
        ['event_id' => $event->id, 'profession' => 'nursing_staff'],
        ['event_id' => $event->id, 'profession' => 'doctor'],
        ['event_id' => $other->id, 'profession' => 'doctor'],
    ]);
    $user = User::factory()->create();
    DB::table('users')->where('id', $user->id)->update(['profession' => 'nursing_staff']);

    $migration->up();

    expect(DB::table('event_professions')->where('event_id', $event->id)->orderBy('profession')->pluck('profession')->all())->toBe(['doctor', 'midwife', 'nurse'])
        ->and(DB::table('event_professions')->where('event_id', $other->id)->pluck('profession')->all())->toBe(['doctor'])
        ->and($user->fresh()->profession)->toBe('nurse');
});
