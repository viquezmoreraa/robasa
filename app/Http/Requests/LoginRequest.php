<?php

namespace App\Http\Requests;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    private const MAX_INTENTOS = 5;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'usuario'  => ['required', 'string', 'max:150'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'usuario.required'  => 'Ingrese su usuario o correo electrónico.',
            'password.required' => 'Ingrese su contraseña.',
        ];
    }

    /**
     * Verifica las credenciales contra la tabla users (Auth::attempt compara el hash).
     * El mismo campo acepta el correo o el nombre de usuario: el username no admite "@",
     * así que si lo trae es un correo (también cuentas antiguas con correos como "x@m").
     */
    public function authenticate(): void
    {
        $this->asegurarQueNoEstaBloqueado();

        $usuario = $this->input('usuario');
        $campo = str_contains($usuario, '@') ? 'email' : 'username';

        if (! Auth::attempt([$campo => $usuario, 'password' => $this->input('password')], $this->boolean('recordar'))) {
            RateLimiter::hit($this->claveLimite());

            throw ValidationException::withMessages([
                'usuario' => 'El usuario, correo o contraseña no son correctos.',
            ]);
        }

        RateLimiter::clear($this->claveLimite());
    }

    private function asegurarQueNoEstaBloqueado(): void
    {
        if (! RateLimiter::tooManyAttempts($this->claveLimite(), self::MAX_INTENTOS)) {
            return;
        }

        event(new Lockout($this));

        throw ValidationException::withMessages([
            'usuario' => 'Demasiados intentos. Intente de nuevo en '.RateLimiter::availableIn($this->claveLimite()).' segundos.',
        ]);
    }

    private function claveLimite(): string
    {
        return Str::transliterate(Str::lower($this->input('usuario')).'|'.$this->ip());
    }
}
