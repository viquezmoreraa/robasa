<?php

namespace App\Services\Psicometria;

use App\Services\Psicometria\Estrategias\Combinacion;
use App\Services\Psicometria\Estrategias\DimensionMayor;
use App\Services\Psicometria\Estrategias\EstrategiaResultado;
use App\Services\Psicometria\Estrategias\PatronDisc;
use App\Services\Psicometria\Estrategias\PuntajePorDimension;
use App\Services\Psicometria\Estrategias\PuntajeT;
use App\Services\Psicometria\Estrategias\TotalCorrectas;
use App\Services\Psicometria\Estrategias\DicotomiaCombinada;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Orquesta: valida -> suma -> interpreta -> guarda (en una transacción).
 * Es genérico: nunca pregunta "qué prueba es", solo qué método usa.
 */
final class MotorPsicometrico
{
    /** Súbanla cuando cambien las reglas de cálculo; queda en cada resultado. */
    public const VERSION = '1.2.0';

    public function calcular(int $asignacionId): ResultadoFinal
    {
        $asignacion = DB::table('asignaciones_prueba')->where('id', $asignacionId)->first();
        if (!$asignacion) {
            throw new DomainException('La asignación no existe.');
        }
        if (!in_array($asignacion->estado, ['EN_CURSO', 'COMPLETADA'], true)) {
            throw new DomainException('La asignación no está en un estado calculable.');
        }

        $prueba = DB::table('pruebas')->where('id', $asignacion->prueba_id)->first();
        if ($prueba->estado !== 'APROBADA') {
            throw new DomainException('La prueba no está aprobada por Talento Humano.');
        }

        // El límite de tiempo se controla en el endpoint que RECIBE cada
        // respuesta (servidor), no aquí.
        $this->validarCompletitud($asignacionId, (int) $prueba->id);

        $detalle = $this->detalle($asignacionId, (int) $prueba->id);

        $perfiles = DB::table('perfiles')->where('prueba_id', $prueba->id)->get()
            ->keyBy('codigo')->map(fn ($p) => (array) $p)->all();
        $rangos = DB::table('rangos_interpretacion')->where('prueba_id', $prueba->id)->get()
            ->map(fn ($r) => (array) $r)->all();

        $estrategia = $this->estrategiaPara(
            $prueba->metodo_resultado,
            (int) $prueba->id,
            $asignacion->sexo_baremo ?? null,
        );
        $final = $estrategia->interpretar($detalle, $perfiles, $rangos);

        $this->guardar($asignacionId, $detalle, $final);

        return $final;
    }

    private function validarCompletitud(int $asignacionId, int $pruebaId): void
    {
        // Cuenta cuántas respuestas distintas (UNICA/MAS/MENOS) tiene cada pregunta
        // obligatoria. Una pregunta normal necesita 1; una MAS_MENOS necesita 2
        // (una MAS y una MENOS, de palabras distintas).
        $faltantes = DB::table('preguntas as p')
            ->leftJoin('respuestas as r', function ($join) use ($asignacionId) {
                $join->on('r.pregunta_id', '=', 'p.id')->where('r.asignacion_id', '=', $asignacionId);
            })
            ->where('p.prueba_id', $pruebaId)
            ->where('p.obligatoria', true)
            ->select('p.id', 'p.tipo')
            ->groupBy('p.id', 'p.tipo')
            // CASE WHEN en vez de IF(...): IF() es solo MySQL y rompía esto bajo
            // SQLite (BD de phpunit). CASE WHEN es SQL estándar y funciona igual
            // en ambos motores.
            ->havingRaw("COUNT(r.id) < CASE WHEN p.tipo = 'MAS_MENOS' THEN 2 ELSE 1 END")
            ->count();

        if ($faltantes > 0) {
            throw new DomainException("Faltan {$faltantes} preguntas obligatorias por responder.");
        }
    }

    /** @return ResultadoDimension[] */
    private function detalle(int $asignacionId, int $pruebaId): array
    {
        // orderBy('id') como segundo criterio: 'orden' no es único entre sí (p. ej.
        // Keirsey deja el default en todas las filas), y sin un segundo criterio
        // el orden de los empates no está garantizado — MySQL y SQLite pueden
        // devolverlos distinto (esto se detectó corriendo este mismo cálculo bajo
        // SQLite en los tests). orderBy('id') reproduce el orden de inserción,
        // que es el que las estrategias como DicotomiaCombinada necesitan.
        $dimensiones = DB::table('dimensiones')->where('prueba_id', $pruebaId)->orderBy('orden')->orderBy('id')->get();

        // Puntaje neto por dimensión: una respuesta 'MENOS' resta en vez de sumar.
        // Para UNICA (y ESCALA) el signo siempre es +1, igual que antes.
        $brutos = DB::table('respuestas as r')
            ->join('opcion_dimension as od', 'od.opcion_id', '=', 'r.opcion_id')
            ->where('r.asignacion_id', $asignacionId)
            ->groupBy('od.dimension_id')
            // CASE WHEN en vez de IF(...): igual que arriba, IF() es solo MySQL.
            ->selectRaw("od.dimension_id, SUM(od.puntos * CASE WHEN r.seleccion = 'MENOS' THEN -1 ELSE 1 END) as bruto")
            ->pluck('bruto', 'dimension_id');

        // Máximo posible = suma, por pregunta, del mayor puntaje que alimenta la dimensión.
        // Para preguntas MAS_MENOS esto no es un "máximo" literal (el neto puede ser
        // negativo); se deja igual por compatibilidad con las demás estrategias, que
        // sí lo usan como porcentaje. PatronDisc no lo necesita.
        $maximos = collect(DB::select(
            'SELECT t.dimension_id, SUM(t.mx) AS maximo FROM (
                SELECT od.dimension_id, o.pregunta_id, MAX(od.puntos) AS mx
                  FROM opcion_dimension od
                  JOIN opciones o  ON o.id = od.opcion_id
                  JOIN preguntas p ON p.id = o.pregunta_id
                 WHERE p.prueba_id = ?
                 GROUP BY od.dimension_id, o.pregunta_id
             ) t GROUP BY t.dimension_id',
            [$pruebaId]
        ))->pluck('maximo', 'dimension_id');

        return $dimensiones->map(fn ($d) => new ResultadoDimension(
            dimensionId: (int) $d->id,
            codigo: $d->codigo,
            nombre: $d->nombre,
            bruto: (float) $brutos->get($d->id, 0),
            maximo: $maximos->has($d->id) ? (float) $maximos->get($d->id) : null,
            padreId: ($d->dimension_padre_id ?? null) !== null ? (int) $d->dimension_padre_id : null,
        ))->all();
    }

    private function estrategiaPara(string $metodo, int $pruebaId, ?string $sexo): EstrategiaResultado
    {
        return match ($metodo) {
            'DIMENSION_MAYOR'       => new DimensionMayor(),
            'COMBINACION'           => new Combinacion(2),
            'PUNTAJE_POR_DIMENSION' => new PuntajePorDimension(),
            'TOTAL_CORRECTAS'       => new TotalCorrectas(),
            'PUNTAJE_T'             => new PuntajeT($this->cargarBaremos($pruebaId, $sexo)),
            'PATRON_DISC'           => new PatronDisc($this->cargarClasificacionDisc($pruebaId)),
            'DICOTOMIA_COMBINADA' => new DicotomiaCombinada(),
            default => throw new DomainException("Método de resultado desconocido: {$metodo}"),
        };
    }

    /** @return array<int,array<int,int>> dimension_id => [puntaje_directo => T] */
    private function cargarBaremos(int $pruebaId, ?string $sexo): array
    {
        if (!in_array($sexo, ['H', 'M'], true)) {
            return []; // otras estrategias (p. ej. PATRON_DISC) no usan baremos por sexo
        }

        $baremos = [];
        $filas = DB::table('baremos')
            ->where('prueba_id', $pruebaId)
            ->where('sexo', $sexo)
            ->get(['dimension_id', 'puntaje_directo', 'puntaje_t']);

        foreach ($filas as $f) {
            $baremos[(int) $f->dimension_id][(int) $f->puntaje_directo] = (int) $f->puntaje_t;
        }

        return $baremos;
    }

    /** @return array<string,string|null> código de 4 posiciones => código de perfil (o null) */
    private function cargarClasificacionDisc(int $pruebaId): array
{
    return DB::table('disc_clasificacion')
        ->join('perfiles', 'perfiles.id', '=', 'disc_clasificacion.perfil_id')
        ->where('disc_clasificacion.prueba_id', $pruebaId)
        ->select('disc_clasificacion.codigo as codigo_disc', 'perfiles.codigo as codigo_perfil')
        ->pluck('codigo_perfil', 'codigo_disc')
        ->all();
}

    /** @param ResultadoDimension[] $detalle */
    private function guardar(int $asignacionId, array $detalle, ResultadoFinal $final): void
    {
        DB::transaction(function () use ($asignacionId, $detalle, $final) {
            // Reemplazar, no insertar: calcular dos veces da lo mismo (idempotente).
            DB::table('resultados_dimension')->where('asignacion_id', $asignacionId)->delete();

            DB::table('resultados_dimension')->insert(array_map(fn (ResultadoDimension $d) => [
                'asignacion_id'  => $asignacionId,
                'dimension_id'   => $d->dimensionId,
                'puntaje_bruto'  => $d->bruto,
                'puntaje_maximo' => $d->maximo,
                'porcentaje'     => $d->porcentaje(),
                'puntaje_t'      => $final->puntajeTPorDimension[$d->dimensionId] ?? null,
                'rango_id'       => $final->rangoPorDimension[$d->dimensionId] ?? null,
            ], $detalle));

            DB::table('resultados_prueba')->upsert([[
                'asignacion_id'  => $asignacionId,
                'perfil_id'      => $final->perfilId,
                'resumen'        => $final->resumen,
                'interpretacion' => $final->interpretacion, // copia del texto vigente (snapshot)
                'version_motor'  => self::VERSION,
                'calculado_en'   => now(),
            ]], ['asignacion_id'], ['perfil_id', 'resumen', 'interpretacion', 'version_motor', 'calculado_en']);

            // COALESCE(fecha_fin, NOW()) solo existe en MySQL; se resuelve en PHP
            // para que calcular() también funcione bajo SQLite (BD de phpunit).
            $fechaFinActual = DB::table('asignaciones_prueba')->where('id', $asignacionId)->value('fecha_fin');
            DB::table('asignaciones_prueba')->where('id', $asignacionId)->update([
                'estado'    => 'COMPLETADA',
                'fecha_fin' => $fechaFinActual ?? now(),
            ]);
        });
    }
}
