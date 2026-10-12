<?php

namespace App\Services\Psicometria\Estrategias;

use App\Services\Psicometria\ResultadoFinal;

/** Cada dimensión se ubica en su propio rango (bajo / medio / alto...). */
final class PuntajePorDimension implements EstrategiaResultado
{
    use UtilesEstrategia;

    public function interpretar(array $detalle, array $perfiles, array $rangos): ResultadoFinal
    {
        $porDimension = [];
        $partes = [];
        $textos = [];

        foreach ($detalle as $d) {
            $rango = $this->buscarRango($rangos, $d->dimensionId, $d->bruto);

            if ($rango === null) {
                // Rangos con huecos = dato mal cargado; se muestra, no se oculta.
                $partes[] = "{$d->nombre}: sin rango";
                continue;
            }

            $porDimension[$d->dimensionId] = (int) $rango['id'];
            $partes[] = "{$d->nombre}: {$rango['nivel']}";
            $textos[] = "{$d->nombre}: {$rango['texto_interpretacion']}";
        }

        return new ResultadoFinal(
            resumen: implode(' | ', $partes),
            interpretacion: $textos === [] ? null : implode("\n", $textos),
            rangoPorDimension: $porDimension,
        );
    }
}
