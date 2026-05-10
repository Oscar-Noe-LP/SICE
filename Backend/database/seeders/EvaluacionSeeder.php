<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Inserta evaluaciones de ejemplo en el primer grupo disponible.
 * Solo se ejecuta si existe al menos un grupo y ese grupo
 * aún no tiene evaluaciones registradas.
 */
class EvaluacionSeeder extends Seeder
{
    public function run(): void
    {
        // Buscar el primer grupo que exista
        $grupo = DB::table('grupo')->first();

        if (!$grupo) {
            $this->command->warn('EvaluacionSeeder: no hay grupos en la BD, se omite.');
            return;
        }

        $id_grupo = $grupo->id_grupo;

        // Solo insertar si el grupo no tiene evaluaciones aún
        $yaExiste = DB::table('evaluacion')->where('id_grupo', $id_grupo)->exists();
        if ($yaExiste) {
            $this->command->info("EvaluacionSeeder: grupo {$id_grupo} ya tiene evaluaciones, se omite.");
            return;
        }

        DB::table('evaluacion')->insert([
            ['id_grupo' => $id_grupo, 'nombre' => 'Parcial 1',     'porcentaje' => 30.00],
            ['id_grupo' => $id_grupo, 'nombre' => 'Parcial 2',     'porcentaje' => 30.00],
            ['id_grupo' => $id_grupo, 'nombre' => 'Proyecto Final','porcentaje' => 40.00],
        ]);

        $this->command->info("EvaluacionSeeder: evaluaciones creadas para grupo {$id_grupo}.");
    }
}