<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignaciones_prueba', function (Blueprint $table) {
            $table->id();
            // Sin llave foránea todavía: la tabla 'postulaciones' la crea otro módulo.
            // La llave se agrega en la última migración de esta carpeta.
            $table->unsignedBigInteger('postulacion_id')->index();
            $table->foreignId('prueba_id')->constrained('pruebas');   // apunta a una VERSIÓN concreta
            $table->unsignedTinyInteger('intento')->default(1);
            $table->foreignId('asignada_por')->constrained('users');  // usuario de TH
            $table->dateTime('fecha_asignacion')->useCurrent();
            $table->dateTime('fecha_limite')->nullable();
            $table->dateTime('fecha_inicio')->nullable();
            $table->dateTime('fecha_fin')->nullable();
            $table->enum('estado', ['ASIGNADA', 'EN_CURSO', 'COMPLETADA', 'VENCIDA', 'ANULADA'])->default('ASIGNADA');

            $table->unique(['postulacion_id', 'prueba_id', 'intento'], 'uq_asig');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignaciones_prueba');
    }
};
