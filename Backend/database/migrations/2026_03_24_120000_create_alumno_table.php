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
    Schema::create('alumno', function (Blueprint $table) {
        $table->id('id_alumno');
        $table->string('numero_control')->unique();
        $table->unsignedBigInteger('id_persona');
        $table->timestamps();

        $table->foreign('id_persona')
              ->references('id_persona')
              ->on('persona')
              ->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumno');
    }
};
