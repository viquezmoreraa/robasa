<?php

namespace App\Console\Commands;

use App\Services\Psicometria\MotorPsicometrico;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Verifica de punta a punta el Test de Comunicación contra la base de datos
 * real, usando el caso de la hoja escaneada (30 respuestas A/B marcadas a
 * mano). No persiste nada: todo corre dentro de una transacción que se
 * revierte al final (igual que bfq:verificar y disc:verificar).
 *
 * Caso esperado (contado a mano sobre la hoja, los totales venían en blanco):
 *   Noble = 6, Socrático = 4, Reflexivo = 5  -> estilo predominante: Noble
 *
 * Uso:
 *   php artisan comunicacion:verificar --postulacion=1
 */
class VerificarComunicacion extends Command
{
    protected $signature = 'comunicacion:verificar {--postulacion= : ID de una postulación real ya existente}';

    protected $description = 'Verifica el Test de Comunicación contra el caso resuelto de la hoja escaneada (sin persistir datos).';

    /** orden de pregunta (1-30) => letra marcada en la hoja */
    private const RESPUESTAS = [
        1 => 'A', 2 => 'B', 3 => 'A', 4 => 'A', 5 => 'A',
        6 => 'B', 7 => 'B', 8 => 'A', 9 => 'A', 10 => 'B',
        11 => 'A', 12 => 'B', 13 => 'B', 14 => 'B', 15 => 'A',
        16 => 'B', 17 => 'A', 18 => 'B', 19 => 'B', 20 => 'A',
        21 => 'B', 22 => 'B', 23 => 'A', 24 => 'B', 25 => 'B',
        26 => 'A', 27 => 'A', 28 => 'A', 29 => 'B', 30 => 'A',
    ];

    public function handle(MotorPsicometrico $motor): int
    {
        $postulacionId = (int) $this->option('postulacion');

        if ($postulacionId <= 0) {
            $this->error('Debe indicar --postulacion=<id> de una postulación real existente.');

            return self::FAILURE;
        }

        $ok = true;

        DB::beginTransaction();

        try {
            $pruebaId = DB::table('pruebas')->where('codigo', 'COMUNICACION')->value('id');

            if (! $pruebaId) {
                $this->error('No encuentro la prueba COMUNICACION. ¿Corrió php artisan db:seed --class=ComunicacionSeeder?');
                DB::rollBack();

                return self::FAILURE;
            }

            $asignacionId = DB::table('asignaciones_prueba')->insertGetId([
                'postulacion_id' => $postulacionId,
                'prueba_id' => $pruebaId,
                'estado' => 'EN_CURSO',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $preguntas = DB::table('preguntas')
                ->where('prueba_id', $pruebaId)
                ->select('id', 'orden')
                ->get();

            foreach ($preguntas as $pregunta) {
                $letra = self::RESPUESTAS[$pregunta->orden] ?? null;

                if ($letra === null) {
                    $this->error("No tengo respuesta registrada para la pregunta orden {$pregunta->orden}.");
                    $ok = false;
                    continue;
                }

                $opcionId = DB::table('opciones')
                    ->where('pregunta_id', $pregunta->id)
                    ->where('texto', $letra)
                    ->value('id');

                DB::table('respuestas')->insert([
                    'asignacion_id' => $asignacionId,
                    'pregunta_id' => $pregunta->id,
                    'opcion_id' => $opcionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $resultado = $motor->calcular($asignacionId);

            $puntajes = [];
            foreach ($resultado->rangoPorDimension ?? [] as $dim) {
                $puntajes[$dim->codigo] = $dim->bruto;
            }

            $this->line('Puntajes obtenidos: N=' . ($puntajes['N'] ?? '?')
                . ' S=' . ($puntajes['S'] ?? '?')
                . ' R=' . ($puntajes['R'] ?? '?'));
            $this->line('Perfil/resumen: ' . $resultado->resumen);

            $esperado = ['N' => 6, 'S' => 4, 'R' => 5];

            foreach ($esperado as $codigo => $valor) {
                if (($puntajes[$codigo] ?? null) != $valor) {
                    $this->error("Esperaba {$codigo}={$valor}, obtuve " . ($puntajes[$codigo] ?? 'null'));
                    $ok = false;
                }
            }

            if (! str_contains(mb_strtolower($resultado->resumen ?? ''), 'noble')) {
                $this->error('Esperaba que el estilo predominante fuera "Noble".');
                $ok = false;
            }

            if ($ok) {
                $this->info('Todo coincide con el caso de la hoja escaneada.');
            } else {
                $this->error('Hay diferencias: revise el detalle de arriba.');
            }
        } catch (\Throwable $e) {
            $this->error('Error: ' . $e->getMessage());
            $ok = false;
        } finally {
            DB::rollBack();
        }

        return $ok ? self::SUCCESS : self::FAILURE;
    }
}
