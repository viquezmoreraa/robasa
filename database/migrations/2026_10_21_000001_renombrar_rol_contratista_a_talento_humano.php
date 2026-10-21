<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * El rol interno 'contratista' pasa a llamarse 'talento_humano'. El ENUM se amplía,
 * se mueven las cuentas existentes y luego se quita el valor viejo: reducirlo antes
 * de actualizar fallaría con filas que todavía dicen 'contratista'.
 *
 * ->change() funciona en MySQL y en SQLite (igual que en add_metodo_dicotomia_combinada).
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->cambiarRol(['postulante', 'contratista', 'talento_humano']);
        DB::table('users')->where('rol', 'contratista')->update(['rol' => 'talento_humano']);
        $this->cambiarRol(['postulante', 'talento_humano']);
    }

    public function down(): void
    {
        $this->cambiarRol(['postulante', 'contratista', 'talento_humano']);
        DB::table('users')->where('rol', 'talento_humano')->update(['rol' => 'contratista']);
        $this->cambiarRol(['postulante', 'contratista']);
    }

    private function cambiarRol(array $roles): void
    {
        Schema::table('users', function (Blueprint $table) use ($roles) {
            $table->enum('rol', $roles)->default('postulante')->change();
        });
    }
};
