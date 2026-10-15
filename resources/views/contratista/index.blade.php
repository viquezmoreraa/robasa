@extends('layouts.app')

@section('titulo', 'Candidatos')

@section('contenido')
    <h1>Candidatos</h1>
    <p class="sub">Postulantes que completaron el formulario. Seleccione uno para enviarle pruebas.</p>

    <div class="card tabla-scroll">
        @if ($postulaciones->isEmpty())
            <p>Todavía no hay postulaciones.</p>
        @else
            <table>
                <thead>
                    <tr><th>Nombre</th><th>Puesto</th><th>Correo</th><th>Fecha</th><th>Pruebas</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach ($postulaciones as $p)
                        <tr>
                            <td>{{ $p->nombreCompleto() }}</td>
                            <td>{{ str_replace('_', ' ', $p->puesto ?? '—') }}</td>
                            <td>{{ $p->correo }}</td>
                            <td>{{ $p->created_at->format('d/m/Y') }}</td>
                            <td>
                                @if ($p->asignaciones_count)
                                    <span class="badge {{ $p->completadas_count === $p->asignaciones_count ? 'badge-ok' : 'badge-pend' }}">
                                        {{ $p->completadas_count }}/{{ $p->asignaciones_count }} completadas
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                            <td><a class="btn" href="{{ route('contratista.show', $p) }}">Ver / enviar pruebas</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
