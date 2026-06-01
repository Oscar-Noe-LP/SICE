<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grupo', function (Blueprint $table) {
            if (!Schema::hasColumn('grupo', 'dia')) {
                $table->string('dia', 50)->nullable()->after('capacidad');
            }
            if (!Schema::hasColumn('grupo', 'hora_inicio')) {
                $table->time('hora_inicio')->nullable()->after('dia');
            }
            if (!Schema::hasColumn('grupo', 'hora_fin')) {
                $table->time('hora_fin')->nullable()->after('hora_inicio');
            }
            if (!Schema::hasColumn('grupo', 'id_carrera')) {
                $table->unsignedBigInteger('id_carrera')->nullable()->after('hora_fin');
                $table->foreign('id_carrera')
                      ->references('id_carrera')
                      ->on('carrera')
                      ->nullOnDelete();
            }
            if (!Schema::hasColumn('grupo', 'semestre')) {
                $table->integer('semestre')->nullable()->after('id_carrera');
            }
        });
    }

    public function down(): void
    {
        Schema::table('grupo', function (Blueprint $table) {
            if (Schema::hasColumn('grupo', 'id_carrera')) {
                $table->dropForeign(['id_carrera']);
            }
            $table->dropColumn(
                array_filter(
                    ['dia', 'hora_inicio', 'hora_fin', 'id_carrera', 'semestre'],
                    fn($col) => Schema::hasColumn('grupo', $col)
                )
            );
        });
    }
};
