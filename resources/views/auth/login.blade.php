@extends('layouts.app')

@section('titulo', $empleado ? 'Acceso empleados' : 'Iniciar sesión')

@section('contenido')
    <div class="card" style="max-width: 420px; margin: 40px auto;">
        <h1>{{ $empleado ? 'Acceso empleados' : 'Iniciar sesión' }}</h1>
        <p class="sub">{{ $empleado ? 'Ingrese con sus credenciales de contratista.' : 'Ingrese para ver su perfil y sus tareas.' }}</p>

        <form method="POST" action="{{ route('login.store') }}">
            @csrf
            <label for="usuario">Usuario o correo electrónico</label>
            <input id="usuario" name="usuario" value="{{ old('usuario') }}" autocomplete="username" required autofocus>

            <label for="password">Contraseña</label>
            <input id="password" type="password" name="password" autocomplete="current-password" required>

            <label style="display: flex; align-items: center; gap: 8px;">
                <input type="checkbox" name="recordar" value="1" style="width: auto; margin: 0;" @checked(old('recordar'))>
                Recordarme
            </label>

            <button class="btn" type="submit" style="width: 100%;">Entrar</button>
        </form>
        @unless ($empleado)
            <p style="margin-top: 16px; font-size: .9rem;">¿No tiene cuenta? <a href="/vacantes.html">Postúlese y cree su cuenta</a></p>
        @endunless
        <p style="margin-top: 16px; font-size: .9rem;"><a href="/">← Volver al inicio</a></p>
    </div>
@endsection
