<?php

namespace App\Http\Requests;

use App\Models\Vacante;
use App\Rules\CorreoProveedorPermitido;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegistroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Datos de la cuenta (crear-cuenta)
            'username'     => ['bail', 'required', 'string', 'min:4', 'max:30', 'alpha_dash:ascii', 'unique:users,username'],
            'nuevo_correo' => ['bail', 'required', 'string', 'max:150', 'email', new CorreoProveedorPermitido, 'unique:users,email'],
            'password'     => ['required', 'confirmed', Password::min(8)->letters()->numbers()],

            // Pasos anteriores del formulario (llegan como campos ocultos)
            'puesto'               => ['nullable', 'string', 'max:60', function ($atributo, $valor, $falla) {
                if (Vacante::sinPlazas($valor)) {
                    $falla('Esta vacante ya no tiene plazas disponibles. Revise las demás vacantes.');
                }
            }],
            'nombre'               => 'nullable|string|max:150',
            // Misma regla que datos-personales.html: nacional de 9 dígitos (con o sin guiones) o DIMEX de 11-12
            'cedula'               => ['nullable', 'string', 'regex:/^([1-9]-?\d{4}-?\d{4}|\d{11,12})$/'],
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
        ];
    }

    public function messages(): array
    {
        return [
            'username.required'     => 'Elija un nombre de usuario.',
            'username.min'          => 'El nombre de usuario debe tener al menos 4 caracteres.',
            'username.max'          => 'El nombre de usuario no puede tener más de 30 caracteres.',
            'username.alpha_dash'   => 'El nombre de usuario solo admite letras sin tildes, números, guion y guion bajo.',
            'username.unique'       => 'Ese nombre de usuario ya está en uso.',
            'nuevo_correo.required' => 'Ingrese su correo electrónico.',
            'nuevo_correo.email'    => 'Ingrese un correo electrónico válido.',
            'nuevo_correo.unique'   => 'Ya existe una cuenta con este correo. Inicie sesión.',
            'password.required'     => 'Cree una contraseña.',
            'password.confirmed'    => 'Las contraseñas no coinciden.',
            'password.min'          => 'La contraseña debe tener al menos 8 caracteres.',
            'password.letters'      => 'La contraseña debe incluir al menos una letra.',
            'password.numbers'      => 'La contraseña debe incluir al menos un número.',
            'cedula.regex'          => 'La cédula debe tener 9 dígitos (ej. 1-0234-0567) o, si es DIMEX, 11 o 12 dígitos.',
        ];
    }
}
