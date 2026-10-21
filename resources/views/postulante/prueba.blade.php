@extends('layouts.app')

@section('titulo', $prueba->nombre)

@section('contenido')
    <h1>{{ $prueba->nombre }}</h1>
    <p class="sub" style="white-space: pre-line;">{{ $prueba->instrucciones }}</p>

    <form method="POST" action="{{ route('postulante.prueba.guardar', $asignacion) }}">
        @csrf

        @foreach ($preguntas as $pregunta)
            @php
                $previas = $respuestasPrevias->get($pregunta->id, collect());
                $previaMas = $previas->firstWhere('seleccion', 'MAS')?->opcion_id;
                $previaMenos = $previas->firstWhere('seleccion', 'MENOS')?->opcion_id;
                $previaUnica = $previas->firstWhere('seleccion', 'UNICA')?->opcion_id;
                $opcionesPregunta = $opciones->get($pregunta->id, collect());
            @endphp
            <div class="card">
                <h2>{{ $pregunta->numero }}. {{ $pregunta->enunciado }}</h2>

                @if ($pregunta->tipo === 'MAS_MENOS')
                    <p class="sub">Marque la opción que lo describe MÁS y la que lo describe MENOS (deben ser distintas).</p>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div>
                            <label>Más</label>
                            @foreach ($opcionesPregunta as $opcion)
                                <label style="font-weight: normal; display: flex; gap: 8px; align-items: flex-start; margin-bottom: 10px;">
                                    <input type="radio" name="respuestas[{{ $pregunta->id }}][mas]" value="{{ $opcion->id }}"
                                        style="width: auto; margin-top: 3px;"
                                        {{ (string) $previaMas === (string) $opcion->id ? 'checked' : '' }}
                                        {{ $pregunta->obligatoria ? 'required' : '' }}>
                                    <span>{{ $opcion->texto }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div>
                            <label>Menos</label>
                            @foreach ($opcionesPregunta as $opcion)
                                <label style="font-weight: normal; display: flex; gap: 8px; align-items: flex-start; margin-bottom: 10px;">
                                    <input type="radio" name="respuestas[{{ $pregunta->id }}][menos]" value="{{ $opcion->id }}"
                                        style="width: auto; margin-top: 3px;"
                                        {{ (string) $previaMenos === (string) $opcion->id ? 'checked' : '' }}
                                        {{ $pregunta->obligatoria ? 'required' : '' }}>
                                    <span>{{ $opcion->texto }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @else
                    @foreach ($opcionesPregunta as $opcion)
                        <label style="font-weight: normal; display: flex; gap: 8px; align-items: flex-start; margin-bottom: 10px;">
                            <input type="radio" name="respuestas[{{ $pregunta->id }}]" value="{{ $opcion->id }}"
                                style="width: auto; margin-top: 3px;"
                                {{ (string) $previaUnica === (string) $opcion->id ? 'checked' : '' }}
                                {{ $pregunta->obligatoria ? 'required' : '' }}>
                            <span>{{ $opcion->texto }}</span>
                        </label>
                    @endforeach
                @endif
            </div>
        @endforeach

        <button class="btn" type="submit">Entregar prueba</button>
    </form>
@endsection
