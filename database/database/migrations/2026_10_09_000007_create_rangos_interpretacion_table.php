<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rangos_interpretacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prueba_id')->constrained('pruebas');
            $table->foreignId('dimension_id')->nullable()->constrained('dimensiones');   // null = puntaje total
            $table->string('nivel', 30);
            $table->decimal('puntaje_min', 8, 2);
            $table->decimal('puntaje_max', 8, 2);
            $table->text('texto_interpretacion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rangos_interpretacion');
    }
};
