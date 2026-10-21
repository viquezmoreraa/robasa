<?php

namespace App\Console\Commands;

use App\Services\Psicometria\MotorPsicometrico;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Verifica el Test E.D. contra el caso real de la hoja "Prueba" del Excel
 * oficial "Hoja_de_respuestas_ED.xlsx" (36 respuestas A/B reales). Corre
 * dentro de una transacción que siempre se revierte.
 *
 * Con la clasificación de categorías del equipo (ver ed.json), este caso
 * da: AUT=9, RES=2, DEM=7, AFI=7, FOR=9, DEL=2 (suma 36).
 */
class VerificarEd extends Command
{
    protected $signature = 'ed:verificar {--postulacion=1}';
    protected $description = 'Verifica el cálculo del Test E.D. contra el caso real de la hoja "Prueba" del Excel oficial';

    /** Respuestas reales de la hoja "Prueba", ítem 1..36 => 'A' o 'B'. */
    private const RESPUESTAS = [
        1 => 'B', 2 => 'A', 3 => 'B', 4 => 'A', 5 => 'A', 6 => 'B', 7 => 'B', 8 => 'B', 9 => 'A', 10 => 'B',
        11 => 'A', 12 => 'B', 13 => 'B', 14 => 'A', 15 => 'B', 16 => 'A', 17 => 'A', 18 => 'A', 19 => 'B', 20 => 'B',
        21 => 'B', 22 => 'A', 23 => 'A', 24 => 'A', 25 => 'A', 26 => 'B', 27 => 'A', 28 => 'A', 29 => 'B', 30 => 'B',
        31 => 'A', 32 => 'B', 33 => 'B', 34 => 'A', 35 => 'A', 36 => 'A',
    ];

    public function handle(MotorPsicometrico $motor): int
    {
        $postulacionId = (int) $this->option('postulacion');

        DB::beginTransaction();

        try {
            $pruebaId = DB::table('pruebas')->where('codigo', 'ED')->value('id');

            if (! $pruebaId) {
                $this->error('No existe la prueba ED. Corra primero el seeder.');
                DB::rollBack();

                return self::FAILURE;
            }

            $asignadaPor = DB::table('users')->value('id');

            if (! $asignadaPor) {
                $this->error('No hay ningún usuario en la tabla `users` para usar como `asignada_por`. Cree uno primero.');
                DB::rollBack();

                return self::FAILURE;
            }

            $asignacionId = DB::table('asignaciones_prueba')->insertGetId([
                'postulacion_id' => $postulacionId,
                'prueba_id' => $pruebaId,
                'asignada_por' => $asignadaPor,
                'estado' => 'COMPLETADA',
            ]);

            $preguntas = DB::table('preguntas')
                ->where('prueba_id', $pruebaId)
                ->orderBy('numero')
                ->get();

            foreach ($preguntas as $pregunta) {
                $letra = self::RESPUESTAS[$pregunta->numero] ?? null;

                if ($letra === null) {
                    continue;
                }

                // orden 1 = opción A, orden 2 = opción B (así las insertó el seeder)
                $ordenOpcion = $letra === 'A' ? 1 : 2;

                $opcionId = DB::table('opciones')
                    ->where('pregunta_id', $pregunta->id)
                    ->where('orden', $ordenOpcion)
                    ->value('id');

                DB::table('respuestas')->insert([
                    'asignacion_id' => $asignacionId,
                    'pregunta_id' => $pregunta->id,
                    'opcion_id' => $opcionId,
                ]);
            }

            $resultado = $motor->calcular($asignacionId);

            $this->info('--- Resultado del motor ---');
            $this->line(json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            // Cruce independiente: recomputa los 6 puntajes por categoría
            // directamente por SQL, sin pasar por el motor ni la estrategia.
            $filas = DB::table('respuestas')
                ->join('opcion_dimension', 'respuestas.opcion_id', '=', 'opcion_dimension.opcion_id')
                ->join('dimensiones', 'opcion_dimension.dimension_id', '=', 'dimensiones.id')
                ->where('respuestas.asignacion_id', $asignacionId)
                ->select('dimensiones.codigo', 'dimensiones.nombre', 'opcion_dimension.puntos')
                ->get();

            $sql = [];
            $nombrePorCodigo = [];
            foreach ($filas as $f) {
                $sql[$f->codigo] = ($sql[$f->codigo] ?? 0) + $f->puntos;
                $nombrePorCodigo[$f->codigo] = $f->nombre;
            }

            $esperado = ['AUT' => 9, 'RES' => 2, 'DEM' => 7, 'AFI' => 7, 'FOR' => 9, 'DEL' => 2];

            $sqlConNombre = [];
            foreach ($sql as $codigo => $puntos) {
                $sqlConNombre[$nombrePorCodigo[$codigo] ?? $codigo] = $puntos;
            }

            $this->info('--- Cruce independiente por SQL (categoría => puntos) ---');
            $this->line(json_encode($sqlConNombre, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $ok = true;
            foreach ($esperado as $cat => $valor) {
                $obtenido = $sql[$cat] ?? null;
                // comparar como número: puntos viene de MySQL como decimal
                // y PHP lo suma como float.
                if ($obtenido === null || (float) $obtenido !== (float) $valor) {
                    $this->error("Discrepancia en {$cat}: esperado {$valor}, SQL dio " . ($obtenido ?? 'null'));
                    $ok = false;
                }
            }

            $totalEsperado = array_sum($esperado);
            $totalObtenido = array_sum($sql);
            $this->info("Total: {$totalObtenido}/36 (esperado {$totalEsperado}/36)");

            if ($ok && (float) $totalObtenido === (float) $totalEsperado) {
                $this->info('Todo coincide con el caso real de la hoja "Prueba" del Excel oficial.');
            } else {
                $this->error('Hay discrepancias, revisar la clasificación de categorías.');
                $ok = false;
            }

            DB::rollBack();

            return $ok ? self::SUCCESS : self::FAILURE;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Error: ' . $e->getMessage());
            $this->line($e->getTraceAsString());

            return self::FAILURE;
        }
    }
}