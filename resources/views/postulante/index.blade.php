@extends('layouts.app')

@section('titulo', 'Mi panel')

@push('estilos')
    <style>
        .tabs { display: flex; gap: 8px; margin-bottom: 20px; }
        .tab { background: transparent; border: 1px solid rgba(255,255,255,.25); color: #fff; }
        .tab.activa { background: #f58426; border-color: #f58426; }
        .panel[hidden] { display: none; }
        .tarea { display: flex; gap: 16px; align-items: flex-start; }
        .check {
            flex: none; width: 34px; height: 34px; border-radius: 50%; display: grid; place-items: center;
            border: 2px solid rgba(255,255,255,.3); font-weight: 700;
        }
        .check.ok { background: #2ecc71; border-color: #2ecc71; }
        .tarea-cuerpo { flex: 1; min-width: 0; }
        .estado-postulacion { display: flex; flex-wrap: wrap; align-items: center; gap: 8px 12px; padding: 14px 18px; }
        .estado-postulacion .titulo { color: #9fb3d1; font-size: .9rem; }
        .estado-postulacion .desc { color: #c9d6ea; font-size: .88rem; }
    </style>
@endpush

@section('contenido')
    <h1>Hola, {{ auth()->user()->name }}</h1>
    <p class="sub">Aquí puede revisar su postulación y las tareas que le envíe el equipo de ROBASA.</p>

    @if ($postulacion)
        <div class="card estado-postulacion">
            <span class="titulo">Estado de su postulación:</span>
            <span class="badge {{ $postulacion->estadoClase() }}">{{ $postulacion->estadoEtiqueta() }}</span>
            <span class="desc">{{ $postulacion->estadoDescripcion() }}</span>
        </div>
    @endif

    @php $pendientes = $tareas->reject->completada()->count(); @endphp
    <div class="tabs">
        <button class="btn tab activa" data-tab="tareas">Tareas @if ($pendientes) ({{ $pendientes }}) @endif</button>
        <button class="btn tab" data-tab="perfil">Perfil</button>
    </div>

    <section class="panel" id="panel-tareas">
        @forelse ($tareas as $t)
            <div class="card tarea">
                <div class="check {{ $t->completada() ? 'ok' : '' }}">{{ $t->completada() ? '✓' : '' }}</div>
                <div class="tarea-cuerpo">
                    <h2>{{ $t->prueba->nombre }}</h2>
                    <p class="sub" style="margin-bottom: 12px;">Asignada el {{ $t->fecha_asignacion?->format('d/m/Y') }}</p>
                    <p style="margin-bottom: 14px; white-space: pre-line;">{{ $t->prueba->instrucciones }}</p>

                    @if ($t->completada())
                        <span class="badge badge-ok">Entregada el {{ $t->fecha_fin?->format('d/m/Y H:i') }}</span>
                    @elseif ($t->prueba->tienePreguntas())
                        <a class="btn" href="{{ route('postulante.prueba.show', $t) }}">
                            {{ $t->estado === 'EN_CURSO' ? 'Continuar prueba' : 'Responder prueba' }}
                        </a>
                    @else
                        <form method="POST" action="{{ route('postulante.entregar', $t) }}" enctype="multipart/form-data">
                            @csrf
                            <label for="archivo-{{ $t->id }}">Archivo (PDF, Word, Excel o imagen, máx. 10 MB)</label>
                            <input id="archivo-{{ $t->id }}" type="file" name="archivo" required>
                            <label for="resp-{{ $t->id }}">Comentario (opcional)</label>
                            <textarea id="resp-{{ $t->id }}" name="respuesta_texto" rows="3"></textarea>
                            <button class="btn" type="submit">Subir y marcar como completada</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="card"><p>No tiene tareas pendientes por ahora. Cuando el equipo le envíe una prueba aparecerá aquí.</p></div>
        @endforelse
    </section>

    <section class="panel" id="panel-perfil" hidden>
        <div class="card">
            @if ($postulacion)
                <h2>Mi postulación · {{ str_replace('_', ' ', $postulacion->puesto ?? '') }}</h2>
                <dl class="datos">
                    <div><dt>Correo</dt><dd>{{ $postulacion->correo }}</dd></div>
                    @foreach ($postulacion->camposFormulario() as $campo => $valor)
                        <div><dt>{{ $campo }}</dt><dd>{{ $valor ?? '—' }}</dd></div>
                    @endforeach
                </dl>
            @else
                <p style="margin-bottom: 14px;">Aún no ha completado su postulación.</p>
                <a class="btn" href="/vacantes.html">Ver vacantes</a>
            @endif
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('.tab').forEach(btn => btn.addEventListener('click', () => {
            document.querySelectorAll('.tab').forEach(b => b.classList.toggle('activa', b === btn));
            document.querySelectorAll('.panel').forEach(p => p.hidden = p.id !== 'panel-' + btn.dataset.tab);
        }));
    </script>
@endpush
