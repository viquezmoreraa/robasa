<?php

namespace App\Services\Psicometria\Estrategias;

use App\Services\Psicometria\ResultadoFinal;

/**
 * Para pruebas tipo Keirsey: N dicotomías independientes (pares de
 * dimensiones hijas que comparten un mismo dimension_padre_id, p.ej.
 * E/I, S/N, T/F, J/P). Cada hija representa un lado de su par; dentro de
 * cada par, la de mayor puntaje bruto "gana" esa letra. Las letras
 * ganadoras se concatenan, EN EL ORDEN EN QUE APARECEN LOS GRUPOS en
 * $detalle (es decir, en el orden en que el motor entrega las
 * dimensiones), para formar un código (p.ej. "ESTJ") que se busca en
 * $perfiles para obtener el perfil final.
 *
 * Las filas "padre" (dimension_padre_id === null) no participan en el
 * puntaje: existen solo como agrupador y se ignoran aquí, igual que ya
 * hace PuntajeT para decidir qué mostrar en el resumen.
 *
 * Un empate dentro de un par (ambos lados con el mismo puntaje) se marca
 * con $empate = true y, para ese par, se usa el primer lado encontrado
 * (orden de aparición) solo para poder construir un código completo;
 * quien lea el resultado debe revisar `empate` antes de confiar en el
 * código cuando esto ocurre.
 */
final class DicotomiaCombinada implements EstrategiaResultado
{
    use UtilesEstrategia;

    public function interpretar(array $detalle, array $perfiles, array $rangos): ResultadoFinal
    {
        // Agrupa las dimensiones hijas por su padre, respetando el orden
        // en que aparecen.
        $grupos = [];
        foreach ($detalle as $d) {
            if ($d->padreId === null) {
                continue; // fila padre, no participa en el puntaje
            }
            $grupos[$d->padreId][] = $d;
        }

        $codigo = '';
        $partes = [];
        $empate = false;

        foreach ($grupos as $lados) {
            if (count($lados) !== 2) {
                // Estructura inesperada (no es un par): se ignora este
                // grupo en vez de inventar una letra ganadora.
                continue;
            }

            [$a, $b] = $lados;

            if ($this->iguales($a->bruto, $b->bruto)) {
                $empate = true;
                $ganador = $a; // se usa el primero solo para completar el código
            } else {
                $ganador = $a->bruto > $b->bruto ? $a : $b;
            }

            $codigo .= $ganador->codigo;
            $partes[] = "{$a->codigo}:{$a->bruto} / {$b->codigo}:{$b->bruto} -> {$ganador->codigo}";
        }

        $perfil = $perfiles[$codigo] ?? null;

        return new ResultadoFinal(
            resumen: $codigo . ($empate ? ' (con empate en algún par)' : ''),
            interpretacion: $perfil['texto_interpretacion'] ?? null,
            perfilId: $perfil['id'] ?? null,
            empate: $empate,
        );
    }
}