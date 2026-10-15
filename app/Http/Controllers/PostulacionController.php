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
        $datos = $request->validate([
            'nuevo_correo'         => 'required|email',
            'password'             => 'required|min:8',
            'puesto'               => 'nullable|string|max:60',
            'nombre'               => 'nullable|string|max:150',
            'cedula'               => 'nullable|string|max:30',
            'dia'                  => 'nullable|integer|between:1,31',
            'mes'                  => 'nullable|integer|between:1,12',
            'anio'                 => 'nullable|integer|min:1900',
            'telefono'             => 'nullable|string|max:20',
            'correo'               => 'nullable|email|max:150',
            'direccion'            => 'nullable|string',
            'tiene_licencia'       => 'nullable|in:si,no',
            'tipo_licencia'        => 'nullable|in:B1,B2,B3,A1,otra',
            'tiene_vehiculo'       => 'nullable|in:carro,moto,ambos,no',
            'salario'              => 'nullable|string|max:30',
            'zona'                 => 'nullable|string|max:40',
            'disponibilidad'       => 'nullable|string|max:30',
            'tiene_experiencia'    => 'nullable|in:si,no',
            'detalles_experiencia' => 'nullable|string',
            'nivel_educativo'      => 'nullable|string|max:40',
            'empleos_anteriores'   => 'nullable|string',
        ]);

        $correo = $datos['nuevo_correo'];
        $user = User::firstOrCreate(
            ['email' => $correo],
            ['name' => $datos['nombre'] ?? $correo, 'password' => $datos['password'], 'rol' => 'postulante']
        );

        $fechaNacimiento = null;
        if (isset($datos['dia'], $datos['mes'], $datos['anio']) && checkdate($datos['mes'], $datos['dia'], $datos['anio'])) {
            $fechaNacimiento = sprintf('%04d-%02d-%02d', $datos['anio'], $datos['mes'], $datos['dia']);
        }
        $siNo = fn ($campo) => isset($datos[$campo]) ? $datos[$campo] === 'si' : null;

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

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/exito.html?nuevo_correo='.urlencode($correo));
    }
}
