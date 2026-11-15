<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * The first and last name are kept apart, so lists sort by the real surname (ADR-024).
 * Existing names are split before their last word, the same guess the participant list used to make.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->default('')->after('id');
            $table->string('last_name')->default('')->after('first_name');
        });

        DB::table('users')->select(['id', 'name'])->orderBy('id')->each(function (object $user): void {
            [$firstName, $lastName] = $this->splitName(is_string($user->name ?? null) ? $user->name : '');

            DB::table('users')->where('id', $user->id)->update(['first_name' => $firstName, 'last_name' => $lastName]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->string('first_name')->change();
            $table->string('last_name')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->default('')->after('id');
        });

        DB::table('users')->update(['name' => DB::raw("TRIM(CONCAT(first_name, ' ', last_name))")]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name']);
            $table->string('name')->change();
        });
    }

    /**
     * @return array{string, string}
     */
    public function splitName(string $name): array
    {
        $name = Str::squish($name);
        $lastSpace = mb_strrpos($name, ' ');

        return $lastSpace === false
            ? [$name, '']
            : [mb_substr($name, 0, $lastSpace), mb_substr($name, $lastSpace + 1)];
    }
};
