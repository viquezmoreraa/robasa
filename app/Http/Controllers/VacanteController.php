<?php

namespace App\Http\Controllers;

use App\Models\Vacante;
use Illuminate\Http\Request;

class VacanteController extends Controller
{
    /** Plazas por puesto para public/vacantes.html: { "chofer": 2, ... } */
    public function disponibles()
    {
        return response()->json(Vacante::pluck('cantidad', 'puesto'));
    }

    public function index()
    {
        $vacantes = Vacante::orderBy('nombre')->get();

        return view('talento-humano.vacantes', compact('vacantes'));
    }

    public function actualizar(Request $request)
    {
        $datos = $request->validate([
            'cantidad'   => ['required', 'array'],
            'cantidad.*' => ['required', 'integer', 'min:0', 'max:999'],
        ], [
            'cantidad.*.required' => 'Indique la cantidad de plazas de cada puesto.',
            'cantidad.*.integer'  => 'La cantidad de plazas debe ser un número entero.',
            'cantidad.*.min'      => 'La cantidad de plazas no puede ser negativa.',
            'cantidad.*.max'      => 'La cantidad de plazas no puede ser mayor a 999.',
        ]);

        foreach (Vacante::whereIn('id', array_keys($datos['cantidad']))->get() as $vacante) {
            $vacante->update(['cantidad' => $datos['cantidad'][$vacante->id]]);
        }

        return back()->with('ok', 'Plazas de las vacantes actualizadas.');
    }
}
