<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('grupo', function (Blueprint $table) {
            if (!Schema::hasColumn('grupo', 'acta_cerrada')) {
                $table->boolean('acta_cerrada')->default(false)->after('estatus');
            }
            if (!Schema::hasColumn('grupo', 'fecha_cierre_acta')) {
                $table->timestamp('fecha_cierre_acta')->nullable()->after('acta_cerrada');
            }
        });
    }

    public function down(): void
    {
        Schema::table('grupo', function (Blueprint $table) {
            if (Schema::hasColumn('grupo', 'acta_cerrada')) {
                $table->dropColumn('acta_cerrada');
            }
            if (Schema::hasColumn('grupo', 'fecha_cierre_acta')) {
                $table->dropColumn('fecha_cierre_acta');
            }
        });
    }
};
