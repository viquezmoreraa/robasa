<?php

namespace App\Services\Psicometria\Estrategias;

use App\Services\Psicometria\ResultadoFinal;

/**
 * Convierte el puntaje directo de cada escala en puntaje T usando una tabla
 * de baremos (ya filtrada por el sexo del candidato). Si un puntaje no está
 * en la tabla NO se inventa un valor: queda sin T y se indica en el resumen.
 */
final class PuntajeT implements EstrategiaResultado
{
    /** @param array<int,array<int,int>> $baremos dimension_id => [puntaje_directo => T] */
    public function __construct(private readonly array $baremos)
    {
    }

    public function interpretar(array $detalle, array $perfiles, array $rangos): ResultadoFinal
    {
        $t = [];
        $partes = [];

        foreach ($detalle as $d) {
            $pd = (int) round($d->bruto);
            $valor = $this->baremos[$d->dimensionId][$pd] ?? null;

            if ($valor !== null) {
                $t[$d->dimensionId] = $valor;
            }

            // El resumen muestra solo las dimensiones principales (y Distorsión),
            // no las subescalas (que tienen padre).
            if ($d->padreId === null) {
                $partes[] = "{$d->codigo}: " . ($valor ?? 'sin baremo');
            }
        }

        return new ResultadoFinal(resumen: implode(' | ', $partes), puntajeTPorDimension: $t);
    }
}
