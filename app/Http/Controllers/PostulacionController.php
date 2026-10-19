<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistroRequest;
use App\Models\Postulacion;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PostulacionController extends Controller
{
    public function create()
    {
        return view('auth.registro');
    }

    public function store(RegistroRequest $request)
    {
        $datos = $request->validated();
        $correo = $datos['nuevo_correo'];

        $fechaNacimiento = null;
        if (isset($datos['dia'], $datos['mes'], $datos['anio']) && checkdate($datos['mes'], $datos['dia'], $datos['anio'])) {
            $fechaNacimiento = sprintf('%04d-%02d-%02d', $datos['anio'], $datos['mes'], $datos['dia']);
        }
        $siNo = fn ($campo) => isset($datos[$campo]) ? $datos[$campo] === 'si' : null;

        // Única vía de alta: siempre crea un usuario nuevo con rol postulante (correo y usuario únicos)
        $user = DB::transaction(function () use ($datos, $correo, $fechaNacimiento, $siNo) {
            $user = User::create([
                'name'     => $datos['nombre'] ?? $datos['username'],
                'username' => $datos['username'],
                'email'    => $correo,
                'password' => Hash::make($datos['password']),
                'rol'      => 'postulante',
            ]);

            Postulacion::create([
                'user_id'              => $user->id,
                'acepto_terminos_at'   => now(),
                'nombre'               => $datos['nombre'] ?? null,
                'cedula'               => $datos['cedula'] ?? null,
                'fecha_nacimiento'     => $fechaNacimiento,
                'telefono'             => $datos['telefono'] ?? null,
                'correo_contacto'      => $datos['correo'] ?? null,
                'direccion'            => $datos['direccion'] ?? null,
                'tiene_licencia'       => $siNo('tiene_licencia'),
                'tipo_licencia'        => $datos['tipo_licencia'] ?? null,
                'tiene_vehiculo'       => $datos['tiene_vehiculo'] ?? null,
                'puesto'               => $datos['puesto'] ?? null,
                'salario'              => $datos['salario'] ?? null,
                'zona'                 => $datos['zona'] ?? null,
                'disponibilidad'       => $datos['disponibilidad'] ?? null,
                'tiene_experiencia'    => $siNo('tiene_experiencia'),
                'detalles_experiencia' => $datos['detalles_experiencia'] ?? null,
                'nivel_educativo'      => $datos['nivel_educativo'] ?? null,
                'empleos_anteriores'   => $datos['empleos_anteriores'] ?? null,
                'correo'               => $correo,
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/exito.html?nuevo_correo='.urlencode($correo));
    }
}
