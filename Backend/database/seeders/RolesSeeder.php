<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    /**
     * Inserta los roles básicos del sistema SICE si no existen.
     * Incluye la columna 'clave' para que el sistema de permisos funcione
     * correctamente incluso en bases de datos recién creadas.
     */
    public function run(): void
    {
        $roles = [
            ['nombre_rol' => 'Administrador',       'clave' => 'ADMIN',           'descripcion' => 'Acceso total al sistema',                       'estatus' => 1],
            ['nombre_rol' => 'Servicios Escolares', 'clave' => 'CONTROL_ESCOLAR', 'descripcion' => 'Gestión de alumnos, grupos, calificaciones',    'estatus' => 1],
            ['nombre_rol' => 'Docente',             'clave' => 'DOCENTE',         'descripcion' => 'Registro de calificaciones de sus grupos',      'estatus' => 1],
            ['nombre_rol' => 'Alumno',              'clave' => 'ALUMNO',          'descripcion' => 'Consulta de kardex, horarios y trámites',       'estatus' => 1],
            ['nombre_rol' => 'Coordinador',         'clave' => 'COORDINADOR',     'descripcion' => 'Supervisión académica por carrera',             'estatus' => 1],
            ['nombre_rol' => 'Dirección',           'clave' => 'DIRECCION',       'descripcion' => 'Acceso a reportes y estadísticas generales',    'estatus' => 1],
            ['nombre_rol' => 'Recursos Humanos',    'clave' => 'RH',              'descripcion' => 'Gestión de empleados y docentes',               'estatus' => 1],
            ['nombre_rol' => 'Comité Académico',    'clave' => 'COMITE',          'descripcion' => 'Gestión de solicitudes y sesiones del comité',  'estatus' => 1],
        ];

        foreach ($roles as $rol) {
            DB::table('rol')->updateOrInsert(
                ['nombre_rol' => $rol['nombre_rol']],
                $rol
            );
        }
    }
}
