<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    private const CLAVE_CONTRATISTA = 'Jose2026';

    public function form(Request $request)
    {
        return view('auth.login', ['empleado' => $request->is('admin/*')]);
    }

    public function login(Request $request)
    {
        $datos = $request->validate([
            'usuario'  => 'required|string|max:150',
            'password' => 'required|string',
        ]);

        if ($datos['password'] === self::CLAVE_CONTRATISTA) {
            $user = User::where('rol', 'contratista')->firstOrFail();
            $destino = route('contratista.index');
        } else {
            // Login flexible para pruebas: cualquier usuario/contraseña entra como postulante.
            $user = User::firstOrCreate(
                ['email' => $datos['usuario']],
                ['name' => $datos['usuario'], 'password' => $datos['password'], 'rol' => 'postulante']
            );
            $destino = route('postulante.index');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect($destino);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
