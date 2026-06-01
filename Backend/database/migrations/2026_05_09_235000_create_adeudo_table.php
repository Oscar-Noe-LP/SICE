<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adeudo', function (Blueprint $table) {
            $table->id('id_adeudo');
            $table->unsignedBigInteger('id_alumno');
            $table->string('concepto', 150);
            $table->decimal('monto', 10, 2)->default(0);
            $table->boolean('pagado')->default(false);
            $table->date('fecha_limite')->nullable();
            $table->timestamps();

            $table->foreign('id_alumno')->references('id_alumno')->on('alumno')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adeudo');
    }
};
