<?php

namespace App\Services\Psicometria;

use App\Services\Psicometria\Estrategias\Combinacion;
use App\Services\Psicometria\Estrategias\DimensionMayor;
use App\Services\Psicometria\Estrategias\EstrategiaResultado;
use App\Services\Psicometria\Estrategias\PuntajePorDimension;
use App\Services\Psicometria\Estrategias\TotalCorrectas;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Orquesta: valida -> suma -> interpreta -> guarda (en una transacción).
 * Es genérico: nunca pregunta "qué prueba es", solo qué método usa.
 */
final class MotorPsicometrico
{
    /** Súbanla cuando cambien las reglas de cálculo; queda en cada resultado. */
    public const VERSION = '1.0.0';

    public function calcular(int $asignacionId): ResultadoFinal
    {
        $asignacion = DB::table('asignaciones_prueba')->where('id', $asignacionId)->first();
        if (!$asignacion) {
            throw new DomainException('La asignación no existe.');
        }
        if (!in_array($asignacion->estado, ['EN_CURSO', 'COMPLETADA'], true)) {
            throw new DomainException('La asignación no está en un estado calculable.');
        }

        $prueba = DB::table('pruebas')->where('id', $asignacion->prueba_id)->first();
        if ($prueba->estado !== 'APROBADA') {
            throw new DomainException('La prueba no está aprobada por Talento Humano.');
        }

        // El límite de tiempo se controla en el endpoint que RECIBE cada
        // respuesta (servidor), no aquí.
        $this->validarCompletitud($asignacionId, (int) $prueba->id);

        $detalle = $this->detalle($asignacionId, (int) $prueba->id);

        $perfiles = DB::table('perfiles')->where('prueba_id', $prueba->id)->get()
            ->keyBy('codigo')->map(fn ($p) => (array) $p)->all();
        $rangos = DB::table('rangos_interpretacion')->where('prueba_id', $prueba->id)->get()
            ->map(fn ($r) => (array) $r)->all();

        $final = $this->estrategiaPara($prueba->metodo_resultado)->interpretar($detalle, $perfiles, $rangos);

        $this->guardar($asignacionId, $detalle, $final);

        return $final;
    }

    private function validarCompletitud(int $asignacionId, int $pruebaId): void
    {
        // Para preguntas MAS_MENOS hay que exigir ambas selecciones; ajustar
        // aquí cuando se confirme el formato real de cada instrumento.
        $faltantes = DB::table('preguntas as p')
            ->leftJoin('respuestas as r', function ($join) use ($asignacionId) {
                $join->on('r.pregunta_id', '=', 'p.id')->where('r.asignacion_id', '=', $asignacionId);
            })
            ->where('p.prueba_id', $pruebaId)
            ->where('p.obligatoria', true)
            ->whereNull('r.id')
            ->count();

        if ($faltantes > 0) {
            throw new DomainException("Faltan {$faltantes} preguntas obligatorias por responder.");
        }
    }

    /** @return ResultadoDimension[] */
    private function detalle(int $asignacionId, int $pruebaId): array
    {
        $dimensiones = DB::table('dimensiones')->where('prueba_id', $pruebaId)->orderBy('orden')->get();

        // Si el candidato no suma nada en una dimensión, simplemente no aparece: cuenta como 0.
        $brutos = DB::table('respuestas as r')
            ->join('opcion_dimension as od', 'od.opcion_id', '=', 'r.opcion_id')
            ->where('r.asignacion_id', $asignacionId)
            ->groupBy('od.dimension_id')
            ->selectRaw('od.dimension_id, SUM(od.puntos) as bruto')
            ->pluck('bruto', 'dimension_id');

        // Máximo posible = suma, por pregunta, del mayor puntaje que alimenta la dimensión.
        // Calculado desde la matriz: si TH cambia la prueba, se actualiza solo.
        $maximos = collect(DB::select(
            'SELECT t.dimension_id, SUM(t.mx) AS maximo FROM (
                SELECT od.dimension_id, o.pregunta_id, MAX(od.puntos) AS mx
                  FROM opcion_dimension od
                  JOIN opciones o  ON o.id = od.opcion_id
                  JOIN preguntas p ON p.id = o.pregunta_id
                 WHERE p.prueba_id = ?
                 GROUP BY od.dimension_id, o.pregunta_id
             ) t GROUP BY t.dimension_id',
            [$pruebaId]
        ))->pluck('maximo', 'dimension_id');

        return $dimensiones->map(fn ($d) => new ResultadoDimension(
            dimensionId: (int) $d->id,
            codigo: $d->codigo,
            nombre: $d->nombre,
            bruto: (float) $brutos->get($d->id, 0),
            maximo: $maximos->has($d->id) ? (float) $maximos->get($d->id) : null,
        ))->all();
    }

    private function estrategiaPara(string $metodo): EstrategiaResultado
    {
        return match ($metodo) {
            'DIMENSION_MAYOR'       => new DimensionMayor(),
            'COMBINACION'           => new Combinacion(2),
            'PUNTAJE_POR_DIMENSION' => new PuntajePorDimension(),
            'TOTAL_CORRECTAS'       => new TotalCorrectas(),
            default => throw new DomainException("Método de resultado desconocido: {$metodo}"),
        };
    }

    /** @param ResultadoDimension[] $detalle */
    private function guardar(int $asignacionId, array $detalle, ResultadoFinal $final): void
    {
        DB::transaction(function () use ($asignacionId, $detalle, $final) {
            // Reemplazar, no insertar: calcular dos veces da lo mismo (idempotente).
            DB::table('resultados_dimension')->where('asignacion_id', $asignacionId)->delete();

            DB::table('resultados_dimension')->insert(array_map(fn (ResultadoDimension $d) => [
                'asignacion_id'  => $asignacionId,
                'dimension_id'   => $d->dimensionId,
                'puntaje_bruto'  => $d->bruto,
                'puntaje_maximo' => $d->maximo,
                'porcentaje'     => $d->porcentaje(),
                'rango_id'       => $final->rangoPorDimension[$d->dimensionId] ?? null,
            ], $detalle));

            DB::table('resultados_prueba')->upsert([[
                'asignacion_id'  => $asignacionId,
                'perfil_id'      => $final->perfilId,
                'resumen'        => $final->resumen,
                'interpretacion' => $final->interpretacion, // copia del texto vigente (snapshot)
                'version_motor'  => self::VERSION,
                'calculado_en'   => now(),
            ]], ['asignacion_id'], ['perfil_id', 'resumen', 'interpretacion', 'version_motor', 'calculado_en']);

            DB::table('asignaciones_prueba')->where('id', $asignacionId)->update([
                'estado'    => 'COMPLETADA',
                'fecha_fin' => DB::raw('COALESCE(fecha_fin, NOW())'),
            ]);
        });
    }
}
