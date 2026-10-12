<?php

namespace App\Services\Psicometria;

/** Puntaje de UNA dimensión para UNA asignación (objeto inmutable). */
final class ResultadoDimension
{
    public function __construct(
        public readonly int $dimensionId,
        public readonly string $codigo,
        public readonly string $nombre,
        public readonly float $bruto,
        public readonly ?float $maximo = null,
    ) {
    }

    public function porcentaje(): ?float
    {
        if ($this->maximo === null || $this->maximo <= 0) {
            return null;
        }

        return round($this->bruto / $this->maximo * 100, 2);
    }
}
