<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bitacora', function (Blueprint $table) {
            $table->json('datos_anteriores')->nullable()->after('direccion_ip');
            $table->json('datos_nuevos')->nullable()->after('datos_anteriores');
        });
    }

    public function down(): void
    {
        Schema::table('bitacora', function (Blueprint $table) {
            $table->dropColumn(['datos_anteriores', 'datos_nuevos']);
        });
    }
};
