<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('benefit_options', function (Blueprint $table) {
            $table->boolean('is_disabled')->default(false)->after('seat_limit');
            $table->boolean('is_hidden')->default(false)->after('is_disabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('benefit_options', function (Blueprint $table) {
            $table->dropColumn(['is_disabled', 'is_hidden']);
        });
    }
};
