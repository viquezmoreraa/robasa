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
    </style>
@endpush

@section('contenido')
    <h1>Hola, {{ auth()->user()->name }}</h1>
    <p class="sub">Aquí puede revisar su postulación y las tareas que le envíe el equipo de ROBASA.</p>

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
                        <span class="badge badge-ok">Completada el {{ $t->fecha_fin?->format('d/m/Y H:i') }}</span>
                    @else
                        {{-- La prueba se responde como formulario en una ventana aparte --}}
                        <a class="btn" href="{{ route('postulante.formulario', $t) }}" target="_blank" rel="noopener">
                            {{ $t->estado === 'EN_CURSO' ? 'Continuar prueba' : 'Ir al formulario' }}
                        </a>
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
