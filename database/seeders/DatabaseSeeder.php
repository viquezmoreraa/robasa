<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
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
    }
}
