<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inscripcion', function (Blueprint $table) {
            if (!Schema::hasColumn('inscripcion', 'estatus')) {
                $table->string('estatus', 50)->default('Activo')->after('id_grupo');
            }
            if (!Schema::hasColumn('inscripcion', 'fecha_inscripcion')) {
                $table->date('fecha_inscripcion')->nullable()->after('estatus');
            }
        });
    }

    public function down(): void
    {
        Schema::table('inscripcion', function (Blueprint $table) {
            $cols = array_filter(
                ['estatus', 'fecha_inscripcion'],
                fn($c) => Schema::hasColumn('inscripcion', $c)
            );
            if ($cols) $table->dropColumn(array_values($cols));
        });
    }
};
