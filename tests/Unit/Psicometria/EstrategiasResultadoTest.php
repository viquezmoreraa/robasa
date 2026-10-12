<?php

namespace Tests\Unit\Psicometria;

use App\Services\Psicometria\Estrategias\Combinacion;
use App\Services\Psicometria\Estrategias\DimensionMayor;
use App\Services\Psicometria\Estrategias\PuntajePorDimension;
use App\Services\Psicometria\Estrategias\TotalCorrectas;
use App\Services\Psicometria\ResultadoDimension;
use PHPUnit\Framework\TestCase;

/** Pruebas con datos INVENTADOS y mínimos; no usan la base de datos. */
class EstrategiasResultadoTest extends TestCase
{
    private function dim(int $id, string $codigo, float $bruto, ?float $max = null): ResultadoDimension
    {
        return new ResultadoDimension($id, $codigo, "Dim {$codigo}", $bruto, $max);
    }

    private function perfiles(string ...$codigos): array
    {
        $out = [];
        foreach ($codigos as $i => $c) {
            $out[$c] = ['id' => $i + 1, 'texto_interpretacion' => "Texto {$c}"];
        }

        return $out;
    }

    public function test_dimension_mayor_elige_la_de_mayor_puntaje(): void
    {
        $r = (new DimensionMayor())->interpretar(
            [$this->dim(1, 'A', 5), $this->dim(2, 'B', 9), $this->dim(3, 'C', 2)],
            $this->perfiles('A', 'B', 'C'),
            []
        );

        $this->assertSame('Dim B', $r->resumen);
        $this->assertSame(2, $r->perfilId);
        $this->assertFalse($r->empate);
    }

    public function test_dimension_mayor_reporta_empate_en_lugar_de_elegir(): void
    {
        $r = (new DimensionMayor())->interpretar(
            [$this->dim(1, 'A', 9), $this->dim(2, 'B', 9), $this->dim(3, 'C', 2)],
            $this->perfiles('A', 'B', 'C'),
            []
        );

        $this->assertTrue($r->empate);
        $this->assertNull($r->perfilId);
    }

    public function test_combinacion_forma_el_codigo_con_las_dos_mas_altas(): void
    {
        $r = (new Combinacion(2))->interpretar(
            [$this->dim(1, 'D', 10), $this->dim(2, 'I', 8), $this->dim(3, 'S', 3), $this->dim(4, 'C', 1)],
            $this->perfiles('DI'),
            []
        );

        $this->assertSame(1, $r->perfilId);
        $this->assertFalse($r->empate);
    }

    public function test_combinacion_detecta_empate_en_el_limite(): void
    {
        $r = (new Combinacion(2))->interpretar(
            [$this->dim(1, 'D', 10), $this->dim(2, 'I', 8), $this->dim(3, 'S', 8), $this->dim(4, 'C', 1)],
            $this->perfiles('DI', 'DS'),
            []
        );

        $this->assertTrue($r->empate);
        $this->assertNull($r->perfilId);
    }

    public function test_rangos_incluyen_los_bordes(): void
    {
        $rangos = [
            ['id' => 1, 'dimension_id' => 1, 'nivel' => 'Bajo',  'puntaje_min' => 0,  'puntaje_max' => 9,  'texto_interpretacion' => 'x'],
            ['id' => 2, 'dimension_id' => 1, 'nivel' => 'Medio', 'puntaje_min' => 10, 'puntaje_max' => 19, 'texto_interpretacion' => 'y'],
        ];
        $est = new PuntajePorDimension();

        $this->assertSame([1 => 1], $est->interpretar([$this->dim(1, 'A', 9)], [], $rangos)->rangoPorDimension);
        $this->assertSame([1 => 2], $est->interpretar([$this->dim(1, 'A', 10)], [], $rangos)->rangoPorDimension);
    }

    public function test_dimension_sin_rango_no_se_oculta(): void
    {
        $r = (new PuntajePorDimension())->interpretar([$this->dim(1, 'A', 50)], [], []);

        $this->assertStringContainsString('sin rango', $r->resumen);
    }

    public function test_total_correctas_suma_todas_las_dimensiones(): void
    {
        $rangos = [
            ['id' => 1, 'dimension_id' => null, 'nivel' => 'Alto', 'puntaje_min' => 5, 'puntaje_max' => 10, 'texto_interpretacion' => 'z'],
        ];

        $r = (new TotalCorrectas())->interpretar([$this->dim(1, 'A', 3), $this->dim(2, 'B', 4)], [], $rangos);

        $this->assertSame('Total: 7 - Alto', $r->resumen);
    }

    public function test_porcentaje_y_maximo_nulo(): void
    {
        $this->assertSame(75.0, $this->dim(1, 'A', 15, 20)->porcentaje());
        $this->assertNull($this->dim(1, 'A', 15, null)->porcentaje());
    }
}
