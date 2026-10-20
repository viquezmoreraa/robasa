<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Agrega 'DICOTOMIA_COMBINADA' al ENUM pruebas.metodo_resultado, siguiendo
 * el mismo patrón que la migración que agregó 'PATRON_DISC'. Lo usa el
 * Test de Keirsey (4 dicotomías E/I, S/N, T/F, J/P combinadas en un código
 * de 4 letras que se busca en `perfiles`).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE pruebas
            MODIFY metodo_resultado ENUM(
                'DIMENSION_MAYOR',
                'COMBINACION',
                'PUNTAJE_POR_DIMENSION',
                'TOTAL_CORRECTAS',
                'PUNTAJE_T',
                'PATRON_DISC',
                'DICOTOMIA_COMBINADA'
            ) NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE pruebas
            MODIFY metodo_resultado ENUM(
                'DIMENSION_MAYOR',
                'COMBINACION',
                'PUNTAJE_POR_DIMENSION',
                'TOTAL_CORRECTAS',
                'PUNTAJE_T',
                'PATRON_DISC'
            ) NOT NULL
        ");
    }
};