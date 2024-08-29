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
        Schema::create('point_description', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('point_id')->index();
            $table->string('point_type')->nullable()->index();
            $table->unsignedBigInteger('point_type_id')->nullable()->index();
            $table->longText('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_description');
    }
};
