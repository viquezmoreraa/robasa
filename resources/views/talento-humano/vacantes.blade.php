@extends('layouts.app')

@section('titulo', 'Vacantes')

@push('estilos')
    <style>
        .vacantes td input { width: 110px; margin-bottom: 0; }
        .vacantes .acciones { display: flex; justify-content: flex-end; margin-top: 18px; }
    </style>
@endpush

@section('contenido')
    <p style="margin-bottom: 14px;"><a href="{{ route('talento-humano.index') }}">← Volver a candidatos</a></p>
    <h1>Vacantes</h1>
    <p class="sub">Cantidad de plazas abiertas por puesto. Se muestra en la página de vacantes; con 0 el puesto aparece sin plazas y no recibe postulaciones.</p>

    <form method="POST" action="{{ route('talento-humano.vacantes.actualizar') }}" class="card tabla-scroll vacantes">
        @csrf
        <table>
            <thead><tr><th>Puesto</th><th>Plazas</th><th>Estado</th><th>Última actualización</th></tr></thead>
            <tbody>
                @foreach ($vacantes as $v)
                    <tr>
                        <td><label for="cantidad-{{ $v->id }}" style="margin: 0; color: inherit; font-size: inherit;">{{ $v->nombre }}</label></td>
                        <td><input id="cantidad-{{ $v->id }}" type="number" name="cantidad[{{ $v->id }}]" value="{{ old('cantidad.' . $v->id, $v->cantidad) }}" min="0" max="999" step="1" required></td>
                        <td>
                            <span class="badge {{ $v->cantidad > 0 ? 'badge-ok' : 'badge-no' }}">{{ $v->cantidad > 0 ? 'Abierta' : 'Sin plazas' }}</span>
                        </td>
                        <td>{{ $v->updated_at?->format('d/m/Y H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="acciones"><button class="btn" type="submit">Guardar plazas</button></div>
    </form>
@endsection
