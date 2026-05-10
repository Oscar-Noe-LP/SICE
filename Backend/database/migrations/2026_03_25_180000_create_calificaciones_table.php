<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * NOTA: Esta migración fue reemplazada por el esquema real de la tabla `calificacion`
 * (singular) que es la que usan todos los controladores. Se conserva como no-op
 * para no romper el historial de migraciones en bases de datos existentes.
 */
class CreateCalificacionesTable extends Migration
{
    public function up(): void
    {
        // No-op: la tabla real es `calificacion` (singular), no `calificaciones`.
        // El esquema fue definido directamente en la base de datos de producción.
    }

    public function down(): void
    {
        // No-op
    }
}
