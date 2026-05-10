<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('alumno', 'id_estatus_alumno')) {
            Schema::table('alumno', function (Blueprint $table) {
                $table->unsignedBigInteger('id_estatus_alumno')->nullable()->after('estatus');
                $table->foreign('id_estatus_alumno')
                      ->references('id_estatus_alumno')
                      ->on('estatus_alumno')
                      ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('alumno', function (Blueprint $table) {
            $table->dropForeign(['id_estatus_alumno']);
            $table->dropColumn('id_estatus_alumno');
        });
    }
};
