<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupo', function (Blueprint $table) {
            $table->id('id_grupo');
            $table->string('clave_grupo', 20)->unique();
            $table->unsignedBigInteger('id_materia')->nullable();
            $table->unsignedBigInteger('id_docente')->nullable();
            $table->unsignedBigInteger('id_aula')->nullable();
            $table->unsignedBigInteger('id_periodo')->nullable();
            $table->integer('capacidad')->default(30);
            $table->boolean('estatus')->default(true);
            $table->boolean('acta_cerrada')->default(false);
            $table->timestamp('fecha_cierre_acta')->nullable();
            $table->timestamps();

            $table->foreign('id_materia')
                  ->references('id_materia')
                  ->on('materia')
                  ->nullOnDelete();

            $table->foreign('id_docente')
                  ->references('id_docente')
                  ->on('docente')
                  ->nullOnDelete();

            $table->foreign('id_aula')
                  ->references('id_aula')
                  ->on('aula')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupo');
    }
};
