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
    Schema::create('inscripcion', function (Blueprint $table) {
        $table->id('id_inscripcion');
        $table->unsignedBigInteger('id_alumno');
        $table->unsignedBigInteger('id_grupo');
        $table->timestamps();

        $table->foreign('id_alumno')
              ->references('id_alumno')
              ->on('alumno')
              ->onDelete('cascade');

        $table->foreign('id_grupo')
              ->references('id_grupo')
              ->on('grupo')
              ->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscripcion');
    }
};