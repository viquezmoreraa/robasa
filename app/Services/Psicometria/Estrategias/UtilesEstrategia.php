<?php

namespace App\Services\Psicometria\Estrategias;

use App\Services\Psicometria\ResultadoDimension;
use DomainException;

trait UtilesEstrategia
{
    /** Tolerancia para comparar decimales sin errores de punto flotante. */
    private function iguales(float $a, float $b): bool
    {
        return abs($a - $b) < 0.00001;
    }

    /**
     * Mayor a menor. El orden es estable: ante igualdad se respeta el
     * orden de las dimensiones definido en la prueba.
     *
     * @param  ResultadoDimension[] $detalle
     * @return ResultadoDimension[]
     */
    private function ordenarPorPuntaje(array $detalle): array
    {
        if ($detalle === []) {
            throw new DomainException('La prueba no tiene dimensiones.');
        }

        usort($detalle, fn (ResultadoDimension $a, ResultadoDimension $b) => $b->bruto <=> $a->bruto);

        return $detalle;
    }

    /**
     * Busca el rango (bordes incluidos) de una dimensión; $dimensionId = null
     * busca entre los rangos del puntaje total.
     *
     * @param array<int,array> $rangos
     */
    private function buscarRango(array $rangos, ?int $dimensionId, float $valor): ?array
    {
        foreach ($rangos as $r) {
            $rd = $r['dimension_id'] === null ? null : (int) $r['dimension_id'];
            if ($rd !== $dimensionId) {
                continue;
            }
            if ($valor >= (float) $r['puntaje_min'] - 0.00001 && $valor <= (float) $r['puntaje_max'] + 0.00001) {
                return $r;
            }
        }

        return null;
    }
}
