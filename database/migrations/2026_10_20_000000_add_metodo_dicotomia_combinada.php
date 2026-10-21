<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega 'DICOTOMIA_COMBINADA' al ENUM pruebas.metodo_resultado, siguiendo
 * el mismo patrón que la migración que agregó 'PATRON_DISC'. Lo usa el
 * Test de Keirsey (4 dicotomías E/I, S/N, T/F, J/P combinadas en un código
 * de 4 letras que se busca en `perfiles`).
 *
 * ->change() funciona en MySQL y en SQLite (antes era ALTER TABLE ... MODIFY,
 * solo MySQL, y rompía las pruebas con phpunit porque la BD de pruebas es
 * SQLite en memoria).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pruebas', function (Blueprint $table) {
            $table->enum('metodo_resultado', ['DIMENSION_MAYOR', 'PUNTAJE_POR_DIMENSION', 'COMBINACION', 'TOTAL_CORRECTAS', 'PUNTAJE_T', 'PATRON_DISC', 'DICOTOMIA_COMBINADA'])->change();
        });
    }

    public function down(): void
    {
        // Falla si ya hay pruebas con DICOTOMIA_COMBINADA; bórrenlas antes si necesitan revertir.
        Schema::table('pruebas', function (Blueprint $table) {
            $table->enum('metodo_resultado', ['DIMENSION_MAYOR', 'PUNTAJE_POR_DIMENSION', 'COMBINACION', 'TOTAL_CORRECTAS', 'PUNTAJE_T', 'PATRON_DISC'])->change();
        });
    }
};