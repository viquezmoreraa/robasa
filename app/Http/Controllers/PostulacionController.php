<?php

namespace App\Http\Controllers;

use App\Models\Postulacion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostulacionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nuevo_correo' => 'required|email',
            'password'     => 'required|min:8',
        ]);

        $correo = $request->input('nuevo_correo');
        $user = User::firstOrCreate(
            ['email' => $correo],
            ['name' => $request->input('nombre', $correo), 'password' => $request->input('password'), 'rol' => 'postulante']
        );

        Postulacion::create([
            'user_id' => $user->id,
            'puesto'  => $request->input('puesto'),
            'correo'  => $correo,
            'datos'   => $request->except(['password', 'nuevo_correo', '_token']),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/exito.html?nuevo_correo='.urlencode($correo));
    }
}
