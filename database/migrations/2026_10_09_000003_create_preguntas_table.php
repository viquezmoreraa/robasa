<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preguntas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prueba_id')->constrained('pruebas');
            $table->unsignedSmallInteger('numero');    // orden oficial
            $table->text('enunciado');
            $table->enum('tipo', ['OPCION_UNICA', 'ESCALA', 'MAS_MENOS'])->default('OPCION_UNICA');
            $table->boolean('obligatoria')->default(true);

            $table->unique(['prueba_id', 'numero'], 'uq_preg');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preguntas');
    }
};
