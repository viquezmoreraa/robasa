<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perfiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prueba_id')->constrained('pruebas');
            $table->string('codigo', 30);
            $table->string('nombre', 100);
            $table->text('texto_interpretacion');      // texto APROBADO por TH
            $table->json('regla')->nullable();

            $table->unique(['prueba_id', 'codigo'], 'uq_perfil');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perfiles');
    }
};
