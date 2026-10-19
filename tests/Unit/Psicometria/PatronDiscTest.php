<?php

namespace Tests\Unit\Psicometria;

use App\Services\Psicometria\Estrategias\PatronDisc;
use App\Services\Psicometria\ResultadoDimension;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** Datos inventados y mínimos; no usan la base de datos. */
class PatronDiscTest extends TestCase
{
    private function detalle(int $d, int $i, int $s, int $c): array
    {
        return [
            new ResultadoDimension(1, 'D', 'D', (float) $d),
            new ResultadoDimension(2, 'I', 'I', (float) $i),
            new ResultadoDimension(3, 'S', 'S', (float) $s),
            new ResultadoDimension(4, 'C', 'C', (float) $c),
        ];
    }

    public function test_reproduce_el_caso_resuelto_del_excel_codigo_2464_especialista(): void
    {
        // Netos D=-4, I=0, S=4, C=0, tal como quedó en el Excel de ejemplo.
        $estrategia = new PatronDisc(['2464' => '15']);
        $perfiles = ['15' => ['id' => 99, 'nombre' => 'Especialista', 'texto_interpretacion' => 'texto']];

        $r = $estrategia->interpretar($this->detalle(-4, 0, 4, 0), $perfiles, []);

        $this->assertSame(99, $r->perfilId);
        $this->assertStringContainsString('Especialista', $r->resumen);
        $this->assertStringContainsString('2464', $r->resumen);
    }

    public function test_codigo_sin_perfil_asignado_no_inventa_un_resultado(): void
    {
        // Código presente en la clasificación pero con perfil null (p. ej. "Subactivo").
        $estrategia = new PatronDisc(['1111' => null]);

        $r = $estrategia->interpretar($this->detalle(-20, -20, -20, -20), [], []);

        $this->assertNull($r->perfilId);
        $this->assertStringContainsString('sin perfil asignado', $r->resumen);
    }

    public function test_codigo_que_no_esta_en_la_tabla_tampoco_inventa_un_resultado(): void
    {
        $estrategia = new PatronDisc([]); // tabla vacía: ningún código conocido

        $r = $estrategia->interpretar($this->detalle(0, 0, 0, 0), [], []);

        $this->assertNull($r->perfilId);
        $this->assertStringContainsString('sin perfil asignado', $r->resumen);
    }

    #[DataProvider('fronteras')]
    public function test_las_posiciones_no_dejan_huecos_en_los_bordes(int $d, int $i, int $s, int $c, string $codigoEsperado): void
    {
        $estrategia = new PatronDisc([$codigoEsperado => '1']);
        $perfiles = ['1' => ['id' => 1, 'nombre' => 'x', 'texto_interpretacion' => 'x']];

        $r = $estrategia->interpretar($this->detalle($d, $i, $s, $c), $perfiles, []);

        $this->assertSame(1, $r->perfilId, "esperaba el código {$codigoEsperado}");
    }

    public static function fronteras(): array
    {
        return [
            // extremos muy bajos y muy altos de cada eje (posición 1 y 7)
            'todo muy bajo'  => [-99, -99, -99, -99, '1111'],
            'todo muy alto'  => [99, 99, 99, 99, '7777'],
            // el caso real verificado contra el Excel
            'caso del excel' => [-4, 0, 4, 0, '2464'],
        ];
    }
}
