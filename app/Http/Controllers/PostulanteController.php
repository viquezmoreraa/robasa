<?php

namespace App\Http\Controllers;

use App\Models\AsignacionPrueba;
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

    // Ventana aparte donde el postulante responde la prueba en formato formulario.
    // Por ahora solo muestra la portada; el formulario de preguntas se construirá después.
    public function formulario(Request $request, AsignacionPrueba $asignacion)
    {
        abort_unless($asignacion->postulacion?->user_id == $request->user()->id, 403);
        abort_if($asignacion->estado === 'ANULADA', 404);

        $asignacion->load('prueba');
        $totalPreguntas = DB::table('preguntas')->where('prueba_id', $asignacion->prueba_id)->count();

        return view('postulante.formulario', compact('asignacion', 'totalPreguntas'));
    }
}
