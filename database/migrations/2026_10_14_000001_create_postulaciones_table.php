<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('rol', ['postulante', 'contratista'])->default('postulante')->after('email');
        });

        Schema::create('postulaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // terminos.html
            $table->timestamp('acepto_terminos_at')->nullable();

            // datos-personales.html
            $table->string('nombre', 150)->nullable();
            $table->string('cedula', 30)->nullable();
            $table->date('fecha_nacimiento')->nullable();          // dia + mes + anio
            $table->string('telefono', 20)->nullable();
            $table->string('correo_contacto', 150)->nullable();    // campo "correo" del formulario
            $table->text('direccion')->nullable();
            $table->boolean('tiene_licencia')->nullable();
            $table->string('tipo_licencia', 10)->nullable();       // B1, B2, B3, A1, otra
            $table->string('tiene_vehiculo', 10)->nullable();      // carro, moto, ambos, no

            // puesto-experiencia.html
            $table->string('puesto', 60)->nullable();
            $table->string('salario', 30)->nullable();             // rango, ej. 350000-400000
            $table->string('zona', 40)->nullable();
            $table->string('disponibilidad', 30)->nullable();
            $table->boolean('tiene_experiencia')->nullable();
            $table->text('detalles_experiencia')->nullable();

            // academico.html
            $table->string('nivel_educativo', 40)->nullable();

            // empleos-anteriores.html
            $table->text('empleos_anteriores')->nullable();

            // crear-cuenta.html (la contraseña va a users)
            $table->string('correo', 150);

            $table->timestamps();
        });

        Schema::table('asignaciones_prueba', function (Blueprint $table) {
            $table->string('archivo_path')->nullable();
            $table->text('respuesta_texto')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('asignaciones_prueba', function (Blueprint $table) {
            $table->dropColumn(['archivo_path', 'respuesta_texto']);
        });
        Schema::dropIfExists('postulaciones');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('rol');
        });
    }
};
