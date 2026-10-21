@extends('layouts.app')

{{--
    Ventana aparte donde el postulante responde una prueba asignada.
    De momento solo contiene la PORTADA del formulario. El formulario de preguntas
    (tablas preguntas / opciones, respuestas en la tabla respuestas) todavía no está hecho:
    va en la sección marcada "FORMULARIO DE LA PRUEBA" más abajo.
--}}

@section('titulo', $asignacion->prueba->nombre)

@push('estilos')
    <style>
        .portada { text-align: center; padding: 40px 28px; }
        .portada .etiqueta {
            display: inline-block; font-size: .75rem; letter-spacing: .08em; text-transform: uppercase;
            color: #f9a660; margin-bottom: 10px;
        }
        .portada h1 { font-size: 2rem; margin-bottom: 10px; }
        .portada .datos-prueba { display: flex; justify-content: center; flex-wrap: wrap; gap: 10px; margin: 18px 0 26px; }
        .portada .instrucciones {
            max-width: 640px; margin: 0 auto 28px; text-align: left; white-space: pre-line;
            background: rgba(255,255,255,.04); border-radius: 10px; padding: 16px 18px; color: #dbe5f3;
        }
        .pendiente-form {
            border: 2px dashed rgba(255,255,255,.2); border-radius: 14px; padding: 28px;
            text-align: center; color: #9fb3d1;
        }
    </style>
@endpush

@section('contenido')
    <section class="card portada">
        <span class="etiqueta">Prueba asignada · Intento {{ $asignacion->intento }}</span>
        <h1>{{ $asignacion->prueba->nombre }}</h1>
        <p class="sub" style="margin-bottom: 0;">Asignada el {{ $asignacion->fecha_asignacion?->format('d/m/Y') }}</p>

        <div class="datos-prueba">
            @if ($totalPreguntas)
                <span class="badge badge-pend">{{ $totalPreguntas }} preguntas</span>
            @endif
            <span class="badge badge-pend">
                {{ $asignacion->prueba->minutos_limite ? $asignacion->prueba->minutos_limite . ' minutos' : 'Sin límite de tiempo' }}
            </span>
            @if ($asignacion->completada())
                <span class="badge badge-ok">✓ Completada</span>
            @endif
        </div>

        @if ($asignacion->prueba->instrucciones)
            <div class="instrucciones">{{ $asignacion->prueba->instrucciones }}</div>
        @endif

        @if ($asignacion->completada())
            <p>Ya completó esta prueba. Puede cerrar esta ventana.</p>
        @else
            <button class="btn" type="button" disabled style="opacity: .6; cursor: not-allowed;">Comenzar prueba</button>
        @endif
    </section>

    {{-- ===================== FORMULARIO DE LA PRUEBA ===================== --}}
    {{-- Pendiente: aquí se renderizarán las preguntas de la prueba y se guardarán las respuestas. --}}
    @unless ($asignacion->completada())
        <section class="pendiente-form">
            <p>El formulario de esta prueba estará disponible próximamente.</p>
        </section>
    @endunless
@endsection
