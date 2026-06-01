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
        Schema::table('persona', function (Blueprint $table) {
            $table->string('estado_civil', 50)->nullable()->after('id_genero');
            $table->string('nacionalidad', 60)->nullable()->default('Mexicana')->after('estado_civil');
        });
    }

    public function down(): void
    {
        Schema::table('persona', function (Blueprint $table) {
            $table->dropColumn(['estado_civil', 'nacionalidad']);
        });
    }
};
