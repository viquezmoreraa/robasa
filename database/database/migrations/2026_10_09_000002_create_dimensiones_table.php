<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dimensiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prueba_id')->constrained('pruebas');
            $table->string('codigo', 30);
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->unsignedTinyInteger('orden')->default(1);
            $table->decimal('puntaje_maximo', 8, 2)->nullable();

            $table->unique(['prueba_id', 'codigo'], 'uq_dim');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dimensiones');
    }
};
