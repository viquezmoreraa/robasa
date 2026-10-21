<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0d2347">
    <title>@yield('titulo', 'ROBASA') | ROBASA</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', system-ui, sans-serif; min-height: 100vh; color: #fff;
            background: linear-gradient(135deg, #091833 0%, #0f2d59 100%);
        }
        a { color: #f58426; }
        .topbar {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            padding: 14px 24px; background: rgba(9, 24, 51, .85); border-bottom: 1px solid rgba(255,255,255,.08);
        }
        .topbar img { height: 40px; }
        .topbar .usuario { display: flex; align-items: center; gap: 14px; font-size: .9rem; color: #c9d6ea; }
        main { max-width: 1050px; margin: 0 auto; padding: 32px 16px; }
        h1 { font-size: 1.6rem; margin-bottom: 6px; }
        h2 { font-size: 1.2rem; margin-bottom: 14px; }
        .sub { color: #9fb3d1; margin-bottom: 24px; }
        .card {
            background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.1);
            border-radius: 14px; padding: 22px; margin-bottom: 20px;
        }
        .btn {
            display: inline-block; border: 0; border-radius: 8px; padding: 10px 18px; cursor: pointer;
            background: #f58426; color: #fff; font-weight: 600; text-decoration: none; font-size: .95rem;
        }
        .btn-sec { background: transparent; border: 1px solid rgba(255,255,255,.3); }
        input, select, textarea {
            width: 100%; padding: 10px 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,.2);
            background: rgba(255,255,255,.08); color: #fff; font: inherit; margin-bottom: 12px;
        }
        select option { color: #000; }
        label { display: block; font-size: .9rem; margin-bottom: 6px; color: #c9d6ea; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 10px 8px; border-bottom: 1px solid rgba(255,255,255,.08); font-size: .92rem; }
        th { color: #9fb3d1; font-weight: 600; }
        .tabla-scroll { overflow-x: auto; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: .78rem; font-weight: 600; }
        .badge-pend { background: rgba(245,132,38,.2); color: #f9a660; }
        .badge-ok { background: rgba(46,204,113,.2); color: #5fe39a; }
        .badge-no { background: rgba(231,76,60,.2); color: #ff9b8f; }
        .alerta { padding: 12px 16px; border-radius: 8px; margin-bottom: 18px; background: rgba(46,204,113,.15); color: #7deaa9; }
        .alerta-error { background: rgba(231,76,60,.15); color: #ff9b8f; }
        .datos { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px 24px; }
        .datos dt { color: #9fb3d1; font-size: .8rem; text-transform: capitalize; }
        .datos dd { margin-bottom: 4px; word-break: break-word; }
    </style>
    @stack('estilos')
</head>
<body>
    <header class="topbar">
        <a href="/"><img src="/imagenes/robasa-logo.png" alt="ROBASA"></a>
        @auth
            <div class="usuario">
                <span>{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-sec" type="submit">Cerrar sesión</button>
                </form>
            </div>
        @endauth
    </header>
    <main>
        @if (session('ok'))
            <div class="alerta">{{ session('ok') }}</div>
        @endif
        @if ($errors->any())
            <div class="alerta alerta-error">{{ $errors->first() }}</div>
        @endif
        @yield('contenido')
    </main>
    @stack('scripts')
</body>
</html>
