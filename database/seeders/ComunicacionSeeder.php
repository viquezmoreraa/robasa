<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComunicacionSeeder extends Seeder
{
    private const TIPO_PREGUNTA_UNICA = 'OPCION_UNICA';

    public function run(): void
    {
        $json = json_decode(
            file_get_contents(__DIR__ . '/data/comunicacion.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        DB::transaction(function () use ($json) {
            $this->limpiarSiExiste($json['prueba']['codigo']);

            $pruebaId = DB::table('pruebas')->insertGetId([
                'codigo' => $json['prueba']['codigo'],
                'nombre' => $json['prueba']['nombre'],
                'metodo_resultado' => $json['prueba']['metodo_resultado'],
                'version' => '1.0',
                'instrucciones' => $json['prueba']['descripcion'],
                'estado' => 'APROBADA',
            ]);

            // Dimensiones: código => id
            $dimensionId = [];
            foreach ($json['dimensiones'] as $dim) {
                $dimensionId[$dim['codigo']] = DB::table('dimensiones')->insertGetId([
                    'prueba_id' => $pruebaId,
                    'codigo' => $dim['codigo'],
                    'nombre' => $dim['nombre'],
                ]);
            }

            // Preguntas + opciones A/B + opcion_dimension (A=1 punto, B=0)
            foreach ($json['preguntas'] as $p) {
                $preguntaId = DB::table('preguntas')->insertGetId([
                    'prueba_id' => $pruebaId,
                    'enunciado' => $p['texto'],
                    'numero' => $p['orden'],
                    'tipo' => self::TIPO_PREGUNTA_UNICA,
                ]);

                foreach (['A' => 1, 'B' => 0] as $letra => $puntos) {
                    $opcionId = DB::table('opciones')->insertGetId([
                        'pregunta_id' => $preguntaId,
                        'texto' => $letra,
                        'orden' => $letra === 'A' ? 1 : 2,
                    ]);

                    DB::table('opcion_dimension')->insert([
                        'opcion_id' => $opcionId,
                        'dimension_id' => $dimensionId[$p['dimension']],
                        'puntos' => $puntos,
                    ]);
                }
            }

            // Perfiles (perfiles.codigo == dimensiones.codigo es lo que usa
            // DimensionMayor para encontrar el perfil de la dimensión ganadora;
            // la tabla perfiles no tiene dimension_id).
            foreach ($json['perfiles'] as $perfil) {
                DB::table('perfiles')->insert([
                    'prueba_id' => $pruebaId,
                    'codigo' => $perfil['codigo'],
                    'nombre' => $perfil['nombre'],
                    'texto_interpretacion' => $perfil['interpretacion'],
                ]);
            }
        });

        $this->command?->info('Test de Comunicación sembrado: 1 prueba, 3 dimensiones, 30 preguntas, 3 perfiles.');
    }

    private function limpiarSiExiste(string $codigoPrueba): void
    {
        $pruebaId = DB::table('pruebas')->where('codigo', $codigoPrueba)->value('id');

        if (! $pruebaId) {
            return;
        }

        $preguntaIds = DB::table('preguntas')->where('prueba_id', $pruebaId)->pluck('id');
        $opcionIds = DB::table('opciones')->whereIn('pregunta_id', $preguntaIds)->pluck('id');

        DB::table('opcion_dimension')->whereIn('opcion_id', $opcionIds)->delete();
        DB::table('opciones')->whereIn('id', $opcionIds)->delete();
        DB::table('preguntas')->where('prueba_id', $pruebaId)->delete();
        DB::table('perfiles')->where('prueba_id', $pruebaId)->delete();
        DB::table('dimensiones')->where('prueba_id', $pruebaId)->delete();
        DB::table('pruebas')->where('id', $pruebaId)->delete();
    }
}