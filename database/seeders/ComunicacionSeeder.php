<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Siembra la prueba "Test de Estilos de Comunicación" (Noble / Socrático / Reflexivo).
 *
 * No agrega ninguna estrategia nueva: esta prueba usa exactamente el mismo
 * mecanismo que DimensionMayor (la dimensión con más puntos gana) que ya
 * está implementado y probado desde el motor original. Aquí solo se cargan
 * datos: 1 prueba, 3 dimensiones, 30 preguntas (2 opciones A/B cada una) y
 * 3 perfiles (uno por dimensión).
 *
 * Idempotente: si ya existe una prueba con este código, borra sus datos
 * (preguntas, opciones, opcion_dimension, dimensiones, perfiles) antes de
 * volver a insertarlos, igual que BigFiveSeeder y DiscSeeder.
 *
 * SUPUESTOS DE ESQUEMA (ajustar si su tabla usa otros nombres de columna):
 *  - preguntas.tipo acepta el valor 'UNICA' (selección única, 1 respuesta).
 *    Si su ENUM usa otro literal para "una sola respuesta", cambie la
 *    constante TIPO_PREGUNTA_UNICA más abajo.
 *  - perfiles tiene una columna `interpretacion` (texto) y `dimension_id`
 *    (FK nullable a dimensiones). Si en su esquema el texto vive en otra
 *    columna (p. ej. `descripcion`), ajuste el arreglo $perfilData.
 */
class ComunicacionSeeder extends Seeder
{
    /** Ajuste este valor si su ENUM de preguntas.tipo usa otro literal. */
    private const TIPO_PREGUNTA_UNICA = 'UNICA';

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
                'descripcion' => $json['prueba']['descripcion'],
                'metodo_resultado' => $json['prueba']['metodo_resultado'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Dimensiones: código => id
            $dimensionId = [];
            foreach ($json['dimensiones'] as $dim) {
                $dimensionId[$dim['codigo']] = DB::table('dimensiones')->insertGetId([
                    'prueba_id' => $pruebaId,
                    'codigo' => $dim['codigo'],
                    'nombre' => $dim['nombre'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Preguntas + opciones A/B + opcion_dimension (A=1 punto, B=0)
            foreach ($json['preguntas'] as $p) {
                $preguntaId = DB::table('preguntas')->insertGetId([
                    'prueba_id' => $pruebaId,
                    'texto' => $p['texto'],
                    'orden' => $p['orden'],
                    'tipo' => self::TIPO_PREGUNTA_UNICA,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach (['A' => 1, 'B' => 0] as $letra => $puntos) {
                    $opcionId = DB::table('opciones')->insertGetId([
                        'pregunta_id' => $preguntaId,
                        'texto' => $letra,
                        'orden' => $letra === 'A' ? 1 : 2,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('opcion_dimension')->insert([
                        'opcion_id' => $opcionId,
                        'dimension_id' => $dimensionId[$p['dimension']],
                        'puntos' => $puntos,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Perfiles (uno por dimensión, usado por DimensionMayor)
            foreach ($json['perfiles'] as $perfil) {
                DB::table('perfiles')->insert([
                    'prueba_id' => $pruebaId,
                    'dimension_id' => $dimensionId[$perfil['dimension']],
                    'codigo' => $perfil['codigo'],
                    'nombre' => $perfil['nombre'],
                    'interpretacion' => $perfil['interpretacion'],
                    'created_at' => now(),
                    'updated_at' => now(),
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
