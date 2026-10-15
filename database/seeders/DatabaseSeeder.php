<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'jose@robasa.com'],
            ['name' => 'Jose', 'password' => 'Jose2026', 'rol' => 'contratista']
        );

        $this->call(BigFiveSeeder::class);

        // Pruebas que se responden subiendo un archivo
        foreach ([
            ['CONOCIMIENTOS', 'Prueba de conocimientos', 'Descargue, resuelva y suba la prueba en PDF o imagen.'],
            ['CASO_PRACTICO', 'Caso práctico', 'Resuelva el caso práctico indicado por el contratista y suba su respuesta.'],
        ] as [$codigo, $nombre, $instrucciones]) {
            DB::table('pruebas')->updateOrInsert(
                ['codigo' => $codigo, 'version' => '1'],
                ['nombre' => $nombre, 'instrucciones' => $instrucciones, 'metodo_resultado' => 'TOTAL_CORRECTAS',
                 'estado' => 'APROBADA', 'aprobada_por' => 'Talento Humano']
            );
        }
    }
}
