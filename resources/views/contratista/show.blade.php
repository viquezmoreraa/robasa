@extends('layouts.app')

@section('titulo', $postulacion->nombreCompleto())

@section('contenido')
    <p style="margin-bottom: 14px;"><a href="{{ route('contratista.index') }}">← Volver a candidatos</a></p>
    <h1>{{ $postulacion->nombreCompleto() }}</h1>
    <p class="sub">{{ str_replace('_', ' ', $postulacion->puesto ?? '') }} · {{ $postulacion->correo }}</p>

    <div class="card">
        <h2>Enviar prueba</h2>
        @if ($pruebas->isEmpty())
            <p>No hay pruebas aprobadas. Ejecute <code>php artisan db:seed</code>.</p>
        @else
            <form method="POST" action="{{ route('contratista.asignar', $postulacion) }}">
                @csrf
                <label for="prueba_id">Prueba</label>
                <select id="prueba_id" name="prueba_id" required>
                    @foreach ($pruebas as $prueba)
                        <option value="{{ $prueba->id }}">{{ $prueba->nombre }} (v{{ $prueba->version }})</option>
                    @endforeach
                </select>
                <button class="btn" type="submit">Enviar al postulante</button>
            </form>
        @endif
    </div>

    <div class="card tabla-scroll">
        <h2>Pruebas asignadas</h2>
        @if ($postulacion->asignaciones->isEmpty())
            <p>Aún no se le ha enviado ninguna prueba.</p>
        @else
            <table>
                <thead><tr><th>Prueba</th><th>Intento</th><th>Asignada</th><th>Estado</th><th>Entrega</th></tr></thead>
                <tbody>
                    @foreach ($postulacion->asignaciones as $a)
                        <tr>
                            <td>{{ $a->prueba->nombre }}</td>
                            <td>{{ $a->intento }}</td>
                            <td>{{ $a->fecha_asignacion?->format('d/m/Y H:i') }}</td>
                            <td>
                                <span class="badge {{ $a->completada() ? 'badge-ok' : 'badge-pend' }}">
                                    {{ $a->completada() ? '✓ Completada' : ucfirst(strtolower($a->estado)) }}
                                </span>
                            </td>
                            <td>
                                @if ($a->archivo_path)
                                    <a href="{{ route('contratista.archivo', $a) }}">Descargar archivo</a>
                                @endif
                                @if ($a->respuesta_texto)
                                    <div style="font-size: .85rem; color: #c9d6ea; margin-top: 4px;">{{ $a->respuesta_texto }}</div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="card">
        <h2>Datos de la postulación</h2>
        <dl class="datos">
            @foreach ($postulacion->datos ?? [] as $campo => $valor)
                <div><dt>{{ str_replace('_', ' ', $campo) }}</dt><dd>{{ is_array($valor) ? implode(', ', $valor) : ($valor ?: '—') }}</dd></div>
            @endforeach
        </dl>
    </div>
@endsection
