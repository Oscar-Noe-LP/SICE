<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turno', function (Blueprint $table) {
            $table->id('id_turno');
            $table->string('nombre_turno', 50)->unique();
        });

        DB::table('turno')->insert([
            ['nombre_turno' => 'Matutino'],
            ['nombre_turno' => 'Vespertino'],
            ['nombre_turno' => 'Nocturno'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('turno');
    }
};
