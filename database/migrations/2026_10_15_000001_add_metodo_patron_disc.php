<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE pruebas MODIFY metodo_resultado
            ENUM('DIMENSION_MAYOR','PUNTAJE_POR_DIMENSION','COMBINACION','TOTAL_CORRECTAS','PUNTAJE_T','PATRON_DISC') NOT NULL");
    }

    public function down(): void
    {
        // Falla si ya hay pruebas con PATRON_DISC; bórrenlas antes si necesitan revertir.
        DB::statement("ALTER TABLE pruebas MODIFY metodo_resultado
            ENUM('DIMENSION_MAYOR','PUNTAJE_POR_DIMENSION','COMBINACION','TOTAL_CORRECTAS','PUNTAJE_T') NOT NULL");
    }
};
