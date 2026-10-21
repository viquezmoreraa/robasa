<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('postulaciones', function (Blueprint $table) {
            // Estado del proceso de selección que asigna Talento Humano (etiquetas en Postulacion::ESTADOS).
            // Las postulaciones que ya existen quedan en POSTULADO por el valor por defecto.
            $table->enum('estado', [
                'POSTULADO', 'EN_REVISION', 'PRESELECCIONADO', 'ENTREVISTA', 'PRUEBAS',
                'REFERENCIAS', 'OFERTA', 'CONTRATADO', 'NO_SELECCIONADO',
            ])->default('POSTULADO')->after('correo');
        });
    }

    public function down(): void
    {
        Schema::table('postulaciones', function (Blueprint $table) {
            $table->dropColumn('estado');
        });
    }
};
