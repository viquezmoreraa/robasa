@extends('layouts.app')

@section('titulo', $postulacion->nombreCompleto())

@section('contenido')
    <p style="margin-bottom: 14px;"><a href="{{ route('talento-humano.index') }}">← Volver a candidatos</a></p>
    <h1>{{ $postulacion->nombreCompleto() }}</h1>
    <p class="sub">{{ str_replace('_', ' ', $postulacion->puesto ?? '') }} · {{ $postulacion->correo }}</p>

    <div class="card">
        <h2>Estado del proceso</h2>
        <p style="margin-bottom: 14px;">
            <span class="badge {{ $postulacion->estadoClase() }}">{{ $postulacion->estadoEtiqueta() }}</span>
            <span style="font-size: .9rem; color: #c9d6ea; margin-left: 8px;">{{ $postulacion->estadoDescripcion() }}</span>
        </p>
        <form method="POST" action="{{ route('talento-humano.estado', $postulacion) }}">
            @csrf
            <label for="estado">Cambiar estado</label>
            <select id="estado" name="estado" required>
                @foreach (\App\Models\Postulacion::ESTADOS as $clave => [$etiqueta])
                    <option value="{{ $clave }}" @selected($postulacion->estado === $clave)>{{ $etiqueta }}</option>
                @endforeach
            </select>
            <button class="btn" type="submit">Guardar estado</button>
        </form>
    </div>

    <div class="card">
        <h2>Enviar prueba</h2>
        @if ($pruebas->isEmpty())
            <p>No hay pruebas aprobadas. Ejecute <code>php artisan db:seed</code>.</p>
        @else
            <form method="POST" action="{{ route('talento-humano.asignar', $postulacion) }}">
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
                                    <a href="{{ route('talento-humano.archivo', $a) }}">Descargar archivo</a>
                                @endif
                                @if ($a->respuesta_texto)
                                    <div style="font-size: .85rem; color: #c9d6ea; margin-top: 4px;">{{ $a->respuesta_texto }}</div>
                                @endif
                                @if ($resultados->has($a->id))
                                    @php $r = $resultados->get($a->id); @endphp
                                    <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid rgba(255,255,255,.1);">
                                        <strong>{{ $r->resumen }}</strong>
                                        @if ($r->interpretacion)
                                            <div style="font-size: .85rem; color: #c9d6ea; margin-top: 4px; white-space: pre-line;">{{ $r->interpretacion }}</div>
                                        @elseif ($dimensiones->has($a->id))
                                            <div style="font-size: .85rem; color: #c9d6ea; margin-top: 4px;">
                                                <em>Sin normas de referencia cargadas para esta prueba; se muestra el puntaje directo por área.</em>
                                                <table style="margin-top: 6px;">
                                                    <thead><tr><th>Área</th><th>Puntaje</th><th>%</th></tr></thead>
                                                    <tbody>
                                                        @foreach ($dimensiones->get($a->id) as $d)
                                                            <tr>
                                                                <td>{{ $d->dimension_nombre }}</td>
                                                                <td>{{ rtrim(rtrim(number_format((float) $d->puntaje_bruto, 2), '0'), '.') }}{{ $d->puntaje_maximo !== null ? '/' . rtrim(rtrim(number_format((float) $d->puntaje_maximo, 2), '0'), '.') : '' }}</td>
                                                                <td>{{ $d->porcentaje !== null ? rtrim(rtrim(number_format((float) $d->porcentaje, 2), '0'), '.') . '%' : '—' }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    </div>
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
            @foreach ($postulacion->camposFormulario() as $campo => $valor)
                <div><dt>{{ $campo }}</dt><dd>{{ $valor ?? '—' }}</dd></div>
            @endforeach
        </dl>
    </div>
@endsection
