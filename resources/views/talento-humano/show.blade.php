@extends('layouts.app')

@section('titulo', $postulacion->nombreCompleto())

@push('estilos')
    <style>
        .pruebas-encabezado { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 14px; }
        .pruebas-encabezado h2 { margin-bottom: 0; }
        .prueba { border: 1px solid rgba(255,255,255,.1); border-radius: 12px; padding: 16px 18px; background: rgba(9,24,51,.35); }
        .prueba + .prueba { margin-top: 12px; }
        .prueba-pendiente { padding: 12px 18px; background: transparent; }
        .prueba-cabeza { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
        .prueba-cabeza h3 { font-size: 1.02rem; margin-bottom: 2px; }
        .prueba-cabeza .badge { flex: none; }
        .prueba-meta { font-size: .8rem; color: #9fb3d1; }
        .resultado { margin-top: 14px; padding: 12px 14px; border-left: 3px solid #f58426; border-radius: 6px; background: rgba(245,132,38,.08); }
        .resultado-etiqueta { display: block; font-size: .72rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #9fb3d1; margin-bottom: 4px; }
        .resultado-resumen { font-size: 1.05rem; line-height: 1.4; word-break: break-word; }
        .interpretacion { margin-top: 14px; }
        .interpretacion p { font-size: .92rem; line-height: 1.6; color: #dbe5f3; white-space: pre-line; max-width: 75ch; }
        .nota { margin-top: 12px; font-size: .82rem; color: #9fb3d1; font-style: italic; }
        .areas { list-style: none; margin-top: 10px; display: grid; gap: 10px; }
        .area-fila { display: flex; justify-content: space-between; align-items: baseline; gap: 12px; font-size: .9rem; }
        .area-puntaje { white-space: nowrap; font-variant-numeric: tabular-nums; }
        .area-puntaje span { color: #9fb3d1; margin-left: 6px; font-size: .82rem; }
        .barra { height: 6px; margin-top: 5px; border-radius: 999px; background: rgba(255,255,255,.1); overflow: hidden; }
        .barra span { display: block; height: 100%; border-radius: inherit; background: #f58426; }
        .entrega { margin-top: 14px; }
        .entrega video { display: block; width: 100%; max-width: 420px; border-radius: 8px; }
        .comentario { margin-top: 6px; font-size: .88rem; color: #c9d6ea; white-space: pre-line; }
        .mensaje-campo { margin: -6px 0 12px; font-size: .85rem; }
        .mensaje-campo:empty { display: none; }
        .mensaje-campo[data-estado="error"] { color: #ff9b8f; }
        .mensaje-campo[data-estado="ok"] { color: #5fe39a; }
        input.invalido { border-color: #e74c3c; }
        input.valido { border-color: #2ecc71; }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('js/coincidencia-password.js') }}"></script>
@endpush

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

    @php
        // 12.50 -> "12.5", 12.00 -> "12": los puntajes se guardan con 2 decimales
        $num = fn ($v) => rtrim(rtrim(number_format((float) $v, 2), '0'), '.');
        // Primero las completadas (lo que Talento Humano viene a revisar), luego las pendientes
        $asignaciones = $postulacion->asignaciones->sortBy(fn ($a) => $a->completada() ? 0 : 1)->values();
        $completadas = $postulacion->asignaciones->filter->completada()->count();
    @endphp

    <div class="card">
        <div class="pruebas-encabezado">
            <h2>Pruebas y resultados</h2>
            @if ($postulacion->asignaciones->isNotEmpty())
                <span class="badge {{ $completadas === $postulacion->asignaciones->count() ? 'badge-ok' : 'badge-pend' }}">
                    {{ $completadas }} de {{ $postulacion->asignaciones->count() }} completadas
                </span>
            @endif
        </div>

        @if ($asignaciones->isEmpty())
            <p>Aún no se le ha enviado ninguna prueba.</p>
        @endif

        @foreach ($asignaciones as $a)
            @php $r = $resultados->get($a->id); @endphp
            <article class="prueba {{ $a->completada() ? '' : 'prueba-pendiente' }}">
                <header class="prueba-cabeza">
                    <div>
                        <h3>{{ $a->prueba->nombre }}</h3>
                        <p class="prueba-meta">
                            Intento {{ $a->intento }} · Asignada {{ $a->fecha_asignacion?->format('d/m/Y H:i') }}
                            @if ($a->fecha_fin) · Entregada {{ $a->fecha_fin->format('d/m/Y H:i') }} @endif
                        </p>
                    </div>
                    <span class="badge {{ $a->completada() ? 'badge-ok' : 'badge-pend' }}">
                        {{ $a->completada() ? '✓ Completada' : ucfirst(strtolower(str_replace('_', ' ', $a->estado))) }}
                    </span>
                </header>

                @if ($r)
                    <div class="resultado">
                        <span class="resultado-etiqueta">Resultado</span>
                        <strong class="resultado-resumen">{{ $r->resumen }}</strong>
                    </div>

                    @if ($r->interpretacion)
                        <div class="interpretacion">
                            <span class="resultado-etiqueta">Interpretación</span>
                            <p>{{ $r->interpretacion }}</p>
                        </div>
                    @elseif ($dimensiones->has($a->id))
                        <p class="nota">Sin normas de referencia cargadas para esta prueba; se muestra el puntaje directo por área.</p>
                        <ul class="areas">
                            @foreach ($dimensiones->get($a->id) as $d)
                                <li>
                                    <div class="area-fila">
                                        <span class="area-nombre">{{ $d->dimension_nombre }}</span>
                                        <span class="area-puntaje">
                                            <strong>{{ $num($d->puntaje_bruto) }}{{ $d->puntaje_maximo !== null ? '/' . $num($d->puntaje_maximo) : '' }}</strong>
                                            @if ($d->porcentaje !== null) <span>{{ $num($d->porcentaje) }}%</span> @endif
                                        </span>
                                    </div>
                                    @if ($d->porcentaje !== null)
                                        <div class="barra" role="presentation"><span style="width: {{ min(100, max(0, (float) $d->porcentaje)) }}%"></span></div>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                @endif

                @if ($a->archivo_path || $a->respuesta_texto)
                    <div class="entrega">
                        @if ($a->archivo_path && $a->prueba->esVideo())
                            <video src="{{ route('talento-humano.archivo', $a) }}" controls preload="metadata"></video>
                        @elseif ($a->archivo_path)
                            <a href="{{ route('talento-humano.archivo', $a) }}">Descargar archivo</a>
                        @endif
                        @if ($a->respuesta_texto)
                            <p class="comentario">{{ $a->respuesta_texto }}</p>
                        @endif
                    </div>
                @endif
            </article>
        @endforeach
    </div>

    @if ($postulacion->user?->rol === 'postulante')
        <div class="card">
            <h2>Cambiar contraseña del postulante</h2>
            <p style="margin-bottom: 14px; font-size: .9rem; color: #c9d6ea;">
                Úselo solo si el postulante olvidó su contraseña y no puede recuperarla por correo.
                Usuario: <strong>{{ $postulacion->user->username }}</strong>. Se cerrarán sus sesiones abiertas.
            </p>
            <form method="POST" action="{{ route('talento-humano.contrasena', $postulacion) }}" style="max-width: 420px;">
                @csrf
                <label for="password">Contraseña nueva</label>
                <x-campo-password autocomplete="new-password" minlength="8" required />

                <label for="password_confirmation">Confirme la contraseña</label>
                <x-campo-password id="password_confirmation" name="password_confirmation" autocomplete="new-password" minlength="8" data-coincide-con="password" aria-describedby="coincidenciaMensaje" required />
                <p class="mensaje-campo" id="coincidenciaMensaje" aria-live="polite"></p>

                <button class="btn" type="submit" onclick="return confirm('¿Cambiar la contraseña de este postulante?')">Cambiar contraseña</button>
            </form>
        </div>
    @endif

    <div class="card">
        <h2>Datos de la postulación</h2>
        <dl class="datos">
            @foreach ($postulacion->camposFormulario() as $campo => $valor)
                <div><dt>{{ $campo }}</dt><dd>{{ $valor ?? '—' }}</dd></div>
            @endforeach
        </dl>
    </div>
@endsection
