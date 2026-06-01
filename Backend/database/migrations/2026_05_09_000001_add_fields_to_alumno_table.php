<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumno', function (Blueprint $table) {
            // Guards idempotentes: no falla si alguien ya corrió correcion2.sql en Railway
            if (!Schema::hasColumn('alumno', 'estatus')) {
                $table->string('estatus', 50)->default('Activo')->after('numero_control');
            }
            if (!Schema::hasColumn('alumno', 'id_carrera')) {
                $table->unsignedBigInteger('id_carrera')->nullable()->after('estatus');
                $table->foreign('id_carrera')
                      ->references('id_carrera')
                      ->on('carrera')
                      ->nullOnDelete();
            }
            if (!Schema::hasColumn('alumno', 'semestre_actual')) {
                $table->integer('semestre_actual')->nullable()->after('id_carrera');
            }
            if (!Schema::hasColumn('alumno', 'fecha_ingreso')) {
                $table->date('fecha_ingreso')->nullable()->after('semestre_actual');
            }
        });
    }

    public function down(): void
    {
        Schema::table('alumno', function (Blueprint $table) {
            if (Schema::hasColumn('alumno', 'id_carrera')) {
                $table->dropForeign(['id_carrera']);
            }
            $table->dropColumn(
                array_filter(
                    ['estatus', 'id_carrera', 'semestre_actual', 'fecha_ingreso'],
                    fn($col) => Schema::hasColumn('alumno', $col)
                )
            );
        });
    }
};
