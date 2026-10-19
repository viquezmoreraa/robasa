<?php

namespace App\Console\Commands;

use App\Services\Psicometria\MotorPsicometrico;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Verifica de punta a punta el Test de Escucha Activa contra la base de
 * datos real, usando el caso de la hoja original (EA.pdf / hoja "Test de
 * escucha" del Excel). No persiste nada: todo corre dentro de una
 * transacción que se revierte al final.
 *
 * Caso esperado (respuestas reales de la hoja, los 4 totales SÍ venían
 * escritos y se usaron para reconstruir la regla de puntaje):
 *   E (Escuchar sin interrumpir)              = 3
 *   F (Escucha prestando atención)             = 4
 *   G (Escucha más allá de las palabras)       = 4
 *   H (Escucha incentivando a profundizar)     = 2
 *
 * Uso:
 *   php artisan escucha:verificar --postulacion=1
 */
class VerificarEscuchaActiva extends Command
{
    protected $signature = 'escucha:verificar {--postulacion= : ID de una postulación real ya existente}';

    protected $description = 'Verifica el Test de Escucha Activa contra el caso resuelto de la hoja original (sin persistir datos).';

    /** numero de pregunta (1-20) => respuesta marcada en la hoja */
    private const RESPUESTAS = [
        1 => 'Sí', 2 => 'No', 3 => 'No', 4 => 'Sí', 5 => 'No',
        6 => 'Sí', 7 => 'No', 8 => 'Sí', 9 => 'No', 10 => 'No',
        11 => 'Sí', 12 => 'No', 13 => 'No', 14 => 'No', 15 => 'Sí',
        16 => 'Sí', 17 => 'Sí', 18 => 'No', 19 => 'Sí', 20 => 'Sí',
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
            $pruebaId = DB::table('pruebas')->where('codigo', 'ESCUCHA_ACTIVA')->value('id');

            if (! $pruebaId) {
                $this->error('No encuentro la prueba ESCUCHA_ACTIVA. ¿Corrió php artisan db:seed --class=EscuchaActivaSeeder?');
                DB::rollBack();

                return self::FAILURE;
            }

            $usuarioId = DB::table('users')->value('id');

            if (! $usuarioId) {
                $this->error('No hay ningún usuario en la tabla users para usar como asignada_por.');
                DB::rollBack();

                return self::FAILURE;
            }

            $asignacionId = DB::table('asignaciones_prueba')->insertGetId([
                'postulacion_id' => $postulacionId,
                'prueba_id' => $pruebaId,
                'estado' => 'EN_CURSO',
                'asignada_por' => $usuarioId,
            ]);

            $preguntas = DB::table('preguntas')
                ->where('prueba_id', $pruebaId)
                ->select('id', 'numero')
                ->get();

            foreach ($preguntas as $pregunta) {
                $respuesta = self::RESPUESTAS[$pregunta->numero] ?? null;

                if ($respuesta === null) {
                    $this->error("No tengo respuesta registrada para la pregunta numero {$pregunta->numero}.");
                    $ok = false;
                    continue;
                }

                $opcionId = DB::table('opciones')
                    ->where('pregunta_id', $pregunta->id)
                    ->where('texto', $respuesta)
                    ->value('id');

                DB::table('respuestas')->insert([
                    'asignacion_id' => $asignacionId,
                    'pregunta_id' => $pregunta->id,
                    'opcion_id' => $opcionId,
                ]);
            }

            $resultado = $motor->calcular($asignacionId);

            $this->line('Detalle completo del resultado (para inspeccionar cómo PuntajePorDimension llena rangoPorDimension):');
            $this->line(json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            // Verificación independiente: recalculamos E/F/G/H directo desde
            // la base de datos (sin depender de cómo el motor estructure su
            // objeto de resultado).
            $puntajesReales = DB::table('respuestas as r')
                ->join('opcion_dimension as od', 'od.opcion_id', '=', 'r.opcion_id')
                ->join('dimensiones as d', 'd.id', '=', 'od.dimension_id')
                ->where('r.asignacion_id', $asignacionId)
                ->groupBy('d.codigo')
                ->selectRaw('d.codigo as codigo, SUM(od.puntos) as total')
                ->pluck('total', 'codigo');

            $this->line('Puntajes reales (calculados directo de la BD): E='
                . ($puntajesReales['E'] ?? '?') . ' F=' . ($puntajesReales['F'] ?? '?')
                . ' G=' . ($puntajesReales['G'] ?? '?') . ' H=' . ($puntajesReales['H'] ?? '?'));

           $esperado = ['E' => 3, 'F' => 4, 'G' => 5, 'H' => 2];

            foreach ($esperado as $codigo => $valor) {
                if ((int) ($puntajesReales[$codigo] ?? -1) !== $valor) {
                    $this->error("Esperaba {$codigo}={$valor}, obtuve " . ($puntajesReales[$codigo] ?? 'null'));
                    $ok = false;
                }
            }

            if ($ok) {
                $this->info('Todo coincide con el caso de la hoja original (EA.pdf).');
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