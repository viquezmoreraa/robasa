<?php

namespace App\Http\Controllers;

use App\Models\Postulacion;
use App\Models\Prueba;
use Illuminate\Http\Request;

class ContratistaController extends Controller
{
    public function index()
    {
        $postulaciones = Postulacion::withCount([
            'asignaciones',
            'asignaciones as completadas_count' => fn ($q) => $q->where('estado', 'COMPLETADA'),
        ])->latest()->get();

        return view('contratista.index', compact('postulaciones'));
    }

    public function show(Postulacion $postulacion)
    {
        $postulacion->load('asignaciones.prueba');
        $pruebas = Prueba::where('estado', 'APROBADA')->orderBy('nombre')->get();

        return view('contratista.show', compact('postulacion', 'pruebas'));
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
}
