<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Siembra el "Test E.D. (Estilos de Dirección)": 36 ítems de elección
 * forzada A/B, usando la estrategia ya existente PuntajePorDimension (no
 * hace falta estrategia nueva ni migración: cada ítem enfrenta dos
 * categorías DISTINTAS según el ítem -no son pares fijos como Keirsey-,
 * así que basta con que cada opción sume 1 punto a su propia dimensión).
 *
 * Las 6 categorías (AUT, RES, DEM, AFI, FOR, DEL) son una hipótesis de
 * trabajo del equipo, no categorías oficiales confirmadas (no hay manual
 * oficial del instrumento). Ver observaciones en data/ed.json para el
 * detalle completo de cómo se clasificó cada afirmación y sus límites
 * conocidos (4 ítems donde ambas opciones cayeron en la misma categoría).
 *
 * A propósito NO se siembran perfiles ni rangos_interpretacion: sin
 * manual oficial no se inventa texto de interpretación; se muestra solo
 * puntaje directo y porcentaje por categoría.
 */
class EdSeeder extends Seeder
{
    private const TIPO_PREGUNTA_UNICA = 'OPCION_UNICA';

    public function run(): void
    {
        $json = json_decode(
            file_get_contents(__DIR__ . '/data/ed.json'),
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

            // 6 dimensiones (categorías), con el máximo real según cuántas
            // de las 72 afirmaciones se clasificaron en cada una (no es
            // parejo, ver observaciones).
            $dimensionId = [];
            foreach ($json['dimensiones'] as $dim) {
                $dimensionId[$dim['codigo']] = DB::table('dimensiones')->insertGetId([
                    'prueba_id' => $pruebaId,
                    'codigo' => $dim['codigo'],
                    'nombre' => $dim['nombre'],
                    'puntaje_maximo' => $dim['puntaje_maximo'],
                ]);
            }

            // 36 preguntas, 2 opciones (A/B) cada una, 1 punto a la
            // categoría de la opción elegida.
            foreach ($json['preguntas'] as $p) {
                $preguntaId = DB::table('preguntas')->insertGetId([
                    'prueba_id' => $pruebaId,
                    'enunciado' => "Ítem {$p['orden']}", // el enunciado real son las 2 opciones, no un encabezado único
                    'numero' => $p['orden'],
                    'tipo' => self::TIPO_PREGUNTA_UNICA,
                ]);

                $opciones = [
                    ['texto' => $p['a_texto'], 'cat' => $p['a_cat']],
                    ['texto' => $p['b_texto'], 'cat' => $p['b_cat']],
                ];

                foreach ($opciones as $orden => $op) {
                    $opcionId = DB::table('opciones')->insertGetId([
                        'pregunta_id' => $preguntaId,
                        'texto' => $op['texto'],
                        'orden' => $orden + 1,
                    ]);

                    DB::table('opcion_dimension')->insert([
                        'opcion_id' => $opcionId,
                        'dimension_id' => $dimensionId[$op['cat']],
                        'puntos' => 1,
                    ]);
                }
            }
        });

        $this->command?->info('Test E.D. sembrado: 1 prueba, 6 dimensiones, 36 preguntas (72 opciones).');
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