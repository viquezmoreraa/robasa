<?php

namespace App\Console\Commands;

use App\Services\Psicometria\MotorPsicometrico;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Verifica el Test BARSIT contra el caso oficial resuelto del Excel
 * "Calificación BARSIT" (el mismo que aparece como "Caso 3" en el
 * documento de instrucciones del instrumento). Corre dentro de una
 * transacción que siempre se revierte.
 *
 * Todas las respuestas son correctas (opción 1 de cada pregunta, que es
 * siempre la correcta según el seeder) EXCEPTO los ítems 36, 51 y 53,
 * que se responden mal a propósito para reproducir:
 *   Información 10/12, Vocabulario 12/12, Razonamiento verbal 11/12,
 *   Razonamiento lógico 12/12, Razonamiento numérico 12/12,
 *   total 57/60 = 95%.
 */
class VerificarBarsit extends Command
{
    protected $signature = 'barsit:verificar {--postulacion=1}';
    protected $description = 'Verifica el cálculo del Test BARSIT contra el caso oficial resuelto (57/60 = 95%)';

    /** Ítems que a propósito se responden con una opción incorrecta. */
    private const RESPUESTAS_INCORRECTAS = [36, 51, 53];

    public function handle(MotorPsicometrico $motor): int
    {
        $postulacionId = (int) $this->option('postulacion');

        DB::beginTransaction();

        try {
            $pruebaId = DB::table('pruebas')->where('codigo', 'BARSIT')->value('id');

            if (! $pruebaId) {
                $this->error('No existe la prueba BARSIT. Corra primero el seeder.');
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
                // La posición de la opción correcta varía por pregunta (es
                // la posición real del test impreso), así que se busca por
                // opcion_dimension.puntos en vez de asumir un `orden` fijo.
                $esIncorrecta = in_array($pregunta->numero, self::RESPUESTAS_INCORRECTAS, true);

                $query = DB::table('opciones')
                    ->join('opcion_dimension', 'opciones.id', '=', 'opcion_dimension.opcion_id')
                    ->where('opciones.pregunta_id', $pregunta->id)
                    ->where('opcion_dimension.puntos', $esIncorrecta ? 0 : 1)
                    ->orderBy('opciones.orden');

                $opcionId = $query->value('opciones.id');

                DB::table('respuestas')->insert([
                    'asignacion_id' => $asignacionId,
                    'pregunta_id' => $pregunta->id,
                    'opcion_id' => $opcionId,
                ]);
            }

            $resultado = $motor->calcular($asignacionId);

            $this->info('--- Resultado del motor ---');
            $this->line(json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            // Cruce independiente: recomputa los 5 puntajes por área
            // directamente por SQL, sin pasar por el motor ni la estrategia.
            $filas = DB::table('respuestas')
                ->join('opcion_dimension', 'respuestas.opcion_id', '=', 'opcion_dimension.opcion_id')
                ->join('dimensiones', 'opcion_dimension.dimension_id', '=', 'dimensiones.id')
                ->where('respuestas.asignacion_id', $asignacionId)
                ->select('dimensiones.codigo', 'opcion_dimension.puntos')
                ->get();

            $sql = [];
            foreach ($filas as $f) {
                $sql[$f->codigo] = ($sql[$f->codigo] ?? 0) + $f->puntos;
            }

            $esperado = ['INFO' => 10, 'VOCAB' => 12, 'VERBAL' => 11, 'LOGICO' => 12, 'NUM' => 12];

            $this->info('--- Cruce independiente por SQL (área => puntos) ---');
            $this->line(json_encode($sql, JSON_PRETTY_PRINT));

            $ok = true;
            foreach ($esperado as $area => $valor) {
                $obtenido = $sql[$area] ?? null;
                // comparar como número: puntos viene de MySQL como decimal
                // y PHP lo suma como float (ver bug ya encontrado en Keirsey).
                if ($obtenido === null || (float) $obtenido !== (float) $valor) {
                    $this->error("Discrepancia en {$area}: esperado {$valor}, SQL dio " . ($obtenido ?? 'null'));
                    $ok = false;
                }
            }

            $totalEsperado = array_sum($esperado);
            $totalObtenido = array_sum($sql);
            $this->info("Total: {$totalObtenido}/60 (esperado {$totalEsperado}/60 = 95%)");

            if ($ok && (float) $totalObtenido === (float) $totalEsperado) {
                $this->info('Todo coincide con el caso oficial del Excel (Caso 3 del documento del instrumento).');
            } else {
                $this->error('Hay discrepancias, revisar la clave de respuesta.');
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