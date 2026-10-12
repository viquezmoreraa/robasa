<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resultados_dimension', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asignacion_id')->constrained('asignaciones_prueba');
            $table->foreignId('dimension_id')->constrained('dimensiones');
            $table->decimal('puntaje_bruto', 8, 2);
            $table->decimal('puntaje_maximo', 8, 2)->nullable();
            $table->decimal('porcentaje', 5, 2)->nullable();
            $table->foreignId('rango_id')->nullable()->constrained('rangos_interpretacion');

            $table->unique(['asignacion_id', 'dimension_id'], 'uq_resdim');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resultados_dimension');
    }
};
