<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opcion_dimension', function (Blueprint $table) {
            // LA MATRIZ DE PUNTUACIÓN: cuántos puntos suma cada opción y a qué dimensión.
            $table->foreignId('opcion_id')->constrained('opciones');
            $table->foreignId('dimension_id')->constrained('dimensiones');
            $table->decimal('puntos', 6, 2);

            $table->primary(['opcion_id', 'dimension_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opcion_dimension');
    }
};
