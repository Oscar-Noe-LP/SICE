<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * DESHABILITADO — la tabla real es 'calificacion' (singular),
 * no 'calificaciones'. Las calificaciones se crean a través del
 * flujo inscripcion → evaluacion → calificacion desde la API,
 * no desde semillas.
 */
class CalificacionesSeeder extends Seeder
{
    public function run(): void
    {
        // No-op intencional. Ver CalificacionController@guardarCalificaciones.
    }
}