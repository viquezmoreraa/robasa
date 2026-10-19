<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Nuevo método de resultado: PUNTAJE_T (->change() funciona en MySQL y en SQLite)
        Schema::table('pruebas', function (Blueprint $table) {
            $table->enum('metodo_resultado', ['DIMENSION_MAYOR', 'PUNTAJE_POR_DIMENSION', 'COMBINACION', 'TOTAL_CORRECTAS', 'PUNTAJE_T'])->change();
        });

        // Sexo con el que se aplica el baremo; se copia del candidato al asignar la prueba.
        // Es un valor controlado (H o M), nunca texto libre.
        Schema::table('asignaciones_prueba', function (Blueprint $table) {
            $table->enum('sexo_baremo', ['H', 'M'])->nullable()->after('intento');
        });

        Schema::table('resultados_dimension', function (Blueprint $table) {
            $table->unsignedSmallInteger('puntaje_t')->nullable()->after('porcentaje');
        });
    }

    public function down(): void
    {
        Schema::table('resultados_dimension', function (Blueprint $table) {
            $table->dropColumn('puntaje_t');
        });

        Schema::table('asignaciones_prueba', function (Blueprint $table) {
            $table->dropColumn('sexo_baremo');
        });

        // Falla si ya hay pruebas con PUNTAJE_T; en ese caso, bórrenlas antes.
        Schema::table('pruebas', function (Blueprint $table) {
            $table->enum('metodo_resultado', ['DIMENSION_MAYOR', 'PUNTAJE_POR_DIMENSION', 'COMBINACION', 'TOTAL_CORRECTAS'])->change();
        });
    }
};
