<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ->change() funciona en MySQL y en SQLite (antes era ALTER TABLE ... MODIFY, solo MySQL)
        Schema::table('pruebas', function (Blueprint $table) {
            $table->enum('metodo_resultado', ['DIMENSION_MAYOR', 'PUNTAJE_POR_DIMENSION', 'COMBINACION', 'TOTAL_CORRECTAS', 'PUNTAJE_T', 'PATRON_DISC'])->change();
        });
    }

    public function down(): void
    {
        // Falla si ya hay pruebas con PATRON_DISC; bórrenlas antes si necesitan revertir.
        Schema::table('pruebas', function (Blueprint $table) {
            $table->enum('metodo_resultado', ['DIMENSION_MAYOR', 'PUNTAJE_POR_DIMENSION', 'COMBINACION', 'TOTAL_CORRECTAS', 'PUNTAJE_T'])->change();
        });
    }
};
