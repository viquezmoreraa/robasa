<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Carga el Big Five (BFQ) desde database/seeders/data/bigfive.json:
 * dimensiones y subescalas, 132 preguntas con sus 5 opciones, la matriz de
 * puntuación (con los ítems inversos ya invertidos) y los baremos.
 * Todo el contenido proviene del Excel entregado por Talento Humano.
 *
 * Uso: php artisan db:seed --class=BigFiveSeeder
 */
class BigFiveSeeder extends Seeder
{
    public function run(): void
    {
        $datos = json_decode(
            file_get_contents(database_path('seeders/data/bigfive.json')),
            true, 512, JSON_THROW_ON_ERROR
        );
        $p = $datos['prueba'];

        if (DB::table('pruebas')->where('codigo', $p['codigo'])->where('version', $p['version'])->exists()) {
            $this->command?->warn("La prueba {$p['codigo']} (versión {$p['version']}) ya está cargada. No se hizo nada.");
            return;
        }

        DB::transaction(function () use ($datos, $p) {
            $pruebaId = DB::table('pruebas')->insertGetId([
                'codigo'           => $p['codigo'],
                'version'          => $p['version'],
                'nombre'           => $p['nombre'],
                'instrucciones'    => $p['instrucciones'],
                'metodo_resultado' => $p['metodo_resultado'],
                'minutos_limite'   => null,
                'permite_reanudar' => true,
                'estado'           => 'APROBADA',
                'aprobada_por'     => 'Talento Humano',
                'fecha_aprobacion' => null,
            ]);

            // Dimensiones: primero las principales, después las subescalas
            $dimIds = [];
            $padres = [];
            foreach ([false, true] as $conPadre) {
                foreach ($datos['dimensiones'] as $d) {
                    if (($d['padre'] !== null) !== $conPadre) {
                        continue;
                    }
                    $dimIds[$d['codigo']] = DB::table('dimensiones')->insertGetId([
                        'prueba_id'          => $pruebaId,
                        'dimension_padre_id' => $d['padre'] !== null ? $dimIds[$d['padre']] : null,
                        'codigo'             => $d['codigo'],
                        'nombre'             => $d['nombre'],
                        'orden'              => $d['orden'],
                    ]);
                    $padres[$d['codigo']] = $d['padre'];
                }
            }

            // Preguntas, opciones y matriz de puntuación.
            // Cada opción suma a su subescala y a la dimensión principal.
            // Ítem inverso: puntos = 6 - valor.
            $filasOpcionDimension = [];
            foreach ($datos['preguntas'] as $q) {
                $preguntaId = DB::table('preguntas')->insertGetId([
                    'prueba_id'   => $pruebaId,
                    'numero'      => $q['numero'],
                    'enunciado'   => $q['enunciado'],
                    'tipo'        => 'ESCALA',
                    'obligatoria' => true,
                ]);

                foreach ($datos['opciones'] as $o) {
                    $opcionId = DB::table('opciones')->insertGetId([
                        'pregunta_id' => $preguntaId,
                        'orden'       => $o['valor'],
                        'texto'       => $o['texto'],
                    ]);

                    $puntos = $q['inverso'] ? 6 - $o['valor'] : $o['valor'];

                    $filasOpcionDimension[] = [
                        'opcion_id'    => $opcionId,
                        'dimension_id' => $dimIds[$q['escala']],
                        'puntos'       => $puntos,
                    ];
                    if ($padres[$q['escala']] !== null) {
                        $filasOpcionDimension[] = [
                            'opcion_id'    => $opcionId,
                            'dimension_id' => $dimIds[$padres[$q['escala']]],
                            'puntos'       => $puntos,
                        ];
                    }
                }
            }
            foreach (array_chunk($filasOpcionDimension, 500) as $lote) {
                DB::table('opcion_dimension')->insert($lote);
            }

            // Baremos (puntaje directo -> T), por escala y sexo
            $filasBaremo = [];
            foreach ($datos['baremos'] as $sexo => $porEscala) {
                foreach ($porEscala as $codigo => $tabla) {
                    foreach ($tabla as $pd => $t) {
                        $filasBaremo[] = [
                            'prueba_id'       => $pruebaId,
                            'dimension_id'    => $dimIds[$codigo],
                            'sexo'            => $sexo,
                            'puntaje_directo' => (int) $pd,
                            'puntaje_t'       => (int) $t,
                        ];
                    }
                }
            }
            foreach (array_chunk($filasBaremo, 500) as $lote) {
                DB::table('baremos')->insert($lote);
            }
        });

        $this->command?->info('Big Five cargado: 132 preguntas, 16 escalas y sus baremos.');
    }
}
