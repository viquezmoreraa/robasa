<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * IMPORTANTE: el nombre de este archivo (su fecha) debe ser POSTERIOR al de
 * la migración que crea la tabla 'postulaciones'. Si la migración de
 * postulaciones tiene una fecha más nueva, renombren este archivo con una
 * fecha mayor.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asignaciones_prueba', function (Blueprint $table) {
            $table->foreign('postulacion_id')->references('id')->on('postulaciones');
        });
    }

    public function down(): void
    {
        Schema::table('asignaciones_prueba', function (Blueprint $table) {
            $table->dropForeign(['postulacion_id']);
        });
    }
};
