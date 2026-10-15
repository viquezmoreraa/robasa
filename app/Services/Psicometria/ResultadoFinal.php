<?php

namespace App\Services\Psicometria;

/** Lo que decide una estrategia: perfil, texto, rangos y puntajes T. */
final class ResultadoFinal
{
    /**
     * @param array<int,int> $rangoPorDimension     dimension_id => rango_id
     * @param array<int,int> $puntajeTPorDimension  dimension_id => puntaje T
     */
    public function __construct(
        public readonly string $resumen,
        public readonly ?int $perfilId = null,
        public readonly ?string $interpretacion = null,
        public readonly array $rangoPorDimension = [],
        public readonly bool $empate = false,
        public readonly array $puntajeTPorDimension = [],
    ) {
    }
}
