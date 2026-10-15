<?php

namespace App\Console\Commands;

use App\Services\Psicometria\MotorPsicometrico;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Prueba de punta a punta: carga el caso ya resuelto a mano en el Excel del
 * DISC (28 grupos, cada uno con una palabra marcada MAS y otra MENOS), lo
 * calcula con el motor real contra MySQL y compara con el patrón que dio el
 * Excel. Todo ocurre dentro de una transacción que se deshace al final: no
 * deja datos en la base.
 */
class VerificarDisc extends Command
{
    protected $signature = 'disc:verificar {--postulacion=999999 : id de postulación a usar}';

    protected $description = 'Calcula el caso resuelto del Excel del DISC y lo compara con el patrón esperado';

    public function handle(MotorPsicometrico $motor): int
    {
        $datos = json_decode(
            file_get_contents(database_path('seeders/data/disc.json')),
            true, 512, JSON_THROW_ON_ERROR
        );
        $caso = $datos['caso_ejemplo'];

        $prueba = DB::table('pruebas')
            ->where('codigo', $datos['prueba']['codigo'])
            ->where('version', $datos['prueba']['version'])
            ->first();

        if (!$prueba) {
            $this->error('Primero cargue la prueba: php artisan db:seed --class=DiscSeeder');
            return self::FAILURE;
        }

        $ok = true;

        DB::beginTransaction();
        try {
            $usuarioId = DB::table('users')->insertGetId([
                'name'       => 'verificacion-disc',
                'email'      => 'verificacion-' . uniqid() . '@example.test',
                'password'   => 'no-usar',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $asignacionId = DB::table('asignaciones_prueba')->insertGetId([
                'postulacion_id' => (int) $this->option('postulacion'),
                'prueba_id'      => $prueba->id,
                'intento'        => 1,
                'asignada_por'   => $usuarioId,
                'estado'         => 'EN_CURSO',
            ]);

            $preguntas = DB::table('preguntas')->where('prueba_id', $prueba->id)->pluck('id', 'numero');
            $opciones = DB::table('opciones')
                ->whereIn('pregunta_id', $preguntas->values()->all())
                ->get(['id', 'pregunta_id', 'orden'])
                ->groupBy('pregunta_id');

            $filas = [];
            foreach ($caso['respuestas'] as $r) {
                $preguntaId = $preguntas[$r['grupo']];
                $opcionesGrupo = $opciones[$preguntaId];
                $filas[] = [
                    'asignacion_id' => $asignacionId,
                    'pregunta_id'   => $preguntaId,
                    'opcion_id'     => $opcionesGrupo->firstWhere('orden', $r['mas_orden'])->id,
                    'seleccion'     => 'MAS',
                ];
                $filas[] = [
                    'asignacion_id' => $asignacionId,
                    'pregunta_id'   => $preguntaId,
                    'opcion_id'     => $opcionesGrupo->firstWhere('orden', $r['menos_orden'])->id,
                    'seleccion'     => 'MENOS',
                ];
            }
            DB::table('respuestas')->insert($filas);

            $resultado = $motor->calcular($asignacionId);
            $this->line('Resumen del motor: ' . $resultado->resumen);
            $this->newLine();

            $obtenidos = DB::table('resultados_dimension as r')
                ->join('dimensiones as d', 'd.id', '=', 'r.dimension_id')
                ->where('r.asignacion_id', $asignacionId)
                ->get(['d.codigo', 'r.puntaje_bruto'])
                ->pluck('puntaje_bruto', 'codigo');

            $filasTabla = [];
            foreach (['D', 'I', 'S', 'C'] as $eje) {
                $esperado = $caso['esperado']['netos'][$eje];
                $obtenido = isset($obtenidos[$eje]) ? (int) $obtenidos[$eje] : null;
                $bien = $obtenido === $esperado;
                $ok = $ok && $bien;
                $filasTabla[] = [$eje, $esperado, $obtenido ?? '-', $bien ? 'OK' : 'DIFERENTE'];
            }
            $this->table(['Eje', 'Neto esperado', 'Neto motor', ''], $filasTabla);

            $perfilResultado = DB::table('resultados_prueba')->where('asignacion_id', $asignacionId)->first();
            $nombreObtenido = $perfilResultado && $perfilResultado->perfil_id
                ? DB::table('perfiles')->where('id', $perfilResultado->perfil_id)->value('nombre')
                : null;

            $this->line("Patrón esperado: {$caso['esperado']['patron_nombre']} (código {$caso['esperado']['codigo']})");
            $this->line('Patrón obtenido: ' . ($nombreObtenido ?? 'ninguno'));

            $ok = $ok && $nombreObtenido === $caso['esperado']['patron_nombre'];
        } catch (\Throwable $e) {
            $this->error('Error: ' . $e->getMessage());
            $ok = false;
        } finally {
            DB::rollBack(); // no deja nada guardado
        }

        $ok
            ? $this->info('Todo coincide con el Excel.')
            : $this->error('Hay diferencias o un error: revise el detalle de arriba.');

        return $ok ? self::SUCCESS : self::FAILURE;
    }
}
