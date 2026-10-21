<?php

namespace Tests\Unit\Psicometria;

use App\Services\Psicometria\Estrategias\DicotomiaCombinada;
use App\Services\Psicometria\ResultadoDimension;
use PHPUnit\Framework\TestCase;

/** Pruebas con datos INVENTADOS y mínimos; no usan la base de datos. */
class DicotomiaCombinadaTest extends TestCase
{
    /**
     * @param array<string,array<string,float>> $pares  'EI' => ['E' => 5, 'I' => 4], ...
     * @return ResultadoDimension[]
     */
    private function detalle(array $pares): array
    {
        $detalle = [];
        $id = 1;
        foreach ($pares as $padreCodigo => $lados) {
            $padreId = $id++;
            $detalle[] = new ResultadoDimension($padreId, $padreCodigo, $padreCodigo, 0);
            foreach ($lados as $codigo => $bruto) {
                $detalle[] = new ResultadoDimension($id++, $codigo, $codigo, (float) $bruto, null, $padreId);
            }
        }

        return $detalle;
    }

    public function test_reproduce_el_caso_verificado_de_keirsey_estj(): void
    {
        // E=5,I=4, S=12,N=8, T=13,F=5, J=14,P=9 -> ESTJ (mismo caso que VerificarKeirsey).
        $detalle = $this->detalle([
            'EI' => ['E' => 5, 'I' => 4],
            'SN' => ['S' => 12, 'N' => 8],
            'TF' => ['T' => 13, 'F' => 5],
            'JP' => ['J' => 14, 'P' => 9],
        ]);
        $perfiles = ['ESTJ' => ['id' => 20, 'texto_interpretacion' => 'El Supervisor...']];

        $r = (new DicotomiaCombinada())->interpretar($detalle, $perfiles, []);

        $this->assertSame('ESTJ', $r->resumen);
        $this->assertSame(20, $r->perfilId);
        $this->assertFalse($r->empate);
    }

    public function test_empate_en_un_par_se_marca_pero_igual_arma_un_codigo(): void
    {
        $detalle = $this->detalle([
            'EI' => ['E' => 5, 'I' => 5], // empate: se usa E solo para completar el código
            'SN' => ['S' => 12, 'N' => 8],
        ]);

        $r = (new DicotomiaCombinada())->interpretar($detalle, [], []);

        $this->assertTrue($r->empate);
        $this->assertStringContainsString('ES', $r->resumen);
        $this->assertStringContainsString('empate', $r->resumen);
    }

    public function test_codigo_sin_perfil_asignado_no_inventa_un_resultado(): void
    {
        $detalle = $this->detalle([
            'EI' => ['E' => 1, 'I' => 0],
            'SN' => ['S' => 1, 'N' => 0],
        ]);

        $r = (new DicotomiaCombinada())->interpretar($detalle, [], []);

        $this->assertSame('ES', $r->resumen);
        $this->assertNull($r->perfilId);
        $this->assertNull($r->interpretacion);
    }

    public function test_un_grupo_con_mas_o_menos_de_dos_lados_se_ignora_en_vez_de_inventar_una_letra(): void
    {
        // Un padre con 3 hijas (estructura inesperada): ese grupo se ignora.
        $detalle = [
            new ResultadoDimension(1, 'EI', 'EI', 0),
            new ResultadoDimension(2, 'E', 'E', 5, null, 1),
            new ResultadoDimension(3, 'I', 'I', 4, null, 1),
            new ResultadoDimension(4, 'X', 'X', 1, null, 1),
        ];

        $r = (new DicotomiaCombinada())->interpretar($detalle, [], []);

        $this->assertSame('', $r->resumen);
        $this->assertNull($r->perfilId);
    }
}
