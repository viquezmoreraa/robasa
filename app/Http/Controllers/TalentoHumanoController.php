<?php

namespace App\Http\Controllers;

use App\Models\AsignacionPrueba;
use App\Models\Postulacion;
use App\Models\Prueba;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TalentoHumanoController extends Controller
{
    public function index()
    {
        $postulaciones = Postulacion::withCount([
            'asignaciones',
            'asignaciones as completadas_count' => fn ($q) => $q->where('estado', 'COMPLETADA'),
        ])->latest()->get();

        return view('talento-humano.index', compact('postulaciones'));
    }

    public function show(Postulacion $postulacion)
    {
        $postulacion->load('asignaciones.prueba');
        $pruebas = Prueba::where('estado', 'APROBADA')->orderBy('nombre')->get();

        $asignacionIds = $postulacion->asignaciones->pluck('id');

        // Resultado final por asignación (lo que calculó MotorPsicometrico), si ya se respondió en línea.
        $resultados = DB::table('resultados_prueba')
            ->whereIn('asignacion_id', $asignacionIds)
            ->get()
            ->keyBy('asignacion_id');

        // Puntaje directo por dimensión: se usa como respaldo cuando la prueba no tiene
        // normas/rangos cargados (p. ej. BARSIT, E.D.) y por tanto `interpretacion` queda null
        // (no se inventa texto). orderBy('id') como segundo criterio: igual que en
        // MotorPsicometrico, 'orden' no es único entre dimensiones.
        $dimensiones = DB::table('resultados_dimension')
            ->join('dimensiones', 'dimensiones.id', '=', 'resultados_dimension.dimension_id')
            ->whereIn('resultados_dimension.asignacion_id', $asignacionIds)
            ->select('resultados_dimension.*', 'dimensiones.nombre as dimension_nombre')
            ->orderBy('dimensiones.orden')
            ->orderBy('dimensiones.id')
            ->get()
            ->groupBy('asignacion_id');

        return view('talento-humano.show', compact('postulacion', 'pruebas', 'resultados', 'dimensiones'));
    }

    public function actualizarEstado(Request $request, Postulacion $postulacion)
    {
        $datos = $request->validate(['estado' => ['required', Rule::in(array_keys(Postulacion::ESTADOS))]]);

        // Asignación directa: 'estado' no está en $fillable para que el registro nunca lo reciba del formulario.
        $postulacion->estado = $datos['estado'];
        $postulacion->save();

        return back()->with('ok', "Estado actualizado a «{$postulacion->estadoEtiqueta()}».");
    }

    public function asignar(Request $request, Postulacion $postulacion)
    {
        $datos = $request->validate(['prueba_id' => 'required|exists:pruebas,id']);

        $intento = $postulacion->asignaciones()->where('prueba_id', $datos['prueba_id'])->max('intento') ?? 0;

        $postulacion->asignaciones()->create([
            'prueba_id'        => $datos['prueba_id'],
            'intento'          => $intento + 1,
            'asignada_por'     => $request->user()->id,
            'fecha_asignacion' => now(),
            'estado'           => 'ASIGNADA',
        ]);

        return back()->with('ok', 'Prueba enviada al postulante.');
    }

    public function archivo(AsignacionPrueba $asignacion)
    {
        abort_unless($asignacion->archivo_path && Storage::exists($asignacion->archivo_path), 404);

        return Storage::download($asignacion->archivo_path);
    }
}
