<?php

namespace App\Services\Psicometria;

/** Lo que decide una estrategia: perfil, texto y rango por dimensión. */
final class ResultadoFinal
{
    /**
     * @param array<int,int> $rangoPorDimension  dimension_id => rango_id
     */
    public function __construct(
        public readonly string $resumen,
        public readonly ?int $perfilId = null,
        public readonly ?string $interpretacion = null,
        public readonly array $rangoPorDimension = [],
        public readonly bool $empate = false,
    ) {
    }
}
