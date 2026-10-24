<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
 * Elimina las pruebas "Prueba de conocimientos" y "Caso práctico": no tenían preguntas
 * digitalizadas y no se usaban. Se borran primero sus asignaciones (y lo que cuelga de
 * ellas) porque las llaves foráneas no tienen cascade.
 */
return new class extends Migration
{
    public function up(): void
    {
        $pruebas = DB::table('pruebas')->whereIn('codigo', ['CONOCIMIENTOS', 'CASO_PRACTICO'])->pluck('id');
        $asignaciones = DB::table('asignaciones_prueba')->whereIn('prueba_id', $pruebas)->pluck('id');

        DB::transaction(function () use ($pruebas, $asignaciones) {
            DB::table('respuestas')->whereIn('asignacion_id', $asignaciones)->delete();
            DB::table('resultados_dimension')->whereIn('asignacion_id', $asignaciones)->delete();
            DB::table('resultados_prueba')->whereIn('asignacion_id', $asignaciones)->delete();
            DB::table('asignaciones_prueba')->whereIn('id', $asignaciones)->delete();
            DB::table('pruebas')->whereIn('id', $pruebas)->delete();
        });
    }

    public function down(): void
    {
        // Irreversible: las pruebas eliminadas no se vuelven a crear.
    }
};
