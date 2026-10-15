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
            $table->string('puesto', 60)->nullable();
            $table->string('correo', 150);
            $table->json('datos');                     // todos los campos del formulario
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
