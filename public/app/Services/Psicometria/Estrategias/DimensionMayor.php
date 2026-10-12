<?php

namespace App\Services\Psicometria\Estrategias;

use App\Services\Psicometria\ResultadoFinal;

/** Gana la dimensión con más puntos (estilos, tipos...). Reporta empates. */
final class DimensionMayor implements EstrategiaResultado
{
    use UtilesEstrategia;

    public function interpretar(array $detalle, array $perfiles, array $rangos): ResultadoFinal
    {
        $orden = $this->ordenarPorPuntaje($detalle);
        $mayor = $orden[0]->bruto;

        $empatadas = array_values(array_filter($orden, fn ($d) => $this->iguales($d->bruto, $mayor)));
        if (count($empatadas) > 1) {
            // No se elige al azar: se informa para que TH decida con su criterio.
            $nombres = implode(' / ', array_map(fn ($d) => $d->nombre, $empatadas));

            return new ResultadoFinal(resumen: "Empate: {$nombres}", empate: true);
        }

        $perfil = $perfiles[$orden[0]->codigo] ?? null;

        return new ResultadoFinal(
            resumen: $orden[0]->nombre,
            perfilId: $perfil['id'] ?? null,
            interpretacion: $perfil['texto_interpretacion'] ?? null,
        );
    }
}
