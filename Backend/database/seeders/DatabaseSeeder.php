<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Siembra la base de datos de SICE.
     * Orden importante: roles y datos iniciales primero,
     * luego seeders que dependen de grupos/inscripciones.
     */
    public function run(): void
    {
        $this->call([
            DatosInicialesSeeder::class,   // géneros, deptos, puestos, etc.
            RolesSeeder::class,            // roles del sistema
        ]);
    }
}
