<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Cuenta interna de Talento Humano: el registro público solo crea postulantes
        User::updateOrCreate(
            ['email' => 'jose@robasa.com'],
            ['name' => 'Jose', 'username' => 'jose', 'password' => Hash::make('Jose2026'), 'rol' => 'talento_humano']
        );

        $this->call(BigFiveSeeder::class);
        $this->call(ComunicacionSeeder::class);
        $this->call(EscuchaActivaSeeder::class);
        $this->call(KeirseySeeder::class);
        $this->call(BarsitSeeder::class);
        $this->call(EdSeeder::class);
        $this->call(DiscSeeder::class);

        // Pruebas adicionales (se responden en formulario, igual que las demás)
        foreach ([
            ['CONOCIMIENTOS', 'Prueba de conocimientos', 'Lea cada pregunta con atención y responda en el formulario.'],
            ['CASO_PRACTICO', 'Caso práctico', 'Analice el caso planteado y responda las preguntas del formulario.'],
        ] as [$codigo, $nombre, $instrucciones]) {
            DB::table('pruebas')->updateOrInsert(
                ['codigo' => $codigo, 'version' => '1'],
                ['nombre' => $nombre, 'instrucciones' => $instrucciones, 'metodo_resultado' => 'TOTAL_CORRECTAS',
                 'estado' => 'APROBADA', 'aprobada_por' => 'Talento Humano']
            );
        }
    }
}
