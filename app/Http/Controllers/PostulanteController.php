<?php

namespace App\Http\Controllers;

use App\Models\AsignacionPrueba;
use App\Services\Psicometria\MotorPsicometrico;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostulanteController extends Controller
{
    public function index(Request $request)
    {
        $postulacion = $request->user()->postulacion;
        $tareas = $postulacion
            ? $postulacion->asignaciones()->with('prueba')->where('estado', '!=', 'ANULADA')->orderByDesc('fecha_asignacion')->get()
            : collect();

        return view('postulante.index', compact('postulacion', 'tareas'));
    }

    public function entregar(Request $request, AsignacionPrueba $asignacion)
    {
        abort_unless($asignacion->postulacion?->user_id == $request->user()->id, 403);
        abort_if($asignacion->completada(), 422, 'Esta tarea ya fue entregada.');

        $datos = $request->validate([
            'archivo'         => 'required|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,xls,xlsx',
            'respuesta_texto' => 'nullable|string|max:5000',
        ]);

        $asignacion->update([
            'archivo_path'    => $request->file('archivo')->store('entregas'),
            'respuesta_texto' => $datos['respuesta_texto'] ?? null,
            'estado'          => 'COMPLETADA',
            'fecha_fin'       => now(),
        ]);

        return back()->with('ok', 'Tarea entregada correctamente.');
    }

    /** Muestra las preguntas de una prueba psicométrica digitalizada para responderla en línea. */
    public function mostrarPrueba(Request $request, AsignacionPrueba $asignacion)
    {
        abort_unless($asignacion->postulacion?->user_id == $request->user()->id, 403);
        abort_if($asignacion->completada(), 422, 'Esta prueba ya fue entregada.');

        $prueba = $asignacion->prueba;
        abort_unless($prueba->tienePreguntas(), 404);

        $preguntas = DB::table('preguntas')->where('prueba_id', $prueba->id)->orderBy('numero')->get();
        $opciones = DB::table('opciones')
            ->whereIn('pregunta_id', $preguntas->pluck('id'))
            ->orderBy('orden')
            ->get()
            ->groupBy('pregunta_id');

        // Respuestas ya guardadas, por si recarga la página a mitad de la prueba.
        $respuestasPrevias = DB::table('respuestas')
            ->where('asignacion_id', $asignacion->id)
            ->get()
            ->groupBy('pregunta_id');

        if ($asignacion->estado === 'ASIGNADA') {
            DB::table('asignaciones_prueba')->where('id', $asignacion->id)->update([
                'estado'       => 'EN_CURSO',
                'fecha_inicio' => now(),
            ]);
            $asignacion->refresh();
        }

        return view('postulante.prueba', compact('asignacion', 'prueba', 'preguntas', 'opciones', 'respuestasPrevias'));
    }

    /** Guarda las respuestas CRUDAS en `respuestas` y dispara MotorPsicometrico::calcular(). */
    public function guardarPrueba(Request $request, AsignacionPrueba $asignacion)
    {
        abort_unless($asignacion->postulacion?->user_id == $request->user()->id, 403);
        abort_if($asignacion->completada(), 422, 'Esta prueba ya fue entregada.');

        $preguntas = DB::table('preguntas')->where('prueba_id', $asignacion->prueba_id)->get();
        abort_unless($preguntas->isNotEmpty(), 404);

        // Opciones válidas por pregunta: nunca se confía en los ids que llegan del formulario.
        $opcionesValidas = DB::table('opciones')
            ->whereIn('pregunta_id', $preguntas->pluck('id'))
            ->get()
            ->groupBy('pregunta_id')
            ->map(fn ($grupo) => $grupo->pluck('id')->all());

        $filas = [];
        foreach ($preguntas as $pregunta) {
            $validas = $opcionesValidas->get($pregunta->id, []);

            if ($pregunta->tipo === 'MAS_MENOS') {
                $mas = $request->input("respuestas.{$pregunta->id}.mas");
                $menos = $request->input("respuestas.{$pregunta->id}.menos");

                if ($pregunta->obligatoria && (!$mas || !$menos || $mas === $menos)) {
                    return back()->withErrors([
                        'general' => "Falta marcar \"más\" y \"menos\" (distintas) en la pregunta {$pregunta->numero}.",
                    ])->withInput();
                }
                if ($mas && in_array((int) $mas, $validas, true)) {
                    $filas[] = ['asignacion_id' => $asignacion->id, 'pregunta_id' => $pregunta->id, 'opcion_id' => $mas, 'seleccion' => 'MAS', 'respondida_en' => now()];
                }
                if ($menos && in_array((int) $menos, $validas, true)) {
                    $filas[] = ['asignacion_id' => $asignacion->id, 'pregunta_id' => $pregunta->id, 'opcion_id' => $menos, 'seleccion' => 'MENOS', 'respondida_en' => now()];
                }
            } else {
                $opcionId = $request->input("respuestas.{$pregunta->id}");

                if ($pregunta->obligatoria && !$opcionId) {
                    return back()->withErrors([
                        'general' => "Falta responder la pregunta {$pregunta->numero}.",
                    ])->withInput();
                }
                if ($opcionId && in_array((int) $opcionId, $validas, true)) {
                    $filas[] = ['asignacion_id' => $asignacion->id, 'pregunta_id' => $pregunta->id, 'opcion_id' => $opcionId, 'seleccion' => 'UNICA', 'respondida_en' => now()];
                }
            }
        }

        DB::transaction(function () use ($asignacion, $filas) {
            // Reemplazar, no acumular: permite recalcular si el postulante corrige y reenvía.
            DB::table('respuestas')->where('asignacion_id', $asignacion->id)->delete();
            if ($filas !== []) {
                DB::table('respuestas')->insert($filas);
            }
        });

        try {
            app(MotorPsicometrico::class)->calcular($asignacion->id);
        } catch (DomainException $e) {
            return back()->withErrors(['general' => $e->getMessage()])->withInput();
        }

        return redirect()->route('postulante.index')->with('ok', 'Prueba entregada correctamente.');
    }
}
