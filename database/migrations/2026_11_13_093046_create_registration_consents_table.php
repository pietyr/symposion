<?php

declare(strict_types=1);

use App\Models\Consent;
use App\Models\Registration;
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
        Schema::create('registration_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Registration::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Consent::class)->constrained()->restrictOnDelete();
            $table->text('content');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_consents');
    }
};
