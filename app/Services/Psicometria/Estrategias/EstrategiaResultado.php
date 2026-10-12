<?php

namespace App\Services\Psicometria\Estrategias;

use App\Services\Psicometria\ResultadoDimension;
use App\Services\Psicometria\ResultadoFinal;

interface EstrategiaResultado
{
    /**
     * Lógica PURA: no toca la base de datos, por eso se prueba sin ella.
     *
     * @param ResultadoDimension[] $detalle  puntajes ya sumados, en el orden de la prueba
     * @param array<string,array>  $perfiles perfiles de la prueba, indexados por 'codigo'
     * @param array<int,array>     $rangos   filas de rangos_interpretacion de la prueba
     */
    public function interpretar(array $detalle, array $perfiles, array $rangos): ResultadoFinal;
}
