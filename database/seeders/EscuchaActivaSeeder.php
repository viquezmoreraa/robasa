<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Siembra el "Test de Escucha Activa": 20 afirmaciones Sí/No, 5 por cada
 * una de las 4 dimensiones, con puntaje independiente por dimensión (usa
 * la estrategia ya existente PuntajePorDimension). Este paquete solo
 * carga datos.
 *
 * Regla de puntaje y texto de interpretación tomados tal cual del
 * documento "Calificación de Test Escucha activa" (PDF escaneado):
 *  - Dim 1 (E) Escuchar sin interrumpir: ítems 1,5,9,13,17 -> 1 pto c/NO
 *  - Dim 2 (F) Escuchar prestando 100% de atención: ítems 2,6,10,14,18 -> 1 pto c/NO
 *  - Dim 3 (G) Escuchar más allá de las palabras: ítems 3,7 -> 1 pto c/NO; ítems 11,15,19 -> 1 pto c/SI
 *  - Dim 4 (H) Escuchar incentivando al otro a profundizar: ítems 4,8,12 -> 1 pto c/SI; ítems 16,20 -> 1 pto c/NO
 * Las 4 dimensiones quedan parejas: 5 ítems / 5 puntos máximo cada una.
 *
 * IMPORTANTE: PuntajePorDimension::interpretar() NO usa la tabla
 * `perfiles` en absoluto. El texto de interpretación por rango de puntaje
 * (5 / 3-4 / 0-2) va en `rangos_interpretacion`, que es lo que
 * MotorPsicometrico::calcular() consulta y le pasa a la estrategia
 * (confirmado contra el código real de MotorPsicometrico.php). Requiere
 * la migración create_rangos_interpretacion_table.
 *
 * Esquema confirmado (igual que Comunicación):
 *  - preguntas usa `numero`/`enunciado`, tipo 'OPCION_UNICA'
 *  - ninguna tabla catálogo tiene timestamps
 */
class EscuchaActivaSeeder extends Seeder
{
    private const TIPO_PREGUNTA_UNICA = 'OPCION_UNICA';

    public function run(): void
    {
        $json = json_decode(
            file_get_contents(__DIR__ . '/data/escucha_activa.json'),
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

            // Dimensiones: código => id (las 4 quedan parejas, 5 puntos máximo)
            $dimensionId = [];
            foreach ($json['dimensiones'] as $dim) {
                $dimensionId[$dim['codigo']] = DB::table('dimensiones')->insertGetId([
                    'prueba_id' => $pruebaId,
                    'codigo' => $dim['codigo'],
                    'nombre' => $dim['nombre'],
                    'puntaje_maximo' => 5,
                ]);
            }

            // Preguntas + opciones Sí/No + opcion_dimension
            foreach ($json['preguntas'] as $p) {
                $preguntaId = DB::table('preguntas')->insertGetId([
                    'prueba_id' => $pruebaId,
                    'enunciado' => $p['texto'],
                    'numero' => $p['orden'],
                    'tipo' => self::TIPO_PREGUNTA_UNICA,
                ]);

                foreach (['Sí', 'No'] as $orden => $letra) {
                    $opcionId = DB::table('opciones')->insertGetId([
                        'pregunta_id' => $preguntaId,
                        'texto' => $letra,
                        'orden' => $orden + 1,
                    ]);

                    DB::table('opcion_dimension')->insert([
                        'opcion_id' => $opcionId,
                        'dimension_id' => $dimensionId[$p['dimension']],
                        'puntos' => $letra === $p['bueno'] ? 1 : 0,
                    ]);
                }
            }

            // Rangos de interpretación: 3 bandas por dimensión (5 / 3-4 / 0-2
            // puntos), con el texto real del documento de calificación.
            foreach ($json['rangos'] as $rango) {
                DB::table('rangos_interpretacion')->insert([
                    'prueba_id' => $pruebaId,
                    'dimension_id' => $dimensionId[$rango['dimension']],
                    'puntaje_min' => $rango['min'],
                    'puntaje_max' => $rango['max'],
                    'nivel' => $rango['nivel'],
                    'texto_interpretacion' => $rango['interpretacion'],
                ]);
            }
        });

        $this->command?->info('Test de Escucha Activa sembrado: 1 prueba, 4 dimensiones, 20 preguntas, 12 rangos de interpretación.');
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
        DB::table('rangos_interpretacion')->where('prueba_id', $pruebaId)->delete();
        DB::table('perfiles')->where('prueba_id', $pruebaId)->delete();
        DB::table('dimensiones')->where('prueba_id', $pruebaId)->delete();
        DB::table('pruebas')->where('id', $pruebaId)->delete();
    }
}