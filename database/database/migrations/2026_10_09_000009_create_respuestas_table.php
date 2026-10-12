<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('respuestas', function (Blueprint $table) {
            // Respuestas CRUDAS: se guardan siempre para poder recalcular.
            $table->id();
            $table->foreignId('asignacion_id')->constrained('asignaciones_prueba');
            $table->foreignId('pregunta_id')->constrained('preguntas');
            $table->foreignId('opcion_id')->constrained('opciones');
            $table->enum('seleccion', ['UNICA', 'MAS', 'MENOS'])->default('UNICA');
            $table->dateTime('respondida_en')->useCurrent();

            $table->unique(['asignacion_id', 'pregunta_id', 'seleccion'], 'uq_resp');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('respuestas');
    }
};
