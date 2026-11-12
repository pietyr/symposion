<?php

declare(strict_types=1);

use App\Models\Benefit;
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
        Schema::create('benefit_options', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Benefit::class)->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('price')->default(0);
            $table->unsignedInteger('seat_limit')->nullable();
            $table->unsignedInteger('position');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('benefit_options');
    }
};
