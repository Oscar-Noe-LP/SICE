<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Solo agrega la columna si no existe
        if (!Schema::hasColumn('evaluacion', 'porcentaje')) {
            Schema::table('evaluacion', function (Blueprint $table) {
                $table->decimal('porcentaje', 5, 2)->default(100)->after('nombre');
            });
        }

        // fecha puede ser nullable (no siempre se registra)
        if (Schema::hasColumn('evaluacion', 'fecha')) {
            Schema::table('evaluacion', function (Blueprint $table) {
                $table->date('fecha')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('evaluacion', 'porcentaje')) {
            Schema::table('evaluacion', function (Blueprint $table) {
                $table->dropColumn('porcentaje');
            });
        }
    }
};
