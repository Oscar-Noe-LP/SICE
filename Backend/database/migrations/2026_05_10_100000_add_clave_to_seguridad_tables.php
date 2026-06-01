<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migración equivalente a correcionid.sql de Rodolfo.
 * Agrega columna 'clave' a rol, modulo y permiso,
 * y asigna claves legibles a cada registro existente.
 * Totalmente idempotente.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Agregar columna clave a las tres tablas ────────────
        if (Schema::hasTable('rol') && !Schema::hasColumn('rol', 'clave')) {
            Schema::table('rol', function (Blueprint $table) {
                $table->string('clave', 50)->nullable()->after('nombre_rol');
            });
        }
        if (Schema::hasTable('modulo') && !Schema::hasColumn('modulo', 'clave')) {
            Schema::table('modulo', function (Blueprint $table) {
                $table->string('clave', 50)->nullable()->after('nombre_modulo');
            });
        }
        if (Schema::hasTable('permiso') && !Schema::hasColumn('permiso', 'clave')) {
            Schema::table('permiso', function (Blueprint $table) {
                $table->string('clave', 100)->nullable()->after('nombre_permiso');
            });
        }

        // ── 2. Asignar claves a ROLES ─────────────────────────────
        // Mapeamos por nombre_rol (case-insensitive via LOWER)
        $rolesMap = [
            'ADMIN'           => ['Administrador'],
            'CONTROL_ESCOLAR' => ['Servicios Escolares', 'Control Escolar'],  // ambos nombres posibles
            'DOCENTE'         => ['Docente'],
            'ALUMNO'          => ['Alumno'],
            'COORDINADOR'     => ['Coordinador'],
            'DIRECCION'       => ['Dirección', 'Directivo', 'Dirección'],
            'RH'              => ['Recursos Humanos'],
            'COMITE'          => ['Comité Académico', 'Comite Academico'],
        ];

        foreach ($rolesMap as $clave => $nombres) {
            $lower = array_map('strtolower', array_map('trim', $nombres));
            DB::table('rol')
                ->whereNull('clave')
                ->orWhere('clave', '')
                ->get()
                ->each(function ($rol) use ($clave, $lower) {
                    if (in_array(strtolower(trim($rol->nombre_rol)), $lower)) {
                        DB::table('rol')->where('id_rol', $rol->id_rol)->update(['clave' => $clave]);
                    }
                });
        }

        // Fallback: cualquier rol sin clave recibe ROL_{id}
        DB::table('rol')->whereNull('clave')->orWhere('clave', '')->update([
            'clave' => DB::raw("CONCAT('ROL_', id_rol)"),
        ]);

        // ── 3. Asignar claves a MÓDULOS ───────────────────────────
        $modulosMap = [
            'SEGURIDAD'  => ['Seguridad'],
            'PERSONAS'   => ['Personas'],
            'RH'         => ['Recursos Humanos'],
            'ACADEMICO'  => ['Gestión Académica', 'Gestion Academica', 'Académico', 'Academico'],
            'ESCOLAR'    => ['Control Escolar', 'Escolar'],
            'EVENTOS'    => ['Eventos'],
            'COMITE'     => ['Comité', 'Comite'],
        ];

        foreach ($modulosMap as $clave => $nombres) {
            $lower = array_map('strtolower', array_map('trim', $nombres));
            DB::table('modulo')
                ->get()
                ->each(function ($mod) use ($clave, $lower) {
                    if (in_array(strtolower(trim($mod->nombre_modulo)), $lower)) {
                        DB::table('modulo')->where('id_modulo', $mod->id_modulo)->update(['clave' => $clave]);
                    }
                });
        }

        DB::table('modulo')->whereNull('clave')->orWhere('clave', '')->update([
            'clave' => DB::raw("CONCAT('MODULO_', id_modulo)"),
        ]);

        // ── 4. Asignar claves a PERMISOS ──────────────────────────
        $permisosMap = [
            'CREAR'    => ['crear'],
            'EDITAR'   => ['editar'],
            'ELIMINAR' => ['eliminar'],
            'VER'      => ['ver'],
        ];

        foreach ($permisosMap as $clave => $nombres) {
            $lower = array_map('strtolower', array_map('trim', $nombres));
            DB::table('permiso')
                ->get()
                ->each(function ($p) use ($clave, $lower) {
                    if (in_array(strtolower(trim($p->nombre_permiso)), $lower)) {
                        DB::table('permiso')->where('id_permiso', $p->id_permiso)->update(['clave' => $clave]);
                    }
                });
        }

        DB::table('permiso')->whereNull('clave')->orWhere('clave', '')->update([
            'clave' => DB::raw("CONCAT('PERMISO_', id_permiso)"),
        ]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('rol', 'clave')) {
            Schema::table('rol', fn(Blueprint $t) => $t->dropColumn('clave'));
        }
        if (Schema::hasColumn('modulo', 'clave')) {
            Schema::table('modulo', fn(Blueprint $t) => $t->dropColumn('clave'));
        }
        if (Schema::hasColumn('permiso', 'clave')) {
            Schema::table('permiso', fn(Blueprint $t) => $t->dropColumn('clave'));
        }
    }
};
