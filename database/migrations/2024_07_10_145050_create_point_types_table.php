<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('point_types', function (Blueprint $table) {
            $table->id();
            $table->string('type')->index();
            $table->string('key')->index()->comment('klucz wywołania typu bez spacji');
            $table->string('icon')->index()->comment('Nazwa ikony dopasowana do punktu');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('point_types');
    }
};
