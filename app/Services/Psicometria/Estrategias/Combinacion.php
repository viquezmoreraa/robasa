<?php

namespace App\Services\Psicometria\Estrategias;

use App\Services\Psicometria\ResultadoFinal;

/**
 * Toma las $n dimensiones más altas y busca el perfil cuyo 'codigo' sea
 * la concatenación en ese orden (ej.: "DI"). Confirmar con TH el valor de $n.
 */
final class Combinacion implements EstrategiaResultado
{
    use UtilesEstrategia;

    public function __construct(private readonly int $n = 2)
    {
    }

    public function interpretar(array $detalle, array $perfiles, array $rangos): ResultadoFinal
    {
        $orden = $this->ordenarPorPuntaje($detalle);

        // Empate justo en el límite: no se puede saber cuál entra al perfil.
        if (isset($orden[$this->n]) && $this->iguales($orden[$this->n - 1]->bruto, $orden[$this->n]->bruto)) {
            $borde = $orden[$this->n - 1]->bruto;
            $nombres = implode(' / ', array_map(
                fn ($d) => $d->nombre,
                array_filter($orden, fn ($d) => $this->iguales($d->bruto, $borde))
            ));

            return new ResultadoFinal(resumen: "Empate en el límite del perfil: {$nombres}", empate: true);
        }

        $top = array_slice($orden, 0, $this->n);
        $codigo = implode('', array_map(fn ($d) => $d->codigo, $top));
        $perfil = $perfiles[$codigo] ?? null;

        return new ResultadoFinal(
            resumen: implode(' / ', array_map(fn ($d) => $d->nombre, $top)),
            perfilId: $perfil['id'] ?? null,
            interpretacion: $perfil['texto_interpretacion'] ?? null,
        );
    }
}
