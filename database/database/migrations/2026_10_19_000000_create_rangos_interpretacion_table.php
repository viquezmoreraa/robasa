<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla que faltaba para que la estrategia PuntajePorDimension (y
 * cualquier otra que use UtilesEstrategia::buscarRango()) pueda resolver
 * un "nivel" (bajo/medio/alto...) y su texto de interpretación a partir
 * de un puntaje bruto.
 *
 * Nombre y columnas confirmados contra el código real:
 *  - MotorPsicometrico::calcular() hace
 *    DB::table('rangos_interpretacion')->where('prueba_id', $prueba->id)->get()
 *    (sin filtrar por dimension_id: eso lo hace buscarRango() en memoria)
 *  - UtilesEstrategia::buscarRango() lee de cada fila:
 *    $r['dimension_id'], $r['puntaje_min'], $r['puntaje_max'],
 *    $r['nivel'], $r['texto_interpretacion'], $r['id']
 *
 * dimension_id es NULLABLE a propósito: buscarRango() contempla
 * dimension_id = null para los rangos del puntaje TOTAL de la prueba
 * (no de una dimensión específica), para cuando alguna estrategia lo
 * necesite.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rangos_interpretacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prueba_id')->constrained('pruebas')->cascadeOnDelete();
            $table->foreignId('dimension_id')->nullable()->constrained('dimensiones')->cascadeOnDelete();
            $table->decimal('puntaje_min', 8, 2);
            $table->decimal('puntaje_max', 8, 2);
            $table->string('nivel', 50);
            $table->text('texto_interpretacion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rangos_interpretacion');
    }
};