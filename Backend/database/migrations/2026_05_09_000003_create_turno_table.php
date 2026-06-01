<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('turno')) {
            Schema::create('turno', function (Blueprint $table) {
                $table->id('id_turno');
                $table->string('nombre_turno', 50)->unique();
            });
        }

        $existing = DB::table('turno')->pluck('nombre_turno')->toArray();
        $insert = array_filter([
            ['nombre_turno' => 'Matutino'],
            ['nombre_turno' => 'Vespertino'],
            ['nombre_turno' => 'Nocturno'],
        ], fn($r) => !in_array($r['nombre_turno'], $existing));

        if ($insert) {
            DB::table('turno')->insert(array_values($insert));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('turno');
    }
};
