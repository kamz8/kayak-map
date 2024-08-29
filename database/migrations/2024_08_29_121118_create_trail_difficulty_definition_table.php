<?php

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
        Schema::create('trail_difficulty_definition', function (Blueprint $table) {
            $table->id();
            $table->string('accessibility'); // Dostępność
            $table->text('conditions'); // Warunki spływu
            $table->string('label'); // Międzynarodowa klasyfikacja trudności
            $table->string('code'); // Kod trudności (np. ZWA, ZWB, WW I)
            $table->enum('difficulty_level', ['łatwy', 'umiarkowany', 'trudny']); // Trójstopniowa skala trudności
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trail_difficulty_definition');
    }
};
