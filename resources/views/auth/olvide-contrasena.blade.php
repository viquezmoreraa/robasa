@extends('layouts.app')

@section('titulo', 'Recuperar contraseña')

@section('contenido')
    <div class="card" style="max-width: 420px; margin: 40px auto;">
        <h1>Recuperar contraseña</h1>
        <p class="sub">Ingrese el correo electrónico de su cuenta y le enviaremos un enlace para crear una contraseña nueva.</p>

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <label for="email">Correo electrónico</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" maxlength="150" autocomplete="email" required autofocus>

            <button class="btn" type="submit" style="width: 100%;">Enviar enlace</button>
        </form>
        <p style="margin-top: 16px; font-size: .9rem;"><a href="{{ route('login') }}">← Volver a iniciar sesión</a></p>
    </div>
@endsection
