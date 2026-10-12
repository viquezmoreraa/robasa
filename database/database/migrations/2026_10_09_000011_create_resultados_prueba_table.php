<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resultados_prueba', function (Blueprint $table) {
            // Una fila por asignación: la propia asignacion_id es la llave primaria.
            $table->foreignId('asignacion_id')->primary()->constrained('asignaciones_prueba');
            $table->foreignId('perfil_id')->nullable()->constrained('perfiles');
            $table->string('resumen', 255)->nullable();
            $table->text('interpretacion')->nullable();       // copia (snapshot) del texto vigente
            $table->string('version_motor', 20);
            $table->dateTime('calculado_en')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resultados_prueba');
    }
};
