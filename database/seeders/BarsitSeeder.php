<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Siembra el "Test Rápido de Barranquilla" (BARSIT): 60 ítems de opción
 * múltiple (5 opciones), 5 áreas de 12 ítems cada una, usando la
 * estrategia ya existente PuntajePorDimension (no hace falta estrategia
 * nueva: 1 punto por acierto por dimensión, ResultadoDimension::porcentaje()
 * ya da el (aciertos/12)*100 que pide el documento del instrumento).
 *
 * Áreas (ver observaciones en data/barsit.json para el detalle de cómo se
 * confirmó la agrupación real, por residuo de número de ítem mod 5, contra
 * las fórmulas del Excel oficial - NO son bloques de 1-12, 13-24, etc.):
 *  - INFO   (resto 1): Información
 *  - VOCAB  (resto 2): Comprensión de vocabulario
 *  - VERBAL (resto 3): Razonamiento verbal
 *  - LOGICO (resto 4): Razonamiento lógico
 *  - NUM    (resto 0): Razonamiento numérico
 *
 * Los 12 ítems de Razonamiento numérico eran de respuesta abierta en el
 * instrumento original; se reformularon como opción múltiple (la pareja
 * correcta + 4 distractoras) porque el motor no soporta respuesta libre
 * (decisión acordada con el equipo).
 *
 * A propósito NO se siembra rangos_interpretacion: el instrumento indica
 * no inventar baremos sin validar: se muestra solo puntuación directa y
 * porcentaje por área (PuntajePorDimension ya deja interpretacion/rango en
 * null si no hay rango configurado, sin inventar nada).
 */
class BarsitSeeder extends Seeder
{
    private const TIPO_PREGUNTA_UNICA = 'OPCION_UNICA';

    public function run(): void
    {
        $json = json_decode(
            file_get_contents(__DIR__ . '/data/barsit.json'),
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

            // 5 dimensiones (áreas), 12 puntos máximo cada una.
            $dimensionId = [];
            foreach ($json['dimensiones'] as $dim) {
                $dimensionId[$dim['codigo']] = DB::table('dimensiones')->insertGetId([
                    'prueba_id' => $pruebaId,
                    'codigo' => $dim['codigo'],
                    'nombre' => $dim['nombre'],
                    'puntaje_maximo' => 12,
                ]);
            }

            // 60 preguntas, 5 opciones cada una, 1 punto a la dimensión de
            // su área solo en la opción correcta.
            foreach ($json['preguntas'] as $p) {
                $preguntaId = DB::table('preguntas')->insertGetId([
                    'prueba_id' => $pruebaId,
                    'enunciado' => $p['texto'],
                    'numero' => $p['orden'],
                    'tipo' => self::TIPO_PREGUNTA_UNICA,
                ]);

                foreach ($p['opciones'] as $i => $texto) {
                    $numeroOpcion = $i + 1; // 1..5, coincide con p['correcta']

                    $opcionId = DB::table('opciones')->insertGetId([
                        'pregunta_id' => $preguntaId,
                        'texto' => $texto,
                        'orden' => $numeroOpcion,
                    ]);

                    DB::table('opcion_dimension')->insert([
                        'opcion_id' => $opcionId,
                        'dimension_id' => $dimensionId[$p['area']],
                        'puntos' => $numeroOpcion === $p['correcta'] ? 1 : 0,
                    ]);
                }
            }
        });

        $this->command?->info('Test BARSIT sembrado: 1 prueba, 5 dimensiones, 60 preguntas (300 opciones).');
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