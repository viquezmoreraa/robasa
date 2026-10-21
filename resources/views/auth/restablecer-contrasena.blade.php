@extends('layouts.app')

@section('titulo', 'Nueva contraseña')

{{-- La URL lleva el token: que no se filtre por el encabezado Referer al seguir un enlace --}}
@push('estilos')
    <meta name="referrer" content="no-referrer">
    <style>
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
    <div class="card" style="max-width: 420px; margin: 40px auto;">
        <h1>Nueva contraseña</h1>
        <p class="sub">Cree una contraseña de al menos 8 caracteres que combine letras y números.</p>

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <label for="email">Correo electrónico</label>
            <input id="email" type="email" name="email" value="{{ old('email', $email) }}" maxlength="150" autocomplete="username" required @readonly($email)>

            <label for="password">Contraseña nueva</label>
            <x-campo-password autocomplete="new-password" minlength="8" required autofocus />

            <label for="password_confirmation">Confirme la contraseña</label>
            <x-campo-password id="password_confirmation" name="password_confirmation" autocomplete="new-password" minlength="8" data-coincide-con="password" aria-describedby="coincidenciaMensaje" required />
            <p class="mensaje-campo" id="coincidenciaMensaje" aria-live="polite"></p>

            <button class="btn" type="submit" style="width: 100%;">Guardar contraseña</button>
        </form>
        <p style="margin-top: 16px; font-size: .9rem;"><a href="{{ route('password.request') }}">Solicitar un enlace nuevo</a></p>
    </div>
@endsection
