<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla de clasificación del DISC: código de 4 posiciones (D,I,S,C, cada una
        // de 1 a 7) -> perfil. perfil_id queda NULL para los códigos que, en el
        // material original, caen en un patrón sin número asignado (ver observaciones
        // del seeder): esos casos no deben inventarse, se marcan como pendientes.
        Schema::create('disc_clasificacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prueba_id')->constrained('pruebas');
            $table->char('codigo', 4);               // ej.: "2464" (posiciones D,I,S,C)
            $table->foreignId('perfil_id')->nullable()->constrained('perfiles');

            $table->unique(['prueba_id', 'codigo'], 'uq_disc_codigo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disc_clasificacion');
    }
};
