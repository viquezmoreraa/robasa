<?php

namespace App\Console\Commands;

use App\Services\Psicometria\MotorPsicometrico;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Prueba de punta a punta: carga el caso resuelto del Excel del Big Five,
 * lo calcula con el motor real contra MySQL y compara con los puntajes del
 * Excel. Todo ocurre dentro de una transacción que se deshace al final:
 * no deja datos en la base.
 */
class VerificarBigFive extends Command
{
    protected $signature = 'bfq:verificar {--postulacion=999999 : id de postulación a usar (si ya existe la llave foránea, use una real)}';

    protected $description = 'Calcula el caso resuelto del Excel del Big Five y lo compara con los puntajes esperados';

    public function handle(MotorPsicometrico $motor): int
    {
        $datos = json_decode(
            file_get_contents(database_path('seeders/data/bigfive.json')),
            true, 512, JSON_THROW_ON_ERROR
        );
        $caso = $datos['caso_ejemplo'];

        $prueba = DB::table('pruebas')
            ->where('codigo', $datos['prueba']['codigo'])
            ->where('version', $datos['prueba']['version'])
            ->first();

        if (!$prueba) {
            $this->error('Primero cargue la prueba: php artisan db:seed --class=BigFiveSeeder');
            return self::FAILURE;
        }

        $todoOk = true;

        DB::beginTransaction();
        try {
            $usuarioId = DB::table('users')->insertGetId([
                'name'       => 'verificacion-bfq',
                'email'      => 'verificacion-' . uniqid() . '@example.test',
                'password'   => 'no-usar',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $asignacionId = DB::table('asignaciones_prueba')->insertGetId([
                'postulacion_id' => (int) $this->option('postulacion'),
                'prueba_id'      => $prueba->id,
                'intento'        => 1,
                'sexo_baremo'    => $caso['sexo'],
                'asignada_por'   => $usuarioId,
                'estado'         => 'EN_CURSO',
            ]);

            $preguntas = DB::table('preguntas')->where('prueba_id', $prueba->id)->pluck('id', 'numero');
            $opciones = DB::table('opciones')
                ->whereIn('pregunta_id', $preguntas->values()->all())
                ->get(['id', 'pregunta_id', 'orden'])
                ->groupBy('pregunta_id');

            $filas = [];
            foreach ($caso['respuestas'] as $i => $valor) {
                $preguntaId = $preguntas[$i + 1];
                $filas[] = [
                    'asignacion_id' => $asignacionId,
                    'pregunta_id'   => $preguntaId,
                    'opcion_id'     => $opciones[$preguntaId]->firstWhere('orden', $valor)->id,
                    'seleccion'     => 'UNICA',
                ];
            }
            DB::table('respuestas')->insert($filas);

            $resultado = $motor->calcular($asignacionId);
            $this->line('Resumen del motor: ' . $resultado->resumen);
            $this->newLine();

            $obtenidos = DB::table('resultados_dimension as r')
                ->join('dimensiones as d', 'd.id', '=', 'r.dimension_id')
                ->where('r.asignacion_id', $asignacionId)
                ->get(['d.codigo', 'r.puntaje_bruto', 'r.puntaje_t'])
                ->keyBy('codigo');

            $tabla = [];
            foreach ($caso['esperado'] as $codigo => $esp) {
                $fila = $obtenidos->get($codigo);
                $pd = $fila ? (int) $fila->puntaje_bruto : null;
                $t = $fila && $fila->puntaje_t !== null ? (int) $fila->puntaje_t : null;
                $ok = $pd === $esp['pd'] && $t === $esp['t'];
                $todoOk = $todoOk && $ok;
                $tabla[] = [$codigo, $esp['pd'], $pd ?? '-', $esp['t'], $t ?? '-', $ok ? 'OK' : 'DIFERENTE'];
            }

            $this->table(['Escala', 'PD esperado', 'PD motor', 'T esperado', 'T motor', ''], $tabla);
        } catch (\Throwable $e) {
            $this->error('Error: ' . $e->getMessage());
            $todoOk = false;
        } finally {
            DB::rollBack(); // no deja nada guardado
        }

        $todoOk
            ? $this->info('Todo coincide con el Excel.')
            : $this->error('Hay diferencias o un error: revise el detalle de arriba.');

        return $todoOk ? self::SUCCESS : self::FAILURE;
    }
}
