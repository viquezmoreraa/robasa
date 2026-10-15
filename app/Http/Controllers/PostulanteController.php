<?php

namespace App\Http\Controllers;

use App\Models\AsignacionPrueba;
use Illuminate\Http\Request;

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
}
