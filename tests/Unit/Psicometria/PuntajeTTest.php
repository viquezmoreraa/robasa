<?php

namespace Tests\Unit\Psicometria;

use App\Services\Psicometria\Estrategias\PuntajeT;
use App\Services\Psicometria\ResultadoDimension;
use PHPUnit\Framework\TestCase;

/** Datos inventados y mínimos; no usan la base de datos. */
class PuntajeTTest extends TestCase
{
    public function test_convierte_el_puntaje_directo_en_t_con_el_baremo(): void
    {
        // E (id 1) es dimensión principal; Di (id 3) es subescala de E
        $detalle = [
            new ResultadoDimension(1, 'E', 'Energía', 88),
            new ResultadoDimension(3, 'Di', 'Dinamismo', 44, null, 1),
        ];
        $baremos = [1 => [88 => 56], 3 => [44 => 50]];

        $r = (new PuntajeT($baremos))->interpretar($detalle, [], []);

        $this->assertSame([1 => 56, 3 => 50], $r->puntajeTPorDimension);
        $this->assertSame('E: 56', $r->resumen); // la subescala no aparece en el resumen
    }

    public function test_si_el_puntaje_no_esta_en_el_baremo_no_inventa_un_valor(): void
    {
        $detalle = [new ResultadoDimension(1, 'E', 'Energía', 999)];

        $r = (new PuntajeT([1 => [88 => 56]]))->interpretar($detalle, [], []);

        $this->assertSame([], $r->puntajeTPorDimension);
        $this->assertStringContainsString('sin baremo', $r->resumen);
    }

    public function test_el_resumen_lista_las_dimensiones_principales_en_orden(): void
    {
        $detalle = [
            new ResultadoDimension(1, 'E', 'Energía', 88),
            new ResultadoDimension(2, 'A', 'Cordialidad', 93),
        ];

        $r = (new PuntajeT([1 => [88 => 56], 2 => [93 => 53]]))->interpretar($detalle, [], []);

        $this->assertSame('E: 56 | A: 53', $r->resumen);
    }
}
