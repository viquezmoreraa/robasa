<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as ReglaPassword;

/**
 * Recuperación de contraseña con el broker de Laravel: el token es aleatorio (64 caracteres),
 * en password_reset_tokens solo se guarda su hash, vence según auth.passwords.users.expire
 * y se borra al usarse, así que cada enlace sirve una sola vez.
 */
class RecuperarContrasenaController extends Controller
{
    public function solicitud()
    {
        return view('auth.olvide-contrasena');
    }

    public function enviarEnlace(Request $request)
    {
        $request->validate(['email' => ['required', 'string', 'email', 'max:150']], [
            'email.required' => 'Ingrese su correo electrónico.',
            'email.email'    => 'Ingrese un correo electrónico válido.',
        ]);

        // Misma respuesta exista o no la cuenta (o si se pidió otro enlace hace poco),
        // para no revelar qué correos están registrados.
        Password::sendResetLink($request->only('email'));

        return back()->with('ok', 'Si el correo pertenece a una cuenta, le enviamos un enlace para restablecer su contraseña. Revise también la carpeta de spam.');
    }

    public function formulario(Request $request, string $token)
    {
        return view('auth.restablecer-contrasena', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function restablecer(Request $request)
    {
        $request->validate([
            'token'    => ['required', 'string'],
            'email'    => ['required', 'string', 'email', 'max:150'],
            'password' => ['required', 'confirmed', ReglaPassword::min(8)->letters()->numbers()],
        ], [
            'email.required'     => 'Ingrese su correo electrónico.',
            'email.email'        => 'Ingrese un correo electrónico válido.',
            'password.required'  => 'Ingrese la contraseña nueva.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'password.letters'   => 'La contraseña debe incluir al menos una letra.',
            'password.numbers'   => 'La contraseña debe incluir al menos un número.',
        ]);

        $estado = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                // Nuevo remember_token: invalida las cookies de "Recordarme" anteriores
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                // Cierra las sesiones abiertas en otros dispositivos
                if (config('session.driver') === 'database') {
                    DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
                }

                event(new PasswordReset($user));
            }
        );

        if ($estado !== Password::PASSWORD_RESET) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'El enlace no es válido o ya venció. Solicite uno nuevo.']);
        }

        return redirect()->route('login')->with('ok', 'Su contraseña fue actualizada. Ya puede iniciar sesión.');
    }
}
