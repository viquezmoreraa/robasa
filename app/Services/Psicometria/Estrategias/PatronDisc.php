<?php

namespace App\Services\Psicometria\Estrategias;

use App\Services\Psicometria\ResultadoFinal;
use DomainException;

/**
 * Clasificación DISC de 15 patrones clásicos. Recibe el puntaje NETO (MAS
 * menos MENOS) de las cuatro dimensiones D, I, S y C -ya calculado por el
 * motor genérico, usando el signo de 'respuestas.seleccion'- y:
 *  1) convierte cada neto en una posición de 1 a 7 según el eje (los cuatro
 *     ejes usan tablas de corte DISTINTAS entre sí: no son intercambiables),
 *  2) concatena las 4 posiciones en el orden D-I-S-C para formar el código,
 *  3) busca ese código en la tabla de clasificación para obtener el perfil.
 *
 * Es lógica PURA: no toca la base de datos (igual que las demás
 * estrategias), lo que permite probarla sin MySQL.
 *
 * Si el código no tiene perfil asignado (ver migración y seeder: unos 345 de
 * los 2401 códigos posibles caen en patrones que el material original de
 * Talento Humano no numeró), NO se inventa un resultado: se informa.
 */
final class PatronDisc implements EstrategiaResultado
{
    /** @param array<string,string|null> $clasificacion código de 4 posiciones => código de perfil (o null) */
    public function __construct(private readonly array $clasificacion)
    {
    }

    public function interpretar(array $detalle, array $perfiles, array $rangos): ResultadoFinal
    {
        $neto = [];
        foreach ($detalle as $d) {
            $neto[$d->codigo] = (int) round($d->bruto);
        }
        foreach (['D', 'I', 'S', 'C'] as $eje) {
            if (!array_key_exists($eje, $neto)) {
                throw new DomainException("Falta la dimensión {$eje} en el detalle calculado.");
            }
        }

        $codigo = $this->posicionD($neto['D'])
            . $this->posicionI($neto['I'])
            . $this->posicionS($neto['S'])
            . $this->posicionC($neto['C']);

        $perfilCodigo = $this->clasificacion[$codigo] ?? null;

        if ($perfilCodigo === null) {
            return new ResultadoFinal(
                resumen: "Código {$codigo}: sin perfil asignado en el material original (revisar con Talento Humano)",
            );
        }

        $perfil = $perfiles[$perfilCodigo] ?? null;
        if ($perfil === null) {
            throw new DomainException("La clasificación apunta al perfil '{$perfilCodigo}', que no está cargado.");
        }

        return new ResultadoFinal(
            resumen: "Código {$codigo}: {$perfil['nombre']}",
            perfilId: (int) $perfil['id'],
            interpretacion: $perfil['texto_interpretacion'],
        );
    }

    // Umbrales extraídos literalmente de las fórmulas del Excel (distintos por eje,
    // y forman una partición completa de los enteros: no debería faltar ningún caso).
    private function posicionD(int $n): int
    {
        return match (true) {
            $n < -7 => 1, $n > -8 && $n < -3 => 2, $n > -4 && $n < 0 => 3,
            $n > -1 && $n < 2 => 4, $n > 1 && $n < 5 => 5, $n > 4 && $n < 9 => 6,
            $n > 8 => 7,
            default => throw new DomainException("Puntaje neto D fuera de rango: {$n}"),
        };
    }

    private function posicionI(int $n): int
    {
        return match (true) {
            $n < -7 => 1, $n > -8 && $n < -3 => 2, $n > -4 && $n < -1 => 3,
            $n > -2 && $n < 2 => 4, $n > 1 && $n < 4 => 5, $n > 3 && $n < 7 => 6,
            $n > 6 => 7,
            default => throw new DomainException("Puntaje neto I fuera de rango: {$n}"),
        };
    }

    private function posicionS(int $n): int
    {
        return match (true) {
            $n < -10 => 1, $n > -11 && $n < -6 => 2, $n > -7 && $n < -3 => 3,
            $n > -4 && $n < 0 => 4, $n > -1 && $n < 3 => 5, $n > 2 && $n < 8 => 6,
            $n > 7 => 7,
            default => throw new DomainException("Puntaje neto S fuera de rango: {$n}"),
        };
    }

    private function posicionC(int $n): int
    {
        return match (true) {
            $n < -5 => 1, $n > -6 && $n < -2 => 2, $n > -3 && $n < 0 => 3,
            $n > -1 && $n < 3 => 4, $n > 2 && $n < 5 => 5, $n > 4 && $n < 9 => 6,
            $n > 8 => 7,
            default => throw new DomainException("Puntaje neto C fuera de rango: {$n}"),
        };
    }
}
