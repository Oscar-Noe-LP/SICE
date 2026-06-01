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
        Schema::create('evaluacion', function (Blueprint $table) {
            $table->bigIncrements('id_evaluacion'); // PK BIGINT UNSIGNED
            $table->unsignedBigInteger('id_grupo'); // FK BIGINT UNSIGNED

            $table->string('nombre', 50)->nullable();
            $table->decimal('porcentaje', 5, 2)->nullable();

            // Llave foránea hacia grupo
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
        Schema::dropIfExists('evaluacion');
    }
};