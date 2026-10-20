<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Siembra el "Test de Keirsey": 70 ítems de elección forzada A/B sobre 4
 * dicotomías independientes (E/I, S/N, T/F, J/P), usando la nueva
 * estrategia DicotomiaCombinada.
 *
 * Estructura de dimensiones (12 filas por prueba):
 *  - 4 filas "padre" (EI, SN, TF, JP) que NO reciben puntaje directo,
 *    existen solo como agrupador (dimension_padre_id) para que
 *    DicotomiaCombinada sepa qué 2 hijas forman cada par.
 *  - 8 filas "hija" (E, I, S, N, T, F, J, P), cada una con
 *    dimension_padre_id apuntando a su par. Cada opción de cada
 *    pregunta otorga 1 punto a la hija correspondiente (A o B).
 *
 * El orden (`orden`) de las dimensiones es el que determina el orden de
 * las letras en el código final (E/I primero, S/N segundo, T/F tercero,
 * J/P cuarto), ya que DicotomiaCombinada agrupa respetando el orden de
 * aparición en $detalle.
 *
 * Clave de puntaje de los 70 ítems: reconstruida a mano por no existir
 * una clave oficial, a partir del contenido de cada ítem (ver
 * database/seeders/data/keirsey.json, campos a_dim/b_dim). Validada
 * contra el caso de ejemplo del PDF escaneado (ESTP.pdf): el nombre del
 * archivo es solo una referencia y no aplica como resultado esperado
 * (confirmado por el equipo); el resultado real esperado para ese caso
 * es ESTJ.
 *
 * Perfiles: 16 filas (uno por código de 4 letras), con texto de
 * interpretación oficial. DicotomiaCombinada busca $perfiles[$codigo].
 */
class KeirseySeeder extends Seeder
{
    private const TIPO_PREGUNTA_UNICA = 'OPCION_UNICA';

    public function run(): void
    {
        $json = json_decode(
            file_get_contents(__DIR__ . '/data/keirsey.json'),
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

            // Primera pasada: crear las 4 filas padre (sin dimension_padre_id).
            $dimensionId = [];
            foreach ($json['dimensiones'] as $dim) {
                if ($dim['padre'] !== null) {
                    continue;
                }
                $dimensionId[$dim['codigo']] = DB::table('dimensiones')->insertGetId([
                    'prueba_id' => $pruebaId,
                    'codigo' => $dim['codigo'],
                    'nombre' => $dim['nombre'],
                ]);
            }

            // Segunda pasada: crear las 8 filas hija, ya con el padre resuelto.
            // IMPORTANTE: el orden de inserción (y por tanto el id, que es lo
            // que determina el orden en que MotorPsicometrico::detalle() las
            // entrega) sigue a propósito la secuencia E,I,S,N,T,F,J,P -> esto
            // es lo que hace que DicotomiaCombinada arme el código final en
            // el orden correcto (EI primero, SN segundo, TF tercero, JP
            // cuarto). Si la tabla dimensiones tuviera una columna `orden`
            // propia y MotorPsicometrico ordenara por ella en vez de por id,
            // habría que fijarla aquí también con estos mismos valores.
            foreach ($json['dimensiones'] as $dim) {
                if ($dim['padre'] === null) {
                    continue;
                }
                $dimensionId[$dim['codigo']] = DB::table('dimensiones')->insertGetId([
                    'prueba_id' => $pruebaId,
                    'codigo' => $dim['codigo'],
                    'nombre' => $dim['nombre'],
                    'dimension_padre_id' => $dimensionId[$dim['padre']],
                ]);
            }

            // Preguntas + opciones A/B + opcion_dimension (1 punto a la
            // dimensión hija correspondiente de cada opción).
            foreach ($json['preguntas'] as $p) {
                $preguntaId = DB::table('preguntas')->insertGetId([
                    'prueba_id' => $pruebaId,
                    'enunciado' => $p['texto'],
                    'numero' => $p['orden'],
                    'tipo' => self::TIPO_PREGUNTA_UNICA,
                ]);

                $opciones = [
                    ['texto' => $p['a_texto'], 'dim' => $p['a_dim']],
                    ['texto' => $p['b_texto'], 'dim' => $p['b_dim']],
                ];

                foreach ($opciones as $orden => $op) {
                    $opcionId = DB::table('opciones')->insertGetId([
                        'pregunta_id' => $preguntaId,
                        'texto' => $op['texto'],
                        'orden' => $orden + 1,
                    ]);

                    DB::table('opcion_dimension')->insert([
                        'opcion_id' => $opcionId,
                        'dimension_id' => $dimensionId[$op['dim']],
                        'puntos' => 1,
                    ]);
                }
            }

            // Perfiles: 16 códigos de 4 letras con su texto de interpretación.
            foreach ($json['perfiles'] as $perfil) {
                DB::table('perfiles')->insert([
                    'prueba_id' => $pruebaId,
                    'codigo' => $perfil['codigo'],
                    'nombre' => $perfil['nombre'],
                    'texto_interpretacion' => $perfil['texto_interpretacion'],
                ]);
            }
        });

        $this->command?->info('Test de Keirsey sembrado: 1 prueba, 12 dimensiones (4 pares + 8 letras), 70 preguntas, 16 perfiles.');
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