<?php

namespace App\Console\Commands;

use App\Services\Psicometria\MotorPsicometrico;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Verifica el Test de Keirsey contra el caso real de ejemplo (PDF
 * escaneado "ESTP.pdf", 70 ítems respondidos). Corre dentro de una
 * transacción que siempre se revierte (no deja datos de prueba).
 *
 * Respuestas reales marcadas en el PDF (A o B por ítem, 1..70) y clave
 * de puntaje reconstruida (ver keirsey.json). Resultado esperado:
 * E=5, I=4, S=12, N=8, T=13, F=5, J=14, P=9 -> código ESTJ.
 * (El nombre del archivo "ESTP" es solo una referencia, no el resultado
 * esperado - confirmado por el equipo.)
 */
class VerificarKeirsey extends Command
{
    protected $signature = 'keirsey:verificar {--postulacion=1}';
    protected $description = 'Verifica el cálculo del Test de Keirsey contra el caso de ejemplo real (ESTP.pdf)';

    /** Respuestas marcadas en el PDF, ítem 1..70 => 'A' o 'B'. */
    private const RESPUESTAS = [
        1 => 'A', 2 => 'B', 3 => 'B', 4 => 'A', 5 => 'B', 6 => 'A', 7 => 'A', 8 => 'A', 9 => 'A', 10 => 'B',
        11 => 'B', 12 => 'A', 13 => 'A', 14 => 'A', 15 => 'A', 16 => 'B', 17 => 'A', 18 => 'A', 19 => 'A', 20 => 'B',
        21 => 'B', 22 => 'A', 23 => 'A', 24 => 'B', 25 => 'A', 26 => 'A', 27 => 'A', 28 => 'A', 29 => 'A', 30 => 'A',
        31 => 'A', 32 => 'B', 33 => 'A', 34 => 'B', 35 => 'B', 36 => 'A', 37 => 'A', 38 => 'A', 39 => 'B', 40 => 'A',
        41 => 'A', 42 => 'A', 43 => 'B', 44 => 'A', 45 => 'A', 46 => 'B', 47 => 'A', 48 => 'A', 49 => 'A', 50 => 'B',
        51 => 'A', 52 => 'B', 53 => 'A', 54 => 'A', 55 => 'A', 56 => 'A', 57 => 'B', 58 => 'B', 59 => 'B', 60 => 'B',
        61 => 'A', 62 => 'B', 63 => 'A', 64 => 'B', 65 => 'A', 66 => 'A', 67 => 'A', 68 => 'A', 69 => 'A', 70 => 'B',
    ];

    public function handle(MotorPsicometrico $motor): int
    {
        $postulacionId = (int) $this->option('postulacion');

        DB::beginTransaction();

        try {
            $pruebaId = DB::table('pruebas')->where('codigo', 'KEIRSEY')->value('id');

            if (! $pruebaId) {
                $this->error('No existe la prueba KEIRSEY. Corra primero el seeder.');
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
                $opcionOrden = $letra === 'A' ? 1 : 2;

                $opcionId = DB::table('opciones')
                    ->where('pregunta_id', $pregunta->id)
                    ->where('orden', $opcionOrden)
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

            // Cruce independiente: recomputa los 8 puntajes letra por letra
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

            $esperado = ['E' => 5, 'I' => 4, 'S' => 12, 'N' => 8, 'T' => 13, 'F' => 5, 'J' => 14, 'P' => 9];

            $this->info('--- Cruce independiente por SQL (letra => puntos) ---');
            $this->line(json_encode($sql, JSON_PRETTY_PRINT));

            $ok = true;
            foreach ($esperado as $letra => $valor) {
                // puntos viene de MySQL como decimal -> PHP lo suma como
                // float; se compara como número, no con !== estricto
                // (float 5.0 !== int 5 aunque sean el mismo valor).
                $obtenido = $sql[$letra] ?? null;
                if ($obtenido === null || (float) $obtenido !== (float) $valor) {
                    $this->error("Discrepancia en {$letra}: esperado {$valor}, SQL dio " . ($obtenido ?? 'null'));
                    $ok = false;
                }
            }

            $codigoEsperado = 'ESTJ';
            $this->info("Código esperado: {$codigoEsperado}");

            if ($ok) {
                $this->info('Todo coincide con el caso de ejemplo real (ESTP.pdf, resultado real ESTJ).');
            } else {
                $this->error('Hay discrepancias, revisar la clave de puntaje.');
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