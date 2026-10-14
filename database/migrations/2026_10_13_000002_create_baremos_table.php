<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tablas de conversión: puntaje directo -> puntaje T, por escala y sexo.
        Schema::create('baremos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prueba_id')->constrained('pruebas');
            $table->foreignId('dimension_id')->constrained('dimensiones');
            $table->enum('sexo', ['H', 'M']);
            $table->unsignedSmallInteger('puntaje_directo');
            $table->unsignedSmallInteger('puntaje_t');

            $table->unique(['dimension_id', 'sexo', 'puntaje_directo'], 'uq_baremo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('baremos');
    }
};
