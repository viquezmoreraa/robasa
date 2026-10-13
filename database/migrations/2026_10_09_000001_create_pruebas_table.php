<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pruebas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30);              // DISC, COMUNICACION, ESCUCHA, KEIRSEY, GOLEMAN, ED, BIG5, BARSIT
            $table->string('version', 20);             // versión oficial entregada por TH
            $table->string('nombre', 100);
            $table->text('instrucciones');
            $table->enum('metodo_resultado', ['DIMENSION_MAYOR', 'PUNTAJE_POR_DIMENSION', 'COMBINACION', 'TOTAL_CORRECTAS']);
            $table->unsignedSmallInteger('minutos_limite')->nullable();   // null = sin límite
            $table->boolean('permite_reanudar')->default(true);
            $table->enum('estado', ['BORRADOR', 'APROBADA', 'RETIRADA'])->default('BORRADOR');
            $table->string('aprobada_por', 100)->nullable();
            $table->date('fecha_aprobacion')->nullable();

            $table->unique(['codigo', 'version'], 'uq_prueba_version');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pruebas');
    }
};
