@extends('layouts.app')

@section('titulo', 'Candidatos')

@section('contenido')
    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-start; gap: 12px;">
        <div>
            <h1>Candidatos</h1>
            <p class="sub">Postulantes que completaron el formulario. Seleccione uno para cambiar su estado o enviarle pruebas.</p>
        </div>
        <a class="btn btn-sec" href="{{ route('talento-humano.vacantes') }}">Vacantes y plazas</a>
    </div>

    <div class="card tabla-scroll">
        @if ($postulaciones->isEmpty())
            <p>Todavía no hay postulaciones.</p>
        @else
            <table>
                <thead>
                    <tr><th>Nombre</th><th>Puesto</th><th>Correo</th><th>Fecha</th><th>Estado</th><th>Pruebas</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach ($postulaciones as $p)
                        <tr>
                            <td>{{ $p->nombreCompleto() }}</td>
                            <td>{{ str_replace('_', ' ', $p->puesto ?? '—') }}</td>
                            <td>{{ $p->correo }}</td>
                            <td>{{ $p->created_at->format('d/m/Y') }}</td>
                            <td><span class="badge {{ $p->estadoClase() }}">{{ $p->estadoEtiqueta() }}</span></td>
                            <td>
                                @if ($p->asignaciones_count)
                                    <span class="badge {{ $p->completadas_count === $p->asignaciones_count ? 'badge-ok' : 'badge-pend' }}">
                                        {{ $p->completadas_count }}/{{ $p->asignaciones_count }} completadas
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                            <td><a class="btn" href="{{ route('talento-humano.show', $p) }}">Ver / enviar pruebas</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
