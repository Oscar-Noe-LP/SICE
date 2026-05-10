<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Siembra los catálogos base del sistema SICE.
 * Usa updateOrInsert para que sea IDEMPOTENTE:
 * se puede correr múltiples veces sin duplicar datos.
 */
class DatosInicialesSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. GÉNEROS ─────────────────────────────────────────────
        $generos = ['Masculino', 'Femenino', 'No especificado'];
        foreach ($generos as $g) {
            DB::table('genero')->updateOrInsert(
                ['nombre_genero' => $g],
                ['nombre_genero' => $g]
            );
        }

        // ── 2. NIVEL CARRERA ────────────────────────────────────────
        $niveles = ['Licenciatura', 'Ingeniería', 'Maestría', 'Doctorado', 'Técnico Superior'];
        foreach ($niveles as $n) {
            DB::table('nivel_carrera')->updateOrInsert(
                ['nombre_nivel' => $n],
                ['nombre_nivel' => $n]
            );
        }

        // ── 3. DEPARTAMENTOS ────────────────────────────────────────
        $deptos = [
            ['nombre' => 'Sistemas y Computación',    'descripcion' => 'Departamento académico de TI',         'estatus' => 1],
            ['nombre' => 'Ciencias Básicas',          'descripcion' => 'Departamento de matemáticas y física', 'estatus' => 1],
            ['nombre' => 'Administración',            'descripcion' => 'Departamento administrativo',          'estatus' => 1],
            ['nombre' => 'Civil e Industrial',        'descripcion' => 'Departamento de ingeniería civil',     'estatus' => 1],
            ['nombre' => 'Recursos Humanos',          'descripcion' => 'Gestión de personal institucional',    'estatus' => 1],
        ];
        foreach ($deptos as $d) {
            DB::table('departamento')->updateOrInsert(
                ['nombre' => $d['nombre']],
                $d
            );
        }

        // ── 4. PUESTOS ──────────────────────────────────────────────
        $puestos = [
            ['nombre_puesto' => 'Docente',                'descripcion' => 'Profesor de asignatura o tiempo completo'],
            ['nombre_puesto' => 'Jefe de Departamento',  'descripcion' => 'Responsable del departamento académico'],
            ['nombre_puesto' => 'Administrativo',        'descripcion' => 'Personal de apoyo administrativo'],
            ['nombre_puesto' => 'Director',              'descripcion' => 'Dirección del plantel'],
            ['nombre_puesto' => 'Subdirector Académico', 'descripcion' => 'Subdirección académica'],
        ];
        foreach ($puestos as $p) {
            DB::table('puesto')->updateOrInsert(
                ['nombre_puesto' => $p['nombre_puesto']],
                $p
            );
        }

        // ── 5. TIPO EVENTO ──────────────────────────────────────────
        $tiposEvento = ['Académico', 'Cultural', 'Deportivo', 'Conferencia', 'Seminario', 'Taller'];
        foreach ($tiposEvento as $t) {
            DB::table('tipo_evento')->updateOrInsert(
                ['nombre_tipo' => $t],
                ['nombre_tipo' => $t]
            );
        }

        // ── 6. TIPO SOLICITUD ───────────────────────────────────────
        $tiposSolicitud = [
            'Cambio de carrera',
            'Reingreso',
            'Baja temporal',
            'Baja definitiva',
            'Solicitud especial académica',
            'Recursamiento',
            'Equivalencia de materias',
        ];
        foreach ($tiposSolicitud as $t) {
            DB::table('tipo_solicitud')->updateOrInsert(
                ['nombre_tipo' => $t],
                ['nombre_tipo' => $t]
            );
        }

        // ── 7. MÓDULOS ──────────────────────────────────────────────
        $modulos = [
            ['nombre_modulo' => 'Seguridad',           'descripcion' => 'Gestión de usuarios, roles y permisos'],
            ['nombre_modulo' => 'Personas',            'descripcion' => 'Gestión de datos personales'],
            ['nombre_modulo' => 'Recursos Humanos',    'descripcion' => 'Gestión de empleados y docentes'],
            ['nombre_modulo' => 'Gestión Académica',   'descripcion' => 'Carreras, planes, materias y grupos'],
            ['nombre_modulo' => 'Control Escolar',     'descripcion' => 'Inscripciones y calificaciones'],
            ['nombre_modulo' => 'Eventos',             'descripcion' => 'Eventos institucionales'],
            ['nombre_modulo' => 'Comité',              'descripcion' => 'Gestión de solicitudes y resoluciones'],
        ];
        foreach ($modulos as $m) {
            DB::table('modulo')->updateOrInsert(
                ['nombre_modulo' => $m['nombre_modulo']],
                $m
            );
        }

        // ── 8. PERMISOS ─────────────────────────────────────────────
        $permisos = [
            ['nombre_permiso' => 'crear',    'descripcion' => 'Permite crear registros'],
            ['nombre_permiso' => 'editar',   'descripcion' => 'Permite editar registros'],
            ['nombre_permiso' => 'eliminar', 'descripcion' => 'Permite eliminar registros'],
            ['nombre_permiso' => 'ver',      'descripcion' => 'Permite visualizar información'],
        ];
        foreach ($permisos as $p) {
            DB::table('permiso')->updateOrInsert(
                ['nombre_permiso' => $p['nombre_permiso']],
                $p
            );
        }

        // ── 9. PERIODOS ─────────────────────────────────────────────
        DB::table('periodo')->updateOrInsert(
            ['nombre_periodo' => '2026-1'],
            ['nombre_periodo' => '2026-1', 'fecha_inicio' => '2026-01-15', 'fecha_fin' => '2026-06-30', 'estatus' => 1]
        );
        DB::table('periodo')->updateOrInsert(
            ['nombre_periodo' => '2026-2'],
            ['nombre_periodo' => '2026-2', 'fecha_inicio' => '2026-08-01', 'fecha_fin' => '2026-12-15', 'estatus' => 1]
        );
    }
}
