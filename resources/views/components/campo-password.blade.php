{{-- Campo de contraseña con botón de ojo para mostrar u ocultar lo que se escribió --}}
@props(['id' => 'password', 'name' => 'password', 'autocomplete' => 'current-password'])

<div class="campo-password">
    <input id="{{ $id }}" type="password" name="{{ $name }}" autocomplete="{{ $autocomplete }}" {{ $attributes }}>
    <button type="button" class="toggle-password" aria-controls="{{ $id }}" aria-pressed="false" aria-label="Mostrar contraseña" title="Mostrar contraseña">
        <svg class="ojo" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        <svg class="ojo-tachado" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
    </button>
</div>

@once
    @push('estilos')
        <style>
            .campo-password { position: relative; margin-bottom: 12px; }
            .campo-password input { margin-bottom: 0; padding-right: 46px; }
            .toggle-password {
                position: absolute; top: 0; right: 0; bottom: 0; width: 44px;
                display: flex; align-items: center; justify-content: center;
                background: none; border: 0; color: #9fb3d1; cursor: pointer; border-radius: 0 8px 8px 0;
            }
            .toggle-password:hover, .toggle-password:focus-visible { color: #f58426; }
            .toggle-password:focus-visible { outline: 2px solid #f58426; outline-offset: -2px; }
            .toggle-password .ojo-tachado,
            .toggle-password[aria-pressed="true"] .ojo { display: none; }
            .toggle-password[aria-pressed="true"] .ojo-tachado { display: block; }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('click', (e) => {
                const boton = e.target.closest('.toggle-password');
                if (!boton) return;

                const input = document.getElementById(boton.getAttribute('aria-controls'));
                const mostrar = input.type === 'password';
                const texto = mostrar ? 'Ocultar contraseña' : 'Mostrar contraseña';

                input.type = mostrar ? 'text' : 'password';
                boton.setAttribute('aria-pressed', String(mostrar));
                boton.setAttribute('aria-label', texto);
                boton.title = texto;
                input.focus();
            });

            // Al enviar se vuelve a ocultar, para que el navegador no la guarde como texto plano
            document.addEventListener('submit', (e) => {
                e.target.querySelectorAll('.campo-password input[type="text"]').forEach((input) => {
                    input.type = 'password';
                });
            });
        </script>
    @endpush
@endonce
