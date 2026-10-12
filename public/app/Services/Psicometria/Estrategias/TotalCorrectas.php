<?php

namespace App\Services\Psicometria\Estrategias;

use App\Services\Psicometria\ResultadoFinal;

/** Suma todos los aciertos y busca el rango del total (dimension_id NULL). */
final class TotalCorrectas implements EstrategiaResultado
{
    use UtilesEstrategia;

    public function interpretar(array $detalle, array $perfiles, array $rangos): ResultadoFinal
    {
        $total = array_sum(array_map(fn ($d) => $d->bruto, $detalle));
        $rango = $this->buscarRango($rangos, null, $total);

        return new ResultadoFinal(
            resumen: 'Total: ' . $total . ($rango ? " - {$rango['nivel']}" : ' - sin rango'),
            interpretacion: $rango['texto_interpretacion'] ?? null,
        );
    }
}
