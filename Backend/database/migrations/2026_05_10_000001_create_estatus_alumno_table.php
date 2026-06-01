<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('estatus_alumno')) {
            Schema::create('estatus_alumno', function (Blueprint $table) {
                $table->id('id_estatus_alumno');
                $table->string('nombre', 50)->unique();
            });
        }

        // Insertar valores por defecto (idempotente)
        $valores = [
            'Activo', 'Baja Temporal', 'Baja Definitiva',
            'Titulado', 'Egresado', 'Aspirante',
            'Preinscrito', 'Admitido', 'Proceso de Egreso',
        ];
        foreach ($valores as $v) {
            DB::table('estatus_alumno')->updateOrInsert(
                ['nombre' => $v],
                ['nombre' => $v]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('estatus_alumno');
    }
};
