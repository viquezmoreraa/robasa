<?php

namespace App\Http\Controllers;

use App\Models\AsignacionPrueba;
use App\Models\Postulacion;
use App\Models\Prueba;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as ReglaPassword;

class TalentoHumanoController extends Controller
{
    public function index()
    {
        $postulaciones = Postulacion::withCount([
            'asignaciones',
            'asignaciones as completadas_count' => fn ($q) => $q->where('estado', 'COMPLETADA'),
        ])->latest()->get();

        return view('talento-humano.index', compact('postulaciones'));
    }

    public function show(Postulacion $postulacion)
    {
        $postulacion->load('asignaciones.prueba');
        $pruebas = Prueba::where('estado', 'APROBADA')->orderBy('nombre')->get();

        $asignacionIds = $postulacion->asignaciones->pluck('id');

        // Resultado final por asignación (lo que calculó MotorPsicometrico), si ya se respondió en línea.
        $resultados = DB::table('resultados_prueba')
            ->whereIn('asignacion_id', $asignacionIds)
            ->get()
            ->keyBy('asignacion_id');

        // Puntaje directo por dimensión: se usa como respaldo cuando la prueba no tiene
        // normas/rangos cargados (p. ej. BARSIT, E.D.) y por tanto `interpretacion` queda null
        // (no se inventa texto). orderBy('id') como segundo criterio: igual que en
        // MotorPsicometrico, 'orden' no es único entre dimensiones.
        $dimensiones = DB::table('resultados_dimension')
            ->join('dimensiones', 'dimensiones.id', '=', 'resultados_dimension.dimension_id')
            ->whereIn('resultados_dimension.asignacion_id', $asignacionIds)
            ->select('resultados_dimension.*', 'dimensiones.nombre as dimension_nombre')
            ->orderBy('dimensiones.orden')
            ->orderBy('dimensiones.id')
            ->get()
            ->groupBy('asignacion_id');

        return view('talento-humano.show', compact('postulacion', 'pruebas', 'resultados', 'dimensiones'));
    }

    public function actualizarEstado(Request $request, Postulacion $postulacion)
    {
        $datos = $request->validate(['estado' => ['required', Rule::in(array_keys(Postulacion::ESTADOS))]]);

        // Asignación directa: 'estado' no está en $fillable para que el registro nunca lo reciba del formulario.
        $postulacion->estado = $datos['estado'];
        $postulacion->save();

        $mensaje = "Estado actualizado a «{$postulacion->estadoEtiqueta()}».";

        if ($postulacion->estado === 'PRESELECCIONADO' && $postulacion->wasChanged('estado')
            && $postulacion->asignarVideoExperiencia($request->user())) {
            $mensaje .= ' Se le asignó la tarea del video de experiencia laboral.';
        }

        return back()->with('ok', $mensaje);
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

    /**
     * Talento Humano asigna una contraseña nueva al postulante cuando la olvidó y no puede usar
     * el enlace por correo. Solo aplica a cuentas de postulante, nunca a otras de Talento Humano.
     */
    public function cambiarContrasena(Request $request, Postulacion $postulacion)
    {
        $usuario = $postulacion->user;
        abort_unless($usuario && $usuario->rol === 'postulante', 404);

        $request->validate([
            'password' => ['required', 'confirmed', ReglaPassword::min(8)->letters()->numbers()],
        ], [
            'password.required'  => 'Ingrese la contraseña nueva.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'password.letters'   => 'La contraseña debe incluir al menos una letra.',
            'password.numbers'   => 'La contraseña debe incluir al menos un número.',
        ]);

        // Igual que en la recuperación por correo: nuevo remember_token y se cierran sus sesiones abiertas
        $usuario->forceFill([
            'password'       => Hash::make($request->input('password')),
            'remember_token' => Str::random(60),
        ])->save();

        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))->where('user_id', $usuario->id)->delete();
        }

        // Si tenía un enlace de recuperación pendiente, deja de servir
        DB::table('password_reset_tokens')->where('email', $usuario->email)->delete();

        return back()->with('ok', "Contraseña actualizada. Comuníquela al postulante (usuario: {$usuario->username}).");
    }

    public function archivo(AsignacionPrueba $asignacion)
    {
        abort_unless($asignacion->archivo_path && Storage::exists($asignacion->archivo_path), 404);

        // Los videos se sirven en línea (con soporte de rangos) para reproducirlos y adelantarlos en el panel
        if ($asignacion->prueba->esVideo()) {
            return response()->file(Storage::path($asignacion->archivo_path));
        }

        return Storage::download($asignacion->archivo_path);
    }
}
